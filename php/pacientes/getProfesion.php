<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$query = "SELECT profesion_id, nombre 
   FROM profesion
   ORDER BY nombre"; 
$result = $mysqli->query($query); 
  
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