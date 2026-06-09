<?php
session_start();
include "../funtions.php";

header('Content-Type: application/json; charset=UTF-8');

$mysqli = connect_mysqli();
$mysqli->set_charset("utf8");

$colaborador_id = isset($_SESSION['colaborador_id']) ? intval($_SESSION['colaborador_id']) : 0;
$type = isset($_SESSION['type']) ? intval($_SESSION['type']) : 0;
$usuario = $colaborador_id;

$paginaActual = isset($_POST['partida']) ? intval($_POST['partida']) : 1;
$fechai = isset($_POST['fechai']) ? trim($_POST['fechai']) : '';
$fechaf = isset($_POST['fechaf']) ? trim($_POST['fechaf']) : '';
$dato = isset($_POST['dato']) ? trim($_POST['dato']) : '';
$pacientesIDGrupo = isset($_POST['pacientesIDGrupo']) ? trim($_POST['pacientesIDGrupo']) : '';
$estado = isset($_POST['estado']) ? intval($_POST['estado']) : 1;

if ($paginaActual <= 0) {
    $paginaActual = 1;
}

if ($estado == 1) {
    $estadosPermitidos = array(2, 4);
} else if ($estado == 4) {
    $estadosPermitidos = array(4);
} else {
    $estadosPermitidos = array(3);
}

$nroLotes = 25;
$limit = ($paginaActual <= 1) ? 0 : $nroLotes * ($paginaActual - 1);

function extraerNumeroFacturaBusqueda($texto) {
    $texto = trim($texto);

    if ($texto === '') {
        return '';
    }

    preg_match_all('/\d+/', $texto, $matches);

    if (!isset($matches[0]) || count($matches[0]) === 0) {
        return '';
    }

    $ultimo = end($matches[0]);

    return strval(intval($ultimo));
}

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

$placeholdersEstado = implode(',', array_fill(0, count($estadosPermitidos), '?'));

$condiciones = array();
$types = '';
$params = array();

foreach ($estadosPermitidos as $estadoPermitido) {
    $types .= 'i';
    $params[] = intval($estadoPermitido);
}

$condiciones[] = "f.estado IN ($placeholdersEstado)";

if ($dato === '') {
    if ($fechai !== '' && $fechaf !== '') {
        $condiciones[] = "f.fecha BETWEEN ? AND ?";
        $types .= 'ss';
        $params[] = $fechai;
        $params[] = $fechaf;
    }
}

if ($pacientesIDGrupo !== '') {
    $condiciones[] = "f.pacientes_id = ?";
    $types .= 'i';
    $params[] = intval($pacientesIDGrupo);
}

if (!($type == 1 || $type == 2 || $type == 4)) {
    $condiciones[] = "f.usuario = ?";
    $types .= 'i';
    $params[] = intval($usuario);
}

if ($dato !== '') {
    $datoLike = '%' . $dato . '%';
    $datoInicio = $dato . '%';
    $numeroFacturaBuscado = extraerNumeroFacturaBusqueda($dato);
    $numeroFacturaInt = ($numeroFacturaBuscado !== '') ? intval($numeroFacturaBuscado) : -1;

    $condiciones[] = "(
        CONCAT(p.nombre, ' ', p.apellido) LIKE ?
        OR p.nombre LIKE ?
        OR p.apellido LIKE ?
        OR p.identidad LIKE ?
        OR m.number LIKE ?
        OR f.number LIKE ?
        OR f.number = ?
        OR CONCAT(sc.prefijo, LPAD(f.number, sc.relleno, '0')) LIKE ?
    )";

    $types .= 'ssssssis';
    $params[] = $datoLike;
    $params[] = $datoInicio;
    $params[] = $datoInicio;
    $params[] = $datoInicio;
    $params[] = $datoLike;
    $params[] = $datoInicio;
    $params[] = $numeroFacturaInt;
    $params[] = $datoLike;
}

$where = "WHERE " . implode(" AND ", $condiciones);

$from = "
    FROM facturas AS f
    INNER JOIN pacientes AS p
        ON f.pacientes_id = p.pacientes_id
    INNER JOIN secuencia_facturacion AS sc
        ON f.secuencia_facturacion_id = sc.secuencia_facturacion_id
    INNER JOIN servicios AS s
        ON f.servicio_id = s.servicio_id
    INNER JOIN colaboradores AS c
        ON f.colaborador_id = c.colaborador_id
    LEFT JOIN muestras AS m
        ON f.muestras_id = m.muestras_id
    LEFT JOIN (
        SELECT 
            facturas_id,
            SUM(precio) AS precio,
            SUM(descuento) AS descuento,
            SUM(cantidad) AS cantidad,
            SUM(precio * cantidad) AS neto_antes_isv,
            SUM(isv_valor) AS isv_neto
        FROM facturas_detalle
        GROUP BY facturas_id
    ) AS fd
        ON fd.facturas_id = f.facturas_id
";

$queryCount = "
    SELECT COUNT(*) AS total
    $from
    $where
";

$countExec = ejecutarConsultaPreparada($mysqli, $queryCount, $types, $params);
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
}

if ($paginaActual > 1) {
    $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(' . $nroPaginas . ');void(0);">Última</a></li>';
}

$query = "
    SELECT 
        f.facturas_id AS facturas_id,
        f.fecha AS fecha,
        DATE_FORMAT(f.fecha, '%d/%m/%Y') AS fecha1,
        p.identidad AS identidad,
        CONCAT(p.nombre, ' ', p.apellido) AS paciente,
        sc.prefijo AS prefijo,
        f.number AS numero,
        s.nombre AS servicio,
        CONCAT(c.nombre, ' ', c.apellido) AS profesional,
        sc.relleno AS relleno,
        f.pacientes_id AS pacientes_id,
        f.cierre AS cierre,
        CASE 
            WHEN f.tipo_factura = 1 THEN 'Contado' 
            ELSE 'Crédito' 
        END AS tipo_documento,
        f.tipo_factura AS tipo_factura,
        COALESCE(m.number, '') AS muestra,
        COALESCE(fd.precio, 0) AS precio,
        COALESCE(fd.descuento, 0) AS descuento,
        COALESCE(fd.cantidad, 0) AS cantidad,
        COALESCE(fd.neto_antes_isv, 0) AS neto_antes_isv,
        COALESCE(fd.isv_neto, 0) AS isv_neto
    $from
    $where
    ORDER BY f.number DESC
    LIMIT ?, ?
";

$typesMain = $types . 'ii';
$paramsMain = $params;
$paramsMain[] = intval($limit);
$paramsMain[] = intval($nroLotes);

$mainExec = ejecutarConsultaPreparada($mysqli, $query, $typesMain, $paramsMain);
$stmt = $mainExec[0];
$result = $mainExec[1];

$tabla .= '
<table class="table table-striped table-condensed table-hover">
    <tr>
        <th width="2.66%">No.</th>
        <th width="6.66%">Factura</th>
        <th width="6.66%">Fecha</th>
        <th width="10.14%">Muestra</th>
        <th width="6.66%">Identidad</th>
        <th width="10.66%">Paciente</th>
        <th width="13.66%">Número</th>
        <th width="6.66%">Importe</th>
        <th width="6.66%">ISV</th>
        <th width="6.66%">Descuento</th>
        <th width="6.66%">Neto</th>
        <th width="6.66%">Servicio</th>
        <th width="6.66%">Profesional</th>
        <th width="8%">Acciones</th>
    </tr>
';

$i = $limit + 1;

while ($registro2 = $result->fetch_assoc()) {
    $facturas_id = intval($registro2['facturas_id']);
    $pacientes_id = intval($registro2['pacientes_id']);

    $precio = floatval($registro2['precio']);
    $descuento = floatval($registro2['descuento']);
    $isv_neto = floatval($registro2['isv_neto']);
    $neto_antes_isv = floatval($registro2['neto_antes_isv']);
    $total = ($neto_antes_isv + $isv_neto) - $descuento;

    if ($registro2['numero'] !== "" && $registro2['numero'] !== null) {
        $numero = $registro2['prefijo'] . rellenarDigitos($registro2['numero'], $registro2['relleno']);
    } else {
        $numero = "Aún no se ha generado";
    }

    $cierre = intval($registro2['cierre']);

    if ($cierre == 1) {
        $cierre_texto = '<span class="dropdown-item disabled"><i class="fas fa-check-double text-success mr-2"></i> Factura cerrada</span>';
    } else {
        $cierre_texto = '<span class="dropdown-item disabled"><i class="fas fa-check text-secondary mr-2"></i> Sin cierre</span>';
    }

    $tipo_documento = htmlspecialchars($registro2['tipo_documento'], ENT_QUOTES, 'UTF-8');
    $fecha1 = htmlspecialchars($registro2['fecha1'], ENT_QUOTES, 'UTF-8');
    $muestra = htmlspecialchars($registro2['muestra'], ENT_QUOTES, 'UTF-8');
    $identidad = htmlspecialchars($registro2['identidad'], ENT_QUOTES, 'UTF-8');
    $paciente = htmlspecialchars($registro2['paciente'], ENT_QUOTES, 'UTF-8');
    $numero_html = htmlspecialchars($numero, ENT_QUOTES, 'UTF-8');
    $servicio = htmlspecialchars($registro2['servicio'], ENT_QUOTES, 'UTF-8');
    $profesional = htmlspecialchars($registro2['profesional'], ENT_QUOTES, 'UTF-8');

    $badgeFactura = ($registro2['tipo_factura'] == 1)
        ? '<span class="badge badge-warning px-3 py-2"><i class="fas fa-file-invoice-dollar"></i> Contado</span>'
        : '<span class="badge badge-info px-3 py-2"><i class="fas fa-file-invoice"></i> Crédito</span>';

    $tabla .= '
    <tr>
        <td>' . $i . '</td>
        <td>' . $badgeFactura . '</td>
        <td>
            <a style="text-decoration:none;" class="text-primary font-weight-bold" href="javascript:invoicesDetails(' . $facturas_id . ');void(0);">
                ' . $fecha1 . '
            </a>
        </td>
        <td>' . $muestra . '</td>
        <td>' . $identidad . '</td>
        <td>' . $paciente . '</td>
        <td><span class="badge badge-light border px-2 py-2">' . $numero_html . '</span></td>
        <td>' . number_format($precio, 2) . '</td>
        <td>' . number_format($isv_neto, 2) . '</td>
        <td>' . number_format($descuento, 2) . '</td>
        <td>
            <span style="border:2px solid #A5BF13; border-radius:12px; padding:5px 10px; color:#A5BF13; font-weight:bold;">
                ' . number_format($total, 2) . '
            </span>
        </td>
        <td>' . $servicio . '</td>
        <td>' . $profesional . '</td>
        <td>
            <div class="btn-group">
                <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-cog"></i> Acciones
                </button>
                <div class="dropdown-menu dropdown-menu-right shadow-sm">
                    <a class="dropdown-item" href="javascript:printBill(' . $facturas_id . ');void(0);">
                        <i class="fas fa-print text-primary mr-2"></i> Imprimir
                    </a>
                    ' . $cierre_texto . '
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger" href="javascript:modal_rollback(' . $facturas_id . ',' . $pacientes_id . ');void(0);">
                        <i class="fas fa-undo mr-2"></i> Anular
                    </a>
                </div>
            </div>
        </td>
    </tr>';

    $i++;
}

if ($nroProductos == 0) {
    $tabla .= '
    <tr>
        <td colspan="14" style="color:#C7030D">
            No se encontraron resultados.
        </td>
    </tr>';
} else {
    $tabla .= '
    <tr>
        <td colspan="14">
            <b><p align="center">Total de Registros Encontrados: ' . number_format($nroProductos) . '</p></b>
        </td>
    </tr>';
}

$tabla .= '</table>';

$stmt->close();
$mysqli->close();

echo json_encode(array($tabla, $lista), JSON_UNESCAPED_UNICODE);