<?php
include('../funtions.php');	

//CONEXION A DB
session_start(); 
$mysqli = connect_mysqli();

date_default_timezone_set('America/Tegucigalpa');

//CONSULTAR USUARIOS 
$query = "SELECT COUNT(expediente) AS 'total' 
     FROM pacientes 
	 WHERE estado = 1 AND tipo_paciente_id = 2";
$result = $mysqli->query($query);	 

$consulta2=$result->fetch_assoc(); 

$total = $consulta2['total'];  

echo number_format($total);

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>