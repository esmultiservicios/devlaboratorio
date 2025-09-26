<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
include "../funtions.php";

// Helper de respuesta JSON
function respond($ok, $code, $title, $message, $extra = []) {
    echo json_encode(array_merge([
        "status"  => (bool)$ok,
        "code"    => (string)$code,     // OK, INVALID_INPUT, NUMBER_IN_USE, DB_ERROR, NO_SESSION
        "title"   => (string)$title,    // Success, Error, Warning
        "message" => (string)$message
    ], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

// Mapea estado -> texto
function estadoFacturaTexto($estadoInt) {
    switch (intval($estadoInt)) {
        case 1: return "Borrador";
        case 2: return "Pagado";
        case 3: return "Cancelado";
        case 4: return "Crédito";
        default: return "Desconocido";
    }
}

// Conexión
$mysqli = connect_mysqli();
if ($mysqli->connect_errno) {
    respond(false, "DB_CONN_ERROR", "Error", "No se pudo conectar a la base de datos.");
}

// Entradas
$secuencia_facturacion_id = isset($_POST['secuencia_facturacion_id']) ? intval($_POST['secuencia_facturacion_id']) : 0;
$siguiente                = isset($_POST['siguiente']) ? intval($_POST['siguiente']) : 0;
$estado                   = isset($_POST['estado']) ? intval($_POST['estado']) : 0;
$usuario                  = isset($_SESSION['colaborador_id']) ? intval($_SESSION['colaborador_id']) : 0;

$fecha_registro_now = date("Y-m-d H:i:s");
$hoy                = date("Y-m-d");

if ($usuario <= 0) {
    respond(false, "NO_SESSION", "Error", "Sesión no válida. Inicie sesión nuevamente.");
}
if ($secuencia_facturacion_id <= 0 || $siguiente <= 0) {
    respond(false, "INVALID_INPUT", "Error", "Parámetros inválidos.");
}

// Consultar la secuencia actual
$query = "
    SELECT 
        secuencia_facturacion_id,
        empresa_id,
        cai,
        prefijo,
        relleno,
        incremento,
        siguiente,
        rango_inicial,
        rango_final,
        fecha_activacion,
        fecha_limite,
        comentario,
        activo,
        usuario,
        CAST(fecha_registro AS DATE) AS fecha_registro,
        documento_id
    FROM secuencia_facturacion
    WHERE secuencia_facturacion_id = '$secuencia_facturacion_id'
    LIMIT 1
";
$result = $mysqli->query($query);
if (!$result || $result->num_rows === 0) {
    respond(false, "NOT_FOUND", "Error", "Secuencia de facturación no encontrada.");
}
$consulta_registro = $result->fetch_assoc();

// Datos actuales
$empresa        = $consulta_registro['empresa_id'];
$cai            = $consulta_registro['cai'];
$prefijo        = $consulta_registro['prefijo'];
$relleno        = $consulta_registro['relleno'];
$incremento     = $consulta_registro['incremento'];
$rango_inicial  = $consulta_registro['rango_inicial'];
$rango_final    = $consulta_registro['rango_final'];
$fecha_limite   = $consulta_registro['fecha_limite'];
$activo_actual  = $consulta_registro['activo'];
$comentario     = $consulta_registro['comentario'];
$documento_id   = $consulta_registro['documento_id'];
$fecha_reg_row  = $consulta_registro['fecha_registro'];

// Número anterior
$q_ant = "
    SELECT siguiente AS numero_anterior
    FROM secuencia_facturacion
    WHERE secuencia_facturacion_id = '$secuencia_facturacion_id'
    LIMIT 1
";
$result_datos = $mysqli->query($q_ant);
$numero_anterior = 0;
if ($result_datos && $result_datos->num_rows > 0) {
    $tmp = $result_datos->fetch_assoc();
    $numero_anterior = intval($tmp['numero_anterior']);
    $result_datos->free();
}

// ==========================
// VALIDACIÓN: número en uso
// ==========================
// 1) En facturas (misma secuencia + mismo number)
$q_exist_f = "
    SELECT facturas_id, estado, fecha
    FROM facturas
    WHERE secuencia_facturacion_id = '$secuencia_facturacion_id'
      AND number = '$siguiente'
    LIMIT 1
";
$resF = $mysqli->query($q_exist_f);
if ($resF && $resF->num_rows > 0) {
    $rowF = $resF->fetch_assoc();
    $estado_txt = estadoFacturaTexto($rowF['estado']);
    $resF->free();
    respond(false, "NUMBER_IN_USE", "Warning",
        "El número $prefijo".rellenarDigitos($siguiente, intval($relleno))." ya está utilizado en FACTURAS (estado: $estado_txt). No se puede asignar.",
        ["where" => "facturas", "estado" => intval($rowF['estado']), "estado_texto" => $estado_txt]
    );
}

// 2) En facturas_grupal (misma secuencia + mismo number)
$q_exist_g = "
    SELECT facturas_grupal_id, estado, fecha
    FROM facturas_grupal
    WHERE secuencia_facturacion_id = '$secuencia_facturacion_id'
      AND number = '".$mysqli->real_escape_string((string)$siguiente)."'
    LIMIT 1
";
$resG = $mysqli->query($q_exist_g);
if ($resG && $resG->num_rows > 0) {
    $rowG = $resG->fetch_assoc();
    $estado_txt = estadoFacturaTexto($rowG['estado']);
    $resG->free();
    respond(false, "NUMBER_IN_USE", "Warning",
        "El número $prefijo".rellenarDigitos($siguiente, intval($relleno))." ya está utilizado en FACTURAS GRUPAL (estado: $estado_txt). No se puede asignar.",
        ["where" => "facturas_grupal", "estado" => intval($rowG['estado']), "estado_texto" => $estado_txt]
    );
}

// ==========================
// Actualizar secuencia
// ==========================
$estado_final = $estado; // si no viene, ya está seteado arriba
$update = "
    UPDATE secuencia_facturacion 
    SET 
        cai = '$cai',
        prefijo = '$prefijo',
        relleno = '$relleno',
        incremento = '$incremento',
        siguiente = '$siguiente',
        rango_inicial = '$rango_inicial',
        rango_final = '$rango_final',
        fecha_limite = '$fecha_limite',
        comentario = '$comentario',
        activo = '$estado_final',
        documento_id = '$documento_id'
    WHERE secuencia_facturacion_id = '$secuencia_facturacion_id'
";
$q_upd = $mysqli->query($update);
if (!$q_upd) {
    respond(false, "DB_ERROR", "Error", "No se pudo modificar la secuencia: ".$mysqli->error);
}

// ==========================
// Historial
// ==========================
$historial_numero   = historial();
$estado_historial   = "Modificar";
$observacion_hist   = "Se ha modificado el número de la secuencia de facturación con el prefijo: $prefijo y rangos $rango_inicial a $rango_final, número anterior: $numero_anterior, número nuevo: $siguiente";
$modulo             = "Secuencia Facturación";

$insert_hist = "
    INSERT INTO historial
    VALUES (
        '$historial_numero',
        '0',
        '0',
        '$modulo',
        '$secuencia_facturacion_id',
        '0',
        '0',
        '$hoy',
        '$estado_historial',
        '$observacion_hist',
        '$usuario',
        '$fecha_registro_now'
    )
";
if (!$mysqli->query($insert_hist)) {
    respond(false, "DB_ERROR", "Error", "No se pudo escribir el historial: ".$mysqli->error);
}

// OK
respond(true, "OK", "Success", "Registro modificado correctamente.", [
    "data" => [
        "secuencia_facturacion_id" => $secuencia_facturacion_id,
        "numero_anterior"          => $numero_anterior,
        "numero_nuevo"             => $siguiente,
        "prefijo"                  => $prefijo,
        "relleno"                  => intval($relleno)
    ]
]);