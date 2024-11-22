<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

if(isset($_POST['editar_atencion'])){//COMPRUEBO SI LA VARIABLE ESTA DIFINIDA
	if($_POST['editar_atencion'] == ""){
		$editar_atencion = 2;
	}else{
		$editar_atencion = $_POST['editar_atencion'];
	}
}else{
	$editar_atencion = 2;
}

$pacientes_id = $_POST['pacientes_id'];
$fecha = $_POST['fecha'];
$antecedentes = cleanString($_POST['antecedentes']);
$muestras_id = $_POST['muestras_id'];
$bioxia_numero = $_POST['bioxia_numero'];
$servicio_id = $_POST['servicio_id'];
$historia_clinica = cleanString($_POST['historia_clinica']);
$examen_fisico = cleanString($_POST['exame_fisico']);
$diagnostico = cleanString($_POST['diagnostico']);
$seguimiento = cleanString($_POST['seguimiento']);

//OBTENER EL COLABORADOR QUE ENVIA LA MUESTRA
$query_colabodor_muestra = "SELECT colaborador_id
	FROM muestras
	WHERE muestras_id = '$muestras_id'";
$result_colabodor_muestra = $mysqli->query($query_colabodor_muestra) or die($mysqli->error);

$consulta_colaborador_muestra = 0;

if($result_colabodor_muestra->num_rows>0){
	$consulta_colaborador_muestra= $result_colabodor_muestra->fetch_assoc();
	$colaborador_id = $consulta_colaborador_muestra['colaborador_id'];	
}

$hora = date("H:i", strtotime('00:00'));
$fecha_cita =  date("Y-m-d H:i:s", strtotime($fecha));
$fecha_cita_end =  date("Y-m-d H:i:s", strtotime($fecha));
$fecha_registro = date("Y-m-d H:i:s");

//ESTADO DE LA ATENCION DEL PACIENTE PARA LA FACTURACION 1. PENDIENTE 2. REALIZADO
if($editar_atencion == 1){
	$estado = 1;
}else{
	$estado = 2;
}

$consultar_tipo_paciente = "SELECT atencion_id 
	FROM atenciones_medicas AS am
	INNER JOIN colaboradores AS c
	ON am.colaborador_id = c.colaborador_id
	WHERE am.pacientes_id = '$pacientes_id' AND am.colaborador_id = '$colaborador_id' AND am.servicio_id = '$servicio_id'";
$result_tipo_paciente = $mysqli->query($consultar_tipo_paciente) or die($mysqli->error);

$tipo_paciente = '';

if($result_tipo_paciente->num_rows==0){
	$tipo_paciente = 'N';
}else{
	$tipo_paciente = 'S';
}

//CONSULTA DATOS DEL PACIENTE
$query = "SELECT CONCAT(nombre, ' ', apellido) AS 'paciente', identidad, expediente AS 'expediente', fecha_nacimiento, edad
	FROM pacientes
	WHERE pacientes_id = '$pacientes_id'";
$result = $mysqli->query($query) or die($mysqli->error);

$paciente = '';
$identidad = '';
$expediente = '';
$fecha_nacimiento = '';
$edad = '';
$atencion_consulta = '';

if($result->num_rows>=1){
	$consulta_registro = $result->fetch_assoc();
	$paciente = $consulta_registro['paciente'];
	$identidad = $consulta_registro['identidad'];
	$expediente = $consulta_registro['expediente'];
	$fecha_nacimiento = $consulta_registro['fecha_nacimiento'];	
	$edad = $consulta_registro['edad'];		
}	

//CONSULTAMOS SI EXITE LA ATENCION
$query = "SELECT atencion_id 
   FROM atenciones_medicas
   WHERE pacientes_id = '$pacientes_id' AND fecha = '$fecha' AND servicio_id = '$servicio_id' AND muestras_id = '$muestras_id'";

$result_existencia = $mysqli->query($query) or die($mysqli->error);   

//OBTENER CORRELATIVO
if($pacientes_id != 0){
	if($servicio_id != 0){
		if($result_existencia->num_rows == 0){		
			$correlativo = correlativo('atencion_id', 'atenciones_medicas');

			//ALMACENAMOS EL REGISTRO EN LA ENTIDAD atenciones_medicas
			$insert = "INSERT INTO atenciones_medicas VALUES('$correlativo','$muestras_id','$pacientes_id','$edad','$fecha','$antecedentes','$historia_clinica','$examen_fisico','$diagnostico','$seguimiento','$tipo_paciente','$servicio_id','$colaborador_id','$estado','$fecha_registro')";

			$query = $mysqli->query($insert) or die($mysqli->error);

			if($query){
				//FILE IMAGE
				if($_FILES['files']['name']!=""){
					//CONTAMOS CUANTOS REGISTROS VIENEN EN EL INPUT FILE DEL
					$totalfiles = count($_FILES['files']['name']);
					echo "El total de archivos encontrados es: ".$totalfiles."***";

					//RRECORREMOS LOS REGISTROS
					for($i=0;$i<$totalfiles;$i++){
						$file = $_FILES['file']['name'][$i];
						$path = $_SERVER["DOCUMENT_ROOT"].PRODUCT_PATH.$file;
						echo "El nombre del archivo es: ".$file."***";
						echo "La dirección del archivo es: ".$path."***";

						//MOVEMOS LA IMAGEN EN LA CARPETA DE EXAMENES
						if (file_exists($path)) {
							$file_exist = 1;
						}else{
							$cargar_imagen = move_uploaded_file($_FILES['file']['tmp_name'][$i],$path);
							if($cargar_imagen){

								$examenes_id = correlativo('examenes_id', 'examenes');

								$insert = "INSERT INTO examenes 
								VALUES('$examenes_id','$correlativo','$file','$colaborador_id','$fecha_registro')";	
								$mysqli->query($insert) or die($mysqli->error);						
							}
						}
					}				
				}

				//ACTUALIZAMOS EL CALENDARIO PARA INDICAR QUE LA MUESTRA SE HA CULMINADO CON EXITO
				$update = "UPDATE calendario
				SET
					estado = '1'
				WHERE muestras_id = '$muestras_id'";
				$mysqli->query($update) or die($mysqli->error);

				if($editar_atencion == 2){
					$datos = array(
						0 => "Almacenado", 
						1 => "Registro Almacenado Correctamente, Puede seguir editando el registro", 
						2 => "success",
						3 => "btn-primary",
						4 => "formulario_atenciones",
						5 => "Registro",
						6 => "",//FUNCION DE LA TABLA QUE LLAMAREMOS PARA QUE ACTUALICE (DATATABLE BOOSTRAP)
						7 => "modal_registro_atenciones", //Modals Para Cierre Automatico
						8 => "",
						9 => "Guardar",				
					);						
				}else{
					$datos = array(
						0 => "Almacenado", 
						1 => "Registro Almacenado Correctamente", 
						2 => "success",
						3 => "btn-primary",
						4 => "formulario_atenciones",
						5 => "Registro",
						6 => "AtencionMedica",//FUNCION DE LA TABLA QUE LLAMAREMOS PARA QUE ACTUALICE (DATATABLE BOOSTRAP)
						7 => "modal_registro_atenciones", //Modals Para Cierre Automatico
						8 => $correlativo,
						9 => 'Guardar',					
					);
					
					//ACTUALIZAMOS EL ESTADO DE LA MUESTRTA
					$update = "UPDATE muestras SET estado = '$estado'
					   WHERE muestras_id = '$muestras_id'";	
					$mysqli->query($update) or die($mysqli->error);					
				}
				
				//INGRESAR REGISTROS EN LA ENTIDAD HISTORIAL
				$historial_numero = historial();
				$estado_historial = "Agregar";
				$observacion_historial = "Se ha agregado una nueva atención para este paciente: $paciente con identidad n° $identidad";
				$modulo = "Atención Pacientes";
				$insert = "INSERT INTO historial 
				   VALUES('$historial_numero','$pacientes_id','$expediente','$modulo','$correlativo','$colaborador_id','$servicio_id','$fecha','$estado_historial','$observacion_historial','$colaborador_id','$fecha_registro')";	 
				$mysqli->query($insert) or die($mysqli->error);
				/********************************************/
			}else{
				$datos = array(
					0 => "Error", 
					1 => "No se puedo almacenar este registro, los datos son incorrectos por favor corregir", 
					2 => "error",
					3 => "btn-danger",
					4 => "",
					5 => "",			
				);
			}
		}else{
			$consulta_registro_atencion = $result_existencia->fetch_assoc();
			$atencion_consulta = $consulta_registro_atencion['atencion_id'];
			//EVALUAMOS SI EL USUARIO DESEA SEGUIR EDITANDO EL REGISTROS

			if($editar_atencion == 2){//EL USUARIO DESEA SEGUIR EDITANDO EL REGISTRO
				//ACTUALIZAMOS LA ATENCION DEL PACIENTE
				$update = "UPDATE atenciones_medicas
					SET
						antecedentes = '$antecedentes',
						historia_clinica = '$historia_clinica',
						examen_fisico = '$examen_fisico',
						diagnostico = '$diagnostico',
						seguimiento = '$seguimiento'
					WHERE atencion_id = '$atencion_consulta'";
				$query_registro = $mysqli->query($update) or die($mysqli->error);

				if($query_registro){
					$datos = array(
						0 => "Almacenado", 
						1 => "Registro Almacenado Correctamente, Puede seguir editando el registro", 
						2 => "success",
						3 => "btn-primary",
						4 => "formulario_atenciones",
						5 => "Registro",
						6 => "",//FUNCION DE LA TABLA QUE LLAMAREMOS PARA QUE ACTUALICE (DATATABLE BOOSTRAP)
						7 => "modal_registro_atenciones", //Modals Para Cierre Automatico
						8 => "",
						9 => "Guardar",					
					);					
				}
			}else{
				//ACTUALIZAMOS LA ATENCION DEL PACIENTE
				$update = "UPDATE atenciones_medicas
					SET
						antecedentes = '$antecedentes',
						historia_clinica = '$historia_clinica',
						examen_fisico = '$examen_fisico',
						diagnostico = '$diagnostico',
						seguimiento = '$seguimiento',
						estado = '$estado'
					WHERE atencion_id = '$atencion_consulta'";
				$query_registro = $mysqli->query($update) or die($mysqli->error);				
				
				//ACTUALIZAMOS EL ESTADO DE LA MUESTRA
				$update = "UPDATE muestras SET estado = '$estado'
				   WHERE muestras_id = '$muestras_id'";	
				$mysqli->query($update) or die($mysqli->error);	

				if($query_registro){
					$datos = array(
						0 => "Almacenado", 
						1 => "Registro Almacenado Correctamente", 
						2 => "success",
						3 => "btn-primary",
						4 => "formulario_atenciones",
						5 => "Registro",
						6 => "AtencionMedica",//FUNCION DE LA TABLA QUE LLAMAREMOS PARA QUE ACTUALICE (DATATABLE BOOSTRAP)
						7 => "modal_registro_atenciones", //Modals Para Cierre Automatico
						8 => $atencion_consulta,
						9 => 'Guardar',					
					);					
				}
			}
			
			//INGRESAR REGISTROS EN LA ENTIDAD HISTORIAL
			$historial_numero = historial();
			$estado_historial = "Agregar";
			$observacion_historial = "Se han actualizado los datos de la atención para este paciente: $paciente con identidad n° $identidad";
			$modulo = "Atención Pacientes";
			$insert = "INSERT INTO historial 
			   VALUES('$historial_numero','$pacientes_id','$expediente','$modulo','$atencion_consulta','$colaborador_id','$servicio_id','$fecha','$estado_historial','$observacion_historial','$colaborador_id','$fecha_registro')";	 
			$mysqli->query($insert) or die($mysqli->error);
			/********************************************/			
		}
	}else{
		$datos = array(
			0 => "Error", 
			1 => "Lo sentimos, debe seleccionar un consultorio antes de continuar, por favor corregir", 
			2 => "error",
			3 => "btn-danger",
			4 => "",
			5 => "",			
		);
	}		
}else{
	$datos = array(
		0 => "Error", 
		1 => "Lo sentimos, debe seleccionar un paciente antes de continuar, por favor corregir", 
		2 => "error",
		3 => "btn-danger",
		4 => "",
		5 => "",			
	);	
}
echo json_encode($datos);

$mysqli->close();//CERRAR CONEXIÓN
?>