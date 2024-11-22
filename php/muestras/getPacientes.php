<?php
session_start();   
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

$tipo_paciente = $_POST['tipo_paciente'];

$consulta = "SELECT pacientes_id, CONCAT(nombre, ' ', apellido) AS 'paciente' 
    FROM pacientes
	WHERE tipo_paciente_id = '$tipo_paciente'";

$result = $mysqli->query($consulta) or die($mysqli->error);

if($result->num_rows>0){
	echo '<option value="">Seleccione</option>';
	while($consulta2 = $result->fetch_assoc()){
		echo '<option value="'.$consulta2['pacientes_id'].'">'.$consulta2['paciente'].'</option>';
	}
}

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>