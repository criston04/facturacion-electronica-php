<?php

class Facturalaya
{
    private mysqli $con;
    private array $emisor;
    private string $apiUrlFactura = 'https://facturalahoy.com/api/facturalaya/factura';
    private string $apiUrlBoleta = 'https://facturalahoy.com/api/boleta';
    // Endpoints de notas/baja/resumen/consulta. Por confirmar con la documentación de
    // facturalahoy.com: si responden 404 el comprobante queda 'pendiente'. // AJUSTAR
    private string $apiUrlNotaCredito = 'https://facturalahoy.com/api/nota-credito';
    private string $apiUrlNotaDebito  = 'https://facturalahoy.com/api/nota-debito';
    private string $apiUrlBaja        = 'https://facturalahoy.com/api/comunicacion-baja';
    private string $apiUrlResumen     = 'https://facturalahoy.com/api/resumen-diario';
    private string $apiUrlConsulta    = 'https://facturalahoy.com/api/consultar-ticket';

    public function __construct()
    {
        date_default_timezone_set('America/Lima');
        require_once __DIR__ . '/Database.php';
        $db = new Database();
        $this->con = $db->connect();
        $this->cargarEmisor();
        $r = $this->con->query("SHOW COLUMNS FROM ventas LIKE 'xml_file'");
        if ($r && $r->num_rows === 0) {
            $this->con->query("ALTER TABLE ventas ADD COLUMN xml_file VARCHAR(255) DEFAULT NULL AFTER sunat_cdr");
        }
    }

    private function cargarEmisor(): void
    {
        $result = $this->con->query("SELECT * FROM emisor WHERE id = 2 LIMIT 1");
        $this->emisor = $result->fetch_assoc() ?? [];
    }

    public function enviar(array $venta, array $detalle, ?array $cliente = null): array
    {
        if (empty($this->emisor)) {
            return ['success' => false, 'message' => 'No hay datos del emisor configurados'];
        }

        $payload = $this->buildPayload($venta, $detalle, $cliente);
        $apiUrl = $venta['tipo_comprobante'] === 'FACTURA' ? $this->apiUrlFactura : $this->apiUrlBoleta;

        $result = $this->post($apiUrl, $payload);
        $result['payload'] = $payload;
        return $result;
    }

    /**
     * Envía una nota de crédito (07) o débito (08) referenciando el comprobante afectado.
     * Reutiliza buildPayload() para ítems/totales/cliente/emisor y añade los campos propios
     * de la nota (tipo de documento, serie, docs_referencia y motivo SUNAT).
     */
    public function enviarNota(array $nota, array $detalle, ?array $cliente, array $docAfectado): array
    {
        if (empty($this->emisor)) {
            return ['success' => false, 'message' => 'No hay datos del emisor configurados'];
        }

        $esCredito = $nota['tipo_comprobante'] === 'NOTA_CREDITO';

        // Base: se calcula igual que el comprobante afectado (mismos ítems y totales).
        $base = [
            'tipo_comprobante' => $docAfectado['tipo_comprobante'], // FACTURA / BOLETA
            'correlativo'      => $nota['correlativo'],
            'fecha_emision'    => $nota['fecha_emision'],
            'serie'            => $nota['serie'],
        ];
        $payload = $this->buildPayload($base, $detalle, $cliente);

        // Campos propios de la nota
        $payload['cod_tipo_documento'] = $esCredito ? '07' : '08';
        $payload['serie_comprobante']  = $nota['serie'];
        $payload['numero_comprobante'] = (int)$nota['correlativo'];
        $payload['docs_referencia']    = [[
            'cod_tipo_documento' => $docAfectado['tipo_comprobante'] === 'FACTURA' ? '01' : '03',
            'serie'  => $docAfectado['serie'],
            'numero' => (int)$docAfectado['correlativo'],
        ]];
        if ($esCredito) {
            $payload['cod_tipo_nota_credito'] = $nota['cod_motivo'];
        } else {
            $payload['cod_tipo_nota_debito'] = $nota['cod_motivo'];
        }
        $payload['motivo_sustento'] = $nota['desc_motivo'];

        // AJUSTAR: endpoint real de notas según la documentación de facturalahoy.com
        $apiUrl = $esCredito ? $this->apiUrlNotaCredito : $this->apiUrlNotaDebito;
        $result = $this->post($apiUrl, $payload);
        $result['payload'] = $payload;
        return $result;
    }

    /**
     * POST JSON genérico a la API con el token del emisor. Devuelve un arreglo normalizado
     * (success/http_code/response/message/ticket/estado_sunat) usado por todos los envíos.
     */
    private function post(string $apiUrl, array $payload): array
    {
        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'token: ' . ($this->emisor['token_cliente'] ?? ''),
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'http_code' => 0, 'message' => 'Error de conexión: ' . $error];
        }

        $data = json_decode($response, true);

        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'response' => $data,
            'message' => $data['message'] ?? ($data['error'] ?? 'Respuesta sin mensaje'),
            'ticket' => $data['ticket'] ?? $data['numero_ticket'] ?? null,
            'estado_sunat' => ($data['respuesta'] ?? '') === 'ok' ? 'aceptado' : ($data['estado_sunat'] ?? ($data['sunat_estado'] ?? 'pendiente')),
        ];
    }

    private function buildPayload(array $venta, array $detalle, ?array $cliente): array
    {
        $em = $this->emisor;
        $tipoDoc = $venta['tipo_comprobante'] === 'FACTURA' ? '01' : ($venta['tipo_comprobante'] === 'BOLETA' ? '03' : '07');

        $serieMap = ['FACTURA' => 'F001', 'BOLETA' => 'B001'];
        $serie = $serieMap[$venta['tipo_comprobante']] ?? 'B001';
        $codMoneda = 'PEN';

        $payload = [
            'token_cliente' => $em['token_cliente'],
            'ruc_proveedor' => $em['ruc_proveedor'],
            'secret_data' => [
                'tipo_certificado' => $em['tipo_certificado'],
                'tipo_proceso' => $em['tipo_proceso'],
                'modalidad_envio_sunat' => $em['modalidad_envio_sunat'],
            ],
            'emisor' => [
                'ruc' => $em['ruc'],
                'tipo_doc' => $em['tipo_doc'],
                'email' => $em['email'],
                'nom_comercial' => $em['nom_comercial'],
                'razon_social' => $em['razon_social'],
                'codigo_ubigeo' => $em['codigo_ubigeo'],
                'direccion' => $em['direccion'],
                'modalidad_envio_sunat' => $em['modalidad_envio_sunat'],
                'direccion_departamento' => $em['direccion_departamento'],
                'direccion_provincia' => $em['direccion_provincia'],
                'direccion_distrito' => $em['direccion_distrito'],
                'direccion_codigopais' => $em['direccion_codigopais'],
            ],
            'tipo_operacion' => '0101',
            'cod_tipo_documento' => $tipoDoc,
            'serie_comprobante' => $serie,
            'numero_comprobante' => (int)$venta['correlativo'],
            'fecha_comprobante' => date('Y-m-d', strtotime($venta['fecha_emision'])),
            'fecha_vto_comprobante' => date('Y-m-d', strtotime($venta['fecha_emision'])),
            'cod_moneda' => $codMoneda,
            'cod_sucursal_sunat' => '0000',
            'forma_de_pago' => 'contado',
            'monto_deuda_total' => 0,
            'detalle_cuotas' => [],
            'docs_referencia' => [],
            'modalidad_envio_sunat' => $em['modalidad_envio_sunat'],
            'nro_otr_comprobante' => '',
            'transporte_nro_placa' => '',
        ];

        $docTipo = $cliente ? (!empty($cliente['tipo_documento']) && $cliente['tipo_documento'] === 'DNI' ? '1' : '6') : ($venta['tipo_comprobante'] === 'BOLETA' ? '1' : '6');
        $docNum = $cliente ? (!empty($cliente['numero_documento']) ? $cliente['numero_documento'] : '00000000') : '00000000';
        $cliNombre = $cliente ? (!empty($cliente['razon_social']) ? $cliente['razon_social'] : 'CLIENTE GENERAL') : 'CLIENTE GENERAL';
        $cliDir = $cliente ? (!empty($cliente['direccion']) ? $cliente['direccion'] : '-') : '-';
        $cliPais = $cliente ? (!empty($cliente['pais']) ? $cliente['pais'] : 'PE') : 'PE';
        $cliUbigeo = $cliente ? (!empty($cliente['codigo_ubigeo']) ? $cliente['codigo_ubigeo'] : '000000') : '000000';
        $cliCiudad = $cliente ? (!empty($cliente['ciudad']) ? $cliente['ciudad'] : '') : '';
        $cliDep = $cliente ? (!empty($cliente['departamento']) ? $cliente['departamento'] : '') : '';
        $cliProv = $cliente ? (!empty($cliente['provincia']) ? $cliente['provincia'] : '') : '';
        $cliDist = $cliente ? (!empty($cliente['distrito']) ? $cliente['distrito'] : '') : '';

        $payload['cliente_tipodocumento'] = $docTipo;
        $payload['cliente_numerodocumento'] = $docNum;
        $payload['cliente_nombre'] = $cliNombre;
        $payload['cliente_direccion'] = $cliDir;
        $payload['cliente_pais'] = $cliPais;
        $payload['cliente_ciudad'] = $cliCiudad;
        $payload['cliente_codigoubigeo'] = $cliUbigeo;
        $payload['cliente_departamento'] = $cliDep;
        $payload['cliente_provincia'] = $cliProv;
        $payload['cliente_distrito'] = $cliDist;

        $items = [];
        $totalGravadas = 0;
        $totalIgv = 0;

        foreach ($detalle as $i => $item) {
            $precioUnitario = (float)$item['precio_unitario'];
            $cantidad = (float)$item['cantidad'];
            $subtotalItem = (float)$item['subtotal'];

            $importe = round($subtotalItem / 1.18, 2);
            $igv = round($subtotalItem - $importe, 2);
            $precioSinIgv = round($precioUnitario / 1.18, 2);

            $totalGravadas += $importe;
            $totalIgv += $igv;

            $items[] = [
                'ITEM_DET' => (string)($i + 1),
                'UNIDAD_MEDIDA_DET' => $item['unidad_medida'] ?? 'NIU',
                'PRECIO_TIPO_CODIGO' => '01',
                'COD_TIPO_OPERACION_DET' => $item['tipo_operacion'] ?? '10',
                'CANTIDAD_DET' => (int)$item['cantidad'],
                'PRECIO_DET' => $precioUnitario,
                'IGV_DET' => $igv,
                'ICBPER_DET' => 0,
                'ISC_DET' => 0,
                'PRECIO_SIN_IGV_DET' => $precioSinIgv,
                'IMPORTE_DET' => $importe,
                'CODIGO_DET' => $item['codigo_producto'] ?? 'P' . $item['id_producto'],
                'DESCRIPCION_DET' => $item['producto_nombre'] ?? 'Producto',
                'DESCUENTO_ITEM' => 'no',
                'PORCENTAJE_DESCUENTO' => 0,
                'MONTO_DESCUENTO' => 0,
                'CODIGO_DESCUENTO' => '00',
            ];
        }

        $payload['detalle'] = $items;
        $payload['total_gravadas'] = round($totalGravadas, 2);
        $payload['total_inafecta'] = 0;
        $payload['total_exoneradas'] = 0;
        $payload['total_gratuitas'] = 0;
        $payload['total_exportacion'] = 0;
        $payload['total_isc'] = 0;
        $payload['total_icbper'] = 0;
        $payload['total_otr_imp'] = 0;
        $payload['total_descuento'] = 0;
        $payload['impuesto_icbper'] = 0.10;
        $payload['porcentaje_igv'] = 18;
        $payload['total_igv'] = round($totalIgv, 2);
        $payload['sub_total'] = round($totalGravadas, 2);
        $payload['total'] = round($totalGravadas + $totalIgv, 2);
        $payload['total_letras'] = $this->numertoLetras(round($totalGravadas + $totalIgv, 2));

        return $payload;
    }

    /**
     * Consulta en SUNAT el estado de un comprobante por su número de ticket.
     * Devuelve el arreglo normalizado de post() (estado_sunat/message/response...).
     */
    public function consultarEstado(string $ticket): array
    {
        if (empty($this->emisor)) {
            return ['success' => false, 'message' => 'No hay datos del emisor configurados'];
        }
        $payload = [
            'token_cliente' => $this->emisor['token_cliente'] ?? '',
            'ruc_proveedor' => $this->emisor['ruc_proveedor'] ?? '',
            'ticket'        => $ticket,
        ];
        return $this->post($this->apiUrlConsulta, $payload); // AJUSTAR endpoint según doc
    }

    /**
     * Comunicación de baja (anulación) de una factura o nota ya aceptada.
     * Las boletas NO se dan de baja por aquí: se anulan vía el resumen diario.
     */
    public function enviarBaja(array $doc, string $motivo): array
    {
        if (empty($this->emisor)) {
            return ['success' => false, 'message' => 'No hay datos del emisor configurados'];
        }
        $codMap = ['FACTURA' => '01', 'NOTA_CREDITO' => '07', 'NOTA_DEBITO' => '08'];
        $payload = [
            'token_cliente'  => $this->emisor['token_cliente'] ?? '',
            'ruc_proveedor'  => $this->emisor['ruc_proveedor'] ?? '',
            'fecha_generacion' => date('Y-m-d'),
            'fecha_comunicacion' => date('Y-m-d'),
            'documentos'     => [[
                'cod_tipo_documento' => $codMap[$doc['tipo_comprobante']] ?? '01',
                'serie'  => $doc['serie'],
                'numero' => (int)$doc['correlativo'],
                'motivo' => $motivo ?: 'Anulación de la operación',
            ]],
        ];
        return $this->post($this->apiUrlBaja, $payload); // AJUSTAR endpoint según doc
    }

    /**
     * Resumen diario de boletas (y sus notas) emitidas en una fecha.
     * $boletas: arreglo de filas de `ventas` (id, serie, correlativo, total, igv, subtotal, estado...).
     */
    public function enviarResumen(string $fecha, array $boletas): array
    {
        if (empty($this->emisor)) {
            return ['success' => false, 'message' => 'No hay datos del emisor configurados'];
        }
        $items = [];
        foreach ($boletas as $i => $b) {
            $items[] = [
                'fila'               => $i + 1,
                'cod_tipo_documento' => '03',
                'serie'              => $b['serie'],
                'numero'             => (int)$b['correlativo'],
                'cod_estado_item'    => ($b['estado'] ?? 'COMPLETADO') === 'ANULADO' ? '3' : '1', // 1=adiciona, 3=anula
                'total'              => round((float)$b['total'], 2),
                'total_gravadas'     => round((float)$b['subtotal'], 2),
                'total_igv'          => round((float)$b['igv'], 2),
            ];
        }
        $payload = [
            'token_cliente'      => $this->emisor['token_cliente'] ?? '',
            'ruc_proveedor'      => $this->emisor['ruc_proveedor'] ?? '',
            'fecha_generacion'   => date('Y-m-d'),
            'fecha_referencia'   => $fecha,
            'cod_moneda'         => 'PEN',
            'detalle'            => $items,
        ];
        return $this->post($this->apiUrlResumen, $payload); // AJUSTAR endpoint según doc
    }

    public function actualizarVenta(int $idVenta, array $resultado): void
    {
        $ticket = $resultado['ticket'] ?? null;
        $estado = $resultado['estado_sunat'] ?? 'pendiente';
        $mensaje = $resultado['message'] ?? '';
        $cdr = isset($resultado['response']) ? json_encode($resultado['response'], JSON_UNESCAPED_UNICODE) : null;

        $xmlFile = $this->guardarArchivoZip($idVenta, $resultado);

        $stmt = $this->con->prepare(
            "UPDATE ventas SET sunat_ticket = ?, sunat_estado = ?, sunat_mensaje = ?, sunat_cdr = ?, xml_file = ? WHERE id = ?"
        );
        $stmt->bind_param('sssssi', $ticket, $estado, $mensaje, $cdr, $xmlFile, $idVenta);
        $stmt->execute();
    }

    private function guardarArchivoZip(int $idVenta, array $resultado): ?string
    {
        $response = $resultado['response'] ?? [];
        $fileZip = $response['file_cpe_zip'] ?? '';
        if (empty($fileZip)) return null;

        $tipoStr = $response['name_file_xml_cpe'] ?? '';
        if (empty($tipoStr)) {
            $tipoStr = "CPE-{$idVenta}.zip";
        }

        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

        $filename = pathinfo($tipoStr, PATHINFO_FILENAME) . '.zip';
        $filepath = $uploadDir . $filename;

        try {
            $data = base64_decode($fileZip, true);
            if ($data === false) return null;
            file_put_contents($filepath, $data);
            return 'uploads/' . $filename;
        } catch (Exception $e) {
            return null;
        }
    }

    public function actualizarDetalleProducto(int $idVenta, array $detalle): void
    {
        $stmt = $this->con->prepare(
            "UPDATE venta_detalle SET codigo_producto = ?, unidad_medida = ?, tipo_operacion = ? WHERE id_venta = ? AND id_producto = ?"
        );
        foreach ($detalle as $item) {
            $codigo = $item['codigo_producto'] ?? $item['codigo'] ?? null;
            $um = $item['unidad_medida'] ?? 'NIU';
            $top = $item['tipo_operacion'] ?? '10';
            $idProd = (int)($item['id_producto'] ?? 0);
            if ($codigo && $idProd > 0) {
                $stmt->bind_param('sssii', $codigo, $um, $top, $idVenta, $idProd);
                $stmt->execute();
            }
        }
    }

    private function numertoLetras(float $numero): string
    {
        $partes = explode('.', number_format($numero, 2, '.', ''));
        $entero = (int)$partes[0];
        $decimal = $partes[1] ?? '00';

        $unidades = ['', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
        $decenas = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

        if ($entero === 0) {
            $letras = 'CERO';
        } elseif ($entero === 1) {
            $letras = 'UN';
        } elseif ($entero < 10) {
            $letras = $unidades[$entero];
        } elseif ($entero < 30) {
            $mapa = [10 => 'DIEZ', 11 => 'ONCE', 12 => 'DOCE', 13 => 'TRECE', 14 => 'CATORCE', 15 => 'QUINCE', 16 => 'DIECISÉIS', 17 => 'DIECISIETE', 18 => 'DIECIOCHO', 19 => 'DIECINUEVE', 20 => 'VEINTE', 21 => 'VEINTIUNO', 22 => 'VEINTIDÓS', 23 => 'VEINTITRÉS', 24 => 'VEINTICUATRO', 25 => 'VEINTICINCO', 26 => 'VEINTISÉIS', 27 => 'VEINTISIETE', 28 => 'VEINTIOCHO', 29 => 'VEINTINUEVE'];
            $letras = $mapa[$entero] ?? $decenas[intval($entero / 10)] . ($entero % 10 > 0 ? ' Y ' . $unidades[$entero % 10] : '');
        } elseif ($entero < 100) {
            $letras = $decenas[intval($entero / 10)] . ($entero % 10 > 0 ? ' Y ' . $unidades[$entero % 10] : '');
        } elseif ($entero < 1000) {
            $c = intval($entero / 100);
            $r = $entero % 100;
            $letras = ($c === 1 && $r === 0 ? 'CIEN' : $centenas[$c]) . ($r > 0 ? ' ' . $this->numertoLetras($r) : '');
        } elseif ($entero < 1000000) {
            $m = intval($entero / 1000);
            $r = $entero % 1000;
            $letras = ($m === 1 ? 'MIL' : $this->numertoLetras($m) . ' MIL') . ($r > 0 ? ' ' . $this->numertoLetras($r) : '');
        } elseif ($entero < 1000000000) {
            $m = intval($entero / 1000000);
            $r = $entero % 1000000;
            $letras = ($m === 1 ? 'UN MILLÓN' : $this->numertoLetras($m) . ' MILLONES') . ($r > 0 ? ' ' . $this->numertoLetras($r) : '');
        } else {
            $letras = 'VALOR MUY ALTO';
        }

        return "SON {$letras} CON {$decimal}/100";
    }
}
