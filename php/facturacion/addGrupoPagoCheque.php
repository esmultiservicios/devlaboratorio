<?php
//addGrupopagoCheque.php
session_start();
include "../funtions.php";

// CONEXION A DB
$mysqli = connect_mysqli();
$mysqli->set_charset('utf8mb4');

// --------- ENTRADAS ----------
$facturas_id    = (int)($_POST['factura_id_cheque'] ?? 0);     // facturas_grupal_id
$fecha          = date("Y-m-d");
$importe        = (float)($_POST['monto_efectivo'] ?? 0);      // total (contado)
$efectivo_bill  = (float)($_POST['importe'] ?? 0);             // abono (crédito)
$cambio         = 0;
$empresa_id     = (int)$_SESSION['empresa_id'];
$usuario        = (int)$_SESSION['colaborador_id'];

$tipo_pago_id   = 3;                                           // CHEQUE
$banco_id       = (int)($_POST['bk_nm_chk'] ?? 0);
$tipo_pago      = 1;                                           // 1=CONTADO 2=CRÉDITO (campo en pagos/pagos_grupal)
$estado         = 2;                                           // FACTURA PAGADA (para facturas contado)
$estado_atencion= 1;                                           // para atenciones
$estado_pago    = 1;                                           // activo
$fecha_registro = date("Y-m-d H:i:s");

$referencia_pago1 = cleanStringConverterCase($_POST['check_num'] ?? '');
$referencia_pago2 = "";
$referencia_pago3 = "";

// Para CHEQUE, en tu esquema usas 'tarjeta' para guardar el monto no-efectivo.
$efectivo   = 0.0;
$tarjeta    = (float)$importe;
$tipoLabel  = "PagosGrupal";

// --------- HELPERS ----------
function obtenerTotalFactura($mysqli, $facturaId) {
    $sql = "SELECT COALESCE(SUM(cantidad * precio),0) AS total_bruto
            FROM facturas_detalle
            WHERE facturas_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $facturaId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : ['total_bruto'=>0];
    $stmt->close();
    return (float)$row['total_bruto'];
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

function insertarPagoFacturaCheque($mysqli, $facturaId, $tipo_pago, $fecha, $monto, $usuario, $empresa_id, $fecha_registro, $tipo_pago_id, $banco_id, $ref1, $ref2, $ref3) {
    $efectivo = 0.0; $tarjeta = $monto; $cambio = 0; $estado_pago = 1;
    $pagos_id = correlativo("pagos_id","pagos");
    $insPag = "INSERT INTO pagos
        (pagos_id, facturas_id, tipo_pago, fecha, importe, efectivo, cambio, tarjeta, usuario, estado, empresa_id, fecha_registro)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $mysqli->prepare($insPag);
    $stmt->bind_param("iiisddidiiis",
        $pagos_id, $facturaId, $tipo_pago, $fecha, $monto, $efectivo, $cambio, $tarjeta, $usuario, $estado_pago, $empresa_id, $fecha_registro
    );
    if(!$stmt->execute()){ $stmt->close(); throw new Exception("Error pago (cheque) factura $facturaId: ".$mysqli->error); }
    $stmt->close();

    $pagos_detalles_id = correlativo("pagos_detalles_id","pagos_detalles");
    $insDet = "INSERT INTO pagos_detalles
        (pagos_detalles_id, pagos_id, tipo_pago_id, banco_id, efectivo, descripcion1, descripcion2, descripcion3)
        VALUES (?,?,?,?,?,?,?,?)";
    $stmt = $mysqli->prepare($insDet);
    $stmt->bind_param("iiiidsss", $pagos_detalles_id, $pagos_id, $tipo_pago_id, $banco_id, $monto, $ref1, $ref2, $ref3);
    if(!$stmt->execute()){ $stmt->close(); throw new Exception("Error detalle pago (cheque) factura $facturaId: ".$mysqli->error); }
    $stmt->close();
    return $pagos_id;
}

// --------- TRANSACCIÓN ----------
$mysqli->begin_transaction();

try {
    // Datos de la factura GRUPAL
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
    $tipo_factura   = (int)$rowF['tipo_factura']; // 1=contado 2=crédito
    $numero         = (string)$rowF['number'];
    if ($tipo_factura === 2) $tipoLabel = "PagosGrupalCredito";

    // Si contado, validar que no exista pago grupal previo
    if ($tipo_factura === 1) {
        $sqlPrev = "SELECT pagos_grupal_id FROM pagos_grupal WHERE facturas_grupal_id = ?";
        $stmt = $mysqli->prepare($sqlPrev);
        $stmt->bind_param("i", $facturas_id);
        $stmt->execute();
        $prev = $stmt->get_result();
        $stmt->close();
        if ($prev->num_rows > 0) throw new Exception("El pago de esta factura grupal ya fue registrado");
    }

    // 1) Registrar pago grupal (cheque)
    $monto_grupal = ($tipo_factura === 1) ? $importe : $efectivo_bill;
    $efectivo_grupal = 0.0; $tarjeta_grupal = $monto_grupal; $cambio_grupal = 0;

    $pagos_grupal_id = correlativo("pagos_grupal_id", "pagos_grupal");
    $insPG = "INSERT INTO pagos_grupal
        (pagos_grupal_id, facturas_grupal_id, tipo_pago, fecha, importe, efectivo, cambio, tarjeta, usuario, estado, empresa_id, fecha_registro)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $mysqli->prepare($insPG);
    $stmt->bind_param("iiisddidiiis",
        $pagos_grupal_id, $facturas_id, $tipo_pago, $fecha_factura,
        $monto_grupal, $efectivo_grupal, $cambio_grupal, $tarjeta_grupal,
        $usuario, $estado_pago, $empresa_id, $fecha_registro
    );
    if(!$stmt->execute()) throw new Exception("Error pago grupal (cheque): ".$stmt->error);
    $stmt->close();

    $pgd_id = correlativo('pagos_grupal_detalles_id', 'pagos_grupal_detalles');
    $insPGD = "INSERT INTO pagos_grupal_detalles
        (pagos_grupal_detalles_id, pagos_id, tipo_pago_id, banco_id, efectivo, descripcion1, descripcion2, descripcion3)
        VALUES (?,?,?,?,?,?,?,?)";
    $stmt = $mysqli->prepare($insPGD);
    $stmt->bind_param("iiiidsss", $pgd_id, $pagos_grupal_id, $tipo_pago_id, $banco_id, $monto_grupal, $referencia_pago1, $referencia_pago2, $referencia_pago3);
    if(!$stmt->execute()) throw new Exception("Error detalle pago grupal (cheque): ".$stmt->error);
    $stmt->close();

    // 2) Obtener facturas del grupal
    $facturas_sel = [];
    $sqlFact = "SELECT facturas_id FROM facturas_grupal_detalle WHERE facturas_grupal_id = ? ORDER BY facturas_id ASC";
    $stmt = $mysqli->prepare($sqlFact);
    $stmt->bind_param("i", $facturas_id);
    $stmt->execute();
    $resList = $stmt->get_result();
    while ($r = $resList->fetch_assoc()) $facturas_sel[] = (int)$r['facturas_id'];
    $stmt->close();

    // 3) Aplicación según tipo
    if ($tipo_factura === 1) {
        // CONTADO
        // a) grupal pagado
        $updFG = "UPDATE facturas_grupal SET estado = ? WHERE facturas_grupal_id = ?";
        $stmt = $mysqli->prepare($updFG);
        $stmt->bind_param("ii", $estado, $facturas_id);
        $stmt->execute(); $stmt->close();

        // b) pagar y marcar muestras por cada factura
        foreach ($facturas_sel as $fid) {
            $totalFactura = obtenerTotalFactura($mysqli, $fid);
            if ($totalFactura <= 0) continue;

            insertarPagoFacturaCheque(
                $mysqli, $fid, 1/*contado*/, $fecha_factura, $totalFactura,
                $usuario, $empresa_id, $fecha_registro,
                $tipo_pago_id, $banco_id, $referencia_pago1, $referencia_pago2, $referencia_pago3
            );

            // marcar muestra atendida
            marcarMuestraAtendidaPorFactura($mysqli, $fid);

            // factura pagada
            $updF = "UPDATE facturas SET estado = ? WHERE facturas_id = ?";
            $stmt = $mysqli->prepare($updF);
            $stmt->bind_param("ii", $estado, $fid);
            $stmt->execute(); $stmt->close();
        }

        // c) actualizar atencion si existe
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
            $stmt->bind_param("ii", $estado_atencion, $atencion_id);
            $stmt->execute(); $stmt->close();
        }

        // d) CxC grupal
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
        }

    } else {
        // CRÉDITO (abono con cheque)
        $abono_restante = $efectivo_bill;
        foreach ($facturas_sel as $fid) {
            if ($abono_restante <= 0) break;
            $totalFactura = obtenerTotalFactura($mysqli, $fid);
            $pagadoAntes  = pagosPreviosFactura($mysqli, $fid);
            $pendiente    = max(0.0, $totalFactura - $pagadoAntes);
            if ($pendiente <= 0) continue;

            $aPagar = ($abono_restante >= $pendiente) ? $pendiente : $abono_restante;
            if ($aPagar > 0) {
                insertarPagoFacturaCheque(
                    $mysqli, $fid, 2/*crédito*/, $fecha_factura, $aPagar,
                    $usuario, $empresa_id, $fecha_registro,
                    $tipo_pago_id, $banco_id, $referencia_pago1, $referencia_pago2, $referencia_pago3
                );

                // Recalcular pendiente y si quedó saldada: marcar muestra y poner pagada
                $pagadoNuevo = pagosPreviosFactura($mysqli, $fid);
                $pendNuevo   = max(0.0, $totalFactura - $pagadoNuevo);
                if ($pendNuevo <= 0.0001) {
                    marcarMuestraAtendidaPorFactura($mysqli, $fid);
                    $updF = "UPDATE facturas SET estado = 2 WHERE facturas_id = ?";
                    $stmt = $mysqli->prepare($updF);
                    $stmt->bind_param("i", $fid);
                    $stmt->execute(); $stmt->close();
                }
                $abono_restante -= $aPagar;
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
    }

    // COMMIT
    $mysqli->commit();
    $datos = array(
        0=>"Almacenado",1=>"Pago Realizado Correctamente, ¿Desea enviar esta factura por correo electrónico?",
        2=>"info",3=>"btn-primary",4=>"formChequeBillGrupal",5=>"Registro",
        6=>$tipoLabel,7=>"modal_grupo_pagos",8=>$facturas_id,9=>$numero,10=>"Guardar"
    );

} catch (Exception $e) {
    $mysqli->rollback();
    $datos = array(0=>"Error",1=>$e->getMessage(),2=>"error",3=>"btn-danger",4=>"",5=>"");
}
echo json_encode($datos);