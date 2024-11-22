<?php
session_start();   
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli(); 

$atenciones_id = $_POST['atenciones_id'];

$query = "SELECT *
	FROM plantillas
	WHERE atenciones_id = '$atenciones_id'";
$result = $mysqli->query($query) or die($mysqli->error);			  

if($result->num_rows>0){
	while($consulta2 = $result->fetch_assoc()){
		echo '<option value="'.$consulta2['plantillas_id'].'">'.$consulta2['asunto'].'</option>';
	}
}else{
	echo '<option value="">no hay datos que mostrar</option>';
}

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>