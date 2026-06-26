<?php
// pendienteAtenciones.php
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
    error_log('pendienteAtenciones.php: No se pudo conectar a la base de datos.');
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

$anio = date('Y', strtotime($fecha_sistema));
$mes = date('m', strtotime($fecha_sistema));
$ultimo_dia_mes = date('d', mktime(0, 0, 0, (int)$mes + 1, 0, (int)$anio));

$fecha_inicial = date('Y-m-d', strtotime($anio . '-' . $mes . '-01'));
$fecha_final = date('Y-m-d', strtotime($anio . '-' . $mes . '-' . $ultimo_dia_mes));

$total = 0;

$query = "SELECT COUNT(calendario_id) AS total
          FROM calendario
          WHERE CAST(fecha_cita AS DATE) BETWEEN ? AND ?
            AND estado = 0";

$stmt = $mysqli->prepare($query);
if (!$stmt) {
    error_log('pendienteAtenciones.php: Error al preparar consulta: ' . $mysqli->error);
    echo number_format(0);
    $mysqli->close();
    exit;
}

$stmt->bind_param('ss', $fecha_inicial, $fecha_final);

if (!$stmt->execute()) {
    error_log('pendienteAtenciones.php: Error al ejecutar consulta: ' . $stmt->error);
    $stmt->close();
    $mysqli->close();
    echo number_format(0);
    exit;
}

$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $consulta = $result->fetch_assoc();
    $total = isset($consulta['total']) ? (int)$consulta['total'] : 0;
}

if ($result) {
    $result->free();
}

$stmt->close();
$mysqli->close();

echo number_format($total);
exit;