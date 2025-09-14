<?php
// addGrupoPagoTransferencia.php
session_start();
include "../funtions.php";

// CONEXION A DB
$mysqli = connect_mysqli();
$mysqli->set_charset('utf8mb4');

// --------- ENTRADAS ----------
$facturas_id   = (int)($_POST['factura_id_transferencia'] ?? 0); // facturas_grupal_id
$fecha         = date("Y-m-d");
$importe       = (float)($_POST['monto_efectivo'] ?? 0);         // total contado (transfer)
$efectivo_bill = (float)($_POST['importe'] ?? 0);                 // abono crédito
$cambio        = 0;

$empresa_id    = (int)$_SESSION['empresa_id'];
$usuario       = (int)$_SESSION['colaborador_id'];

$tipo_pago_id  = 4;   // TRANSFERENCIA
$banco_id      = 0;   // SIN BANCO
$tipo_pago     = 1;   // 1=CONTADO 2=CRÉDITO
$estado_pagada = 2;   // FACTURA PAGADA
$estado_atenc  = 1;   // atenciones
$estado_pago   = 1;   // activo
$fecha_reg     = date("Y-m-d H:i:s");

$ref1 = cleanStringConverterCase($_POST['ben_nm'] ?? '');
$ref2 = "";
$ref3 = "";

$tipoLabel = "PagosGrupal";

// --------- HELPERS ----------
function totalFactura($mysqli, $facturaId) {
    $sql = "SELECT COALESCE(SUM(cantidad * precio),0) AS total
            FROM facturas_detalle
            WHERE facturas_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $facturaId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : ['total'=>0];
    $stmt->close();
    return (float)$row['total'];
}

function pagosPreviosFactura($mysqli, $facturaId) {
    $sql = "SELECT COALESCE(SUM(importe),0) AS pagado
            FROM pagos
            WHERE facturas_id = ? AND estado = 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $facturaId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : ['pagado'=>0];
    $stmt->close();
    return (float)$row['pagado'];
}

function insertarPagoFacturaTransfer($mysqli, $facturaId, $tipo_pago, $fecha, $monto, $usuario, $empresa_id, $fecha_reg, $tipo_pago_id, $banco_id, $ref1, $ref2, $ref3) {
    $efectivo = 0.0; $tarjeta = $monto; $cambio = 0; $estado = 1;
    $pagos_id = correlativo("pagos_id","pagos");
    $insPag = "INSERT INTO pagos
        (pagos_id, facturas_id, tipo_pago, fecha, importe, efectivo, cambio, tarjeta, usuario, estado, empresa_id, fecha_registro)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $mysqli->prepare($insPag);
    $stmt->bind_param("iiisddidiiis",
        $pagos_id, $facturaId, $tipo_pago, $fecha, $monto, $efectivo, $cambio, $tarjeta, $usuario, $estado, $empresa_id, $fecha_reg
    );
    if(!$stmt->execute()){ $stmt->close(); throw new Exception("Error pago (transfer) factura $facturaId: ".$mysqli->error); }
    $stmt->close();

    $pagos_detalles_id = correlativo("pagos_detalles_id","pagos_detalles");
    $insDet = "INSERT INTO pagos_detalles
        (pagos_detalles_id, pagos_id, tipo_pago_id, banco_id, efectivo, descripcion1, descripcion2, descripcion3)
        VALUES (?,?,?,?,?,?,?,?)";
    $stmt = $mysqli->prepare($insDet);
    $stmt->bind_param("iiiidsss", $pagos_detalles_id, $pagos_id, $tipo_pago_id, $banco_id, $monto, $ref1, $ref2, $ref3);
    if(!$stmt->execute()){ $stmt->close(); throw new Exception("Error detalle (transfer) factura $facturaId: ".$mysqli->error); }
    $stmt->close();
    return $pagos_id;
}

// --------- TRANSACCIÓN ----------
$mysqli->begin_transaction();

try {
    // 1) Datos de la factura GRUPAL
    $qf = "SELECT servicio_id, colaborador_id, fecha, pacientes_id, tipo_factura, number
           FROM facturas_grupal WHERE facturas_grupal_id = ?";
    $stmt = $mysqli->prepare($qf);
    $stmt->bind_param("i", $facturas_id);
    $stmt->execute();
    $resF = $stmt->get_result();
    $rowF = $resF ? $resF->fetch_assoc() : null;
    $stmt->close();
    if (!$rowF) throw new Exception("Factura grupal no encontrada");

    $servicio_id    = (int)$rowF['servicio_id'];
    $colaborador_id = (int)$rowF['colaborador_id'];
    $fecha_factura  = $rowF['fecha'];
    $pacientes_id   = (int)$rowF['pacientes_id'];
    $tipo_factura   = (int)$rowF['tipo_factura'];  // 1=contado 2=crédito
    $numero         = (string)$rowF['number'];
    if ($tipo_factura === 2) $tipoLabel = "PagosGrupalCredito";

    // 2) Pago grupal (TRANSFERENCIA)
    $monto_grupal   = ($tipo_factura === 1) ? $importe : $efectivo_bill;
    if ($monto_grupal <= 0) throw new Exception("El monto del pago no puede ser cero.");

    if ($tipo_factura === 1) {
        $sqlPrev = "SELECT pagos_grupal_id FROM pagos_grupal WHERE facturas_grupal_id = ?";
        $stmt = $mysqli->prepare($sqlPrev);
        $stmt->bind_param("i", $facturas_id);
        $stmt->execute();
        $prev = $stmt->get_result();
        $stmt->close();
        if ($prev->num_rows > 0) throw new Exception("El pago de esta factura grupal ya fue registrado");
    }

    $efectivo_grupal = 0.0; $tarjeta_grupal = $monto_grupal; $cambio_grupal = 0;

    $pagos_grupal_id = correlativo("pagos_grupal_id","pagos_grupal");
    $insPG = "INSERT INTO pagos_grupal
        (pagos_grupal_id, facturas_grupal_id, tipo_pago, fecha, importe, efectivo, cambio, tarjeta, usuario, estado, empresa_id, fecha_registro)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $mysqli->prepare($insPG);
    $stmt->bind_param("iiisddidiiis",
        $pagos_grupal_id, $facturas_id, $tipo_pago, $fecha_factura,
        $monto_grupal, $efectivo_grupal, $cambio_grupal, $tarjeta_grupal,
        $usuario, $estado_pago, $empresa_id, $fecha_reg
    );
    if(!$stmt->execute()) throw new Exception("Error pago grupal (transfer): ".$stmt->error);
    $stmt->close();

    $pgd_id = correlativo("pagos_grupal_detalles_id","pagos_grupal_detalles");
    $insPGD = "INSERT INTO pagos_grupal_detalles
        (pagos_grupal_detalles_id, pagos_id, tipo_pago_id, banco_id, efectivo, descripcion1, descripcion2, descripcion3)
        VALUES (?,?,?,?,?,?,?,?)";
    $stmt = $mysqli->prepare($insPGD);
    $stmt->bind_param("iiiidsss", $pgd_id, $pagos_grupal_id, $tipo_pago_id, $banco_id, $monto_grupal, $ref1, $ref2, $ref3);
    if(!$stmt->execute()) throw new Exception("Error detalle pago grupal (transfer): ".$stmt->error);
    $stmt->close();

    // 3) Facturas del grupal
    $facturas_sel = [];
    $sqlFact = "SELECT facturas_id FROM facturas_grupal_detalle
                WHERE facturas_grupal_id = ? ORDER BY facturas_id ASC";
    $stmt = $mysqli->prepare($sqlFact);
    $stmt->bind_param("i", $facturas_id);
    $stmt->execute();
    $resList = $stmt->get_result();
    while ($r = $resList->fetch_assoc()) $facturas_sel[] = (int)$r['facturas_id'];
    $stmt->close();

    // 4) Aplicación según tipo
    if ($tipo_factura === 1) {
        // CONTADO: pagar cada factura completa con TRANSFERENCIA + marcar muestra
        foreach ($facturas_sel as $fid) {
            $totalFactura = totalFactura($mysqli, $fid);
            if ($totalFactura <= 0) continue;

            insertarPagoFacturaTransfer(
                $mysqli, $fid, 1/*contado*/, $fecha_factura, $totalFactura,
                $usuario, $empresa_id, $fecha_reg, $tipo_pago_id, $banco_id, $ref1, $ref2, $ref3
            );

            // marcar muestra atendida
            marcarMuestraAtendidaPorFactura($mysqli, $fid);

            // factura pagada
            $updF = "UPDATE facturas SET estado = ? WHERE facturas_id = ?";
            $stmt = $mysqli->prepare($updF);
            $stmt->bind_param("ii", $estado_pagada, $fid);
            $stmt->execute(); $stmt->close();
        }

        // grupal pagado
        $updFG = "UPDATE facturas_grupal SET estado = ? WHERE facturas_grupal_id = ?";
        $stmt = $mysqli->prepare($updFG);
        $stmt->bind_param("ii", $estado_pagada, $facturas_id);
        $stmt->execute(); $stmt->close();

        // atención (si existe)
        $sqlAt = "SELECT atencion_id
                  FROM atenciones_medicas
                  WHERE pacientes_id = ? AND servicio_id = ? AND colaborador_id = ? AND fecha = ?
                  LIMIT 1";
        $stmt = $mysqli->prepare($sqlAt);
        $stmt->bind_param("iiis", $pacientes_id, $servicio_id, $colaborador_id, $fecha_factura);
        $stmt->execute();
        $resAt = $stmt->get_result();
        $rowAt = $resAt ? $resAt->fetch_assoc() : null;
        $stmt->close();
        if ($rowAt && !empty($rowAt['atencion_id'])) {
            $atencion_id = (int)$rowAt['atencion_id'];
            $updAt = "UPDATE atenciones_medicas SET estado = ? WHERE atencion_id = ?";
            $stmt = $mysqli->prepare($updAt);
            $stmt->bind_param("ii", $estado_atenc, $atencion_id);
            $stmt->execute(); $stmt->close();
        }

        // CxC grupal
        $sqlSaldo = "SELECT saldo FROM cobrar_clientes_grupales WHERE facturas_id = ?";
        $stmt = $mysqli->prepare($sqlSaldo);
        $stmt->bind_param("i", $facturas_id);
        $stmt->execute();
        $resSaldo = $stmt->get_result();
        $rowSaldo = $resSaldo ? $resSaldo->fetch_assoc() : null;
        $stmt->close();
        if ($rowSaldo) {
            $saldo_cxc   = (float)$rowSaldo['saldo'];
            $nuevo_saldo = $saldo_cxc - $monto_grupal;
            $estado_cxc  = (abs($nuevo_saldo) < 0.0001) ? 2 : 1;
            $updCxc = "UPDATE cobrar_clientes_grupales SET saldo = ?, estado = ? WHERE facturas_id = ?";
            $stmt = $mysqli->prepare($updCxc);
            $stmt->bind_param("dii", $nuevo_saldo, $estado_cxc, $facturas_id);
            $stmt->execute(); $stmt->close();
            if ($estado_cxc === 2) $tipoLabel = "PagosCXCGrupal";
        }

    } else {
        // CRÉDITO: abono secuencial con TRANSFERENCIA — y marcar muestra si saldada
        $abono_rest = $efectivo_bill;
        foreach ($facturas_sel as $fid) {
            if ($abono_rest <= 0) break;
            $total  = totalFactura($mysqli, $fid);
            $pagado = pagosPreviosFactura($mysqli, $fid);
            $pend   = max(0.0, $total - $pagado);
            if ($pend <= 0) continue;

            $aPagar = min($abono_rest, $pend);
            if ($aPagar > 0) {
                insertarPagoFacturaTransfer(
                    $mysqli, $fid, 2/*crédito*/, $fecha_factura, $aPagar,
                    $usuario, $empresa_id, $fecha_reg, $tipo_pago_id, $banco_id, $ref1, $ref2, $ref3
                );

                // si quedó saldada: marcar muestra y poner pagada
                $pagadoNuevo = pagosPreviosFactura($mysqli, $fid);
                $pendNuevo   = max(0.0, $total - $pagadoNuevo);
                if ($pendNuevo <= 0.0001) {
                    marcarMuestraAtendidaPorFactura($mysqli, $fid);
                    $updF = "UPDATE facturas SET estado = 2 WHERE facturas_id = ?";
                    $stmt = $mysqli->prepare($updF);
                    $stmt->bind_param("i", $fid);
                    $stmt->execute(); $stmt->close();
                }

                $abono_rest -= $aPagar;
            }
        }

        // CxC grupal
        $sqlSaldo = "SELECT saldo FROM cobrar_clientes_grupales WHERE facturas_id = ? AND estado = 1";
        $stmt = $mysqli->prepare($sqlSaldo);
        $stmt->bind_param("i", $facturas_id);
        $stmt->execute();
        $resSaldo = $stmt->get_result();
        $rowSaldo = $resSaldo ? $resSaldo->fetch_assoc() : null;
        $stmt->close();
        if (!$rowSaldo) throw new Exception("No existe un cobro pendiente para este cliente");

        $saldo_cxc   = (float)$rowSaldo['saldo'];
        $nuevo_saldo = $saldo_cxc - $efectivo_bill;
        $estado_cxc  = (abs($nuevo_saldo) < 0.0001) ? 2 : 1;
        $updCxc = "UPDATE cobrar_clientes_grupales SET saldo = ?, estado = ? WHERE facturas_id = ?";
        $stmt = $mysqli->prepare($updCxc);
        $stmt->bind_param("dii", $nuevo_saldo, $estado_cxc, $facturas_id);
        $stmt->execute(); $stmt->close();
        if ($estado_cxc === 2) $tipoLabel = "PagosCXCGrupal";
    }

    // COMMIT
    $mysqli->commit();
    $datos = array(
        0=>"Almacenado",1=>"Pago Realizado Correctamente, ¿Desea enviar esta factura por correo electrónico?",
        2=>"info",3=>"btn-primary",4=>"formTransferenciaBillGrupal",5=>"Registro",
        6=>$tipoLabel,7=>"modal_grupo_pagos",8=>$facturas_id,9=>$numero,10=>"Guardar"
    );

} catch (Exception $e) {
    $mysqli->rollback();
    $datos = array(0=>"Error",1=>$e->getMessage(),2=>"error",3=>"btn-danger",4=>"",5=>"");
}
echo json_encode($datos);
