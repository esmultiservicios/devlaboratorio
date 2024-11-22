<?php
session_start();
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

$query = "SELECT hospitales_id
  FROM hospitales
	WHERE nombre = 'Clinicas'";
$result = $mysqli->query($query);
$consulta2 = $result->fetch_assoc();

$hospitales_id = $consulta2['hospitales_id'];

echo $hospitales_id;

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>
