<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();
 
$muestras_id = $_POST['muestras_id'];

$query = "SELECT material_eviando 
    FROM muestras 
	WHERE muestras_id = '$muestras_id'";
$result = $mysqli->query($query);

if($result->num_rows>=0){
	$valores = $result->fetch_assoc();
	$material_eviando = $valores['material_eviando'];
}

$datos = array(
	0 => $material_eviando,
);

echo json_encode($datos);
$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>