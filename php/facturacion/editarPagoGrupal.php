<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli(); 
 
$facturas_grupal_id = $_POST['facturas_grupal_id'];

//CONSULTAR DATOS DEL METODO DE PAGO
$query = "SELECT fg.facturas_grupal_id AS facturas_id, DATE_FORMAT(fg.fecha, '%d/%m/%Y') AS 'fecha', p.pacientes_id AS 'pacientes_id', CONCAT(p.nombre,' ',p.apellido) AS 'paciente', p.identidad AS 'identidad', CONCAT(c.nombre,' ',c.apellido) AS 'profesional', fg.colaborador_id AS 'colaborador_id', fg.estado AS 'estado', s.nombre AS 'consultorio', fg.servicio_id AS 'servicio_id', fg.fecha AS 'fecha_factura', fg.notas AS 'notas', cc.saldo
	FROM facturas_grupal AS fg
	INNER JOIN pacientes AS p
	ON fg.pacientes_id = p.pacientes_id
	INNER JOIN servicios AS s
	ON fg.servicio_id = s.servicio_id
	INNER JOIN colaboradores AS c
	ON fg.colaborador_id = c.colaborador_id
	INNER JOIN cobrar_clientes_grupales AS cc
	ON fg.facturas_grupal_id = cc.facturas_id
	WHERE fg.facturas_grupal_id = '$facturas_grupal_id'";
$result = $mysqli->query($query) or die($mysqli->error);
$consulta_registro = $result->fetch_assoc();   
     
$paciente = "";
$fecha_factura = "";
$importe = 0;
$saldo = 0;

//OBTENEMOS LOS VALORES DEL REGISTRO
if($result->num_rows>0){
	$paciente = $consulta_registro['paciente'];
	$fecha_factura = $consulta_registro['fecha_factura'];	
	$saldo = $consulta_registro['saldo'];
}

$query_factura_detalles = "SELECT fgd.importe AS 'precio', fgd.isv_valor AS 'isv_valor', fgd.descuento AS 'descuento'
	FROM facturas_grupal_detalle AS fgd
	INNER JOIN facturas_grupal As fg
	ON fgd.facturas_grupal_id = fg.facturas_grupal_id
	WHERE fgd.facturas_grupal_id = '$facturas_grupal_id'";
$result_factura = $mysqli->query($query_factura_detalles);

while($registro2 = $result_factura->fetch_assoc()){
	$precio = $registro2['precio'];
	$descuento = $registro2['descuento'];
	$isv_valor = $registro2['isv_valor'];
	$importe += ($precio - $descuento) + $isv_valor;
}	

$datos = array(
	 0 => $paciente, 
	 1 => $fecha_factura, 
	 2 => $importe, 
	 3 => $saldo
);	
	
echo json_encode($datos);

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN