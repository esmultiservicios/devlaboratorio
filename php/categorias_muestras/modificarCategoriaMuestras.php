<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$usuario = $_SESSION['colaborador_id'];
$tiempo_categoria = cleanStringStrtolower($_POST['tiempo_categoria']);
$categoria_id = $_POST['categoria_id'];
$fecha_registro = date("Y-m-d H:i:s");
$fecha = date("Y-m-d");

$update = "UPDATE categoria
	SET
	    tiempo = '$tiempo_categoria'		
	WHERE categoria_id = '$categoria_id'";
$query = $mysqli->query($update) or die($mysqli->error);

if($query){			
	$datos = array(
		0 => "Editado", 
		1 => "Registro Editado Correctamente", 
		2 => "success",
		3 => "btn-primary",
		4 => "formularioCategoriaMuestras",
		5 => "Editar",
		6 => "CategoriaMuestras",//FUNCION DE LA TABLA QUE LLAMAREMOS PARA QUE ACTUALICE (DATATABLE BOOSTRAP)
		7 => "modalCategoriaMuestras", //Modals Para Cierre Automatico
	);	
	
	/*********************************************************************************************************************************************************************/
	//INGRESAR REGISTROS EN LA ENTIDAD HISTORIAL
	$historial_numero = historial();
	$estado_historial = "Agregar";
	$observacion_historial = "Se ha modificado la Categoria con código $categoria_id";
	$modulo = "Muestras";
	$insert = "INSERT INTO historial 
	   VALUES('$historial_numero','0','0','$modulo','$categoria_id','$usuario','0','$fecha','$estado_historial','$observacion_historial','$usuario','$fecha_registro')";	 
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