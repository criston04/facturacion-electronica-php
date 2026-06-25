<?php

class Producto
{
    private $con;

    function __construct()
    {
        include_once("Database.php");
        $db = new Database();
        $this->con = $db->connect();
    }

    private function uploadImage($file, $current = null)
    {
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('prod_') . '.' . $ext;
            $dest = __DIR__ . '/../../uploads/productos/' . $filename;
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                if ($current && file_exists(__DIR__ . '/../../uploads/productos/' . $current)) {
                    unlink(__DIR__ . '/../../uploads/productos/' . $current);
                }
                return $filename;
            }
        }
        return $current;
    }

    public function addRegistro($id, $codigo, $nombre, $descripcion, $id_categoria, $id_proveedor, $precio_venta, $costo_compra, $stock_actual, $stock_minimo, $codigo_barras, $imagen_file, $imagen_actual, $estado)
    {
        $imagen = $this->uploadImage($imagen_file, $imagen_actual);

        if ($id == 0) {
            $stmt = $this->con->prepare("INSERT INTO productos 
                (codigo, nombre, descripcion, id_categoria, id_proveedor, precio_venta, costo_compra, stock_actual, stock_minimo, codigo_barras, imagen, estado)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param(
                "sssiiddiisss",
                $codigo, $nombre, $descripcion, $id_categoria, $id_proveedor, $precio_venta, $costo_compra, $stock_actual, $stock_minimo, $codigo_barras, $imagen, $estado
            );
            if ($stmt->execute()) {
                return ['status' => 202, 'message' => 'Producto registrado correctamente.'];
            } else {
                return ['status' => 303, 'message' => 'Error al registrar producto.'];
            }
        } else {
            $q = $this->con->query("UPDATE productos SET
                codigo = '$codigo',
                nombre = '$nombre',
                descripcion = '$descripcion',
                id_categoria = '$id_categoria',
                id_proveedor = '$id_proveedor',
                precio_venta = '$precio_venta',
                costo_compra = '$costo_compra',
                stock_actual = '$stock_actual',
                stock_minimo = '$stock_minimo',
                codigo_barras = '$codigo_barras',
                imagen = " . ($imagen ? "'$imagen'" : "imagen") . ",
                estado = '$estado'
                WHERE id = '$id'");
            if ($q) {
                return ['status' => 202, 'message' => 'Producto modificado correctamente.'];
            } else {
                return ['status' => 303, 'message' => 'No se pudo modificar el producto.'];
            }
        }
    }

    public function deleteRegistro($cid = null)
    {
        if ($cid != null) {
            $res = $this->con->query("SELECT imagen FROM productos WHERE id = '$cid'");
            if ($row = $res->fetch_assoc()) {
                if ($row['imagen'] && file_exists(__DIR__ . '/../../uploads/productos/' . $row['imagen'])) {
                    unlink(__DIR__ . '/../../uploads/productos/' . $row['imagen']);
                }
            }
            $q = $this->con->query("DELETE FROM productos WHERE id = '$cid'") or die($this->con->error);
            if ($q) {
                return ['status' => 202, 'message' => 'Registro eliminado correctamente.'];
            } else {
                return ['status' => 202, 'message' => 'No se ha podido eliminar el registro.'];
            }
        } else {
            return ['status' => 303, 'message' => 'ID inválido.'];
        }
    }

    public function getAllProductos()
    {
        $data = [];
        $q = $this->con->query("SELECT p.*, c.nombre as categoria_nombre, pr.empresa as proveedor_empresa
            FROM productos p
            LEFT JOIN categorias c ON p.id_categoria = c.id
            LEFT JOIN proveedores pr ON p.id_proveedor = pr.id
            ORDER BY p.id DESC");
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
    $codigo = $_POST['codigo'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $id_categoria = $_POST['id_categoria'];
    $id_proveedor = $_POST['id_proveedor'];
    $precio_venta = $_POST['precio_venta'];
    $costo_compra = $_POST['costo_compra'];
    $stock_actual = $_POST['stock_actual'];
    $stock_minimo = $_POST['stock_minimo'];
    $codigo_barras = $_POST['codigo_barras'];
    $imagen_file = isset($_FILES['imagen']) ? $_FILES['imagen'] : null;
    $imagen_actual = $_POST['imagen_actual'] ?? '';
    $estado = $_POST['estado'];

    $p = new Producto();
    echo json_encode($p->addRegistro($id, $codigo, $nombre, $descripcion, $id_categoria, $id_proveedor, $precio_venta, $costo_compra, $stock_actual, $stock_minimo, $codigo_barras, $imagen_file, $imagen_actual, $estado));
}

if (isset($_POST['eliminar_registro'])) {
    $cid = $_POST['cid'];
    $p = new Producto();
    echo json_encode($p->deleteRegistro($cid));
}
