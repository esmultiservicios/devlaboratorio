<?php
// paginar_historico_muestras_pacientes.php OPTIMIZADO
// Mantiene la misma salida JSON: array(tabla, paginacion)
// Optimización:
// - Prepared statements
// - COUNT real sin cargar todos los registros
// - UNION ALL con agrupación externa para evitar duplicados
// - LIMIT aplicado correctamente al resultado final

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

function ejecutarConsultaPreparadaHistoricoPacientes($mysqli, $sql, $types = '', $params = array()) {
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

$types = 'ii';
$params = array($pacientes_id, $pacientes_id);
$filtroDato1 = '';
$filtroDato2 = '';

if ($dato !== '') {
    $datoPrefix = $dato . '%';
    $datoLike = '%' . $dato . '%';

    $filtroDato1 = " AND (m.number LIKE ? OR tm.nombre LIKE ?)";
    $filtroDato2 = " AND (m.number LIKE ? OR tm.nombre LIKE ?)";

    $types .= 'ssss';
    $params[] = $datoPrefix;
    $params[] = $datoLike;
    $params[] = $datoPrefix;
    $params[] = $datoLike;
}

$baseUnion = "
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
        CONCAT(p1.nombre, ' ', p1.apellido) AS empresa
    FROM muestras AS m
    INNER JOIN pacientes AS p
        ON m.pacientes_id = p.pacientes_id
    INNER JOIN tipo_muestra AS tm
        ON m.tipo_muestra_id = tm.tipo_muestra_id
    INNER JOIN pacientes AS p1
        ON m.pacientes_id = p1.pacientes_id
    WHERE m.pacientes_id = ?
      AND m.estado NOT IN ('2')
      $filtroDato1

    UNION ALL

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
        CONCAT(p1.nombre, ' ', p1.apellido) AS empresa
    FROM muestras AS m
    INNER JOIN muestras_hospitales AS mh
        ON m.muestras_id = mh.muestras_id
    INNER JOIN pacientes AS p
        ON mh.pacientes_id = p.pacientes_id
    INNER JOIN tipo_muestra AS tm
        ON m.tipo_muestra_id = tm.tipo_muestra_id
    INNER JOIN pacientes AS p1
        ON m.pacientes_id = p1.pacientes_id
    WHERE mh.pacientes_id = ?
      AND m.estado NOT IN ('2')
      $filtroDato2
";

$queryCount = "
    SELECT COUNT(*) AS total
    FROM (
        SELECT muestras_id
        FROM (
            $baseUnion
        ) AS u
        GROUP BY muestras_id
    ) AS conteo
";

$countExec = ejecutarConsultaPreparadaHistoricoPacientes($mysqli, $queryCount, $types, $params);
$stmtCount = $countExec[0];
$resultCount = $countExec[1];
$rowCount = $resultCount->fetch_assoc();
$nroProductos = isset($rowCount['total']) ? intval($rowCount['total']) : 0;
$stmtCount->close();

$nroPaginas = ($nroProductos > 0) ? ceil($nroProductos / $nroLotes) : 1;

$lista = '';
$tabla = '';

if ($paginaActual > 1) {
    $lista .= '<li class="page-item"><a class="page-link" href="javascript:historiaMuestrasPacientes(1);void(0);">Inicio</a></li>';
    $lista .= '<li class="page-item"><a class="page-link" href="javascript:historiaMuestrasPacientes(' . ($paginaActual - 1) . ');void(0);">Anterior ' . ($paginaActual - 1) . '</a></li>';
}

if ($paginaActual < $nroPaginas) {
    $lista .= '<li class="page-item"><a class="page-link" href="javascript:historiaMuestrasPacientes(' . ($paginaActual + 1) . ');void(0);">Siguiente ' . ($paginaActual + 1) . ' de ' . $nroPaginas . '</a></li>';
}

if ($paginaActual > 1 && $nroPaginas > 0) {
    $lista .= '<li class="page-item"><a class="page-link" href="javascript:historiaMuestrasPacientes(' . $nroPaginas . ');void(0);">Ultima</a></li>';
}

$query = "
    SELECT
        pacientes_id,
        paciente,
        fecha,
        diagnostico_clinico,
        material_eviando,
        datos_clinico,
        estatus,
        muestras_id,
        mostrar_datos_clinicos,
        numero,
        empresa
    FROM (
        $baseUnion
    ) AS u
    GROUP BY muestras_id
    ORDER BY fecha DESC, muestras_id DESC
    LIMIT ?, ?
";

$typesMain = $types . 'ii';
$paramsMain = $params;
$paramsMain[] = $limit;
$paramsMain[] = $nroLotes;

$mainExec = ejecutarConsultaPreparadaHistoricoPacientes($mysqli, $query, $typesMain, $paramsMain);
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
        $empresa = htmlspecialchars($registro2['empresa'], ENT_QUOTES, 'UTF-8') . ' (<b>Paciente:</b> ' . htmlspecialchars($registro2['paciente'], ENT_QUOTES, 'UTF-8') . ')';

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
