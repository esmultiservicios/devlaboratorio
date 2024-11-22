<?php
include('../funtions.php');

session_start(); 	
//CONEXION A DB
$mysqli = connect_mysqli();

date_default_timezone_set('America/Tegucigalpa');
$fecha_sistema = date("Y-m-d");

//CONSULTAR USUARIOS
$query = "SELECT COUNT(muestras_id) AS 'total' 
     FROM muestras 
	 WHERE estado = 0";
$result = $mysqli->query($query);	 

$consulta2=$result->fetch_assoc();

$total = $consulta2['total'];  

echo number_format($total);

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>