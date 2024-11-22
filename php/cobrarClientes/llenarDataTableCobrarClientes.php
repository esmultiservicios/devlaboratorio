<?php	
	session_start();   
	include "../funtions.php";

	//CONEXION A DB
	$mysqli = connect_mysqli(); 
	
	$datos = [
		"estado" => $_POST['estado'],
		"clientes_id" => $_POST['clientes_id'],
		"fechai" => $_POST['fechai'],
		"fechaf" => $_POST['fechaf'],		
	];	

	$clientes_id = "";
	$fecha_actual = date("Y-m-d");
	$fecha = "";

	if($datos['fechai'] != $fecha_actual){
		$fecha = "AND cc.fecha BETWEEN '".$datos['fechai']."' AND '".$datos['fechaf']."'";
	}

	if($datos['clientes_id'] != 0){
		$clientes_id = "AND cc.pacientes_id = '".$datos['clientes_id']."'";
	}

	$queryCobrarClientes = "SELECT cc.cobrar_clientes_id AS 'cobrar_clientes_id', f.facturas_id AS 'facturas_id', CONCAT(p.nombre, ' ', p.apellido) AS 'cliente',
	f.fecha AS 'fecha', cc.saldo AS 'saldo', CONCAT(sf.prefijo,'',LPAD(f.number, sf.relleno, 0)) AS 'numero', cc.estado,
	f.importe, CONCAT(co.nombre, ' ', co.apellido) AS 'vendedor', CONCAT('Cliente') As 'Tipo'
		FROM cobrar_clientes AS cc
		INNER JOIN pacientes AS p
		ON cc.pacientes_id = p.pacientes_id
		INNER JOIN facturas AS f
		ON cc.facturas_id = f.facturas_id
		INNER JOIN secuencia_facturacion AS sf
		ON f.secuencia_facturacion_id = sf.secuencia_facturacion_id
		INNER JOIN colaboradores AS co
		ON f.colaborador_id = co.colaborador_id
		WHERE cc.estado = '".$datos['estado']."'
		$fecha
		$clientes_id
		UNION
		SELECT cc.cobrar_clientes_id AS 'cobrar_clientes_id', f.facturas_grupal_id AS 'facturas_id', CONCAT(p.nombre, ' ', p.apellido) AS 'cliente',
		f.fecha AS 'fecha', cc.saldo AS 'saldo', CONCAT(sf.prefijo,'',LPAD(f.number, sf.relleno, 0)) AS 'numero', cc.estado,
		f.importe, CONCAT(co.nombre, ' ', co.apellido) AS 'vendedor', CONCAT('Grupo') As 'Tipo'
		FROM cobrar_clientes_grupales AS cc
		INNER JOIN pacientes AS p
		ON cc.pacientes_id = p.pacientes_id
		INNER JOIN facturas_grupal AS f
		ON cc.facturas_id = f.facturas_grupal_id
		INNER JOIN secuencia_facturacion AS sf
		ON f.secuencia_facturacion_id = sf.secuencia_facturacion_id
		INNER JOIN colaboradores AS co
		ON f.colaborador_id = co.colaborador_id
		WHERE cc.estado = '".$datos['estado']."'
		$fecha
		$clientes_id	
	";
	$resultCobrarClientes = $mysqli->query($queryCobrarClientes);
	
	$arreglo = array();
	$data = array();
	$estadoColor = 'bg-warning';
	$credito = 0.00;
	$abono = 0.00;
	$saldo = 0.00;
	$totalCredito = 0;
	$totalAbono = 0;
	$totalPendiente = 0;

	while($row = $resultCobrarClientes->fetch_assoc()){
		$facturas_id = $row['facturas_id'];
		$queryAbonos = "";

		if($row['Tipo'] == 'Cliente'){
			$queryAbonos = "SELECT SUM(importe) As 'total'
			FROM pagos
			WHERE facturas_id = '$facturas_id'";		
		}else{
			$queryAbonos = "SELECT SUM(importe) As 'total'
			FROM pagos_grupal
			WHERE facturas_grupal_id = '$facturas_id'";
		}

		$resultAbonos = $mysqli->query($queryAbonos);
		$rowAbonos = $resultAbonos->fetch_assoc();

		if ($rowAbonos['total'] != null || $rowAbonos['total'] != ""){
			$abono = $rowAbonos['total'];
		}else{
			$abono = 0.00;
		}

		$credito = $row['importe'];
		$saldo = $row['importe'] - $abono;

		$totalCredito += $credito;
		$totalAbono += $abono;
		$totalPendiente += $saldo;

		if($row['estado'] == 2){
			$estadoColor = 'bg-c-green';
		}else{
			$estadoColor = 'bg-warning';
		}

		$data[] = array( 
			"cobrar_clientes_id"=>$row['cobrar_clientes_id'],
			"facturas_id"=>$row['facturas_id'],
			"fecha"=>$row['fecha'],
			"cliente"=> $row['cliente'],
			"numero"=>$row['numero'],
			"credito"=> $credito,
			"abono"=>$abono,						
			"saldo"=>$saldo,
			"color"=> $estadoColor,
			"estado"=>$row['estado'],
			"total_credito"=> number_format($totalCredito,2),
			"total_abono"=>number_format($totalAbono,2),
			"total_pendiente"=> number_format($totalPendiente,2),
			"vendedor"=>$row['vendedor'],
			"tipo"=>$row['Tipo'],
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