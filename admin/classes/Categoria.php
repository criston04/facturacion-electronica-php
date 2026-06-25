<?php

class Categoria
{
    private $con;

    function __construct()
    {
        include_once("Database.php");
        $db = new Database();
        $this->con = $db->connect();
    }

    public function addRegistro($id, $nombre, $descripcion, $estado)
    {
        if ($id == 0) {
            $stmt = $this->con->prepare("INSERT INTO categorias (nombre, descripcion, estado) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nombre, $descripcion, $estado);
            if ($stmt->execute()) {
                return ['status' => 202, 'message' => 'Categoría registrada correctamente.'];
            } else {
                return ['status' => 303, 'message' => 'Error al registrar categoría.'];
            }
        } else {
            $q = $this->con->query("UPDATE categorias SET
                nombre = '$nombre',
                descripcion = '$descripcion',
                estado = '$estado'
                WHERE id = '$id'");
            if ($q) {
                return ['status' => 202, 'message' => 'Categoría modificada correctamente.'];
            } else {
                return ['status' => 303, 'message' => 'No se pudo modificar la categoría.'];
            }
        }
    }

    public function deleteRegistro($cid = null)
    {
        if ($cid != null) {
            $q = $this->con->query("DELETE FROM categorias WHERE id = '$cid'") or die($this->con->error);
            if ($q) {
                return ['status' => 202, 'message' => 'Registro eliminado correctamente.'];
            } else {
                return ['status' => 202, 'message' => 'No se ha podido eliminar el registro.'];
            }
        } else {
            return ['status' => 303, 'message' => 'ID inválido.'];
        }
    }

    public function getAllCategorias()
    {
        $data = [];
        $q = $this->con->query("SELECT id, nombre, descripcion, estado FROM categorias ORDER BY id DESC");
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
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $estado = $_POST['estado'];

    $p = new Categoria();
    echo json_encode($p->addRegistro($id, $nombre, $descripcion, $estado));
}

if (isset($_POST['eliminar_registro'])) {
    $cid = $_POST['cid'];
    $p = new Categoria();
    echo json_encode($p->deleteRegistro($cid));
}

if (isset($_POST['getAllCategorias'])) {
    $p = new Categoria();
    echo json_encode($p->getAllCategorias());
}
