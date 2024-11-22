<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$hospitales_id = $_POST['hospitales_id'];

$consulta = "SELECT precio
	FROM administrador_precios
	WHERE hospitales_id = '$hospitales_id'";
$result = $mysqli->query($consulta) or die($mysqli->error);			  

$precio = "";
if($result->num_rows>0){
	$consulta2 = $result->fetch_assoc();
	$precio = $consulta2['precio'];
}

$datos = array(
	0 => $precio,
);	

echo json_encode($datos);

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>