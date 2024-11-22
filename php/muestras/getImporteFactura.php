<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();
 
$facturas_id = $_POST['facturas_id'];

$query = "SELECT importe
    FROM facturas 
	WHERE facturas_id = '$facturas_id'";
$result = $mysqli->query($query);   
$consulta2 = $result->fetch_assoc(); 

$importe = $consulta2['importe'];

echo $importe;

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>