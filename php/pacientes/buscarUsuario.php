<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$pacientes_id = $_POST['id'];
$usuario = $_SESSION['colaborador_id'];
$estado = 1; //1. Activo 2. Inactivo
$fecha_registro = date("Y-m-d H:i:s");

//OBTENEMOS LOS VALORES DEL REGISTRO

//CONSULTA EN LA ENTIDAD CORPORACION
$query = "SELECT pacientes_id,  nombre, apellido, identidad, fecha_nacimiento, identidad, fecha, genero,
(CASE WHEN estado = '1' THEN 'Activo' ELSE 'Inactivo' END) AS 'estado',
(CASE WHEN expediente = '0' THEN 'TEMP' ELSE expediente END) AS 'expediente', edad
			 FROM pacientes
			 WHERE pacientes_id = '$pacientes_id'";
$result = $mysqli->query($query);

$nombre_completo = "";
$expediente = "";
$identidad = "";
$nombre = "";
$apellido = "";
$sexo = "";
$fecha_nacimiento = "";
$fecha = "";
$anos = "";
$meses = "";
$edad = "";

if($result->num_rows>0){
	$consulta_expediente1 = $result->fetch_assoc();
	$expediente = $consulta_expediente1['expediente'];
	$identidad = $consulta_expediente1['identidad'];
	$nombre = $consulta_expediente1['nombre'];
	$apellido = $consulta_expediente1['apellido'];
	$sexo = $consulta_expediente1['genero'];
	$fecha_nacimiento = $consulta_expediente1['fecha_nacimiento'];
	$fecha = $consulta_expediente1['fecha'];
	$edad = $consulta_expediente1['edad'];	
		 
	if( strlen($identidad)<10 ){
		$bloqueo = 2; //NO SE BLOQUEA	   	   
	}else{
		$bloqueo = 1; //SI SE BLOQUEA	   
	}	 
		 
	$nombre_completo = $nombre." ".$apellido;
}			 
	 
$datos = array(
				0 => $nombre_completo, 
				1 => $identidad, 
				2 => $sexo, 
				3 => $fecha_nacimiento, 	
                4 => $fecha,	
                5 => $expediente,	
                6 => $edad,			
				);
echo json_encode($datos);

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>