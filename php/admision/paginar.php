<?php
// paginar.php - Admision
session_start();
include "../funtions.php";

header('Content-Type: application/json; charset=UTF-8');

$mysqli = connect_mysqli();
$mysqli->set_charset("utf8");

$paginaActual = isset($_POST['partida']) ? intval($_POST['partida']) : 1;
if ($paginaActual <= 0) {
    $paginaActual = 1;
}

$dato = isset($_POST['dato']) ? trim($_POST['dato']) : '';

$tipo = (isset($_POST['tipo']) && trim($_POST['tipo']) !== '') ? intval($_POST['tipo']) : 1;
$estado = (isset($_POST['estado']) && trim($_POST['estado']) !== '') ? intval($_POST['estado']) : 1;

$nroLotes = 15;
$limit = ($paginaActual <= 1) ? 0 : $nroLotes * ($paginaActual - 1);

$condiciones = array();
$types = '';
$params = array();

$condiciones[] = "p.estado = ?";
$types .= "i";
$params[] = $estado;

$condiciones[] = "p.tipo_paciente_id = ?";
$types .= "i";
$params[] = $tipo;

if ($dato !== '') {
    $dato_like = '%' . $dato . '%';

    $condiciones[] = "(
        p.expediente LIKE ?
        OR p.nombre LIKE ?
        OR p.apellido LIKE ?
        OR p.identidad LIKE ?
        OR p.telefono1 LIKE ?
        OR p.telefono2 LIKE ?
        OR p.email LIKE ?
        OR p.localidad LIKE ?
        OR CONCAT(p.nombre, ' ', p.apellido) LIKE ?
        OR CONCAT(p.apellido, ' ', p.nombre) LIKE ?
        OR CONCAT(TRIM(p.nombre), ' ', TRIM(p.apellido)) LIKE ?
        OR CONCAT(TRIM(p.apellido), ' ', TRIM(p.nombre)) LIKE ?
    )";

    $types .= "ssssssssssss";
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

$where = " WHERE " . implode(" AND ", $condiciones);

function ejecutarConsultaPreparada($mysqli, $sql, $types = '', $params = array()) {
    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        echo json_encode(array(
            '<div style="color:#C7030D">Error al preparar consulta: ' . htmlspecialchars($mysqli->error, ENT_QUOTES, 'UTF-8') . '</div>',
            ''
        ), JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($types !== '' && count($params) > 0) {
        $bindParams = array();
        $bindParams[] = $types;

        for ($i = 0; $i < count($params); $i++) {
            $bindParams[] = &$params[$i];
        }

        call_user_func_array(array($stmt, 'bind_param'), $bindParams);
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

/* CONTAR REGISTROS */
$query_count = "
    SELECT COUNT(*) AS total
    FROM pacientes AS p
    $where
";

$countExec = ejecutarConsultaPreparada($mysqli, $query_count, $types, $params);
$stmtCount = $countExec[0];
$resultCount = $countExec[1];

$rowCount = $resultCount->fetch_assoc();
$nroProductos = isset($rowCount['total']) ? intval($rowCount['total']) : 0;

$stmtCount->close();

$nroPaginas = ($nroProductos > 0) ? ceil($nroProductos / $nroLotes) : 1;

$lista = '';
$tabla = '';

if ($paginaActual > 1) {
    $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(1);void(0);">Inicio</a></li>';
    $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(' . ($paginaActual - 1) . ');void(0);">Anterior ' . ($paginaActual - 1) . '</a></li>';
}

if ($paginaActual < $nroPaginas) {
    $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(' . ($paginaActual + 1) . ');void(0);">Siguiente ' . ($paginaActual + 1) . ' de ' . $nroPaginas . '</a></li>';
    $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(' . $nroPaginas . ');void(0);">Última</a></li>';
}

/* CONSULTA PRINCIPAL */
$query = "
    SELECT 
        p.pacientes_id,
        CONCAT(TRIM(p.nombre), ' ', TRIM(p.apellido)) AS nombre,
        p.edad,
        p.telefono1 AS telefono,
        p.telefono2 AS telefono1,
        p.email AS correo,
        p.localidad AS direccion,
        p.identidad AS identidad
    FROM pacientes AS p
    $where
    ORDER BY p.pacientes_id ASC
    LIMIT ?, ?
";

$typesMain = $types . "ii";
$paramsMain = $params;
$paramsMain[] = $limit;
$paramsMain[] = $nroLotes;

$mainExec = ejecutarConsultaPreparada($mysqli, $query, $typesMain, $paramsMain);
$stmt = $mainExec[0];
$result = $mainExec[1];

if ($estado === 1) {
    $estado_label = "Inhabilitar";
    $icon = "fa fa-ban";
    $estado_color = "text-warning";
} else {
    $estado_label = "Habilitar";
    $icon = "fa fa-check";
    $estado_color = "text-success";
}

$tabla .= '
<style>
    .clientes-table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .clientes-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background: #fff;
        font-size: 14px;
    }

    .clientes-table thead th {
        background: #129aaa;
        color: #fff;
        font-weight: 700;
        padding: 14px 12px;
        vertical-align: middle;
        border: none;
        white-space: nowrap;
    }

    .clientes-table tbody td {
        padding: 14px 12px;
        vertical-align: middle;
        border-top: 1px solid #e8edf2;
        color: #222;
    }

    .clientes-table tbody tr:nth-child(even) {
        background: #f4f6f8;
    }

    .clientes-table tbody tr:hover {
        background: #eef9fb;
    }

    .cliente-nombre {
        font-weight: 700;
        color: #243447;
        line-height: 1.3;
    }

    .cliente-sub {
        font-size: 12px;
        color: #6c757d;
        margin-top: 2px;
    }

    .badge-rtn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #eef7ff;
        border: 1px solid #b8dcff;
        color: #006992;
        border-radius: 12px;
        padding: 5px 9px;
        font-weight: 600;
        white-space: nowrap;
    }

    .badge-edad {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 42px;
        background: #f7f7f7;
        border: 1px solid #d8dde3;
        border-radius: 12px;
        padding: 5px 8px;
        font-weight: 700;
        color: #333;
    }

    .telefono-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #f1f8f4;
        color: #198754;
        border: 1px solid #bfe8cf;
        border-radius: 12px;
        padding: 5px 9px;
        font-weight: 600;
        white-space: nowrap;
    }

    .telefono-empty {
        display: inline-flex;
        align-items: center;
        background: #f2f2f2;
        color: #888;
        border-radius: 12px;
        padding: 5px 9px;
        font-weight: 600;
    }

    .correo-text {
        color: #006992;
        font-weight: 600;
        word-break: break-word;
    }

    .direccion-text {
        max-width: 360px;
        white-space: normal;
        line-height: 1.35;
        color: #333;
    }

    .btn-acciones-clientes {
        background: #0d6efd;
        border-color: #0d6efd;
        color: #fff !important;
        font-weight: 600;
        border-radius: 7px;
        padding: 7px 12px;
        box-shadow: 0 2px 5px rgba(13, 110, 253, .20);
    }

    .btn-acciones-clientes:hover {
        background: #0b5ed7;
        border-color: #0b5ed7;
        color: #fff !important;
    }

    .dropdown-menu-clientes {
        border: 0;
        border-radius: 10px;
        box-shadow: 0 10px 25px rgba(0,0,0,.12);
        overflow: hidden;
        min-width: 180px;
    }

    .dropdown-menu-clientes .dropdown-item {
        padding: 10px 14px;
        font-weight: 600;
        color: #333;
    }

    .dropdown-menu-clientes .dropdown-item:hover {
        background: #f2f7ff;
    }

    .total-clientes-row {
        background: #129aaa !important;
        color: #fff !important;
    }

    .total-clientes-row td {
        color: #fff !important;
        font-weight: 700;
        padding: 13px !important;
        border-top: none !important;
    }
</style>

<div class="clientes-table-wrapper">
<table class="clientes-table table table-hover">
    <thead>
        <tr>
            <th width="4%">N°</th>
            <th width="10%">RTN</th>
            <th width="18%">Cliente</th>
            <th width="6%">Edad</th>
            <th width="10%">Teléfono 1</th>
            <th width="10%">Teléfono 2</th>
            <th width="16%">Correo</th>
            <th width="18%">Dirección</th>
            <th width="8%">Acciones</th>
        </tr>
    </thead>
    <tbody>
';

$i = $limit + 1;

if ($result->num_rows > 0) {
    while ($registro2 = $result->fetch_assoc()) {
        $pacientes_id = intval($registro2['pacientes_id']);

        $identidad = htmlspecialchars($registro2['identidad'], ENT_QUOTES, 'UTF-8');
        $nombre = htmlspecialchars(trim($registro2['nombre']), ENT_QUOTES, 'UTF-8');
        $edad = htmlspecialchars($registro2['edad'], ENT_QUOTES, 'UTF-8');
        $telefono = htmlspecialchars($registro2['telefono'], ENT_QUOTES, 'UTF-8');
        $telefono1 = htmlspecialchars($registro2['telefono1'], ENT_QUOTES, 'UTF-8');
        $correo = htmlspecialchars($registro2['correo'], ENT_QUOTES, 'UTF-8');
        $direccion = htmlspecialchars($registro2['direccion'], ENT_QUOTES, 'UTF-8');

        if ($nombre === '') {
            $nombre = 'Sin nombre';
        }

        $telefono_html = ($telefono !== '' && $telefono !== '0')
            ? '<span class="telefono-pill"><i class="fas fa-phone-alt"></i> ' . $telefono . '</span>'
            : '<span class="telefono-empty">Sin dato</span>';

        $telefono1_html = ($telefono1 !== '' && $telefono1 !== '0')
            ? '<span class="telefono-pill"><i class="fas fa-phone-alt"></i> ' . $telefono1 . '</span>'
            : '<span class="telefono-empty">Sin dato</span>';

        $correo_html = ($correo !== '')
            ? '<span class="correo-text"><i class="fas fa-envelope"></i> ' . $correo . '</span>'
            : '<span class="telefono-empty">Sin correo</span>';

        $direccion_html = ($direccion !== '')
            ? '<div class="direccion-text"><i class="fas fa-map-marker-alt text-danger"></i> ' . $direccion . '</div>'
            : '<span class="telefono-empty">Sin dirección</span>';

        $tabla .= '
        <tr>
            <td><strong>' . $i . '</strong></td>
            <td><span class="badge-rtn"><i class="fas fa-id-card"></i> ' . $identidad . '</span></td>
            <td>
                <div class="cliente-nombre">' . $nombre . '</div>
                <div class="cliente-sub">Código: ' . $pacientes_id . '</div>
            </td>
            <td><span class="badge-edad">' . $edad . '</span></td>
            <td>' . $telefono_html . '</td>
            <td>' . $telefono1_html . '</td>
            <td>' . $correo_html . '</td>
            <td>' . $direccion_html . '</td>
            <td>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-acciones-clientes dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-cog"></i> Acciones
                    </button>
                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-clientes">
                        <a class="dropdown-item" href="javascript:showModalhistoriaMuestrasEmpresas(' . $pacientes_id . ');void(0);">
                            <i class="fas fa-eye text-info mr-2"></i> Ver muestras
                        </a>
                        <a class="dropdown-item" href="javascript:editarRegistro(' . $pacientes_id . ');void(0);">
                            <i class="fas fa-user-edit text-primary mr-2"></i> Editar cliente
                        </a>
                        <a class="dropdown-item" href="javascript:DisableRegister(' . $pacientes_id . ');void(0);">
                            <i class="' . $icon . ' ' . $estado_color . ' mr-2"></i> ' . $estado_label . '
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" href="javascript:modal_eliminar(' . $pacientes_id . ');void(0);">
                            <i class="fas fa-trash mr-2"></i> Eliminar
                        </a>
                    </div>
                </div>
            </td>
        </tr>';

        $i++;
    }

    $tabla .= '
    <tr class="total-clientes-row">
        <td colspan="9" align="center">
            Total de registros encontrados: ' . number_format($nroProductos) . '
        </td>
    </tr>';
} else {
    $tabla .= '
    <tr>
        <td colspan="9" style="color:#C7030D; padding:18px; font-weight:700;">
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