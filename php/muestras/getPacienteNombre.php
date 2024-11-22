<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();
 
$pacientes_id = $_POST['pacientes_id'];

$query = "SELECT CONCAT(nombre,' ',apellido) AS 'paciente' 
    FROM pacientes 
	WHERE pacientes_id = '$pacientes_id'";
$result = $mysqli->query($query);   
$consulta2 = $result->fetch_assoc(); 

$paciente = $consulta2['paciente'];

echo $paciente;

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>