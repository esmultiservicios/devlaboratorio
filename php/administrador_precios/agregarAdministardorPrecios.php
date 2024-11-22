<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$usuario = $_SESSION['colaborador_id'];
$hospitales_id = $_POST['hospitales_id'];
$precio = $_POST['precio'];
$fecha = date("Y-m-d");
$fecha_registro = date("Y-m-d H:i:s");

//VERIFICAMOS SI EXSTE EL ALMACEN
$query = "SELECT hospitales_id
	FROM administrador_precios
	WHERE hospitales_id = '$hospitales_id'";
$result = $mysqli->query($query) or die($mysqli->error);

if($result->num_rows==0){
	$administrador_precios_id  = correlativo('administrador_precios_id', 'administrador_precios');
	$insert = "INSERT INTO administrador_precios 
		VALUES('$administrador_precios_id','$hospitales_id','$precio','$fecha_registro')";
	$query = $mysqli->query($insert) or die($mysqli->error);
	
    if($query){
		$datos = array(
			0 => "Almacenado", 
			1 => "Registro Almacenado Correctamente", 
			2 => "success",
			3 => "btn-primary",
			4 => "formularioAdministradorPrecios",
			5 => "Registro",
			6 => "AdministradorPrecios",//FUNCION DE LA TABLA QUE LLAMAREMOS PARA QUE ACTUALICE (DATATABLE BOOSTRAP)
			7 => "modalAdministradorPrecios", //Modals Para Cierre Automatico
		);
		
		/*********************************************************************************************************************************************************************/
		/*********************************************************************************************************************************************************************/
		//INGRESAR REGISTROS EN LA ENTIDAD HISTORIAL
		$historial_numero = historial();
		$estado_historial = "Agregar";
		$observacion_historial = "Se ha agregado un nuevo Administrador de Precios: $administrador_precios_id";
		$modulo = "Administrador de Precios";
		$insert = "INSERT INTO historial 
		   VALUES('$historial_numero','0','0','$modulo','$administrador_precios_id','$usuario','0','$fecha','$estado_historial','$observacion_historial','$usuario','$fecha_registro')";	 
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