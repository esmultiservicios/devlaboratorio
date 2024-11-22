<?php
session_start();
include "../funtions.php";

header("Content-Type: text/html;charset=utf-8");

require_once '../../dompdf/vendor/autoload.php';

use Dompdf\Dompdf;

//CONEXION A DB
$mysqli = connect_mysqli();

$fecha = $_GET['fecha'];
$empresa_id = $_SESSION['empresa_id'];
$anulada = '';
$activo = 1;

$query = "SELECT e.nombre AS 'empresa', CONCAT(c.nombre, ' ', c.apellido) AS 'usuario'
	FROM facturas AS f
	INNER JOIN secuencia_facturacion AS sf
	ON f.secuencia_facturacion_id = sf.secuencia_facturacion_id
	INNER JOIN empresa AS e
	ON sf.empresa_id = e.empresa_id
    INNER JOIN pagos AS p
    ON f.facturas_id = p.facturas_id
	INNER JOIN colaboradores AS c
	ON p.usuario = c.colaborador_id
	GROUP BY e.empresa_id";
$result = $mysqli->query($query) or die($mysqli->error);

//OBTENER DETALLE DE FACTURA
$query_factura_detalle = "SELECT f.number As 'factura', SUM(f.importe) AS 'importe'
	FROM facturas AS f
	WHERE f.estado = 2 AND f.fecha = '$fecha'
	GROUP BY f.number";
$result_factura_detalle = $mysqli->query($query_factura_detalle) or die($mysqli->error);

//CONSULTAR DATOS DE LA SECUENCIA DE FACTURACION
$query_secuencia = "SELECT secuencia_facturacion_id, prefijo, siguiente AS 'numero', rango_final, fecha_limite, incremento, relleno
   FROM secuencia_facturacion
   WHERE activo = '$activo' AND empresa_id = '$empresa_id'";
$result = $mysqli->query($query_secuencia) or die($mysqli->error);
$consulta2 = $result->fetch_assoc();

$secuencia_facturacion_id = "";
$prefijo = "";
$numero = "";
$relleno = "";
$rango_final = "";
$fecha_limite = "";
$incremento = "";
$no_factura = "";

if($result->num_rows>0){
	$secuencia_facturacion_id = $consulta2['secuencia_facturacion_id'];
	$prefijo = $consulta2['prefijo'];
	$numero = $consulta2['numero'];
	$relleno = $consulta2['relleno'];
	$rango_final = $consulta2['rango_final'];
	$fecha_limite = $consulta2['fecha_limite'];
	$incremento = $consulta2['incremento'];
}

if($result->num_rows>0){
	$consulta_registro = $result->fetch_assoc();

	ob_start();
	include(dirname('__FILE__').'/cierreCaja.php');
	$html = ob_get_clean();

	// instantiate and use the dompdf class
	$dompdf = new Dompdf();

	$dompdf->set_option('isRemoteEnabled', true);

	$dompdf->loadHtml(utf8_decode(utf8_encode($html)));
	// (Optional) Setup the paper size and orientation
	$dompdf->setPaper('B7', 'portrait');
	// Render the HTML as PDF
	$dompdf->render();

	// Output the generated PDF to Browser
	$dompdf->stream('Reporte Cierre de Caja.pdf',array('Attachment'=>0));

	exit;
}
?>
