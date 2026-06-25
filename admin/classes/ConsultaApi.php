<?php
include_once("Database.php");
header('Content-Type: application/json');

$db = new Database();
$con = $db->connect();

$emisor = $con->query("SELECT token_cliente FROM emisor WHERE id = 2 LIMIT 1")->fetch_assoc();
$token = $emisor['token_cliente'] ?? '';

if (isset($_POST['consultar_dni'])) {
    $numero = preg_replace('/\D/', '', $_POST['consultar_dni'] ?? '');
    if (strlen($numero) !== 8) {
        echo json_encode(['error' => 'DNI inválido']);
        exit;
    }
    $url = "https://facturalahoy.com/api/persona/{$numero}/{$token}";
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo $response ?: json_encode(['error' => 'Error de conexión']);
    exit;
}

if (isset($_POST['consultar_ruc'])) {
    $numero = preg_replace('/\D/', '', $_POST['consultar_ruc'] ?? '');
    if (strlen($numero) !== 11) {
        echo json_encode(['error' => 'RUC inválido']);
        exit;
    }
    $url = "https://facturalahoy.com/api/empresa/{$numero}/{$token}";
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo $response ?: json_encode(['error' => 'Error de conexión']);
    exit;
}

echo json_encode(['error' => 'Acción inválida']);
