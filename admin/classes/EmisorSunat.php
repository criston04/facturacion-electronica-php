<?php
// Greenter (xmldsig) emite avisos "Deprecated" en PHP 8.4; los silenciamos para no romper el JSON.
error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE);

/**
 * EmisorSunat: emisor PROPIO con certificado digital, usando la librería Greenter.
 *
 * Genera el XML UBL 2.1, lo FIRMA con el certificado y lo envía a los Web Services de SUNAT
 * (sin pasar por un tercero). Expone la MISMA interfaz pública que Facturalaya
 * (enviar, enviarNota, actualizarVenta) para que el resto del sistema no cambie.
 *
 * La firma del XML siempre funciona localmente; el envío real depende de tener un
 * certificado y credenciales SOL válidos (en beta se usa el certificado de prueba).
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Greenter\See;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Client\Client;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\Note;

class EmisorSunat
{
    private $con;
    private array $emisor = [];
    private array $cfg;
    private ?See $see = null;
    private ?string $lastXml = null;

    public function __construct()
    {
        date_default_timezone_set('America/Lima');
        require_once __DIR__ . '/Database.php';
        $db = new Database();
        $this->con = $db->connect();
        $this->cfg = require __DIR__ . '/config_emision.php';
        $r = $this->con->query("SELECT * FROM emisor WHERE id = 2 LIMIT 1");
        $this->emisor = $r ? ($r->fetch_assoc() ?? []) : [];
    }

    // ---------- Configuración de Greenter ----------
    private function getSee(): See
    {
        if ($this->see) return $this->see;
        $see = new See();
        $see->setCertificate(file_get_contents($this->cfg['sunat']['certificado']));
        $see->setService(
            ($this->cfg['sunat']['entorno'] ?? 'beta') === 'produccion'
                ? SunatEndpoints::FE_PRODUCCION
                : SunatEndpoints::FE_BETA
        );
        $see->setClaveSOL(
            $this->cfg['sunat']['ruc'],
            $this->cfg['sunat']['usuario_sol'],
            $this->cfg['sunat']['clave_sol']
        );
        return $this->see = $see;
    }

    private function getCompany(): Company
    {
        $em = $this->emisor;
        $address = (new Address())
            ->setUbigueo($em['codigo_ubigeo'] ?? '150101')
            ->setDepartamento($em['direccion_departamento'] ?? 'LIMA')
            ->setProvincia($em['direccion_provincia'] ?? 'LIMA')
            ->setDistrito($em['direccion_distrito'] ?? 'LIMA')
            ->setUrbanizacion('-')
            ->setDireccion($em['direccion'] ?? '-')
            ->setCodLocal('0000');

        return (new Company())
            ->setRuc($this->cfg['sunat']['ruc'])
            ->setRazonSocial($em['razon_social'] ?? 'EMPRESA DEMO S.A.C.')
            ->setNombreComercial($em['nom_comercial'] ?? '')
            ->setAddress($address);
    }

    private function getClient(?array $cliente, string $tipoComprobante): Client
    {
        $tipoDoc = '6'; $num = '00000000'; $nombre = 'CLIENTE GENERAL';
        if ($cliente) {
            $td = $cliente['tipo_documento'] ?? '';
            $tipoDoc = $td === 'DNI' ? '1' : ($td === 'RUC' ? '6' : '1');
            $num = $cliente['numero_documento'] ?? '00000000';
            $nombre = !empty($cliente['razon_social'])
                ? $cliente['razon_social']
                : trim(($cliente['nombres'] ?? '') . ' ' . ($cliente['apellido_paterno'] ?? '') . ' ' . ($cliente['apellido_materno'] ?? ''));
        } elseif ($tipoComprobante === 'BOLETA') {
            $tipoDoc = '1';
        }
        return (new Client())
            ->setTipoDoc($tipoDoc)
            ->setNumDoc($num ?: '00000000')
            ->setRznSocial($nombre ?: 'CLIENTE GENERAL');
    }

    /** Convierte el detalle de la BD en líneas de Greenter (con su IGV calculado). */
    private function buildDetails(array $detalle): array
    {
        $items = [];
        foreach ($detalle as $it) {
            $cantidad = (float)($it['cantidad'] ?? 1);
            $precio   = (float)($it['precio_unitario'] ?? 0); // valor de venta sin IGV
            $valorVenta = round($cantidad * $precio, 2);
            $igv = round($valorVenta * 0.18, 2);

            $items[] = (new SaleDetail())
                ->setCodProducto($it['codigo_producto'] ?? ('P' . ($it['id_producto'] ?? '0')))
                ->setUnidad($it['unidad_medida'] ?? 'NIU')
                ->setCantidad($cantidad)
                ->setDescripcion($it['producto_nombre'] ?? 'Producto')
                ->setMtoBaseIgv($valorVenta)
                ->setPorcentajeIgv(18.00)
                ->setIgv($igv)
                ->setTipAfeIgv('10') // gravado - operación onerosa
                ->setTotalImpuestos($igv)
                ->setMtoValorVenta($valorVenta)
                ->setMtoValorUnitario($precio)
                ->setMtoPrecioUnitario(round($precio * 1.18, 2));
        }
        return $items;
    }

    private function totales(array $items): array
    {
        $gravadas = 0; $igv = 0;
        foreach ($items as $d) { $gravadas += $d->getMtoValorVenta(); $igv += $d->getIgv(); }
        return [round($gravadas, 2), round($igv, 2)];
    }

    private function legend(float $total): Legend
    {
        return (new Legend())->setCode('1000')->setValue($this->numeroALetras($total));
    }

    // ---------- Emitir BOLETA / FACTURA ----------
    public function enviar(array $venta, array $detalle, ?array $cliente = null): array
    {
        try {
            $tipo    = $venta['tipo_comprobante'];
            $tipoDoc = $tipo === 'FACTURA' ? '01' : '03';
            $serie   = $tipo === 'FACTURA' ? 'F001' : 'B001';

            $items = $this->buildDetails($detalle);
            [$gravadas, $igv] = $this->totales($items);

            $invoice = (new Invoice())
                ->setUblVersion('2.1')
                ->setTipoOperacion('0101')
                ->setTipoDoc($tipoDoc)
                ->setSerie($serie)
                ->setCorrelativo((string)$venta['correlativo'])
                ->setFechaEmision(new DateTime($venta['fecha_emision'] ?? 'now'))
                ->setTipoMoneda('PEN')
                ->setCompany($this->getCompany())
                ->setClient($this->getClient($cliente, $tipo))
                ->setMtoOperGravadas($gravadas)
                ->setMtoIGV($igv)
                ->setTotalImpuestos($igv)
                ->setValorVenta($gravadas)
                ->setSubTotal($gravadas + $igv)
                ->setMtoImpVenta($gravadas + $igv)
                ->setDetails($items)
                ->setLegends([$this->legend($gravadas + $igv)]);

            return $this->firmarYEnviar($invoice);
        } catch (\Throwable $e) {
            return $this->error($e);
        }
    }

    // ---------- Emitir NOTA de crédito (07) / débito (08) ----------
    public function enviarNota(array $nota, array $detalle, ?array $cliente, array $docAfectado): array
    {
        try {
            $esCredito = $nota['tipo_comprobante'] === 'NOTA_CREDITO';
            $tipoDocAfectado = $docAfectado['tipo_comprobante'] === 'FACTURA' ? '01' : '03';
            $numAfectado = $docAfectado['serie'] . '-' . $docAfectado['correlativo'];

            $items = $this->buildDetails($detalle);
            [$gravadas, $igv] = $this->totales($items);

            $note = (new Note())
                ->setUblVersion('2.1')
                ->setTipoDoc($esCredito ? '07' : '08')
                ->setSerie($nota['serie'])
                ->setCorrelativo((string)$nota['correlativo'])
                ->setFechaEmision(new DateTime($nota['fecha_emision'] ?? 'now'))
                ->setTipDocAfectado($tipoDocAfectado)
                ->setNumDocfectado($numAfectado)
                ->setCodMotivo($nota['cod_motivo'])
                ->setDesMotivo($nota['desc_motivo'])
                ->setTipoMoneda('PEN')
                ->setCompany($this->getCompany())
                ->setClient($this->getClient($cliente, $docAfectado['tipo_comprobante']))
                ->setMtoOperGravadas($gravadas)
                ->setMtoIGV($igv)
                ->setTotalImpuestos($igv)
                ->setMtoImpVenta($gravadas + $igv)
                ->setDetails($items)
                ->setLegends([$this->legend($gravadas + $igv)]);

            return $this->firmarYEnviar($note);
        } catch (\Throwable $e) {
            return $this->error($e);
        }
    }

    /** Firma el documento, lo envía a SUNAT y normaliza la respuesta. */
    private function firmarYEnviar($documento): array
    {
        $see = $this->getSee();
        $this->lastXml = $see->getXmlSigned($documento); // ← firma con el certificado (local)
        $result = $see->send($documento);                // ← envío a SUNAT

        if (!$result->isSuccess()) {
            $err = $result->getError();
            return [
                'success'      => false,
                'http_code'    => 0,
                'response'     => null,
                'message'      => $err ? ($err->getCode() . ' - ' . $err->getMessage()) : 'Error de envío a SUNAT',
                'ticket'       => $documento->getName(),
                'estado_sunat' => 'rechazado',
                'xml'          => $this->lastXml,
            ];
        }

        $cdr = $result->getCdrResponse();
        $aceptado = $cdr && $cdr->getCode() === '0';
        return [
            'success'      => true,
            'http_code'    => 200,
            'response'     => $cdr ? ['code' => $cdr->getCode(), 'description' => $cdr->getDescription()] : null,
            'message'      => $cdr ? $cdr->getDescription() : 'Comprobante aceptado',
            'ticket'       => $documento->getName(),
            'estado_sunat' => $aceptado ? 'aceptado' : 'observado',
            'cdr_zip'      => $result->getCdrZip(),
            'xml'          => $this->lastXml,
        ];
    }

    private function error(\Throwable $e): array
    {
        return [
            'success'      => false,
            'http_code'    => 0,
            'message'      => 'Error del emisor (certificado): ' . $e->getMessage(),
            'estado_sunat' => 'pendiente',
            'xml'          => $this->lastXml,
        ];
    }

    /** Solo genera y firma el XML (sin enviar). Útil para enseñar / verificar. */
    public function generarXmlFirmado(array $venta, array $detalle, ?array $cliente = null): string
    {
        $tipo    = $venta['tipo_comprobante'];
        $tipoDoc = $tipo === 'FACTURA' ? '01' : '03';
        $serie   = $tipo === 'FACTURA' ? 'F001' : 'B001';
        $items = $this->buildDetails($detalle);
        [$gravadas, $igv] = $this->totales($items);
        $invoice = (new Invoice())
            ->setUblVersion('2.1')->setTipoOperacion('0101')->setTipoDoc($tipoDoc)
            ->setSerie($serie)->setCorrelativo((string)$venta['correlativo'])
            ->setFechaEmision(new DateTime($venta['fecha_emision'] ?? 'now'))
            ->setTipoMoneda('PEN')->setCompany($this->getCompany())
            ->setClient($this->getClient($cliente, $tipo))
            ->setMtoOperGravadas($gravadas)->setMtoIGV($igv)->setTotalImpuestos($igv)
            ->setValorVenta($gravadas)->setSubTotal($gravadas + $igv)->setMtoImpVenta($gravadas + $igv)
            ->setDetails($items)->setLegends([$this->legend($gravadas + $igv)]);
        return $this->getSee()->getXmlSigned($invoice);
    }

    // ---------- Guardar resultado en la BD (misma firma que Facturalaya) ----------
    public function actualizarVenta(int $idVenta, array $resultado): void
    {
        $estado  = $resultado['estado_sunat'] ?? 'pendiente';
        $mensaje = $resultado['message'] ?? '';
        $ticket  = $resultado['ticket'] ?? null;
        $cdr = isset($resultado['response']) ? json_encode($resultado['response'], JSON_UNESCAPED_UNICODE) : null;

        // Guardar el XML firmado en uploads
        $xmlFile = null;
        if (!empty($resultado['xml'])) {
            $dir = __DIR__ . '/../uploads/';
            if (!is_dir($dir)) mkdir($dir, 0775, true);
            $nombre = ($ticket ?: ('CPE-' . $idVenta)) . '.xml';
            file_put_contents($dir . $nombre, $resultado['xml']);
            $xmlFile = 'uploads/' . $nombre;
        }

        $stmt = $this->con->prepare(
            "UPDATE ventas SET sunat_ticket = ?, sunat_estado = ?, sunat_mensaje = ?, sunat_cdr = ?, xml_file = ? WHERE id = ?"
        );
        $stmt->bind_param('sssssi', $ticket, $estado, $mensaje, $cdr, $xmlFile, $idVenta);
        $stmt->execute();
    }

    /** Compatibilidad con la interfaz de Facturalaya (aquí no se necesita). */
    public function actualizarDetalleProducto(int $idVenta, array $detalle): void {}

    // ---------- Número a letras (para la leyenda 1000) ----------
    private function numeroALetras(float $numero): string
    {
        $partes = explode('.', number_format($numero, 2, '.', ''));
        $entero = (int)$partes[0];
        $decimal = $partes[1] ?? '00';
        $u = ['', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
        $d = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $c = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];
        if ($entero === 0) $l = 'CERO';
        elseif ($entero === 1) $l = 'UN';
        elseif ($entero < 10) $l = $u[$entero];
        elseif ($entero < 100) $l = $d[intval($entero / 10)] . ($entero % 10 ? ' Y ' . $u[$entero % 10] : '');
        elseif ($entero < 1000) { $r = $entero % 100; $l = ($entero === 100 ? 'CIEN' : $c[intval($entero / 100)]) . ($r ? ' ' . $this->numeroALetras($r) : ''); }
        elseif ($entero < 1000000) { $m = intval($entero / 1000); $r = $entero % 1000; $l = ($m === 1 ? 'MIL' : $this->numeroALetras($m) . ' MIL') . ($r ? ' ' . $this->numeroALetras($r) : ''); }
        else $l = 'VALOR ALTO';
        return "SON {$l} CON {$decimal}/100 SOLES";
    }
}
