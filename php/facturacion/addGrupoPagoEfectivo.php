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
$estado_atencion = 1;
$estado_pago = 1;//ACTIVO
$fecha_registro = date("Y-m-d H:i:s");
$referencia_pago1 = "";
$referencia_pago2 = "";
$referencia_pago3 = "";
$efectivo = $importe;
$tarjeta = 0;
$tipoLabel = "PagosGrupal";

//CONSULTAR DATOS DE LA FACTURA
$query_factura = "SELECT servicio_id, colaborador_id, fecha, pacientes_id, tipo_factura, number FROM facturas_grupal WHERE facturas_grupal_id = ?";
$stmt = $mysqli->prepare($query_factura);
$stmt->bind_param("i", $facturas_id);
$stmt->execute();
$result_factura = $stmt->get_result();
$consultaFactura = $result_factura->fetch_assoc();

$servicio_id = $colaborador_id = $fecha_factura = $pacientes_id = $tipo_factura = "";

if($result_factura->num_rows>0){
    $servicio_id = $consultaFactura['servicio_id'];
    $colaborador_id = $consultaFactura['colaborador_id'];
    $fecha_factura = $consultaFactura['fecha'];
    $pacientes_id = $consultaFactura['pacientes_id'];
    $tipo_factura = $consultaFactura['tipo_factura'];
    $numero = $consultaFactura['number'];
}

if($tipo_factura == 2){
    $tipoLabel = "PagosGrupalCredito";
}

$datos = array();

if($tipo_factura === "1"){
    //VALIDAMOS QUE EL PAGO PARA LA FACTURA NO EXISTA
    $queryPagos = "SELECT pagos_grupal_id FROM pagos_grupal WHERE facturas_grupal_id = ?";
    $stmt = $mysqli->prepare($queryPagos);
    $stmt->bind_param("i", $facturas_id);
    $stmt->execute();
    $result_ConsultaPagos = $stmt->get_result();

    if($result_ConsultaPagos->num_rows==0){
        $pagos_grupal_id = correlativo("pagos_grupal_id","pagos_grupal");
        
        $insert = "INSERT INTO pagos_grupal VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($insert);
        $stmt->bind_param("iiisdddiiiss", $pagos_grupal_id, $facturas_id, $tipo_pago, $fecha_factura, $importe, $efectivo, $cambio, $tarjeta, $usuario, $estado_pago, $empresa_id, $fecha_registro);
        $query = $stmt->execute();

        if($query){
            //ACTUALIZAMOS EL DETALLE DEL PAGO
            $pagos_grupal_detalles_id = correlativo('pagos_grupal_detalles_id', 'pagos_grupal_detalles');
            $insert = "INSERT INTO pagos_grupal_detalles VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($insert);
            $stmt->bind_param("iiiissss", $pagos_grupal_detalles_id, $pagos_grupal_id, $tipo_pago_id, $banco_id, $importe, $referencia_pago1, $referencia_pago2, $referencia_pago3);
            $stmt->execute();

            //CONSULTAMOS EL NUMERO DE ATENCION
            $query_atencion = "SELECT atencion_id FROM atenciones_medicas WHERE pacientes_id = ? AND servicio_id = ? AND colaborador_id = ? AND fecha = ?";
            $stmt = $mysqli->prepare($query_atencion);
            $stmt->bind_param("iiis", $pacientes_id, $servicio_id, $colaborador_id, $fecha_factura);
            $stmt->execute();
            $result_atencion = $stmt->get_result();
            $consultaDatosAtencion = $result_atencion->fetch_assoc();

            $atencion_id = "";
            if($result_atencion->num_rows>0){
                $atencion_id = $consultaDatosAtencion['atencion_id'];
            }            

            //ACTUALIZAMOS EL ESTADO DE LA FACTURA GRUPAL
            $update_factura = "UPDATE facturas_grupal SET estado = ? WHERE facturas_grupal_id = ?";
            $stmt = $mysqli->prepare($update_factura);
            $stmt->bind_param("ii", $estado, $facturas_id);
            $stmt->execute();

            //CONSULTAMOS LOS NUMEROS DE FACTURAS QUE SE ATENDIERON
            $query_facturas = "SELECT facturas_id FROM facturas_grupal_detalle WHERE facturas_grupal_id = ?";
            $stmt = $mysqli->prepare($query_facturas);
            $stmt->bind_param("i", $facturas_id);
            $stmt->execute();
            $result_facturas = $stmt->get_result();

            while($registroFacturas = $result_facturas->fetch_assoc()){
                $facturaConsulta = $registroFacturas['facturas_id'];

                //ACTUALIZAMOS EL ESTADO DE LA FACTURA
                $update_factura = "UPDATE facturas SET estado = ? WHERE facturas_id = ?";
                $stmt = $mysqli->prepare($update_factura);
                $stmt->bind_param("ii", $estado, $facturaConsulta);
                $stmt->execute();
            }

            //ACTUALIZAMOS EL ESTADO DE LA ATENCION
            if(!empty($atencion_id)){
                $update_atencion = "UPDATE atenciones_medicas SET estado = ? WHERE atencion_id = ?";
                $stmt = $mysqli->prepare($update_atencion);
                $stmt->bind_param("ii", $estado_atencion, $atencion_id);
                $stmt->execute();
            }
            
            //CONSULTAMOS EL SALDO ANTERIOR cobrar_clientes
            $query_saldo_cxc = "SELECT saldo FROM cobrar_clientes_grupales WHERE facturas_id = ?";
            $stmt = $mysqli->prepare($query_saldo_cxc);
            $stmt->bind_param("i", $facturas_id);
            $stmt->execute();
            $result_saldo_cxc = $stmt->get_result();
            
            if($result_saldo_cxc->num_rows>0){
                $consulta2Saldo = $result_saldo_cxc->fetch_assoc();
                $saldo_cxc = (float)$consulta2Saldo['saldo'];
                $nuevo_saldo = (float)$saldo_cxc - (float)$importe;
                $estado_cxc = 1;
                
                $tolerancia = 0.0001;
                if (abs($nuevo_saldo) < $tolerancia) {
                    $estado_cxc = 2;
                }
                
                //ACTUALIZAR CUENTA POR cobrar_clientes
                $update_ccx = "UPDATE cobrar_clientes_grupales SET saldo = ?, estado = ? WHERE facturas_id = ?";
                $stmt = $mysqli->prepare($update_ccx);
                $stmt->bind_param("dii", $nuevo_saldo, $estado_cxc, $facturas_id);
                $stmt->execute();                  
            }            

            $datos = array(
                0 => "Almacenado",
                1 => "Pago Realizado Correctamente, ¿Desea enviar esta factura por correo electrónico?",
                2 => "info",
                3 => "btn-primary",
                4 => "formEfectivoBillGrupal",
                5 => "Registro",
                6 => $tipoLabel,
                7 => "modal_grupo_pagos",
                8 => $facturas_id,
                9 => $numero,
                10 => "Guardar"
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
    }
}else{
    $abono = $efectivo_bill;
    $cambio = 0;

    //CONSULTAMOS EL SALDO ANTERIOR cobrar_clientes
    $query_saldo_cxc = "SELECT saldo FROM cobrar_clientes_grupales WHERE facturas_id = ? AND estado = 1";
    $stmt = $mysqli->prepare($query_saldo_cxc);
    $stmt->bind_param("i", $facturas_id);
    $stmt->execute();
    $result_saldo_cxc = $stmt->get_result();

    if($result_saldo_cxc->num_rows>0){
        $consulta2Saldo = $result_saldo_cxc->fetch_assoc();
        $saldo_cxc = (float)$consulta2Saldo['saldo'];
        
        //CONSULTAMOS EL TOTAL DEL PAGO REALIZADO
        $query_pagos = "SELECT CAST(COALESCE(SUM(importe), 0) AS UNSIGNED) AS 'importe' FROM pagos_grupal WHERE facturas_grupal_id = ?";
        $stmt = $mysqli->prepare($query_pagos);
        $stmt->bind_param("i", $facturas_id);
        $stmt->execute();
        $result_pagos = $stmt->get_result();
        
        if($result_pagos->num_rows>0){
            $consulta2Saldo = $result_pagos->fetch_assoc();
            $abono = $consulta2Saldo['importe'] === "0" ? $efectivo_bill : (float)$consulta2Saldo['importe'];
        }
                            
        $nuevo_saldo = (float)$saldo_cxc - (float)$efectivo_bill;    

        $pagos_grupal_id = correlativo("pagos_grupal_id","pagos_grupal");
        $insert = "INSERT INTO pagos_grupal VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($insert);
        $stmt->bind_param("iiisdddiiiss", $pagos_grupal_id, $facturas_id, $tipo_pago, $fecha_factura, $efectivo_bill, $efectivo_bill, $cambio, $tarjeta, $usuario, $estado_pago, $empresa_id, $fecha_registro);
        $query = $stmt->execute();
        
        if($query){
            //ACTUALIZAMOS EL DETALLE DEL PAGO
            $pagos_grupal_detalles_id = correlativo('pagos_grupal_detalles_id', 'pagos_grupal_detalles');
            $insert = "INSERT INTO pagos_grupal_detalles VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($insert);
            $stmt->bind_param("iiiissss", $pagos_grupal_detalles_id, $pagos_grupal_id, $tipo_pago_id, $banco_id, $efectivo_bill, $referencia_pago1, $referencia_pago2, $referencia_pago3);
            $stmt->execute();    

            $estado_cxc = 1;
            
            $tolerancia = 0.0001;
            if (is_float($nuevo_saldo) && abs($nuevo_saldo) < $tolerancia) {
                $estado_cxc = 2;
            }

            if ($nuevo_saldo == 0) {
                $estado_cxc = 2;
            }
            
            //ACTUALIZAR CUENTA POR cobrar_clientes
            $update_ccx = "UPDATE cobrar_clientes_grupales SET saldo = ?, estado = ? WHERE facturas_id = ?";
            $stmt = $mysqli->prepare($update_ccx);
            $stmt->bind_param("dii", $nuevo_saldo, $estado_cxc, $facturas_id);
            $stmt->execute();    
            
            $datos = array(
                0 => "Almacenado",
                1 => "Pago Realizado Correctamente, ¿Desea enviar esta factura por correo electrónico?",
                2 => "info",
                3 => "btn-primary",
                4 => "formEfectivoBillGrupal",
                5 => "Registro",
                6 => $tipoLabel,
                7 => "modal_grupo_pagos",
                8 => $facturas_id,
                9 => $numero,
                10 => "Guardar"
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