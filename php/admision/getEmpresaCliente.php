<?php
	session_start();
	include('../funtions.php');

	//CONEXION A DB
	$mysqli = connect_mysqli();
	$tipo = $_POST['tipo'];
	//CONSULTA LOS DATOS DE LA ENTIDAD CORPORACION
	$consulta = "SELECT pacientes_id, CONCAT(nombre, ' ', apellido) AS 'nombre'
	FROM pacientes
	WHERE tipo_paciente_id = '$tipo' AND estado = 1";

	$result = $mysqli->query($consulta);

	if($result->num_rows>0){
		while($consulta2 = $result->fetch_assoc()){
			echo '<option value="'.$consulta2['pacientes_id'].'">'.$consulta2['nombre'].'</option>';
		}
	}else{
		echo '<option value="">No hay registros que mostrar</option>';
	}

	$result->free();//LIMPIAR RESULTADO
	$mysqli->close();//CERRAR CONEXIÓN
?>
