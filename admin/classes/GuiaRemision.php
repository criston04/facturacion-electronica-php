<?php
// Greenter (xmldsig) emite avisos "Deprecated" en PHP 8.4; los silenciamos para no romper el JSON de respuesta.
error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE);

/**
 * GuiaRemision: emisión de Guía de Remisión Electrónica (GRE remitente, tipo 09).
 *
 * Usa Greenter (gre-api) que arma el XML, lo firma con certificado y lo envía a la API REST
 * de GRE con OAuth2. Por defecto apunta al entorno de PRUEBAS gratuito (gre-test.nubefact.com)
 * con las credenciales de demostración, para poder probar sin credenciales reales de SUNAT.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Greenter\Api;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Client\Client;
use Greenter\Model\Despatch\Despatch;
use Greenter\Model\Despatch\DespatchDetail;
use Greenter\Model\Despatch\Shipment;
use Greenter\Model\Despatch\Direction;
use Greenter\Model\Despatch\Vehicle;
use Greenter\Model\Despatch\Driver;
use Greenter\Model\Despatch\Transportist;

class GuiaRemision
{
    private $con;

    // --- Configuración GRE de PRUEBAS (entorno gratuito de Greenter/nubefact) ---
    // En PRODUCCIÓN: usar endpoints de SUNAT (api-seguridad/api-cpe), tu RUC, tu usuario SOL,
    // tu client_id/secret (portal SOL) y tu certificado real.
    private array $gre = [
        'endpoint_auth' => 'https://gre-test.nubefact.com/v1',
        'endpoint_cpe'  => 'https://gre-test.nubefact.com/v1',
        'ruc'           => '20161515648',
        'usuario_sol'   => 'MODDATOS',
        'clave_sol'     => 'moddatos',
        'client_id'     => 'test-85e5b0ae-255c-4891-a595-0b98c65c9854',
        'client_secret' => 'test-Hty/M6QshYvPgItX2P0+Kw==',
        'razon_social'  => 'EMPRESA DEMO GRE S.A.C.',
        'cert_nombre'   => 'certificado_gre_prueba.pem',
    ];

    function __construct()
    {
        date_default_timezone_set('America/Lima');
        include_once("Database.php");
        $db = new Database();
        $this->con = $db->connect();
        $this->ensureTables();
        $this->ensureCert();
    }

    /** Crea las tablas de la guía si no existen. */
    private function ensureTables(): void
    {
        $this->con->query("CREATE TABLE IF NOT EXISTS guia_remision (
            id INT AUTO_INCREMENT PRIMARY KEY,
            serie VARCHAR(10) NOT NULL,
            correlativo INT NOT NULL,
            tipo_guia VARCHAR(2) DEFAULT '09',
            fecha_emision DATETIME NOT NULL,
            fecha_traslado DATE NOT NULL,
            motivo_traslado VARCHAR(2) NOT NULL,
            modalidad_transporte VARCHAR(2) NOT NULL,
            peso_total DECIMAL(10,2) DEFAULT 0,
            unidad_peso VARCHAR(5) DEFAULT 'KGM',
            id_cliente INT DEFAULT NULL,
            id_usuario INT DEFAULT NULL,
            ubigeo_partida VARCHAR(6), dir_partida VARCHAR(255),
            ubigeo_llegada VARCHAR(6), dir_llegada VARCHAR(255),
            transportista_ruc VARCHAR(11), transportista_razon VARCHAR(255), transportista_mtc VARCHAR(30),
            vehiculo_placa VARCHAR(10),
            conductor_doc VARCHAR(15), conductor_nombres VARCHAR(150), conductor_apellidos VARCHAR(150), conductor_licencia VARCHAR(20),
            observacion VARCHAR(255),
            sunat_ticket VARCHAR(100), sunat_estado VARCHAR(30) DEFAULT 'pendiente',
            sunat_mensaje TEXT, xml_file VARCHAR(255),
            estado VARCHAR(20) DEFAULT 'EMITIDA'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->con->query("CREATE TABLE IF NOT EXISTS guia_remision_detalle (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_guia INT NOT NULL,
            id_producto INT DEFAULT NULL,
            codigo VARCHAR(50), descripcion VARCHAR(255),
            unidad VARCHAR(5) DEFAULT 'NIU', cantidad DECIMAL(10,2) DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    /** Genera un certificado de prueba (CN = RUC de pruebas GRE) si no existe. */
    private function ensureCert(): void
    {
        $dir = __DIR__ . '/../certificados/';
        $path = $dir . $this->gre['cert_nombre'];
        if (file_exists($path)) return;
        if (!is_dir($dir)) mkdir($dir, 0775, true);

        $cnf = $dir . '_gre_openssl.cnf';
        file_put_contents($cnf, "[ req ]\ndistinguished_name = dn\nprompt = no\n[ dn ]\n");
        $config = ['config' => $cnf, 'digest_alg' => 'sha256', 'private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA];
        $dn = ['countryName' => 'PE', 'organizationName' => $this->gre['razon_social'], 'commonName' => $this->gre['ruc']];
        $pk = openssl_pkey_new($config);
        if ($pk) {
            $csr = openssl_csr_new($dn, $pk, $config);
            $x509 = openssl_csr_sign($csr, null, $pk, 730, $config);
            openssl_x509_export($x509, $certPem);
            openssl_pkey_export($pk, $keyPem, null, $config);
            file_put_contents($path, $keyPem . $certPem);
        }
        @unlink($cnf);
    }

    /** Catálogo 20 SUNAT — motivos de traslado (extracto). */
    public static function motivosTraslado(): array
    {
        return [
            '01' => 'Venta',
            '02' => 'Compra',
            '04' => 'Traslado entre establecimientos de la misma empresa',
            '08' => 'Importación',
            '09' => 'Exportación',
            '13' => 'Otros',
            '14' => 'Venta sujeta a confirmación del comprador',
            '18' => 'Traslado emisor itinerante CP',
        ];
    }

    /** Catálogo 18 SUNAT — modalidad de transporte. */
    public static function modalidades(): array
    {
        return ['01' => 'Transporte público', '02' => 'Transporte privado'];
    }

    public function getNextCorrelativo(string $serie = 'T001'): int
    {
        $stmt = $this->con->prepare("SELECT MAX(correlativo) AS u FROM guia_remision WHERE serie = ?");
        $stmt->bind_param("s", $serie);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        return (int)($r['u'] ?? 0) + 1;
    }

    public function getAll(): array
    {
        $data = [];
        $sql = "SELECT g.*, COALESCE(NULLIF(c.razon_social,''), TRIM(CONCAT_WS(' ', c.nombres, c.apellido_paterno, c.apellido_materno))) AS cliente_nombre
                FROM guia_remision g LEFT JOIN clientes c ON g.id_cliente = c.id ORDER BY g.id DESC";
        $q = $this->con->query($sql);
        if ($q) while ($row = $q->fetch_assoc()) $data[] = $row;
        return $data;
    }

    /** Crea la guía, la guarda y la envía a SUNAT (GRE). */
    public function addRegistro(array $d): array
    {
        $serie = 'T001';
        $modalidad = $d['modalidad_transporte'] ?? '02';
        $motivo = $d['motivo_traslado'] ?? '01';
        $detalle = $d['detalle'] ?? [];
        if (empty($detalle)) return ['status' => 303, 'message' => 'Agregue al menos un bien a trasladar.'];
        if (empty($d['id_cliente'])) return ['status' => 303, 'message' => 'Seleccione el destinatario.'];
        if (empty($d['ubigeo_partida']) || empty($d['ubigeo_llegada'])) return ['status' => 303, 'message' => 'Indique ubigeo de partida y llegada.'];

        $correlativo = $this->getNextCorrelativo($serie);
        $fechaEmision = date('Y-m-d H:i:s');
        $fechaTraslado = !empty($d['fecha_traslado']) ? $d['fecha_traslado'] : date('Y-m-d');

        $cli = $this->con->query("SELECT * FROM clientes WHERE id = " . (int)$d['id_cliente'])->fetch_assoc();

        $this->con->begin_transaction();
        try {
            $stmt = $this->con->prepare(
                "INSERT INTO guia_remision
                (serie, correlativo, tipo_guia, fecha_emision, fecha_traslado, motivo_traslado, modalidad_transporte,
                 peso_total, unidad_peso, id_cliente, id_usuario, ubigeo_partida, dir_partida, ubigeo_llegada, dir_llegada,
                 transportista_ruc, transportista_razon, transportista_mtc, vehiculo_placa,
                 conductor_doc, conductor_nombres, conductor_apellidos, conductor_licencia, observacion)
                VALUES (?, ?, '09', ?, ?, ?, ?, ?, 'KGM', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $peso = (float)($d['peso_total'] ?? 1);
            $idCli = (int)$d['id_cliente'];
            $idUsr = (int)($d['id_usuario'] ?? 0) ?: null;
            $stmt->bind_param(
                "sisssdsiisssssssssssss",
                $serie, $correlativo, $fechaEmision, $fechaTraslado, $motivo, $modalidad,
                $peso, $idCli, $idUsr,
                $d['ubigeo_partida'], $d['dir_partida'], $d['ubigeo_llegada'], $d['dir_llegada'],
                $d['transportista_ruc'], $d['transportista_razon'], $d['transportista_mtc'], $d['vehiculo_placa'],
                $d['conductor_doc'], $d['conductor_nombres'], $d['conductor_apellidos'], $d['conductor_licencia'],
                $d['observacion']
            );
            if (!$stmt->execute()) throw new Exception("Error al guardar la guía: " . $stmt->error);
            $idGuia = $this->con->insert_id;

            $sd = $this->con->prepare("INSERT INTO guia_remision_detalle (id_guia, id_producto, codigo, descripcion, unidad, cantidad) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($detalle as $it) {
                $idp = (int)($it['id_producto'] ?? 0) ?: null;
                $cod = $it['codigo'] ?? ('P' . ($idp ?? '0'));
                $desc = $it['descripcion'] ?? 'Bien';
                $und = $it['unidad'] ?? 'NIU';
                $cant = (float)($it['cantidad'] ?? 1);
                $sd->bind_param("iisssd", $idGuia, $idp, $cod, $desc, $und, $cant);
                $sd->execute();
            }
            $this->con->commit();
        } catch (Exception $e) {
            $this->con->rollback();
            return ['status' => 303, 'message' => $e->getMessage()];
        }

        // Enviar a SUNAT (GRE)
        try {
            $res = $this->enviarGre($idGuia, [
                'serie' => $serie, 'correlativo' => $correlativo, 'fecha_emision' => $fechaEmision,
                'fecha_traslado' => $fechaTraslado, 'motivo' => $motivo, 'modalidad' => $modalidad, 'peso' => $peso,
                'ubigeo_partida' => $d['ubigeo_partida'], 'dir_partida' => $d['dir_partida'],
                'ubigeo_llegada' => $d['ubigeo_llegada'], 'dir_llegada' => $d['dir_llegada'],
                'transportista_ruc' => $d['transportista_ruc'], 'transportista_razon' => $d['transportista_razon'], 'transportista_mtc' => $d['transportista_mtc'],
                'vehiculo_placa' => $d['vehiculo_placa'],
                'conductor_doc' => $d['conductor_doc'], 'conductor_nombres' => $d['conductor_nombres'], 'conductor_apellidos' => $d['conductor_apellidos'], 'conductor_licencia' => $d['conductor_licencia'],
            ], $cli, $detalle);

            $this->actualizar($idGuia, $res);
            return [
                'status' => 202,
                'message' => $res['success'] ? "Guía $serie-" . str_pad((string)$correlativo, 6, '0', STR_PAD_LEFT) . " aceptada por SUNAT." : "Guía guardada, pero SUNAT respondió: {$res['message']}",
                'id_guia' => $idGuia, 'serie' => $serie, 'correlativo' => $correlativo, 'sunat' => $res,
            ];
        } catch (\Throwable $e) {
            return ['status' => 202, 'message' => "Guía #$idGuia guardada, error de envío: " . $e->getMessage(), 'id_guia' => $idGuia, 'sunat' => ['success' => false, 'message' => $e->getMessage()]];
        }
    }

    /** Arma el Despatch de Greenter, lo firma y lo envía; consulta el ticket. */
    private function enviarGre(int $idGuia, array $g, ?array $cli, array $detalle): array
    {
        // Cliente HTTP. verify=false para el entorno de PRUEBAS (Windows sin CA bundle).
        // En PRODUCCIÓN: configurar curl.cainfo/openssl.cafile con un cacert.pem y quitar 'verify'.
        $guzzle = new \GuzzleHttp\Client(['verify' => false, 'timeout' => 30]);
        $factory = new \Greenter\Api\ApiFactory(
            new \Greenter\Sunat\GRE\Api\AuthApi($guzzle, (new \Greenter\Sunat\GRE\Configuration())->setHost($this->gre['endpoint_auth'])),
            $guzzle,
            new \Greenter\Api\InMemoryStore(),
            $this->gre['endpoint_cpe']
        );
        $api = (new Api(null, $factory))
            ->setBuilderOptions(['strict_variables' => true, 'optimizations' => 0, 'debug' => false, 'cache' => false])
            ->setApiCredentials($this->gre['client_id'], $this->gre['client_secret'])
            ->setClaveSOL($this->gre['ruc'], $this->gre['usuario_sol'], $this->gre['clave_sol'])
            ->setCertificate(file_get_contents(__DIR__ . '/../certificados/' . $this->gre['cert_nombre']));

        $company = (new Company())
            ->setRuc($this->gre['ruc'])
            ->setRazonSocial($this->gre['razon_social'])
            ->setAddress((new Address())->setDireccion($g['dir_partida'] ?: '-'));

        $tipoDocCli = ($cli['tipo_documento'] ?? '') === 'RUC' ? '6' : '1';
        $destinatario = (new Client())
            ->setTipoDoc($tipoDocCli)
            ->setNumDoc($cli['numero_documento'] ?? '00000000')
            ->setRznSocial(!empty($cli['razon_social']) ? $cli['razon_social'] : (trim(($cli['nombres'] ?? '') . ' ' . ($cli['apellido_paterno'] ?? '') . ' ' . ($cli['apellido_materno'] ?? '')) ?: 'CLIENTE'));

        $partida = new Direction($g['ubigeo_partida'], $g['dir_partida'] ?: '-');
        $llegada = new Direction($g['ubigeo_llegada'], $g['dir_llegada'] ?: '-');

        $envio = (new Shipment())
            ->setCodTraslado($g['motivo'])
            ->setModTraslado($g['modalidad'])
            ->setFecTraslado(new DateTime($g['fecha_traslado']))
            ->setPesoTotal((float)$g['peso'])
            ->setUndPesoTotal('KGM')
            ->setNumBultos(1)
            ->setLlegada($llegada)
            ->setPartida($partida);

        if ($g['modalidad'] === '01') {
            // Transporte público: datos del transportista
            $envio->setTransportista(
                (new Transportist())
                    ->setTipoDoc('6')
                    ->setNumDoc($g['transportista_ruc'] ?: '20000000000')
                    ->setRznSocial($g['transportista_razon'] ?: 'TRANSPORTES')
                    ->setNroMtc($g['transportista_mtc'] ?: '0001')
            );
        } else {
            // Transporte privado: vehículo + conductor
            $envio->setVehiculo((new Vehicle())->setPlaca($g['vehiculo_placa'] ?: 'ABC123'));
            $envio->setChoferes([
                (new Driver())
                    ->setTipo('Principal')
                    ->setTipoDoc('1')
                    ->setNroDoc($g['conductor_doc'] ?: '00000000')
                    ->setLicencia($g['conductor_licencia'] ?: 'Q00000000')
                    ->setNombres($g['conductor_nombres'] ?: 'CONDUCTOR')
                    ->setApellidos($g['conductor_apellidos'] ?: '-')
            ]);
        }

        $items = [];
        foreach ($detalle as $it) {
            $items[] = (new DespatchDetail())
                ->setCantidad((float)($it['cantidad'] ?? 1))
                ->setUnidad($it['unidad'] ?? 'NIU')
                ->setDescripcion($it['descripcion'] ?? 'Bien')
                ->setCodigo($it['codigo'] ?? 'P1');
        }

        $despatch = (new Despatch())
            ->setVersion('2022')
            ->setTipoDoc('09')
            ->setSerie($g['serie'])
            ->setCorrelativo((string)$g['correlativo'])
            ->setFechaEmision(new DateTime($g['fecha_emision']))
            ->setCompany($company)
            ->setDestinatario($destinatario)
            ->setEnvio($envio)
            ->setDetails($items);

        // El XML se genera y FIRMA dentro de send() antes de pedir el token OAuth.
        $xml = null;
        try {
            $result = $api->send($despatch); // SummaryResult con ticket
            $xml = $api->getLastXml();
        } catch (\Throwable $e) {
            $xml = $api->getLastXml(); // ya firmado, aunque el envío (OAuth) falle
            return [
                'success' => false, 'estado' => 'pendiente', 'ticket' => null, 'xml' => $xml,
                'message' => 'XML generado y firmado correctamente. Para el envío real se necesitan credenciales GRE (client_id/secret del portal SOL): ' . $e->getMessage(),
            ];
        }
        if (!$result || !$result->isSuccess()) {
            $err = $result ? $result->getError() : null;
            return ['success' => false, 'estado' => 'rechazado', 'message' => $err ? ($err->getCode() . ' - ' . $err->getMessage()) : 'Error al enviar la GRE', 'ticket' => null, 'xml' => $xml];
        }
        $ticket = $result->getTicket();

        // Consultar el estado del ticket
        $estado = 'pendiente'; $msg = 'En proceso';
        try {
            $st = $api->getStatus($ticket);
            if ($st->isSuccess() && $st->getCode() === '0') { $estado = 'aceptado'; $msg = 'La guía ha sido aceptada por SUNAT'; }
            elseif ($st->getError()) { $msg = $st->getError()->getCode() . ' - ' . $st->getError()->getMessage(); $estado = $st->getCode() === '0' ? 'aceptado' : 'observado'; }
        } catch (\Throwable $e) { $msg = 'Ticket generado: ' . $ticket; }

        return ['success' => $estado === 'aceptado', 'estado' => $estado, 'message' => $msg, 'ticket' => $ticket, 'xml' => $xml];
    }

    private function actualizar(int $idGuia, array $res): void
    {
        $xmlFile = null;
        if (!empty($res['xml'])) {
            $dir = __DIR__ . '/../uploads/';
            if (!is_dir($dir)) mkdir($dir, 0775, true);
            $nombre = 'GRE-' . $idGuia . '.xml';
            file_put_contents($dir . $nombre, $res['xml']);
            $xmlFile = 'uploads/' . $nombre;
        }
        $ticket = $res['ticket'] ?? null;
        $estado = $res['estado'] ?? 'pendiente';
        $msg = $res['message'] ?? '';
        $stmt = $this->con->prepare("UPDATE guia_remision SET sunat_ticket=?, sunat_estado=?, sunat_mensaje=?, xml_file=? WHERE id=?");
        $stmt->bind_param("ssssi", $ticket, $estado, $msg, $xmlFile, $idGuia);
        $stmt->execute();
    }

    /** Cabecera + detalle de una guía (para impresión). */
    public function getById(int $id): ?array
    {
        $g = $this->con->query("SELECT g.*,
            COALESCE(NULLIF(c.razon_social,''), TRIM(CONCAT_WS(' ', c.nombres, c.apellido_paterno, c.apellido_materno))) AS cliente_nombre,
            c.numero_documento AS cliente_doc, c.tipo_documento AS cliente_tipo, c.direccion AS cliente_dir
            FROM guia_remision g LEFT JOIN clientes c ON g.id_cliente = c.id WHERE g.id = " . (int)$id)->fetch_assoc();
        if (!$g) return null;
        $g['detalle'] = [];
        $r = $this->con->query("SELECT * FROM guia_remision_detalle WHERE id_guia = " . (int)$id);
        if ($r) while ($row = $r->fetch_assoc()) $g['detalle'][] = $row;
        return $g;
    }

    /** Consulta el estado de la guía por su ticket (requiere credenciales GRE reales). */
    public function consultarTicket(int $id): array
    {
        $row = $this->con->query("SELECT sunat_ticket FROM guia_remision WHERE id = " . (int)$id)->fetch_assoc();
        if (!$row) return ['status' => 303, 'message' => 'Guía no encontrada.'];
        if (empty($row['sunat_ticket'])) {
            return ['status' => 303, 'message' => 'La guía no tiene ticket SUNAT. El envío real requiere credenciales GRE del portal SOL.'];
        }
        try {
            $guzzle = new \GuzzleHttp\Client(['verify' => false, 'timeout' => 30]);
            $factory = new \Greenter\Api\ApiFactory(
                new \Greenter\Sunat\GRE\Api\AuthApi($guzzle, (new \Greenter\Sunat\GRE\Configuration())->setHost($this->gre['endpoint_auth'])),
                $guzzle, new \Greenter\Api\InMemoryStore(), $this->gre['endpoint_cpe']
            );
            $api = (new Api(null, $factory))
                ->setApiCredentials($this->gre['client_id'], $this->gre['client_secret'])
                ->setClaveSOL($this->gre['ruc'], $this->gre['usuario_sol'], $this->gre['clave_sol'])
                ->setCertificate(file_get_contents(__DIR__ . '/../certificados/' . $this->gre['cert_nombre']));
            $st = $api->getStatus($row['sunat_ticket']);
            $estado = ($st->isSuccess() && $st->getCode() === '0') ? 'aceptado' : 'observado';
            $msg = $st->getError() ? ($st->getError()->getCode() . ' - ' . $st->getError()->getMessage()) : 'Aceptada por SUNAT';
            $this->con->query("UPDATE guia_remision SET sunat_estado='" . $this->con->real_escape_string($estado) . "', sunat_mensaje='" . $this->con->real_escape_string($msg) . "' WHERE id=" . (int)$id);
            return ['status' => 202, 'message' => 'Estado: ' . strtoupper($estado) . ' — ' . $msg, 'estado' => $estado];
        } catch (\Throwable $e) {
            return ['status' => 303, 'message' => 'Error al consultar: ' . $e->getMessage()];
        }
    }
}

// ----------------------------- Endpoints AJAX -----------------------------

if (isset($_POST['get_catalogos_gre'])) {
    echo json_encode([
        'motivos' => GuiaRemision::motivosTraslado(),
        'modalidades' => GuiaRemision::modalidades(),
    ]);
    exit();
}
if (isset($_POST['get_next_gre'])) {
    $g = new GuiaRemision();
    echo json_encode(['serie' => 'T001', 'correlativo' => $g->getNextCorrelativo('T001')]);
    exit();
}
if (isset($_POST['add_guia'])) {
    $detalle = is_array($_POST['detalle'] ?? null) ? $_POST['detalle'] : json_decode($_POST['detalle'] ?? '[]', true);
    $g = new GuiaRemision();
    echo json_encode($g->addRegistro(array_merge($_POST, ['detalle' => $detalle])));
    exit();
}
if (isset($_POST['get_all_guias'])) {
    $g = new GuiaRemision();
    echo json_encode($g->getAll());
    exit();
}
if (isset($_POST['consultar_guia'])) {
    if (!empty($_POST['id'])) {
        $g = new GuiaRemision();
        echo json_encode($g->consultarTicket((int)$_POST['id']));
        exit();
    }
}
