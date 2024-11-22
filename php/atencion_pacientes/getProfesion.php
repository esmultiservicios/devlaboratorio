<?php
session_start();   
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

$consulta = "SELECT * FROM profesion";
$result = $mysqli->query($consulta) or die($mysqli->error);

if($result->num_rows>0){	
	while($consulta2 = $result->fetch_assoc()){
		echo '<option value="'.$consulta2['profesion_id'].'">'.$consulta2['nombre'].'</option>';
	}
}else{
	echo '<option value="">No hay datos que mostrar</option>';
}

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>