<?php
// paginar_historico_muestras_empresas.php OPTIMIZADO
// Mantiene la misma salida JSON: array(tabla, paginacion)
// Optimización:
// - Prepared statements
// - Elimina consulta N+1 para buscar paciente empresa
// - Elimina consulta innecesaria a facturas que no se usaba en la salida
// - Usa COUNT separado y LIMIT real

session_start();
include "../funtions.php";

header('Content-Type: application/json; charset=UTF-8');

$mysqli = connect_mysqli();
$mysqli->set_charset("utf8");

$paginaActual = isset($_POST['partida']) ? intval($_POST['partida']) : 1;
if ($paginaActual <= 0) {
    $paginaActual = 1;
}

$pacientes_id = isset($_POST['pacientes_id']) ? intval($_POST['pacientes_id']) : 0;
$dato = isset($_POST['dato']) ? trim((string)$_POST['dato']) : '';

$nroLotes = 5;
$limit = ($paginaActual <= 1) ? 0 : $nroLotes * ($paginaActual - 1);

function ejecutarConsultaPreparadaHistoricoEmpresas($mysqli, $sql, $types = '', $params = array()) {
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

$condiciones = array();
$types = '';
$params = array();

$condiciones[] = "m.pacientes_id = ?";
$types .= 'i';
$params[] = $pacientes_id;

$condiciones[] = "m.estado NOT IN ('2')";

if ($dato !== '') {
    $datoPrefix = $dato . '%';
    $datoLike = '%' . $dato . '%';

    $condiciones[] = "(
        m.number LIKE ?
        OR tm.nombre LIKE ?
    )";

    $types .= 'ss';
    $params[] = $datoPrefix;
    $params[] = $datoLike;
}

$where = ' WHERE ' . implode(' AND ', $condiciones);

$from = "
    FROM muestras AS m
    INNER JOIN pacientes AS p
        ON m.pacientes_id = p.pacientes_id
    INNER JOIN tipo_muestra AS tm
        ON m.tipo_muestra_id = tm.tipo_muestra_id
    LEFT JOIN muestras_hospitales AS mh
        ON mh.muestras_id = m.muestras_id
    LEFT JOIN pacientes AS pc
        ON pc.pacientes_id = mh.pacientes_id
";

$query_count = "
    SELECT COUNT(DISTINCT m.muestras_id) AS total
    $from
    $where
";

$countExec = ejecutarConsultaPreparadaHistoricoEmpresas($mysqli, $query_count, $types, $params);
$stmtCount = $countExec[0];
$resultCount = $countExec[1];
$rowCount = $resultCount->fetch_assoc();
$nroProductos = isset($rowCount['total']) ? intval($rowCount['total']) : 0;
$stmtCount->close();

$nroPaginas = ($nroProductos > 0) ? ceil($nroProductos / $nroLotes) : 1;

$lista = '';
$tabla = '';

if ($paginaActual > 1) {
    $lista .= '<li class="page-item"><a class="page-link" href="javascript:historiaMuestrasEmpresas(1);void(0);">Inicio</a></li>';
    $lista .= '<li class="page-item"><a class="page-link" href="javascript:historiaMuestrasEmpresas(' . ($paginaActual - 1) . ');void(0);">Anterior ' . ($paginaActual - 1) . '</a></li>';
}

if ($paginaActual < $nroPaginas) {
    $lista .= '<li class="page-item"><a class="page-link" href="javascript:historiaMuestrasEmpresas(' . ($paginaActual + 1) . ');void(0);">Siguiente ' . ($paginaActual + 1) . ' de ' . $nroPaginas . '</a></li>';
}

if ($paginaActual > 1 && $nroPaginas > 0) {
    $lista .= '<li class="page-item"><a class="page-link" href="javascript:historiaMuestrasEmpresas(' . $nroPaginas . ');void(0);">Ultima</a></li>';
}

$query = "
    SELECT
        p.pacientes_id AS pacientes_id,
        CONCAT(p.nombre, ' ', p.apellido) AS paciente,
        m.fecha AS fecha,
        m.diagnostico_clinico AS diagnostico_clinico,
        m.material_eviando AS material_eviando,
        m.datos_clinico AS datos_clinico,
        CASE WHEN m.estado = '1' THEN 'Atendido' ELSE 'Pendiente' END AS estatus,
        m.muestras_id AS muestras_id,
        m.mostrar_datos_clinicos AS mostrar_datos_clinicos,
        m.number AS numero,
        MIN(pc.pacientes_id) AS pacientes_id_cliente_codigo,
        MIN(CONCAT(pc.nombre, ' ', pc.apellido)) AS pacientes_id_cliente
    $from
    $where
    GROUP BY
        p.pacientes_id,
        p.nombre,
        p.apellido,
        m.fecha,
        m.diagnostico_clinico,
        m.material_eviando,
        m.datos_clinico,
        m.estado,
        m.muestras_id,
        m.mostrar_datos_clinicos,
        m.number
    ORDER BY m.fecha DESC, m.muestras_id DESC
    LIMIT ?, ?
";

$typesMain = $types . 'ii';
$paramsMain = $params;
$paramsMain[] = $limit;
$paramsMain[] = $nroLotes;

$mainExec = ejecutarConsultaPreparadaHistoricoEmpresas($mysqli, $query, $typesMain, $paramsMain);
$stmt = $mainExec[0];
$result = $mainExec[1];

$tabla .= '<table class="table table-striped table-condensed table-hover">
            <tr>
            <th width="1.3%">No.</th>
            <th width="10.3%">Fecha</th>
            <th width="15.3%">Número</th>
            <th width="24.3%">Paciente</th>
            <th width="16.3%">Diagnostico Clínico</th>
            <th width="16.3%">Material Enviado</th>
            <th width="16.3%">Datos Clínicos</th>
            </tr>';

$i = $limit + 1;

if ($result->num_rows > 0) {
    while ($registro2 = $result->fetch_assoc()) {
        $paciente = htmlspecialchars($registro2['paciente'], ENT_QUOTES, 'UTF-8');
        $pacientes_id_cliente = isset($registro2['pacientes_id_cliente']) ? trim((string)$registro2['pacientes_id_cliente']) : '';

        if ($pacientes_id_cliente === '') {
            $empresa = $paciente;
        } else {
            $empresa = $paciente . ' (<b>Paciente:</b> ' . htmlspecialchars($pacientes_id_cliente, ENT_QUOTES, 'UTF-8') . ')';
        }

        $tabla .= '<tr>
            <td>' . $i . '</td>
            <td>' . htmlspecialchars($registro2['fecha'], ENT_QUOTES, 'UTF-8') . '</td>
            <td>' . htmlspecialchars($registro2['numero'], ENT_QUOTES, 'UTF-8') . '</td>
            <td>' . $empresa . '</td>
            <td>' . htmlspecialchars($registro2['diagnostico_clinico'], ENT_QUOTES, 'UTF-8') . '</td>
            <td>' . htmlspecialchars($registro2['material_eviando'], ENT_QUOTES, 'UTF-8') . '</td>
            <td>' . htmlspecialchars($registro2['datos_clinico'], ENT_QUOTES, 'UTF-8') . '</td>
            </tr>';

        $i++;
    }
}

if ($nroProductos == 0) {
    $tabla .= '<tr>
       <td colspan="12" style="color:#C7030D">No se encontraron resultados</td>
    </tr>';
} else {
    $tabla .= '<tr>
      <td colspan="12"><b><p ALIGN="center">Total de Registros Encontrados: ' . number_format($nroProductos) . '</p></b>
   </tr>';
}

$tabla .= '</table>';

$stmt->close();
$mysqli->close();

echo json_encode(array($tabla, $lista), JSON_UNESCAPED_UNICODE);
?>
