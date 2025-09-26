<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
include "../funtions.php";

/* ========= Helper JSON ========= */
function respond($ok, $code, $title, $message, $extra = []) {
    echo json_encode(array_merge([
        "status"  => (bool)$ok,
        "code"    => (string)$code,   // OK | INVALID_INPUT | NO_SESSION | NOT_FOUND | DB_ERROR
        "title"   => (string)$title,  // Success | Error | Warning
        "message" => (string)$message
    ], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

/* ========= Conexión ========= */
$mysqli = connect_mysqli();
if ($mysqli->connect_errno) {
    respond(false, "DB_CONN_ERROR", "Error", "No se pudo conectar a la base de datos.");
}

/* ========= Entrada ========= */
$secuencia_facturacion_id = isset($_POST['secuencia_facturacion_id']) ? (int)$_POST['secuencia_facturacion_id'] : 0;
if ($secuencia_facturacion_id <= 0){
    respond(false, "INVALID_INPUT", "Error", "ID de secuencia inválido.");
}

/* ========= Consulta ========= */
$q = "
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
$r = $mysqli->query($q);
if (!$r){
    respond(false, "DB_ERROR", "Error", "No se pudo consultar la secuencia: ".$mysqli->error);
}
if ($r->num_rows === 0){
    respond(false, "NOT_FOUND", "Error", "Secuencia de facturación no encontrada.");
}

$row = $r->fetch_assoc();
$r->free();

/* ========= Respuesta ========= */
respond(true, "OK", "Success", "Secuencia cargada correctamente.", [
    "data" => [
        "secuencia_facturacion_id" => (int)$row["secuencia_facturacion_id"],
        "empresa_id"               => (int)$row["empresa_id"],
        "cai"                      => (string)$row["cai"],
        "prefijo"                  => (string)$row["prefijo"],
        "relleno"                  => (int)$row["relleno"],
        "incremento"               => (int)$row["incremento"],
        "siguiente"                => (int)$row["siguiente"],
        "rango_inicial"            => (string)$row["rango_inicial"],  // viene almacenado con relleno (char)
        "rango_final"              => (string)$row["rango_final"],    // idem
        "fecha_activacion"         => (string)$row["fecha_activacion"],
        "fecha_limite"             => (string)$row["fecha_limite"],
        "comentario"               => (string)$row["comentario"],
        "activo"                   => (int)$row["activo"],
        "usuario"                  => (int)$row["usuario"],
        "fecha_registro"           => (string)$row["fecha_registro"],
        "documento_id"             => (int)$row["documento_id"]
    ]
]);

/* (no se ejecuta por respond) */
$mysqli->close();