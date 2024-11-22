<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$usuario = $_SESSION['colaborador_id'];
$hospitales = $_POST['hospitales'];
$fecha = date("Y-m-d");
$fecha_registro = date("Y-m-d H:i:s");

//VERIFICAMOS SI EXSTE EL HOSPITAL
$query = "SELECT hospitales_id
	FROM hospitales
	WHERE nombre = '$hospitales'";
$result = $mysqli->query($query) or die($mysqli->error);

if($result->num_rows==0){
	$hospitales_id  = correlativo('hospitales_id', 'hospitales');
	$insert = "INSERT INTO hospitales VALUES('$hospitales_id','$hospitales')";
	$query = $mysqli->query($insert) or die($mysqli->error);
	
    if($query){
		$datos = array(
			0 => "Almacenado", 
			1 => "Registro Almacenado Correctamente", 
			2 => "success",
			3 => "btn-primary",
			4 => "formularioHospitales",
			5 => "Registro",
			6 => "Hospitales",//FUNCION DE LA TABLA QUE LLAMAREMOS PARA QUE ACTUALICE (DATATABLE BOOSTRAP)
			7 => "modalHospitales", //Modals Para Cierre Automatico
		);
		
		/*********************************************************************************************************************************************************************/
		/*********************************************************************************************************************************************************************/
		//INGRESAR REGISTROS EN LA ENTIDAD HISTORIAL
		$historial_numero = historial();
		$estado_historial = "Agregar";
		$observacion_historial = "Se ha agregado un nuevo Hospital con el código: $hospitales_id";
		$modulo = "Hospitales";
		$insert = "INSERT INTO historial 
		   VALUES('$historial_numero','0','0','$modulo','$hospitales_id','$usuario','0','$fecha','$estado_historial','$observacion_historial','$usuario','$fecha_registro')";	 
		$mysqli->query($insert) or die($mysqli->error);
		/*********************************************************************************************************************************************************************/		
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