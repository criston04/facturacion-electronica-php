<?php


class Empresa
{

	private $con;

	function __construct()
	{
		include_once("Database.php");
		$db = new Database();
		$this->con = $db->connect();
	}

	public function addRegistro($id_empresa, $ruc, $nombre_razon_social, $nombre_comercial,$direccion_fiscal, $codigo_establecimiento, $codigo_punto_emision)
	{

		if ($id_empresa == 0) {
			$q = $this->con->query("SELECT count(*) FROM empresa  LIMIT 1");
			if ($q->num_rows > 0) {
				return ['status' => 303, 'message' => 'ya existe un registro'];
			}
			
		
			
			$stmt =  $this->con->prepare("INSERT INTO empresa 
            (ruc, nombre_razon_social, nombre_comercial, direccion_fiscal, codigo_establecimiento, codigo_punto_emision, activo)
            VALUES (?, ?, ?, ?, ?, ?, 1)");
			$stmt->bind_param(
				"ssssss",
				$ruc,
				$nombre_razon_social,
				$nombre_comercial,
				$direccion_fiscal,
				$codigo_establecimiento,
				$codigo_punto_emision
			);
			if ($stmt->execute()) {

				return ['status' => 202, 'message' => 'Se registró correctamente.'];
			} else {
				return ['status' => 303, 'message' => 'Error al registrar producto'];
			}
		} else {

			$q = $this->con->query("UPDATE empresa
			 SET ruc= '$ruc',
			 nombre_razon_social= '$nombre_razon_social',
			 nombre_comercial= '$nombre_comercial',
			 direccion_fiscal= '$direccion_fiscal',
			 codigo_establecimiento= '$codigo_establecimiento',
			 codigo_punto_emision= '$codigo_punto_emision'
			 WHERE id_empresa = '$id_empresa'");
			if ($q) {
				return ['status' => 202, 'message' => 'Registro modificado correctamente'];
			} else {
				return ['status' => 303, 'message' => 'No se podido modificar el registro'];
			}
		}
	}


	public function getAllEmpresas()
	{
		$data = [];
		$q = $this->con->query("SELECT id_empresa, ruc, nombre_razon_social, nombre_comercial FROM empresa WHERE activo = 1");
		if ($q && $q->num_rows > 0) {
			while ($row = $q->fetch_assoc()) {
				$data[] = $row;
			}
		}
		return $data;
	}

	public function deleteRegistro($cid = null)
	{
		if ($cid != null) {
			
			$q = $this->con->query("DELETE FROM empresa WHERE id_empresa = '$cid'")  or die($this->con->error);
			if ($q) {
				return ['status' => 202, 'message' => 'El registro se elimino correctamente'];
			} else {
				return ['status' => 202, 'message' => 'No se ha podido eliminar el registro'];
			}
		} else {
			return ['status' => 303, 'message' => 'ID de area inválido'];
		}
	}

	

}

if (isset($_POST['add_update'])) {
	$id_empresa = $_POST['id_empresa'];
	$ruc = $_POST['ruc'];
	$nombre_razon_social = $_POST['nombre_razon_social'];
	$nombre_comercial = $_POST['nombre_comercial'];
	$direccion_fiscal = $_POST['direccion_fiscal'];
	$codigo_establecimiento = $_POST['codigo_establecimiento'];
	$codigo_punto_emision = $_POST['codigo_punto_emision'];
	

	$p = new Empresa();
	echo json_encode($p->addRegistro($id_empresa, $ruc, $nombre_razon_social,$nombre_comercial, $direccion_fiscal, $codigo_establecimiento, $codigo_punto_emision));
}
