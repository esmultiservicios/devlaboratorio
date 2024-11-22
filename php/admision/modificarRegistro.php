<?php
session_start();
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

if(isset($_POST['cliente_admision'])){//COMPRUEBO SI LA VARIABLE ESTA DIFINIDA
	if($_POST['cliente_admision'] == ""){
		$cliente_admision = 0;
	}else{
		$cliente_admision = $_POST['cliente_admision'];
	}
}else{
	$cliente_admision = 0;
}

$pacientes_id = $_POST['pacientes_id'];
$nombre = cleanString($_POST['name']);
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

$fecha_nacimiento = $_POST['fecha_nac'];
$edad = $_POST['edad'];
$telefono1 = $_POST['telefono1'];
$telefono2 = $_POST['telefono2'];
$genero = $_POST['genero'];
$departamento_id = 0;
$municipio_id = 0;
$localidad = cleanString($_POST['direccion']);
$correo = strtolower(cleanString($_POST['correo']));
$fecha = date("Y-m-d");
$religion_id = 0;
$profesion_id = 0;
$paciente_tipo = 1;//1. CLIENTE 2. EMPRESA
$usuario = $_SESSION['colaborador_id'];
$estado = 1; //1. Activo 2. Inactivo
$fecha_registro = date("Y-m-d H:i:s");

$update = "UPDATE pacientes
	SET
	  nombre = '$nombre'
		,apellido = '$apellido'
		,identidad = '$identidad'
		,edad = '$edad'
		,telefono1 = '$telefono1'
		,telefono2 = '$telefono2'
		,genero = '$genero'
		,localidad = '$localidad'
		,email = '$correo'
	WHERE pacientes_id = '$pacientes_id'";
$pacientes_id = $cliente_admision;
$query = $mysqli->query($update);

if($query){
	/*********************************************************************************************************************************************************************/
	//INGRESAR REGISTROS EN LA ENTIDAD HISTORIAL
	$historial_numero = historial();
	$estado_historial = "Agregar";
	$observacion_historial = "Se ha modificado el cliente: $nombre $apellido";
	$modulo = "Pacientes";
	$insert = "INSERT INTO historial
		VALUES('$historial_numero','0','0','$modulo','$pacientes_id','$usuario','0','$fecha','$estado_historial','$observacion_historial','$usuario','$fecha_registro')";
	$mysqli->query($insert) or die($mysqli->error);
	/*********************************************************************************************************************************************************************/

	$datos = array(
		0 => "Almacenado",
		1 => "Registro Almacenado Correctamente",
		2 => "success",
		3 => "btn-primary",
		4 => "",
		5 => "Registro",
		6 => "formulario_admision_clientes_editar",
		7 => "modal_admision_clientes_editar",
		8 => "",
		9 => "Guardar",
		10 => ""
	);
}else{
	$datos = array(
		0 => "Error",
		1 => "No se puedo almacenar este registro, los datos son incorrectos por favor corregir",
		2 => "error",
		3 => "btn-danger",
		4 => "",
		5 => "",
	);
}

echo json_encode($datos);
?>
