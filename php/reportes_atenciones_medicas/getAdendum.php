<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$atencion_id = $_POST['atencion_id'];

//CONSULTA LOS DATOS DE LA ENTIDAD CORPORACION
$consulta = "SELECT descripcion
	FROM adendum
	WHERE atencion_id = '$atencion_id'";
$result = $mysqli->query($consulta);	

$descripcion = "";

if($result->num_rows>0){
	$consulta2 = $result->fetch_assoc();
	$descripcion = $consulta2['descripcion'];
}

$datos = array(
	 0 => $descripcion,  	 
);	
	
echo json_encode($datos);

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>       