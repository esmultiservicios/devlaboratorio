<?php
// getInfoFacturaCambioFecha.php
session_start();
include '../funtions.php';

header('Content-Type: application/json; charset=UTF-8');

$mysqli = connect_mysqli();

if (!isset($_SESSION['colaborador_id'])) {
    echo json_encode([
        'status' => false,
        'title' => 'Sesión expirada',
        'message' => 'Debe iniciar sesión nuevamente.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$facturas_id = isset($_POST['facturas_id']) ? intval($_POST['facturas_id']) : 0;

if ($facturas_id <= 0) {
    echo json_encode([
        'status' => false,
        'title' => 'Error',
        'message' => 'No se recibió la factura.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$sql = "
SELECT 
    f.facturas_id,
    f.secuencia_facturacion_id,
    f.number,
    f.fecha,
    DATE_FORMAT(f.fecha, '%Y-%m-%d') AS fecha_iso,
    DATE_FORMAT(f.fecha, '%d/%m/%Y') AS fecha_formato,
    f.pacientes_id,
    CONCAT(p.nombre, ' ', p.apellido) AS paciente,
    p.identidad,
    sc.prefijo,
    sc.relleno,
    s.nombre AS servicio,
    CONCAT(c.nombre, ' ', c.apellido) AS profesional
FROM facturas AS f
INNER JOIN pacientes AS p ON f.pacientes_id = p.pacientes_id
INNER JOIN secuencia_facturacion AS sc ON f.secuencia_facturacion_id = sc.secuencia_facturacion_id
INNER JOIN servicios AS s ON f.servicio_id = s.servicio_id
INNER JOIN colaboradores AS c ON f.colaborador_id = c.colaborador_id
WHERE f.facturas_id = ?
LIMIT 1
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $facturas_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode([
        'status' => false,
        'title' => 'No encontrado',
        'message' => 'No se encontró la factura seleccionada.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = $result->fetch_assoc();

if (intval($data['number']) <= 0) {
    echo json_encode([
        'status' => false,
        'title' => 'Factura no generada',
        'message' => 'No se puede cambiar la fecha porque la factura aún no tiene número generado.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$numero = intval($data['number']);
$secuencia_facturacion_id = intval($data['secuencia_facturacion_id']);

$sql_grupo = "
SELECT 
    f.facturas_id,
    f.fecha,
    CONCAT(p.nombre, ' ', p.apellido) AS paciente,
    p.identidad
FROM facturas AS f
INNER JOIN pacientes AS p ON f.pacientes_id = p.pacientes_id
WHERE f.number = ?
AND f.secuencia_facturacion_id = ?
AND f.estado IN (2,4)
ORDER BY f.facturas_id ASC
";

$stmt_grupo = $mysqli->prepare($sql_grupo);
$stmt_grupo->bind_param("ii", $numero, $secuencia_facturacion_id);
$stmt_grupo->execute();
$res_grupo = $stmt_grupo->get_result();

$facturas_grupo = [];
while ($row = $res_grupo->fetch_assoc()) {
    $facturas_grupo[] = $row;
}

$total_facturas_afectadas = count($facturas_grupo);

$sql_pago = "
SELECT COUNT(*) AS total_pagos
FROM pagos
WHERE facturas_id IN (
    SELECT facturas_id 
    FROM facturas 
    WHERE number = ?
    AND secuencia_facturacion_id = ?
    AND estado IN (2,4)
)
AND estado = 1
";

$stmt_pago = $mysqli->prepare($sql_pago);
$stmt_pago->bind_param("ii", $numero, $secuencia_facturacion_id);
$stmt_pago->execute();
$res_pago = $stmt_pago->get_result();
$row_pago = $res_pago->fetch_assoc();

$tiene_pago = intval($row_pago['total_pagos']) > 0 ? 1 : 0;

$factura = $data['prefijo'] . rellenarDigitos($data['number'], $data['relleno']);

echo json_encode([
    'status' => true,
    'data' => [
        'facturas_id' => intval($data['facturas_id']),
        'secuencia_facturacion_id' => $secuencia_facturacion_id,
        'numero' => $numero,
        'factura' => $factura,
        'fecha_actual' => $data['fecha_iso'],
        'fecha_actual_formato' => $data['fecha_formato'],
        'paciente' => $data['paciente'],
        'identidad' => $data['identidad'],
        'servicio' => $data['servicio'],
        'profesional' => $data['profesional'],
        'tipo_factura_agrupada' => $total_facturas_afectadas > 1 ? 'Grupal' : 'Individual',
        'total_facturas_afectadas' => $total_facturas_afectadas,
        'tiene_pago' => $tiene_pago,
        'facturas_grupo' => $facturas_grupo
    ]
], JSON_UNESCAPED_UNICODE);

$stmt->close();
$stmt_grupo->close();
$stmt_pago->close();
$mysqli->close();