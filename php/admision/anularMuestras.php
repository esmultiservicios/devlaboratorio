<?php
session_start();
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

$muestras_id = $_POST['muestras_id'];
$pacientes_id = $_POST['pacientes_id'];
$comentario = $_POST['comentario'];
$fecha = date("Y-m-d");
$fecha_registro = date("Y-m-d H:i:s");
$usuario = $_SESSION['colaborador_id'];

//VERIFICAMOS SI LA MUESTRA TIENE UNA FACTURA ANTES DE ANULARLA
$consultar_factura = "SELECT facturas_id
		FROM facturas
		WHERE muestras_id = '$muestras_id' AND estado NOT IN(3)";
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

//CONSULTAMOS EL NOMBRE DEL CLIENTE
$consultar_cliente = "SELECT CONCAT(nombre, ' ', apellido) AS 'cliente', expediente
	FROM pacientes
	WHERE pacientes_id = '$pacientes_id'";
$resultCliente = $mysqli->query($consultar_cliente);
$consultaCliente = $resultCliente->fetch_assoc();
$NombreCliente = cleanString($consultaCliente['cliente']);
$expediente = $consultaCliente['expediente'];

if($result->num_rows==0){
	/*********************************************************************************************************************************************************************/
	$historial_numero = historial();
	$estado_historial = "Anular";
	$observacion_historial = "Se anulo la muestra numero: $NumeroMuestra del cliente $NombreCliente, por el usuario: $NombreColaborador, con el comentario: $comentario";
	$modulo = "Muestras";
	$insert = "INSERT INTO historial
		VALUES('$historial_numero','$pacientes_id','$expediente','$modulo','$pacientes_id','$usuario','0','$fecha','$estado_historial','$observacion_historial','$usuario','$fecha_registro')";
	$mysqli->query($insert) or die($mysqli->error);
	/*********************************************************************************************************************************************************************/

	$update = "UPDATE muestras
	 		SET estado = 2
			WHERE muestras_id = '$muestras_id'";
	$mysqli->query($update);

	if($update){
		echo 1;//REGISTRO ANULADO CORRECTAMENTE
	}else{
		echo 2;//ERROR AL PROCESAR SU SOLICITUD
	}
}else{
	echo 3;//ESTE REGISTRO CUENTA CON INFORMACIÓN, NO SE PUEDE ELIMINAR
}

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>
