<?php
//paginar.php - Facturacion
session_start();
include "../funtions.php";

header('Content-Type: application/json; charset=UTF-8');

// CONEXION A DB
$mysqli = connect_mysqli();
$mysqli->set_charset('utf8mb4');

$colaborador_id        = isset($_SESSION['colaborador_id']) ? (int)$_SESSION['colaborador_id'] : 0;
$paginaActual          = isset($_POST['partida']) ? (int)$_POST['partida'] : 1;
$fechai                = isset($_POST['fechai']) && $_POST['fechai'] !== '' ? $_POST['fechai'] : date('Y-m-01');
$fechaf                = isset($_POST['fechaf']) && $_POST['fechaf'] !== '' ? $_POST['fechaf'] : date('Y-m-d');
$dato                  = isset($_POST['dato']) ? trim($_POST['dato']) : '';
$tipo_paciente_grupo   = isset($_POST['tipo_paciente_grupo']) && $_POST['tipo_paciente_grupo'] !== '' ? (int)$_POST['tipo_paciente_grupo'] : null;
$pacientesIDGrupo      = isset($_POST['pacientesIDGrupo']) && $_POST['pacientesIDGrupo'] !== '' ? (int)$_POST['pacientesIDGrupo'] : null;
$estado                = isset($_POST['estado']) && $_POST['estado'] !== '' ? (int)$_POST['estado'] : 1;

if ($paginaActual <= 0) {
    $paginaActual = 1;
}

$nroLotes = 200;
$offset   = max(0, ($paginaActual - 1) * $nroLotes);

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function bindParams(mysqli_stmt $stmt, string $types, array $params) {
    if ($types === '' || empty($params)) {
        return true;
    }

    $refs = [];

    foreach ($params as $key => $value) {
        $refs[$key] = &$params[$key];
    }

    return $stmt->bind_param($types, ...$refs);
}

// ===============================
// 1) Construir WHERE + binds
// ===============================
$where   = [];
$types   = '';
$params  = [];

// Estado
$where[] = 'f.estado = ?';
$types  .= 'i';
$params[] = $estado;

// Si NO hay cliente seleccionado, aplica rango de fechas.
// Si hay cliente seleccionado, trae todo el historial del cliente sin obligarte a mover fechas.
if (is_null($pacientesIDGrupo)) {
    $where[] = 'f.fecha BETWEEN ? AND ?';
    $types  .= 'ss';
    $params[] = $fechai;
    $params[] = $fechaf;
}

// Si estado es 2 ó 4 filtrar por usuario
if ($estado === 2 || $estado === 4) {
    $where[] = 'f.usuario = ?';
    $types  .= 'i';
    $params[] = $colaborador_id;
}

// Filtro por tipo cliente
if (!is_null($tipo_paciente_grupo)) {
    $where[] = 'p.tipo_paciente_id = ?';
    $types  .= 'i';
    $params[] = $tipo_paciente_grupo;
}

// Filtro por cliente
if (!is_null($pacientesIDGrupo)) {
    $where[] = 'p.pacientes_id = ?';
    $types  .= 'i';
    $params[] = $pacientesIDGrupo;
}

// Búsqueda libre
if ($dato !== '') {
    $like = "%$dato%";

    $where[] = '(
        p.expediente LIKE ?
        OR p.nombre LIKE ?
        OR p.apellido LIKE ?
        OR CONCAT(p.apellido, " ", p.nombre) LIKE ?
        OR CONCAT(p.nombre, " ", p.apellido) LIKE ?
        OR CAST(f.number AS CHAR) LIKE ?
    )';

    $types  .= 'ssssss';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$whereSQL = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// ===============================
// 2) Consulta principal
// ===============================
$sql = "
SELECT
    f.facturas_id,
    DATE_FORMAT(f.fecha, '%d/%m/%Y') AS fecha,
    CONCAT(p.nombre, ' ', p.apellido) AS empresa,
    p.identidad AS identidad,
    CONCAT(c.nombre, ' ', c.apellido) AS profesional,
    f.estado,
    s.nombre AS consultorio,
    sc.prefijo,
    f.number,
    sc.relleno,
    COALESCE(CONCAT(p1.nombre, ' ', p1.apellido), '') AS paciente,
    p1.pacientes_id AS codigoPacienteEmpresa,
    f.muestras_id,
    c.colaborador_id,
    m.number AS muestra,
    COALESCE(fd_sum.total_precio, 0)      AS total_precio,
    COALESCE(fd_sum.total_cantidad, 0)    AS total_cantidad,
    COALESCE(fd_sum.total_descuento, 0)   AS total_descuento,
    COALESCE(fd_sum.total_isv, 0)         AS total_isv,
    COALESCE(fd_sum.neto_antes_isv, 0)    AS neto_antes_isv
FROM facturas AS f
INNER JOIN pacientes AS p
    ON f.pacientes_id = p.pacientes_id
INNER JOIN secuencia_facturacion AS sc
    ON f.secuencia_facturacion_id = sc.secuencia_facturacion_id
INNER JOIN servicios AS s
    ON f.servicio_id = s.servicio_id
INNER JOIN colaboradores AS c
    ON f.colaborador_id = c.colaborador_id
LEFT JOIN muestras_hospitales AS mh
    ON f.muestras_id = mh.muestras_id
LEFT JOIN pacientes AS p1
    ON mh.pacientes_id = p1.pacientes_id
LEFT JOIN muestras AS m
    ON f.muestras_id = m.muestras_id
LEFT JOIN (
    SELECT
        facturas_id,
        SUM(precio) AS total_precio,
        SUM(cantidad) AS total_cantidad,
        SUM(descuento) AS total_descuento,
        SUM(isv_valor) AS total_isv,
        SUM(precio * cantidad) AS neto_antes_isv
    FROM facturas_detalle
    GROUP BY facturas_id
) AS fd_sum
    ON fd_sum.facturas_id = f.facturas_id
{$whereSQL}
ORDER BY f.fecha DESC, f.facturas_id DESC
LIMIT ?, ?
";

$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    echo json_encode([
        '<div style="color:#C7030D; font-weight:700;">Error al preparar consulta: ' . e($mysqli->error) . '</div>',
        ''
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$types_main = $types . 'ii';
$params_main = $params;
$params_main[] = $offset;
$params_main[] = $nroLotes;

bindParams($stmt, $types_main, $params_main);
$stmt->execute();
$result = $stmt->get_result();

// ===============================
// 3) COUNT total
// ===============================
$sqlCount = "
SELECT COUNT(*) AS total
FROM facturas AS f
INNER JOIN pacientes AS p
    ON f.pacientes_id = p.pacientes_id
{$whereSQL}
";

$stmtCount = $mysqli->prepare($sqlCount);

if (!$stmtCount) {
    echo json_encode([
        '<div style="color:#C7030D; font-weight:700;">Error al preparar conteo: ' . e($mysqli->error) . '</div>',
        ''
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

bindParams($stmtCount, $types, $params);
$stmtCount->execute();

$countResult = $stmtCount->get_result();
$total_registros = 0;

if ($countRow = $countResult->fetch_assoc()) {
    $total_registros = (int)$countRow['total'];
}

$stmtCount->close();

$nroPaginas = (int)ceil($total_registros / $nroLotes);

// ===============================
// 4) Paginación
// ===============================
$lista = '';

if ($nroPaginas > 1) {
    if ($paginaActual > 1) {
        $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(1);void(0);">Inicio</a></li>';
        $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(' . ($paginaActual - 1) . ');void(0);">Anterior ' . ($paginaActual - 1) . '</a></li>';
    }

    if ($paginaActual < $nroPaginas) {
        $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(' . ($paginaActual + 1) . ');void(0);">Siguiente ' . ($paginaActual + 1) . ' de ' . $nroPaginas . '</a></li>';
        $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(' . $nroPaginas . ');void(0);">Última</a></li>';
    }
}

// ===============================
// 5) Render tabla
// ===============================
$tabla = '
<style>
    .facturas-table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .facturas-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background: #fff;
        font-size: 14px;
    }

    .facturas-table thead th {
        background: #129aaa;
        color: #fff;
        font-weight: 700;
        padding: 14px 12px;
        vertical-align: middle;
        border: none;
        white-space: nowrap;
    }

    .facturas-table tbody td {
        padding: 14px 12px;
        vertical-align: middle;
        border-top: 1px solid #e8edf2;
        color: #222;
    }

    .facturas-table tbody tr:nth-child(even) {
        background: #f4f6f8;
    }

    .facturas-table tbody tr:hover {
        background: #eef9fb;
    }

    .factura-check {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .factura-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 12px;
        padding: 6px 10px;
        font-weight: 700;
        white-space: nowrap;
        line-height: 1;
    }

    .badge-fecha {
        background: #f7f7f7;
        border: 1px solid #d8dde3;
        color: #333;
    }

    .badge-muestra {
        background: #eef7ff;
        border: 1px solid #b8dcff;
        color: #006992;
    }

    .badge-numero {
        background: #fff7e6;
        border: 1px solid #ffd98a;
        color: #9a6500;
    }

    .badge-identidad {
        background: #f3f7fa;
        border: 1px solid #d9e4ec;
        color: #333;
    }

    .empresa-factura {
        font-weight: 700;
        color: #243447;
        line-height: 1.35;
    }

    .paciente-factura {
        display: block;
        margin-top: 6px;
        font-size: 13px;
        color: #333;
    }

    .paciente-factura b {
        color: #111;
    }

    .profesional-factura {
        font-weight: 600;
        color: #333;
        line-height: 1.35;
    }

    .money-normal {
        font-weight: 700;
        color: #333;
        white-space: nowrap;
    }

    .money-isv {
        font-weight: 700;
        color: #006992;
        white-space: nowrap;
    }

    .money-descuento {
        font-weight: 700;
        color: #CC2936;
        white-space: nowrap;
    }

    .money-neto {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 86px;
        border: 2px solid #A5BF13;
        color: #7f960c;
        border-radius: 12px;
        padding: 6px 10px;
        font-weight: 800;
        white-space: nowrap;
    }

    .badge-estado {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 14px;
        padding: 7px 12px;
        font-weight: 700;
        white-space: nowrap;
        line-height: 1;
    }

    .estado-borrador {
        background: #fff3cd;
        color: #8a6400;
        border: 1px solid #ffe08a;
    }

    .estado-pagada {
        background: #eaf8ee;
        color: #198754;
        border: 1px solid #bfe8cf;
    }

    .estado-credito {
        background: #e9f3ff;
        color: #006992;
        border: 1px solid #b8dcff;
    }

    .estado-cancelada {
        background: #fdecec;
        color: #CC2936;
        border: 1px solid #f7b9bf;
    }

    .btn-accion-factura {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-width: 105px;
        border-radius: 8px;
        padding: 8px 12px;
        color: #fff !important;
        font-weight: 700;
        text-decoration: none !important;
        box-shadow: 0 2px 5px rgba(0,0,0,.12);
        white-space: nowrap;
        line-height: 1;
    }

    .btn-facturar { background: #0d6efd; }
    .btn-facturar:hover { background: #0b5ed7; }

    .btn-eliminar { background: #dc3545; }
    .btn-eliminar:hover { background: #bb2d3b; }

    .btn-imprimir { background: #6c757d; }
    .btn-imprimir:hover { background: #5c636a; }

    .btn-enviar { background: #20c997; }
    .btn-enviar:hover { background: #1aa179; }

    .btn-cobrar { background: #198754; }
    .btn-cobrar:hover { background: #157347; }

    .factura-hidden-data {
        display: none;
    }

    .factura-empty {
        display: inline-flex;
        align-items: center;
        background: #f2f2f2;
        color: #888;
        border-radius: 12px;
        padding: 6px 10px;
        font-weight: 600;
        white-space: nowrap;
    }

    .total-facturas-row {
        background: #129aaa !important;
        color: #fff !important;
    }

    .total-facturas-row td {
        color: #fff !important;
        font-weight: 700;
        padding: 13px !important;
        border-top: none !important;
    }
</style>

<div class="facturas-table-wrapper">
<table class="facturas-table table table-hover">
    <thead>
        <tr>
            <th width="2.66%"><input id="checkAllFactura" class="factura-check formcontrol" type="checkbox"></th>
            <th width="2.66%">No.</th>
            <th width="5.66%">Fecha</th>
            <th width="8.66%">Muestra</th>
            <th width="9.66%">Factura</th>
            <th width="16.66%">Empresa / Paciente</th>
            <th width="8.66%">Identidad</th>
            <th width="10.66%">Profesional</th>
            <th width="6.66%">Importe</th>
            <th width="6.66%">ISV</th>
            <th width="6.66%">Descuento</th>
            <th width="7.66%">Neto</th>
            <th width="7.66%">Estado</th>
            <th width="7.66%">Acción 1</th>
            <th width="7.66%">Acción 2</th>
        </tr>
    </thead>
    <tbody>
';

if ($total_registros > 0) {
    $i = $offset + 1;

    while ($r = $result->fetch_assoc()) {
        $facturas_id = (int)$r['facturas_id'];

        $total = ((float)$r['neto_antes_isv'] + (float)$r['total_isv']) - (float)$r['total_descuento'];

        $numero = ((int)$r['number'] === 0)
            ? "Aún no se ha generado"
            : $r['prefijo'] . rellenarDigitos($r['number'], $r['relleno']);

        $fecha       = e($r['fecha']);
        $muestra     = e($r['muestra'] ?: '');
        $numero_html = e($numero);
        $empresa     = e($r['empresa']);
        $identidad   = e($r['identidad']);
        $profesional = e($r['profesional']);
        $paciente    = e($r['paciente']);

        $muestra_html = ($muestra === '')
            ? '<span class="factura-empty">Sin muestra</span>'
            : '<span class="factura-badge badge-muestra"><i class="fas fa-vial"></i> ' . $muestra . '</span>';

        $numero_badge = ($numero === "Aún no se ha generado")
            ? '<span class="factura-empty">' . $numero_html . '</span>'
            : '<span class="factura-badge badge-numero"><i class="fas fa-file-invoice"></i> ' . $numero_html . '</span>';

        $empresa_html = '<div class="empresa-factura"><i class="fas fa-building text-info"></i> ' . $empresa . '</div>';

        if ($paciente !== '') {
            $empresa_html .= '<span class="paciente-factura"><b>Paciente:</b> ' . $paciente . '</span>';
        }

        if ((int)$r['estado'] === 1) {
            $estadoTxt = '<span class="badge-estado estado-borrador"><i class="fas fa-edit"></i> Borrador</span>';
            $texto1_btn = '<a class="btn-accion-factura btn-facturar" href="javascript:pay(' . $facturas_id . ');void(0);"><i class="fas fa-file-invoice"></i> Facturar</a>';
            $texto2_btn = '<a class="btn-accion-factura btn-eliminar" href="javascript:deleteBill(' . $facturas_id . ');void(0);"><i class="fas fa-trash"></i> Eliminar</a>';
        } else if ((int)$r['estado'] === 2) {
            $estadoTxt = '<span class="badge-estado estado-pagada"><i class="fas fa-check-circle"></i> Pagada</span>';
            $texto1_btn = '<a class="btn-accion-factura btn-enviar" href="javascript:mailBill(' . $facturas_id . ');void(0);"><i class="far fa-paper-plane"></i> Enviar</a>';
            $texto2_btn = '<a class="btn-accion-factura btn-imprimir" href="javascript:printBill(' . $facturas_id . ');void(0);"><i class="fas fa-print"></i> Imprimir</a>';
        } else if ((int)$r['estado'] === 4) {
            $estadoTxt = '<span class="badge-estado estado-credito"><i class="fas fa-credit-card"></i> Crédito</span>';
            $texto1_btn = '<a class="btn-accion-factura btn-imprimir" href="javascript:printBill(' . $facturas_id . ');void(0);"><i class="fas fa-print"></i> Imprimir</a>';
            $texto2_btn = '<a class="btn-accion-factura btn-cobrar" href="javascript:pago(' . $facturas_id . ');void(0);"><i class="fab fa-amazon-pay"></i> Cobrar</a>';
        } else if ((int)$r['estado'] === 3) {
            $estadoTxt = '<span class="badge-estado estado-cancelada"><i class="fas fa-ban"></i> Cancelada</span>';
            $texto1_btn = '<a class="btn-accion-factura btn-imprimir" href="javascript:printBill(' . $facturas_id . ');void(0);"><i class="fas fa-print"></i> Imprimir</a>';
            $texto2_btn = '';
        } else {
            $estadoTxt = '<span class="factura-empty">Sin estado</span>';
            $texto1_btn = '';
            $texto2_btn = '';
        }

        $tabla .= '<tr>';

        $tabla .= '<td>
            <input class="itemRowFactura factura-check" type="checkbox" name="itemFactura" id="itemFactura_' . ($i - 1) . '" value="' . $facturas_id . '">
        </td>';

        $tabla .= '<td><strong>' . $i . '</strong></td>';

        $tabla .= '<td>
            <span class="factura-badge badge-fecha">
                <i class="fas fa-calendar-alt"></i> ' . $fecha . '
            </span>
        </td>';

        $tabla .= '<td>' . $muestra_html . '</td>';
        $tabla .= '<td>' . $numero_badge . '</td>';
        $tabla .= '<td>' . $empresa_html . '</td>';
        $tabla .= '<td><span class="factura-badge badge-identidad"><i class="fas fa-id-card"></i> ' . $identidad . '</span></td>';
        $tabla .= '<td><div class="profesional-factura"><i class="fas fa-user-md text-primary"></i> ' . $profesional . '</div></td>';
        $tabla .= '<td><span class="money-normal">L ' . number_format((float)$r['total_precio'], 2) . '</span></td>';
        $tabla .= '<td><span class="money-isv">L ' . number_format((float)$r['total_isv'], 2) . '</span></td>';
        $tabla .= '<td><span class="money-descuento">L ' . number_format((float)$r['total_descuento'], 2) . '</span></td>';

        $tabla .= '<td>
            <div class="factura-hidden-data" id="quantyGrupoQuantityValor_' . $facturas_id . '" data-value="' . e($r['total_cantidad']) . '"></div>
            <div class="factura-hidden-data" id="profesionalIDGrupo_' . $facturas_id . '" data-value="' . e($r['colaborador_id']) . '"></div>
            <div class="factura-hidden-data" id="muestraGrupo_' . $facturas_id . '" data-value="' . e($r['muestras_id']) . '"></div>
            <div class="factura-hidden-data" id="codigoFacturaGrupo_' . $facturas_id . '" data-value="' . $facturas_id . '"></div>
            <div class="factura-hidden-data" id="pacientesIDFacturaGrupo_' . $facturas_id . '" data-value="' . e($r['codigoPacienteEmpresa']) . '"></div>
            <div class="factura-hidden-data" id="importeFacturaGrupo_' . $facturas_id . '" data-value="' . number_format($total, 2, '.', '') . '"></div>
            <div class="factura-hidden-data" id="precioFacturaGrupo_' . $facturas_id . '" data-value="' . number_format((float)$r['total_precio'], 2, '.', '') . '"></div>
            <div class="factura-hidden-data" id="ISVFacturaGrupo_' . $facturas_id . '" data-value="' . number_format((float)$r['total_isv'], 2, '.', '') . '"></div>
            <div class="factura-hidden-data" id="DescuentoFacturaGrupo_' . $facturas_id . '" data-value="' . number_format((float)$r['total_descuento'], 2, '.', '') . '"></div>
            <div class="factura-hidden-data" id="netoAntesISVFacturaGrupo_' . $facturas_id . '" data-value="' . number_format((float)$r['neto_antes_isv'], 2, '.', '') . '"></div>
            <span class="money-neto">L ' . number_format($total, 2) . '</span>
        </td>';

        $tabla .= '<td>' . $estadoTxt . '</td>';
        $tabla .= '<td>' . $texto1_btn . '</td>';
        $tabla .= '<td>' . $texto2_btn . '</td>';

        $tabla .= '</tr>';

        $i++;
    }

    $tabla .= '
    <tr class="total-facturas-row">
        <td colspan="15" align="center">
            Total de registros encontrados: ' . number_format($total_registros) . '
        </td>
    </tr>';
} else {
    $tabla .= '
    <tr>
        <td colspan="15" style="color:#C7030D; padding:18px; font-weight:700;">
            No se encontraron resultados para los filtros seleccionados
        </td>
    </tr>';
}

$tabla .= '
    </tbody>
</table>
</div>';

echo json_encode([
    0 => $tabla,
    1 => $lista
], JSON_UNESCAPED_UNICODE);

if ($result) {
    $result->free();
}

$stmt->close();
$mysqli->close();