<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli(); 

$tipo_paciente = isset($_POST['tipo_paciente']) ? $_POST['tipo_paciente'] : '1';

// CORREGIDO: Filtrar por tipo_paciente
$consulta = "SELECT p.pacientes_id, 
			CONCAT(p.nombre,' ',p.apellido) AS paciente, 
			p.identidad, 
			p.expediente, 
			p.email
	FROM pacientes AS p
	WHERE p.tipo_paciente_id = '$tipo_paciente' AND p.estado = 1
	ORDER BY p.nombre ASC";
	
$result = $mysqli->query($consulta);	

$arreglo = array();

while($data = $result->fetch_assoc()){				
	$arreglo["data"][] = $data;		
}

echo json_encode($arreglo);

$result->free();
$mysqli->close();