<?php	
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$query = "SELECT limite
	FROM limite_muetras";
$result = $mysqli->query($query) or die($mysqli->error);

$limite = "";

if($result->num_rows>=0){	
	$valores2 = $result->fetch_assoc();

	$limite = $valores2['limite'];
}

$datos = array(
	0 => $limite,
);	

echo json_encode($datos);