<?php
session_start();   
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

//OBTENEMOS EL DESCUENTO A APLICAR SEGUN LO ESTABLECIDO POR EL PROFESIONAL
$query = "SELECT * FROM documento";
$result = $mysqli->query($query);

if($result->num_rows>0){	
	echo '<option value="">Todos</option>';
	while($consulta2 = $result->fetch_assoc()){
		echo '<option value="'.$consulta2['documento_id'].'">'.$consulta2['nombre'].'</option>';
	}
}else{
	echo '<option value="">No hay datos que mostrar</option>';
}

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN