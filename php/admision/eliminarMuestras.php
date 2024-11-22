<?php
session_start();
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

$muestras_id = $_POST['muestras_id'];
$pacientes_id = $_POST['pacientes_id'];
$comentario = $_POST['comentario'];
$usuario = $_SESSION['colaborador_id'];

//VERIFICAMOS SI EL REGISTRO CUENTA CON INFORMACION ALMACENADA
$consultar_factura = "SELECT facturas_id
		FROM facturas
		WHERE muestras_id = '$muestras_id'";
$result = $mysqli->query($consultar_factura);

//CONSULTAMOS EL NUMERO DE LA MUESTRA
$consultar_muestra= "SELECT number
		FROM muestras
		WHERE muestras_id = '$muestras_id'";
$resultMuestras = $mysqli->query($consultar_muestra);
$consultaMuestras = $resultMuestras->fetch_assoc();
$NumeroMuestra = $consultaMuestras['number'];

//CONSULTAMOS EL NOMBRE DEL USUARIO DEL SISTEMA
$consultar_colaborador = "SELECT CONCAT(nombre, ' ', apellido) AS 'colaborador'
	FROM colaboradores
	WHERE colaborador_id = '$usuario'";
$resultColaborador = $mysqli->query($consultar_colaborador);
$consultaColaborador = $resultColaborador->fetch_assoc();
$NombreColaborador = $consultaColaborador['colaborador'];

if($result->num_rows==0){
	/*********************************************************************************************************************************************************************/
	$historial_numero = historial();
	$estado_historial = "Eliminar";
	$observacion_historial = "Se elimino la muestra numero: $NumeroMuestra, por el usuario: $NombreColaborador, con el comentario: $comentario";
	$modulo = "Muestras";
	$insert = "INSERT INTO historial
		VALUES('$historial_numero','0','0','$modulo','$pacientes_id','$usuario','0','$fecha','$estado_historial','$observacion_historial','$usuario','$fecha_registro')";
	$mysqli->query($insert) or die($mysqli->error);
	/*********************************************************************************************************************************************************************/

	$delete = "DELETE FROM muestras WHERE muestras_id = '$muestras_id'";
	$mysqli->query($delete);

	if($delete){
		echo 1;//REGISTRO ELIMINADO CORRECTAMENTE
	}else{
		echo 2;//ERROR AL PROCESAR SU SOLICITUD
	}
}else{
	echo 3;//ESTE REGISTRO CUENTA CON INFORMACIÓN, NO SE PUEDE ELIMINAR
}

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>
