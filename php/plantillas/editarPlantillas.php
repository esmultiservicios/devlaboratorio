<?php
session_start();   
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli(); 

//CONSULTA LOS DATOS DE LA ENTIDAD CORPORACION
$plantillas_id = $_POST['plantillas_id'];

$consulta = "SELECT p.plantillas_id AS 'plantillas_id', p.atenciones_id AS 'atenciones_id', a.nombre AS 'atencion', p.asunto As 'asunto', p.descripcion AS 'descripcion'
	FROM plantillas As p
	INNER JOIN atenciones AS a
	ON p.atenciones_id = a.atenciones_id
	WHERE plantillas_id = '$plantillas_id'
	ORDER BY a.nombre";
$result = $mysqli->query($consulta);	

$atencion_id = "";
$atencion = "";
$asunto = "";
$descripcion = "";

if($result->num_rows>0){
	$consulta = $result->fetch_assoc();

	$atencion_id = $consulta['atenciones_id'];
	$atencion = $consulta['atencion'];
	$asunto = $consulta['asunto'];
	$descripcion = $consulta['descripcion'];	
}
 
$datos = array(
	 0 => $atencion_id, 
 	 1 => $atencion,	
	 2 => $asunto, 	
 	 3 => $descripcion,	 
);	
	
echo json_encode($datos);

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>