<?php 
include('../funtions.php');
session_start(); 
	
//CONEXION A DB
$mysqli = connect_mysqli();

date_default_timezone_set('America/Tegucigalpa');
$fecha_sistema = date("Y-m-d");
$colaborador_id = $_SESSION['colaborador_id'];
$type = $_SESSION['type'];

$año = date("Y", strtotime($fecha_sistema));
$mes = date("m", strtotime($fecha_sistema));
$dia = date("d", mktime(0,0,0, $mes+1, 0, $año));

$dia1 = date('d', mktime(0,0,0, $mes, 1, $año)); //PRIMER DIA DEL MES
$dia2 = date('d', mktime(0,0,0, $mes, $dia, $año)); // ULTIMO DIA DEL MES

$fecha_inicial = date("Y-m-d", strtotime($año."-".$mes."-".$dia1));
$fecha_final = date("Y-m-d", strtotime($año."-".$mes."-".$dia2));
$nuevafecha = date("Y-m-d", strtotime ( '-1 day' , strtotime ( $fecha_sistema )));

//CONSULTAR USUARIOS
$query = "SELECT COUNT(calendario_id) AS 'total'
	FROM calendario
	WHERE CAST(fecha_cita AS DATE) BETWEEN '$fecha_inicial' AND '$fecha_final' AND estado = 0";

$result = $mysqli->query($query);

$total = 0;

if($result->num_rows>0){
	$consulta2=$result->fetch_assoc();
	$total = $consulta2['total']; 	
}	 

echo number_format($total);

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>