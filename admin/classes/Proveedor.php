<?php

class Proveedor
{
    private $con;

    function __construct()
    {
        include_once("Database.php");
        $db = new Database();
        $this->con = $db->connect();
    }

    public function addRegistro($id, $empresa, $nombre_comercial, $condicion, $estado_ruc, $tipo, $inscripcion, $codigo_ubigeo, $sistema_emision, $actividad_exterior, $sistema_contabilidad, $emision_electronica, $ple, $respuesta_api, $ruc, $contacto, $telefono, $email, $direccion, $estado)
    {
        if ($id == 0) {
            $stmt = $this->con->prepare("INSERT INTO proveedores 
                (empresa, nombre_comercial, condicion, estado_ruc, tipo, inscripcion, codigo_ubigeo, sistema_emision, actividad_exterior, sistema_contabilidad, emision_electronica, ple, respuesta_api, ruc, contacto, telefono, email, direccion, estado)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param(
                "sssssssssssssssssss",
                $empresa, $nombre_comercial, $condicion, $estado_ruc, $tipo, $inscripcion, $codigo_ubigeo, $sistema_emision, $actividad_exterior, $sistema_contabilidad, $emision_electronica, $ple, $respuesta_api, $ruc, $contacto, $telefono, $email, $direccion, $estado
            );
            if ($stmt->execute()) {
                return ['status' => 202, 'message' => 'Proveedor registrado correctamente.'];
            } else {
                return ['status' => 303, 'message' => 'Error al registrar proveedor.'];
            }
        } else {
            $q = $this->con->query("UPDATE proveedores SET
                empresa = '$empresa',
                nombre_comercial = '$nombre_comercial',
                condicion = '$condicion',
                estado_ruc = '$estado_ruc',
                tipo = '$tipo',
                inscripcion = '$inscripcion',
                codigo_ubigeo = '$codigo_ubigeo',
                sistema_emision = '$sistema_emision',
                actividad_exterior = '$actividad_exterior',
                sistema_contabilidad = '$sistema_contabilidad',
                emision_electronica = '$emision_electronica',
                ple = '$ple',
                respuesta_api = '$respuesta_api',
                ruc = '$ruc',
                contacto = '$contacto',
                telefono = '$telefono',
                email = '$email',
                direccion = '$direccion',
                estado = '$estado'
                WHERE id = '$id'");
            if ($q) {
                return ['status' => 202, 'message' => 'Proveedor modificado correctamente.'];
            } else {
                return ['status' => 303, 'message' => 'No se pudo modificar el proveedor.'];
            }
        }
    }

    public function deleteRegistro($cid = null)
    {
        if ($cid != null) {
            $q = $this->con->query("DELETE FROM proveedores WHERE id = '$cid'") or die($this->con->error);
            if ($q) {
                return ['status' => 202, 'message' => 'Registro eliminado correctamente.'];
            } else {
                return ['status' => 202, 'message' => 'No se ha podido eliminar el registro.'];
            }
        } else {
            return ['status' => 303, 'message' => 'ID inválido.'];
        }
    }

    public function getAllProveedores()
    {
        $data = [];
        $q = $this->con->query("SELECT id, empresa, ruc, contacto, email, estado FROM proveedores ORDER BY id DESC");
        if ($q && $q->num_rows > 0) {
            while ($row = $q->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }
}

if (isset($_POST['add_update'])) {
    $id = $_POST['id'];
    $empresa = $_POST['empresa'];
    $nombre_comercial = $_POST['nombre_comercial'];
    $condicion = $_POST['condicion'];
    $estado_ruc = $_POST['estado_ruc'];
    $tipo = $_POST['tipo'];
    $inscripcion = $_POST['inscripcion'];
    $codigo_ubigeo = $_POST['codigo_ubigeo'];
    $sistema_emision = $_POST['sistema_emision'];
    $actividad_exterior = $_POST['actividad_exterior'];
    $sistema_contabilidad = $_POST['sistema_contabilidad'];
    $emision_electronica = $_POST['emision_electronica'];
    $ple = $_POST['ple'];
    $respuesta_api = $_POST['respuesta_api'];
    $ruc = $_POST['ruc'];
    $contacto = $_POST['contacto'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $direccion = $_POST['direccion'];
    $estado = $_POST['estado'];

    $p = new Proveedor();
    echo json_encode($p->addRegistro($id, $empresa, $nombre_comercial, $condicion, $estado_ruc, $tipo, $inscripcion, $codigo_ubigeo, $sistema_emision, $actividad_exterior, $sistema_contabilidad, $emision_electronica, $ple, $respuesta_api, $ruc, $contacto, $telefono, $email, $direccion, $estado));
}

if (isset($_POST['eliminar_registro'])) {
    $cid = $_POST['cid'];
    $p = new Proveedor();
    echo json_encode($p->deleteRegistro($cid));
}

if (isset($_POST['getAllProveedores'])) {
    $p = new Proveedor();
    echo json_encode($p->getAllProveedores());
}
