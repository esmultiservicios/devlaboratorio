<?php
session_start();
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

$facturas_id = $_POST['facturas_id'];
$usuario = $_SESSION['colaborador_id'];

//CONSULTAMOS EL NUMERO DE LA MUESTRA
$query_muestra = "SELECT muestras_id
	FROM facturas
	WHERE facturas_id = '$facturas_id'";
$result_muestras = $mysqli->query($query_muestra) or die($mysqli->error);

//ELIIMINAMOS LA FACTURA
$delete_factura = "DELETE FROM facturas WHERE facturas_id = '$facturas_id' AND estado = 1";
$query = $mysqli->query($delete_factura);

if($query){
	//ELIMINAMOS EL DETALLE DE LA FACTURA
	$delete_detalle = "DELETE FROM facturas_detalle WHERE facturas_id = '$facturas_id'";
	$mysqli->query($delete_detalle);

	echo 1;//REGISTRO ELIMINADO CORRECTAMENTE

	if($result_muestras->num_rows>0){
		$consulta2Muestras = $result_muestras->fetch_assoc();
		$muestras_id = $consulta2Muestras['muestras_id'];

		//ACTUALIZAMOS EL ESTADO DE LA MUESTRA
		$update_muestra = "UPDATE muestras
			SET
				estado = '0'
			WHERE muestras_id = '$muestras_id'";
			$mysqli->query($update_muestra) or die($mysqli->error);
	}

	//ELIMINAMOS LOS PRODUCTOS DE LA FACTURA SI ES QUE EXISTEN
	$delete_detalles = "DELETE FROM facturas_detalle WHERE facturas_id = '$facturas_id'";
	$mysqli->query($delete_detalles);
}else{
	echo 2;//NO SE PUEDO ELIMINAR EL REGISTRO
}

$mysqli->close();//CERRAR CONEXIÓN
?>