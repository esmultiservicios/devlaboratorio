<?php
session_start(); 
include('../funtions.php');
	
//CONEXION A DB
$mysqli = connect_mysqli(); 

//CONSULTA LOS DATOS DE LA ENTIDAD CORPORACION
$tipo_muestra_id = $_POST['tipo_muestra_id'];
$consulta = "SELECT productos_id, nombre
FROM productos
WHERE tipo_muestra_id = '$tipo_muestra_id'";

$result = $mysqli->query($consulta);	

if($result->num_rows>0){
	while($consulta2 = $result->fetch_assoc()){
		echo '<option value="'.$consulta2['productos_id'].'">'.$consulta2['nombre'].'</option>';
	}
}else{
	echo '<option value="">No hay registros que mostrar</option>';
}

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>