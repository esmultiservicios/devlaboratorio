<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$muestras_id = $_POST['muestras_id'];

//CONSULTA LOS DATOS DE LA ENTIDAD CORPORACION
$consulta = "SELECT number
	FROM muestras
	WHERE muestras_id = '$muestras_id'";
$result = $mysqli->query($consulta);	

$numero = "";

if($result->num_rows>0){
	$consulta2 = $result->fetch_assoc();
	$numero = $consulta2['number'];
}

$datos = array(
	 0 => $numero,  	 
);	
	
echo json_encode($datos);

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>       