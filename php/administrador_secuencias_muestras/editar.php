<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli(); 

$secuencias_id = $_POST['secuencias_id'];

//CONSULTAR DATOS DEL METODO DE PAGO
$query = "SELECT * FROM secuencias_muestas
	WHERE secuencias_id = '$secuencias_id'";
$result = $mysqli->query($query) or die($mysqli->error);
$consulta_registro = $result->fetch_assoc();   
     
$empresa_id = "";
$tipo_muestra_id = "";
$prefijo = "";
$sufijo = "";
$relleno = "";
$incremento = "";
$siguiente = "";
$estado = "";
$comentario = "";

//OBTENEMOS LOS VALORES DEL REGISTRO
if($result->num_rows>0){
	$empresa_id = $consulta_registro['empresa_id'];
	$tipo_muestra_id = $consulta_registro['tipo_muestra_id'];	
	$prefijo = $consulta_registro['prefijo'];
	$sufijo = $consulta_registro['sufijo'];	
	$relleno = $consulta_registro['relleno'];
	$incremento = $consulta_registro['incremento'];
	$siguiente = $consulta_registro['siguiente'];	
	$estado = $consulta_registro['estado'];
	$comentario = $consulta_registro['comentario'];		
}
	
$datos = array(
	 0 => $empresa_id, 
	 1 => $tipo_muestra_id, 	 
	 2 => $prefijo, 
	 3 => $sufijo, 
	 4 => $relleno, 
	 5 => $incremento, 
	 6 => $siguiente, 
	 7 => $estado, 
	 8 => $comentario, 		 
);	
	
echo json_encode($datos);

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>