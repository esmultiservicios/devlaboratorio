<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();
 
$muestras_id = $_POST['muestras_id'];

$query = "SELECT number
    FROM muestras 
	WHERE muestras_id = '$muestras_id'";
$result = $mysqli->query($query);   
$consulta2 = $result->fetch_assoc(); 

$numeroMuestra = $consulta2['number'];

echo $numeroMuestra;

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>