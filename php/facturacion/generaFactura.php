<?php
session_start();
include '../funtions.php';

header('Content-Type: text/html;charset=utf-8');

require_once '../../dompdf/vendor/autoload.php';

use Dompdf\Dompdf;

// CONEXION A DB
$mysqli = connect_mysqli();

date_default_timezone_set('America/Tegucigalpa');

$noFactura = $_GET['facturas_id'];
$anulada = '';

$query = "SELECT CONCAT(p.nombre, ' ', p.apellido) AS 'paciente', p.identidad AS 'identidad', p.expediente AS 'expediente', p.telefono1 AS 'tel_paciente', p.localidad AS 'localidad_paciente', e.nombre AS 'empresa', e.ubicacion AS 'direccion_empresa', e.telefono AS 'empresa_telefono', e.correo AS 'empresa_correo', CONCAT(c.nombre, ' ', c.apellido) AS 'profesional', s.nombre AS 'servicio', sf.prefijo AS 'prefijo', sf.siguiente AS 'numero', sf.relleno AS 'relleno', DATE_FORMAT(f.fecha, '%d/%m/%Y') AS 'fecha', time(f.fecha_registro) AS 'hora', sf.cai AS 'cai', e.rtn AS 'rtn', sf.fecha_activacion AS 'fecha_activacion', sf.fecha_limite AS 'fecha_limite', pc.nombre AS 'puesto', f.estado AS 'estado', sf.rango_inicial AS 'rango_inicial', sf.rango_final AS 'rango_final', am.edad AS 'edad', f.number AS 'numero_factura', f.notas AS 'notas', e.otra_informacion As 'otra_informacion', e.eslogan AS 'eslogan', e.celular As 'celular', (CASE WHEN f.tipo_factura = 1 THEN 'Contado' ELSE 'Crédito' END) AS 'tipo_documento', m.referencia AS 'referencia', d.nombre AS 'documento'
	FROM facturas AS f
	INNER JOIN pacientes AS p
	ON f.pacientes_id = p.pacientes_id
	INNER JOIN secuencia_facturacion AS sf
	ON f.secuencia_facturacion_id = sf.secuencia_facturacion_id
	INNER JOIN empresa AS e
	ON sf.empresa_id = e.empresa_id
	INNER JOIN colaboradores AS c
	ON f.colaborador_id = c.colaborador_id
	INNER JOIN servicios AS s
	ON f.servicio_id = s.servicio_id
 \tINNER JOIN puesto_colaboradores AS pc
  \tON c.puesto_id = pc.puesto_id
  \tLEFT JOIN atenciones_medicas AS am
  \tON f.pacientes_id = am.pacientes_id
	LEFT JOIN muestras AS m
	ON f.muestras_id = m.muestras_id
	LEFT JOIN documento AS d
	ON sf.documento_id = d.documento_id
  \tWHERE f.facturas_id = '$noFactura'";
$result = $mysqli->query($query) or die($mysqli->error);

// OBTENER DETALLE DE FACTURA
$query_factura_detalle = "SELECT p.nombre AS 'producto', 
		SUM(fd.cantidad) AS 'cantidad', 
		fd.precio AS 'precio', 
		SUM(fd.descuento) AS 'descuento', 
		fd.productos_id  AS 'productos_id', 
		SUM(fd.isv_valor) AS 'isv_valor', 
		CONCAT(p1.nombre, ' ', p1.apellido) AS 'paciente'
	FROM facturas_detalle AS fd
	INNER JOIN productos AS p ON fd.productos_id = p.productos_id
	INNER JOIN facturas AS f ON fd.facturas_id = f.facturas_id
	LEFT JOIN muestras_hospitales AS mh ON f.muestras_id = mh.muestras_id
	LEFT JOIN pacientes AS p1 ON mh.pacientes_id = p1.pacientes_id
	WHERE fd.facturas_id = '$noFactura'
	GROUP BY fd.productos_id";
$result_factura_detalle = $mysqli->query($query_factura_detalle) or die($mysqli->error);

$abono = 0;
$saldo = 0;

// CONSULTAMOS EL TOTAL DEL PAGO REALIZADO
$query_pagos = "SELECT CAST(COALESCE(SUM(importe), 0) AS UNSIGNED) AS 'importe'
	FROM pagos
	WHERE facturas_id = '$noFactura'";
$result_pagos = $mysqli->query($query_pagos) or die($mysqli->error);

if ($result_pagos->num_rows > 0) {
	$consulta2Saldo = $result_pagos->fetch_assoc();
	$abono = $consulta2Saldo['importe'];
}

if ($result->num_rows > 0) {
	$consulta_registro = $result->fetch_assoc();
	$no_factura = str_pad($consulta_registro['numero_factura'], $consulta_registro['relleno'], '0', STR_PAD_LEFT);

	if ($consulta_registro['estado'] == 3) {
		$anulada = '<img class="anulada" src="../../img/anulado.png" alt="Anulada">';
	}

	ob_start();
	include (dirname('__FILE__') . '/factura.php');
	$html = ob_get_clean();

	// instantiate and use the dompdf class
	$dompdf = new Dompdf();

	$dompdf->set_option('isRemoteEnabled', true);

	$html = mb_convert_encoding($html, 'UTF-8', 'auto');  // Converts $html to UTF-8 encoding
	$dompdf->loadHtml($html);  // Now load the UTF-8 encoded HTML

	// (Optional) Setup the paper size and orientation
	$dompdf->setPaper('letter', 'portrait');
	// Render the HTML as PDF
	$dompdf->render();

	file_put_contents(dirname('__FILE__') . '/Facturas/factura_' . $no_factura . '.pdf', $dompdf->output());

	// Output the generated PDF to Browser
	$dompdf->stream('factura_' . $no_factura . '.pdf', array('Attachment' => 0));

	exit;
}
