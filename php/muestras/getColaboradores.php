<?php
session_start();   
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

$consulta = "SELECT c.colaborador_id AS 'colaborador_id', CONCAT(c.nombre,' ',c.apellido) AS 'colaborador'
	FROM colaboradores AS c
	INNER JOIN puesto_colaboradores AS pc
	ON c.puesto_id = pc.puesto_id
	WHERE pc.puesto_id IN(6)
	ORDER BY CONCAT(c.nombre,' ',c.apellido)";
$result = $mysqli->query($consulta) or die($mysqli->error);

if($result->num_rows>0){
	echo '<option value="">Seleccione</option>';
	while($consulta2 = $result->fetch_assoc()){
		echo '<option value="'.$consulta2['colaborador_id'].'">'.$consulta2['colaborador'].'</option>';
	}
}

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>