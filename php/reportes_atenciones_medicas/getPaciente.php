<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$atencion_id = $_POST['atencion_id'];

//CONSULTA LOS DATOS DE LA ENTIDAD CORPORACION
$consulta = "SELECT CONCAT(p.nombre, ' ', p.apellido) AS 'paciente' 
    FROM atenciones_medicas AS am
    INNER JOIN pacientes AS p
    ON am.pacientes_id = p.pacientes_id
    WHERE am.atencion_id = '$atencion_id'";
$result = $mysqli->query($consulta);	

$paciente = "";

if($result->num_rows>0){
	$consulta2 = $result->fetch_assoc();
	$paciente = $consulta2['paciente'];
}

$datos = array(
	 0 => $paciente,  	 
);	
	
echo json_encode($datos);

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>       