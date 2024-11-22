<?php	
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$administrador_precios_id = $_POST['administrador_precios_id'];

$query = "SELECT *
	FROM administrador_precios
	WHERE administrador_precios_id = '$administrador_precios_id'";
$result = $mysqli->query($query) or die($mysqli->error);

$hospitales_id = "";
$precio = "";

if($result->num_rows>=0){	
	$valores2 = $result->fetch_assoc();

	$hospitales_id = $valores2['hospitales_id'];
	$precio = $valores2['precio'];
}

$datos = array(
	0 => $hospitales_id,
	1 => $precio,
);	

echo json_encode($datos);