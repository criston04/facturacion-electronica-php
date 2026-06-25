<?php
class Commun
{
    private $con;
    function __construct()
    {
        include_once("Database.php");
        $db = new Database();
        $this->con = $db->connect();
    }

    public function getUnidadMedida()
    {
        $enumerado = [];
        $q = $this->con->query("SELECT u.id_unidad, u.nombre FROM unidad_medida u ");
        if ($q->num_rows > 0) {
            while ($row = $q->fetch_assoc()) {
                $enumerado[] = $row;
            }
            $_DATA['enumerado'] = $enumerado;
        }
        return ['status' => 202, 'message' => $_DATA];
    }

    public function getTipoImpuesto()
    {
        $enumerado = [];
        $q = $this->con->query("SELECT i.id_impuesto, i.nombre FROM tipo_impuesto i ");
        if ($q->num_rows > 0) {
            while ($row = $q->fetch_assoc()) {
                $enumerado[] = $row;
            }
            $_DATA['enumerado'] = $enumerado;
        }
        return ['status' => 202, 'message' => $_DATA];
    }

    public function getTiposDocumentoIdentidad()
    {
        $data = [];
        $sql = "SELECT id_tipo_doc, nombre FROM tipo_documento_identidad ";
        $q = $this->con->query($sql);
        if ($q && $q->num_rows > 0) {
            while ($row = $q->fetch_assoc()) {
                $data[] = $row;
            }
        }
        $_DATA['enumerado'] = $data;
        return ['status' => 202, 'message' => $_DATA];
    }

    public function getTipoComprobante()
    {
        $data = [];
        $q = $this->con->query("SELECT id_tipo_comp, nombre FROM tipo_comprobante");
        if ($q && $q->num_rows > 0) {
            while ($row = $q->fetch_assoc()) {
                $data[] = $row;
            }
        }
        $_DATA['enumerado'] = $data;
        return ['status' => 202, 'message' => $_DATA];
    }

    public function getMoneda()
    {
        $data = [];
        $q = $this->con->query("SELECT id_moneda, nombre, simbolo FROM moneda");
        if ($q && $q->num_rows > 0) {
            while ($row = $q->fetch_assoc()) {
                $data[] = $row;
            }
        }
        $_DATA['enumerado'] = $data;
        return ['status' => 202, 'message' => $_DATA];
    }

    public function getMetodoPago()
    {
        $data = [];
        $q = $this->con->query("SELECT id_metodo_pago, nombre FROM tipo_metodo_pago");
        if ($q && $q->num_rows > 0) {
            while ($row = $q->fetch_assoc()) {
                $data[] = $row;
            }
        }
        $_DATA['enumerado'] = $data;
        return ['status' => 202, 'message' => $_DATA];
    }

    public function getEstadoFactura()
    {
        $data = [];
        $q = $this->con->query("SELECT id_estado, nombre FROM tipo_estado");
        if ($q && $q->num_rows > 0) {
            while ($row = $q->fetch_assoc()) {
                $data[] = $row;
            }
        }
        $_DATA['enumerado'] = $data;
        return ['status' => 202, 'message' => $_DATA];
    }
}

if (isset($_POST['GET_UNIDADMEDIDA'])) {
    $p = new Commun();
    echo json_encode($p->getUnidadMedida());
    exit();
}
if (isset($_POST['GET_TIPOIMPUESTO'])) {
    $p = new Commun();
    echo json_encode($p->getTipoImpuesto());
    exit();
}
if (isset($_POST['GET_TIPODOCUMENTOIDENTIDAD'])) {
    $p = new Commun();
    echo json_encode($p->getTiposDocumentoIdentidad());
    exit();
}
if (isset($_POST['GET_TIPOCOMPROBANTE'])) {
    $p = new Commun();
    echo json_encode($p->getTipoComprobante());
    exit();
}
if (isset($_POST['GET_MONEDA'])) {
    $p = new Commun();
    echo json_encode($p->getMoneda());
    exit();
}
if (isset($_POST['GET_METODOPAGO'])) {
    $p = new Commun();
    echo json_encode($p->getMetodoPago());
    exit();
}
if (isset($_POST['GET_ESTADOFACTURA'])) {
    $p = new Commun();
    echo json_encode($p->getEstadoFactura());
    exit();
}
