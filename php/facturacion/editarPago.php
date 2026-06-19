<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli(); 
$mysqli->set_charset("utf8");
 
$facturas_id = isset($_POST['facturas_id']) ? (int)$_POST['facturas_id'] : 0;

$paciente = "";
$fecha_factura = "";
$importe = 0;
$saldo = 0;

if ($facturas_id <= 0) {
	$datos = array(
		0 => $paciente, 
		1 => $fecha_factura, 
		2 => $importe,	
		3 => $saldo,
	);	
	
	echo json_encode($datos);
	$mysqli->close();
	exit;
}

// =========================================================
// 1) INTENTAR COMO FACTURA INDIVIDUAL
// =========================================================
$query = "SELECT 
		f.facturas_id AS facturas_id, 
		DATE_FORMAT(f.fecha, '%d/%m/%Y') AS fecha, 
		p.pacientes_id AS pacientes_id, 
		CONCAT(p.nombre,' ',p.apellido) AS paciente, 
		p.identidad AS identidad, 
		CONCAT(c.nombre,' ',c.apellido) AS profesional, 
		f.colaborador_id AS colaborador_id, 
		f.estado AS estado, 
		s.nombre AS consultorio, 
		f.servicio_id AS servicio_id, 
		f.fecha AS fecha_factura, 
		f.notas AS notas, 
		f.importe AS importe_factura,
		IFNULL(cc.saldo, 0) AS saldo
	FROM facturas AS f
	INNER JOIN pacientes AS p
		ON f.pacientes_id = p.pacientes_id
	INNER JOIN servicios AS s
		ON f.servicio_id = s.servicio_id
	INNER JOIN colaboradores AS c
		ON f.colaborador_id = c.colaborador_id
	LEFT JOIN cobrar_clientes AS cc
		ON f.facturas_id = cc.facturas_id
	WHERE f.facturas_id = '$facturas_id'
	LIMIT 1";

$result = $mysqli->query($query) or die($mysqli->error);
$consulta_registro = $result->fetch_assoc();   

if($result && $result->num_rows > 0){
	$paciente = $consulta_registro['paciente'];
	$fecha_factura = $consulta_registro['fecha_factura'];	
	$saldo = (float)$consulta_registro['saldo'];

	$query_factura_detalles = "SELECT 
			ROUND(IFNULL(SUM(fd.precio + fd.isv_valor - fd.descuento), 0), 2) AS total_detalle
		FROM facturas_detalle AS fd
		WHERE fd.facturas_id = '$facturas_id'";

	$result_factura = $mysqli->query($query_factura_detalles) or die($mysqli->error);
	$registro2 = $result_factura->fetch_assoc();

	if($registro2){
		$importe = (float)$registro2['total_detalle'];
	}

	if($importe <= 0){
		$importe = (float)$consulta_registro['importe_factura'];
	}

	if($saldo <= 0){
		$saldo = $importe;
	}

	$datos = array(
		0 => $paciente, 
		1 => $fecha_factura, 
		2 => $importe,	
		3 => $saldo,
	);	
	
	echo json_encode($datos);

	if($result_factura){
		$result_factura->free();
	}

	$result->free();
	$mysqli->close();
	exit;
}

if($result){
	$result->free();
}

// =========================================================
// 2) SI NO EXISTE COMO FACTURA INDIVIDUAL, INTENTAR GRUPAL
// =========================================================
$query_grupal = "SELECT 
		fg.facturas_grupal_id AS facturas_grupal_id,
		DATE_FORMAT(fg.fecha, '%d/%m/%Y') AS fecha,
		p.pacientes_id AS pacientes_id,
		CONCAT(p.nombre,' ',p.apellido) AS paciente,
		p.identidad AS identidad,
		CONCAT(c.nombre,' ',c.apellido) AS profesional,
		fg.colaborador_id AS colaborador_id,
		fg.estado AS estado,
		s.nombre AS consultorio,
		fg.servicio_id AS servicio_id,
		fg.fecha AS fecha_factura,
		fg.notas AS notas,
		fg.importe AS importe_factura,
		IFNULL(ccg.saldo, 0) AS saldo
	FROM facturas_grupal AS fg
	INNER JOIN pacientes AS p
		ON fg.pacientes_id = p.pacientes_id
	INNER JOIN servicios AS s
		ON fg.servicio_id = s.servicio_id
	INNER JOIN colaboradores AS c
		ON fg.colaborador_id = c.colaborador_id
	LEFT JOIN cobrar_clientes_grupales AS ccg
		ON fg.facturas_grupal_id = ccg.facturas_id
	WHERE fg.facturas_grupal_id = '$facturas_id'
	LIMIT 1";

$result_grupal = $mysqli->query($query_grupal) or die($mysqli->error);
$consulta_grupal = $result_grupal->fetch_assoc();

if($result_grupal && $result_grupal->num_rows > 0){
	$paciente = $consulta_grupal['paciente'];
	$fecha_factura = $consulta_grupal['fecha_factura'];
	$saldo = (float)$consulta_grupal['saldo'];

	$query_grupal_detalles = "SELECT 
			ROUND(IFNULL(SUM(fgd.importe + fgd.isv_valor - fgd.descuento), 0), 2) AS total_detalle
		FROM facturas_grupal_detalle AS fgd
		WHERE fgd.facturas_grupal_id = '$facturas_id'";

	$result_grupal_detalle = $mysqli->query($query_grupal_detalles) or die($mysqli->error);
	$registro_grupal_detalle = $result_grupal_detalle->fetch_assoc();

	if($registro_grupal_detalle){
		$importe = (float)$registro_grupal_detalle['total_detalle'];
	}

	if($importe <= 0){
		$importe = (float)$consulta_grupal['importe_factura'];
	}

	if($saldo <= 0){
		$saldo = $importe;
	}

	if($result_grupal_detalle){
		$result_grupal_detalle->free();
	}
}

$datos = array(
	0 => $paciente, 
	1 => $fecha_factura, 
	2 => $importe,	
	3 => $saldo,
);	
	
echo json_encode($datos);

if($result_grupal){
	$result_grupal->free();
}

$mysqli->close();