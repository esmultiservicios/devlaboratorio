<?php
session_start();   
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli(); 

//CONSULTA LOS DATOS DE LA ENTIDAD CORPORACION
$consulta = "SELECT p.plantillas_id AS 'plantillas_id', p.atenciones_id AS 'atenciones_id', a.nombre AS 'atencion', p.asunto As 'asunto', p.descripcion AS 'descripcion'
	FROM plantillas As p
	INNER JOIN atenciones AS a
	ON p.atenciones_id = a.atenciones_id
	ORDER BY p.atenciones_id ";
$result = $mysqli->query($consulta);	

$arreglo = array();

while($data = $result->fetch_assoc()){				
	$arreglo["data"][] = $data;		
}
 
echo json_encode($arreglo);

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>