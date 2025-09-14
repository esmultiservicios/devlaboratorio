<?php
//addGrupoPagoEfectivo.php
session_start();
include "../funtions.php";

// CONEXION A DB
$mysqli = connect_mysqli();
$mysqli->set_charset('utf8mb4');

// --------- ENTRADAS ---------
$facturas_id   = (int)($_POST['factura_id_efectivo'] ?? 0);       // facturas_grupal_id
$fecha         = date("Y-m-d");
$importe       = (float)($_POST['monto_efectivo'] ?? 0);
$efectivo_bill = (float)($_POST['efectivo_bill'] ?? 0);
$cambio        = (float)($_POST['cambio_efectivo'] ?? 0);
$empresa_id    = (int)$_SESSION['empresa_id'];
$usuario       = (int)$_SESSION['colaborador_id'];

$tipo_pago_id    = 1; // EFECTIVO
$banco_id        = 0; // SIN BANCO
$tipo_pago       = 1; // 1=CONTADO, 2=CRÉDITO  (campo en pagos/pagos_grupal)
$estado_pagada   = 2; // FACTURA PAGADA (para contado / cuando se salda)
$estado_atencion = 1; // para atenciones
$estado_pago     = 1; // ACTIVO
$fecha_registro  = date("Y-m-d H:i:s");
$ref1 = ""; $ref2 = ""; $ref3 = "";
$tarjeta  = 0.0; // en efectivo no se usa

// --------- HELPERS ---------
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

function insertarPagoFactura($mysqli, $facturaId, $tipo_pago, $fecha, $monto, $efectivo, $cambio, $tarjeta, $usuario, $estado_pago, $empresa_id, $fecha_registro, $tipo_pago_id, $banco_id, $ref1, $ref2, $ref3) {
    // pagos (12 cols)
    $pagos_id = correlativo("pagos_id","pagos");
    $insPag = "INSERT INTO pagos
        (pagos_id, facturas_id, tipo_pago, fecha, importe, efectivo, cambio, tarjeta, usuario, estado, empresa_id, fecha_registro)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $mysqli->prepare($insPag);
    $stmt->bind_param("iiisddidiiis",
        $pagos_id, $facturaId, $tipo_pago, $fecha, $monto, $efectivo, $cambio, $tarjeta, $usuario, $estado_pago, $empresa_id, $fecha_registro
    );
    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        throw new Exception("Error al registrar pago individual de factura $facturaId: ".$err);
    }
    $stmt->close();

    // pagos_detalles (8 cols)
    $pagos_detalles_id = correlativo("pagos_detalles_id","pagos_detalles");
    $insDet = "INSERT INTO pagos_detalles
        (pagos_detalles_id, pagos_id, tipo_pago_id, banco_id, efectivo, descripcion1, descripcion2, descripcion3)
        VALUES (?,?,?,?,?,?,?,?)";
    $stmt = $mysqli->prepare($insDet);
    $stmt->bind_param("iiiidsss",
        $pagos_detalles_id, $pagos_id, $tipo_pago_id, $banco_id, $monto, $ref1, $ref2, $ref3
    );
    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        throw new Exception("Error al registrar detalle de pago de factura $facturaId: ".$err);
    }
    $stmt->close();

    return $pagos_id;
}

// --------- INICIA TRANSACCIÓN ---------
$mysqli->begin_transaction();

try {
    // Datos de la factura GRUPAL
    $query_factura = "SELECT servicio_id, colaborador_id, fecha, pacientes_id, tipo_factura, number
                      FROM facturas_grupal
                      WHERE facturas_grupal_id = ?";
    $stmt = $mysqli->prepare($query_factura);
    $stmt->bind_param("i", $facturas_id);
    $stmt->execute();
    $result_factura = $stmt->get_result();
    $consultaFactura = $result_factura->fetch_assoc();
    $stmt->close();

    if (!$consultaFactura) {
        throw new Exception("Factura grupal no encontrada");
    }

    $servicio_id    = (int)$consultaFactura['servicio_id'];
    $colaborador_id = (int)$consultaFactura['colaborador_id'];
    $fecha_factura  = $consultaFactura['fecha'];
    $pacientes_id   = (int)$consultaFactura['pacientes_id'];
    $tipo_factura   = (int)$consultaFactura['tipo_factura']; // 1=contado, 2=crédito
    $numero         = (string)$consultaFactura['number'];

    $tipoLabel = ($tipo_factura === 2) ? "PagosGrupalCredito" : "PagosGrupal";

    // ¿Existe pago grupal previo si es contado?
    if ($tipo_factura === 1) {
        $queryPagos = "SELECT pagos_grupal_id FROM pagos_grupal WHERE facturas_grupal_id = ?";
        $stmt = $mysqli->prepare($queryPagos);
        $stmt->bind_param("i", $facturas_id);
        $stmt->execute();
        $resPrev = $stmt->get_result();
        $stmt->close();

        if ($resPrev->num_rows > 0) {
            throw new Exception("El pago de esta factura grupal ya fue registrado");
        }
    }

    // =========================
    // 1) INSERTAR pago GRUPAL
    // =========================
    // Para contado: $importe es el total; para crédito: usamos $efectivo_bill (abono)
    $monto_grupal    = ($tipo_factura === 1) ? $importe : $efectivo_bill;
    $efectivo_grupal = $monto_grupal;
    $cambio_grupal   = ($tipo_factura === 1) ? (int)$cambio : 0;

    if ($monto_grupal <= 0) {
        throw new Exception("El monto del pago no puede ser cero.");
    }

    $pagos_grupal_id = correlativo("pagos_grupal_id","pagos_grupal");
    $insGrupal = "INSERT INTO pagos_grupal
        (pagos_grupal_id, facturas_grupal_id, tipo_pago, fecha, importe, efectivo, cambio, tarjeta, usuario, estado, empresa_id, fecha_registro)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $mysqli->prepare($insGrupal);
    $stmt->bind_param("iiisddidiiis",
        $pagos_grupal_id, $facturas_id, $tipo_pago, $fecha_factura, $monto_grupal, $efectivo_grupal, $cambio_grupal, $tarjeta,
        $usuario, $estado_pago, $empresa_id, $fecha_registro
    );
    if (!$stmt->execute()) {
        throw new Exception("Error al registrar pago grupal: ".$stmt->error);
    }
    $stmt->close();

    // Detalle del pago GRUPAL
    $pagos_grupal_detalles_id = correlativo('pagos_grupal_detalles_id', 'pagos_grupal_detalles');
    $insGrupalDet = "INSERT INTO pagos_grupal_detalles
        (pagos_grupal_detalles_id, pagos_id, tipo_pago_id, banco_id, efectivo, descripcion1, descripcion2, descripcion3)
        VALUES (?,?,?,?,?,?,?,?)";
    $stmt = $mysqli->prepare($insGrupalDet);
    $stmt->bind_param("iiiidsss",
        $pagos_grupal_detalles_id, $pagos_grupal_id, $tipo_pago_id, $banco_id, $monto_grupal, $ref1, $ref2, $ref3
    );
    if (!$stmt->execute()) {
        throw new Exception("Error al registrar detalle de pago grupal: ".$stmt->error);
    }
    $stmt->close();

    // =========================
    // 2) ACTUALIZACIONES por tipo
    // =========================

    // a) OBTENER facturas del grupal
    $facturas_sel = [];
    $sqlFact = "SELECT facturas_id
                FROM facturas_grupal_detalle
                WHERE facturas_grupal_id = ?
                ORDER BY facturas_id ASC";
    $stmt = $mysqli->prepare($sqlFact);
    $stmt->bind_param("i", $facturas_id);
    $stmt->execute();
    $resF = $stmt->get_result();
    while ($r = $resF->fetch_assoc()) {
        $facturas_sel[] = (int)$r['facturas_id'];
    }
    $stmt->close();

    if ($tipo_factura === 1) {
        // ------- CONTADO -------

        // 1) actualizar estado del grupal -> Pagado
        $updFG = "UPDATE facturas_grupal SET estado = ? WHERE facturas_grupal_id = ?";
        $stmt = $mysqli->prepare($updFG);
        $stmt->bind_param("ii", $estado_pagada, $facturas_id);
        $stmt->execute();
        $stmt->close();

        // 2) registrar pago COMPLETO por cada factura individual, marcar muestra y poner estado=Pagada
        foreach ($facturas_sel as $fid) {
            $totalFactura = obtenerTotalFactura($mysqli, $fid);
            if ($totalFactura <= 0) { continue; }

            insertarPagoFactura(
                $mysqli, $fid, 1/*contado*/, $fecha_factura, $totalFactura, $totalFactura, 0, 0,
                $usuario, 1/*estado pago activo*/, $empresa_id, $fecha_registro,
                $tipo_pago_id, $banco_id, $ref1, $ref2, $ref3
            );

            // marcar muestra atendida para esta factura
            marcarMuestraAtendidaPorFactura($mysqli, $fid);

            // factura pagada
            $updF = "UPDATE facturas SET estado = ? WHERE facturas_id = ?";
            $stmt = $mysqli->prepare($updF);
            $stmt->bind_param("ii", $estado_pagada, $fid);
            $stmt->execute();
            $stmt->close();
        }

        // 3) Actualizar estado de la atención (si aplica)
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
            $stmt->execute();
            $stmt->close();
        }

        // 4) CXC grupal -> actualizar saldo/estado
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
            $stmt->execute();
            $stmt->close();
        }

    } else {
        // ------- CRÉDITO (abono) -------

        $abono_restante = $efectivo_bill;

        foreach ($facturas_sel as $fid) {
            if ($abono_restante <= 0) break;

            $totalFactura  = obtenerTotalFactura($mysqli, $fid);
            $pagadoAntes   = pagosPreviosFactura($mysqli, $fid);
            $pendiente     = max(0.0, $totalFactura - $pagadoAntes);
            if ($pendiente <= 0) continue;

            $aPagar = ($abono_restante >= $pendiente) ? $pendiente : $abono_restante;

            if ($aPagar > 0) {
                insertarPagoFactura(
                    $mysqli, $fid, 2/*crédito*/, $fecha_factura, $aPagar, $aPagar, 0, 0,
                    $usuario, 1/*activo*/, $empresa_id, $fecha_registro,
                    $tipo_pago_id, $banco_id, $ref1, $ref2, $ref3
                );

                // si la factura quedó saldada con este abono => marcar muestra y poner pagada
                $pagadoNuevo = pagosPreviosFactura($mysqli, $fid);
                $pendNuevo   = max(0.0, $totalFactura - $pagadoNuevo);
                if ($pendNuevo <= 0.0001) {
                    marcarMuestraAtendidaPorFactura($mysqli, $fid);
                    $updF = "UPDATE facturas SET estado = 2 WHERE facturas_id = ?";
                    $stmt = $mysqli->prepare($updF);
                    $stmt->bind_param("i", $fid);
                    $stmt->execute();
                    $stmt->close();
                }

                $abono_restante -= $aPagar;
            }
        }

        // CXC grupal: restar abono
        $sqlSaldo = "SELECT saldo FROM cobrar_clientes_grupales WHERE facturas_id = ? AND estado = 1";
        $stmt = $mysqli->prepare($sqlSaldo);
        $stmt->bind_param("i", $facturas_id);
        $stmt->execute();
        $resSaldo = $stmt->get_result();
        $rowSaldo = $resSaldo ? $resSaldo->fetch_assoc() : null;
        $stmt->close();

        if (!$rowSaldo) {
            throw new Exception("No existe un cobro pendiente para este cliente");
        }

        $saldo_cxc   = (float)$rowSaldo['saldo'];
        $nuevo_saldo = $saldo_cxc - $efectivo_bill;
        $estado_cxc  = (abs($nuevo_saldo) < 0.0001) ? 2 : 1;

        $updCxc = "UPDATE cobrar_clientes_grupales SET saldo = ?, estado = ? WHERE facturas_id = ?";
        $stmt = $mysqli->prepare($updCxc);
        $stmt->bind_param("dii", $nuevo_saldo, $estado_cxc, $facturas_id);
        $stmt->execute();
        $stmt->close();
    }

    // --------- COMMIT ---------
    $mysqli->commit();

    $datos = array(
        0  => "Almacenado",
        1  => "Pago Realizado Correctamente, ¿Desea enviar esta factura por correo electrónico?",
        2  => "info",
        3  => "btn-primary",
        4  => "formEfectivoBillGrupal",
        5  => "Registro",
        6  => ($tipo_factura === 2 ? "PagosGrupalCredito" : "PagosGrupal"),
        7  => "modal_grupo_pagos",
        8  => $facturas_id,
        9  => $numero,
        10 => "Guardar"
    );

} catch (Exception $e) {
    $mysqli->rollback();
    $datos = array(
        0 => "Error",
        1 => $e->getMessage(),
        2 => "error",
        3 => "btn-danger",
        4 => "",
        5 => "",
    );
}

echo json_encode($datos);