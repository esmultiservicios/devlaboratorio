<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
include "../funtions.php";

/* =========================
 * Helper JSON
 * ========================= */
function respond($ok, $code, $title, $message, $extra = []) {
	echo json_encode(array_merge([
		"status"  => (bool)$ok,                 // true | false
		"code"    => (string)$code,             // OK | INVALID_INPUT | NO_SESSION | NOT_FOUND | HAS_DEPENDENCIES | DB_ERROR | DB_CONN_ERROR
		"title"   => (string)$title,            // Eliminado | Error | Warning
		"message" => (string)$message
	], $extra), JSON_UNESCAPED_UNICODE);
	exit;
}

/* =========================
 * Conexión
 * ========================= */
$mysqli = connect_mysqli();
if ($mysqli->connect_errno) {
	respond(false, "DB_CONN_ERROR", "Error", "No se pudo conectar a la base de datos. Intente nuevamente o contacte al administrador.");
}

/* =========================
 * Entradas
 * ========================= */
$secuencia_facturacion_id = isset($_POST['secuencia_facturacion_id']) ? (int)$_POST['secuencia_facturacion_id'] : 0;
$comentario               = isset($_POST['comentario']) ? cleanStringStrtolower($_POST['comentario']) : '';
$usuario                  = isset($_SESSION['colaborador_id']) ? (int)$_SESSION['colaborador_id'] : 0;

$fecha_registro = date("Y-m-d H:i:s");
$hoy            = date("Y-m-d");

/* =========================
 * Validaciones
 * ========================= */
if ($usuario <= 0) {
	respond(false, "NO_SESSION", "Sesión expirada", "Su sesión no es válida o ha caducado. Inicie sesión nuevamente.");
}
if ($secuencia_facturacion_id <= 0) {
	respond(false, "INVALID_INPUT", "Dato faltante", "No se recibió el identificador de la secuencia a eliminar.");
}

/* =========================
 * Consultar la secuencia
 * ========================= */
$qSeq = "
	SELECT *
	FROM secuencia_facturacion
	WHERE secuencia_facturacion_id = '$secuencia_facturacion_id'
	LIMIT 1
";
$rSeq = $mysqli->query($qSeq);
if (!$rSeq) {
	respond(false, "DB_ERROR", "Error", "No se pudo consultar la secuencia: ".$mysqli->error);
}
if ($rSeq->num_rows === 0) {
	respond(false, "NOT_FOUND", "No encontrado", "La secuencia de facturación indicada no existe o ya fue eliminada.");
}
$seq = $rSeq->fetch_assoc();
$rSeq->free();

/* Guardar campos para historial */
$empresa          = $seq['empresa_id'];
$cai              = $seq['cai'];
$prefijo          = $seq['prefijo'];
$relleno          = $seq['relleno'];
$incremento       = $seq['incremento'];
$siguiente        = $seq['siguiente'];
$rango_inicial    = $seq['rango_inicial'];
$rango_final      = $seq['rango_final'];
$fecha_activacion = $seq['fecha_activacion'];
$fecha_limite     = $seq['fecha_limite'];
$activo           = $seq['activo'];

/* =========================
 * Dependencias en facturas
 * ========================= */
$qDep = "
	SELECT COUNT(*) AS total
	FROM facturas
	WHERE secuencia_facturacion_id = '$secuencia_facturacion_id'
";
$rDep = $mysqli->query($qDep);
if (!$rDep) {
	respond(false, "DB_ERROR", "Error", "No se pudo verificar si la secuencia tiene facturas asociadas: ".$mysqli->error);
}
$rowDep = $rDep->fetch_assoc();
$rDep->free();

if ((int)$rowDep['total'] > 0) {
	respond(false, "HAS_DEPENDENCIES", "No se puede eliminar", "Esta secuencia tiene ".$rowDep['total']." factura(s) asociada(s). No es posible eliminarla.");
}

/* =========================
 * Eliminar secuencia
 * ========================= */
$qDel  = "DELETE FROM secuencia_facturacion WHERE secuencia_facturacion_id = '$secuencia_facturacion_id' LIMIT 1";
$okDel = $mysqli->query($qDel);
if (!$okDel) {
	respond(false, "DB_ERROR", "Error", "Ocurrió un error al eliminar la secuencia: ".$mysqli->error);
}

/* =========================
 * Historial de secuencia
 * ========================= */
$correlativo_hist = correlativo('secuencia_facturacion_historial_id ', 'secuencia_facturacion_historial');

$qInsHist = "
	INSERT INTO secuencia_facturacion_historial
	VALUES(
		'$correlativo_hist',
		'$secuencia_facturacion_id',
		'$empresa',
		'$cai',
		'$prefijo',
		'$relleno',
		'$incremento',
		'$siguiente',
		'$rango_inicial',
		'$rango_final',
		'$fecha_activacion',
		'$fecha_limite',
		'$activo',
		'$usuario',
		'$comentario',
		'$fecha_registro'
	)
";
if (!$mysqli->query($qInsHist)) {
	respond(false, "DB_ERROR", "Guardado parcial", "La secuencia fue eliminada, pero no se pudo registrar el historial de secuencias: ".$mysqli->error, [
		"data" => [ "secuencia_facturacion_id" => $secuencia_facturacion_id ]
	]);
}

/* =========================
 * Historial general
 * ========================= */
$historial_numero   = historial();
$estado_historial   = "Eliminar";
$observacion_hist   = "Se eliminó la secuencia de facturación con el prefijo: $prefijo y rangos desde $rango_inicial a $rango_final";
$modulo             = "Secuencia Facturación";

$qInsLog = "
	INSERT INTO historial
	VALUES(
		'$historial_numero',
		'0',
		'0',
		'$modulo',
		'$correlativo_hist',
		'0',
		'0',
		'$hoy',
		'$estado_historial',
		'$observacion_hist',
		'$usuario',
		'$fecha_registro'
	)
";
if (!$mysqli->query($qInsLog)) {
	respond(false, "DB_ERROR", "Guardado parcial", "La secuencia fue eliminada, pero no se pudo registrar el historial general: ".$mysqli->error);
}

/* OK */
respond(true, "OK", "Eliminado", "Registro eliminado correctamente.");

$mysqli->close();