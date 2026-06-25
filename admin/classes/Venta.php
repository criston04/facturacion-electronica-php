<?php

class Venta
{
    private $con;

    function __construct()
    {
        date_default_timezone_set('America/Lima');
        include_once("Database.php");
        $db = new Database();
        $this->con = $db->connect();
    }

    public function getAll($search = '')
    {
        $data = [];
        $where = '';
        if ($search) {
            $s = $this->con->real_escape_string($search);
            $where = "WHERE v.serie LIKE '%$s%' OR v.correlativo LIKE '%$s%'
                OR c.razon_social LIKE '%$s%' OR c.nombres LIKE '%$s%'
                OR c.numero_documento LIKE '%$s%'";
        }
        $sql = "SELECT v.*,
                COALESCE(NULLIF(c.razon_social, ''), TRIM(CONCAT_WS(' ', c.nombres, c.apellido_paterno, c.apellido_materno))) AS cliente_nombre,
                c.numero_documento AS cliente_doc
                FROM ventas v
                LEFT JOIN clientes c ON v.id_cliente = c.id
                $where
                ORDER BY v.id DESC";
        $q = $this->con->query($sql);
        if ($q && $q->num_rows > 0) {
            while ($row = $q->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function getById($id)
    {
        $sql = "SELECT v.*,
                COALESCE(NULLIF(c.razon_social, ''), TRIM(CONCAT_WS(' ', c.nombres, c.apellido_paterno, c.apellido_materno))) AS cliente_nombre,
                c.numero_documento AS cliente_doc,
                c.tipo_documento AS cliente_tipo_doc,
                c.direccion AS cliente_direccion,
                c.codigo_ubigeo AS cliente_ubigeo,
                c.pais AS cliente_pais,
                c.ciudad AS cliente_ciudad,
                c.departamento AS cliente_departamento,
                c.provincia AS cliente_provincia,
                c.distrito AS cliente_distrito
                FROM ventas v
                LEFT JOIN clientes c ON v.id_cliente = c.id
                WHERE v.id = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $r = $stmt->get_result();
        $cabecera = $r->fetch_assoc();
        if (!$cabecera) return null;

        $sql_det = "SELECT vd.*, p.nombre AS producto_nombre, p.codigo AS producto_codigo
                    FROM venta_detalle vd
                    LEFT JOIN productos p ON vd.id_producto = p.id
                    WHERE vd.id_venta = ?";
        $stmt_det = $this->con->prepare($sql_det);
        $stmt_det->bind_param("i", $id);
        $stmt_det->execute();
        $r_det = $stmt_det->get_result();
        $detalle = [];
        while ($row = $r_det->fetch_assoc()) {
            $detalle[] = $row;
        }
        $cabecera['detalle'] = $detalle;
        return $cabecera;
    }

    public function getNextCorrelativo($tipo)
    {
        $serie = $tipo === 'FACTURA' ? 'F001' : 'B001';
        $stmt = $this->con->prepare("SELECT MAX(correlativo) AS ultimo FROM ventas WHERE serie = ?");
        $stmt->bind_param("s", $serie);
        $stmt->execute();
        $r = $stmt->get_result();
        $row = $r->fetch_assoc();
        return ($row['ultimo'] ?? 0) + 1;
    }

    public function addRegistro($cabecera, $detalle)
    {
        $this->con->begin_transaction();

        try {
            if (empty($cabecera['fecha_emision'])) {
                $cabecera['fecha_emision'] = date('Y-m-d H:i:s');
            }
            $tipo = $cabecera['tipo_comprobante'];
            $serie = $tipo === 'FACTURA' ? 'F001' : 'B001';
            $correlativo = $this->getNextCorrelativo($tipo);

            $stmt = $this->con->prepare(
                "INSERT INTO ventas (tipo_comprobante, serie, correlativo, id_cliente, id_usuario,
                fecha_emision, subtotal, igv, total, metodo_pago, monto_recibido, cambio, estado)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'COMPLETADO')"
            );
            $stmt->bind_param(
                "ssiiissddsdd",
                $tipo,
                $serie,
                $correlativo,
                $cabecera['id_cliente'],
                $cabecera['id_usuario'],
                $cabecera['fecha_emision'],
                $cabecera['subtotal'],
                $cabecera['igv'],
                $cabecera['total'],
                $cabecera['metodo_pago'],
                $cabecera['monto_recibido'],
                $cabecera['cambio']
            );
            if (!$stmt->execute()) {
                throw new Exception("Error al insertar venta: " . $stmt->error);
            }

            $idVenta = $this->con->insert_id;

            $stmt_det = $this->con->prepare(
                "INSERT INTO venta_detalle (id_venta, id_producto, codigo_producto, unidad_medida,
                tipo_operacion, cantidad, precio_unitario, subtotal)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );

            foreach ($detalle as $item) {
                $stmt_det->bind_param(
                    "iisssidd",
                    $idVenta,
                    $item['id_producto'],
                    $item['codigo_producto'],
                    $item['unidad_medida'],
                    $item['tipo_operacion'],
                    $item['cantidad'],
                    $item['precio_unitario'],
                    $item['subtotal']
                );
                if (!$stmt_det->execute()) {
                    throw new Exception("Error al insertar detalle: " . $stmt_det->error);
                }
            }

            // COMMIT antes de llamar a la API externa (evita lock timeout)
            $this->con->commit();

        } catch (Exception $e) {
            $this->con->rollback();
            return ['status' => 303, 'message' => $e->getMessage()];
        }

        // Llamar a Facturalaya API fuera de la transacción
        try {
            $cliente = null;
            if (!empty($cabecera['id_cliente'])) {
                $rs = $this->con->query("SELECT * FROM clientes WHERE id = {$cabecera['id_cliente']}");
                $cliente = $rs->fetch_assoc();
            }

            $fechaDate = explode(' ', $cabecera['fecha_emision'])[0];
            if (!strtotime($fechaDate)) {
                $fechaDate = date('Y-m-d');
            }
            $ventaData = [
                'tipo_comprobante' => $tipo,
                'correlativo' => $correlativo,
                'fecha_emision' => $fechaDate,
                'serie' => $serie,
            ];

            $detalleSunat = [];
            foreach ($detalle as $item) {
                $prodR = $this->con->query("SELECT nombre, codigo FROM productos WHERE id = {$item['id_producto']}");
                $prod = $prodR->fetch_assoc();
                $detalleSunat[] = array_merge($item, [
                    'producto_nombre' => $prod['nombre'] ?? 'Producto',
                    'codigo_producto' => $item['codigo_producto'] ?? $prod['codigo'] ?? ('P' . $item['id_producto']),
                ]);
            }

            include_once("EmisorFactory.php");
            $facturalaya = EmisorFactory::crear(); // tercero o certificado, según config_emision.php
            $resultado = $facturalaya->enviar($ventaData, $detalleSunat, $cliente);

            $facturalaya->actualizarVenta($idVenta, $resultado);
            $facturalaya->actualizarDetalleProducto($idVenta, $detalleSunat);

            return [
                'status' => 202,
                'message' => $resultado['success']
                    ? "Venta generada y enviada a SUNAT. Ticket: {$resultado['ticket']}"
                    : "Venta guardada pero falló envío a SUNAT: {$resultado['message']}",
                'id_venta' => $idVenta,
                'sunat' => $resultado,
            ];

        } catch (Exception $e) {
            return [
                'status' => 202,
                'message' => "Venta #{$idVenta} guardada, error al enviar a SUNAT: {$e->getMessage()}",
                'id_venta' => $idVenta,
                'sunat' => ['success' => false, 'message' => $e->getMessage()],
            ];
        }
    }

    public function deleteRegistro($id, $motivo = null)
    {
        // Se carga el comprobante: la baja ante SUNAT difiere según el tipo.
        $stmt = $this->con->prepare("SELECT tipo_comprobante, serie, correlativo, sunat_ticket FROM ventas WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $doc = $stmt->get_result()->fetch_assoc();
        if (!$doc) {
            return ['status' => 303, 'message' => 'Comprobante no encontrado'];
        }

        $motivoTxt = $motivo ?: 'Anulación de la operación';
        $sunatEstado = 'baja';
        $sunatMsg = null;

        // Facturas y notas se anulan con comunicación de baja; las boletas se anulan
        // informándolas en el resumen diario (no por comunicación de baja).
        if (in_array($doc['tipo_comprobante'], ['FACTURA', 'NOTA_CREDITO', 'NOTA_DEBITO'], true)) {
            include_once("Facturalaya.php");
            $f = new Facturalaya();
            $res = $f->enviarBaja($doc, $motivoTxt);
            $sunatEstado = $res['success'] ? 'baja' : 'pendiente';
            $sunatMsg = $res['message'] ?? null;
        } else {
            $sunatMsg = 'Boleta anulada localmente. Debe informarse en el resumen diario.';
        }

        $stmt2 = $this->con->prepare(
            "UPDATE ventas SET estado = 'ANULADO', sunat_estado = ?,
             sunat_mensaje = COALESCE(?, sunat_mensaje), descripcion_motivo = ? WHERE id = ?"
        );
        $stmt2->bind_param("sssi", $sunatEstado, $sunatMsg, $motivoTxt, $id);
        if ($stmt2->execute()) {
            return ['status' => 202, 'message' => 'Comprobante anulado. ' . ($sunatMsg ?? '')];
        }
        return ['status' => 303, 'message' => 'No se pudo anular el comprobante'];
    }

    /** Reconsulta a SUNAT el estado del comprobante por su ticket y actualiza la BD. */
    public function consultarEstadoSunat($id)
    {
        $stmt = $this->con->prepare("SELECT sunat_ticket FROM ventas WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) {
            return ['status' => 303, 'message' => 'Comprobante no encontrado.'];
        }
        if (empty($row['sunat_ticket'])) {
            return ['status' => 303, 'message' => 'El comprobante no tiene ticket SUNAT para consultar.'];
        }

        include_once("Facturalaya.php");
        $f = new Facturalaya();
        $res = $f->consultarEstado($row['sunat_ticket']);

        // Actualiza solo el estado/mensaje/CDR (sin tocar el ticket)
        $estado = $res['estado_sunat'] ?? 'pendiente';
        $mensaje = $res['message'] ?? '';
        $cdr = isset($res['response']) ? json_encode($res['response'], JSON_UNESCAPED_UNICODE) : null;
        $up = $this->con->prepare("UPDATE ventas SET sunat_estado = ?, sunat_mensaje = ?, sunat_cdr = ? WHERE id = ?");
        $up->bind_param("sssi", $estado, $mensaje, $cdr, $id);
        $up->execute();

        return [
            'status' => 202,
            'message' => "Estado SUNAT actualizado: " . strtoupper($estado),
            'estado_sunat' => $estado,
            'sunat' => $res,
        ];
    }
}

if (isset($_POST['add_venta'])) {
    $detalle_array = is_array($_POST['detalle']) ? $_POST['detalle'] : json_decode($_POST['detalle'], true);
    $cabecera = $_POST['cabecera'];
    $venta = new Venta();
    echo json_encode($venta->addRegistro($cabecera, $detalle_array));
    exit();
}

if (isset($_POST['get_all_ventas'])) {
    $venta = new Venta();
    $search = $_POST['search'] ?? '';
    echo json_encode($venta->getAll($search));
    exit();
}

if (isset($_POST['get_venta_by_id'])) {
    $venta = new Venta();
    echo json_encode($venta->getById($_POST['id_venta']));
    exit();
}

if (isset($_POST['eliminar_venta'])) {
    if (!empty($_POST['id'])) {
        $venta = new Venta();
        $motivo = isset($_POST['motivo_anulacion']) ? $_POST['motivo_anulacion'] : null;
        echo json_encode($venta->deleteRegistro($_POST['id'], $motivo));
        exit();
    }
}

if (isset($_POST['get_next_correlativo'])) {
    $venta = new Venta();
    $tipo = $_POST['tipo_comprobante'] ?? 'BOLETA';
    echo json_encode(['correlativo' => $venta->getNextCorrelativo($tipo)]);
    exit();
}

if (isset($_POST['consultar_estado_sunat'])) {
    if (!empty($_POST['id'])) {
        $venta = new Venta();
        echo json_encode($venta->consultarEstadoSunat($_POST['id']));
        exit();
    }
}
