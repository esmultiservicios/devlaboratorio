<?php
session_start();
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

$nombre = cleanString($_POST['name']);
$apellido = "";
$sexo = $_POST['sexo'];
$telefono1 = $_POST['telefono1'];
$telefono2 = $_POST['telefono2'];
$correo = strtolower(cleanString($_POST['correo']));
$fecha = date("Y-m-d");

if(isset($_POST['departamento_id'])){//COMPRUEBO SI LA VARIABLE ESTA DIFINIDA
	if($_POST['departamento_id'] == ""){
		$departamento_id = 0;
	}else{
		$departamento_id = $_POST['departamento_id'];
	}
}else{
	$departamento_id = 0;
}

if(isset($_POST['municipio_id'])){//COMPRUEBO SI LA VARIABLE ESTA DIFINIDA
	if($_POST['municipio_id'] == ""){
		$municipio_id = 0;
	}else{
		$municipio_id = $_POST['municipio_id'];
	}
}else{
	$municipio_id = 0;
}

$edad = $_POST['edad'];
$localidad = cleanStringStrtolower($_POST['direccion']);
$fecha_nacimiento = date("Y-m-d");
$religion_id = 0;
$profesion_id = 0;
$identidad = $_POST['rtn'];
$paciente_tipo = $_POST['paciente_tipo'];
$usuario = $_SESSION['colaborador_id'];
$estado = 1; //1. Activo 2. Inactivo
$fecha_registro = date("Y-m-d H:i:s");

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

//EVALUAR SI EXISTE EL PACIENTE
$select = "SELECT pacientes_id
	FROM pacientes
	WHERE identidad = '$identidad' AND nombre = '$nombre' AND apellido = '$apellido' AND telefono1 = '$telefono1'";
$result = $mysqli->query($select) or die($mysqli->error);

if($result->num_rows==0){
	$pacientes_id = correlativo('pacientes_id ', 'pacientes');
	$expediente = correlativo('expediente ', 'pacientes');
	$insert = "INSERT INTO pacientes VALUES ('$pacientes_id','$expediente','$identidad','$nombre','$apellido','$sexo','$telefono1','$telefono2','$fecha_nacimiento','$edad','$correo','$fecha','$departamento_id','$municipio_id','$localidad','$religion_id','$profesion_id','$usuario','$estado','$paciente_tipo','$fecha_registro')";
	$query = $mysqli->query($insert);

	if($query){
		/*********************************************************************************************************************************************************************/
		$consultar_colaborador = "SELECT CONCAT(nombre, ' ', apellido) AS 'colaborador'
			FROM colaboradores
			WHERE colaborador_id = '$usuario'";
		$resultColaborador = $mysqli->query($consultar_colaborador);
		$consultaColaborador = $resultColaborador->fetch_assoc();
		$NombreColaborador = $consultaColaborador['colaborador'];

		//INGRESAR REGISTROS EN LA ENTIDAD HISTORIAL
		$historial_numero = historial();
		$estado_historial = "Agregar";
		$observacion_historial = "Se ha agregado un nuevo cliente: $nombre $apellido, por el usuario: $NombreColaborador";
		$modulo = "Clientes";
		$insert = "INSERT INTO historial
			VALUES('$historial_numero','0','0','$modulo','$pacientes_id','$usuario','0','$fecha','$estado_historial','$observacion_historial','$usuario','$fecha_registro')";
		$mysqli->query($insert) or die($mysqli->error);
		/*********************************************************************************************************************************************************************/

		$datos = array(
			0 => "Almacenado",
			1 => "Registro Almacenado Correctamente",
			2 => "success",
			3 => "btn-primary",
			4 => "formulario_pacientes",
			5 => "Registro",
			6 => "formPacientes",
			7 => "modal_pacientes",
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
}else{
	$datos = array(
		0 => "Error",
		1 => "Lo sentimos este registro ya existe no se puede almacenar",
		2 => "error",
		3 => "btn-danger",
		4 => "",
		5 => "",
	);
}

echo json_encode($datos);
?>
