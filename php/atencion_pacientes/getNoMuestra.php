<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$atencion_id = $_POST['atencion_id'];

//CONSULTAR DATOS DE FACTURA
$query = "SELECT m.number AS 'number'
	FROM atenciones_medicas AS am
	INNER JOIN muestras AS m
	ON am.muestras_id = m.muestras_id
	WHERE am.atencion_id = '$atencion_id'";
$result = $mysqli->query($query);
$no_muestra = "";

if($result->num_rows>=0){
	 $consultaMuestra = $result->fetch_assoc();
	 $no_muestra = $consultaMuestra['number'];
}

$datos = array(
	0 => $no_muestra,
);
echo json_encode($datos);

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>              