<?php

class Emisor
{
    private $con;

    function __construct()
    {
        include_once("Database.php");
        $db = new Database();
        $this->con = $db->connect();
    }

    public function addRegistro($id, $ruc, $tipo_doc, $razon_social, $nom_comercial, $email, $codigo_ubigeo, $direccion, $direccion_departamento, $direccion_provincia, $direccion_distrito, $direccion_codigopais, $modalidad_envio_sunat, $logo, $token_cliente, $ruc_proveedor, $tipo_certificado, $tipo_proceso)
    {
        if ($id == 0) {
            $stmt = $this->con->prepare("INSERT INTO emisor 
                (ruc, tipo_doc, razon_social, nom_comercial, email, codigo_ubigeo, direccion, direccion_departamento, direccion_provincia, direccion_distrito, direccion_codigopais, modalidad_envio_sunat, logo, token_cliente, ruc_proveedor, tipo_certificado, tipo_proceso)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param(
                "sssssssssssssssss",
                $ruc, $tipo_doc, $razon_social, $nom_comercial, $email, $codigo_ubigeo, $direccion, $direccion_departamento, $direccion_provincia, $direccion_distrito, $direccion_codigopais, $modalidad_envio_sunat, $logo, $token_cliente, $ruc_proveedor, $tipo_certificado, $tipo_proceso
            );
            if ($stmt->execute()) {
                return ['status' => 202, 'message' => 'Emisor registrado correctamente.'];
            } else {
                return ['status' => 303, 'message' => 'Error al registrar emisor: ' . $stmt->error];
            }
        } else {
            $q = $this->con->query("UPDATE emisor SET
                ruc = '$ruc',
                tipo_doc = '$tipo_doc',
                razon_social = '$razon_social',
                nom_comercial = '$nom_comercial',
                email = '$email',
                codigo_ubigeo = '$codigo_ubigeo',
                direccion = '$direccion',
                direccion_departamento = '$direccion_departamento',
                direccion_provincia = '$direccion_provincia',
                direccion_distrito = '$direccion_distrito',
                direccion_codigopais = '$direccion_codigopais',
                modalidad_envio_sunat = '$modalidad_envio_sunat',
                logo = '$logo',
                token_cliente = '$token_cliente',
                ruc_proveedor = '$ruc_proveedor',
                tipo_certificado = '$tipo_certificado',
                tipo_proceso = '$tipo_proceso'
                WHERE id = '$id'");
            if ($q) {
                return ['status' => 202, 'message' => 'Emisor modificado correctamente.'];
            } else {
                return ['status' => 303, 'message' => 'No se pudo modificar el emisor.'];
            }
        }
    }

    public function deleteRegistro($cid = null)
    {
        if ($cid != null) {
            $q = $this->con->query("DELETE FROM emisor WHERE id = '$cid'") or die($this->con->error);
            if ($q) {
                return ['status' => 202, 'message' => 'Registro eliminado correctamente.'];
            } else {
                return ['status' => 202, 'message' => 'No se ha podido eliminar el registro.'];
            }
        } else {
            return ['status' => 303, 'message' => 'ID inválido.'];
        }
    }

    public function getAllEmisores()
    {
        $data = [];
        $q = $this->con->query("SELECT id, ruc, razon_social, nom_comercial FROM emisor ORDER BY id DESC");
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
    $ruc = $_POST['ruc'];
    $tipo_doc = $_POST['tipo_doc'];
    $razon_social = $_POST['razon_social'];
    $nom_comercial = $_POST['nom_comercial'];
    $email = $_POST['email'];
    $codigo_ubigeo = $_POST['codigo_ubigeo'];
    $direccion = $_POST['direccion'];
    $direccion_departamento = $_POST['direccion_departamento'];
    $direccion_provincia = $_POST['direccion_provincia'];
    $direccion_distrito = $_POST['direccion_distrito'];
    $direccion_codigopais = $_POST['direccion_codigopais'];
    $modalidad_envio_sunat = $_POST['modalidad_envio_sunat'];
    $logo = $_POST['logo'];
    $token_cliente = $_POST['token_cliente'];
    $ruc_proveedor = $_POST['ruc_proveedor'];
    $tipo_certificado = $_POST['tipo_certificado'];
    $tipo_proceso = $_POST['tipo_proceso'];

    $p = new Emisor();
    echo json_encode($p->addRegistro($id, $ruc, $tipo_doc, $razon_social, $nom_comercial, $email, $codigo_ubigeo, $direccion, $direccion_departamento, $direccion_provincia, $direccion_distrito, $direccion_codigopais, $modalidad_envio_sunat, $logo, $token_cliente, $ruc_proveedor, $tipo_certificado, $tipo_proceso));
}

if (isset($_POST['eliminar_registro'])) {
    $cid = $_POST['cid'];
    $p = new Emisor();
    echo json_encode($p->deleteRegistro($cid));
}
