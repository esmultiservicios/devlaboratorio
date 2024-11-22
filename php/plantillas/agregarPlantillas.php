<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli(); 

$plantilla_atencion = $_POST['plantilla_atencion'];
$plantilla_asunto = cleanStringStrtolower($_POST['plantilla_asunto']);
$plantilla_descripcion = cleanStringStrtolower($_POST['plantilla_descripcion']);
$fecha_registro = date("Y-m-d H:i:s");

//VALIDAMOS SI EXISTE EL ASUNTO ANTES DE ALMACENARLO
$consulta_asunto = "SELECT asunto
	FROM plantillas
	WHERE asunto = '$plantilla_asunto' AND plantillas_id = '$plantilla_atencion'";
$result = $mysqli->query($consulta_asunto) or die($mysqli->error);

if($result->num_rows==0){
	//ALMACENAMOS LA PLANTILLA PARA EL TIPO DE ATENCIÓN SELECCIONADO
	$plantillas_id = correlativo("plantillas_id","plantillas");
	$insert = "INSERT INTO plantillas 
		VALUES ('$plantillas_id','$plantilla_atencion','$plantilla_asunto','$plantilla_descripcion','$fecha_registro')";
	$query = $mysqli->query($insert);

	if($query){
		$datos = array(
			0 => "Almacenado", 
			1 => "Registro Almacenado Correctamente", 
			2 => "success",
			3 => "btn-primary",
			4 => "formularioPlantillas",//FORMULARIO PARA RESETEO DE DATOS
			5 => "Registro",
			6 => "Plantillas",//FUNCION DE LA TABLA QUE LLAMAREMOS PARA QUE ACTUALICE (DATATABLE BOOSTRAP)
			7 => "modal_plantillas", //Modals Para Cierre Automatico
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