<?php
session_start();   
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli(); 

//CONSULTA LOS DATOS DE LA ENTIDAD CORPORACION
$consulta = "SELECT h.nombre AS 'hospital', ap.administrador_precios_id AS 'administrador_precios_id', ap.precio AS 'precio', ap.hospitales_id AS 'hospitales_id'
FROM administrador_precios AS ap
INNER JOIN hospitales AS h
ON ap.hospitales_id = h.hospitales_id
ORDER BY h.nombre ASC";
$result = $mysqli->query($consulta);	

$arreglo = array();

while($data = $result->fetch_assoc()){				
	$arreglo["data"][] = $data;		
}
 
echo json_encode($arreglo);

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>