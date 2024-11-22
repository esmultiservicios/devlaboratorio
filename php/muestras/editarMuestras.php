<?php	
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$muestras_id  = $_POST['muestras_id'];

$query = "SELECT m.pacientes_id AS 'paciente_consulta', mh.pacientes_empresa_id AS 'empresa', mh.pacientes_id AS 'pacientes_id', m.fecha AS 'fecha', m.diagnostico_clinico AS 'diagnostico_clinico', m.material_eviando As 'material_eviando', m.datos_clinico As 'datos_clinico', (CASE WHEN m.estado = '1' THEN 'Atendido' ELSE 'Pendiente' END) AS 'estatus', m.muestras_id  As 'muestras_id', m.mostrar_datos_clinicos As 'mostrar_datos_clinicos', m.colaborador_id AS 'remitente', m.hospitales_id AS 'hospitales_id', m.servicio_id AS 'servicio_id', m.colaborador_id AS 'colaborador_id', m.sitio_muestra AS 'sitio_muestra', m.tipo_muestra_id AS 'tipo_muestra_id', m.categoria_id AS 'categoria_id', CONCAT(p.nombre, ' ', p.apellido) AS 'empresa', CONCAT(p1.nombre, ' ', p1.apellido) AS 'paciente', p.tipo_paciente_id AS 'tipo_paciente_id', mh.pacientes_id AS 'muestra_paciente_id'
	FROM muestras AS m
	LEFT JOIN muestras_hospitales AS mh
	ON m.muestras_id = mh.muestras_id
	INNER JOIN pacientes AS p
	ON m.pacientes_id = p.pacientes_id
	LEFT JOIN pacientes AS p1
	ON mh.pacientes_id = p1.pacientes_id
	WHERE m.muestras_id = '$muestras_id'";
$result = $mysqli->query($query) or die($mysqli->error);

$paciente = "";
$paciente_empresa = "";
$fecha = "";
$diagonostico_muestra = "";
$material_muestra = "";
$datos_relevantes_muestras = "";
$mostrar_datos_clinicos = "";
$remitente = "";
$hospitales_id = "";
$servicio_id = "";
$sitio_muestra = "";
$tipo_muestra_id = "";
$categoria_id = "";
$tipo_paciente_id = "";
$muestra_paciente_id = "";

if($result->num_rows>=0){	
	$valores2 = $result->fetch_assoc();

	$paciente_consulta = $valores2['paciente_consulta'];

	if($valores2['paciente'] == "" || $valores2['paciente'] == null){
		$paciente_empresa = "";//LA MUESTRA PERTENECE A UN PACIENTE Y NO HAY EMPRESA
		$paciente = $valores2['empresa'];
	}else{
		$paciente_empresa = $valores2['empresa'];//LA MUESTRA PERTENECE A UNA EMPRESA
		$paciente = $valores2['paciente'];//MOSTRAMOS EL PACIENTE
	}

	$diagonostico_muestra = $valores2['diagnostico_clinico'];
	$material_muestra = $valores2['material_eviando'];
	$datos_relevantes_muestras = $valores2['datos_clinico'];
	$mostrar_datos_clinicos = $valores2['mostrar_datos_clinicos'];
	$remitente = $valores2['remitente'];
	$hospitales_id = $valores2['hospitales_id'];
	$servicio_id = $valores2['servicio_id'];	
	$sitio_muestra = $valores2['sitio_muestra'];
	$tipo_muestra_id = $valores2['tipo_muestra_id'];	
	$categoria_id = $valores2['categoria_id'];	
	$tipo_paciente_id = $valores2['tipo_paciente_id'];	
	$muestra_paciente_id = $valores2['muestra_paciente_id'];		
}

$datos = array(
	0 => $paciente_empresa,
	1 => $paciente,
	2 => $diagonostico_muestra,
	3 => $material_muestra,
	4 => $datos_relevantes_muestras,
	5 => $mostrar_datos_clinicos,
	6 => $remitente,
	7 => $hospitales_id,
	8 => $servicio_id,
	9 => $sitio_muestra,
	10 => $tipo_muestra_id,
	11 => $categoria_id,
	12 => $tipo_paciente_id,
	13 => $muestra_paciente_id		
);	

echo json_encode($datos);