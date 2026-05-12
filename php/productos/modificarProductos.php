<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

include "../funtions.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function responder($datos) {
	echo json_encode($datos);
	exit;
}

function post_int($key, $default = 0) {
	if (!isset($_POST[$key]) || trim($_POST[$key]) === "") {
		return $default;
	}
	return (int)$_POST[$key];
}

function post_float($key, $default = 0) {
	if (!isset($_POST[$key]) || trim($_POST[$key]) === "") {
		return $default;
	}

	$valor = trim($_POST[$key]);
	$valor = str_replace(",", "", $valor);

	return (float)$valor;
}

function post_string($key, $default = "") {
	if (!isset($_POST[$key])) {
		return $default;
	}
	return trim($_POST[$key]);
}

try {
	$mysqli = connect_mysqli();
	$mysqli->set_charset("utf8");

	if (!isset($_SESSION['colaborador_id'])) {
		responder(array(
			0 => "Error",
			1 => "La sesión ha expirado. Por favor, inicie sesión nuevamente.",
			2 => "error",
			3 => "btn-danger",
			4 => "",
			5 => "",
		));
	}

	$usuario = (int)$_SESSION['colaborador_id'];

	$productos_id = post_int('productos_id', 0);
	$nombre = cleanString(post_string('nombre'));
	$descripcion = cleanString(post_string('descripcion'));

	$cantidad = post_float('cantidad', 0);
	$precio_compra = post_float('precio_compra', 0);
	$precio_venta = post_float('precio_venta', 0);
	$precio_venta2 = post_float('precio_venta2', 0);
	$precio_venta3 = post_float('precio_venta3', 0);
	$precio_venta4 = post_float('precio_venta4', 0);

	$cantidad_minima = post_int('cantidad_minima', 0);
	$cantidad_maxima = post_int('cantidad_maxima', 0);
	$tipo_muestra_id = post_int('categoria_producto', 0);

	$estado = isset($_POST['producto_activo']) ? 1 : 2;
	$isv = isset($_POST['producto_isv_factura']) ? 1 : 2;

	$fecha_registro = date("Y-m-d H:i:s");
	$fecha = date("Y-m-d");

	if ($productos_id <= 0) {
		responder(array(
			0 => "Error",
			1 => "No se encontró el código del producto a modificar.",
			2 => "error",
			3 => "btn-danger",
			4 => "",
			5 => "",
		));
	}

	if ($nombre == "") {
		responder(array(
			0 => "Error",
			1 => "Debe ingresar el nombre del producto.",
			2 => "error",
			3 => "btn-danger",
			4 => "",
			5 => "",
		));
	}

	// Verificar que exista el producto
	$stmt_existe = $mysqli->prepare("
		SELECT productos_id 
		FROM productos 
		WHERE productos_id = ? 
		LIMIT 1
	");
	$stmt_existe->bind_param("i", $productos_id);
	$stmt_existe->execute();
	$result_existe = $stmt_existe->get_result();

	if ($result_existe->num_rows == 0) {
		responder(array(
			0 => "Error",
			1 => "El producto que intenta modificar no existe.",
			2 => "error",
			3 => "btn-danger",
			4 => "",
			5 => "",
		));
	}

	// Evitar duplicar nombre con otro producto
	$stmt_duplicado = $mysqli->prepare("
		SELECT productos_id 
		FROM productos 
		WHERE nombre = ? 
		AND productos_id <> ?
		LIMIT 1
	");
	$stmt_duplicado->bind_param("si", $nombre, $productos_id);
	$stmt_duplicado->execute();
	$result_duplicado = $stmt_duplicado->get_result();

	if ($result_duplicado->num_rows > 0) {
		responder(array(
			0 => "Error",
			1 => "Ya existe otro producto registrado con ese nombre.",
			2 => "error",
			3 => "btn-danger",
			4 => "",
			5 => "",
		));
	}

	$stmt = $mysqli->prepare("
		UPDATE productos
		SET
			nombre = ?,
			cantidad = ?,
			precio_compra = ?,
			precio_venta = ?,
			precio_venta2 = ?,
			precio_venta3 = ?,
			precio_venta4 = ?,
			estado = ?,
			isv = ?,
			descripcion = ?,
			cantidad_minima = ?,
			cantidad_maxima = ?,
			tipo_muestra_id = ?
		WHERE productos_id = ?
	");

	$stmt->bind_param(
		"sddddddiisiiii",
		$nombre,
		$cantidad,
		$precio_compra,
		$precio_venta,
		$precio_venta2,
		$precio_venta3,
		$precio_venta4,
		$estado,
		$isv,
		$descripcion,
		$cantidad_minima,
		$cantidad_maxima,
		$tipo_muestra_id,
		$productos_id
	);

	$stmt->execute();

	// HISTORIAL
	$historial_numero = historial();
	$estado_historial = "Editar";
	$observacion_historial = "Se ha modificado el producto: $nombre con codigo: $productos_id";
	$modulo = "Productos";
	$cero = 0;

	$stmt_historial = $mysqli->prepare("
		INSERT INTO historial
		VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
	");

	$stmt_historial->bind_param(
		"iiisiiisssis",
		$historial_numero,
		$cero,
		$cero,
		$modulo,
		$productos_id,
		$usuario,
		$cero,
		$fecha,
		$estado_historial,
		$observacion_historial,
		$usuario,
		$fecha_registro
	);

	$stmt_historial->execute();

	responder(array(
		0 => "Editado",
		1 => "Registro editado correctamente.",
		2 => "success",
		3 => "btn-primary",
		4 => "",
		5 => "Editar",
		6 => "Productos",
		7 => "modal_productos",
	));

} catch (Throwable $e) {
	responder(array(
		0 => "Error",
		1 => "No se pudo modificar el producto. Detalle técnico: " . $e->getMessage(),
		2 => "error",
		3 => "btn-danger",
		4 => "",
		5 => "",
	));
}