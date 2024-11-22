<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$usuario = $_SESSION['colaborador_id'];
$muestras_id = $_POST['muestras_id'];
$servicio_id = $_POST['servicio_muestras'];
$hospital_clinica = $_POST['hospital_clinica'];
$colaborador_id = $_POST['remitente'];
$fecha = date("Y-m-d");
$sitio_muestra = cleanStringStrtolower($_POST['sitio_muestra']);
$diagonostico_muestra = cleanStringStrtolower($_POST['diagonostico_muestra']);
$material_muestra = cleanStringStrtolower($_POST['material_muestra']);
$datos_relevantes_muestras = cleanStringStrtolower($_POST['datos_relevantes_muestras']);

if(isset($_POST['paciente_muestras'])){//COMPRUEBO SI LA VARIABLE ESTA DIFINIDA
	if($_POST['paciente_muestras'] == ""){
		$paciente_muestras = 0;
	}else{
		$paciente_muestras = $_POST['paciente_muestras'];
	}
}else{
	$paciente_muestras = 0;
}

if(isset($_POST['mostrar_datos_clinicos'])){//COMPRUEBO SI LA VARIABLE ESTA DIFINIDA
	if($_POST['mostrar_datos_clinicos'] == ""){
		$mostrar_datos_clinicos = 2;
	}else{
		$mostrar_datos_clinicos = $_POST['mostrar_datos_clinicos'];
	}
}else{
	$mostrar_datos_clinicos = 2;
}

$fecha_registro = date("Y-m-d H:i:s");

$update = "UPDATE muestras
	SET
		sitio_muestra = '$sitio_muestra',
		diagnostico_clinico = '$diagonostico_muestra',
		material_eviando = '$material_muestra',	
		datos_clinico = '$datos_relevantes_muestras',
		mostrar_datos_clinicos = '$mostrar_datos_clinicos',
		hospitales_id = '$hospital_clinica',
		servicio_id = '$servicio_id',
		colaborador_id = '$colaborador_id'		
	WHERE muestras_id = '$muestras_id'";
$query = $mysqli->query($update) or die($mysqli->error);

if($query){			
	$datos = array(
		0 => "Editado", 
		1 => "Registro Editado Correctamente", 
		2 => "success",
		3 => "btn-primary",
		4 => "formularioMuestras",
		5 => "Editar",
		6 => "MuestrasModificar",//FUNCION DE LA TABLA QUE LLAMAREMOS PARA QUE ACTUALICE (DATATABLE BOOSTRAP)
		7 => "modal_muestras", //Modals Para Cierre Automatico
	);	
	
	/*********************************************************************************************************************************************************************/
	//INGRESAR REGISTROS EN LA ENTIDAD HISTORIAL
	$historial_numero = historial();
	$estado_historial = "Agregar";
	$observacion_historial = "Se ha modificado la muestra con código $muestras_id";
	$modulo = "Muestras";
	$insert = "INSERT INTO historial 
	   VALUES('$historial_numero','0','0','$modulo','$muestras_id','$usuario','0','$fecha','$estado_historial','$observacion_historial','$usuario','$fecha_registro')";	 
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