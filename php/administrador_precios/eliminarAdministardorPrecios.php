<?php	
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$administrador_precios_id = $_POST['administrador_precios_id'];
$hospitales_id_consulta = $_POST['hospitales_id_consulta'];
$fecha_registro = date("Y-m-d H:i:s");
$fecha = date("Y-m-d");
$usuario = $_SESSION['colaborador_id'];

//VERIFICAMOS SI EL PRODUCTO EXISTE EN LA FACTURACION O EN LAS COMPRAS
$query_facturas = "SELECT muestras_id
	FROM muestras 
	WHERE hospitales_id = '$hospitales_id_consulta'";
$result_facturas = $mysqli->query($query_facturas) or die($mysqli->error);


if($result_facturas->num_rows ==0){
	$delete = "DELETE FROM administrador_precios WHERE administrador_precios_id  = '$administrador_precios_id '";
	$query = $mysqli->query($delete) or die($mysqli->error);
	
	if($query){
		$datos = array(
			0 => "Eliminado", 
			1 => "Registro Eliminado Correctamente", 
			2 => "success",
			3 => "btn-primary",
			4 => "formularioAdministradorPrecios",
			5 => "Eliminar",
			6 => "AdministradorPrecios",//FUNCION DE LA TABLA QUE LLAMAREMOS PARA QUE ACTUALICE (DATATABLE BOOSTRAP)
			7 => "modalAdministradorPrecios", //Modals Para Cierre Automatico
			8 => "",
			9 => "Eliminar", //PERMITE CERRAR EL MODAL SEGUN EL INDICADOR este indicador esta en main.js			
		);	

		/*********************************************************************************************************************************************************************/
		//INGRESAR REGISTROS EN LA ENTIDAD HISTORIAL
		$historial_numero = historial();
		$estado_historial = "Agregar";
		$observacion_historial = "Se ha eliminado el administrador de precios con código $administrador_precios_id";
		$modulo = "Administrador de Precios";
		$insert = "INSERT INTO historial 
		   VALUES('$historial_numero','0','0','$modulo','$administrador_precios_id','$usuario','0','$fecha','$estado_historial','$observacion_historial','$usuario','$fecha_registro')";	 
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