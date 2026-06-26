<?php
// getTotalMuestras.php
include('../funtions.php');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: text/plain; charset=utf-8');

ini_set('display_errors', 0);
error_reporting(E_ALL);

date_default_timezone_set('America/Tegucigalpa');

$mysqli = connect_mysqli();
if (!$mysqli) {
    error_log('getTotalMuestras.php: No se pudo conectar a la base de datos.');
    echo number_format(0);
    exit;
}

if (method_exists($mysqli, 'set_charset')) {
    $mysqli->set_charset('utf8');
}

$fecha_sistema = date('Y-m-d');

// Valores de sesión seguros. En este archivo no se usan para filtrar,
// pero se conservan para no romper compatibilidad con llamadas existentes.
$colaborador_id = isset($_SESSION['colaborador_id']) ? (int)$_SESSION['colaborador_id'] : 0;
$type = isset($_SESSION['type']) ? (int)$_SESSION['type'] : 0;

$total = 0;

$query = "SELECT COUNT(muestras_id) AS total
          FROM muestras
          WHERE estado = 1";

$result = $mysqli->query($query);

if (!$result) {
    error_log('getTotalMuestras.php: Error al consultar total de muestras: ' . $mysqli->error);
    $mysqli->close();
    echo number_format(0);
    exit;
}

if ($result->num_rows > 0) {
    $consulta = $result->fetch_assoc();
    $total = isset($consulta['total']) ? (int)$consulta['total'] : 0;
}

$result->free();
$mysqli->close();

echo number_format($total);
exit;