<?php
session_start();   
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

$categoria_id = $_POST['categoria_id'];

//CONSULTAR NOMBRE
$consulta_nombre = "SELECT nombre
     FROM categoria
	WHERE categoria_id = categoria_id";
$result = $mysqli->query($consulta_nombre);	

$nombre = "";

if($result->num_rows>0){
     $consulta_nombre1 = $result->fetch_assoc();
     $nombre = $consulta_nombre1['nombre'];
}

echo $nombre;

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>