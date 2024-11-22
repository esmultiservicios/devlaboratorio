<?php
session_start();
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

$pacientes_id = $_POST['pacientes_id'];
$usuario = $_SESSION['colaborador_id'];
$estado = 1; //1. Activo 2. Inactivo
$fecha_registro = date("Y-m-d H:i:s");
$fecha= date("Y-m-d");

$nombre = $_POST['name'];
$apellido = "";
$sexo = $_POST['sexo'];
$telefono1 = $_POST['telefono1'];
$telefono2 = $_POST['telefono2'];
$correo = strtolower(cleanString($_POST['correo']));
$localidad = cleanStringStrtolower($_POST['direccion']);
$departamento_id = $_POST['departamento_id'];
$municipio_id = $_POST['municipio_id'];
$edad = $_POST['edad'];
$paciente_tipo = $_POST['paciente_tipo'];
$profesion = 0;
$religion = 0;
$fecha_nacimiento = date("Y-m-d");

$update = "UPDATE pacientes
	SET
		nombre = '$nombre',
		apellido = '$apellido',
		genero = '$sexo',
		telefono1 = '$telefono1',
		telefono2 = '$telefono2',
		email = '$correo',
		localidad = '$localidad',
		departamento_id	= '$departamento_id',
		municipio_id = '$municipio_id',
		religion_id = '$religion',
		profesion_id = '$profesion',
		fecha_nacimiento = '$fecha_nacimiento',
		edad = '$edad',
		tipo_paciente_id = '$paciente_tipo'
	WHERE pacientes_id = '$pacientes_id'";

$query = $mysqli->query($update);

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
		$estado_historial = "Modificar";
		$observacion_historial = "Se ha agregado un nuevo cliente: $nombre $apellido, por el usuario: $NombreColaborador";
		$modulo = "Clientes";
		$insert = "INSERT INTO historial
			VALUES('$historial_numero','0','0','$modulo','$pacientes_id','$usuario','0','$fecha','$estado_historial','$observacion_historial','$usuario','$fecha_registro')";
		$mysqli->query($insert) or die($mysqli->error);
		/*********************************************************************************************************************************************************************/

		$datos = array(
			0 => "Editado",
			1 => "Registro Editado Correctamente",
			2 => "success",
			3 => "btn-primary",
			4 => "",
			5 => "Editar",
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

echo json_encode($datos);
?>
