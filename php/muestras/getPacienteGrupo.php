<?php
session_start();   
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

$tipo_paciente = $_POST['tipo_paciente'];

//OBTENEMOS EL DESCUENTO A APLICAR SEGUN LO ESTABLECIDO POR EL PROFESIONAL
$query = "SELECT p.pacientes_id, CONCAT(p.nombre,' ',p.apellido) AS 'empresa'
	FROM muestras AS m
	INNER JOIN pacientes AS p
	ON m.pacientes_id = p.pacientes_id
	WHERE p.tipo_paciente_id = '$tipo_paciente'
	GROUP BY p.pacientes_id";
$result = $mysqli->query($query) or die($mysqli->error);

if($result->num_rows>0){
	echo '<option value="">Cliente</option>';
	while($consulta2 = $result->fetch_assoc()){
		echo '<option value="'.$consulta2['pacientes_id'].'">'.$consulta2['empresa'].'</option>';
	}
}else{
	echo '<option value="">Cliente</option>';
}

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>