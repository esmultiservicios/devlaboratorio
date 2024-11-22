<?php	
	session_start();   
	include "../funtions.php";

	//CONEXION A DB
	$mysqli = connect_mysqli(); 
	
	$factura_id = $_POST['factura_id'];
	$tipo = $_POST['tipo'];

	$query = "";

	if($tipo == "Cliente"){
		$query = "SELECT
			pagos.facturas_id,
			pagos.fecha,
			pagos.importe as abono,
			pagos_detalles.descripcion1,
			facturas.importe,
			pacientes.nombre as cliente,
			tipo_pago.nombre as tipo_pago,
			sf.prefijo AS 'prefijo',
			sf.siguiente AS 'numero',
			sf.relleno AS 'relleno',
			sf.prefijo AS 'prefijo',
			CONCAT(colaboradores.nombre, ' ', colaboradores.apellido) AS 'usuario'
		FROM pagos
		LEFT JOIN pagos_detalles ON pagos.pagos_id = pagos_detalles.pagos_id
		INNER JOIN facturas ON facturas.facturas_id = pagos.facturas_id
		INNER JOIN pacientes ON facturas.pacientes_id = pacientes.pacientes_id
		INNER JOIN tipo_pago ON pagos_detalles.tipo_pago_id = tipo_pago.tipo_pago_id
		INNER JOIN secuencia_facturacion AS sf ON facturas.secuencia_facturacion_id = sf.secuencia_facturacion_id
		INNER JOIN colaboradores ON pagos.usuario = colaboradores.colaborador_id
		WHERE pagos.facturas_id = '$factura_id'";
	}else{
		$query = "SELECT 
			pagos_grupal.facturas_grupal_id AS 'facturas_id',
			pagos_grupal.fecha,
			pagos_grupal.importe as abono,
			pagos_grupal_detalles.descripcion1,
			facturas_grupal.importe,
			pacientes.nombre as cliente,
			tipo_pago.nombre as tipo_pago,
			sf.prefijo AS 'prefijo',
			sf.siguiente AS 'numero',
			sf.relleno AS 'relleno',
			sf.prefijo AS 'prefijo',
			CONCAT(colaboradores.nombre, ' ', colaboradores.apellido) AS 'usuario'
		FROM pagos_grupal
		INNER JOIN pagos_grupal_detalles ON pagos_grupal_detalles.pagos_id = pagos_grupal.pagos_grupal_id
		INNER JOIN facturas_grupal ON facturas_grupal.facturas_grupal_id = pagos_grupal.facturas_grupal_id	
		INNER JOIN pacientes ON facturas_grupal.pacientes_id = pacientes.pacientes_id
		INNER JOIN tipo_pago ON pagos_grupal_detalles.tipo_pago_id = tipo_pago.tipo_pago_id
		INNER JOIN secuencia_facturacion AS sf ON facturas_grupal.secuencia_facturacion_id = sf.secuencia_facturacion_id
		INNER JOIN colaboradores ON pagos_grupal.usuario = colaboradores.colaborador_id
		WHERE pagos_grupal.facturas_grupal_id = '$factura_id'";		
	}

	$result = $mysqli->query($query);

    $total_abono = 0;
	$arreglo = array();
	$data = array();
		
	while($row = $result->fetch_assoc()){		
        $total_abono += $row['abono'];

		$no_factura = $row['prefijo'].str_pad( $row['numero'], $row['relleno'], "0", STR_PAD_LEFT);		

		$data[] = array( 
			"facturas_id"=>$row['facturas_id'],
			"fecha"=>$row['fecha'],
			"abono"=>number_format($row['abono'],2),						
			"cliente"=> $row['cliente'],
			"descripcion"=>$row['descripcion1'],
			"tipo_pago"=>$row['tipo_pago'],
			"importe"=>number_format($row['importe'],2),
            "total"=> number_format($total_abono ,2),
			"no_factura"=>$no_factura,
			"usuario"=>$row['usuario'],
		);		
	}
	
	$arreglo = array(
		"echo" => 1,
		"totalrecords" => count($data),
		"totaldisplayrecords" => count($data),
		"data" => $data
	);

	echo json_encode($arreglo);
?>	