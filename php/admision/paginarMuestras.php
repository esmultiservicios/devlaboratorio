<?php
// paginarMuestras.php
session_start();
include "../funtions.php";

header('Content-Type: application/json; charset=UTF-8');

$mysqli = connect_mysqli();
$mysqli->set_charset("utf8");

$colaborador_id = isset($_SESSION['colaborador_id']) ? $_SESSION['colaborador_id'] : '';

$paginaActual = isset($_POST['partida']) ? intval($_POST['partida']) : 1;
if ($paginaActual <= 0) {
    $paginaActual = 1;
}

$estado       = isset($_POST['estado']) ? trim($_POST['estado']) : '';
$cliente      = isset($_POST['cliente']) ? trim($_POST['cliente']) : '';
$tipo_muestra = isset($_POST['tipo_muestra']) ? trim($_POST['tipo_muestra']) : '';
$fechai       = isset($_POST['fecha_i']) ? trim($_POST['fecha_i']) : '';
$fechaf       = isset($_POST['fecha_f']) ? trim($_POST['fecha_f']) : '';
$dato         = isset($_POST['dato']) ? trim($_POST['dato']) : '';

$nroLotes = 200;
$limit = ($paginaActual <= 1) ? 0 : $nroLotes * ($paginaActual - 1);

$condiciones = array();
$types = '';
$params = array();

/*
    BÚSQUEDA INTELIGENTE:
    - Si NO se escribe nada en Buscar, se filtra por fecha.
    - Si SÍ se escribe algo en Buscar, NO se obliga la fecha.
*/
if ($dato === '') {
    if ($fechai !== '' && $fechaf !== '') {
        $condiciones[] = "m.fecha BETWEEN ? AND ?";
        $types .= 'ss';
        $params[] = $fechai;
        $params[] = $fechaf;
    }
}

if ($estado !== '') {
    $condiciones[] = "m.estado = ?";
    $types .= 's';
    $params[] = $estado;
}

if ($cliente !== '') {
    $condiciones[] = "m.pacientes_id = ?";
    $types .= 'i';
    $params[] = intval($cliente);
}

if ($tipo_muestra !== '') {
    $condiciones[] = "m.tipo_muestra_id = ?";
    $types .= 'i';
    $params[] = intval($tipo_muestra);
}

if ($dato !== '') {
    $dato_like = '%' . $dato . '%';
    $dato_inicio = $dato . '%';
    $dato_sin_espacios = '%' . str_replace(' ', '', $dato) . '%';

    $condiciones[] = "(
        p.expediente LIKE ?
        OR CONCAT(p.nombre, ' ', p.apellido) LIKE ?
        OR p.nombre LIKE ?
        OR p.apellido LIKE ?
        OR p.identidad LIKE ?
        OR p.telefono1 LIKE ?
        OR p.telefono2 LIKE ?
        OR m.number LIKE ?
        OR REPLACE(m.number, ' ', '') LIKE ?
        OR m.diagnostico_clinico LIKE ?
        OR m.material_eviando LIKE ?
        OR m.datos_clinico LIKE ?
    )";

    $types .= 'ssssssssssss';
    $params[] = $dato_like;
    $params[] = $dato_like;
    $params[] = $dato_inicio;
    $params[] = $dato_inicio;
    $params[] = $dato_inicio;
    $params[] = $dato_inicio;
    $params[] = $dato_inicio;
    $params[] = $dato_like;
    $params[] = $dato_sin_espacios;
    $params[] = $dato_like;
    $params[] = $dato_like;
    $params[] = $dato_like;
}

$where = '';

if (count($condiciones) > 0) {
    $where = ' WHERE ' . implode(' AND ', $condiciones);
}

$from = "
    FROM muestras AS m
    INNER JOIN pacientes AS p 
        ON m.pacientes_id = p.pacientes_id
    LEFT JOIN (
        SELECT 
            mh.muestras_id,
            MIN(mh.pacientes_id) AS pacientes_id_cliente_codigo,
            MIN(CONCAT(pc.nombre, ' ', pc.apellido)) AS pacientes_id_cliente
        FROM muestras_hospitales AS mh
        INNER JOIN pacientes AS pc 
            ON mh.pacientes_id = pc.pacientes_id
        GROUP BY mh.muestras_id
    ) AS mh_data
        ON mh_data.muestras_id = m.muestras_id
";

function ejecutar_stmt($mysqli, $sql, $types = '', $params = array()) {
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

/* TOTAL DE REGISTROS */
$query_count = "
    SELECT COUNT(*) AS total
    $from
    $where
";

$count_exec = ejecutar_stmt($mysqli, $query_count, $types, $params);
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
}

if ($paginaActual > 1) {
    $lista .= '<li class="page-item"><a class="page-link" href="javascript:paginationMuestras(' . ($paginaActual - 1) . ');void(0);">Anterior ' . ($paginaActual - 1) . '</a></li>';
}

if ($paginaActual < $nroPaginas) {
    $lista .= '<li class="page-item"><a class="page-link" href="javascript:paginationMuestras(' . ($paginaActual + 1) . ');void(0);">Siguiente ' . ($paginaActual + 1) . ' de ' . $nroPaginas . '</a></li>';
}

if ($paginaActual > 1 && $nroPaginas > 0) {
    $lista .= '<li class="page-item"><a class="page-link" href="javascript:paginationMuestras(' . $nroPaginas . ');void(0);">Última</a></li>';
}

/* CONSULTA PRINCIPAL */
$query = "
    SELECT
        p.pacientes_id AS pacientes_id,
        CONCAT(p.nombre, ' ', p.apellido) AS paciente,
        m.fecha AS fecha,
        m.diagnostico_clinico AS diagnostico_clinico,
        m.material_eviando AS material_eviando,
        m.datos_clinico AS datos_clinico,
        CASE 
            WHEN m.estado = '1' THEN 'Atendido' 
            ELSE 'Pendiente' 
        END AS estatus,
        m.muestras_id AS muestras_id,
        m.mostrar_datos_clinicos AS mostrar_datos_clinicos,
        m.number AS numero,
        mh_data.pacientes_id_cliente_codigo AS pacientes_id_cliente_codigo,
        mh_data.pacientes_id_cliente AS pacientes_id_cliente
    $from
    $where
    ORDER BY m.fecha DESC, m.muestras_id DESC
    LIMIT ?, ?
";

$types_main = $types . 'ii';
$params_main = $params;
$params_main[] = $limit;
$params_main[] = $nroLotes;

$main_exec = ejecutar_stmt($mysqli, $query, $types_main, $params_main);
$stmt = $main_exec[0];
$result = $main_exec[1];

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
        min-width: 180px;
    }

    .dropdown-menu-muestras .dropdown-item {
        padding: 10px 14px;
        font-weight: 600;
        color: #333;
    }

    .dropdown-menu-muestras .dropdown-item:hover {
        background: #f2f7ff;
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

        $paciente = htmlspecialchars($registro2['paciente'], ENT_QUOTES, 'UTF-8');
        $fecha = htmlspecialchars($registro2['fecha'], ENT_QUOTES, 'UTF-8');
        $numero = htmlspecialchars($registro2['numero'], ENT_QUOTES, 'UTF-8');
        $diagnostico_clinico = htmlspecialchars($registro2['diagnostico_clinico'], ENT_QUOTES, 'UTF-8');
        $material_eviando = htmlspecialchars($registro2['material_eviando'], ENT_QUOTES, 'UTF-8');
        $datos_clinico = htmlspecialchars($registro2['datos_clinico'], ENT_QUOTES, 'UTF-8');

        $pacientes_id_cliente_codigo = isset($registro2['pacientes_id_cliente_codigo']) ? intval($registro2['pacientes_id_cliente_codigo']) : 0;
        $pacientes_id_cliente = isset($registro2['pacientes_id_cliente']) ? trim($registro2['pacientes_id_cliente']) : '';

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

        $tabla .= '
        <tr>
            <td><strong>' . $i . '</strong></td>
            <td>
                <span class="muestra-fecha-badge">
                    <i class="fas fa-calendar-alt"></i> ' . $fecha . '
                </span>
            </td>
            <td>
                <span class="muestra-numero-badge">
                    <i class="fas fa-vial"></i> ' . $numero . '
                </span>
            </td>
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
                        <a class="dropdown-item" href="javascript:modalCreateBill(' . $muestras_id . ');void(0);">
                            <i class="fas fa-file-invoice text-primary mr-2"></i> Facturar
                        </a>
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