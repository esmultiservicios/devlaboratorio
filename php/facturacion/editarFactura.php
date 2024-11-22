<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli(); 
 
$facturas_id = $_POST['facturas_id'];

//CONSULTAR DATOS DEL METODO DE PAGO
$query = "SELECT f.facturas_id AS facturas_id, DATE_FORMAT(f.fecha, '%d/%m/%Y') AS 'fecha', p.pacientes_id AS 'pacientes_id', CONCAT(p.nombre,' ',p.apellido) AS 'empresa', p.identidad AS 'identidad', CONCAT(c.nombre,' ',c.apellido) AS 'profesional', f.colaborador_id AS 'colaborador_id', f.estado AS 'estado', s.nombre AS 'consultorio', f.servicio_id AS 'servicio_id', f.fecha AS 'fecha_factura', f.notas AS 'notas', CONCAT(p1.nombre,' ',p1.apellido) AS 'paciente', m.number AS 'number'
	FROM facturas AS f
	INNER JOIN pacientes AS p
	ON f.pacientes_id = p.pacientes_id
	INNER JOIN servicios AS s
	ON f.servicio_id = s.servicio_id
	INNER JOIN colaboradores AS c
	ON f.colaborador_id = c.colaborador_id
	INNER JOIN muestras AS m
	ON f.muestras_id = m.muestras_id
	LEFT JOIN muestras_hospitales AS mh
	ON f.muestras_id = mh.muestras_id
	LEFT JOIN pacientes AS p1
	ON mh.pacientes_id = p1.pacientes_id
	WHERE facturas_id = '$facturas_id'";
$result = $mysqli->query($query) or die($mysqli->error);
$consulta_registro = $result->fetch_assoc();   
     
$pacientes_id = "";
$empresa = "";
$profesional = "";
$colaborador_id = "";
$servicio_id = "";
$fecha_factura = "";
$notas = "";
$paciente = "";
$number = "";

//OBTENEMOS LOS VALORES DEL REGISTRO
if($result->num_rows>0){
	$pacientes_id = $consulta_registro['pacientes_id'];
	$empresa = $consulta_registro['empresa'];
	$profesional = $consulta_registro['profesional'];
	$colaborador_id = $consulta_registro['colaborador_id'];	
	$servicio_id = $consulta_registro['servicio_id'];
	$fecha_factura = $consulta_registro['fecha_factura'];
	$notas = $consulta_registro['notas'];	
	$paciente = $consulta_registro['paciente'];	
	$number = $consulta_registro['number'];		
}

$datos = array(
	 0 => $pacientes_id, 
	 1 => $empresa, 
	 2 => $fecha_factura,
	 3 => $colaborador_id,  	 
	 4 => $profesional, 	 
	 5 => $servicio_id, 
	 6 => $notas, 	
	 7 => $paciente, 
	 8 => $number,			 
);	
	
echo json_encode($datos);

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>