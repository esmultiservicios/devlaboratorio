<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli(); 
 
$pacientes_id = $_POST['pacientes_id'];
$muestras_id = $_POST['muestras_id'];

//CONSULTAR LOS DATOS DEL PACIENTE
$sql = "SELECT p.identidad AS 'identidad', p.fecha_nacimiento 'fecha_nacimiento', CONCAT(p.nombre, ' ', p.apellido) AS 'paciente', p.localidad AS 'localidad', p.religion_id AS 'religion', p.profesion_id AS 'profesion', CAST(m.fecha AS DATE) AS 'fecha', m.servicio_id As 'servicio_id', m.number AS 'numero'
   FROM muestras AS m
   INNER JOIN pacientes AS p
   ON m.pacientes_id = p.pacientes_id
   WHERE m.muestras_id = '$muestras_id'";
$result = $mysqli->query($sql) or die($mysqli->error);  
     
$identidad = "";
$nombre = "";
$fecha_nacimiento = "";
$edad = "";
$profesion = "";
$religion = "";
$fecha_cita = "";
$paciente = "";
$anos = "";
$valores_array = "";
$localidad = "";
$servicio_id = "";
$numero = "";

//OBTENEMOS LOS VALORES DEL REGISTRO
if($result->num_rows>0){
	$consulta_registro = $result->fetch_assoc();
	
	$identidad = $consulta_registro['identidad'];
	$fecha_nacimiento = $consulta_registro['fecha_nacimiento'];	
	$paciente = $consulta_registro['paciente'];
	$localidad = $consulta_registro['localidad'];	
	$religion = $consulta_registro['religion'];
	$profesion = $consulta_registro['profesion'];
	$fecha_cita = $consulta_registro['fecha'];
	$servicio_id = $consulta_registro['servicio_id'];
	$numero = $consulta_registro['numero'];	
	
	//CONSULTA AÑO, MES y DIA DEL PACIENTE
	$valores_array = getEdad($fecha_nacimiento);
	$anos = $valores_array['anos'];
	/*********************************************************************************/
}

//OBTENER SEGUIMIENTO
$query_seguimiento = "SELECT fecha, seguimiento
	FROM atenciones_medicas
	WHERE pacientes_id = '$pacientes_id'";
$result_seguimiento = $mysqli->query($query_seguimiento) or die($mysqli->error);

$seguimiento_consulta = "";
	
while($registro_seguimiento = $result_seguimiento->fetch_assoc()){
	$fecha = $registro_seguimiento['fecha'];
	$seguimiento = $registro_seguimiento['seguimiento'];
	
	$seguimiento_consulta.= "Fecha: ".$fecha."\n".$seguimiento."\n\n";
}	

//CONSULTAMOS LA ATENCION DEL PACIENTE
$query = "SELECT atencion_id, antecedentes, historia_clinica, examen_fisico, diagnostico, seguimiento
   FROM atenciones_medicas
   WHERE pacientes_id = '$pacientes_id' AND fecha = '$fecha_cita' AND servicio_id = '$servicio_id' AND muestras_id = '$muestras_id'";
$result_existencia_atencion = $mysqli->query($query) or die($mysqli->error);

$antecedentes = '';
$historia_clinica = '';
$examen_fisico = '';
$diagnostico = '';
$seguimiento = '';

if($result_existencia_atencion->num_rows>0){
	$consulta_registro_atencion = $result_existencia_atencion->fetch_assoc();
	$antecedentes = $consulta_registro_atencion['antecedentes'];
	$historia_clinica = $consulta_registro_atencion['historia_clinica'];	
	$examen_fisico = $consulta_registro_atencion['examen_fisico'];
	$diagnostico = $consulta_registro_atencion['diagnostico'];	
	$seguimiento = $consulta_registro_atencion['seguimiento'];	
}

$datos = array(
	 0 => $identidad, 
 	 1 => $paciente,	
	 2 => $anos, 	
 	 3 => $localidad,		 
     4 => $pacientes_id,
     5 => $fecha_cita,
     6 => $servicio_id,	 
     7 => $numero,	 
     8 => $antecedentes,	 
     9 => $historia_clinica,	 
     10 => $examen_fisico,	
     11 => $diagnostico,		 
     12 => $seguimiento,	 	 
);	
	
echo json_encode($datos);

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>