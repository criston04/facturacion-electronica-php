<?php

class Cliente
{
    private $con;

    function __construct()
    {
        include_once("Database.php");
        $db = new Database();
        $this->con = $db->connect();
    }

    public function getAllClientes()
    {
        $data = [];
        $sql = "SELECT id, tipo_documento, numero_documento, 
                       CONCAT(tipo_documento, ': ', numero_documento, ' - ', COALESCE(NULLIF(razon_social, ''), TRIM(CONCAT_WS(' ', nombres, apellido_paterno, apellido_materno)))) as nombre_razon_social 
                FROM clientes WHERE estado_cliente = 'ACTIVO'";
        $q = $this->con->query($sql);
        if ($q && $q->num_rows > 0) {
            while ($row = $q->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function addRegistro(
        $idcliente,
        $tipo_documento,
        $numero_documento,
        $nombres,
        $apellido_paterno,
        $apellido_materno,
        $sexo,
        $fecha_nacimiento,
        $razon_social,
        $nombre_comercial,
        $condicion,
        $estado_ruc,
        $codigo_ubigeo,
        $direccion,
        $telefono,
        $email,
        $estado_cliente
    ) {
        if ($idcliente == 0) {
            $check = $this->con->query("SELECT id FROM clientes WHERE tipo_documento = '$tipo_documento' AND numero_documento = '$numero_documento' LIMIT 1");
            if ($check && $check->num_rows > 0) {
                return ['status' => 303, 'message' => 'Ya existe un cliente con ese documento'];
            }
            if ($fecha_nacimiento === '') {
                $fecha_nacimiento = null;
            }
            $stmt = $this->con->prepare("INSERT INTO clientes 
                (tipo_documento, numero_documento, nombres, apellido_paterno, apellido_materno, sexo, fecha_nacimiento, razon_social, nombre_comercial, condicion, estado, codigo_ubigeo, direccion, telefono, email, estado_cliente)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param(
                "ssssssssssssssss",
                $tipo_documento,
                $numero_documento,
                $nombres,
                $apellido_paterno,
                $apellido_materno,
                $sexo,
                $fecha_nacimiento,
                $razon_social,
                $nombre_comercial,
                $condicion,
                $estado_ruc,
                $codigo_ubigeo,
                $direccion,
                $telefono,
                $email,
                $estado_cliente
            );
            if ($stmt->execute()) {
                return ['status' => 202, 'message' => 'Cliente registrado correctamente.'];
            } else {
                return ['status' => 303, 'message' => 'Error al registrar cliente: ' . $stmt->error];
            }
        } else {
            if ($fecha_nacimiento === '') {
                $fecha_nacimiento = null;
            }
            $stmt = $this->con->prepare("UPDATE clientes SET 
                tipo_documento = ?,
                numero_documento = ?,
                nombres = ?,
                apellido_paterno = ?,
                apellido_materno = ?,
                sexo = ?,
                fecha_nacimiento = ?,
                razon_social = ?,
                nombre_comercial = ?,
                condicion = ?,
                estado = ?,
                codigo_ubigeo = ?,
                direccion = ?,
                telefono = ?,
                email = ?,
                estado_cliente = ?
                WHERE id = ?");
            $stmt->bind_param(
                "ssssssssssssssssi",
                $tipo_documento,
                $numero_documento,
                $nombres,
                $apellido_paterno,
                $apellido_materno,
                $sexo,
                $fecha_nacimiento,
                $razon_social,
                $nombre_comercial,
                $condicion,
                $estado_ruc,
                $codigo_ubigeo,
                $direccion,
                $telefono,
                $email,
                $estado_cliente,
                $idcliente
            );
            if ($stmt->execute()) {
                return ['status' => 202, 'message' => 'Cliente modificado correctamente.'];
            } else {
                return ['status' => 303, 'message' => 'Error al modificar cliente: ' . $stmt->error];
            }
        }
    }

    public function deleteRegistro($cid = null)
    {
        if ($cid != null) {
            $q = $this->con->query("DELETE FROM clientes WHERE id = '$cid'") or die($this->con->error);
            if ($q) {
                return ['status' => 202, 'message' => 'El registro se eliminó correctamente'];
            } else {
                return ['status' => 303, 'message' => 'No se ha podido eliminar el registro'];
            }
        } else {
            return ['status' => 303, 'message' => 'ID de cliente inválido'];
        }
    }
}

if (isset($_POST['eliminar_registro'])) {
    if (!empty($_POST['cid'])) {
        $p = new Cliente();
        echo json_encode($p->deleteRegistro($_POST['cid']));
        exit();
    }
}

if (isset($_POST['add_update'])) {
    $p = new Cliente();
    echo json_encode($p->addRegistro(
        $_POST['idcliente'] ?? 0,
        $_POST['tipo_documento'] ?? '',
        $_POST['numero_documento'] ?? '',
        $_POST['nombres'] ?? '',
        $_POST['apellido_paterno'] ?? '',
        $_POST['apellido_materno'] ?? '',
        $_POST['sexo'] ?? '',
        $_POST['fecha_nacimiento'] ?? null,
        $_POST['razon_social'] ?? '',
        $_POST['nombre_comercial'] ?? '',
        $_POST['condicion'] ?? '',
        $_POST['estado_ruc'] ?? '',
        $_POST['codigo_ubigeo'] ?? '',
        $_POST['direccion'] ?? '',
        $_POST['telefono'] ?? '',
        $_POST['email'] ?? '',
        $_POST['estado_cliente'] ?? 'ACTIVO'
    ));
}
