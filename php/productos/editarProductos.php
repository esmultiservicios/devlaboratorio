<?php	
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$productos_id  = $_POST['productos_id'];

$query = "SELECT p.productos_id AS 'productos_id', p.nombre AS 'producto', p.descripcion AS 'descripcion', p.concentracion AS 'concentracion', p.cantidad AS 'cantidad', m.nombre AS 'medida', p.medida_id AS 'medida_id', p.almacen_id AS 'almacen_id', p.precio_compra AS 'precio_compra', p.precio_venta AS 'precio_venta', a.nombre AS 'almacen', u.nombre AS 'ubicacion', cp.nombre AS 'categoria', cp.categoria_producto_id AS 'categoria_producto_id', (CASE WHEN p.estado = '1' THEN 'Activo' ELSE 'Inactivo' END) AS 'estado', (CASE WHEN p.isv = '1' THEN 'Sí' ELSE 'No' END) AS 'isv', p.estado AS 'estado_producto', p.isv AS 'isv_venta', p.cantidad_minima AS 'cantidad_minima', p.cantidad_maxima AS 'cantidad_maxima', p.precio_venta2 AS 'precio_venta2', p.precio_venta3 AS 'precio_venta3', p.precio_venta4 AS 'precio_venta4', cp.nombre AS 'categoria_producto', p.tipo_muestra_id
	FROM productos AS p
	INNER JOIN medida AS m
	ON p.medida_id = m.medida_id
	INNER JOIN almacen AS a
	ON p.almacen_id = a.almacen_id
	INNER JOIN ubicacion AS u
	ON a.ubicacion_id = u.ubicacion_id
	INNER JOIN categoria_producto AS cp
	ON p.categoria_producto_id = cp.categoria_producto_id
	WHERE productos_id = '$productos_id'
	GROUP BY p.productos_id";
$result = $mysqli->query($query) or die($mysqli->error);

$producto = "";
$producto = "";
$categoria = "";
$concentracion = "";
$medida = "";
$almacen = "";
$cantidad = "";
$precio_compra = "";
$precio_venta = "";
$precio_venta2 = "";
$precio_venta3 = "";
$precio_venta4 = "";
$descripcion = "";
$estado = "";
$isv = "";
$cantidad_minima = "";
$cantidad_maxima = "";
$categoria_producto = "";
$tipo_muestra_id = "";

if($result->num_rows>=0){	
	$valores2 = $result->fetch_assoc();

	$producto = $valores2['producto'];
	$categoria = $valores2['categoria_producto_id'];
	$concentracion = $valores2['concentracion'];
	$medida = $valores2['medida_id'];
	$almacen = $valores2['almacen_id'];
	$cantidad = $valores2['cantidad'];
	$precio_compra = $valores2['precio_compra'];
	$precio_venta = $valores2['precio_venta'];
	$precio_venta2 = $valores2['precio_venta2'];
	$precio_venta3 = $valores2['precio_venta3'];
	$precio_venta4 = $valores2['precio_venta4'];	
	$descripcion = $valores2['descripcion'];
	$estado = $valores2['estado_producto'];
	$isv = $valores2['isv_venta'];
	$cantidad_minima = $valores2['cantidad_minima'];
	$cantidad_maxima = $valores2['cantidad_maxima'];
	$categoria_producto = $valores2['categoria_producto'];	
	$tipo_muestra_id = $valores2['tipo_muestra_id'];
}

$datos = array(
	0 => $producto,
	1 => $categoria,
	2 => $concentracion,
	3 => $medida,
	4 => $almacen,
	5 => $cantidad,
	6 => $precio_compra,
	7 => $precio_venta,
	8 => $descripcion,
	9 => $estado,
	10 => $isv,
	11 => $cantidad_minima,
	12 => $cantidad_maxima,
	13 => $precio_venta2,
	14 => $precio_venta3,
	15 => $precio_venta4,	
	16 => $categoria_producto,
	17 => $tipo_muestra_id,	
);	

echo json_encode($datos);