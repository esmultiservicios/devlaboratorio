<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
include "../funtions.php";

// ======================
// Helpers de respuesta
// ======================
function respond($ok, $code, $title, $message, $extra = []) {
    $payload = array_merge([
        "status"  => (bool)$ok,   // true|false
        "code"    => (string)$code,     // OK, INVALID_INPUT, NOT_FOUND, DB_ERROR, NO_SESSION, PERIODO_CERRADO
        "title"   => (string)$title,    // Success, Error, Warning
        "message" => (string)$message
    ], $extra);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

// ======================
// Conexión
// ======================
$mysqli = connect_mysqli();
if ($mysqli->connect_errno) {
    respond(false, "DB_CONN_ERROR", "Error", "No se pudo conectar a la base de datos.");
}

// ======================
// Entradas
// ======================
$facturas_id    = isset($_POST['facturas_id']) ? intval($_POST['facturas_id']) : 0;
$comentario     = isset($_POST['comentario']) ? cleanStringStrtolower($_POST['comentario']) : '';
$fecha_registro = date("Y-m-d H:i:s");
$hoyYmd         = date("Y-m-d");
$usuario        = isset($_SESSION['colaborador_id']) ? intval($_SESSION['colaborador_id']) : 0;

// Estados
$ESTADO_FACTURA_CANCELADA = 3;    // 1=Borrador 2=Pagado 3=Cancelado 4=Crédito
$ESTADO_PAGO_CANCELADO    = 2;    // 1=Borrador 2=Cancelado
$ESTADO_ATENCION_PEND     = 1;    // 1=Pendiente 2=Pagada
$ESTADO_MUESTRA_PEND      = '0';  // 0=pendiente según tu app

// ======================
// Validaciones básicas
// ======================
if ($usuario <= 0) {
    respond(false, "NO_SESSION", "Error", "Sesión no válida. Inicie sesión nuevamente.");
}
if ($facturas_id <= 0) {
    respond(false, "INVALID_INPUT", "Error", "Parámetros inválidos: faltan datos de factura.");
}

// ======================
// 1) Buscar datos base de la factura pedida por ID
// ======================
$prefijo            = '';
$relleno            = 0;
$numero_correlativo = 0;
$pacientes_id       = 0;
$expediente         = 0;
$colaborador_id     = 0;
$servicio_id        = 0;
$fecha_factura      = '';
$numero_factura     = '';
$empresa_id         = 0; // por si luego usas periodo declarado por empresa

$query_factura = "
    SELECT 
        sf.prefijo AS prefijo,
        f.number   AS numero,
        sf.relleno AS relleno,
        f.pacientes_id,
        p.expediente,
        f.colaborador_id,
        f.servicio_id,
        f.fecha AS fecha_factura,
        f.empresa_id
    FROM facturas AS f
    INNER JOIN secuencia_facturacion AS sf
        ON f.secuencia_facturacion_id = sf.secuencia_facturacion_id
    INNER JOIN pacientes AS p
        ON f.pacientes_id = p.pacientes_id
    WHERE f.facturas_id = '$facturas_id'
    LIMIT 1
";
$resF = $mysqli->query($query_factura);
if ($resF && $resF->num_rows > 0) {
    $row = $resF->fetch_assoc();
    $prefijo            = $row['prefijo'];
    $relleno            = intval($row['relleno']);
    $numero_correlativo = intval($row['numero']);
    $pacientes_id       = intval($row['pacientes_id']);
    $expediente         = intval($row['expediente']);
    $colaborador_id     = intval($row['colaborador_id']);
    $servicio_id        = intval($row['servicio_id']);
    $fecha_factura      = $row['fecha_factura'];
    $empresa_id         = intval($row['empresa_id']);
    $numero_factura     = $prefijo . rellenarDigitos($numero_correlativo, $relleno);
    $resF->free();
} else {
    // Fallback: ¿el ID corresponde a facturas_grupal?
    $query_grupal = "
        SELECT 
            fg.number,
            fg.pacientes_id,
            fg.colaborador_id,
            fg.servicio_id,
            fg.fecha,
            sf.prefijo,
            sf.relleno,
            p.expediente,
            fg.empresa_id
        FROM facturas_grupal AS fg
        INNER JOIN secuencia_facturacion AS sf
            ON fg.secuencia_facturacion_id = sf.secuencia_facturacion_id
        INNER JOIN pacientes AS p
            ON fg.pacientes_id = p.pacientes_id
        WHERE fg.facturas_grupal_id = '$facturas_id'
        LIMIT 1
    ";
    $resG = $mysqli->query($query_grupal);
    if ($resG && $resG->num_rows > 0) {
        $row = $resG->fetch_assoc();
        $prefijo            = $row['prefijo'];
        $relleno            = intval($row['relleno']);
        $numero_correlativo = intval($row['number']);
        $pacientes_id       = intval($row['pacientes_id']);
        $expediente         = intval($row['expediente']);
        $colaborador_id     = intval($row['colaborador_id']);
        $servicio_id        = intval($row['servicio_id']);
        $fecha_factura      = $row['fecha'];
        $empresa_id         = intval($row['empresa_id']);
        $numero_factura     = $prefijo . rellenarDigitos($numero_correlativo, $relleno);
        $resG->free();
    } else {
        respond(false, "NOT_FOUND", "Error", "La factura no existe.");
    }
}

// ======================
// 2) Regla SAR: validar si se permite anulación por fecha límite dinámica
//    - Límite = día 10 del mes siguiente; si cae en fin de semana, mover a lunes.
//    - Si hoy > límite => bloquear (usar Nota de Crédito).
//    - (Si más adelante manejas 'período declarado', pásalo como true al helper.)
// ======================
$eval = anulacionPermitidaSegunSAR($fecha_factura, $hoyYmd /* , $periodoDeclarado = false */);
if (!$eval['permitida']) {
    respond(false, "PERIODO_CERRADO", "Warning",
        "El período de la factura está cerrado (límite: {$eval['fecha_limite']}). Para corregir, emita una Nota de Crédito que refiera la factura.",
        [
            "data" => [
                "fecha_factura" => $fecha_factura,
                "hoy"           => $eval['hoy'],
                "fecha_limite"  => $eval['fecha_limite'],
                "periodo_cerrado" => $eval['periodo_cerrado']
            ]
        ]
    );
}

// ======================
// 3) Anular pagos (solo por esta factura_id indicada)
// ======================
$upd_pagos = "UPDATE pagos SET estado = '$ESTADO_PAGO_CANCELADO' WHERE facturas_id = '$facturas_id'";
if (!$mysqli->query($upd_pagos)) {
    respond(false, "DB_ERROR", "Error", "No se pudieron anular los pagos: ".$mysqli->error);
}
$pagos_anulados = $mysqli->affected_rows;

// ======================
// 4) Cancelar todas las facturas y facturas_grupal con el MISMO number
//    (sin filtrar por secuencia_facturacion_id, tal como definiste)
// ======================
$upd_fact_all = "
    UPDATE facturas
    SET estado = '$ESTADO_FACTURA_CANCELADA'
    WHERE number = '$numero_correlativo'
";
if (!$mysqli->query($upd_fact_all)) {
    respond(false, "DB_ERROR", "Error", "No se pudo actualizar el estado en facturas: ".$mysqli->error);
}
$facturas_actualizadas_all = $mysqli->affected_rows;

$upd_grupal_all = "
    UPDATE facturas_grupal
    SET estado = '$ESTADO_FACTURA_CANCELADA'
    WHERE number = '".$mysqli->real_escape_string((string)$numero_correlativo)."'
";
if (!$mysqli->query($upd_grupal_all)) {
    respond(false, "DB_ERROR", "Error", "No se pudo actualizar el estado en facturas_grupal: ".$mysqli->error);
}
$grupal_actualizadas_all = $mysqli->affected_rows;

// ======================
// 5) Atención -> pendiente (solo la ligada a los datos de la factura base)
// ======================
if ($pacientes_id > 0 && $servicio_id > 0 && $colaborador_id > 0 && $fecha_factura != '') {
    $q_atencion = "
        SELECT atencion_id
        FROM atenciones_medicas
        WHERE pacientes_id   = '$pacientes_id'
          AND servicio_id    = '$servicio_id'
          AND colaborador_id = '$colaborador_id'
          AND fecha          = '$fecha_factura'
        LIMIT 1
    ";
    $resA = $mysqli->query($q_atencion);
    if ($resA && $resA->num_rows > 0) {
        $rowA = $resA->fetch_assoc();
        $atencion_id = intval($rowA['atencion_id']);
        $resA->free();

        $upd_atencion = "UPDATE atenciones_medicas SET estado = '$ESTADO_ATENCION_PEND' WHERE atencion_id = '$atencion_id'";
        if (!$mysqli->query($upd_atencion)) {
            respond(false, "DB_ERROR", "Error", "No se pudo actualizar la atención: ".$mysqli->error);
        }
    } elseif ($resA instanceof mysqli_result) {
        $resA->free();
    }
}

// ======================
// 6) Muestras -> 0 (pendiente) para TODAS las filas con ese number
// ======================
$muestras_list = [];
$muestras_actualizadas = 0;

$q_muestras = "
    SELECT DISTINCT muestras_id
    FROM facturas
    WHERE number = '$numero_correlativo'
      AND muestras_id > 0
";
$resM = $mysqli->query($q_muestras);
if ($resM) {
    while ($r = $resM->fetch_assoc()) {
        $mid = intval($r['muestras_id']);
        if ($mid > 0) $muestras_list[] = $mid;
    }
    $resM->free();
} else {
    respond(false, "DB_ERROR", "Error", "No se pudieron consultar las muestras: ".$mysqli->error);
}

if (!empty($muestras_list)) {
    $ids_in = implode(',', array_map('intval', $muestras_list));
    $upd_muestras_all = "UPDATE muestras SET estado = '$ESTADO_MUESTRA_PEND' WHERE muestras_id IN ($ids_in)";
    if (!$mysqli->query($upd_muestras_all)) {
        respond(false, "DB_ERROR", "Error", "No se pudieron actualizar las muestras: ".$mysqli->error);
    }
    $muestras_actualizadas = $mysqli->affected_rows;
}

// ======================
// 7) Historial
// ======================
$NombreColaborador = '';
$q_col = "
    SELECT CONCAT(nombre, ' ', apellido) AS colaborador
    FROM colaboradores
    WHERE colaborador_id = '$usuario'
    LIMIT 1
";
$resCol = $mysqli->query($q_col);
if ($resCol && $resCol->num_rows > 0) {
    $rC = $resCol->fetch_assoc();
    $NombreColaborador = $rC['colaborador'];
    $resCol->free();
}

$historial_numero      = historial();
$estado_historial      = "Agregar";
$observacion_historial = "El número de factura $numero_factura ha sido anulada correctamente, por el usuario: $NombreColaborador, según comentario: $comentario";
$modulo                = "Facturas";

$pacientes_id_h = ($pacientes_id > 0) ? $pacientes_id : 0;
$expediente_h   = ($expediente > 0)   ? $expediente   : 0;
$colaborador_h  = ($colaborador_id>0) ? $colaborador_id:0;
$servicio_h     = ($servicio_id > 0)  ? $servicio_id  : 0;
$fecha_h        = ($fecha_factura!='')? $fecha_factura: $hoyYmd;

$insert_historial = "
    INSERT INTO historial
    VALUES (
        '$historial_numero',
        '$pacientes_id_h',
        '$expediente_h',
        '$modulo',
        '$facturas_id',
        '$colaborador_h',
        '$servicio_h',
        '$fecha_h',
        '$estado_historial',
        '$observacion_historial',
        '$usuario',
        '$fecha_registro'
    )
";
if (!$mysqli->query($insert_historial)) {
    respond(false, "DB_ERROR", "Error", "No se pudo registrar el historial: ".$mysqli->error);
}

// ======================
// 8) OK
// ======================
respond(true, "OK", "Success", "Registro anulado correctamente.", [
    "data" => [
        "facturas_id"                => $facturas_id,
        "numero_correlativo"         => $numero_correlativo,
        "numero_factura"             => $numero_factura,
        "fecha_factura"              => $fecha_factura,
        "fecha_limite_sar"           => $eval['fecha_limite'],
        "pagos_anulados"             => $pagos_anulados,
        "facturas_actualizadas_all"  => $facturas_actualizadas_all,
        "grupal_actualizadas_all"    => $grupal_actualizadas_all,
        "muestras_actualizadas"      => $muestras_actualizadas,
        "muestras_list"              => $muestras_list
    ]
]);

// (no llega aquí por respond())
$mysqli->close();