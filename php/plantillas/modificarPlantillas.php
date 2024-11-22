<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli(); 

$plantillas_id = $_POST['plantillas_id'];
$plantilla_asunto = cleanStringStrtolower($_POST['plantilla_asunto']);
$plantilla_descripcion = cleanStringStrtolower($_POST['plantilla_descripcion']);
$fecha_registro = date("Y-m-d H:i:s");

//MODIFICAMOS LOS DATOS DE LA PLANTILLA
$update = "UPDATE plantillas
	SET
		descripcion = '$plantilla_descripcion'
	WHERE plantillas_id = '$plantillas_id'";
$query = $mysqli->query($update);

if($query){
	$datos = array(
		0 => "Editado", 
		1 => "Registro Editado Correctamente", 
		2 => "success",
		3 => "btn-primary",
		4 => "formularioPlantillas",//FORMULARIO PARA RESETEO DE DATOS
		5 => "Editar",
		6 => "Plantillas",//FUNCION DE LA TABLA QUE LLAMAREMOS PARA QUE ACTUALICE (DATATABLE BOOSTRAP)
		7 => "modal_plantillas", //Modals Para Cierre Automatico
	);		
}else{
	$datos = array(
		0 => "Error", 
		1 => "No se puedo editar este registro, los datos son incorrectos por favor corregir", 
		2 => "error",
		3 => "btn-danger",
		4 => "",
		5 => "",			
	);	
}

echo json_encode($datos);
?>