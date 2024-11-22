<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli(); 

$muestras_id = $_POST['muestras_id'];
$comentario = cleanStringStrtolower($_POST['comentario']);
$usuario_sistema = $_SESSION['colaborador_id'];

//CONSULTAR PACIENTES_ID DE LA ENTIDAD PACIENTES
$consultar = "SELECT m.fecha AS 'fecha', p.expediente, p.pacientes_id, m.servicio_id As 'servicio_id', m.colaborador_id AS 'colaborador_id' 
    FROM muestras AS m 
    INNER JOIN pacientes AS p
	WHERE muestras_id = '$muestras_id'";
$result = $mysqli->query($consultar);
$consultar2 = $result->fetch_assoc();

$fecha = "";
$pacientes_id = "";
$expediente = "";
$servicio_id = "";
$colaborador_id = "";

if($result->num_rows>0){
	$fecha = $consultar2['fecha'];
	$pacientes_id = $consultar2['pacientes_id'];
	$expediente = $consultar2['expediente'];
	$servicio_id = $consultar2['servicio_id'];
	$colaborador_id = $consultar2['colaborador_id'];	
}

$fecha_registro = date("Y-m-d H:i:s");
$fecha_sistema = date("Y-m-d");
$usuario_sistema = $_SESSION['colaborador_id'];

//CONSULTAR EL NOMBRE DEL SERVICIO
$consulta_servicio = "SELECT nombre 
    FROM servicios 
	WHERE servicio_id = '$servicio_id'";
$result = $mysqli->query($consulta_servicio);
$consulta_servicio2 = $result->fetch_assoc();

$servicio_nombre = "";

if($result->num_rows>0){
	$servicio_nombre = $consulta_servicio2['nombre'];
}

//CONSULTAMOS DATOS DEL PACIENTES
$consulta_paciente = "SELECT *
	FROM pacientes 
	WHERE pacientes_id = '$pacientes_id'";
$result = $mysqli->query($consulta_paciente);	
$consulta_paciente2 = $result->fetch_assoc();

$expediente = "";
$identidad = "";
$usuario = "";
$nombre = "";
$apellido = "";
$fecha_nacimiento = "";
$telefono1 = "";
$telefono2 = "";
$sexo = "";
$localidades = "";
$departamento = "";
$municipio = "";
$correo = "";
$consulta_paciente_status = "";

if($result->num_rows>0){
	$expediente = $consulta_paciente2['expediente'];
	$identidad = $consulta_paciente2['identidad'];
	$usuario = $consulta_paciente2['usuario'];
	$nombre = $consulta_paciente2['nombre'];
	$apellido = $consulta_paciente2['apellido'];
	$fecha_nacimiento = $consulta_paciente2['fecha_nacimiento'];
	$telefono1 = $consulta_paciente2['telefono1'];
	$telefono2 = $consulta_paciente2['telefono2'];
	$sexo = $consulta_paciente2['genero'];
	$localidades = $consulta_paciente2['localidad'];
	$departamento = $consulta_paciente2['departamento_id'];
	$municipio = $consulta_paciente2['municipio_id'];
	$correo = $consulta_paciente2['email'];
	$consulta_paciente_status = $consulta_paciente2['estado'];
}

if($consulta_paciente_status == 1){
   $estado_paciente_ = "Activo";
}else if($consulta_paciente_status == 2 || $consulta_paciente_status == 4){
   $estado_paciente_ = "Pasivo";
}else if($consulta_paciente_status == 3){
   $estado_paciente_ = "Fallecido";
}   

//CONSULTAR PUESTO COLABORADOR
$consulta_puesto = "SELECT puesto_id 
     FROM colaboradores 
	 WHERE colaborador_id = '$colaborador_id'";
$result = $mysqli->query($consulta_puesto);
$consulta_puesto1 = $result->fetch_assoc(); 

$puesto_colaborador = "";

if($result->num_rows>0){
	$puesto_colaborador = $consulta_puesto1['puesto_id'];
}

//CONSULTAMOS LA EXISTENCIA DE LA AUSENCIA
$consulta = "SELECT ausencia_id 
       FROM ausencias 
	   WHERE pacientes_id = '$pacientes_id' AND colaborador_id = '$colaborador_id' AND servicio_id = '$servicio_id' AND fecha = '$fecha'";
$result = $mysqli->query($consulta);
$consulta2 = $result->fetch_assoc();

$ausencia_id = "";	

if($result->num_rows>0){
	$ausencia_id = $consulta2['ausencia_id'];
}
/*INCIO CORRELATIVOS DE LAS ENTIDADES*/

//OBTENER CORRELATIVO DE LA ENTIDAD AUSENCIA
$numero = correlativo("ausencia_id", "ausencias");

//OBTENER CORRELATIVO HISTORIAL PACIENTES
$numero_historial = correlativo("historial_id", "historial_pacientes");

/*********************************************************************************/
if($ausencia_id == ""){//EVALUAMOS SI AL USUARIO NO SE LE HA ALMACENADO UNA ASENCIA EN LA ENTIDAD AUSENCIAS
	//CAMBIAMOS EL ESTATUS DEL USAURIO EN LA ENTIDAD AGENDA, COLOCONADOLE 2 (AUSENCIA)
	$update = "UPDATE muestras SET estado = 2
	   WHERE muestras_id = '$muestras_id'";
	$query = $mysqli->query($update);
	
	//INGRESAR REGISTROS EN LA ENTIDAD HISTORIAL
	$historial_numero = historial();
	$estado = "Actualizar";
	$observacion = "Se actualiza el campo preclinica en la entidad agenda para el servicio: $servicio_nombre";
	$modulo = "Agenda";
	$insert = "INSERT INTO historial 
		  VALUES('$historial_numero','$pacientes_id','$expediente','$modulo','$muestras_id','$colaborador_id','$servicio_id','$fecha','$estado','$observacion','$usuario_sistema','$fecha_registro')";	 
	$mysqli->query($insert);
	/*****************************************************/			

	//SE HACE LA CONSULTA DE EL TIPO DE PACIENTE QUE SE ESTA ATENDIENDO
	$consultar_expediente = "SELECT m.muestras_id 
		 FROM muestras AS m 
		 INNER JOIN colaboradores AS c
		 ON m.colaborador_id = c.colaborador_id
		 WHERE m.pacientes_id = '$pacientes_id' AND c.puesto_id = '$puesto_colaborador' AND m.servicio_id = '$servicio_id' AND m.estado = 1";
	$result = $mysqli->query($consultar_expediente);
	$consultar_expediente1 = $result->fetch_assoc(); 

	//SE EVALUA EL TIPO DE PACUENTE/USUARIO SI ES NUEVO O SUBSIGUIENTE
	if ($consultar_expediente1['muestras_id']== ""){
	   $paciente = 'N';
	}else{
	   $paciente = 'S';
	}

	if($query){
		//INSERTAMOS LOS VALORES EN LA ENTIDAD AUSENCIA
		$insert = "INSERT INTO ausencias 
				  VALUES('$numero','$pacientes_id', '$expediente', '$muestras_id','$fecha','$comentario','$usuario_sistema','$colaborador_id','$servicio_id','$paciente','$fecha_registro')";
		$mysqli->query($insert);
					
		//INGRESAR REGISTROS EN LA ENTIDAD HISTORIAL
		$historial_numero = historial();
		$estado = "Agregar";
		$observacion = "Se agrega la ausencia del usuario en el servicio: $servicio_nombre";
		$modulo = "Ausencias";
		$insert = "INSERT INTO historial 
			 VALUES('$historial_numero','$pacientes_id','$expediente','$modulo','$numero','$colaborador_id','$servicio_id','$fecha','$estado','$observacion','$usuario_sistema','$fecha_registro')";	 
		$mysqli->query($insert);
		/*****************************************************/
		echo 1;//REGISTRO PROCESADO CORRECTAMENTE
	}else{
		echo 2;//ERROR AL PROCESAR LA SOLICITUD
	}		
}else{
   echo 3;//NO SE PUEDE ALMACENAR LA AUSENCIA DE ESTE USUARIO, DEBIDO A QUE YA HA SIDO ALMACENADA.
}
/*********************************************************************************/

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>