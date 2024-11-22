<?php
session_start();   
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli(); 

$plantillas_id = $_POST['plantillas_id'];

$query = "SELECT descripcion
	FROM plantillas
	WHERE plantillas_id = '$plantillas_id'";
$result = $mysqli->query($query) or die($mysqli->error);			  

$descripcion = "";

if($result->num_rows>0){
	$consulta = $result->fetch_assoc();
	$descripcion = $consulta['descripcion'];
}

$datos = array(
	 0 => $descripcion, 	 
);	
	
echo json_encode($datos);

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>