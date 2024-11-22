<?php	
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$pacientes_id  = $_POST['pacientes_id'];

$query = "SELECT nombre, apellido, edad, identidad, genero, telefono1, telefono2, localidad, email
	FROM pacientes
	WHERE pacientes_id = '$pacientes_id'";
$result = $mysqli->query($query) or die($mysqli->error);

$nombre = "";
$apellido = "";
$identidad = "";
$genero = "";
$telefono1 = "";
$telefono2 = "";
$localidad = "";
$number = "";
$edad = "";

if($result->num_rows>=0){	
	$valores2 = $result->fetch_assoc();

	$nombre = $valores2['nombre'];
	$apellido = $valores2['apellido'];	
	$identidad = $valores2['identidad'];
	$edad = $valores2['edad'];
	$genero = $valores2['genero'];
	$telefono1 = $valores2['telefono1'];	
	$telefono2 = $valores2['telefono2'];
	$localidad = $valores2['localidad'];
	$email = $valores2['email'];	
}

$datos = array(
	0 => $nombre,
	1 => $apellido,	
	2 => $identidad,
	3 => $edad,
	4 => $telefono1,	
	5 => $genero,
	6 => $localidad,
	7 => $email,
	8 => $telefono2
);	

echo json_encode($datos);