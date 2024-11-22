<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$pacientes_id = $_POST['pacientes_id'];

$query = "SELECT p.pacientes_id,  CONCAT(p.nombre,' ',p.apellido) AS 'paciente', p.identidad As 'identidad', p.telefono1 As 'telefono1', p.telefono2 AS 'telefono2', p.fecha_nacimiento AS 'fecha_nacimiento', p.email AS 'email',
(CASE WHEN p.estado = '1' THEN 'Activo' ELSE 'Inactivo' END) AS 'estado',
(CASE WHEN p.genero = 'H' THEN 'Hombre' ELSE 'Mujer' END) AS 'genero',
(CASE WHEN expediente = '0' THEN 'TEMP' ELSE expediente END) AS 'expediente',
pro.nombre AS 'profesional_paciente', r.nombre AS 'religion', p.edad AS 'edad'
	FROM pacientes AS p
	LEFT JOIN profesion AS pro
	ON p.profesion_id  = pro.profesion_id 
	LEFT JOIN religion AS r
	ON p.religion_id = r.religion_id
	WHERE p.pacientes_id = '$pacientes_id'";
$result = $mysqli->query($query);

if($result->num_rows>0){
	$consulta1=$result->fetch_assoc();
	$nombre = $consulta1['paciente'];
	$identidad = $consulta1['identidad'];
	$telefono1 = $consulta1['telefono1'];
	$telefono2 = $consulta1['telefono2'];
	$fecha_nacimiento = $consulta1['fecha_nacimiento'];
	$estado = $consulta1['estado'];
	$genero = $consulta1['genero'];	
	$email = $consulta1['email'];		
	$expediente = $consulta1['expediente'];	
	$profesional_paciente = $consulta1['profesional_paciente'];		
	$religion = $consulta1['religion'];	
	$edad = $consulta1['edad'];
  
	//OBTENER LA EDAD DEL USUARIO 
	/*********************************************************************************/
	$valores_array = getEdad($fecha_nacimiento);
	$anos = $valores_array['anos'];
	$meses = $valores_array['meses'];	  
	$dias = $valores_array['dias'];	
	/*********************************************************************************/  

	if ($anos>1 ){
	   $palabra_anos = "Años";
	}else{
	  $palabra_anos = "Año";
	}

	if ($meses>1 ){
	   $palabra_mes = "Meses";
	}else{
	  $palabra_mes = "Mes";
	}

	if($dias>1){
		$palabra_dia = "Días";
	}else{
		$palabra_dia = "Día";
	}

echo "  
	<div class='form-row'>
		<div class='col-md-12 mb-6 sm-3'>
		  <p style='color: #333FFF;' align='center'><b>Información</b></p>
		</div>					
	</div>
	<div class='form-row'>
		<div class='col-md-4 mb-3'>
		  <p><b>Nombre:</b> $nombre</p>
		</div>
		<div class='col-md-4 mb-3'>
		  <p><b>Expediente:</b> $expediente</p>
		</div>					
		<div class='col-md-4 mb-3'>
		  <p><b>Genero:</b> $genero</p>
		</div>					
		<div class='col-md-4 mb-3 sm-3'>
		  <p><b>Edad:</b> $edad <b></p>
		</div>	
		<div class='col-md-4 mb-3 sm-3'>
		  <p>Teléfono 1:</b> $telefono1</p>
		</div>	
		<div class='col-md-4 mb-3 sm-3'>
		  <p><b>Telefono 2:</b> $telefono2</p>
		</div>		
	</div>	
	<div class='form-row'>
		<div class='col-md-4 mb-3 sm-3'>
		  <p><b>Correo:</b> $email</p>
		</div>	
		<div class='col-md-4 mb-3 sm-3'>
		  <p><b>Estado:</b> $estado</p>
		</div>	
		<div class='col-md-4 mb-3 sm-3'>
		  <p><b>Identidad:</b> $identidad</p>
		</div>			
	</div>	
"; 
}else{
	echo 1;
}

$mysqli->close();//CERRAR CONEXIÓN
?>