<?php
// addGrupoFactura.php
session_start();
include "../funtions.php";

// CONEXION A DB
$mysqli = connect_mysqli();

// Iniciar transacción
$mysqli->begin_transaction();

try {
    // =========================
    // 1) ENTRADAS
    // =========================
    $pacientes_id   = intval($_POST['clienteIDGrupo']);           // Cliente/Empresa
    $fecha          = date("Y-m-d");
    $colaborador_id = intval($_POST['colaborador_idGrupo']);
    $servicio_id    = intval($_POST['servicio_idGrupo']);
    $notes          = cleanStringStrtolower($_POST['notesBillGrupo'] ?? '');
    $tamano         = intval($_POST['tamano'] ?? 0);
    $usuario        = intval($_SESSION['colaborador_id']);
    $empresa_id     = intval($_SESSION['empresa_id']);
    $fecha_registro = date("Y-m-d H:i:s");

    // Estados / configuración
    $estado         = 4; // CREDITO (según tu semántica)
    $cierre         = 2; // 1=Sí / 2=No
    $tipo           = "";

    // Tipo de factura (1 contado, 2 crédito)
    if (isset($_POST['facturas_grupal_activo'])) {
        if ($_POST['facturas_grupal_activo'] === "") {
            $tipo_factura = 2;
            $tipo = "facturacionGrupalCredito";
        } else {
            $tipo_factura = intval($_POST['facturas_grupal_activo']);
            $tipo = "facturacionGrupal";
        }
    } else {
        $tipo_factura = 2;
        $tipo = "facturacionGrupalCredito";
    }

    // Documento para secuencia: 1=Factura (contado), 4=Proforma (crédito)
    $documento_tipo = ($tipo_factura == 1) ? 1 : 4;

    // =========================
    // 2) OBTENER NÚMERO DE FACTURA (fallidos -> secuencia)
    // =========================
    $numeroFactura = obtenerNumeroFactura($mysqli, $empresa_id, $documento_tipo);
    if ($numeroFactura['error']) {
        throw new Exception($numeroFactura['mensaje']);
    }

    $secuencia_facturacion_id = intval($numeroFactura['data']['secuencia_facturacion_id']);
    $numero   = intval($numeroFactura['data']['numero']);
    $prefijo  = (string)($numeroFactura['data']['prefijo'] ?? '');
    $relleno  = intval($numeroFactura['data']['relleno'] ?? 0);
    $no_factura = $prefijo . str_pad($numero, $relleno, "0", STR_PAD_LEFT);

    // =========================
    // 3) VALIDAR DETALLE (líneas seleccionadas)
    // =========================
    if (
        $tamano <= 0 ||
        !isset($_POST['billGrupoID'][0]) ||
        $_POST['billGrupoID'][0] === "" ||
        !isset($_POST['pacienteIDBillGrupo'][0]) ||
        $_POST['pacienteIDBillGrupo'][0] === ""
    ) {
        throw new Exception("El detalle de la factura no puede quedar vacío");
    }

    // =========================
    // 4) INSERTAR CABECERA GRUPAL
    // =========================
    $facturas_grupal_id = correlativo("facturas_grupal_id", "facturas_grupal");

    $insert_grupal = "INSERT INTO facturas_grupal
        (facturas_grupal_id, secuencia_facturacion_id, number, tipo_factura, pacientes_id, colaborador_id, servicio_id, importe, notas, fecha, estado, cierre, usuario, empresa_id, fecha_registro)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

    $stmt = $mysqli->prepare($insert_grupal);
    $importe_inicial = 0.00;
    $number_str = (string)$numero; // columna es char(15)
    // Tipos: i,i,s,i,i,i,i,d,s,s,i,i,i,i,s  => "iisiiiidssiiiis"
    $stmt->bind_param(
        "iisiiiidssiiiis",
        $facturas_grupal_id,
        $secuencia_facturacion_id,
        $number_str,
        $tipo_factura,
        $pacientes_id,
        $colaborador_id,
        $servicio_id,
        $importe_inicial,
        $notes,
        $fecha,
        $estado,
        $cierre,
        $usuario,
        $empresa_id,
        $fecha_registro
    );

    if (!$stmt->execute()) {
        registrarNumeroFallido($mysqli, $empresa_id, $documento_tipo, $numero);
        throw new Exception("Error al guardar la factura grupal: " . $stmt->error);
    }
    $stmt->close();

    // =========================
    // 5) PROCESAR DETALLES
    //    - Actualiza cada factura individual con la misma secuencia/número y estado=2 (Pagado)
    //    - Inserta en facturas_grupal_detalle con totales calculados desde facturas_detalle
    // =========================
    $total_valor = 0.0;
    $descuentos  = 0.0;
    $isv_neto    = 0.0;

    for ($i = 0; $i < $tamano; $i++) {
        if (
            empty($_POST['billGrupoID'][$i]) ||
            empty($_POST['pacienteIDBillGrupo'][$i])
        ) {
            continue;
        }

        $lineaFactura_id   = intval($_POST['billGrupoID'][$i]);           // facturas_id
        $lineaPacientes_id = intval($_POST['pacienteIDBillGrupo'][$i]);   // paciente de la línea
        $muestra_id        = isset($_POST['billGrupoMuestraID'][$i]) ? intval($_POST['billGrupoMuestraID'][$i]) : 0;

        // a) Actualizar factura individual con la secuencia y número, estado=2 (Pagado)
        $update_fact = "UPDATE facturas
                        SET fecha = ?, number = ?, secuencia_facturacion_id = ?, estado = 2
                        WHERE facturas_id = ?";
        $stmt = $mysqli->prepare($update_fact);
        $stmt->bind_param("siii", $fecha, $numero, $secuencia_facturacion_id, $lineaFactura_id);
        if (!$stmt->execute()) {
            throw new Exception("Error al actualizar factura individual (ID $lineaFactura_id): " . $stmt->error);
        }
        $stmt->close();

        // b) Obtener TOTALES desde facturas_detalle para esa factura
        $sql_sum = "SELECT
                        COALESCE(SUM(cantidad * precio), 0) AS importe_sum,
                        COALESCE(SUM(isv_valor), 0)        AS isv_sum,
                        COALESCE(SUM(descuento), 0)        AS desc_sum
                    FROM facturas_detalle
                    WHERE facturas_id = ?";
        $stmt = $mysqli->prepare($sql_sum);
        $stmt->bind_param("i", $lineaFactura_id);
        $stmt->execute();
        $resSum = $stmt->get_result();
        $rowSum = $resSum ? $resSum->fetch_assoc() : ['importe_sum' => 0, 'isv_sum' => 0, 'desc_sum' => 0];
        $stmt->close();

        $lineaImporte   = (float)$rowSum['importe_sum'];
        $lineaISV       = (float)$rowSum['isv_sum'];
        $lineaDescuento = (float)$rowSum['desc_sum'];
        $lineaCantidad  = 1; // SIEMPRE 1 (aunque la factura tenga varios detalles)

        // c) Insertar detalle grupal
        $facturas_grupal_detalle_id = correlativo("facturas_grupal_detalle_id", "facturas_grupal_detalle");
        $insert_det = "INSERT INTO facturas_grupal_detalle
            (facturas_grupal_detalle_id, facturas_grupal_id, facturas_id, pacientes_id, muestras_id, cantidad, importe, isv_valor, descuento)
            VALUES (?,?,?,?,?,?,?,?,?)";
        $stmt = $mysqli->prepare($insert_det);
        // Tipos: i,i,i,i,i,i,d,d,d => "iiiiiiddd"
        $stmt->bind_param(
            "iiiiiiddd",
            $facturas_grupal_detalle_id,
            $facturas_grupal_id,
            $lineaFactura_id,
            $lineaPacientes_id,
            $muestra_id,
            $lineaCantidad,
            $lineaImporte,
            $lineaISV,
            $lineaDescuento
        );
        if (!$stmt->execute()) {
            throw new Exception("Error al guardar detalle de factura grupal (Factura ID $lineaFactura_id): " . $stmt->error);
        }
        $stmt->close();

        // d) Acumular totales del grupal
        $total_valor += $lineaImporte;
        $descuentos  += $lineaDescuento;
        $isv_neto    += $lineaISV;
    }

    $total_despues_isv = ($total_valor + $isv_neto) - $descuentos; // p.ej. 6050.00

    // =========================
    // 6) ACTUALIZAR IMPORTE DEL GRUPAL
    // =========================
    $update_grupal = "UPDATE facturas_grupal
                      SET importe = ?, usuario = ?
                      WHERE facturas_grupal_id = ?";
    $stmt = $mysqli->prepare($update_grupal);
    $stmt->bind_param("dii", $total_despues_isv, $usuario, $facturas_grupal_id);
    if (!$stmt->execute()) {
        throw new Exception("Error al actualizar importe de factura grupal: " . $stmt->error);
    }
    $stmt->close();

    // =========================
    // 7) REGISTRAR CUENTA POR COBRAR GRUPAL
    // =========================
    $sel_cxc = "SELECT cobrar_clientes_id FROM cobrar_clientes_grupales WHERE facturas_id = ?";
    $stmt = $mysqli->prepare($sel_cxc);
    $stmt->bind_param("i", $facturas_grupal_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if (!$result || $result->num_rows == 0) {
        $cobrar_clientes_id = correlativo("cobrar_clientes_id", "cobrar_clientes_grupales");
        $estado_cxc = 1; // Pendiente

        $insert_cxc = "INSERT INTO cobrar_clientes_grupales
            (cobrar_clientes_id, pacientes_id, facturas_id, fecha, saldo, estado, usuario, empresa_id, fecha_registro)
            VALUES (?,?,?,?,?,?,?,?,?)";
        $stmt = $mysqli->prepare($insert_cxc);
        // Tipos: i,i,i,s,d,i,i,i,s => "iiisdiiis"
        $stmt->bind_param(
            "iiisdiiis",
            $cobrar_clientes_id,
            $pacientes_id,
            $facturas_grupal_id,
            $fecha,
            $total_despues_isv,
            $estado_cxc,
            $usuario,
            $empresa_id,
            $fecha_registro
        );
        if (!$stmt->execute()) {
            throw new Exception("Error al registrar cuenta por cobrar grupal: " . $stmt->error);
        }
        $stmt->close();
    }

    // =========================
    // 8) COMMIT
    // =========================
    $mysqli->commit();

    $datos = array(
        0 => "Almacenado",
        1 => "Registro Almacenado Correctamente",
        2 => "success",
        3 => "btn-primary",
        4 => "formGrupoFacturacion",
        5 => "Registro",
        6 => $tipo,
        7 => "",
        8 => $facturas_grupal_id,
        9 => $numero,
    );

} catch (Exception $e) {
    // Rollback
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


/**
 * Registra un número de factura fallido para su posible reutilización
 */
function registrarNumeroFallido($conexion, $empresa_id, $documento_id, $numero) {
    try {
        $insert = "INSERT INTO secuencia_factura_fallida (empresa_id, documento_id, numero, fecha_registro)
                   VALUES (?, ?, ?, NOW())";
        $stmt = $conexion->prepare($insert);
        $stmt->bind_param("iii", $empresa_id, $documento_id, $numero);
        $stmt->execute();
        $stmt->close();
        return true;
    } catch (Exception $e) {
        error_log("Error al registrar número fallido: " . $e->getMessage());
        return false;
    }
}
