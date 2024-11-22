<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$usuario = $_SESSION['colaborador_id'];
$limite = $_POST['limite'];
$fecha_registro = date("Y-m-d H:i:s");
$fecha = date("Y-m-d");

//CONSULTAR LIMITE ANTERIOR
$query = "SELECT limite
FROM limite_muetras";
$resultLimiteAnterior = $mysqli->query($query) or die($mysqli->error);

$limite_anterior = "";

if($resultLimiteAnterior->num_rows>=0){	
	$valores2limite_anterior = $resultLimiteAnterior->fetch_assoc();
	$limite_anterior = $valores2limite_anterior['limite'];
}

$update = "UPDATE limite_muetras
	SET
		limite = '$limite',
		usuario = '$usuario',
		fecha_edicion = '$fecha_registro'";

$query = $mysqli->query($update) or die($mysqli->error);

if($query){
	$datos = array(
		0 => "Editado", 
		1 => "Registro Editado Correctamente", 
		2 => "success",
		3 => "btn-primary",
		4 => "",
		5 => "Editar",
		6 => "LimiteMuestras",//FUNCION DE LA TABLA QUE LLAMAREMOS PARA QUE ACTUALICE (DATATABLE BOOSTRAP)
		7 => "", //Modals Para Cierre Automatico
	);	
	
	/*********************************************************************************************************************************************************************/
	//INGRESAR REGISTROS EN LA ENTIDAD HISTORIAL
	$query = "SELECT CONCAT(nombre, ' ', apellido) As 'usuarioSistema'
		FROM colaboradores
		WHERE colaborador_id = '$usuario'";
	$result = $mysqli->query($query) or die($mysqli->error);

	$usuarioSistema = "";

	if($result->num_rows>=0){	
		$valores2usuarioSistema = $result->fetch_assoc();
		$usuarioSistema = $valores2usuarioSistema['usuarioSistema'];
	}

	$historial_numero = historial();
	$estado_historial = "Modificar";
	$observacion_historial = "Se ha modificado el limite de la muestra por el usuario: $usuarioSistema, limite anterior: $limite_anterior al nuevo limite: $limite";
	$modulo = "LimiteMuestras";
	$insert = "INSERT INTO historial 
	   VALUES('$historial_numero','0','0','$modulo','1','$usuario','0','$fecha','$estado_historial','$observacion_historial','$usuario','$fecha_registro')";	 
	$mysqli->query($insert) or die($mysqli->error);
	/*********************************************************************************************************************************************************************/		
	/*********************************************************************************************************************************************************************/		
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