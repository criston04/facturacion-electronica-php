<?php


class Usuario
{

	private $con;

	function __construct()
	{
		include_once("Database.php");
		$db = new Database();
		$this->con = $db->connect();
	}

	public function addRegistro($idusuarios, $nombres, $apellidos, $usuario, $clave, $enum_rol)
	{

		if ($idusuarios == 0) {
			$q = $this->con->query("SELECT * FROM usuarios WHERE usuario = '$usuario' LIMIT 1");
			if ($q->num_rows > 0) {
				return ['status' => 303, 'message' => 'ya existe un registro'];
			}
			$hash = password_hash($clave, PASSWORD_DEFAULT);
			$stmt =  $this->con->prepare("INSERT INTO usuarios 
            (nombres, apellidos, usuario, clave, enum_rol, estado)
            VALUES (?, ?, ?, ?, ?, 1)");
			$stmt->bind_param(
				"ssssi",
				$nombres,
				$apellidos,
				$usuario,
				$hash,
				$enum_rol
			);
			if ($stmt->execute()) {

				return ['status' => 202, 'message' => 'Cuenta creada correctamente.'];
			} else {
				return ['status' => 303, 'message' => 'Error al registrar usuario'];
			}
		} else {

			$q = $this->con->query("UPDATE usuarios
			 SET nombres= '$nombres',
			 apellidos= '$apellidos'
			 WHERE idusuarios = '$idusuarios'");
			if ($q) {
				return ['status' => 202, 'message' => 'Registro modificado correctamente'];
			} else {
				return ['status' => 303, 'message' => 'No se podido modificar el registro'];
			}
		}
	}


	public function deleteRegistro($cid = null)
	{
		if ($cid != null) {
			
			$q = $this->con->query("DELETE FROM usuarios WHERE idusuarios = '$cid'")  or die($this->con->error);
			if ($q) {
				return ['status' => 202, 'message' => 'El registro se elimino correctamente'];
			} else {
				return ['status' => 202, 'message' => 'No se ha podido eliminar el registro'];
			}
		} else {
			return ['status' => 303, 'message' => 'ID de area inválido'];
		}
	}

	public function getRoles()
	{
		$roles = [];
		$q = $this->con->query("SELECT valor, nombre FROM enumerados where tipo=1 ");
		if ($q->num_rows > 0) {
			while ($row = $q->fetch_assoc()) {
				$roles[] = $row;
			}
			$_DATA['roles'] = $roles;
		}
		return ['status' => 202, 'message' => $_DATA];
	}
	public function updateClave($idusuario, $clave)
	{

		$sql = "UPDATE usuario SET clave=? WHERE idusuario=?";
		$stmt = $this->con->prepare($sql);
		$stmt->bind_param("si", $clave, $idusuario);

		if ($stmt->execute()) {
			return [
				"status" => 202,
				"message" => "Contraseña actualizada correctamente"
			];
		} else {
			return [
				"status" => 303,
				"message" => "Error al actualizar la contraseña"
			];
		}
	}
}

if (isset($_POST['add_update'])) {
	$idusuarios = $_POST['idusuarios'];
	$nombres = $_POST['nombres'];
	$apellidos = $_POST['apellidos'];
	$usuario = $_POST['usuario'];
	$clave = $_POST['clave'];
	$enum_rol = $_POST['enum_rol'];

	$p = new Usuario();
	echo json_encode($p->addRegistro($idusuarios, $nombres, $apellidos, $usuario, $clave, $enum_rol));
}



if (isset($_POST['eliminar_registro'])) {
	if (!empty($_POST['cid'])) {
		$p = new Usuario();
		echo json_encode($p->deleteRegistro($_POST['cid']));
		exit();
	} else {
		echo json_encode(['status' => 303, 'message' => 'ID de usuario inválido']);
		exit();
	}
}

if (isset($_POST['GET_ROLES'])) {
	$p = new Usuario();
	echo json_encode($p->getRoles());
	exit();
}
if (isset($_POST['update_clave'])) {

	$idusuario  = $_POST['id'];
	$newclave   = $_POST['newclave'];
	$confclave  = $_POST['confclave'];

	// 🔴 Validar que sean iguales
	if ($newclave !== $confclave) {
		echo json_encode([
			"status" => 303,
			"message" => "Las contraseñas no coinciden"
		]);
		exit;
	}

	// 🔐 Encriptar contraseña
	$hash = password_hash($newclave, PASSWORD_BCRYPT);

	$p = new Usuario();
	echo json_encode($p->updateClave($idusuario, $hash));
}
