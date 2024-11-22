<?php
session_start();
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

$facturas_id = $_POST['facturas_id'];
$comentario = cleanStringStrtolower($_POST['comentario']);
$fecha_registro = date("Y-m-d H:i:s");
$fecha = date("Y-m-d");
$usuario = $_SESSION['colaborador_id'];
$estado = 3; //1. Borrador 2. Pagada 3. Cancelado
$estado_atencion = 1;//ESTADO DE LA ATENCION DEL PACIENTE PARA LA FACTURACION 1. PENDIENTE 2. PAGADA

//OBTENER DATOS DE LA FACTURA
$query_factura = "SELECT sf.prefijo AS 'prefijo', f.number AS 'numero', sf.relleno AS 'relleno', f.pacientes_id AS 'pacientes_id', p.expediente AS 'expediente', f.colaborador_id AS 'colaborador_id', f.servicio_id AS 'servicio_id', f.fecha AS 'fecha_factura'
   FROM facturas_grupal AS f
   INNER JOIN secuencia_facturacion AS sf
   ON f.secuencia_facturacion_id = sf.secuencia_facturacion_id
   INNER JOIN pacientes AS p
   ON f.pacientes_id = p.pacientes_id
   WHERE f.facturas_grupal_id = '$facturas_id'";
$result = $mysqli->query($query_factura) or die($mysqli->error);
$consultaDatosFactura = $result->fetch_assoc();

$numero_factura = '';
$pacientes_id = '';
$expediente = '';
$colaborador_id = '';
$servicio_id = '';
$fecha_factura = '';

if($result->num_rows>0){
	$numero_factura = $consultaDatosFactura['prefijo'].''.rellenarDigitos($consultaDatosFactura['numero'], $consultaDatosFactura['relleno']);
	$pacientes_id = $consultaDatosFactura['pacientes_id'];
	$expediente = $consultaDatosFactura['expediente'];
	$colaborador_id = $consultaDatosFactura['colaborador_id'];
	$servicio_id = $consultaDatosFactura['servicio_id'];
	$fecha_factura = $consultaDatosFactura['fecha_factura'];
}

//ACTUALIZAMOS EL METODO DE PAGO, CAMBIAMOS EL ESTADO A CANCELADO
$update_factura = "UPDATE facturas_grupal SET estado = '3' WHERE facturas_grupal_id  = '$facturas_id'";
$query = $mysqli->query($update_factura) or die($mysqli->error);

if($query){
	echo 1;//FACTURA CANCELADA CORRECTAMENTE
	//ACTUALIZAMOS LA FACTURA, CAMBIAMOS EL ESTADO A CANCELADO
	$update_metodoPago = "UPDATE pagos_grupal SET estado = '2' WHERE facturas_grupal_id = '$facturas_id'";
	$mysqli->query($update_factura) or die($mysqli->error);

	//CONSULTAMOS LAS FACTRURAS ID PARA ANULARLAS
	$query_facturas = "SELECT facturas_id, muestras_id
		FROM facturas_grupal_detalle
		WHERE facturas_grupal_id = '$facturas_id'";
	$result_faturas = $mysqli->query($query_facturas) or die($mysqli->error);

	while($registro2 = $result_faturas->fetch_assoc()){
		$factura_consulta_id = $registro2['facturas_id'];
		$muestras_id = $registro2['muestras_id'];

		//ANULAMOS LA FACTURA
		$update_factura = "UPDATE facturas SET estado = '3' WHERE facturas_id = '$factura_consulta_id'";
		$mysqli->query($update_factura) or die($mysqli->error);

		//ANULAMOS EL PAGO
		$update_factura = "UPDATE pagos SET estado = '2' WHERE facturas_id = '$factura_consulta_id'";
		$mysqli->query($update_factura) or die($mysqli->error);

		//ACTUALIZAMOS LA MUESTRA PARA PONERLA DISPONIBLE
		$update_muestra = "UPDATE muestras SET estado = 0 WHERE muestras_id = '$muestras_id'";
		$mysqli->query($update_muestra) or die($mysqli->error);		

		$anularPagos = "UPDATE pagos SET estado = 2 WHERE facturas_id = '$factura_consulta_id'";
		$mysqli->query($anularPagos) or die($mysqli->error);
		/*********************************************************************************************************************************************************************/
		//INGRESAR REGISTROS EN LA ENTIDAD HISTORIAL
		$historial_numero = historial();
		$estado_historial = "Agregar";
		$observacion_historial = "el número de factura $numero_factura ha sido anulada correctamente segun comentario: $comentario";
		$modulo = "Facturas";
		$insert = "INSERT INTO historial   VALUES('$historial_numero','$pacientes_id','$expediente','$modulo','$facturas_id','$colaborador_id','$servicio_id','$fecha','$estado_historial','$observacion_historial','$estado','$fecha_registro')";
		$mysqli->query($insert) or die($mysqli->error);
		/********************************************/
	}
}else{
	echo 2;//ERROR AL CANCELAR LA FACTURA
}
?>
