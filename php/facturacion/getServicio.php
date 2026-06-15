<?php
session_start();
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

$query = "SELECT servicio_id, nombre 
	FROM servicios 
	ORDER BY nombre ASC";

$result = $mysqli->query($query) or die($mysqli->error);

echo '<option value="">Seleccione un servicio</option>';

if($result->num_rows > 0){
	while($consulta = $result->fetch_assoc()){
		echo '<option value="'.$consulta['servicio_id'].'">'.$consulta['nombre'].'</option>';
	}
} else {
	echo '<option value="">No hay servicios registrados</option>';
}

$result->free();
$mysqli->close();