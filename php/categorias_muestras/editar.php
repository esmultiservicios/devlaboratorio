<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$categoria_id = $_POST['categoria_id'];

//CONSULTAR REGISTROS
$consulta = "SELECT * FROM categoria 
    WHERE categoria_id = $categoria_id";
$result = $mysqli->query($consulta);

$nombre = "";
$tiempo = "";

if($result->num_rows>0){
	$consulta2 = $result->fetch_array();
	$nombre = $consulta2[1];
	$tiempo = $consulta2[2];	
}

$datos = array(
	0 => $nombre, 
    1 => $tiempo,
    2 => $categoria_id,																
);
echo json_encode($datos);

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>