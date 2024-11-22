<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$query = "SELECT departamento_id, nombre 
   FROM departamentos
   ORDER BY nombre"; 
$result = $mysqli->query($query);
  
if($result->num_rows>0){	
	while($consulta2 = $result->fetch_assoc()){
	     echo '<option value="'.$consulta2['departamento_id'].'">'.$consulta2['nombre'].'</option>';
	}
	echo "</optgroup>";
}else{
	echo '<option value="">No hay datos que mostrar</option>';
}

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>