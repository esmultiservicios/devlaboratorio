<?php
session_start(); 
include('../funtions.php');
	
//CONEXION A DB
$mysqli = connect_mysqli(); 

//CONSULTA LOS DATOS DE LA ENTIDAD CORPORACION
$consulta = "SELECT tipo_muestra_id, nombre FROM tipo_muestra";

$result = $mysqli->query($consulta);	

if($result->num_rows>0){
	echo '<option value="0">Sin selección</option>';
	while($consulta2 = $result->fetch_assoc()){
		echo '<option value="'.$consulta2['tipo_muestra_id'].'">'.$consulta2['nombre'].'</option>';
	}
}else{
	echo '<option value="">No hay registros que mostrar</option>';
}

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>