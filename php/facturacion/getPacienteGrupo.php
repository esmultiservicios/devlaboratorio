<?php
session_start();
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

$tipo_paciente = $_POST['tipo_paciente'];

// Eliminamos el CONCAT del expediente
$query = "SELECT pacientes_id, 
			CONCAT(nombre,' ',apellido, ' - ', identidad) AS 'empresa'
	FROM pacientes 
	WHERE tipo_paciente_id = '$tipo_paciente' AND estado = 1
	ORDER BY nombre ASC
	LIMIT 500";

$result = $mysqli->query($query) or die($mysqli->error);

if($result->num_rows > 0){
	while($consulta2 = $result->fetch_assoc()){
		echo '<option value="'.$consulta2['pacientes_id'].'">'.$consulta2['empresa'].'</option>';
	}
} else {
	echo '<option value="">No hay datos que mostrar</option>';
}

$result->free();
$mysqli->close();
