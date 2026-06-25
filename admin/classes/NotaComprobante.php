<?php

/**
 * NotaComprobante: emisión de notas de crédito (07) y débito (08).
 *
 * Reutiliza la tabla `ventas` (tipo_comprobante = 'NOTA_CREDITO' | 'NOTA_DEBITO') y
 * agrega de forma idempotente las columnas que referencian al documento afectado y al
 * motivo SUNAT. El envío a SUNAT se delega a Facturalaya->enviarNota().
 */
class NotaComprobante
{
    private $con;

    function __construct()
    {
        date_default_timezone_set('America/Lima');
        include_once("Database.php");
        $db = new Database();
        $this->con = $db->connect();
        $this->ensureColumns();
    }

    /** Agrega las columnas de nota a `ventas` si aún no existen (mismo patrón que Facturalaya). */
    private function ensureColumns(): void
    {
        $cols = [
            'id_doc_afectado'      => "ADD COLUMN id_doc_afectado INT DEFAULT NULL",
            'tipo_doc_afectado'    => "ADD COLUMN tipo_doc_afectado VARCHAR(2) DEFAULT NULL",
            'serie_afectada'       => "ADD COLUMN serie_afectada VARCHAR(10) DEFAULT NULL",
            'correlativo_afectado' => "ADD COLUMN correlativo_afectado INT DEFAULT NULL",
            'cod_motivo'           => "ADD COLUMN cod_motivo VARCHAR(2) DEFAULT NULL",
            'desc_motivo'          => "ADD COLUMN desc_motivo VARCHAR(255) DEFAULT NULL",
        ];
        foreach ($cols as $name => $ddl) {
            $r = $this->con->query("SHOW COLUMNS FROM ventas LIKE '$name'");
            if ($r && $r->num_rows === 0) {
                $this->con->query("ALTER TABLE ventas $ddl");
            }
        }
    }

    /** Catálogo 09 SUNAT: motivos de nota de crédito. */
    public static function motivosCredito(): array
    {
        return [
            '01' => 'Anulación de la operación',
            '02' => 'Anulación por error en el RUC',
            '03' => 'Corrección por error en la descripción',
            '04' => 'Descuento global',
            '05' => 'Descuento por ítem',
            '06' => 'Devolución total',
            '07' => 'Devolución por ítem',
            '08' => 'Bonificación',
            '09' => 'Disminución en el valor',
            '10' => 'Otros conceptos',
        ];
    }

    /** Catálogo 10 SUNAT: motivos de nota de débito. */
    public static function motivosDebito(): array
    {
        return [
            '01' => 'Intereses por mora',
            '02' => 'Aumento en el valor',
            '03' => 'Penalidades / otros conceptos',
        ];
    }

    /** Serie de la nota según el tipo de nota y el tipo de comprobante afectado. */
    private function serieNota(string $tipoNota, string $tipoDocAfectado): string
    {
        $esFactura = $tipoDocAfectado === 'FACTURA';
        if ($tipoNota === 'NOTA_CREDITO') {
            return $esFactura ? 'FC01' : 'BC01';
        }
        return $esFactura ? 'FD01' : 'BD01'; // NOTA_DEBITO
    }

    private function getNextCorrelativo(string $serie): int
    {
        $stmt = $this->con->prepare("SELECT MAX(correlativo) AS ultimo FROM ventas WHERE serie = ?");
        $stmt->bind_param("s", $serie);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return (int)($row['ultimo'] ?? 0) + 1;
    }

    /** Comprobantes (boleta/factura, no anulados) sobre los que se puede emitir una nota. */
    public function getDocumentosAfectables(): array
    {
        $data = [];
        $sql = "SELECT v.id, v.tipo_comprobante, v.serie, v.correlativo, v.total, v.sunat_estado,
                COALESCE(NULLIF(c.razon_social, ''), TRIM(CONCAT_WS(' ', c.nombres, c.apellido_paterno, c.apellido_materno))) AS cliente_nombre
                FROM ventas v
                LEFT JOIN clientes c ON v.id_cliente = c.id
                WHERE v.tipo_comprobante IN ('FACTURA', 'BOLETA') AND v.estado <> 'ANULADO'
                ORDER BY v.id DESC";
        $q = $this->con->query($sql);
        if ($q && $q->num_rows > 0) {
            while ($row = $q->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }

    /** Documento afectado con su detalle (para precargar la nota). */
    public function getDocumentoAfectado(int $id): ?array
    {
        $stmt = $this->con->prepare("SELECT * FROM ventas WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $v = $stmt->get_result()->fetch_assoc();
        if (!$v) return null;

        $sd = $this->con->prepare(
            "SELECT vd.*, p.nombre AS producto_nombre, p.codigo AS producto_codigo
             FROM venta_detalle vd LEFT JOIN productos p ON vd.id_producto = p.id
             WHERE vd.id_venta = ?"
        );
        $sd->bind_param("i", $id);
        $sd->execute();
        $v['detalle'] = $sd->get_result()->fetch_all(MYSQLI_ASSOC);
        return $v;
    }

    /**
     * Crea una nota de crédito/débito, la guarda en `ventas` y la envía a SUNAT.
     * $data: id_doc_afectado, tipo_nota, cod_motivo, desc_motivo, id_usuario, detalle[]
     */
    public function addRegistro(array $data): array
    {
        $idDoc      = (int)($data['id_doc_afectado'] ?? 0);
        $tipoNota   = $data['tipo_nota'] ?? '';
        $codMotivo  = $data['cod_motivo'] ?? '';
        $descMotivo = trim($data['desc_motivo'] ?? '');
        $idUsuario  = (int)($data['id_usuario'] ?? 0) ?: null;
        $detalle    = $data['detalle'] ?? [];

        // Validaciones
        if (!in_array($tipoNota, ['NOTA_CREDITO', 'NOTA_DEBITO'], true)) {
            return ['status' => 303, 'message' => 'Tipo de nota inválido.'];
        }
        if ($idDoc <= 0) {
            return ['status' => 303, 'message' => 'Debe indicar el comprobante afectado.'];
        }
        if ($codMotivo === '') {
            return ['status' => 303, 'message' => 'Debe seleccionar un motivo SUNAT.'];
        }
        if (empty($detalle)) {
            return ['status' => 303, 'message' => 'La nota debe tener al menos un ítem.'];
        }
        $motivosValidos = $tipoNota === 'NOTA_CREDITO' ? self::motivosCredito() : self::motivosDebito();
        if (!isset($motivosValidos[$codMotivo])) {
            return ['status' => 303, 'message' => 'El motivo no corresponde al tipo de nota.'];
        }
        if ($descMotivo === '') {
            $descMotivo = $motivosValidos[$codMotivo];
        }

        $doc = $this->getDocumentoAfectado($idDoc);
        if (!$doc) {
            return ['status' => 303, 'message' => 'No se encontró el comprobante afectado.'];
        }
        if (!in_array($doc['tipo_comprobante'], ['FACTURA', 'BOLETA'], true)) {
            return ['status' => 303, 'message' => 'Solo se puede emitir una nota sobre una factura o boleta.'];
        }
        if ($doc['estado'] === 'ANULADO') {
            return ['status' => 303, 'message' => 'El comprobante ya está anulado.'];
        }

        $serie       = $this->serieNota($tipoNota, $doc['tipo_comprobante']);
        $correlativo = $this->getNextCorrelativo($serie);
        $tipoDocAfectado = $doc['tipo_comprobante'] === 'FACTURA' ? '01' : '03';

        // Totales (mismo criterio que la venta: el precio mostrado es valor de venta sin IGV)
        $subtotal = 0.0;
        foreach ($detalle as $item) {
            $subtotal += (float)$item['subtotal'];
        }
        $subtotal = round($subtotal, 2);
        $igv      = round($subtotal * 0.18, 2);
        $total    = round($subtotal + $igv, 2);

        $fechaEmision = date('Y-m-d H:i:s');
        $metodoPago   = $doc['metodo_pago'] ?? 'CONTADO';
        $idCliente    = $doc['id_cliente'] !== null ? (int)$doc['id_cliente'] : null;
        $estado       = 'COMPLETADO';
        $montoRecibido = 0.0;
        $cambio        = 0.0;

        $this->con->begin_transaction();
        try {
            $stmt = $this->con->prepare(
                "INSERT INTO ventas
                (tipo_comprobante, serie, correlativo, id_cliente, id_usuario, fecha_emision,
                 subtotal, igv, total, metodo_pago, monto_recibido, cambio, estado,
                 id_doc_afectado, tipo_doc_afectado, serie_afectada, correlativo_afectado,
                 cod_motivo, desc_motivo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $correlativoAfectado = (int)$doc['correlativo'];
            $stmt->bind_param(
                "ssiiisdddsddsississ",
                $tipoNota,
                $serie,
                $correlativo,
                $idCliente,
                $idUsuario,
                $fechaEmision,
                $subtotal,
                $igv,
                $total,
                $metodoPago,
                $montoRecibido,
                $cambio,
                $estado,
                $idDoc,
                $tipoDocAfectado,
                $doc['serie'],
                $correlativoAfectado,
                $codMotivo,
                $descMotivo
            );
            if (!$stmt->execute()) {
                throw new Exception("Error al insertar la nota: " . $stmt->error);
            }
            $idNota = $this->con->insert_id;

            $stmt_det = $this->con->prepare(
                "INSERT INTO venta_detalle
                (id_venta, id_producto, codigo_producto, unidad_medida, tipo_operacion,
                 cantidad, precio_unitario, subtotal)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            foreach ($detalle as $item) {
                $idProducto    = (int)($item['id_producto'] ?? 0);
                $codigoProd    = $item['codigo_producto'] ?? null;
                $unidad        = $item['unidad_medida'] ?? 'NIU';
                $tipoOperacion = $item['tipo_operacion'] ?? '10';
                $cantidad      = (float)($item['cantidad'] ?? 1);
                $precio        = (float)($item['precio_unitario'] ?? 0);
                $subLinea      = (float)($item['subtotal'] ?? 0);
                $stmt_det->bind_param(
                    "iisssidd",
                    $idNota,
                    $idProducto,
                    $codigoProd,
                    $unidad,
                    $tipoOperacion,
                    $cantidad,
                    $precio,
                    $subLinea
                );
                if (!$stmt_det->execute()) {
                    throw new Exception("Error al insertar detalle: " . $stmt_det->error);
                }
            }

            $this->con->commit();
        } catch (Exception $e) {
            $this->con->rollback();
            return ['status' => 303, 'message' => $e->getMessage()];
        }

        // Envío a SUNAT (fuera de la transacción)
        try {
            $cliente = null;
            if ($idCliente) {
                $rs = $this->con->query("SELECT * FROM clientes WHERE id = " . $idCliente);
                $cliente = $rs->fetch_assoc();
            }

            $detalleSunat = [];
            foreach ($detalle as $item) {
                $idProducto = (int)($item['id_producto'] ?? 0);
                $prodNombre = $item['producto_nombre'] ?? null;
                if (!$prodNombre && $idProducto) {
                    $pr = $this->con->query("SELECT nombre, codigo FROM productos WHERE id = " . $idProducto);
                    $p = $pr ? $pr->fetch_assoc() : null;
                    $prodNombre = $p['nombre'] ?? 'Producto';
                    $item['codigo_producto'] = $item['codigo_producto'] ?? ($p['codigo'] ?? ('P' . $idProducto));
                }
                $detalleSunat[] = array_merge($item, ['producto_nombre' => $prodNombre ?? 'Producto']);
            }

            $notaData = [
                'tipo_comprobante' => $tipoNota,
                'serie'            => $serie,
                'correlativo'      => $correlativo,
                'fecha_emision'    => date('Y-m-d', strtotime($fechaEmision)),
                'cod_motivo'       => $codMotivo,
                'desc_motivo'      => $descMotivo,
            ];
            $docAfectado = [
                'tipo_comprobante' => $doc['tipo_comprobante'],
                'serie'            => $doc['serie'],
                'correlativo'      => (int)$doc['correlativo'],
            ];

            include_once("EmisorFactory.php");
            $facturalaya = EmisorFactory::crear(); // tercero o certificado, según config_emision.php
            $resultado = $facturalaya->enviarNota($notaData, $detalleSunat, $cliente, $docAfectado);
            $facturalaya->actualizarVenta($idNota, $resultado);

            $tipoTxt = $tipoNota === 'NOTA_CREDITO' ? 'Nota de crédito' : 'Nota de débito';
            return [
                'status'  => 202,
                'message' => $resultado['success']
                    ? "$tipoTxt $serie-" . str_pad((string)$correlativo, 6, '0', STR_PAD_LEFT) . " enviada a SUNAT. Ticket: {$resultado['ticket']}"
                    : "$tipoTxt guardada, pero falló el envío a SUNAT: {$resultado['message']}",
                'id_nota' => $idNota,
                'serie'   => $serie,
                'correlativo' => $correlativo,
                'sunat'   => $resultado,
            ];
        } catch (Exception $e) {
            return [
                'status'  => 202,
                'message' => "Nota #{$idNota} guardada, error al enviar a SUNAT: {$e->getMessage()}",
                'id_nota' => $idNota,
                'sunat'   => ['success' => false, 'message' => $e->getMessage()],
            ];
        }
    }
}

// ----------------------------- Endpoints AJAX -----------------------------

if (isset($_POST['get_motivos'])) {
    echo json_encode([
        'credito' => NotaComprobante::motivosCredito(),
        'debito'  => NotaComprobante::motivosDebito(),
    ]);
    exit();
}

if (isset($_POST['get_documentos_afectables'])) {
    $n = new NotaComprobante();
    echo json_encode($n->getDocumentosAfectables());
    exit();
}

if (isset($_POST['get_documento_afectado'])) {
    $n = new NotaComprobante();
    echo json_encode($n->getDocumentoAfectado((int)$_POST['id']));
    exit();
}

if (isset($_POST['add_nota'])) {
    $detalle = is_array($_POST['detalle'] ?? null) ? $_POST['detalle'] : json_decode($_POST['detalle'] ?? '[]', true);
    $n = new NotaComprobante();
    echo json_encode($n->addRegistro([
        'id_doc_afectado' => $_POST['id_doc_afectado'] ?? 0,
        'tipo_nota'       => $_POST['tipo_nota'] ?? '',
        'cod_motivo'      => $_POST['cod_motivo'] ?? '',
        'desc_motivo'     => $_POST['desc_motivo'] ?? '',
        'id_usuario'      => $_POST['id_usuario'] ?? 0,
        'detalle'         => $detalle,
    ]));
    exit();
}
