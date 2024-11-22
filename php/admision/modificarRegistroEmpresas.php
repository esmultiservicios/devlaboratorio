<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$pacientes_id = $_POST['pacientes_id'];
$nombre = cleanString($_POST['empresa']);
$apellido = "";
$identidad = $_POST['rtn'];

//CONSULTAR IDENTIDAD DEL USUARIO
if($identidad == 0){
	$flag_identidad = true;
	while($flag_identidad){
	   $d=rand(1,99999999);
	   $query_identidadRand = "SELECT pacientes_id 
	       FROM pacientes 
		   WHERE identidad = '$d'";
	   $result_identidad = $mysqli->query($query_identidadRand);
	   if($result_identidad->num_rows==0){
		  $identidad = $d;
		  $flag_identidad = false;
	   }else{
		  $flag_identidad = true;
	   }		
	}
}

$fecha_nacimiento = date("Y-m-d");
$edad = 0;
$telefono1 = $_POST['telefono1'];
$telefono2 = "";
$genero = "";
$departamento_id = 0;
$municipio_id = 0;
$localidad = cleanString($_POST['direccion']);
$correo = strtolower(cleanString($_POST['correo']));
$fecha = date("Y-m-d");
$religion_id = 0;
$profesion_id = 0;
$paciente_tipo = 2;//1. CLIENTE 2. EMPRESA
$usuario = $_SESSION['colaborador_id'];
$estado = 1; //1. Activo 2. Inactivo
$fecha_registro = date("Y-m-d H:i:s");

//ACTUALIZAMOS LOS REGISTROS DE LA EMPRESA
$update = "UPDATE pacientes 
	SET 
		 nombre = '$nombre'
		,identidad = '$identidad'
		,localidad = '$localidad'
		,telefono1 = '$telefono1'
		,email = '$correo'
	WHERE pacientes_id = '$pacientes_id'";

$query = $mysqli->query($update);

if($query){
	$datos = array(
		0 => "Modificado", 
		1 => "Registro Modificado Correctamente", 
		2 => "success",
		3 => "btn-primary",
		4 => "formulario_admision_empresas",
		5 => "Registro",
		6 => "formEmpresas",
		7 => "modal_admision_empesas",
		8 => "",
		9 => "Guardar",
	);
}else{
	$datos = array(
		0 => "Error", 
		1 => "No se puedo modificar este registro, los datos son incorrectos por favor corregir", 
		2 => "error",
		3 => "btn-danger",
		4 => "",
		5 => "",			
	);
}

echo json_encode($datos);
?>