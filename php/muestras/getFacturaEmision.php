<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();
 
$muestras_id = $_POST['muestras_id'];

$query = "SELECT facturas_id 
    FROM facturas 
	WHERE muestras_id = '$muestras_id' AND estado = 2";
$result = $mysqli->query($query);   

$facturas_id = "";

if($result->num_rows>0){
    $consulta2 = $result->fetch_assoc(); 
    $facturas_id = $consulta2['facturas_id'];
}

echo $facturas_id;

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN