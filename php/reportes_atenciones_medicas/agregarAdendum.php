<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$usuario = $_SESSION['colaborador_id'];
$muestras_id = $_POST['muestras_id'];
$atencion_id = $_POST['atencion_id'];
$descripcion_adendum = cleanStringStrtolower($_POST['descripcion_adendum']);
$fecha_registro = date("Y-m-d H:i:s");
$fecha = date("Y-m-d");

//VERIFICAMOS SI EXSTE UNA DESCRIPCION ALMACENADA PARA ESTA ATENCION
$query = "SELECT atencion_id
	FROM adendum
	WHERE atencion_id = '$atencion_id'";
$result = $mysqli->query($query) or die($mysqli->error);

if($result->num_rows==0){
	$adendum_id   = correlativo('adendum_id ', 'adendum');
	$insert = "INSERT INTO adendum 
		VALUES('$adendum_id ','$atencion_id','$descripcion_adendum','$usuario','$fecha_registro')";
	$query = $mysqli->query($insert) or die($mysqli->error);
	
    if($query){
		$datos = array(
			0 => "Almacenado", 
			1 => "Registro Almacenado Correctamente", 
			2 => "success",
			3 => "btn-primary",
			4 => "formularioAdendum",
			5 => "Registro",
			6 => "Adendum",//FUNCION DE LA TABLA QUE LLAMAREMOS PARA QUE ACTUALICE (DATATABLE BOOSTRAP)
			7 => "modal_adendum", //Modals Para Cierre Automatico
			8 => $atencion_id,
		);
		
		/*********************************************************************************************************************************************************************/
		/*********************************************************************************************************************************************************************/
		//INGRESAR REGISTROS EN LA ENTIDAD HISTORIAL
		$historial_numero = historial();
		$estado_historial = "Agregar";
		$observacion_historial = "Se ha agregado un nuevo adendum con el código: $adendum_id";
		$modulo = "Productos";
		$insert = "INSERT INTO historial 
		   VALUES('$historial_numero','0','0','$modulo','$adendum_id','$usuario','0','$fecha','$estado_historial','$observacion_historial','$usuario','$fecha_registro')";	 
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