<?php
// paginarMuestras.php
// Admision - Muestras
// Reglas:
// - Por defecto muestra muestras pendientes estado = 0.
// - Sin búsqueda: filtra por fecha desde inicio del año actual.
// - Si estamos en enero: permite consultar desde 4 meses atrás.
// - Si se escribe número de muestra: busca en todo el histórico por número.
// - Si cliente o tipo_muestra vienen en 0, se toman como "sin filtro".
// - Identifica si la muestra no tiene factura, tiene borrador pendiente o factura emitida.
// - Si la factura existe pero number = 0/vacío, se muestra como "Borrador pendiente".
// - Si la factura existe con número real, se muestra como "Factura emitida".

session_start();
include "../funtions.php";

header('Content-Type: application/json; charset=UTF-8');

$mysqli = connect_mysqli();
$mysqli->set_charset("utf8");

$paginaActual = isset($_POST['partida']) ? intval($_POST['partida']) : 1;
if ($paginaActual <= 0) {
    $paginaActual = 1;
}

$estado       = isset($_POST['estado']) ? trim((string)$_POST['estado']) : '0';
$cliente      = isset($_POST['cliente']) ? trim((string)$_POST['cliente']) : '';
$tipo_muestra = isset($_POST['tipo_muestra']) ? trim((string)$_POST['tipo_muestra']) : '';
$fechai       = isset($_POST['fecha_i']) ? trim((string)$_POST['fecha_i']) : '';
$fechaf       = isset($_POST['fecha_f']) ? trim((string)$_POST['fecha_f']) : '';
$dato         = isset($_POST['dato']) ? trim((string)$_POST['dato']) : '';

$nroLotes = 200;
$limit = ($paginaActual <= 1) ? 0 : $nroLotes * ($paginaActual - 1);

// =========================================================
// NORMALIZAR FILTROS
// =========================================================

if ($estado === '') {
    $estado = '0';
}

if ($cliente === '0' || strtolower($cliente) === 'null' || strtolower($cliente) === 'undefined') {
    $cliente = '';
}

if ($tipo_muestra === '0' || strtolower($tipo_muestra) === 'null' || strtolower($tipo_muestra) === 'undefined') {
    $tipo_muestra = '';
}

// =========================================================
// FECHAS
// =========================================================

$hoy = date('Y-m-d');
$mesActual = intval(date('m'));
$fechaMinimaPermitida = date('Y-01-01');

if ($mesActual === 1) {
    $fechaMinimaPermitida = date('Y-m-01', strtotime('-4 months'));
}

function normalizarFechaAdmisionMuestras($fecha) {
    $fecha = trim((string)$fecha);

    if ($fecha === '') {
        return '';
    }

    $formatos = array('Y-m-d', 'd/m/Y', 'm/d/Y');

    foreach ($formatos as $formato) {
        $dt = DateTime::createFromFormat($formato, $fecha);

        if ($dt && $dt->format($formato) === $fecha) {
            return $dt->format('Y-m-d');
        }
    }

    $timestamp = strtotime($fecha);

    if ($timestamp !== false) {
        return date('Y-m-d', $timestamp);
    }

    return '';
}

$fechai = normalizarFechaAdmisionMuestras($fechai);
$fechaf = normalizarFechaAdmisionMuestras($fechaf);

if ($fechai === '') {
    $fechai = $fechaMinimaPermitida;
}

if ($fechaf === '') {
    $fechaf = $hoy;
}

if ($fechai < $fechaMinimaPermitida) {
    $fechai = $fechaMinimaPermitida;
}

if ($fechaf > $hoy) {
    $fechaf = $hoy;
}

if ($fechai > $fechaf) {
    $fechai = $fechaf;
}

$fechaf_exclusive = date('Y-m-d', strtotime($fechaf . ' +1 day'));

// =========================================================
// DETECTAR BÚSQUEDA POR NÚMERO DE MUESTRA
// =========================================================

$buscarNumeroMuestraHistorico = false;

if ($dato !== '' && preg_match('/\d/', $dato)) {
    $buscarNumeroMuestraHistorico = true;
}

$dato_normalizado_numero = '';

if ($dato !== '') {
    $dato_normalizado_numero = preg_replace('/[^A-Za-z0-9]/', '', $dato);
}

// =========================================================
// ARMAR WHERE
// =========================================================

$condiciones = array();
$types = '';
$params = array();

if (!$buscarNumeroMuestraHistorico) {
    $condiciones[] = "m.fecha >= ? AND m.fecha < ?";
    $types .= 'ss';
    $params[] = $fechai;
    $params[] = $fechaf_exclusive;
}

if ($estado !== '') {
    $condiciones[] = "m.estado = ?";
    $types .= 's';
    $params[] = $estado;
}

if ($cliente !== '' && intval($cliente) > 0) {
    $condiciones[] = "m.pacientes_id = ?";
    $types .= 'i';
    $params[] = intval($cliente);
}

if ($tipo_muestra !== '' && intval($tipo_muestra) > 0) {
    $condiciones[] = "m.tipo_muestra_id = ?";
    $types .= 'i';
    $params[] = intval($tipo_muestra);
}

if ($dato !== '') {
    $dato_like = '%' . $dato . '%';
    $dato_sin_espacios = '%' . str_replace(' ', '', $dato) . '%';
    $dato_numero_like = '%' . $dato_normalizado_numero . '%';

    $condiciones[] = "(
        m.number LIKE ?
        OR REPLACE(m.number, ' ', '') LIKE ?
        OR REPLACE(REPLACE(REPLACE(m.number, '-', ''), ' ', ''), '/', '') LIKE ?
        OR p.expediente LIKE ?
        OR p.identidad LIKE ?
        OR p.telefono1 LIKE ?
        OR p.telefono2 LIKE ?
        OR CONCAT(TRIM(p.nombre), ' ', TRIM(p.apellido)) LIKE ?
        OR CONCAT(TRIM(p.apellido), ' ', TRIM(p.nombre)) LIKE ?
        OR p.nombre LIKE ?
        OR p.apellido LIKE ?
        OR pc.identidad LIKE ?
        OR CONCAT(TRIM(pc.nombre), ' ', TRIM(pc.apellido)) LIKE ?
        OR CONCAT(TRIM(pc.apellido), ' ', TRIM(pc.nombre)) LIKE ?
        OR m.diagnostico_clinico LIKE ?
        OR m.material_eviando LIKE ?
        OR m.datos_clinico LIKE ?
    )";

    $types .= 'sssssssssssssssss';

    $params[] = $dato_like;
    $params[] = $dato_sin_espacios;
    $params[] = $dato_numero_like;
    $params[] = $dato_like;
    $params[] = $dato_like;
    $params[] = $dato_like;
    $params[] = $dato_like;
    $params[] = $dato_like;
    $params[] = $dato_like;
    $params[] = $dato_like;
    $params[] = $dato_like;
    $params[] = $dato_like;
    $params[] = $dato_like;
    $params[] = $dato_like;
    $params[] = $dato_like;
    $params[] = $dato_like;
    $params[] = $dato_like;
}

$where = '';

if (count($condiciones) > 0) {
    $where = ' WHERE ' . implode(' AND ', $condiciones);
}

// =========================================================
// FROM
// =========================================================

$from = "
    FROM muestras AS m
    LEFT JOIN pacientes AS p 
        ON m.pacientes_id = p.pacientes_id
    LEFT JOIN muestras_hospitales AS mh
        ON mh.muestras_id = m.muestras_id
    LEFT JOIN pacientes AS pc
        ON pc.pacientes_id = mh.pacientes_id
    LEFT JOIN (
        SELECT
            fx.muestras_id,
            MAX(fx.facturas_id) AS facturas_id,
            MAX(CASE 
                WHEN TRIM(CAST(fx.number AS CHAR)) <> '' 
                     AND TRIM(CAST(fx.number AS CHAR)) <> '0'
                THEN fx.number
                ELSE NULL
            END) AS numero_factura_real,
            MAX(fx.estado) AS estado_factura
        FROM facturas AS fx
        WHERE fx.muestras_id IS NOT NULL
          AND fx.muestras_id > 0
        GROUP BY fx.muestras_id
    ) AS fd
        ON fd.muestras_id = m.muestras_id
";

// =========================================================
// EJECUTOR PREPARADO
// =========================================================

function ejecutar_stmt_admision_muestras($mysqli, $sql, $types = '', $params = array()) {
    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        echo json_encode(array(
            '<div style="color:#C7030D">Error al preparar consulta: ' . htmlspecialchars($mysqli->error, ENT_QUOTES, 'UTF-8') . '</div>',
            ''
        ), JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($types !== '' && count($params) > 0) {
        $bind_names = array();
        $bind_names[] = $types;

        for ($i = 0; $i < count($params); $i++) {
            $bind_names[] = &$params[$i];
        }

        call_user_func_array(array($stmt, 'bind_param'), $bind_names);
    }

    if (!$stmt->execute()) {
        echo json_encode(array(
            '<div style="color:#C7030D">Error al ejecutar consulta: ' . htmlspecialchars($stmt->error, ENT_QUOTES, 'UTF-8') . '</div>',
            ''
        ), JSON_UNESCAPED_UNICODE);
        exit;
    }

    $result = $stmt->get_result();

    if (!$result) {
        echo json_encode(array(
            '<div style="color:#C7030D">Error al obtener resultado: ' . htmlspecialchars($stmt->error, ENT_QUOTES, 'UTF-8') . '</div>',
            ''
        ), JSON_UNESCAPED_UNICODE);
        exit;
    }

    return array($stmt, $result);
}

// =========================================================
// TOTAL DE REGISTROS
// =========================================================

$query_count = "
    SELECT COUNT(DISTINCT m.muestras_id) AS total
    $from
    $where
";

$count_exec = ejecutar_stmt_admision_muestras($mysqli, $query_count, $types, $params);
$stmt_count = $count_exec[0];
$result_count = $count_exec[1];

$row_count = $result_count->fetch_assoc();
$nroProductos = isset($row_count['total']) ? intval($row_count['total']) : 0;

$stmt_count->close();

$nroPaginas = ($nroProductos > 0) ? ceil($nroProductos / $nroLotes) : 0;

$lista = '';
$tabla = '';

if ($paginaActual > 1) {
    $lista .= '<li class="page-item"><a class="page-link" href="javascript:paginationMuestras(1);void(0);">Inicio</a></li>';
    $lista .= '<li class="page-item"><a class="page-link" href="javascript:paginationMuestras(' . ($paginaActual - 1) . ');void(0);">Anterior ' . ($paginaActual - 1) . '</a></li>';
}

if ($paginaActual < $nroPaginas) {
    $lista .= '<li class="page-item"><a class="page-link" href="javascript:paginationMuestras(' . ($paginaActual + 1) . ');void(0);">Siguiente ' . ($paginaActual + 1) . ' de ' . $nroPaginas . '</a></li>';
}

if ($paginaActual > 1 && $nroPaginas > 0) {
    $lista .= '<li class="page-item"><a class="page-link" href="javascript:paginationMuestras(' . $nroPaginas . ');void(0);">Última</a></li>';
}

// =========================================================
// CONSULTA PRINCIPAL
// =========================================================

$query = "
    SELECT
        m.muestras_id AS muestras_id,
        m.pacientes_id AS pacientes_id,
        m.fecha AS fecha,
        m.number AS numero,
        m.diagnostico_clinico AS diagnostico_clinico,
        m.material_eviando AS material_eviando,
        m.datos_clinico AS datos_clinico,
        m.mostrar_datos_clinicos AS mostrar_datos_clinicos,
        m.estado AS estado,
        CASE 
            WHEN m.estado = '1' THEN 'Procesada'
            WHEN m.estado = '2' THEN 'Anulada'
            WHEN m.estado = '0' THEN 'Pendiente'
            ELSE 'Pendiente'
        END AS estatus,
        TRIM(CONCAT(IFNULL(p.nombre, ''), ' ', IFNULL(p.apellido, ''))) AS paciente,
        MIN(pc.pacientes_id) AS pacientes_id_cliente_codigo,
        MIN(TRIM(CONCAT(IFNULL(pc.nombre, ''), ' ', IFNULL(pc.apellido, '')))) AS pacientes_id_cliente,
        MAX(fd.facturas_id) AS factura_id_relacionada,
        MAX(fd.numero_factura_real) AS numero_factura_real,
        MAX(fd.estado_factura) AS estado_factura
    $from
    $where
    GROUP BY 
        m.muestras_id,
        m.pacientes_id,
        m.fecha,
        m.number,
        m.diagnostico_clinico,
        m.material_eviando,
        m.datos_clinico,
        m.mostrar_datos_clinicos,
        m.estado,
        p.nombre,
        p.apellido
    ORDER BY m.fecha DESC, m.muestras_id DESC
    LIMIT ?, ?
";

$types_main = $types . 'ii';
$params_main = $params;
$params_main[] = $limit;
$params_main[] = $nroLotes;

$main_exec = ejecutar_stmt_admision_muestras($mysqli, $query, $types_main, $params_main);
$stmt = $main_exec[0];
$result = $main_exec[1];

// =========================================================
// HTML TABLA
// =========================================================

$tabla .= '
<style>
    .muestras-table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .muestras-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background: #fff;
        font-size: 14px;
    }

    .muestras-table thead th {
        background: #129aaa;
        color: #fff;
        font-weight: 700;
        padding: 14px 12px;
        vertical-align: middle;
        border: none;
        white-space: nowrap;
    }

    .muestras-table tbody td {
        padding: 14px 12px;
        vertical-align: middle;
        border-top: 1px solid #e8edf2;
        color: #222;
    }

    .muestras-table tbody tr:nth-child(even) {
        background: #f4f6f8;
    }

    .muestras-table tbody tr:hover {
        background: #eef9fb;
    }

    .muestra-numero-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #eef7ff;
        border: 1px solid #b8dcff;
        color: #006992;
        border-radius: 12px;
        padding: 6px 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .muestra-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 12px;
        padding: 6px 10px;
        font-weight: 800;
        white-space: nowrap;
        margin-top: 6px;
        font-size: 12px;
    }

    .muestra-status-borrador {
        background: #fff7e6;
        border: 1px solid #ffd28a;
        color: #b35c00;
    }

    .muestra-status-emitida {
        background: #eaf8ef;
        border: 1px solid #bfe8cf;
        color: #198754;
    }

    .muestra-status-sin-factura {
        background: #eef7ff;
        border: 1px solid #b8dcff;
        color: #006992;
    }

    .muestra-factura-numero {
        display: block;
        margin-top: 4px;
        font-size: 12px;
        font-weight: 700;
    }

    .muestra-factura-numero.emitida {
        color: #198754;
    }

    .muestra-factura-numero.borrador {
        color: #b35c00;
    }

    .muestra-fecha-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f7f7f7;
        border: 1px solid #d8dde3;
        color: #333;
        border-radius: 12px;
        padding: 6px 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .muestra-paciente-main {
        font-weight: 700;
        color: #006992;
        text-decoration: none;
        line-height: 1.35;
    }

    .muestra-paciente-main:hover {
        color: #004f73;
        text-decoration: none;
    }

    .muestra-paciente-sub {
        display: block;
        margin-top: 6px;
        font-size: 13px;
        color: #333;
    }

    .muestra-paciente-sub b {
        color: #111;
    }

    .muestra-paciente-sub a {
        color: #0d6efd;
        font-weight: 600;
        text-decoration: none;
    }

    .muestra-texto {
        max-width: 300px;
        white-space: normal;
        line-height: 1.35;
        color: #333;
    }

    .muestra-empty {
        display: inline-flex;
        align-items: center;
        background: #f2f2f2;
        color: #888;
        border-radius: 12px;
        padding: 5px 9px;
        font-weight: 600;
        white-space: nowrap;
    }

    .btn-acciones-muestras {
        background: #0d6efd;
        border-color: #0d6efd;
        color: #fff !important;
        font-weight: 600;
        border-radius: 7px;
        padding: 7px 12px;
        box-shadow: 0 2px 5px rgba(13, 110, 253, .20);
        white-space: nowrap;
    }

    .btn-acciones-muestras:hover {
        background: #0b5ed7;
        border-color: #0b5ed7;
        color: #fff !important;
    }

    .dropdown-menu-muestras {
        border: 0;
        border-radius: 10px;
        box-shadow: 0 10px 25px rgba(0,0,0,.12);
        overflow: hidden;
        min-width: 210px;
    }

    .dropdown-menu-muestras .dropdown-item {
        padding: 10px 14px;
        font-weight: 600;
        color: #333;
    }

    .dropdown-menu-muestras .dropdown-item:hover {
        background: #f2f7ff;
    }

    .dropdown-item-muted {
        color: #6c757d !important;
        cursor: default;
    }

    .total-muestras-row {
        background: #129aaa !important;
        color: #fff !important;
    }

    .total-muestras-row td {
        color: #fff !important;
        font-weight: 700;
        padding: 13px !important;
        border-top: none !important;
    }
</style>

<div class="muestras-table-wrapper">
<table class="muestras-table table table-hover">
    <thead>
        <tr>
            <th width="4%">No.</th>
            <th width="10%">Fecha</th>
            <th width="13%">Número</th>
            <th width="25%">Paciente / Empresa</th>
            <th width="16%">Diagnóstico Clínico</th>
            <th width="14%">Material Enviado</th>
            <th width="12%">Datos Clínicos</th>
            <th width="6%">Acciones</th>
        </tr>
    </thead>
    <tbody>
';

$i = $limit + 1;

if ($result->num_rows > 0) {
    while ($registro2 = $result->fetch_assoc()) {
        $muestras_id = intval($registro2['muestras_id']);
        $pacientes_id = intval($registro2['pacientes_id']);

        $factura_id_relacionada = isset($registro2['factura_id_relacionada']) ? intval($registro2['factura_id_relacionada']) : 0;
        $numero_factura_real_raw = isset($registro2['numero_factura_real']) ? trim((string)$registro2['numero_factura_real']) : '';
        $estado_factura = isset($registro2['estado_factura']) ? trim((string)$registro2['estado_factura']) : '';

        $paciente_raw = trim((string)$registro2['paciente']);
        $numero_raw = trim((string)$registro2['numero']);

        $paciente = htmlspecialchars($paciente_raw, ENT_QUOTES, 'UTF-8');
        $fecha = htmlspecialchars($registro2['fecha'], ENT_QUOTES, 'UTF-8');
        $numero = htmlspecialchars($numero_raw, ENT_QUOTES, 'UTF-8');
        $diagnostico_clinico = htmlspecialchars($registro2['diagnostico_clinico'], ENT_QUOTES, 'UTF-8');
        $material_eviando = htmlspecialchars($registro2['material_eviando'], ENT_QUOTES, 'UTF-8');
        $datos_clinico = htmlspecialchars($registro2['datos_clinico'], ENT_QUOTES, 'UTF-8');
        $numero_factura_real = htmlspecialchars($numero_factura_real_raw, ENT_QUOTES, 'UTF-8');

        $pacientes_id_cliente_codigo = isset($registro2['pacientes_id_cliente_codigo']) ? intval($registro2['pacientes_id_cliente_codigo']) : 0;
        $pacientes_id_cliente = isset($registro2['pacientes_id_cliente']) ? trim($registro2['pacientes_id_cliente']) : '';

        if ($paciente === '') {
            $paciente = 'Sin nombre';
        }

        if ($numero === '') {
            $numero = 'Sin número';
        }

        $numero_html = '
            <span class="muestra-numero-badge">
                <i class="fas fa-vial"></i> ' . $numero . '
            </span>
        ';

        if ($factura_id_relacionada > 0 && $numero_factura_real_raw !== '' && $numero_factura_real_raw !== '0') {
            $numero_html .= '
                <span class="muestra-status-badge muestra-status-emitida">
                    <i class="fas fa-check-circle"></i> Factura emitida
                </span>
                <span class="muestra-factura-numero emitida">
                    Factura: ' . $numero_factura_real . '
                </span>
            ';
        } else if ($factura_id_relacionada > 0) {
            $numero_html .= '
                <span class="muestra-status-badge muestra-status-borrador">
                    <i class="fas fa-clock"></i> Borrador pendiente
                </span>
                <span class="muestra-factura-numero borrador">
                    Pendiente de completar en Facturación
                </span>
            ';
        } else {
            $numero_html .= '
                <span class="muestra-status-badge muestra-status-sin-factura">
                    <i class="fas fa-file-invoice"></i> Sin factura
                </span>
            ';
        }

        if ($pacientes_id_cliente === '') {
            $empresa = '
                <a class="muestra-paciente-main" href="javascript:showModalhistoriaMuestrasEmpresas(' . $pacientes_id . ');void(0);">
                    <i class="fas fa-user"></i> ' . $paciente . '
                </a>
            ';
        } else {
            $paciente_cliente = htmlspecialchars($pacientes_id_cliente, ENT_QUOTES, 'UTF-8');

            $empresa = '
                <a class="muestra-paciente-main" href="javascript:showModalhistoriaMuestrasEmpresas(' . $pacientes_id . ');void(0);">
                    <i class="fas fa-building"></i> ' . $paciente . '
                </a>
                <span class="muestra-paciente-sub">
                    <b>Paciente:</b>
                    <a href="javascript:showModalhistoriaMuestrasEmpresas(' . $pacientes_id_cliente_codigo . ');void(0);">
                        (' . $paciente_cliente . ')
                    </a>
                </span>
            ';
        }

        $diagnostico_html = ($diagnostico_clinico !== '')
            ? '<div class="muestra-texto">' . $diagnostico_clinico . '</div>'
            : '<span class="muestra-empty">Sin diagnóstico</span>';

        $material_html = ($material_eviando !== '')
            ? '<div class="muestra-texto">' . $material_eviando . '</div>'
            : '<span class="muestra-empty">Sin material</span>';

        $datos_html = ($datos_clinico !== '')
            ? '<div class="muestra-texto">' . $datos_clinico . '</div>'
            : '<span class="muestra-empty">Sin datos</span>';

        if ($factura_id_relacionada > 0 && $numero_factura_real_raw !== '' && $numero_factura_real_raw !== '0') {
            $acciones_html = '
                <a class="dropdown-item" href="javascript:printBill(' . $factura_id_relacionada . ');void(0);">
                    <i class="fas fa-file-invoice-dollar text-success mr-2"></i> Ver factura
                </a>
                <a class="dropdown-item dropdown-item-muted" href="javascript:void(0);">
                    <i class="fas fa-check-circle text-success mr-2"></i> Completada
                </a>
            ';
        } else if ($factura_id_relacionada > 0) {
            $acciones_html = '
                <a class="dropdown-item" href="javascript:pago(' . $factura_id_relacionada . ');void(0);">
                    <i class="fas fa-cash-register text-warning mr-2"></i> Completar pago
                </a>
                <a class="dropdown-item dropdown-item-muted" href="javascript:void(0);">
                    <i class="fas fa-clock text-warning mr-2"></i> Borrador pendiente
                </a>
            ';
        } else {
            $acciones_html = '
                <a class="dropdown-item" href="javascript:modalCreateBill(' . $muestras_id . ');void(0);">
                    <i class="fas fa-file-invoice text-primary mr-2"></i> Facturar
                </a>
            ';
        }

        $tabla .= '
        <tr>
            <td><strong>' . $i . '</strong></td>
            <td>
                <span class="muestra-fecha-badge">
                    <i class="fas fa-calendar-alt"></i> ' . $fecha . '
                </span>
            </td>
            <td>' . $numero_html . '</td>
            <td>' . $empresa . '</td>
            <td>' . $diagnostico_html . '</td>
            <td>' . $material_html . '</td>
            <td>' . $datos_html . '</td>
            <td>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-acciones-muestras dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-cog"></i> Acciones
                    </button>
                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-muestras">
                        ' . $acciones_html . '
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" href="javascript:modalAnularMuestras(' . $pacientes_id . ',' . $muestras_id . ');void(0);">
                            <i class="fas fa-ban mr-2"></i> Anular
                        </a>
                    </div>
                </div>
            </td>
        </tr>';

        $i++;
    }

    $tabla .= '
    <tr class="total-muestras-row">
        <td colspan="8" align="center">
            Total de registros encontrados: ' . number_format($nroProductos) . '
        </td>
    </tr>';
} else {
    $tabla .= '
    <tr>
        <td colspan="8" style="color:#C7030D; padding:18px; font-weight:700;">
            No se encontraron resultados
        </td>
    </tr>';
}

$tabla .= '
    </tbody>
</table>
</div>';

$stmt->close();
$mysqli->close();

echo json_encode(array($tabla, $lista), JSON_UNESCAPED_UNICODE);