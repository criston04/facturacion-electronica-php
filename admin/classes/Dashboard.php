<?php

class Dashboard
{
    private $con;

    function __construct()
    {
        include_once("Database.php");
        $db = new Database();
        $this->con = $db->connect();
    }

    public function getTotalProductos()
    {
        $q = $this->con->query("SELECT COUNT(*) AS total FROM productos WHERE estado = 'ACTIVO'");
        return $q->fetch_assoc()['total'] ?? 0;
    }

    public function getTotalClientes()
    {
        $q = $this->con->query("SELECT COUNT(*) AS total FROM clientes WHERE estado_cliente = 'ACTIVO'");
        return $q->fetch_assoc()['total'] ?? 0;
    }

    public function getTotalVentas()
    {
        $q = $this->con->query("SELECT COUNT(*) AS total FROM ventas");
        return $q->fetch_assoc()['total'] ?? 0;
    }

    public function getTotalIngresos()
    {
        $q = $this->con->query("SELECT COALESCE(SUM(total), 0) AS total FROM ventas WHERE estado != 'ANULADO'");
        return $q->fetch_assoc()['total'] ?? 0;
    }

    public function getVentasHoy()
    {
        $hoy = date('Y-m-d');
        $q = $this->con->query("SELECT COUNT(*) AS total, COALESCE(SUM(total), 0) AS monto FROM ventas WHERE DATE(fecha_emision) = '$hoy' AND estado != 'ANULADO'");
        return $q->fetch_assoc();
    }

    public function getProductosStockBajo($limite = 5)
    {
        $data = [];
        $q = $this->con->query("SELECT p.*, c.nombre AS categoria_nombre FROM productos p LEFT JOIN categorias c ON p.id_categoria = c.id WHERE p.estado = 'ACTIVO' AND p.stock_actual <= p.stock_minimo ORDER BY (p.stock_minimo - p.stock_actual) DESC LIMIT $limite");
        if ($q && $q->num_rows > 0) {
            while ($row = $q->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function getVentasRecientes($limite = 10)
    {
        $data = [];
        $sql = "SELECT v.*,
                COALESCE(NULLIF(c.razon_social, ''), TRIM(CONCAT_WS(' ', c.nombres, c.apellido_paterno, c.apellido_materno))) AS cliente_nombre,
                c.numero_documento AS cliente_doc
                FROM ventas v
                LEFT JOIN clientes c ON v.id_cliente = c.id
                ORDER BY v.id DESC LIMIT $limite";
        $q = $this->con->query($sql);
        if ($q && $q->num_rows > 0) {
            while ($row = $q->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function getVentasPorMes($meses = 6)
    {
        $data = [];
        $sql = "SELECT DATE_FORMAT(fecha_emision, '%Y-%m') AS mes, COUNT(*) AS total_ventas, COALESCE(SUM(total), 0) AS total_ingresos
                FROM ventas
                WHERE estado != 'ANULADO'
                AND fecha_emision >= DATE_SUB(CURDATE(), INTERVAL $meses MONTH)
                GROUP BY DATE_FORMAT(fecha_emision, '%Y-%m')
                ORDER BY mes ASC";
        $q = $this->con->query($sql);
        if ($q && $q->num_rows > 0) {
            while ($row = $q->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }
}

if (isset($_POST['get_dashboard_data'])) {
    $d = new Dashboard();
    echo json_encode([
        'status' => 202,
        'total_productos' => $d->getTotalProductos(),
        'total_clientes' => $d->getTotalClientes(),
        'total_ventas' => $d->getTotalVentas(),
        'total_ingresos' => $d->getTotalIngresos(),
        'ventas_hoy' => $d->getVentasHoy(),
        'stock_bajo' => $d->getProductosStockBajo(),
        'ventas_recientes' => $d->getVentasRecientes(),
        'ventas_por_mes' => $d->getVentasPorMes(),
    ]);
    exit();
}
