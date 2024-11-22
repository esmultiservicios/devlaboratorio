<?php	
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$muestras_id = $_POST['muestras_id'];
$fecha_registro = date("Y-m-d H:i:s");
$fecha = date("Y-m-d");
$usuario = $_SESSION['colaborador_id'];

//VERIFICAMOS SI LA MUESTRA YA HA SIDO EVALUADO
$query_atenciones = "SELECT atencion_id 
	FROM atenciones_medicas
	WHERE muestras_id = '$muestras_id'";
$result_atenciones = $mysqli->query($query_atenciones) or die($mysqli->error);


if($result_atenciones->num_rows ==0){
	$delete = "DELETE FROM muestras WHERE muestras_id = '$muestras_id'";
	$query = $mysqli->query($delete) or die($mysqli->error);
	
	if($query){
		//ELIMINAR MUESTRA DE muestras_hospitales
		$delte = "DELETE FROM muestras_hospitales WHERE muestras_id = '$muestras_id'";
		$mysqli->query($delete) or die($mysqli->error);
		
		$datos = array(
			0 => "Eliminado", 
			1 => "Registro Eliminado Correctamente", 
			2 => "success",
			3 => "btn-primary",
			4 => "formularioMuestras",
			5 => "Eliminar",
			6 => "MuestrasEliminar",//FUNCION DE LA TABLA QUE LLAMAREMOS PARA QUE ACTUALICE (DATATABLE BOOSTRAP)
			7 => "modal_muestras", //Modals Para Cierre Automatico
			8 => "",
			9 => "Eliminar"
		);	

		/*********************************************************************************************************************************************************************/
		//INGRESAR REGISTROS EN LA ENTIDAD HISTORIAL
		$historial_numero = historial();
		$estado_historial = "Agregar";
		$observacion_historial = "Se ha eliminado la muetra con código $muestras_id";
		$modulo = "Muestras";
		$insert = "INSERT INTO historial 
		   VALUES('$historial_numero','0','0','$modulo','$muestras_id','$usuario','0','$fecha','$estado_historial','$observacion_historial','$usuario','$fecha_registro')";	 
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