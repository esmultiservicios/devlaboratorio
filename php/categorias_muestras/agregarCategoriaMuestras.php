<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$usuario = $_SESSION['colaborador_id'];
$categoria_muestra = cleanStringStrtolower($_POST['categoria_muestra']);
$tiempo_categoria = cleanStringStrtolower($_POST['tiempo_categoria']);
$fecha_registro = date("Y-m-d H:i:s");
$fecha = date("Y-m-d");

//VERIFICAMOS SI EXSTE EL ALMACEN
$query = "SELECT categoria_id
	FROM categoria
	WHERE nombre = '$categoria_muestra'";
$result = $mysqli->query($query) or die($mysqli->error);

if($result->num_rows==0){
	$categoria_id  = correlativo('categoria_id', 'categoria');
	$insert = "INSERT INTO categoria VALUES('$categoria_id','$categoria_muestra','$tiempo_categoria','$usuario','$fecha_registro')";
	$query = $mysqli->query($insert) or die($mysqli->error);
	
    if($query){
		$datos = array(
			0 => "Almacenado", 
			1 => "Registro Almacenado Correctamente", 
			2 => "success",
			3 => "btn-primary",
			4 => "formularioCategoriaMuestras",
			5 => "Registro",
			6 => "CategoriaMuestras",//FUNCION DE LA TABLA QUE LLAMAREMOS PARA QUE ACTUALICE (DATATABLE BOOSTRAP)
			7 => "modalCategoriaMuestras", //Modals Para Cierre Automatico
		);
		
		/*********************************************************************************************************************************************************************/
		/*********************************************************************************************************************************************************************/
		//INGRESAR REGISTROS EN LA ENTIDAD HISTORIAL
		$historial_numero = historial();
		$estado_historial = "Agregar";
		$observacion_historial = "Se ha agregado un nuevo Categoría: $categoria_muestra";
		$modulo = "Categoria Muestras";
		$insert = "INSERT INTO historial 
		   VALUES('$historial_numero','0','0','$modulo','$categoria_id','$usuario','0','$fecha','$estado_historial','$observacion_historial','$usuario','$fecha_registro')";	 
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