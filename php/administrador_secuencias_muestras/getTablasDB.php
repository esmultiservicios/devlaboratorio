<?php
session_start();   
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

//OBTENEMOS EL DESCUENTO A APLICAR SEGUN LO ESTABLECIDO POR EL PROFESIONAL
$result = getTablesDB();

if($result->num_rows>0){
	while($consulta2 = $result->fetch_row()){
		echo '<option value="'.$consulta2[0].'">'.$consulta2[0].'</option>';
	}
}else{
	echo '<option value="">No hay datos que mostrar</option>';
}

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>