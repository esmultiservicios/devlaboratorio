<?php
session_start();   
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();
header('Content-Type: application/json');   

$fecha_registro = date('Y-m-d');
$tipo_muestra = $_POST['tipo_muestra'];

if($tipo_muestra == ""){
	$where = "WHERE CAST(c.fecha_cita AS DATE) >= '$fecha_registro'";
}else{
	$where = "WHERE CAST(c.fecha_cita AS DATE) >= '$fecha_registro' AND m.tipo_muestra_id = '$tipo_muestra'";
}

$sql = "SELECT c.calendario_id AS 'calendario_id', m.number AS 'muestra', c.fecha_cita AS 'start', c.fecha_cita_end AS 'end', c.color AS 'color', CONCAT(p.nombre, ' ', p.apellido) AS 'cliente'
	FROM calendario AS c
	INNER JOIN pacientes AS p
	ON c.pacientes_id = p.pacientes_id
	INNER JOIN muestras AS m
	ON c.muestras_id = m.muestras_id
	".$where;	
$result = $mysqli->query($sql);		  

$events = array();

while ($row = $result->fetch_assoc()) {
	$e = array();
	$e['id'] = $row['calendario_id'];
	$e['title'] = $row['muestra']."-".$row['cliente'];
	$e['start'] = $row['start'];
	$e['end'] = $row['end'];
	$e['color'] = $row['color'];

	array_push($events, $e);
}     

echo json_encode($events);

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN   
?>