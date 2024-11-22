<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$pacientes_id = $_POST['pacientes_id'];
$estado = 1; //1. Activo 2. Inactivo
$fecha_registro = date("Y-m-d H:i:s");
$usuario = $_SESSION['colaborador_id'];	

$consulta_expediente = "SELECT pacientes_id,  nombre, apellido, identidad, telefono1, telefono2, fecha_nacimiento, fecha, email, genero, localidad,
(CASE WHEN estado = '1' THEN 'Activo' ELSE 'Inactivo' END) AS 'estado',
(CASE WHEN expediente = '0' THEN 'TEMP' ELSE expediente END) AS 'expediente', departamento_id AS 'departamento', municipio_id AS 'municipio', identidad, religion_id, profesion_id, edad, tipo_paciente_id
	FROM pacientes
	WHERE pacientes_id = '$pacientes_id'";
$result = $mysqli->query($consulta_expediente);   

$expediente = "";
$nombre = "";
$apellido = "";
$sexo = "";
$telefono1 = "";
$telefono2 = "";
$fecha_nacimiento = "";
$correo = "";
$fecha = "";
$localidad = "";
$departamento = "";
$municipio = "";
$identidad = "";
$religion_id = "";
$profesion_id = "";
$edad = "";
$tipo_paciente_id = "";
	
if($result->num_rows>0){
	$consulta_expediente1 = $result->fetch_assoc();
	$expediente = $consulta_expediente1['expediente'];
	$nombre = $consulta_expediente1['nombre'];
	$apellido = $consulta_expediente1['apellido'];
	$sexo = $consulta_expediente1['genero'];
	$telefono1 = $consulta_expediente1['telefono1'];
	$telefono2 = $consulta_expediente1['telefono2'];
	$fecha_nacimiento = $consulta_expediente1['fecha_nacimiento'];
	$correo = $consulta_expediente1['email'];
	$fecha = $consulta_expediente1['fecha'];
	$localidad = $consulta_expediente1['localidad'];	
	$departamento = $consulta_expediente1['departamento'];
	$municipio = $consulta_expediente1['municipio'];
	$identidad = $consulta_expediente1['identidad'];
	$religion_id = $consulta_expediente1['religion_id'];
	$profesion_id = $consulta_expediente1['profesion_id'];	
	$edad = $consulta_expediente1['edad'];
	$tipo_paciente_id = $consulta_expediente1['tipo_paciente_id'];
}


$datos = array(
	0 => $nombre, 
	1 => $apellido,	
	2 => $telefono1,
	3 => $telefono2,
	4 => $sexo,
	5 => $correo,
	6 => "",//AQUI IBA LA EDAD DE LA PERSONA					
	7 => $expediente,
	8 => $departamento,
	9 => $municipio,
	10 => $localidad,
	11 => $identidad,
	12 => $religion_id,
	13 => $profesion_id,
	14 => $edad,
	15 => $tipo_paciente_id,	
);
echo json_encode($datos);
?>