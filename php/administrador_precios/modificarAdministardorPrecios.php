<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$usuario = $_SESSION['colaborador_id'];
$administrador_precios_id = $_POST['administrador_precios_id'];
$precio = $_POST['precio'];
$fecha = date("Y-m-d");
$fecha_registro = date("Y-m-d H:i:s");

$update = "UPDATE administrador_precios
	SET
		precio = '$precio'
	WHERE administrador_precios_id = '$administrador_precios_id'";
$query = $mysqli->query($update) or die($mysqli->error);

if($query){
	$datos = array(
		0 => "Editado", 
		1 => "Registro Editado Correctamente", 
		2 => "success",
		3 => "btn-primary",
		4 => "formularioAdministradorPrecios",
		5 => "Editar",
		6 => "AdministradorPrecios",//FUNCION DE LA TABLA QUE LLAMAREMOS PARA QUE ACTUALICE (DATATABLE BOOSTRAP)
		7 => "modalAdministradorPrecios", //Modals Para Cierre Automatico
	);	
	
	/*********************************************************************************************************************************************************************/
	//INGRESAR REGISTROS EN LA ENTIDAD HISTORIAL
	$historial_numero = historial();
	$estado_historial = "Agregar";
	$observacion_historial = "Se ha modificado el administrador de precios con codigo: $administrador_precios_id";
	$modulo = "Administrador de Precios";
	$insert = "INSERT INTO historial 
	   VALUES('$historial_numero','0','0','$modulo','$administrador_precios_id','$usuario','0','$fecha','$estado_historial','$observacion_historial','$usuario','$fecha_registro')";	 
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