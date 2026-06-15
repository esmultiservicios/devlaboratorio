<?php
//getEmpresa.php
session_start();
include('../funtions.php');

//CONEXION A DB
$mysqli = connect_mysqli();

//CONSULTA - Excluye nombres vacíos o NULL
$consulta = "SELECT pacientes_id, CONCAT(nombre, ' ', apellido) AS nombre
FROM pacientes
WHERE tipo_paciente_id = 2 
  AND estado = 1 
  AND nombre IS NOT NULL 
  AND nombre != ''
  AND TRIM(nombre) != ''
ORDER BY nombre ASC";

$result = $mysqli->query($consulta);

if(!$result){
	error_log("Error SQL en getEmpresa.php: " . $mysqli->error);
	echo '<option value="">Error de consulta</option>';
	$mysqli->close();
	exit;
}

if($result->num_rows > 0){
	while($consulta2 = $result->fetch_assoc()){
		echo '<option value="'.(int)$consulta2['pacientes_id'].'">'.htmlspecialchars(trim($consulta2['nombre']), ENT_QUOTES, 'UTF-8').'</option>';
	}
}else{
	echo '<option value="">No hay registros que mostrar</option>';
}

$result->free();
$mysqli->close();