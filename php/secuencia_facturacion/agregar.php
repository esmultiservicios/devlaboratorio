<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
include "../funtions.php";

/* =========================
 * Helpers de respuesta JSON
 * ========================= */
function respond($ok, $code, $title, $message, $extra = []) {
	echo json_encode(array_merge([
		"status"  => (bool)$ok,            // true | false
		"code"    => (string)$code,        // OK | INVALID_INPUT | NO_SESSION | SEQ_ACTIVE_EXISTS | OUT_OF_RANGE | DATE_INVALID | DB_ERROR | NOT_FOUND
		"title"   => (string)$title,       // Success | Error | Warning
		"message" => (string)$message
	], $extra), JSON_UNESCAPED_UNICODE);
	exit;
}

/* ==============
 * Conexión a DB
 * ============== */
$mysqli = connect_mysqli();
if ($mysqli->connect_errno) {
	respond(false, "DB_CONN_ERROR", "Error", "No se pudo conectar a la base de datos.");
}

/* ==========
 * Entradas
 * ========== */
$fecha_registro = date("Y-m-d H:i:s");
$hoy            = date("Y-m-d");

$empresa        = (isset($_POST['empresa']) && $_POST['empresa'] !== "") ? intval($_POST['empresa']) : 1;
$documento_id   = (isset($_POST['documento_id']) && $_POST['documento_id'] !== "") ? intval($_POST['documento_id']) : 1;

$cai            = isset($_POST['cai']) ? trim($_POST['cai']) : '';
$prefijo        = isset($_POST['prefijo']) ? trim($_POST['prefijo']) : '';
$relleno        = isset($_POST['relleno']) ? intval($_POST['relleno']) : 0;
$incremento     = isset($_POST['incremento']) ? intval($_POST['incremento']) : 1;
$siguiente      = isset($_POST['siguiente']) ? intval($_POST['siguiente']) : 0;
$rango_inicial  = isset($_POST['rango_inicial']) ? intval($_POST['rango_inicial']) : 0;
$rango_final    = isset($_POST['rango_final']) ? intval($_POST['rango_final']) : 0;

$fecha_activacion = isset($_POST['fecha_activacion']) ? $_POST['fecha_activacion'] : '';
$fecha_limite     = isset($_POST['fecha_limite'])     ? $_POST['fecha_limite']     : '';

$usuario        = isset($_SESSION['colaborador_id']) ? intval($_SESSION['colaborador_id']) : 0;
$comentario     = "";
$estado         = 0;
if (isset($_POST['estado'])) {
	$estado = ($_POST['estado'] === "") ? 0 : intval($_POST['estado']);
}

/* =================
 * Validaciones base
 * ================= */
if ($usuario <= 0) {
	respond(false, "NO_SESSION", "Error", "Sesión no válida. Inicie sesión nuevamente.");
}

if ($empresa <= 0 || $documento_id <= 0 || $relleno <= 0 || $incremento <= 0 || $siguiente <= 0 || $rango_inicial <= 0 || $rango_final <= 0 || $prefijo === '') {
	respond(false, "INVALID_INPUT", "Error", "Parámetros inválidos o incompletos.");
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_activacion) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_limite)) {
	respond(false, "INVALID_INPUT", "Error", "Fechas inválidas (use formato YYYY-MM-DD).");
}

if (strtotime($fecha_activacion) === false || strtotime($fecha_limite) === false) {
	respond(false, "INVALID_INPUT", "Error", "Fechas inválidas.");
}

if ($rango_inicial > $rango_final) {
	respond(false, "OUT_OF_RANGE", "Error", "El rango inicial no puede ser mayor que el rango final.");
}

if ($siguiente < $rango_inicial || $siguiente > $rango_final) {
	respond(false, "OUT_OF_RANGE", "Warning", "El número 'siguiente' debe estar entre el rango inicial y final.");
}

if (strtotime($fecha_activacion) > strtotime($fecha_limite)) {
	respond(false, "DATE_INVALID", "Error", "La fecha de activación no puede ser mayor que la fecha límite.");
}

/* ===========================================================
 * Verificar que NO exista otra secuencia activa para ese doc
 * (por empresa + documento)
 * =========================================================== */
$qCheck = "
	SELECT secuencia_facturacion_id
	FROM secuencia_facturacion
	WHERE activo = 1
	  AND empresa_id = '$empresa'
	  AND documento_id = '$documento_id'
	LIMIT 1
";
$resCheck = $mysqli->query($qCheck);
if ($resCheck && $resCheck->num_rows > 0) {
	$resCheck->free();
	respond(false, "SEQ_ACTIVE_EXISTS", "Error", "Solo se puede tener una secuencia activa por documento y empresa.");
}

/* ============================================
 * Preparación de números con relleno (display)
 * ============================================ */
$rango_inicial_pad = str_pad((string)$rango_inicial, $relleno, "0", STR_PAD_LEFT);
$rango_final_pad   = str_pad((string)$rango_final,   $relleno, "0", STR_PAD_LEFT);
$siguiente_pad     = str_pad((string)$siguiente,     $relleno, "0", STR_PAD_LEFT);

/* ==================================================
 * Insertar nueva secuencia (correlativo generado)
 * ================================================== */
$correlativo = correlativo('secuencia_facturacion_id ', 'secuencia_facturacion');

$insert = "
	INSERT INTO secuencia_facturacion
	VALUES(
		'$correlativo',
		'$empresa',
		'$cai',
		'$prefijo',
		'$relleno',
		'$incremento',
		'$siguiente',
		'$rango_inicial_pad',
		'$rango_final_pad',
		'$fecha_activacion',
		'$fecha_limite',
		'$comentario',
		'$estado',
		'$usuario',
		'$fecha_registro',
		'$documento_id'
	)
";
$qInsert = $mysqli->query($insert);
if (!$qInsert) {
	respond(false, "DB_ERROR", "Error", "No se pudo almacenar el registro: ".$mysqli->error);
}

/* ==========
 * Historial
 * ========== */
$historial_numero   = historial();
$estado_historial   = "Agregar";
$observacion_hist   = "Se ha agregado una nueva secuencia de facturación con el prefijo: $prefijo y rangos desde $rango_inicial_pad a $rango_final_pad";
$modulo             = "Secuencia Facturación";

$insertHist = "
	INSERT INTO historial
	VALUES(
		'$historial_numero',
		'0',
		'0',
		'$modulo',
		'$correlativo',
		'0',
		'0',
		'$hoy',
		'$estado_historial',
		'$observacion_hist',
		'$usuario',
		'$fecha_registro'
	)
";
if (!$mysqli->query($insertHist)) {
	respond(false, "DB_ERROR", "Error", "El registro fue creado, pero no se pudo escribir el historial: ".$mysqli->error, [
		"data" => [
			"secuencia_facturacion_id" => $correlativo,
			"empresa"                  => $empresa,
			"documento_id"             => $documento_id,
			"prefijo"                  => $prefijo,
			"relleno"                  => $relleno,
			"siguiente"                => $siguiente,
			"siguiente_display"        => $prefijo.$siguiente_pad,
			"rango_inicial_display"    => $prefijo.$rango_inicial_pad,
			"rango_final_display"      => $prefijo.$rango_final_pad
		]
	]);
}

/* =====
 * OK
 * ===== */
respond(true, "OK", "Success", "Registro almacenado correctamente.", [
	"data" => [
		"secuencia_facturacion_id" => $correlativo,
		"empresa"                  => $empresa,
		"documento_id"             => $documento_id,
		"prefijo"                  => $prefijo,
		"relleno"                  => $relleno,
		"siguiente"                => $siguiente,
		"siguiente_display"        => $prefijo.$siguiente_pad,
		"rango_inicial_display"    => $prefijo.$rango_inicial_pad,
		"rango_final_display"      => $prefijo.$rango_final_pad,
		"fecha_activacion"         => $fecha_activacion,
		"fecha_limite"             => $fecha_limite,
		"estado"                   => $estado
	]
]);