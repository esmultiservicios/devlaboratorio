<?php	
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$hospitales_id = $_POST['hospitales_id'];
$fecha_registro = date("Y-m-d H:i:s");
$fecha = date("Y-m-d");
$usuario = $_SESSION['colaborador_id'];

//VERIFICAMOS SI EL PRODUCTO EXISTE EN LA FACTURACION O EN LAS COMPRAS
$query_hospitales = "SELECT muestras_id
	FROM muestras 
	WHERE hospitales_id = '$hospitales_id'";
$result_hospitales = $mysqli->query($query_hospitales) or die($mysqli->error);

if($result_hospitales->num_rows ==0){
	$delete = "DELETE FROM hospitales WHERE hospitales_id = '$hospitales_id'";
	$query = $mysqli->query($delete) or die($mysqli->error);
	
	if($query){
		$datos = array(
			0 => "Eliminado", 
			1 => "Registro Eliminado Correctamente", 
			2 => "success",
			3 => "btn-primary",
			4 => "formularioHospitales",
			5 => "Eliminar",
			6 => "Hospitales",//FUNCION DE LA TABLA QUE LLAMAREMOS PARA QUE ACTUALICE (DATATABLE BOOSTRAP)
			7 => "modalHospitales", //Modals Para Cierre Automatico
		);	

		/*********************************************************************************************************************************************************************/
		//INGRESAR REGISTROS EN LA ENTIDAD HISTORIAL
		$historial_numero = historial();
		$estado_historial = "Agregar";
		$observacion_historial = "Se ha eliminado el hospital de precios con código $hospitales_id";
		$modulo = "Hospitales";
		$insert = "INSERT INTO historial 
		   VALUES('$historial_numero','0','0','$modulo','$hospitales_id','$usuario','0','$fecha','$estado_historial','$observacion_historial','$usuario','$fecha_registro')";	 
		$mysqli->query($insert) or die($mysqli->error);
		/*********************************************************************************************************************************************************************/		
	/*********************************************************************************************************************************************************************/		
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
}else{
	$datos = array(
		0 => "Error", 
		1 => "Lo sentimos este registro cuenta con información almacenada no se puede eliminar", 
		2 => "error",
		3 => "btn-danger",
		4 => "",
		5 => "",		
	);	
}

echo json_encode($datos);
?>