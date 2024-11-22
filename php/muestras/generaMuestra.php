<?php
session_start();   
include "../funtions.php";

header("Content-Type: text/html;charset=utf-8");

include_once "../../dompdf/autoload.inc.php";
require_once '../../pdf/vendor/autoload.php';

use Dompdf\Dompdf;
	
//CONEXION A DB
$mysqli = connect_mysqli();

date_default_timezone_set('America/Tegucigalpa');

$noMuestra = $_GET['muestras_id'];
$anulada = '';

$query = "SELECT e.nombre AS 'empresa', e.ubicacion AS 'direccion_empresa', e.telefono As 'empresa_telefono', e.correo AS 'empresa_correo', m.estado AS 'estado', CONCAT(p.nombre, ' ', p.apellido) As 'paciente', DATE_FORMAT(m.fecha, '%d/%m/%Y') AS 'fecha', 
(CASE WHEN p.genero = 'H' THEN 'Hombre' ELSE 'Mujer' END) AS 'genero', p.telefono1 AS 'telefono', p.email AS 'correo', m.diagnostico_clinico As 'diagnostico_clinico', m.material_eviando As 'material_eviando', m.datos_clinico As 'datos_clinico', m.mostrar_datos_clinicos AS 'mostrar_datos_clinicos', p.fecha_nacimiento AS 'fecha_nacimiento', m.number AS 'number', e.otra_informacion As 'otra_informacion', e.eslogan AS 'eslogan', e.celular As 'celular', CONCAT(c.nombre, ' ', c.apellido) As 'medico_remitente', h.nombre AS 'hospital', m.pacientes_id AS 'pacientes_id', m.servicio_id AS 'servicio_id', m.colaborador_id AS 'colaborador_id', p.edad AS 'edad'
	FROM muestras As m
	INNER JOIN secuencias_muestas As s
	ON m.secuencias_id = s.secuencias_id
	INNER JOIN empresa AS e
	ON s.empresa_id = e.empresa_id
    INNER JOIN pacientes As p
    ON m.pacientes_id = p.pacientes_id
	INNER JOIN colaboradores AS c
    ON m.colaborador_id = c.colaborador_id
    INNER JOIN hospitales AS h
    ON m.hospitales_id = h.hospitales_id
	WHERE m.muestras_id = '$noMuestra'";
$result = $mysqli->query($query) or die($mysqli->error);

if($result->num_rows>0){
	$consulta_registro = $result->fetch_assoc();
	
	$pacientes_id = $consulta_registro['pacientes_id'];
	$colaborador_id = $consulta_registro['colaborador_id'];
	$servicio_id = $consulta_registro['servicio_id'];
	
	$query_muestra_anterior = "SELECT m.number As 'numero_anterior'
		FROM muestras AS m
		WHERE m.pacientes_id = '$pacientes_id' AND m.colaborador_id = '$colaborador_id' AND m.servicio_id = '$servicio_id'
		ORDER BY m.pacientes_id DESC LIMIT 1";
	$result_muestra_anterior = $mysqli->query($query_muestra_anterior) or die($mysqli->error);
	
	$numero_muestra_anterior = "";
	
	if($result_muestra_anterior->num_rows>0){
		$consulta_muestra_anterior = $result_muestra_anterior->fetch_assoc();
		$numero_muestra_anterior = $consulta_muestra_anterior['numero_anterior'];
	}	
	
	//CONSULTA AÑO, MES y DIA DEL PACIENTE
	$anos = $consulta_registro['edad'];
	/*********************************************************************************/
	
	//$noMuestra = str_pad($consulta_registro['numero_factura'], $consulta_registro['relleno'], "0", STR_PAD_LEFT);
	$año_actual = date("Y");
	$mes_actual = date("m");
	$dia_actual = date("d");
	$noMuestras = $consulta_registro['number'];
	if($consulta_registro['estado'] == 3){
		$anulada = '<img class="anulada" src="../../img/anulado.png" alt="Anulada">';
	}

	ob_start();
	include(dirname('__FILE__').'/muestra.php');
	$html = ob_get_clean();

	// instantiate and use the dompdf class
	$dompdf = new Dompdf();
	
	$dompdf->set_option('isRemoteEnabled', true);

	$dompdf->loadHtml(utf8_decode(utf8_encode($html)));
	// (Optional) Setup the paper size and orientation
	$dompdf->setPaper('letter', 'portrait');
	// Render the HTML as PDF
	$dompdf->render();
	
	file_put_contents(dirname('__FILE__').'/Muestras/muestra_'.$noMuestras.'.pdf', $dompdf->output());
	
	// Output the generated PDF to Browser
	$dompdf->stream('muestra_'.$noMuestras.'.pdf',array('Attachment'=>0));
	
	exit;	
}
?>