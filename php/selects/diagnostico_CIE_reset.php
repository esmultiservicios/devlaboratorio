<?php 
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$id = $_POST['id'];

//CONSULTA LOS DATOS DE LA ENTIDAD CORPORACION
$consulta = "SELECT * 
    FROM patologia 
	WHERE patologia_id like '$id%' ORDER BY id";
$result = $mysqli->query($consulta);

if($result->num_rows>0){
	while($consulta2 = $result->fetch_assoc()){
		echo '<option value="'.$consulta2['id'].'">'.$consulta2['patologia_id'].'</option>';
	}
}else{
	echo '<option value="">No hay datos que mostrar</option>';
}

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>