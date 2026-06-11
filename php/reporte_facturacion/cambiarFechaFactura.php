<?php
// cambiarFechaFactura.php
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

$colaborador_id = intval($_SESSION['colaborador_id']);
$usuario = intval($_SESSION['colaborador_id']);
$type = isset($_SESSION['type']) ? intval($_SESSION['type']) : 0;

if (!($type == 1 || $type == 2 || $type == 4)) {
    echo json_encode([
        'status' => false,
        'title' => 'Acceso denegado',
        'message' => 'No tiene permisos para cambiar la fecha de una factura.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$facturas_id = isset($_POST['facturas_id']) ? intval($_POST['facturas_id']) : 0;
$fecha_nueva = isset($_POST['fecha_nueva']) ? trim($_POST['fecha_nueva']) : '';
$comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';

if ($facturas_id <= 0) {
    echo json_encode([
        'status' => false,
        'title' => 'Error',
        'message' => 'No se recibió la factura.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($fecha_nueva == '') {
    echo json_encode([
        'status' => false,
        'title' => 'Fecha requerida',
        'message' => 'Debe seleccionar la nueva fecha de la factura.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_nueva)) {
    echo json_encode([
        'status' => false,
        'title' => 'Fecha inválida',
        'message' => 'La fecha recibida no tiene un formato válido.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($comentario == '') {
    echo json_encode([
        'status' => false,
        'title' => 'Comentario requerido',
        'message' => 'Debe escribir el motivo del cambio de fecha.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (mb_strlen($comentario, 'UTF-8') < 5) {
    echo json_encode([
        'status' => false,
        'title' => 'Comentario muy corto',
        'message' => 'Debe escribir un comentario más claro sobre el motivo del cambio.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (mb_strlen($comentario, 'UTF-8') > 500) {
    echo json_encode([
        'status' => false,
        'title' => 'Comentario muy largo',
        'message' => 'El comentario no puede superar los 500 caracteres.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$sql_factura = "
SELECT 
    f.facturas_id,
    f.secuencia_facturacion_id,
    f.number,
    f.fecha,
    sc.prefijo,
    sc.relleno
FROM facturas AS f
INNER JOIN secuencia_facturacion AS sc ON f.secuencia_facturacion_id = sc.secuencia_facturacion_id
WHERE f.facturas_id = ?
AND f.estado IN (2,4)
LIMIT 1
";

$stmt = $mysqli->prepare($sql_factura);
$stmt->bind_param("i", $facturas_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode([
        'status' => false,
        'title' => 'No encontrado',
        'message' => 'No se encontró la factura o no está en un estado válido para modificar.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$factura_data = $result->fetch_assoc();

$numero = intval($factura_data['number']);
$secuencia_facturacion_id = intval($factura_data['secuencia_facturacion_id']);
$fecha_anterior = $factura_data['fecha'];
$prefijo = $factura_data['prefijo'];
$relleno = intval($factura_data['relleno']);

if ($numero <= 0) {
    echo json_encode([
        'status' => false,
        'title' => 'Factura no generada',
        'message' => 'No se puede cambiar la fecha porque la factura aún no tiene número generado.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($fecha_anterior == $fecha_nueva) {
    echo json_encode([
        'status' => false,
        'title' => 'Sin cambios',
        'message' => 'La nueva fecha es igual a la fecha actual de la factura.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$sql_ids = "
SELECT facturas_id, fecha
FROM facturas
WHERE number = ?
AND secuencia_facturacion_id = ?
AND estado IN (2,4)
ORDER BY facturas_id ASC
";

$stmt_ids = $mysqli->prepare($sql_ids);
$stmt_ids->bind_param("ii", $numero, $secuencia_facturacion_id);
$stmt_ids->execute();
$res_ids = $stmt_ids->get_result();

$ids = [];
$fechas_anteriores = [];

while ($row = $res_ids->fetch_assoc()) {
    $ids[] = intval($row['facturas_id']);
    $fechas_anteriores[] = [
        'facturas_id' => intval($row['facturas_id']),
        'fecha_anterior' => $row['fecha']
    ];
}

if (count($ids) == 0) {
    echo json_encode([
        'status' => false,
        'title' => 'Error',
        'message' => 'No se encontraron facturas para modificar.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$sql_pago = "
SELECT COUNT(*) AS total_pagos
FROM pagos
WHERE facturas_id IN (" . implode(',', array_fill(0, count($ids), '?')) . ")
AND estado = 1
";

$types_pago = str_repeat('i', count($ids));
$stmt_pago = $mysqli->prepare($sql_pago);
$stmt_pago->bind_param($types_pago, ...$ids);
$stmt_pago->execute();
$res_pago = $stmt_pago->get_result();
$row_pago = $res_pago->fetch_assoc();

$tiene_pago = intval($row_pago['total_pagos']) > 0 ? 1 : 0;

$factura = $prefijo . rellenarDigitos($numero, $relleno);
$ids_json = json_encode($ids, JSON_UNESCAPED_UNICODE);
$fechas_anteriores_json = json_encode($fechas_anteriores, JSON_UNESCAPED_UNICODE);

$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : '';

$mysqli->begin_transaction();

try {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $sql_update = "
    UPDATE facturas
    SET fecha = ?
    WHERE facturas_id IN ($placeholders)
    ";

    $stmt_update = $mysqli->prepare($sql_update);

    $types_update = 's' . str_repeat('i', count($ids));
    $params_update = array_merge([$fecha_nueva], $ids);

    $stmt_update->bind_param($types_update, ...$params_update);

    if (!$stmt_update->execute()) {
        throw new Exception('No se pudo actualizar la fecha de la factura.');
    }

    $sql_insert = "
    INSERT INTO facturas_cambio_fecha
    (
        facturas_id_referencia,
        secuencia_facturacion_id,
        numero,
        prefijo,
        factura,
        fecha_anterior,
        fecha_nueva,
        comentario,
        facturas_ids_afectadas,
        total_facturas_afectadas,
        tiene_pago,
        colaborador_id,
        usuario,
        ip,
        user_agent,
        estado,
        fecha_registro
    )
    VALUES
    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
    ";

    $stmt_insert = $mysqli->prepare($sql_insert);

    $total_facturas_afectadas = count($ids);

    $comentario_final = $comentario;

    if ($total_facturas_afectadas > 1) {
        $comentario_final .= " | Factura grupal. Fechas anteriores: " . $fechas_anteriores_json;
    }

    $stmt_insert->bind_param(
        "iiissssssiiiiss",
        $facturas_id,
        $secuencia_facturacion_id,
        $numero,
        $prefijo,
        $factura,
        $fecha_anterior,
        $fecha_nueva,
        $comentario_final,
        $ids_json,
        $total_facturas_afectadas,
        $tiene_pago,
        $colaborador_id,
        $usuario,
        $ip,
        $user_agent
    );

    if (!$stmt_insert->execute()) {
        throw new Exception('No se pudo guardar el historial del cambio.');
    }

    $mysqli->commit();

    echo json_encode([
        'status' => true,
        'title' => 'Fecha actualizada',
        'message' => 'La fecha de la factura fue actualizada correctamente.',
        'data' => [
            'factura' => $factura,
            'fecha_anterior' => $fecha_anterior,
            'fecha_nueva' => $fecha_nueva,
            'total_facturas_afectadas' => $total_facturas_afectadas,
            'tiene_pago' => $tiene_pago
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    $mysqli->rollback();

    echo json_encode([
        'status' => false,
        'title' => 'Error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

$stmt->close();
$stmt_ids->close();
$stmt_pago->close();

if (isset($stmt_update)) {
    $stmt_update->close();
}

if (isset($stmt_insert)) {
    $stmt_insert->close();
}

$mysqli->close();