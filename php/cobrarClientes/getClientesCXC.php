<?php	
	session_start();   
	include "../funtions.php";

	//CONEXION A DB
	$mysqli = connect_mysqli(); 

	$query = "SELECT c.pacientes_id AS 'pacientes_id', p.nombre AS 'nombre'
		FROM cobrar_clientes AS c
		INNER JOIN pacientes AS p
		ON c.pacientes_id = p.pacientes_id
		WHERE p.estado = 1
		GROUP BY p.nombre;";
	$result = $mysqli->query($query);	
	
	if($result->num_rows>0){
		while($consulta2 = $result->fetch_assoc()){
			 echo '<option value="'.$consulta2['pacientes_id'].'">'.$consulta2['nombre'].'</option>';
		}
	}else{
		echo '<option value="">Seleccione</option>';
	}