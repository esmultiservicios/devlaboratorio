<?php
// addPagoCheque.php
session_start();   
include "../funtions.php";

// CONEXION A DB
$mysqli = connect_mysqli();

$facturas_id     = $_POST['factura_id_cheque'];
$fecha           = date("Y-m-d");
$fecha_registro  = date("Y-m-d H:i:s");
$importe         = $_POST['monto_efectivo'];
$efectivo_bill   = $_POST['importe'] ?? 0;
$cambio          = 0;
$empresa_id      = $_SESSION['empresa_id'];
$usuario         = $_SESSION['colaborador_id'];
$tipo_pago_id    = 3; // CHEQUE
$banco_id        = $_POST['bk_nm_chk'];
$tipo_pago       = 1; // 1 CONTADO, 2 CREDITO
$estado_pago     = 1; // ACTIVO
$estado          = 2; // FACTURA PAGADA
$efectivo        = 0;
$tarjeta         = $importe;
$tipoLabel       = "Pagos";

$referencia_pago1 = cleanStringConverterCase($_POST['check_num']);
$referencia_pago2 = "";
$referencia_pago3 = "";
$activo           = 1; // SECUENCIA DE FACTURACION

// CONSULTAR DATOS DE LA FACTURA
$query_factura = "SELECT tipo_factura FROM facturas WHERE facturas_id = '$facturas_id'";
$result_factura = $mysqli->query($query_factura) or die($mysqli->error);
$consultaFactura = $result_factura->fetch_assoc();
$tipo_factura = "";
if($result_factura->num_rows>0){
    $tipo_factura = $consultaFactura['tipo_factura'];
}
if($tipo_factura == 2){
    $tipoLabel = "PagosCredito";
}

// CONSULTAR SECUENCIA FACTURACION (si ya tuviera)
$query_secuencia = "SELECT secuencia_facturacion_id FROM facturas WHERE facturas_id  = '$facturas_id'";
$result_secuencia = $mysqli->query($query_secuencia) or die($mysqli->error);
if($result_secuencia->num_rows>0){
    $consulta2secuencia = $result_secuencia->fetch_assoc();
    $secuencia_facturacion_id = $consulta2secuencia['secuencia_facturacion_id'];
}

if($tipo_factura === "1"){ // CONTADO
    // Validar pago previo
    $query_factura = "SELECT pagos_id FROM pagos WHERE facturas_id = '$facturas_id'";
    $result_fact = $mysqli->query($query_factura) or die($mysqli->error);

    if($result_fact->num_rows==0){
        $pagos_id  = correlativo('pagos_id', 'pagos');
        $insert = "INSERT INTO pagos VALUES ('$pagos_id','$facturas_id','$tipo_pago','$fecha','$importe','$efectivo','$cambio','$tarjeta','$usuario','$estado_pago','$empresa_id','$fecha_registro')";
        $query = $mysqli->query($insert);

        if($query){
            // Detalle
            $pagos_detalles_id  = correlativo('pagos_detalles_id', 'pagos_detalles');
            $insert = "INSERT INTO pagos_detalles VALUES ('$pagos_detalles_id','$pagos_id','$tipo_pago_id','$banco_id','$importe','$referencia_pago1','$referencia_pago2','$referencia_pago3')";
            $mysqli->query($insert);

            // Factura pagada
            $mysqli->query("UPDATE facturas SET estado = '$estado' WHERE facturas_id = '$facturas_id'") or die($mysqli->error);

            // Marcar muestra
            $rsM = $mysqli->query("SELECT muestras_id FROM facturas WHERE facturas_id = '$facturas_id'") or die($mysqli->error);
            if($rsM->num_rows>0){
                $muestras_id = $rsM->fetch_assoc()['muestras_id'];
                if($muestras_id){
                    $mysqli->query("UPDATE muestras SET estado='1' WHERE muestras_id = '$muestras_id'") or die($mysqli->error);
                }
            }

            // CxC
            $rsCxC = $mysqli->query("SELECT saldo FROM cobrar_clientes WHERE facturas_id = '$facturas_id'") or die($mysqli->error);
            if($rsCxC->num_rows>0){
                $saldo_cxc = (float)$rsCxC->fetch_assoc()['saldo'];
                $nuevo_saldo = (float)$saldo_cxc - (float)$importe;
                $estado_cxc = (abs($nuevo_saldo) < 0.0001) ? 2 : 1;
                $mysqli->query("UPDATE cobrar_clientes SET saldo='$nuevo_saldo', estado='$estado_cxc' WHERE facturas_id='$facturas_id'") or die($mysqli->error);
            }

            $datos = array(0=>"Guardar",1=>"Pago Realizado Correctamente",2=>"info",3=>"btn-primary",4=>"formEfectivoBill",5=>"Registro",6=>$tipoLabel,7=>"modal_pagos",8=>$facturas_id,9=>"Guardar");
        }else{
            $datos = array(0=>"Error",1=>"No se puedo almacenar este registro, los datos son incorrectos por favor corregir",2=>"error",3=>"btn-danger",4=>"",5=>"");
        }
    }else{
        $datos = array(0=>"Error",1=>"Lo sentimos, no se puede almacenar el pago por favor valide si existe un pago para esta factura",2=>"error",3=>"btn-danger",4=>"",5=>"");
    }
}else{ // CREDITO (abono)
    // Saldo actual
    $rsCxC = $mysqli->query("SELECT saldo FROM cobrar_clientes WHERE facturas_id = '$facturas_id' AND estado = 1") or die($mysqli->error);
    if($rsCxC->num_rows>0){
        $saldo_cxc = (float)$rsCxC->fetch_assoc()['saldo'];
        $nuevo_saldo = (float)$saldo_cxc - (float)$efectivo_bill;

        // Insert pago
        $pagos_id  = correlativo('pagos_id', 'pagos');
        $insert = "INSERT INTO pagos VALUES ('$pagos_id','$facturas_id','2','$fecha','$efectivo_bill','0','$cambio','$efectivo_bill','$usuario','$estado_pago','$empresa_id','$fecha_registro')";
        $query = $mysqli->query($insert);

        if($query){
            // Detalle
            $pagos_detalles_id  = correlativo('pagos_detalles_id', 'pagos_detalles');
            $insert = "INSERT INTO pagos_detalles VALUES ('$pagos_detalles_id','$pagos_id','$tipo_pago_id','$banco_id','$efectivo_bill','$referencia_pago1','$referencia_pago2','$referencia_pago3')";
            $mysqli->query($insert);

            // CxC
            $estado_cxc = (abs($nuevo_saldo) < 0.0001 || $nuevo_saldo == 0) ? 2 : 1;
            $mysqli->query("UPDATE cobrar_clientes SET saldo='$nuevo_saldo', estado='$estado_cxc' WHERE facturas_id='$facturas_id'") or die($mysqli->error);

            // Si saldo quedó en cero => marcar muestra y factura pagada + asignar secuencia de factura electrónica
            if($estado_cxc == 2){
                // Factura pagada
                $mysqli->query("UPDATE facturas SET estado='2' WHERE facturas_id='$facturas_id'") or die($mysqli->error);
                // Muestra atendida
                $rsM = $mysqli->query("SELECT muestras_id FROM facturas WHERE facturas_id='$facturas_id'") or die($mysqli->error);
                if($rsM->num_rows>0){
                    $muestras_id = $rsM->fetch_assoc()['muestras_id'];
                    if($muestras_id){
                        $mysqli->query("UPDATE muestras SET estado='1' WHERE muestras_id='$muestras_id'") or die($mysqli->error);
                    }
                }
                // Secuencia factura electrónica
                $documento = "1";
                $qSec = "SELECT secuencia_facturacion_id, siguiente AS numero FROM secuencia_facturacion WHERE activo='1' AND empresa_id='$empresa_id' AND documento_id='$documento'";
                $resSec = $mysqli->query($qSec) or die($mysqli->error);
                if($resSec->num_rows>0){
                    $rowSec = $resSec->fetch_assoc();
                    $secuencia_facturacion_id = $rowSec['secuencia_facturacion_id'];
                    $numero = $rowSec['numero'];

                    $mysqli->query("UPDATE facturas SET secuencia_facturacion_id='$secuencia_facturacion_id', number='$numero' WHERE facturas_id='$facturas_id'") or die($mysqli->error);

                    $tipoLabel = "PagosCXC";

                    $numero_secuencia_facturacion = correlativoSecuenciaFacturacion("siguiente","secuencia_facturacion","documento_id = 1 AND activo = 1");
                    $mysqli->query("UPDATE secuencia_facturacion SET siguiente='$numero_secuencia_facturacion' WHERE secuencia_facturacion_id='$secuencia_facturacion_id'");
                }
            }

            $datos = array(0=>"Guardar",1=>"Pago Realizado Correctamente",2=>"info",3=>"btn-primary",4=>"formEfectivoBill",5=>"Registro",6=>$tipoLabel,7=>"modal_pagos",8=>$facturas_id,9=>"Guardar");
        }else{
            $datos = array(0=>"Error",1=>"No se puedo almacenar este registro, los datos son incorrectos por favor corregir",2=>"error",3=>"btn-danger",4=>"",5=>"");
        }
    }else{
        $datos = array(0=>"Error",1=>"No existe un cobro pendiente para este cliente",2=>"error",3=>"btn-danger",4=>"",5=>"");
    }
}

echo json_encode($datos);