<?php

class Factura
{
	private $con;

	function __construct()
	{
		include_once("Database.php");
		$db = new Database();
		$this->con = $db->connect();
	}

	public function getAllFacturas()
	{
		$data = [];
		$sql = "SELECT fc.*, 
				COALESCE(NULLIF(c.razon_social, ''), TRIM(CONCAT_WS(' ', c.nombres, c.apellido_paterno, c.apellido_materno))) as cliente_nombre,
				c.numero_documento as cliente_doc,
				tc.nombre as tipo_comprobante_nombre,
				m.nombre as moneda_nombre,
				tm.nombre as metodo_pago_nombre,
				te.nombre as estado_nombre,
				e.nombre_razon_social as empresa_nombre
				FROM factura_cabecera fc
				LEFT JOIN clientes c ON fc.id_cliente = c.id
				LEFT JOIN tipo_comprobante tc ON fc.id_tipo_comp = tc.id_tipo_comp
				LEFT JOIN moneda m ON fc.id_moneda = m.id_moneda
				LEFT JOIN tipo_metodo_pago tm ON fc.id_metodo_pago = tm.id_metodo_pago
				LEFT JOIN tipo_estado te ON fc.id_estado = te.id_estado
				LEFT JOIN empresa e ON fc.id_empresa = e.id_empresa
				ORDER BY fc.id_factura DESC";
		$q = $this->con->query($sql);
		if ($q && $q->num_rows > 0) {
			while ($row = $q->fetch_assoc()) {
				$data[] = $row;
			}
		}
		return $data;
	}

	public function getFacturaById($id)
	{
		$sql = "SELECT fc.*, 
				COALESCE(NULLIF(c.razon_social, ''), TRIM(CONCAT_WS(' ', c.nombres, c.apellido_paterno, c.apellido_materno))) as cliente_nombre,
				c.numero_documento as cliente_doc,
				c.tipo_documento as id_tipo_doc,
				c.direccion as cliente_direccion,
				tc.nombre as tipo_comprobante_nombre,
				m.nombre as moneda_nombre,
				m.simbolo as moneda_simbolo,
				tm.nombre as metodo_pago_nombre,
				te.nombre as estado_nombre,
				e.nombre_razon_social as empresa_nombre,
				e.ruc as empresa_ruc
				FROM factura_cabecera fc
				LEFT JOIN clientes c ON fc.id_cliente = c.id
				LEFT JOIN tipo_comprobante tc ON fc.id_tipo_comp = tc.id_tipo_comp
				LEFT JOIN moneda m ON fc.id_moneda = m.id_moneda
				LEFT JOIN tipo_metodo_pago tm ON fc.id_metodo_pago = tm.id_metodo_pago
				LEFT JOIN tipo_estado te ON fc.id_estado = te.id_estado
				LEFT JOIN empresa e ON fc.id_empresa = e.id_empresa
				WHERE fc.id_factura = ?";
		$stmt = $this->con->prepare($sql);
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$r = $stmt->get_result();
		$cabecera = $r->fetch_assoc();
		if (!$cabecera) return null;

		$sql_det = "SELECT fd.*, ps.descripcion as producto_nombre, ps.codigo_producto
					FROM factura_detalle fd
					LEFT JOIN producto_servicio ps ON fd.id_producto = ps.id_producto
					WHERE fd.id_factura = ?";
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

	public function addRegistro($cabecera, $detalle)
	{
		$this->con->begin_transaction();

		try {
			$fecha_vencimiento = !empty($cabecera['fecha_vencimiento']) ? $cabecera['fecha_vencimiento'] : null;
			$observacion = !empty($cabecera['observacion']) ? $cabecera['observacion'] : null;

			$sql_cab = "INSERT INTO factura_cabecera 
                (serie, numero_factura, fecha_emision, hora_emision, fecha_vencimiento, 
                id_empresa, id_cliente, id_tipo_comp, id_moneda, tipo_cambio, 
                id_metodo_pago, subtotal, total_impuestos, valor_venta, total, 
                id_estado, observacion) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

			$stmt = $this->con->prepare($sql_cab);

			$stmt->bind_param(
				"sssssiiiididdddis",
				$cabecera['serie'],
				$cabecera['numero_factura'],
				$cabecera['fecha_emision'],
				$cabecera['hora_emision'],
				$fecha_vencimiento,
				$cabecera['id_empresa'],
				$cabecera['id_cliente'],
				$cabecera['id_tipo_comp'],
				$cabecera['id_moneda'],
				$cabecera['tipo_cambio'],
				$cabecera['id_metodo_pago'],
				$cabecera['subtotal'],
				$cabecera['total_impuestos'],
				$cabecera['valor_venta'],
				$cabecera['total'],
				$cabecera['id_estado'],
				$observacion
			);

			if (!$stmt->execute()) {
				throw new Exception("Error al insertar cabecera: " . $stmt->error);
			}

			$id_factura = $this->con->insert_id;

			$sql_det = "INSERT INTO factura_detalle 
                (id_factura, id_producto, cantidad, precio_unitario, valor_venta, 
                valor_impuesto, total_linea) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

			$stmt_det = $this->con->prepare($sql_det);

			foreach ($detalle as $item) {
				$stmt_det->bind_param(
					"iiddddd",
					$id_factura,
					$item['id_producto'],
					$item['cantidad'],
					$item['precio_unitario'],
					$item['valor_venta'],
					$item['valor_impuesto'],
					$item['total_linea']
				);

				if (!$stmt_det->execute()) {
					throw new Exception("Error al insertar detalle: " . $stmt_det->error);
				}
			}

			$this->con->commit();
			return ['status' => 202, 'message' => 'Factura generada con éxito.', 'id_factura' => $id_factura];

		} catch (Exception $e) {
			$this->con->rollback();
			return ['status' => 303, 'message' => $e->getMessage()];
		}
	}

	public function deleteRegistro($cid, $motivo = null)
	{
		$motivo = $motivo ? "'" . $this->con->real_escape_string($motivo) . "'" : "NULL";
		$sql = "UPDATE factura_cabecera SET id_estado = 2, motivo_anulacion = $motivo WHERE id_factura = '$cid'";
		$q = $this->con->query($sql);
		if ($q) {
			return ['status' => 202, 'message' => 'Factura anulada correctamente'];
		} else {
			return ['status' => 303, 'message' => 'No se pudo anular la factura'];
		}
	}
}

if (isset($_POST['add_factura_completa'])) {
	$detalle_array = is_array($_POST['detalle']) ? $_POST['detalle'] : json_decode($_POST['detalle'], true);
	$cabecera = $_POST['cabecera'];
	$factura = new Factura();
	echo json_encode($factura->addRegistro($cabecera, $detalle_array));
}

if (isset($_POST['get_all_facturas'])) {
	$factura = new Factura();
	echo json_encode($factura->getAllFacturas());
	exit();
}

if (isset($_POST['get_factura_by_id'])) {
	$factura = new Factura();
	echo json_encode($factura->getFacturaById($_POST['id_factura']));
	exit();
}

if (isset($_POST['eliminar_registro'])) {
	if (!empty($_POST['cid'])) {
		$factura = new Factura();
		$motivo = isset($_POST['motivo_anulacion']) ? $_POST['motivo_anulacion'] : null;
		echo json_encode($factura->deleteRegistro($_POST['cid'], $motivo));
		exit();
	}
}
