<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli(); 

$tipo_paciente = $_POST['tipo_paciente'];

//CONSULTA LOS DATOS DE LA ENTIDAD CORPORACION
$consulta = "SELECT p.pacientes_id, CONCAT(p.nombre,' ',p.apellido) AS 'paciente', p.identidad AS 'identidad', p.expediente AS 'expediente', p.email As 'email'
	FROM pacientes AS p
	GROUP BY p.pacientes_id";
$result = $mysqli->query($consulta);	

$arreglo = array();

while($data = $result->fetch_assoc()){				
	$arreglo["data"][] = $data;		
}

echo json_encode($arreglo);

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>