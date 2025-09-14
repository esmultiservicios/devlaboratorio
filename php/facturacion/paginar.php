<?php
session_start();
include "../funtions.php";

// CONEXION A DB
$mysqli = connect_mysqli();
$mysqli->set_charset('utf8mb4');

$colaborador_id        = (int)$_SESSION['colaborador_id'];
$paginaActual          = isset($_POST['partida']) ? (int)$_POST['partida'] : 1;
$fechai                = isset($_POST['fechai']) ? $_POST['fechai'] : date('Y-m-01');
$fechaf                = isset($_POST['fechaf']) ? $_POST['fechaf'] : date('Y-m-d');
$dato                  = isset($_POST['dato']) ? trim($_POST['dato']) : '';
$tipo_paciente_grupo   = isset($_POST['tipo_paciente_grupo']) && $_POST['tipo_paciente_grupo'] !== '' ? (int)$_POST['tipo_paciente_grupo'] : null;
$pacientesIDGrupo      = isset($_POST['pacientesIDGrupo']) && $_POST['pacientesIDGrupo'] !== '' ? (int)$_POST['pacientesIDGrupo'] : null;
$estado                = isset($_POST['estado']) ? (int)$_POST['estado'] : 1;

$nroLotes = 200;
$offset   = max(0, ($paginaActual - 1) * $nroLotes);

// ===============================
// 1) Construir WHERE + binds
// ===============================
$where   = [];
$types   = '';
$params  = [];

// estado + rango de fechas (SIEMPRE)
$where[] = 'f.estado = ?';
$types  .= 'i';
$params[] = $estado;

$where[] = 'f.fecha BETWEEN ? AND ?';
$types  .= 'ss';
$params[] = $fechai;
$params[] = $fechaf;

// si estado es 2 ó 4 filtrar por usuario
if ($estado === 2 || $estado === 4) {
    $where[] = 'f.usuario = ?';
    $types  .= 'i';
    $params[] = $colaborador_id;
}

// filtros por tipo/empresa (tabla pacientes p)
if (!is_null($tipo_paciente_grupo)) {
    $where[] = 'p.tipo_paciente_id = ?';
    $types  .= 'i';
    $params[] = $tipo_paciente_grupo;
}
if (!is_null($pacientesIDGrupo)) {
    $where[] = 'p.pacientes_id = ?';
    $types  .= 'i';
    $params[] = $pacientesIDGrupo;
}

// búsqueda libre
if ($dato !== '') {
    $like = "%$dato%";
    $where[] = '(p.expediente LIKE ? OR p.nombre LIKE ? OR p.apellido LIKE ? OR CONCAT(p.apellido," ",p.nombre) LIKE ? OR CAST(f.number AS CHAR) LIKE ?)';
    $types  .= 'sssss';
    array_push($params, $like, $like, $like, $like, $like);
}

$whereSQL = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// ===============================
// 2) Consulta principal (con subquery de agregados)
// ===============================
$sql = "
SELECT
    f.facturas_id,
    DATE_FORMAT(f.fecha, '%d/%m/%Y') AS fecha,
    CONCAT(p.nombre,' ',p.apellido) AS empresa,
    p.identidad AS identidad,
    CONCAT(c.nombre,' ',c.apellido) AS profesional,
    f.estado,
    s.nombre AS consultorio,
    sc.prefijo,
    f.number,
    sc.relleno,
    COALESCE(CONCAT(p1.nombre,' ',p1.apellido), '') AS paciente,
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
INNER JOIN pacientes  AS p  ON f.pacientes_id = p.pacientes_id
INNER JOIN secuencia_facturacion AS sc ON f.secuencia_facturacion_id = sc.secuencia_facturacion_id
INNER JOIN servicios   AS s  ON f.servicio_id  = s.servicio_id
INNER JOIN colaboradores AS c ON f.colaborador_id = c.colaborador_id
LEFT  JOIN muestras_hospitales AS mh ON f.muestras_id = mh.muestras_id
LEFT  JOIN pacientes AS p1 ON mh.pacientes_id = p1.pacientes_id
LEFT  JOIN muestras  AS m  ON f.muestras_id = m.muestras_id
LEFT  JOIN (
    SELECT
        facturas_id,
        SUM(precio)                       AS total_precio,
        SUM(cantidad)                     AS total_cantidad,
        SUM(descuento)                    AS total_descuento,
        SUM(isv_valor)                    AS total_isv,
        SUM(precio * cantidad)            AS neto_antes_isv
    FROM facturas_detalle
    GROUP BY facturas_id
) AS fd_sum ON fd_sum.facturas_id = f.facturas_id
{$whereSQL}
ORDER BY f.fecha DESC, f.facturas_id DESC
LIMIT ?, ?
";

$stmt = $mysqli->prepare($sql);
if (!$stmt) { die($mysqli->error); }
$types_main = $types . 'ii';
$params_main = $params;
$params_main[] = $offset;
$params_main[] = $nroLotes;
$stmt->bind_param($types_main, ...$params_main);
$stmt->execute();
$result = $stmt->get_result();

// ===============================
// 3) COUNT(*) total (sin LIMIT)
// ===============================
$sqlCount = "
SELECT COUNT(*) AS total
FROM facturas AS f
INNER JOIN pacientes AS p ON f.pacientes_id = p.pacientes_id
{$whereSQL}
";
$stmtCount = $mysqli->prepare($sqlCount);
$stmtCount->bind_param($types, ...$params);
$stmtCount->execute();
$total_registros = (int)$stmtCount->get_result()->fetch_assoc()['total'];
$stmtCount->close();

$nroPaginas = (int)ceil($total_registros / $nroLotes);

// ===============================
// 4) Paginación
// ===============================
$lista = '';
if ($nroPaginas > 1) {
    if ($paginaActual > 1) {
        $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(1);">Inicio</a></li>';
        $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination('.($paginaActual-1).');">Anterior '.($paginaActual-1).'</a></li>';
    }
    if ($paginaActual < $nroPaginas) {
        $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination('.($paginaActual+1).');">Siguiente '.($paginaActual+1).' de '.$nroPaginas.'</a></li>';
        $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination('.$nroPaginas.');">Ultima</a></li>';
    }
}

// ===============================
// 5) Botones por estado (mismo comportamiento)
// ===============================
$botones_por_estado = [
    1 => [
        'texto1' => '<a class="btn btn-secondary ml-2" href="javascript:pay(%d);"><i class="fas fa-file-invoice fa-lg"></i> Facturar</a>',
        'texto2' => '<a class="btn btn-secondary ml-2" href="javascript:deleteBill(%d);"><i class="fas fa-trash fa-lg"></i> Eliminar</a>'
    ],
    2 => [
        'texto1' => '<a class="btn btn-secondary ml-2" href="javascript:mailBill(%d);"><i class="far fa-paper-plane fa-lg"></i> Enviar</a>',
        'texto2' => '<a class="btn btn-secondary ml-2" href="javascript:printBill(%d);"><i class="fas fa-print fa-lg"></i> Imprimir</a>'
    ],
    4 => [
        'texto1' => '<a class="btn btn-secondary ml-2" href="javascript:printBill(%d);"><i class="fas fa-print fa-lg"></i> Imprimir</a>',
        'texto2' => '<a class="btn btn-secondary ml-2" href="javascript:pago(%d);"><i class="fab fa-amazon-pay fa-lg"></i> Cobrar</a>'
    ],
    3 => [
        'texto1' => '<a class="btn btn-secondary ml-2" href="javascript:printBill(%d);"><i class="fas fa-print fa-lg"></i> Imprimir</a>',
        'texto2' => ''
    ]
];

// ===============================
// 6) Render tabla (encabezados FIJOS)
// ===============================
$tabla = '<table class="table table-striped table-condensed table-hover">
    <thead>
        <tr>
            <th width="2.66%"><input id="checkAllFactura" class="formcontrol" type="checkbox"></th>
            <th width="2.66%">No.</th>
            <th width="4.66%">Fecha</th>
            <th width="7.66%">Muestra</th>
            <th width="8.66%">Factura</th>
            <th width="8.66%">Empresa</th>
            <th width="6.66%">Identidad</th>
            <th width="6.66%">Profesional</th>
            <th width="6.66%">Importe</th>
            <th width="6.66%">ISV</th>
            <th width="6.66%">Descuento</th>
            <th width="6.66%">Neto</th>
            <th width="3.66%">Estado</th>
            <th width="8.66%">Facturar</th>
            <th width="8.66%">Eliminar</th>
        </tr>
    </thead>
    <tbody>';

if ($total_registros > 0) {
    $i = 1;
    while ($r = $result->fetch_assoc()) {
        $total  = ($r['neto_antes_isv'] + $r['total_isv']) - $r['total_descuento'];
        $numero = ($r['number'] == 0)
            ? "Aún no se ha generado"
            : $r['prefijo'] . rellenarDigitos($r['number'], $r['relleno']);

        $empresaTxt = $r['empresa'];
        if (!empty($r['paciente'])) {
            $empresaTxt .= " (<b>Paciente</b>: " . $r['paciente'] . ")";
        }

        $botones  = $botones_por_estado[$r['estado']] ?? [];
        $texto1_btn = isset($botones['texto1']) ? sprintf($botones['texto1'], $r['facturas_id']) : '';
        $texto2_btn = isset($botones['texto2']) ? sprintf($botones['texto2'], $r['facturas_id']) : '';

        $tabla .= '<tr>';
        $tabla .= '<td><input class="itemRowFactura" type="checkbox" name="itemFactura" id="itemFactura_'.($i-1).'" value="'.$r['facturas_id'].'"></td>';
        $tabla .= '<td>'.($i++).'</td>';
        $tabla .= '<td>'.$r['fecha'].'</td>';
        $tabla .= '<td>'.($r['muestra'] ?: '').'</td>';
        $tabla .= '<td>'.$numero.'</td>';
        $tabla .= '<td>'.$empresaTxt.'</td>';
        $tabla .= '<td>'.$r['identidad'].'</td>';
        $tabla .= '<td>'.$r['profesional'].'</td>';
        $tabla .= '<td>'.number_format($r['total_precio'], 2).'</td>';
        $tabla .= '<td>'.number_format($r['total_isv'], 2).'</td>';
        $tabla .= '<td>'.number_format($r['total_descuento'], 2).'</td>';

        // celda con data-values para el grupal
        $tabla .= '<td>
            <div id="quantyGrupoQuantityValor_'.$r['facturas_id'].'" data-value="'.$r['total_cantidad'].'"></div>
            <div id="profesionalIDGrupo_'.$r['facturas_id'].'"      data-value="'.$r['colaborador_id'].'"></div>
            <div id="muestraGrupo_'.$r['facturas_id'].'"            data-value="'.$r['muestras_id'].'"></div>
            <div id="codigoFacturaGrupo_'.$r['facturas_id'].'"      data-value="'.$r['facturas_id'].'"></div>
            <div id="pacientesIDFacturaGrupo_'.$r['facturas_id'].'" data-value="'.$r['codigoPacienteEmpresa'].'"></div>
            <div id="importeFacturaGrupo_'.$r['facturas_id'].'"     data-value="'.number_format($total,2,'.','').'"></div>'.number_format($total,2).'
            <div id="precioFacturaGrupo_'.$r['facturas_id'].'"      data-value="'.number_format($r['total_precio'],2,'.','').'"></div>
            <div id="ISVFacturaGrupo_'.$r['facturas_id'].'"         data-value="'.number_format($r['total_isv'],2,'.','').'"></div>
            <div id="DescuentoFacturaGrupo_'.$r['facturas_id'].'"   data-value="'.number_format($r['total_descuento'],2,'.','').'"></div>
            <div id="netoAntesISVFacturaGrupo_'.$r['facturas_id'].'" data-value="'.number_format($r['neto_antes_isv'],2,'.','').'"></div>
        </td>';

        // estado y botones
        $estadoTxt = ($r['estado']==1?'Borrador':($r['estado']==2?'Pagada':($r['estado']==4?'Crédito':($r['estado']==3?'Cancelada':''))));
        $tabla .= '<td>'.$estadoTxt.'</td>';
        $tabla .= '<td>'.$texto1_btn.'</td>';
        $tabla .= '<td>'.$texto2_btn.'</td>';

        $tabla .= '</tr>';
    }
} else {
    $tabla .= '<tr><td colspan="15" style="color:#C7030D">No se encontraron resultados para los filtros seleccionados</td></tr>';
}
$tabla .= '</tbody></table>';

if ($total_registros > 0) {
    $tabla .= '<div class="text-center mt-3"><b>Total de Registros Encontrados: '.$total_registros.'</b></div>';
}

echo json_encode([
    0 => $tabla,
    1 => $lista
]);

// liberar
$result && $result->free();
$stmt->close();
$mysqli->close();