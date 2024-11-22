<?php	
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$hospitales_id = $_POST['hospitales_id'];

$query = "SELECT nombre
	FROM hospitales
	WHERE hospitales_id  = '$hospitales_id '";
$result = $mysqli->query($query) or die($mysqli->error);

$nombre = "";

if($result->num_rows>=0){	
	$valores2 = $result->fetch_assoc();

	$nombre = $valores2['nombre'];
}

$datos = array(
	0 => $nombre
);	

echo json_encode($datos);