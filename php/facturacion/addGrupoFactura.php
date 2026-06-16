<?php
// addGrupoFactura.php
session_start();
include "../funtions.php";

header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 0);
error_reporting(E_ALL);

// CONEXION A DB
$mysqli = connect_mysqli();
$mysqli->set_charset("utf8");

// Variables para limpieza manual en caso de error.
// IMPORTANTE: tus tablas son MyISAM, por eso rollback no protege realmente.
$facturas_grupal_id = 0;
$numero = 0;
$documento_tipo = 0;
$empresa_id = 0;
$numeroFacturaObtenido = false;
$facturaGrupalInsertada = false;
$cxcGrupalInsertada = false;
$facturasIndividualesOriginales = array();
$lockSecuenciaNombre = '';
$lockSecuenciaAdquirido = false;
$numeroFacturaData = null;

try {
    // =========================================================
    // 1) VALIDAR SESIÓN
    // =========================================================
    if (!isset($_SESSION['colaborador_id']) || !isset($_SESSION['empresa_id'])) {
        throw new Exception("La sesión expiró. Por favor, inicie sesión nuevamente.");
    }

    // =========================================================
    // 2) ENTRADAS PRINCIPALES
    // =========================================================
    $pacientes_id   = isset($_POST['clienteIDGrupo']) ? (int)$_POST['clienteIDGrupo'] : 0;
    $fecha          = date("Y-m-d");
    $colaborador_id = isset($_POST['colaborador_idGrupo']) ? (int)$_POST['colaborador_idGrupo'] : 0;
    $servicio_id    = isset($_POST['servicio_idGrupo']) ? (int)$_POST['servicio_idGrupo'] : 0;
    $notes          = isset($_POST['notesBillGrupo']) ? cleanStringStrtolower($_POST['notesBillGrupo']) : '';
    $tamano         = isset($_POST['tamano']) ? (int)$_POST['tamano'] : 0;
    $usuario        = (int)$_SESSION['colaborador_id'];
    $empresa_id     = (int)$_SESSION['empresa_id'];
    $fecha_registro = date("Y-m-d H:i:s");

    $estado = 4;
    $cierre = 2;
    $tipo   = "";

    if ($pacientes_id <= 0) {
        throw new Exception("Debe seleccionar el cliente o empresa para la factura grupal.");
    }

    if ($colaborador_id <= 0) {
        throw new Exception("Debe seleccionar el profesional para la factura grupal.");
    }

    if ($servicio_id <= 0) {
        throw new Exception("Debe seleccionar el servicio para la factura grupal.");
    }

    // =========================================================
    // 3) TIPO DE FACTURA GRUPAL
    // =========================================================
    if (isset($_POST['facturas_grupal_activo'])) {
        if ($_POST['facturas_grupal_activo'] === "") {
            $tipo_factura = 2;
            $tipo = "facturacionGrupalCredito";
        } else {
            $tipo_factura = (int)$_POST['facturas_grupal_activo'];
            $tipo = "facturacionGrupal";
        }
    } else {
        $tipo_factura = 2;
        $tipo = "facturacionGrupalCredito";
    }

    // 1 = Factura electrónica / contado
    // 4 = Proforma / crédito
    $documento_tipo = ($tipo_factura == 1) ? 1 : 4;

    // =========================================================
    // 4) VALIDAR Y PREPARAR DETALLE GRUPAL
    // =========================================================
    $detallesGrupales = prepararDetallesFacturaGrupal($mysqli, $_POST, $tamano);

    if (count($detallesGrupales) <= 0) {
        throw new Exception("El detalle de la factura grupal no puede quedar vacío.");
    }

    // =========================================================
    // 5) OBTENER NÚMERO DE FACTURA
    // =========================================================
    $lockSecuenciaNombre = adquirirLockSecuenciaFactura($mysqli, $empresa_id, $documento_tipo, 20);
    $lockSecuenciaAdquirido = true;

    $numeroFactura = obtenerNumeroFacturaConRecuperacion($mysqli, $empresa_id, $documento_tipo, $usuario, 'addGrupoFactura');

    if (!isset($numeroFactura['error']) || $numeroFactura['error']) {
        $mensajeNumero = isset($numeroFactura['mensaje']) ? $numeroFactura['mensaje'] : "No se pudo obtener el número de factura.";
        throw new Exception($mensajeNumero);
    }

    $numeroFactura['empresa_id'] = $empresa_id;
    $numeroFactura['documento_id'] = $documento_tipo;
    $numeroFacturaData = $numeroFactura;
    $numeroFacturaObtenido = true;

    $secuencia_facturacion_id = (int)$numeroFactura['data']['secuencia_facturacion_id'];
    $numero  = (int)$numeroFactura['data']['numero'];
    $prefijo = isset($numeroFactura['data']['prefijo']) ? (string)$numeroFactura['data']['prefijo'] : '';
    $relleno = isset($numeroFactura['data']['relleno']) ? (int)$numeroFactura['data']['relleno'] : 0;

    $no_factura = $prefijo . str_pad($numero, $relleno, "0", STR_PAD_LEFT);

    // La columna number en facturas_grupal es char(15).
    // En tu lógica anterior guardabas solo el número; mantenemos eso para no romper reportes.
    $number_str = (string)$numero;

    // =========================================================
    // 6) CALCULAR TOTAL GENERAL DESDE FACTURAS INDIVIDUALES
    // =========================================================
    $total_valor = 0.00;
    $descuentos  = 0.00;
    $isv_neto    = 0.00;

    foreach ($detallesGrupales as $detalle) {
        $total_valor += $detalle['importe'];
        $descuentos  += $detalle['descuento'];
        $isv_neto    += $detalle['isv_valor'];
    }

    $total_despues_isv = round(($total_valor + $isv_neto) - $descuentos, 2);

    if ($total_despues_isv <= 0) {
        throw new Exception("El total de la factura grupal debe ser mayor a cero.");
    }

    // =========================================================
    // 7) INICIAR TRANSACCIÓN
    // OJO: MyISAM no hace rollback real, pero se deja por compatibilidad.
    // La protección real está en validación previa + limpieza manual del catch.
    // =========================================================
    $mysqli->begin_transaction();

    // =========================================================
    // 8) INSERTAR CABECERA GRUPAL
    // =========================================================
    $facturas_grupal_id = correlativo("facturas_grupal_id", "facturas_grupal");

    $insert_grupal = "INSERT INTO facturas_grupal
        (facturas_grupal_id, secuencia_facturacion_id, number, tipo_factura, pacientes_id, colaborador_id, servicio_id, importe, notas, fecha, estado, cierre, usuario, empresa_id, fecha_registro)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $mysqli->prepare($insert_grupal);

    if (!$stmt) {
        throw new Exception("Error al preparar factura grupal: " . $mysqli->error);
    }

    $stmt->bind_param(
        "iisiiiidssiiiis",
        $facturas_grupal_id,
        $secuencia_facturacion_id,
        $number_str,
        $tipo_factura,
        $pacientes_id,
        $colaborador_id,
        $servicio_id,
        $total_despues_isv,
        $notes,
        $fecha,
        $estado,
        $cierre,
        $usuario,
        $empresa_id,
        $fecha_registro
    );

    if (!$stmt->execute()) {
        $errorStmt = $stmt->error;
        $stmt->close();
        throw new Exception("Error al guardar la factura grupal: " . $errorStmt);
    }

    $stmt->close();
    $facturaGrupalInsertada = true;

    // =========================================================
    // 9) OBTENER CORRELATIVO BASE DEL DETALLE GRUPAL UNA SOLA VEZ
    // =========================================================
    $resultMaxDetalle = $mysqli->query("SELECT IFNULL(MAX(facturas_grupal_detalle_id), 0) AS max_id FROM facturas_grupal_detalle");

    if (!$resultMaxDetalle) {
        throw new Exception("Error al obtener correlativo del detalle grupal: " . $mysqli->error);
    }

    $rowMaxDetalle = $resultMaxDetalle->fetch_assoc();
    $siguienteDetalleGrupalID = ((int)$rowMaxDetalle['max_id']) + 1;

    // =========================================================
    // 10) PREPARAR INSERT DE DETALLE GRUPAL
    // =========================================================
    $insert_det = "INSERT INTO facturas_grupal_detalle
        (facturas_grupal_detalle_id, facturas_grupal_id, facturas_id, pacientes_id, muestras_id, cantidad, importe, isv_valor, descuento)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmtDetalle = $mysqli->prepare($insert_det);

    if (!$stmtDetalle) {
        throw new Exception("Error al preparar detalle de factura grupal: " . $mysqli->error);
    }

    // =========================================================
    // 11) PROCESAR DETALLES
    // =========================================================
    foreach ($detallesGrupales as $detalle) {
        $lineaFactura_id   = (int)$detalle['facturas_id'];
        $lineaPacientes_id = (int)$detalle['pacientes_id'];
        $muestra_id        = (int)$detalle['muestras_id'];
        $lineaCantidad     = (int)$detalle['cantidad'];
        $lineaImporte      = (float)$detalle['importe'];
        $lineaISV          = (float)$detalle['isv_valor'];
        $lineaDescuento    = (float)$detalle['descuento'];

        // Guardar estado original de la factura individual para restaurar si falla algo.
        $facturasIndividualesOriginales[$lineaFactura_id] = obtenerFacturaIndividualOriginal($mysqli, $lineaFactura_id);

        $lineaTotal = round(($lineaImporte + $lineaISV) - $lineaDescuento, 2);

        // Actualizar factura individual con la misma secuencia y número.
        // También se sincroniza importe con el total REAL del detalle individual.
        // Esto evita que una factura con descuento quede con encabezado viejo y bloquee la factura grupal.
        $update_fact = "UPDATE facturas
                        SET fecha = ?, number = ?, secuencia_facturacion_id = ?, importe = ?, estado = 2
                        WHERE facturas_id = ?";

        $stmtUpdateFact = $mysqli->prepare($update_fact);

        if (!$stmtUpdateFact) {
            throw new Exception("Error al preparar actualización de factura individual: " . $mysqli->error);
        }

        $stmtUpdateFact->bind_param(
            "siidi",
            $fecha,
            $numero,
            $secuencia_facturacion_id,
            $lineaTotal,
            $lineaFactura_id
        );

        if (!$stmtUpdateFact->execute()) {
            $errorStmt = $stmtUpdateFact->error;
            $stmtUpdateFact->close();
            throw new Exception("Error al actualizar factura individual ID " . $lineaFactura_id . ": " . $errorStmt);
        }

        if ($stmtUpdateFact->affected_rows < 0) {
            $stmtUpdateFact->close();
            throw new Exception("No se pudo actualizar la factura individual ID " . $lineaFactura_id . ".");
        }

        $stmtUpdateFact->close();

        // Insertar detalle grupal
        $facturas_grupal_detalle_id = $siguienteDetalleGrupalID++;

        $stmtDetalle->bind_param(
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

        if (!$stmtDetalle->execute()) {
            $errorStmt = $stmtDetalle->error;
            $stmtDetalle->close();
            throw new Exception("Error al guardar detalle de factura grupal. Factura individual ID " . $lineaFactura_id . ": " . $errorStmt);
        }
    }

    $stmtDetalle->close();

    // =========================================================
    // 12) VALIDACIÓN FINAL DEL DETALLE GRUPAL CONTRA CABECERA
    // =========================================================
    $query_total_grupal = "SELECT 
                                ROUND(IFNULL(SUM(importe + isv_valor - descuento), 0), 2) AS total_detalle,
                                COUNT(*) AS cantidad_lineas
                           FROM facturas_grupal_detalle
                           WHERE facturas_grupal_id = ?";

    $stmtTotal = $mysqli->prepare($query_total_grupal);

    if (!$stmtTotal) {
        throw new Exception("Error al preparar validación final del total grupal: " . $mysqli->error);
    }

    $stmtTotal->bind_param("i", $facturas_grupal_id);
    $stmtTotal->execute();
    $resultTotal = $stmtTotal->get_result();
    $rowTotal = $resultTotal ? $resultTotal->fetch_assoc() : null;
    $stmtTotal->close();

    if (!$rowTotal) {
        throw new Exception("No se pudo validar el total final de la factura grupal.");
    }

    $total_detalle_bd = round((float)$rowTotal['total_detalle'], 2);
    $cantidad_lineas_bd = (int)$rowTotal['cantidad_lineas'];

    if ($cantidad_lineas_bd != count($detallesGrupales)) {
        throw new Exception(
            "La factura grupal no se guardó completa. Facturas enviadas: " . count($detallesGrupales) .
            ", facturas guardadas: " . $cantidad_lineas_bd . "."
        );
    }

    if (abs($total_detalle_bd - $total_despues_isv) > 0.01) {
        throw new Exception(
            "La factura grupal no cuadra. Encabezado: " . number_format($total_despues_isv, 2, '.', '') .
            ", detalle: " . number_format($total_detalle_bd, 2, '.', '') . "."
        );
    }

    // =========================================================
    // 13) ACTUALIZAR IMPORTE GRUPAL
    // =========================================================
    $update_grupal = "UPDATE facturas_grupal
                      SET importe = ?, usuario = ?
                      WHERE facturas_grupal_id = ?";

    $stmtUpdateGrupal = $mysqli->prepare($update_grupal);

    if (!$stmtUpdateGrupal) {
        throw new Exception("Error al preparar actualización de importe grupal: " . $mysqli->error);
    }

    $stmtUpdateGrupal->bind_param(
        "dii",
        $total_despues_isv,
        $usuario,
        $facturas_grupal_id
    );

    if (!$stmtUpdateGrupal->execute()) {
        $errorStmt = $stmtUpdateGrupal->error;
        $stmtUpdateGrupal->close();
        throw new Exception("Error al actualizar importe de factura grupal: " . $errorStmt);
    }

    $stmtUpdateGrupal->close();

    // =========================================================
    // 14) REGISTRAR O ACTUALIZAR CUENTA POR COBRAR GRUPAL
    // =========================================================
    $sel_cxc = "SELECT cobrar_clientes_id 
                FROM cobrar_clientes_grupales 
                WHERE facturas_id = ? 
                LIMIT 1";

    $stmtCxc = $mysqli->prepare($sel_cxc);

    if (!$stmtCxc) {
        throw new Exception("Error al preparar consulta de cuenta por cobrar grupal: " . $mysqli->error);
    }

    $stmtCxc->bind_param("i", $facturas_grupal_id);
    $stmtCxc->execute();
    $resultCxc = $stmtCxc->get_result();
    $stmtCxc->close();

    if ($resultCxc && $resultCxc->num_rows > 0) {
        $rowCxc = $resultCxc->fetch_assoc();
        $cobrar_clientes_id = (int)$rowCxc['cobrar_clientes_id'];

        $update_cxc = "UPDATE cobrar_clientes_grupales 
                       SET saldo = ?, estado = 1, usuario = ?, empresa_id = ?
                       WHERE cobrar_clientes_id = ?";

        $stmtUpdateCxc = $mysqli->prepare($update_cxc);

        if (!$stmtUpdateCxc) {
            throw new Exception("Error al preparar actualización de cuenta por cobrar grupal: " . $mysqli->error);
        }

        $stmtUpdateCxc->bind_param(
            "diii",
            $total_despues_isv,
            $usuario,
            $empresa_id,
            $cobrar_clientes_id
        );

        if (!$stmtUpdateCxc->execute()) {
            $errorStmt = $stmtUpdateCxc->error;
            $stmtUpdateCxc->close();
            throw new Exception("Error al actualizar cuenta por cobrar grupal: " . $errorStmt);
        }

        $stmtUpdateCxc->close();
    } else {
        $cobrar_clientes_id = correlativo("cobrar_clientes_id", "cobrar_clientes_grupales");
        $estado_cxc = 1;

        $insert_cxc = "INSERT INTO cobrar_clientes_grupales
            (cobrar_clientes_id, pacientes_id, facturas_id, fecha, saldo, estado, usuario, empresa_id, fecha_registro)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmtInsertCxc = $mysqli->prepare($insert_cxc);

        if (!$stmtInsertCxc) {
            throw new Exception("Error al preparar cuenta por cobrar grupal: " . $mysqli->error);
        }

        $stmtInsertCxc->bind_param(
            "iiisdiiss",
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

        if (!$stmtInsertCxc->execute()) {
            $errorStmt = $stmtInsertCxc->error;
            $stmtInsertCxc->close();
            throw new Exception("Error al registrar cuenta por cobrar grupal: " . $errorStmt);
        }

        $stmtInsertCxc->close();
        $cxcGrupalInsertada = true;
    }

    // =========================================================
    // 15) CONFIRMAR REUTILIZACIÓN Y COMMIT
    // =========================================================
    confirmarNumeroReutilizado($mysqli, $numeroFacturaData, $usuario, null, $facturas_grupal_id);

    $mysqli->commit();

    if ($lockSecuenciaAdquirido) {
        liberarLockSecuenciaFactura($mysqli, $lockSecuenciaNombre);
        $lockSecuenciaAdquirido = false;
    }

    $datos = array(
        0  => "Almacenado",
        1  => "Registro Almacenado Correctamente",
        2  => "success",
        3  => "btn-primary",
        4  => "formGrupoFacturacion",
        5  => "Registro",
        6  => $tipo,
        7  => "",
        8  => $facturas_grupal_id,
        9  => $numero,
        10 => $no_factura,
        11 => number_format($total_despues_isv, 2, '.', ''),
        12 => count($detallesGrupales)
    );

} catch (Exception $e) {
    // MyISAM no hace rollback real, pero lo dejamos por compatibilidad.
    $mysqli->rollback();

    // Limpieza manual por MyISAM.
    if ($facturas_grupal_id > 0) {
        limpiarFacturaGrupalFallida($mysqli, $facturas_grupal_id, $facturasIndividualesOriginales);
    }

    // Registrar número fallido solo si después de la limpieza el número NO quedó usado.
    if ($numeroFacturaObtenido && $numero > 0 && $empresa_id > 0 && $documento_tipo > 0) {
        registrarNumeroFallidoCompleto($mysqli, $numeroFacturaData, $e->getMessage(), 'addGrupoFactura', null, $facturas_grupal_id, $usuario);
    }

    if ($lockSecuenciaAdquirido) {
        liberarLockSecuenciaFactura($mysqli, $lockSecuenciaNombre);
        $lockSecuenciaAdquirido = false;
    }

    error_log("Error addGrupoFactura.php: " . $e->getMessage());

    $datos = array(
        0 => "Error",
        1 => $e->getMessage(),
        2 => "error",
        3 => "btn-danger",
        4 => "",
        5 => ""
    );
}

echo json_encode($datos);
exit;


/**
 * Prepara y valida las líneas seleccionadas para la factura grupal.
 * No se permite omitir líneas incompletas silenciosamente.
 */
function prepararDetallesFacturaGrupal($conexion, $post, $tamano) {
    if ($tamano <= 0) {
        throw new Exception("El detalle de la factura grupal no puede quedar vacío.");
    }

    if (
        !isset($post['billGrupoID']) ||
        !isset($post['pacienteIDBillGrupo']) ||
        !is_array($post['billGrupoID']) ||
        !is_array($post['pacienteIDBillGrupo'])
    ) {
        throw new Exception("El detalle de la factura grupal no fue recibido correctamente.");
    }

    $billGrupoIDArray = $post['billGrupoID'];
    $pacienteIDArray = $post['pacienteIDBillGrupo'];
    $muestraIDArray = isset($post['billGrupoMuestraID']) && is_array($post['billGrupoMuestraID']) ? $post['billGrupoMuestraID'] : array();
    $cantidadArray = isset($post['quantyGrupoQuantity']) && is_array($post['quantyGrupoQuantity']) ? $post['quantyGrupoQuantity'] : array();

    $countFacturas = count($billGrupoIDArray);
    $countPacientes = count($pacienteIDArray);

    if ($countFacturas <= 0) {
        throw new Exception("El detalle de la factura grupal no puede quedar vacío.");
    }

    if ($countFacturas != $countPacientes) {
        throw new Exception("El detalle grupal llegó incompleto. Por favor, vuelva a seleccionar las facturas.");
    }

    if ($tamano != $countFacturas) {
        throw new Exception("El tamaño del detalle grupal no coincide con las facturas seleccionadas. Por favor, vuelva a generar la factura grupal.");
    }

    $detalles = array();
    $facturasUsadas = array();

    for ($i = 0; $i < $countFacturas; $i++) {
        $linea = $i + 1;

        $facturas_id = isset($billGrupoIDArray[$i]) ? (int)$billGrupoIDArray[$i] : 0;
        $pacientes_id = isset($pacienteIDArray[$i]) ? (int)$pacienteIDArray[$i] : 0;
        $muestras_id = isset($muestraIDArray[$i]) ? (int)$muestraIDArray[$i] : 0;
        $cantidad = isset($cantidadArray[$i]) ? (int)$cantidadArray[$i] : 1;

        if ($cantidad <= 0) {
            $cantidad = 1;
        }

        if ($facturas_id <= 0) {
            throw new Exception("Hay una factura sin código interno en la línea " . $linea . ".");
        }

        if ($pacientes_id <= 0) {
            throw new Exception("Hay un paciente sin código interno en la línea " . $linea . ".");
        }

        if (isset($facturasUsadas[$facturas_id])) {
            throw new Exception("La factura ID " . $facturas_id . " está duplicada en el detalle grupal.");
        }

        $facturasUsadas[$facturas_id] = true;

        // Validar que la factura individual exista.
        $query_factura = "SELECT 
                                facturas_id,
                                pacientes_id,
                                muestras_id,
                                importe,
                                estado
                          FROM facturas
                          WHERE facturas_id = ?
                          LIMIT 1";

        $stmtFactura = $conexion->prepare($query_factura);

        if (!$stmtFactura) {
            throw new Exception("Error al preparar validación de factura individual: " . $conexion->error);
        }

        $stmtFactura->bind_param("i", $facturas_id);
        $stmtFactura->execute();
        $resultFactura = $stmtFactura->get_result();

        if (!$resultFactura || $resultFactura->num_rows <= 0) {
            $stmtFactura->close();
            throw new Exception("La factura individual ID " . $facturas_id . " no existe.");
        }

        $rowFactura = $resultFactura->fetch_assoc();
        $stmtFactura->close();

        // Si no viene muestra en el POST, usamos la de la factura individual.
        if ($muestras_id <= 0 && isset($rowFactura['muestras_id'])) {
            $muestras_id = (int)$rowFactura['muestras_id'];
        }

        // Obtener total real desde facturas_detalle.
        $sql_sum = "SELECT
                        ROUND(COALESCE(SUM(cantidad * precio), 0), 2) AS importe_sum,
                        ROUND(COALESCE(SUM(isv_valor), 0), 2) AS isv_sum,
                        ROUND(COALESCE(SUM(descuento), 0), 2) AS desc_sum,
                        COUNT(*) AS cantidad_lineas
                    FROM facturas_detalle
                    WHERE facturas_id = ?";

        $stmtSum = $conexion->prepare($sql_sum);

        if (!$stmtSum) {
            throw new Exception("Error al preparar total de factura individual: " . $conexion->error);
        }

        $stmtSum->bind_param("i", $facturas_id);
        $stmtSum->execute();
        $resSum = $stmtSum->get_result();
        $rowSum = $resSum ? $resSum->fetch_assoc() : null;
        $stmtSum->close();

        if (!$rowSum) {
            throw new Exception("No se pudo obtener el detalle de la factura individual ID " . $facturas_id . ".");
        }

        $cantidad_lineas = (int)$rowSum['cantidad_lineas'];

        if ($cantidad_lineas <= 0) {
            throw new Exception("La factura individual ID " . $facturas_id . " no tiene detalle registrado.");
        }

        $lineaImporte = round((float)$rowSum['importe_sum'], 2);
        $lineaISV = round((float)$rowSum['isv_sum'], 2);
        $lineaDescuento = round((float)$rowSum['desc_sum'], 2);

        $lineaTotal = round(($lineaImporte + $lineaISV) - $lineaDescuento, 2);

        if ($lineaTotal <= 0) {
            throw new Exception("La factura individual ID " . $facturas_id . " tiene total cero o inválido.");
        }

        // El total confiable siempre sale del detalle.
        // Si el encabezado individual quedó con importe viejo, se corrige al procesar la factura grupal.
        $importeEncabezadoIndividual = round((float)$rowFactura['importe'], 2);

        $detalles[] = array(
            'facturas_id' => $facturas_id,
            'pacientes_id' => $pacientes_id,
            'muestras_id' => $muestras_id,
            'cantidad' => $cantidad,
            'importe' => $lineaImporte,
            'isv_valor' => $lineaISV,
            'descuento' => $lineaDescuento,
            'total' => $lineaTotal
        );
    }

    if (count($detalles) <= 0) {
        throw new Exception("No hay facturas válidas para generar la factura grupal.");
    }

    return $detalles;
}


/**
 * Obtiene los datos originales de una factura individual antes de actualizarla,
 * para restaurarla si algo falla en MyISAM.
 */
function obtenerFacturaIndividualOriginal($conexion, $facturas_id) {
    $query = "SELECT fecha, number, secuencia_facturacion_id, importe, estado
              FROM facturas
              WHERE facturas_id = ?
              LIMIT 1";

    $stmt = $conexion->prepare($query);

    if (!$stmt) {
        throw new Exception("Error al preparar respaldo de factura individual: " . $conexion->error);
    }

    $stmt->bind_param("i", $facturas_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result || $result->num_rows <= 0) {
        $stmt->close();
        throw new Exception("No se pudo respaldar la factura individual ID " . $facturas_id . ".");
    }

    $row = $result->fetch_assoc();
    $stmt->close();

    return array(
        'fecha' => $row['fecha'],
        'number' => $row['number'],
        'secuencia_facturacion_id' => $row['secuencia_facturacion_id'],
        'importe' => $row['importe'],
        'estado' => $row['estado']
    );
}


/**
 * Limpieza manual para MyISAM si el proceso falla.
 */
function limpiarFacturaGrupalFallida($conexion, $facturas_grupal_id, $facturasIndividualesOriginales) {
    try {
        // Eliminar CXC grupal creada.
        $stmt = $conexion->prepare("DELETE FROM cobrar_clientes_grupales WHERE facturas_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $facturas_grupal_id);
            $stmt->execute();
            $stmt->close();
        }

        // Eliminar detalle grupal.
        $stmt = $conexion->prepare("DELETE FROM facturas_grupal_detalle WHERE facturas_grupal_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $facturas_grupal_id);
            $stmt->execute();
            $stmt->close();
        }

        // Eliminar cabecera grupal.
        $stmt = $conexion->prepare("DELETE FROM facturas_grupal WHERE facturas_grupal_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $facturas_grupal_id);
            $stmt->execute();
            $stmt->close();
        }

        // Restaurar facturas individuales modificadas.
        foreach ($facturasIndividualesOriginales as $facturas_id => $data) {
            $fecha_original = $data['fecha'];
            $number_original = (int)$data['number'];
            $secuencia_original = (int)$data['secuencia_facturacion_id'];
            $importe_original = (float)$data['importe'];
            $estado_original = (int)$data['estado'];

            $stmt = $conexion->prepare("UPDATE facturas 
                                        SET fecha = ?, number = ?, secuencia_facturacion_id = ?, importe = ?, estado = ?
                                        WHERE facturas_id = ?");

            if ($stmt) {
                $stmt->bind_param(
                    "siidii",
                    $fecha_original,
                    $number_original,
                    $secuencia_original,
                    $importe_original,
                    $estado_original,
                    $facturas_id
                );
                $stmt->execute();
                $stmt->close();
            }
        }

        return true;
    } catch (Exception $e) {
        error_log("Error limpiando factura grupal fallida: " . $e->getMessage());
        return false;
    }
}


/**
 * Registra un número de factura fallido para su posible reutilización.
 */
/**
 * Bloqueo lógico por empresa/documento.
 * Sirve aunque las tablas principales sean MyISAM.
 * Evita que dos cajeros tomen el mismo número al mismo tiempo.
 */
function adquirirLockSecuenciaFactura($conexion, $empresa_id, $documento_id, $timeout = 20) {
    $lockName = 'facturacion_' . (int)$empresa_id . '_' . (int)$documento_id;

    $stmt = $conexion->prepare("SELECT GET_LOCK(?, ?) AS lock_obtenido");

    if (!$stmt) {
        throw new Exception("Error al preparar bloqueo de secuencia: " . $conexion->error);
    }

    $stmt->bind_param("si", $lockName, $timeout);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;

    $stmt->close();

    if (!$row || (int)$row['lock_obtenido'] !== 1) {
        throw new Exception("No se pudo asegurar la secuencia de facturación. Intente nuevamente en unos segundos.");
    }

    return $lockName;
}

function liberarLockSecuenciaFactura($conexion, $lockName) {
    if ($lockName === '' || $lockName === null) {
        return false;
    }

    $stmt = $conexion->prepare("SELECT RELEASE_LOCK(?) AS lock_liberado");

    if (!$stmt) {
        error_log("Error al preparar liberación de bloqueo: " . $conexion->error);
        return false;
    }

    $stmt->bind_param("s", $lockName);
    $stmt->execute();
    $stmt->close();

    return true;
}

/**
 * Verifica si un número ya está realmente usado.
 * Si está en facturas o facturas_grupal, NO debe estar disponible como fallido.
 */
function numeroFacturaYaUsado($conexion, $secuencia_facturacion_id, $numero) {
    $secuencia_facturacion_id = (int)$secuencia_facturacion_id;
    $numero = (int)$numero;

    if ($secuencia_facturacion_id <= 0 || $numero <= 0) {
        return true;
    }

    $queryFactura = "SELECT facturas_id
                     FROM facturas
                     WHERE secuencia_facturacion_id = ?
                       AND number = ?
                       AND number > 0
                     LIMIT 1";

    $stmt = $conexion->prepare($queryFactura);

    if (!$stmt) {
        throw new Exception("Error al validar número en facturas: " . $conexion->error);
    }

    $stmt->bind_param("ii", $secuencia_facturacion_id, $numero);
    $stmt->execute();
    $res = $stmt->get_result();
    $usado = ($res && $res->num_rows > 0);
    $stmt->close();

    if ($usado) {
        return true;
    }

    $queryFacturaGrupal = "SELECT facturas_grupal_id
                           FROM facturas_grupal
                           WHERE secuencia_facturacion_id = ?
                             AND CAST(number AS UNSIGNED) = ?
                             AND CAST(number AS UNSIGNED) > 0
                           LIMIT 1";

    $stmt = $conexion->prepare($queryFacturaGrupal);

    if (!$stmt) {
        throw new Exception("Error al validar número en facturas grupales: " . $conexion->error);
    }

    $stmt->bind_param("ii", $secuencia_facturacion_id, $numero);
    $stmt->execute();
    $res = $stmt->get_result();
    $usado = ($res && $res->num_rows > 0);
    $stmt->close();

    return $usado;
}

/**
 * Primero intenta recuperar un número fallido disponible.
 * Si no hay, toma el siguiente número normal con obtenerNumeroFactura().
 */
function obtenerNumeroFacturaConRecuperacion($conexion, $empresa_id, $documento_id, $usuario, $origen) {
    $empresa_id = (int)$empresa_id;
    $documento_id = (int)$documento_id;
    $usuario = (int)$usuario;

    for ($intento = 0; $intento < 50; $intento++) {
        $queryFallida = "SELECT
                            secuencia_factura_fallida_id,
                            empresa_id,
                            secuencia_facturacion_id,
                            documento_id,
                            numero,
                            prefijo,
                            relleno
                         FROM secuencia_factura_fallida
                         WHERE empresa_id = ?
                           AND documento_id = ?
                           AND estado = 1
                         ORDER BY numero ASC, secuencia_factura_fallida_id ASC
                         LIMIT 1";

        $stmt = $conexion->prepare($queryFallida);

        if (!$stmt) {
            throw new Exception("Error al preparar búsqueda de números fallidos: " . $conexion->error);
        }

        $stmt->bind_param("ii", $empresa_id, $documento_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        if (!$row) {
            break;
        }

        $fallida_id = (int)$row['secuencia_factura_fallida_id'];
        $secuencia_facturacion_id = (int)$row['secuencia_facturacion_id'];
        $numero = (int)$row['numero'];

        if (numeroFacturaYaUsado($conexion, $secuencia_facturacion_id, $numero)) {
            $motivo = "Número bloqueado automáticamente porque ya existe en facturas o facturas_grupal.";
            $estadoBloqueado = 3;

            $update = "UPDATE secuencia_factura_fallida
                       SET estado = ?,
                           motivo = ?,
                           usuario_reutilizo = ?,
                           fecha_reutilizado = NOW()
                       WHERE secuencia_factura_fallida_id = ?";

            $stmtUpdate = $conexion->prepare($update);

            if (!$stmtUpdate) {
                throw new Exception("Error al bloquear número fallido ya usado: " . $conexion->error);
            }

            $stmtUpdate->bind_param("isii", $estadoBloqueado, $motivo, $usuario, $fallida_id);
            $stmtUpdate->execute();
            $stmtUpdate->close();

            continue;
        }

        return array(
            'error' => false,
            'mensaje' => 'Número recuperado correctamente.',
            'reutilizado' => true,
            'fallida_id' => $fallida_id,
            'data' => array(
                'secuencia_facturacion_id' => $secuencia_facturacion_id,
                'numero' => $numero,
                'prefijo' => (string)$row['prefijo'],
                'relleno' => (int)$row['relleno']
            )
        );
    }

    $numeroFactura = obtenerNumeroFactura($conexion, $empresa_id, $documento_id);

    if (!isset($numeroFactura['error']) || $numeroFactura['error']) {
        return $numeroFactura;
    }

    $numeroFactura['reutilizado'] = false;
    $numeroFactura['fallida_id'] = 0;

    return $numeroFactura;
}

/**
 * Marca un número recuperado como reutilizado solo cuando la factura ya quedó guardada.
 */
function confirmarNumeroReutilizado($conexion, $numeroFacturaData, $usuario, $facturas_id = null, $facturas_grupal_id = null) {
    if (!is_array($numeroFacturaData)) {
        return false;
    }

    if (!isset($numeroFacturaData['reutilizado']) || !$numeroFacturaData['reutilizado']) {
        return true;
    }

    $fallida_id = isset($numeroFacturaData['fallida_id']) ? (int)$numeroFacturaData['fallida_id'] : 0;

    if ($fallida_id <= 0) {
        return true;
    }

    $estado = 2;
    $usuario = (int)$usuario;
    $facturas_id = !is_null($facturas_id) ? (int)$facturas_id : null;
    $facturas_grupal_id = !is_null($facturas_grupal_id) ? (int)$facturas_grupal_id : null;

    $update = "UPDATE secuencia_factura_fallida
               SET estado = ?,
                   usuario_reutilizo = ?,
                   fecha_reutilizado = NOW(),
                   facturas_id = ?,
                   facturas_grupal_id = ?
               WHERE secuencia_factura_fallida_id = ?
                 AND estado = 1";

    $stmt = $conexion->prepare($update);

    if (!$stmt) {
        throw new Exception("Error al confirmar número reutilizado: " . $conexion->error);
    }

    $stmt->bind_param("iiiii", $estado, $usuario, $facturas_id, $facturas_grupal_id, $fallida_id);

    if (!$stmt->execute()) {
        $errorStmt = $stmt->error;
        $stmt->close();
        throw new Exception("Error al marcar número reutilizado: " . $errorStmt);
    }

    $stmt->close();

    return true;
}

/**
 * Registra un número como fallido solo si todavía no existe en facturas/facturas_grupal.
 */
function registrarNumeroFallidoCompleto($conexion, $numeroFacturaData, $motivo, $origen, $facturas_id, $facturas_grupal_id, $usuario) {
    try {
        if (!is_array($numeroFacturaData) || !isset($numeroFacturaData['data'])) {
            return false;
        }

        if (isset($numeroFacturaData['reutilizado']) && $numeroFacturaData['reutilizado']) {
            // Si era un número recuperado y falló antes de usarse, se queda disponible en estado 1.
            return true;
        }

        $empresa_id = isset($numeroFacturaData['empresa_id']) ? (int)$numeroFacturaData['empresa_id'] : 0;
        $documento_id = isset($numeroFacturaData['documento_id']) ? (int)$numeroFacturaData['documento_id'] : 0;
        $secuencia_facturacion_id = isset($numeroFacturaData['data']['secuencia_facturacion_id']) ? (int)$numeroFacturaData['data']['secuencia_facturacion_id'] : 0;
        $numero = isset($numeroFacturaData['data']['numero']) ? (int)$numeroFacturaData['data']['numero'] : 0;
        $prefijo = isset($numeroFacturaData['data']['prefijo']) ? (string)$numeroFacturaData['data']['prefijo'] : '';
        $relleno = isset($numeroFacturaData['data']['relleno']) ? (int)$numeroFacturaData['data']['relleno'] : 8;

        if ($empresa_id <= 0 || $documento_id <= 0 || $secuencia_facturacion_id <= 0 || $numero <= 0) {
            return false;
        }

        if (numeroFacturaYaUsado($conexion, $secuencia_facturacion_id, $numero)) {
            return false;
        }

        $estadoDisponible = 1;

        $queryExiste = "SELECT secuencia_factura_fallida_id
                        FROM secuencia_factura_fallida
                        WHERE empresa_id = ?
                          AND documento_id = ?
                          AND numero = ?
                          AND estado = 1
                        LIMIT 1";

        $stmtExiste = $conexion->prepare($queryExiste);

        if (!$stmtExiste) {
            error_log("Error al preparar verificación de número fallido: " . $conexion->error);
            return false;
        }

        $stmtExiste->bind_param("iii", $empresa_id, $documento_id, $numero);
        $stmtExiste->execute();
        $resExiste = $stmtExiste->get_result();
        $rowExiste = $resExiste ? $resExiste->fetch_assoc() : null;
        $stmtExiste->close();

        $motivo = substr((string)$motivo, 0, 255);
        $origen = substr((string)$origen, 0, 80);
        $facturas_id = !is_null($facturas_id) ? (int)$facturas_id : null;
        $facturas_grupal_id = !is_null($facturas_grupal_id) ? (int)$facturas_grupal_id : null;
        $usuario = (int)$usuario;

        if ($rowExiste) {
            $fallida_id = (int)$rowExiste['secuencia_factura_fallida_id'];

            $update = "UPDATE secuencia_factura_fallida
                       SET motivo = ?,
                           origen = ?,
                           facturas_id = ?,
                           facturas_grupal_id = ?,
                           usuario = ?,
                           fecha_registro = NOW()
                       WHERE secuencia_factura_fallida_id = ?";

            $stmtUpdate = $conexion->prepare($update);

            if (!$stmtUpdate) {
                error_log("Error al preparar actualización de número fallido: " . $conexion->error);
                return false;
            }

            $stmtUpdate->bind_param("ssiiii", $motivo, $origen, $facturas_id, $facturas_grupal_id, $usuario, $fallida_id);
            $stmtUpdate->execute();
            $stmtUpdate->close();

            return true;
        }

        $insert = "INSERT INTO secuencia_factura_fallida
            (empresa_id, secuencia_facturacion_id, documento_id, numero, prefijo, relleno, estado, motivo, origen, facturas_id, facturas_grupal_id, usuario, fecha_registro)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $conexion->prepare($insert);

        if (!$stmt) {
            error_log("Error al preparar número fallido completo: " . $conexion->error);
            return false;
        }

        $stmt->bind_param(
            "iiiisiissiii",
            $empresa_id,
            $secuencia_facturacion_id,
            $documento_id,
            $numero,
            $prefijo,
            $relleno,
            $estadoDisponible,
            $motivo,
            $origen,
            $facturas_id,
            $facturas_grupal_id,
            $usuario
        );

        $stmt->execute();
        $stmt->close();

        return true;
    } catch (Exception $e) {
        error_log("Error al registrar número fallido completo: " . $e->getMessage());
        return false;
    }
}

/**
 * Compatibilidad con llamadas anteriores dentro del proyecto.
 */
function registrarNumeroFallido($conexion, $empresa_id, $documento_id, $numero) {
    try {
        $querySecuencia = "SELECT secuencia_facturacion_id, prefijo, relleno
                           FROM secuencia_facturacion
                           WHERE empresa_id = ?
                             AND documento_id = ?
                             AND activo = 1
                           LIMIT 1";

        $stmt = $conexion->prepare($querySecuencia);

        if (!$stmt) {
            error_log("Error al preparar secuencia para número fallido: " . $conexion->error);
            return false;
        }

        $stmt->bind_param("ii", $empresa_id, $documento_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        if (!$row) {
            return false;
        }

        $numeroFacturaData = array(
            'reutilizado' => false,
            'empresa_id' => (int)$empresa_id,
            'documento_id' => (int)$documento_id,
            'data' => array(
                'secuencia_facturacion_id' => (int)$row['secuencia_facturacion_id'],
                'numero' => (int)$numero,
                'prefijo' => (string)$row['prefijo'],
                'relleno' => (int)$row['relleno']
            )
        );

        return registrarNumeroFallidoCompleto($conexion, $numeroFacturaData, 'Registro de compatibilidad', 'compatibilidad', null, null, 0);
    } catch (Exception $e) {
        error_log("Error al registrar número fallido compatible: " . $e->getMessage());
        return false;
    }
}