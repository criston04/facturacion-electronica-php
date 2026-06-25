<?php

/**
 * ConfigEmision: lee y guarda el MODO DE EMISIÓN de forma persistente.
 *
 * La configuración efectiva = valores por defecto + lo guardado en config_emision.json.
 * Así la pantalla "Modo de Emisión" puede cambiarlo en vivo sin editar código.
 */
class ConfigEmision
{
    private static function rutaJson(): string
    {
        return __DIR__ . '/config_emision.json';
    }

    private static function carpetaCert(): string
    {
        return __DIR__ . '/../certificados/';
    }

    private static function defaults(): array
    {
        return [
            'modo' => 'certificado',
            'sunat' => [
                'entorno'     => 'beta',
                'ruc'         => '20000000001',
                'usuario_sol' => 'MODDATOS',
                'clave_sol'   => 'MODDATOS',
                'cert_nombre' => 'certificado_prueba.pem',
            ],
        ];
    }

    /** Configuración efectiva (incluye la ruta absoluta del certificado para EmisorSunat). */
    public static function get(): array
    {
        $cfg = self::defaults();
        $ruta = self::rutaJson();
        if (file_exists($ruta)) {
            $j = json_decode(file_get_contents($ruta), true);
            if (is_array($j)) {
                if (!empty($j['modo'])) $cfg['modo'] = $j['modo'];
                if (!empty($j['sunat']) && is_array($j['sunat'])) {
                    $cfg['sunat'] = array_merge($cfg['sunat'], $j['sunat']);
                }
            }
        }
        $cfg['sunat']['certificado'] = self::carpetaCert() . ($cfg['sunat']['cert_nombre'] ?? 'certificado_prueba.pem');
        return $cfg;
    }

    /** Guarda la configuración elegida desde la pantalla. */
    public static function guardar(array $data): array
    {
        $modo    = in_array($data['modo'] ?? '', ['tercero', 'certificado'], true) ? $data['modo'] : 'tercero';
        $entorno = in_array($data['entorno'] ?? '', ['beta', 'produccion'], true) ? $data['entorno'] : 'beta';

        $actual = self::get();
        $json = [
            'modo' => $modo,
            'sunat' => [
                'entorno'     => $entorno,
                'ruc'         => preg_replace('/\D/', '', $data['ruc'] ?? $actual['sunat']['ruc']),
                'usuario_sol' => trim($data['usuario_sol'] ?? $actual['sunat']['usuario_sol']),
                'clave_sol'   => $data['clave_sol'] ?? $actual['sunat']['clave_sol'],
                'cert_nombre' => $data['cert_nombre'] ?? ($actual['sunat']['cert_nombre'] ?? 'certificado_prueba.pem'),
            ],
        ];
        $ok = file_put_contents(self::rutaJson(), json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $ok !== false
            ? ['status' => 202, 'message' => 'Configuración guardada correctamente.']
            : ['status' => 303, 'message' => 'No se pudo guardar (¿permisos de escritura?).'];
    }

    /** Estado del certificado actual (existe, CN, vigencia). */
    public static function estadoCertificado(): array
    {
        $cfg = self::get();
        $path = $cfg['sunat']['certificado'];
        if (!file_exists($path)) {
            return ['cargado' => false, 'nombre' => basename($path)];
        }
        $pem = file_get_contents($path);
        $x = @openssl_x509_parse($pem);
        if (!$x) {
            return ['cargado' => true, 'nombre' => basename($path), 'valido' => false, 'cn' => '—'];
        }
        $hasta = $x['validTo_time_t'] ?? 0;
        return [
            'cargado'  => true,
            'valido'   => true,
            'nombre'   => basename($path),
            'cn'       => $x['subject']['CN'] ?? '—',
            'org'      => $x['subject']['O'] ?? '',
            'desde'    => $hasta ? date('d/m/Y', $x['validFrom_time_t'] ?? 0) : '—',
            'hasta'    => $hasta ? date('d/m/Y', $hasta) : '—',
            'vencido'  => $hasta ? ($hasta < time()) : false,
        ];
    }

    /** Envía una BOLETA de prueba a SUNAT con el modo/credenciales actuales. */
    public static function probarEmision(): array
    {
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING);
        try {
            require_once __DIR__ . '/EmisorFactory.php';
            require_once __DIR__ . '/Database.php';
            $db = new Database();
            $con = $db->connect();
            $cli = $con->query("SELECT * FROM clientes WHERE id = 2 LIMIT 1");
            $cliente = $cli ? $cli->fetch_assoc() : null;

            $det = [[
                'id_producto' => 1, 'codigo_producto' => 'P001', 'producto_nombre' => 'Producto de prueba',
                'unidad_medida' => 'NIU', 'cantidad' => 1, 'precio_unitario' => 10.00, 'subtotal' => 10.00,
            ]];
            $venta = [
                'tipo_comprobante' => 'BOLETA',
                'correlativo'      => rand(10000, 99999),
                'fecha_emision'    => date('Y-m-d H:i:s'),
            ];
            $emisor = EmisorFactory::crear();
            $res = $emisor->enviar($venta, $det, $cliente);
            return [
                'status'  => 202,
                'clase'   => get_class($emisor),
                'success' => $res['success'] ?? false,
                'estado'  => $res['estado_sunat'] ?? '-',
                'mensaje' => $res['message'] ?? '-',
                'serie'   => 'B001-' . str_pad((string)$venta['correlativo'], 6, '0', STR_PAD_LEFT),
            ];
        } catch (\Throwable $e) {
            return ['status' => 303, 'success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
        }
    }

    /** Sube un certificado .pem del usuario. */
    public static function subirCertificado(array $file): array
    {
        if (($file['error'] ?? 1) !== UPLOAD_ERR_OK) {
            return ['status' => 303, 'message' => 'No se recibió el archivo.'];
        }
        $pem = file_get_contents($file['tmp_name']);
        if (!@openssl_x509_parse($pem) || !@openssl_pkey_get_private($pem)) {
            return ['status' => 303, 'message' => 'El archivo no es un .pem válido (debe incluir la llave privada y el certificado).'];
        }
        $nombre = 'certificado.pem';
        if (!is_dir(self::carpetaCert())) mkdir(self::carpetaCert(), 0775, true);
        file_put_contents(self::carpetaCert() . $nombre, $pem);

        $actual = self::get();
        self::guardar([
            'modo' => $actual['modo'],
            'entorno' => $actual['sunat']['entorno'],
            'ruc' => $actual['sunat']['ruc'],
            'usuario_sol' => $actual['sunat']['usuario_sol'],
            'clave_sol' => $actual['sunat']['clave_sol'],
            'cert_nombre' => $nombre,
        ]);
        return ['status' => 202, 'message' => 'Certificado cargado correctamente.', 'cert_nombre' => $nombre];
    }

    /** Genera (o regenera) el certificado de prueba para beta. */
    public static function generarCertificadoPrueba(): array
    {
        $dir = self::carpetaCert();
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $cnf = $dir . '_openssl_min.cnf';
        file_put_contents($cnf, "[ req ]\ndistinguished_name = dn\nprompt = no\n[ dn ]\n");
        $config = ['config' => $cnf, 'digest_alg' => 'sha256', 'private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA];
        $dn = ['countryName' => 'PE', 'stateOrProvinceName' => 'Lima', 'localityName' => 'Lima', 'organizationName' => 'EMPRESA DE PRUEBAS SUNAT', 'commonName' => '20000000001'];
        $pk = openssl_pkey_new($config);
        if (!$pk) { @unlink($cnf); return ['status' => 303, 'message' => 'No se pudo generar la llave.']; }
        $csr = openssl_csr_new($dn, $pk, $config);
        $x509 = openssl_csr_sign($csr, null, $pk, 730, $config);
        openssl_x509_export($x509, $certPem);
        openssl_pkey_export($pk, $keyPem, null, $config);
        file_put_contents($dir . 'certificado_prueba.pem', $keyPem . $certPem);
        @unlink($cnf);

        $actual = self::get();
        self::guardar([
            'modo' => $actual['modo'], 'entorno' => $actual['sunat']['entorno'],
            'ruc' => $actual['sunat']['ruc'], 'usuario_sol' => $actual['sunat']['usuario_sol'],
            'clave_sol' => $actual['sunat']['clave_sol'], 'cert_nombre' => 'certificado_prueba.pem',
        ]);
        return ['status' => 202, 'message' => 'Certificado de prueba generado.', 'cert_nombre' => 'certificado_prueba.pem'];
    }
}

// ----------------------------- Endpoints AJAX -----------------------------

if (isset($_POST['get_config'])) {
    $cfg = ConfigEmision::get();
    unset($cfg['sunat']['certificado']); // no exponer rutas del servidor
    echo json_encode(['config' => $cfg, 'certificado' => ConfigEmision::estadoCertificado()]);
    exit();
}
if (isset($_POST['guardar_config'])) {
    echo json_encode(ConfigEmision::guardar($_POST));
    exit();
}
if (isset($_POST['probar_emision'])) {
    echo json_encode(ConfigEmision::probarEmision());
    exit();
}
if (isset($_POST['generar_cert_prueba'])) {
    echo json_encode(ConfigEmision::generarCertificadoPrueba());
    exit();
}
if (isset($_POST['subir_certificado'])) {
    echo json_encode(ConfigEmision::subirCertificado($_FILES['certificado'] ?? []));
    exit();
}
