<?php
session_start();
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

$query = "SELECT colaborador_id
  FROM colaboradores
	WHERE nombre = 'Clinicas' AND apellido = 'Privadas'";
$result = $mysqli->query($query);
$consulta2 = $result->fetch_assoc();

$colaborador_id = $consulta2['colaborador_id'];

echo $colaborador_id;

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>
