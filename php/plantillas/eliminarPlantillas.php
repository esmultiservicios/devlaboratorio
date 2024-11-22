<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli(); 

$plantillas_id = $_POST['plantillas_id'];

//CONSULTAMOS QUE LA PLANTILLA NO SE HA ALMACENADO EN LA ATENCION

//ELIMINAMOS LA PLANTILLA
$delete = "DELETE FROM plantillas WHERE plantillas_id  = '$plantillas_id'";
$query = $mysqli->query($delete);

if($query){
	$datos = array(
		0 => "Eliminado", 
		1 => "Registro Eliminado Correctamente", 
		2 => "success",
		3 => "btn-primary",
		4 => "formularioPlantillas",//FORMULARIO PARA RESETEO DE DATOS
		5 => "Eliminar",
		6 => "Plantillas",//FUNCION DE LA TABLA QUE LLAMAREMOS PARA QUE ACTUALICE (DATATABLE BOOSTRAP)
		7 => "modal_plantillas", //Modals Para Cierre Automatico
	);	
}else{
	$datos = array(
		0 => "Error", 
		1 => "No se puedo eliminar este registro, los datos son incorrectos por favor corregir", 
		2 => "error",
		3 => "btn-danger",
		4 => "",
		5 => "",			
	);		
}

echo json_encode($datos);
?>