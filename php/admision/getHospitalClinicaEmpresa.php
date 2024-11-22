<?php
session_start(); 
include('../funtions.php');
	
//CONEXION A DB
$mysqli = connect_mysqli(); 

$pacientes_id  = $_POST['pacientes_id'];

//CONSULTA LOS DATOS DE LA ENTIDAD CORPORACION
$consulta = "SELECT hospitales_id
FROM hospitales
WHERE pacientes_id = 'pacientes_id'";

$result = $mysqli->query($consulta);	
$hospital = "";

if($result->num_rows>0){
	$consulta2 = $result->fetch_assoc();
	$hospital = $consulta2['hospitales_id'];
}

echo $hospital;

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>