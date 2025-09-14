<?php
// addPagoEfectivo.php
session_start();   
include "../funtions.php";

// CONEXION A DB
$mysqli = connect_mysqli();

$facturas_id     = $_POST['factura_id_efectivo'];
$fecha           = date("Y-m-d");
$importe         = $_POST['monto_efectivo'];
$efectivo_bill   = $_POST['efectivo_bill'] ?? 0;
$cambio          = $_POST['cambio_efectivo'];
$empresa_id      = $_SESSION['empresa_id'];
$usuario         = $_SESSION['colaborador_id'];
$tipo_pago_id    = 1; // EFECTIVO
$banco_id        = 0;
$tipo_pago       = 1; // 1 CONTADO, 2 CREDITO
$estado          = 2; // FACTURA PAGADA
$estado_pago     = 1; // ACTIVO
$fecha_registro  = date("Y-m-d H:i:s");
$efectivo        = $importe;
$tarjeta         = 0;
$tipoLabel       = "Pagos";

// tipo factura
$r = $mysqli->query("SELECT tipo_factura FROM facturas WHERE facturas_id = '$facturas_id'") or die($mysqli->error);
$tipo_factura = ($r->num_rows>0 ? $r->fetch_assoc()['tipo_factura'] : "");
if($tipo_factura == 2){ $tipoLabel = "PagosCredito"; }

// CONTADO
if($tipo_factura === "1"){
    $rp = $mysqli->query("SELECT pagos_id FROM pagos WHERE facturas_id = '$facturas_id'") or die($mysqli->error);
    if($rp->num_rows==0){
        $pagos_id = correlativo('pagos_id','pagos');
        $mysqli->query("INSERT INTO pagos VALUES ('$pagos_id','$facturas_id','1','$fecha','$importe','$efectivo','$cambio','$tarjeta','$usuario','$estado_pago','$empresa_id','$fecha_registro')");
        // detalle
        $pagos_detalles_id = correlativo('pagos_detalles_id','pagos_detalles');
        $mysqli->query("INSERT INTO pagos_detalles VALUES ('$pagos_detalles_id','$pagos_id','$tipo_pago_id','$banco_id','$importe','','','')");
        // factura pagada
        $mysqli->query("UPDATE facturas SET estado='$estado' WHERE facturas_id='$facturas_id'") or die($mysqli->error);
        // muestra
        $rm = $mysqli->query("SELECT muestras_id FROM facturas WHERE facturas_id='$facturas_id'") or die($mysqli->error);
        if($rm->num_rows>0){
            $muestras_id = $rm->fetch_assoc()['muestras_id'];
            if($muestras_id){ $mysqli->query("UPDATE muestras SET estado='1' WHERE muestras_id='$muestras_id'") or die($mysqli->error); }
        }
        // CxC
        $rc = $mysqli->query("SELECT saldo FROM cobrar_clientes WHERE facturas_id='$facturas_id'") or die($mysqli->error);
        if($rc->num_rows>0){
            $saldo_cxc = (float)$rc->fetch_assoc()['saldo'];
            $nuevo_saldo = (float)$saldo_cxc - (float)$importe;
            $estado_cxc = (abs($nuevo_saldo) < 0.0001) ? 2 : 1;
            $mysqli->query("UPDATE cobrar_clientes SET saldo='$nuevo_saldo', estado='$estado_cxc' WHERE facturas_id='$facturas_id'") or die($mysqli->error);
        }
        $datos = array(0=>"Guardar",1=>"Pago Realizado Correctamente",2=>"info",3=>"btn-primary",4=>"formEfectivoBill",5=>"Registro",6=>$tipoLabel,7=>"modal_pagos",8=>$facturas_id,9=>"Guardar");
    }else{
        $datos = array(0=>"Error",1=>"Lo sentimos, no se puede almacenar el pago por favor valide si existe un pago para esta factura",2=>"error",3=>"btn-danger",4=>"",5=>"");
    }

}else{ // CREDITO
    $rc = $mysqli->query("SELECT saldo FROM cobrar_clientes WHERE facturas_id='$facturas_id' AND estado=1") or die($mysqli->error);
    if($rc->num_rows>0){
        $saldo_cxc = (float)$rc->fetch_assoc()['saldo'];
        $nuevo_saldo = (float)$saldo_cxc - (float)$efectivo_bill;

        $pagos_id = correlativo('pagos_id','pagos');
        $mysqli->query("INSERT INTO pagos VALUES ('$pagos_id','$facturas_id','2','$fecha','$efectivo_bill','$efectivo_bill','0','0','$usuario','$estado_pago','$empresa_id','$fecha_registro')");
        // detalle
        $pagos_detalles_id = correlativo('pagos_detalles_id','pagos_detalles');
        $mysqli->query("INSERT INTO pagos_detalles VALUES ('$pagos_detalles_id','$pagos_id','$tipo_pago_id','$banco_id','$efectivo_bill','','','')");

        $estado_cxc = (abs($nuevo_saldo) < 0.0001 || $nuevo_saldo == 0) ? 2 : 1;
        $mysqli->query("UPDATE cobrar_clientes SET saldo='$nuevo_saldo', estado='$estado_cxc' WHERE facturas_id='$facturas_id'") or die($mysqli->error);

        if($estado_cxc == 2){
            // factura pagada + muestra
            $mysqli->query("UPDATE facturas SET estado='2' WHERE facturas_id='$facturas_id'") or die($mysqli->error);
            $rm = $mysqli->query("SELECT muestras_id FROM facturas WHERE facturas_id='$facturas_id'") or die($mysqli->error);
            if($rm->num_rows>0){
                $muestras_id = $rm->fetch_assoc()['muestras_id'];
                if($muestras_id){ $mysqli->query("UPDATE muestras SET estado='1' WHERE muestras_id='$muestras_id'") or die($mysqli->error); }
            }
            // secuencia factura electrónica
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
        $datos = array(0=>"Error",1=>"No existe un cobro pendiente para este cliente",2=>"error",3=>"btn-danger",4=>"",5=>"");
    }
}

echo json_encode($datos);