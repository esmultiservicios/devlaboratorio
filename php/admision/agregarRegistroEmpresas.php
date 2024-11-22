<?php
session_start();
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

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

if(isset($_POST['hospital_empresa'])){//COMPRUEBO SI LA VARIABLE ESTA DIFINIDA
	if($_POST['hospital_empresa'] == ""){
		$hospital_clinica = 0;
	}else{
		$hospital_clinica = $_POST['hospital_empresa'];
	}
}else{
	$hospital_clinica = 0;
}

//CONSULTAMOS SI EXISTE EL PACIENTE ANTES DE ALMACENARLO
$select = "SELECT pacientes_id
	FROM pacientes
	WHERE identidad = '$identidad' AND nombre = '$nombre' AND apellido = '$apellido' AND genero = '$genero'";
$result = $mysqli->query($select) or die($mysqli->error);

if($result->num_rows==0){//RREGISTRO NO EXISTE PROCEDEMOS A ALMACENARLO
	$pacientes_id = correlativo('pacientes_id ', 'pacientes');
	$expediente = correlativo('expediente ', 'pacientes');
	$insert = "INSERT INTO pacientes VALUES ('$pacientes_id','$expediente','$identidad','$nombre','$apellido','$genero','$telefono1','$telefono2','$fecha_nacimiento','$edad','$correo','$fecha','$departamento_id','$municipio_id','$localidad','$religion_id','$profesion_id','$usuario','$estado','$paciente_tipo','$fecha_registro')";
	$query = $mysqli->query($insert);

	if($query){
		/*********************************************************************************************************************************************************************/
		$consultar_colaborador = "SELECT CONCAT(nombre, ' ', apellido) AS 'colaborador'
			FROM colaboradores
			WHERE colaborador_id = '$usuario'";
		$resultColaborador = $mysqli->query($consultar_colaborador);
		$consultaColaborador = $resultColaborador->fetch_assoc();
		$NombreColaborador = $consultaColaborador['colaborador'];

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
