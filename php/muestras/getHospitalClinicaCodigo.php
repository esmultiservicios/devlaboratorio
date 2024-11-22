<?php
session_start();   
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

$muestras_id = $_POST['muestras_id'];

$consulta = "SELECT hospitales_id
	FROM muestras
	WHERE muestras_id = '$muestras_id'";
$result = $mysqli->query($consulta) or die($mysqli->error);

$hospitales_id = "";

if($result->num_rows>0){
	$valores2 = $result->fetch_assoc();

	$hospitales_id = $valores2['hospitales_id'];
}

$datos = array(
	0 => $hospitales_id,	
);	

echo json_encode($datos);

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>