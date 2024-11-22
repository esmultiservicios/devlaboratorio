<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli(); 

$fecha_registro = date("Y-m-d H:i:s");
$fecha = date("Y-m-d");
$secuencia_facturacion_id = $_POST['secuencia_facturacion_id'];

//CONSULTAR DATOS DEL METODO DE PAGO
$query = "SELECT 
			secuencia_facturacion_id,
			empresa_id,
			cai,
			prefijo,
			relleno,
			incremento,
			siguiente,
			rango_inicial,
			rango_final,
			fecha_activacion,
			fecha_limite,
			comentario,
			activo,
			usuario,
			CAST(fecha_registro AS DATE) AS 'fecha_registro',
			documento_id
		FROM 
				secuencia_facturacion
		 WHERE secuencia_facturacion_id = '$secuencia_facturacion_id'";
$result = $mysqli->query($query) or die($mysqli->error);
$consulta_registro = $result->fetch_assoc();   
     
$empresa = "";
$cai = "";
$prefijo = "";
$relleno = "";
$incremento = "";
$rango_inicial = "";
$rango_final = "";
$fecha_limite = "";
$activo = "";
$comentario = "";
$documento_id = "";
$fecha_registro = "";

//OBTENEMOS LOS VALORES DEL REGISTRO
if($result->num_rows>0){
	$empresa = $consulta_registro['empresa_id'];
	$cai = $consulta_registro['cai'];	
	$prefijo = $consulta_registro['prefijo'];
	$relleno = $consulta_registro['relleno'];	
	$incremento = $consulta_registro['incremento'];
	$rango_inicial = $consulta_registro['rango_inicial'];	
	$rango_final = $consulta_registro['rango_final'];
	$fecha_limite = $consulta_registro['fecha_limite'];
	$activo = $consulta_registro['activo'];	
	$comentario = $consulta_registro['comentario'];		
	$documento_id = $consulta_registro['documento_id'];	
	$fecha_registro = $consulta_registro['fecha_registro'];
}

$estado = $_POST['estado'];
$siguiente = $_POST['siguiente'];
$usuario = $_SESSION['colaborador_id'];

//CONSULTAR EL NUMERO DEL ADMINISTRADOR DE SECUENCIAS
$query = "SELECT siguiente as 'numero_anterior'
   FROM secuencia_facturacion
   WHERE secuencia_facturacion_id = '$secuencia_facturacion_id'";
$result_datos = $mysqli->query($query) or die($mysqli->error);
$consulta_datos2 = $result_datos->fetch_assoc();

$numero_anterior = "";

if($result_datos->num_rows>0){
	$numero_anterior = $consulta_datos2['numero_anterior'];		
}

if(isset($_POST['estado'])){//COMPRUEBO SI LA VARIABLE ESTA DIFINIDA
	if($_POST['estado'] == ""){
		$estado = 0;
	}else{
		$estado = $_POST['estado'];
	}
}else{
	$estado = 0;
}

//ACTUALIZAMOS LOS VALORES
$update = "UPDATE secuencia_facturacion 
	SET 
		cai = '$cai', 
		prefijo = '$prefijo', 
		relleno = '$relleno', 
		incremento = '$incremento', 
		siguiente = '$siguiente', 
		rango_inicial = '$rango_inicial',
		rango_final = '$rango_final', 
		fecha_limite = '$fecha_limite', 
		comentario = '$comentario',  		
		activo = '$estado',
		documento_id = '$documento_id' 
	WHERE secuencia_facturacion_id = '$secuencia_facturacion_id'";
$query = $mysqli->query($update) or die($mysqli->error);

if($query){
	echo 1;//REGISTRO MODIFICADO CORRECTAMENTE
	
	/*********************************************************************************************************************************************************************/
	//INGRESAR REGISTROS EN LA ENTIDAD HISTORIAL
	$historial_numero = historial();
	$estado_historial = "Modificar";
	$observacion_historial = "Se ha modificado el numero a la secuencia de facturacion con el prefijo: $prefijo y rangos desde $rango_inicial a $rango_final, numero anterior: $numero_anterior, numero nuevo: $siguiente";
	$modulo = "Secuencia Facturación";
	$insert = "INSERT INTO historial 
	   VALUES('$historial_numero','0','0','$modulo','$secuencia_facturacion_id','0','0','$fecha','$estado_historial','$observacion_historial','$usuario','$fecha_registro')";	 
	$mysqli->query($insert) or die($mysqli->error);
	/*********************************************************************************************************************************************************************/		
}else{
	echo 2;//ERROR AL ALMACENAR ESTE REGISTRO
}	
$mysqli->close();//CERRAR CONEXIÓN