<?php

/**
 * ResumenDiario: agrupa las boletas de una fecha y las informa a SUNAT mediante el
 * resumen diario (RC). Reutiliza Facturalaya->enviarResumen().
 */
class ResumenDiario
{
    private $con;

    function __construct()
    {
        date_default_timezone_set('America/Lima');
        include_once("Database.php");
        $db = new Database();
        $this->con = $db->connect();
    }

    /** Boletas emitidas en la fecha indicada (Y-m-d). */
    public function getBoletasPorFecha(string $fecha): array
    {
        $data = [];
        $stmt = $this->con->prepare(
            "SELECT id, serie, correlativo, subtotal, igv, total, estado, sunat_estado
             FROM ventas
             WHERE tipo_comprobante = 'BOLETA' AND DATE(fecha_emision) = ?
             ORDER BY correlativo ASC"
        );
        $stmt->bind_param("s", $fecha);
        $stmt->execute();
        $r = $stmt->get_result();
        while ($row = $r->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    /** Arma y envía el resumen diario de las boletas de la fecha. */
    public function enviar(string $fecha): array
    {
        $boletas = $this->getBoletasPorFecha($fecha);
        if (empty($boletas)) {
            return ['status' => 303, 'message' => 'No hay boletas emitidas en esa fecha.'];
        }

        include_once("Facturalaya.php");
        $f = new Facturalaya();
        $res = $f->enviarResumen($fecha, $boletas);

        return [
            'status' => 202,
            'message' => $res['success']
                ? "Resumen del $fecha enviado a SUNAT. Ticket: {$res['ticket']}"
                : "Resumen guardado, pero falló el envío a SUNAT (endpoint por confirmar): {$res['message']}",
            'total_boletas' => count($boletas),
            'sunat' => $res,
        ];
    }
}

// ----------------------------- Endpoints AJAX -----------------------------

if (isset($_POST['get_boletas_fecha'])) {
    $r = new ResumenDiario();
    echo json_encode($r->getBoletasPorFecha($_POST['fecha'] ?? date('Y-m-d')));
    exit();
}

if (isset($_POST['enviar_resumen'])) {
    $r = new ResumenDiario();
    echo json_encode($r->enviar($_POST['fecha'] ?? date('Y-m-d')));
    exit();
}
