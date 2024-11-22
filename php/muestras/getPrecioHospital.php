<?php
session_start();   
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

$hospitales_id = $_POST['hospitales_id'];

$consulta = "SELECT ap.precio AS 'precio'
	FROM administrador_precios AS ap
	WHERE ap.hospitales_id = '$hospitales_id'";
$result = $mysqli->query($consulta) or die($mysqli->error);

$precio = "";

if($result->num_rows>0){
	$valores2 = $result->fetch_assoc();

	$precio = $valores2['precio'];
}

$datos = array(
	0 => $precio,	
);	

echo json_encode($datos);

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>