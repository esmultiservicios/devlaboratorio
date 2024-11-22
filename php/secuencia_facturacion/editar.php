<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli(); 
 
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
$siguiente = "";
$rango_inicial = "";
$rango_final = "";
$fecha_limite = "";
$activo = "";
$comentario = "";
$documento = "";
$fecha_registro = "";

//OBTENEMOS LOS VALORES DEL REGISTRO
if($result->num_rows>0){
	$empresa = $consulta_registro['empresa_id'];
	$cai = $consulta_registro['cai'];	
	$prefijo = $consulta_registro['prefijo'];
	$relleno = $consulta_registro['relleno'];	
	$incremento = $consulta_registro['incremento'];
	$siguiente = $consulta_registro['siguiente'];
	$rango_inicial = $consulta_registro['rango_inicial'];	
	$rango_final = $consulta_registro['rango_final'];
	$fecha_limite = $consulta_registro['fecha_limite'];
	$activo = $consulta_registro['activo'];	
	$comentario = $consulta_registro['comentario'];		
	$documento = $consulta_registro['documento_id'];	
	$fecha_registro = $consulta_registro['fecha_registro'];
}
	
$datos = array(
	 0 => $empresa, 
	 1 => $cai, 	 
	 2 => $prefijo, 
	 3 => $relleno, 
	 4 => $incremento, 
	 5 => $siguiente, 
	 6 => $rango_inicial, 
	 7 => $rango_final, 
	 8 => $fecha_limite, 
	 9 => $activo, 	
	 10 => $comentario, 
	 11 => $documento, 		 
	 12 => $fecha_registro	
);	
	
echo json_encode($datos);

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN