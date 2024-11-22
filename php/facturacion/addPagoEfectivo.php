<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli(); 

$facturas_id = $_POST['factura_id_efectivo'];
$fecha = date("Y-m-d");
$importe = $_POST['monto_efectivo'];
$efectivo_bill = $_POST['efectivo_bill'] ?? 0;
$cambio = $_POST['cambio_efectivo'];
$empresa_id = $_SESSION['empresa_id'];	
$usuario = $_SESSION['colaborador_id'];			
$tipo_pago_id = 1;//EFECTIVO		
$banco_id = 0;//SIN BANCO
$tipo_pago = 1;//1. CONTADO 2. CRÉDITO
$estado = 2;//FACTURA PAGADA
$estado_pago = 1;//ACTIVO
$fecha_registro = date("Y-m-d H:i:s");
$referencia_pago1 = "";
$referencia_pago2 = "";
$referencia_pago3 = "";
$efectivo = $importe;
$tarjeta = 0;
$tipoLabel = "Pagos";
$tipo_factura = $_POST['tipo_factura'];

//CONSULTAR DATOS DE LA FACTURA
$query_factura = "SELECT  tipo_factura
	FROM facturas
	WHERE facturas_id = '$facturas_id'";
$result_factura = $mysqli->query($query_factura) or die($mysqli->error);
$consultaFactura = $result_factura->fetch_assoc();

$tipo_factura = "";

if($result_factura->num_rows>0){
	$tipo_factura = $consultaFactura['tipo_factura'];
}

if($tipo_factura == 2){
	$tipoLabel = "PagosCredito";
}

if($tipo_factura === "1"){//NO ES NECESARIO EL ABONO
	//VERIFICAMOS QUE NO SE HA INGRESADO EL PAGO, SI NO SE HA REALIZADO EL INGRESO, PROCEDEMOS A ALMACENAR EL PAGO
	$query_factura = "SELECT pagos_id
		FROM pagos
		WHERE facturas_id = '$facturas_id'";
	$result_factura = $mysqli->query($query_factura) or die($mysqli->error);
	
	//SI NO SE HA INGRESADO ALMACENAOS EL PAGO
	if($result_factura->num_rows==0){
		$pagos_id  = correlativo('pagos_id', 'pagos');
		$insert = "INSERT INTO pagos 
			VALUES ('$pagos_id','$facturas_id','$tipo_pago','$fecha','$importe','$efectivo','$cambio','$tarjeta','$usuario','$estado_pago','$empresa_id','$fecha_registro')";
		$query = $mysqli->query($insert);	

		if($query){
			//ACTUALIZAMOS LOS DETALLES DEL PAGO
			$pagos_detalles_id  = correlativo('pagos_detalles_id', 'pagos_detalles');
			$insert = "INSERT INTO pagos_detalles 
				VALUES ('$pagos_detalles_id','$pagos_id','$tipo_pago_id','$banco_id','$importe','$referencia_pago1','$referencia_pago2','$referencia_pago3')";
			$query = $mysqli->query($insert);
		
			//ACTUALIZAMOS EL ESTADO DE LA FACTURA
			$update_factura = "UPDATE facturas
				SET
					estado = '$estado'
				WHERE facturas_id = '$facturas_id'";
			$mysqli->query($update_factura) or die($mysqli->error);	

			//CONSULTAMOS EL NUMERO DE LA MUESTRA
			$query_muestra = "SELECT muestras_id
				FROM facturas
				WHERE facturas_id = '$facturas_id'";
			$result_muestras = $mysqli->query($query_muestra) or die($mysqli->error);

			if($result_muestras->num_rows>0){
				$consulta2Muestras = $result_muestras->fetch_assoc();
				$muestras_id = $consulta2Muestras['muestras_id'];

				//ACTUALIZAMOS EL ESTADO DE LA MUESTRA
				$update_muestra = "UPDATE muestras
					SET
						estado = '1'
					WHERE muestras_id = '$muestras_id'";
				$mysqli->query($update_muestra) or die($mysqli->error);
			}	
			
			//CONSULTAMOS EL SALDO ANTERIOR cobrar_clientes
			$query_saldo_cxc = "SELECT saldo FROM cobrar_clientes WHERE facturas_id = '$facturas_id'";
			$result_saldo_cxc = $mysqli->query($query_saldo_cxc) or die($mysqli->error);
			
			if($result_saldo_cxc->num_rows>0){
				$consulta2Saldo = $result_saldo_cxc->fetch_assoc();
				$saldo_cxc = (float)$consulta2Saldo['saldo'];
				$nuevo_saldo = (float)$saldo_cxc - (float)$importe;
				$estado_cxc = 1;
				
				$tolerancia = 0.0001; // Puedes ajustar esta tolerancia según sea necesario

				// Verificar si el nuevo saldo es cercano a cero con tolerancia para valores de punto flotante
				if (is_float($nuevo_saldo) && abs($nuevo_saldo) < $tolerancia) {
					$estado_cxc = 2;
				}

				// Verificar si el nuevo saldo es cero para valores enteros
				if (is_int($nuevo_saldo) && $nuevo_saldo == 0) {
					$estado_cxc = 2;
				}
				
				//ACTUALIZAR CUENTA POR cobrar_clientes
				$update_ccx = "UPDATE cobrar_clientes 
					SET 
						saldo = '$nuevo_saldo',
						estado = '$estado_cxc'
					WHERE 
						facturas_id = '$facturas_id'";
				$mysqli->query($update_ccx) or die($mysqli->error);					
			}
			
			//VERIFICAMOS SI LA FACTURA ES AL CREDITO DE SERLO CABIAMOS SU NUMERO A LA SECUENCIA DE FACTURACION
			if($tipo_factura == 2){//LA FACTURA ES AL CREDITO
				$documento = "4";//Factura Proforma
				
				$query_secuencia = "SELECT secuencia_facturacion_id, prefijo, siguiente AS 'numero', rango_final, fecha_limite, incremento, relleno
					FROM secuencia_facturacion
					WHERE activo = '$activo' AND empresa_id = '$empresa_id' AND documento_id = '$documento'";

				$result = $mysqli->query($query_secuencia) or die($mysqli->error);
				$consulta2 = $result->fetch_assoc();

				$secuencia_facturacion_id = "";
				$prefijo = "";
				$numero = "0";
				$rango_final = "";
				$fecha_limite = "";
				$incremento = "";
				$no_factura = "";

				if($result->num_rows>0){
					$secuencia_facturacion_id = $consulta2['secuencia_facturacion_id'];	
					$numero = $consulta2['numero'];	
					
					//ACTUALIZAMOS LA FACTURA
					$update_factura = "UPDATE facturas
						SET
							secuencia_facturacion_id  = '$secuencia_facturacion_id',
							number = '$numero'
						WHERE facturas_id = '$facturas_id'";
					$mysqli->query($update_factura) or die($mysqli->error);	
								
				}			
			}
			
			$datos = array(
				0 => "Guardar", 
				1 => "Pago Realizado Correctamente", 
				2 => "info",
				3 => "btn-primary",
				4 => "formEfectivoBill",
				5 => "Registro",
				6 => $tipoLabel ,//FUNCION DE LA TABLA QUE LLAMAREMOS PARA QUE ACTUALICE (DATATABLE BOOSTRAP)
				7 => "modal_pagos", //Modals Para Cierre Automatico
				8 => $facturas_id, //Modals Para Cierre Automatico
				9 => "Guardar",
			);		
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
		$datos = array(
			0 => "Error", 
			1 => "Lo sentimos, no se puede almacenar el pago por favor valide si existe un pago para esta factura", 
			2 => "error",
			3 => "btn-danger",
			4 => "",
			5 => "",			
		);
	}	
}else{//SE HARA ABONOS A LA FACTURA
	//CONSULTAMOS LA SECUENCIA DE FACTURACION
	$query_secuencia = "SELECT secuencia_facturacion_id FROM facturas WHERE facturas_id  = '$facturas_id'";
	$result_secuencia = $mysqli->query($query_secuencia) or die($mysqli->error);

	if($result_secuencia->num_rows>0){
		$consulta2secuencia = $result_secuencia->fetch_assoc();
		$secuencia_facturacion_id = $consulta2secuencia['secuencia_facturacion_id'];
	}

	$abono = $efectivo_bill;
	$cambio = 0;
	
	//CONSULTAMOS EL SALDO ANTERIOR cobrar_clientes
	$query_saldo_cxc = "SELECT saldo FROM cobrar_clientes WHERE facturas_id = '$facturas_id' AND estado = 1";
	$result_saldo_cxc = $mysqli->query($query_saldo_cxc) or die($mysqli->error);
	
	if($result_saldo_cxc->num_rows>0){
		$consulta2Saldo = $result_saldo_cxc->fetch_assoc();
		$saldo_cxc = (float)$consulta2Saldo['saldo'];
		
		//CONSULTAMOS EL TOTAL DEL PAGO REALIZADO
		$query_pagos = "SELECT CAST(COALESCE(SUM(importe), 0) AS UNSIGNED) AS 'importe'
			FROM pagos
			WHERE facturas_id = '$facturas_id'";
		$result_pagos = $mysqli->query($query_pagos) or die($mysqli->error);
		
		if($result_pagos->num_rows>0){
			$consulta2Saldo = $result_pagos->fetch_assoc();
			$abono = $consulta2Saldo['importe'] === "0" ? $efectivo_bill : (float)$consulta2Saldo['importe'];
		}
							
		$nuevo_saldo = (float)$saldo_cxc - (float)$efectivo_bill;
		
		$pagos_id  = correlativo('pagos_id', 'pagos');
		$insert = "INSERT INTO pagos
			VALUES ('$pagos_id','$facturas_id','$tipo_pago','$fecha','$efectivo_bill','$efectivo_bill','$cambio','$tarjeta','$usuario','$estado_pago','$empresa_id','$fecha_registro')";
		$query = $mysqli->query($insert);
		
		if($query){
			//ACTUALIZAMOS LOS DETALLES DEL PAGO
			$pagos_detalles_id  = correlativo('pagos_detalles_id', 'pagos_detalles');
			$insert = "INSERT INTO pagos_detalles
				VALUES ('$pagos_detalles_id','$pagos_id','$tipo_pago_id','$banco_id','$efectivo_bill','$referencia_pago1','$referencia_pago2','$referencia_pago3')";
			$query = $mysqli->query($insert);						
			
			$estado_cxc = 1;
			
			$tolerancia = 0.0001; // Puedes ajustar esta tolerancia según sea necesario

			// Verificar si el nuevo saldo es cercano a cero con tolerancia para valores de punto flotante
			if (is_float($nuevo_saldo) && abs($nuevo_saldo) < $tolerancia) {
				$estado_cxc = 2;
			}

			// Verificar si el nuevo saldo es cero para valores enteros
			if ($nuevo_saldo == 0) {
				$estado_cxc = 2;
			}
			
			//ACTUALIZAR CUENTA POR cobrar_clientes
			$update_ccx = "UPDATE cobrar_clientes 
				SET 
					saldo = '$nuevo_saldo',
					estado = '$estado_cxc'
				WHERE 
					facturas_id = '$facturas_id'";
			$mysqli->query($update_ccx) or die($mysqli->error);	
			
			//SI EL SADO LLEGA A CERO PROCEDEMOS EN AGREGAR LA SECUENCIA DE FACTURACION ELIMINANDO LA DE LA FACTURA PROFORMA
			if($nuevo_saldo == 0){
				$documento = "1";//Factura Electronica
				$query_secuencia = "SELECT secuencia_facturacion_id, prefijo, siguiente AS 'numero', rango_final, fecha_limite, incremento, relleno
					FROM secuencia_facturacion
					WHERE activo = '1' AND empresa_id = '$empresa_id' AND documento_id = '$documento'";

				$result = $mysqli->query($query_secuencia) or die($mysqli->error);
				$consulta2 = $result->fetch_assoc();

				$secuencia_facturacion_id = "";
				$numero = "0";

				if($result->num_rows>0){
					$secuencia_facturacion_id = $consulta2['secuencia_facturacion_id'];	
					$numero = $consulta2['numero'];	
					
					//ACTUALIZAMOS LA FACTURA
					$update_factura = "UPDATE facturas
						SET
							secuencia_facturacion_id  = '$secuencia_facturacion_id',
							number = '$numero'
						WHERE facturas_id = '$facturas_id'";
					$mysqli->query($update_factura) or die($mysqli->error);	

					$tipoLabel = "PagosCXC";	

					//ACTUALIZAMOS LA SECUENCIA DE FACTURACION PARA LA FACTURA Electronica
					$numero_secuencia_facturacion = correlativoSecuenciaFacturacion("siguiente", "secuencia_facturacion", "documento_id = 1 AND activo = 1");
					
					$update = "UPDATE secuencia_facturacion 
					SET 
						siguiente = '$numero_secuencia_facturacion' 
					WHERE secuencia_facturacion_id = '$secuencia_facturacion_id'";
					$mysqli->query($update);					
				}		
			}			

			$datos = array(
				0 => "Guardar", 
				1 => "Pago Realizado Correctamente", 
				2 => "info",
				3 => "btn-primary",
				4 => "formEfectivoBill",
				5 => "Registro",
				6 => $tipoLabel,//FUNCION DE LA TABLA QUE LLAMAREMOS PARA QUE ACTUALICE (DATATABLE BOOSTRAP)
				7 => "modal_pagos", //Modals Para Cierre Automatico
				8 => $facturas_id, //Modals Para Cierre Automatico
				9 => "Guardar",
			);				
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
		$datos = array(
			0 => "Error", 
			1 => "No existe un cobro pendiente para este cliente", 
			2 => "error",
			3 => "btn-danger",
			4 => "",
			5 => "",			
		);			
	}
}

echo json_encode($datos);