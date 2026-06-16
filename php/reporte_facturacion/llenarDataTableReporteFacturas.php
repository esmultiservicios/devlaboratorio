<?php
// llenarDataTableReporteFacturas.php
session_start();
include '../funtions.php';

header('Content-Type: application/json; charset=UTF-8');

// CONEXIÓN A DB
$mysqli = connect_mysqli();

$fechai = isset($_POST['fechai']) ? trim($_POST['fechai']) : '';
$fechaf = isset($_POST['fechaf']) ? trim($_POST['fechaf']) : '';
$pacientesIDGrupo = isset($_POST['pacientesIDGrupo']) ? trim($_POST['pacientesIDGrupo']) : '';
$estado = isset($_POST['estado']) ? trim($_POST['estado']) : '1';

$dato = '';

if (isset($_POST['dato'])) {
    $dato = trim($_POST['dato']);
}

if (isset($_POST['search']['value']) && trim($_POST['search']['value']) != '') {
    $dato = trim($_POST['search']['value']);
}

/*
    ESTADOS DE FACTURA:
    2 = Pagado / Contado normal
    3 = Cancelado / Anulado
    4 = Crédito

    IMPORTANTE:
    Antes este reporte también filtraba por usuario para ciertos tipos de cuenta.
    Ese filtro se eliminó porque el reporte debe mostrar lo mismo para todos.
*/

if ($estado == '' || $estado == 1) {
    // Normal: pagadas y crédito
    $in = 'IN(2,4)';
} else if ($estado == 2) {
    // Solo pagadas / contado
    $in = 'IN(2)';
} else if ($estado == 3) {
    // Solo canceladas
    $in = 'IN(3)';
} else if ($estado == 4) {
    // Solo crédito
    $in = 'IN(4)';
} else if ($estado == 5) {
    // Todos los estados visibles en reporte
    $in = 'IN(2,3,4)';
} else {
    // Respaldo seguro
    $in = 'IN(2,4)';
}

function obtenerNumeroFacturaBuscado($texto) {
    $texto = trim($texto);

    if ($texto == '') {
        return '';
    }

    preg_match_all('/\d+/', $texto, $matches);

    if (!isset($matches[0]) || count($matches[0]) == 0) {
        return '';
    }

    $ultimo = end($matches[0]);

    return strval(intval($ultimo));
}

$busqueda_paciente = '';
$consulta_fecha = '';
$consulta_datos = '';

if ($pacientesIDGrupo != '') {
    $pacientesIDGrupo = $mysqli->real_escape_string($pacientesIDGrupo);
    $busqueda_paciente = "AND f.pacientes_id = '$pacientesIDGrupo'";
}

if ($dato == '') {
    $fechai = $mysqli->real_escape_string($fechai);
    $fechaf = $mysqli->real_escape_string($fechaf);

    if ($fechai != '' && $fechaf != '') {
        $consulta_fecha = "AND f.fecha BETWEEN '$fechai' AND '$fechaf'";
    }
} else {
    $dato_esc = $mysqli->real_escape_string($dato);
    $dato_like = '%' . $dato_esc . '%';
    $dato_inicio = $dato_esc . '%';

    $numero_factura_buscado = obtenerNumeroFacturaBuscado($dato);
    $numero_factura_buscado = ($numero_factura_buscado != '') ? intval($numero_factura_buscado) : -1;

    $consulta_datos = "
        AND (
            CONCAT(p.nombre, ' ', p.apellido) LIKE '$dato_like'
            OR p.nombre LIKE '$dato_inicio'
            OR p.apellido LIKE '$dato_inicio'
            OR p.identidad LIKE '$dato_inicio'
            OR m.number LIKE '$dato_like'
            OR f.number LIKE '$dato_inicio'
            OR f.number = '$numero_factura_buscado'
            OR CONCAT(sc.prefijo, LPAD(f.number, sc.relleno, '0')) LIKE '$dato_like'
        )
    ";
}

/*
    IMPORTANTE:
    Validamos si existe la tabla facturas_cambio_fecha.
    Si no existe, el reporte NO debe fallar.
*/
$existe_tabla_cambio_fecha = false;

$check_table = $mysqli->query("SHOW TABLES LIKE 'facturas_cambio_fecha'");

if ($check_table && $check_table->num_rows > 0) {
    $existe_tabla_cambio_fecha = true;
}

$select_cambio_fecha = "
    0 AS 'cantidad_cambios_fecha',
    '' AS 'ultima_modificacion_fecha'
";

$join_cambio_fecha = "";

if ($existe_tabla_cambio_fecha) {
    $select_cambio_fecha = "
        IFNULL(hcf.cantidad_cambios, 0) AS 'cantidad_cambios_fecha',
        DATE_FORMAT(hcf.ultima_modificacion, '%d/%m/%Y %H:%i:%s') AS 'ultima_modificacion_fecha'
    ";

    $join_cambio_fecha = "
        LEFT JOIN (
            SELECT 
                secuencia_facturacion_id,
                numero,
                COUNT(*) AS cantidad_cambios,
                MAX(fecha_registro) AS ultima_modificacion
            FROM facturas_cambio_fecha
            WHERE estado = 1
            GROUP BY secuencia_facturacion_id, numero
        ) AS hcf 
        ON hcf.secuencia_facturacion_id = f.secuencia_facturacion_id
        AND hcf.numero = f.number
    ";
}

$consulta = "
SELECT 
    f.facturas_id AS 'facturas_id', 
    f.secuencia_facturacion_id AS 'secuencia_facturacion_id',

    DATE_FORMAT(f.fecha, '%d/%m/%Y') AS 'fecha', 
    DATE_FORMAT(f.fecha, '%Y-%m-%d') AS 'fecha_iso',

    p.identidad AS 'identidad', 
    CONCAT(p.nombre, ' ', p.apellido) AS 'paciente', 
    sc.prefijo AS 'prefijo', 
    f.number AS 'numero', 
    s.nombre AS 'servicio', 
    CONCAT(c.nombre, ' ', c.apellido) AS 'profesional', 
    sc.relleno AS 'relleno', 
    f.pacientes_id AS 'pacientes_id', 
    f.cierre AS 'cierre', 
    f.estado AS 'estado_factura',
    (CASE WHEN f.tipo_factura = 1 THEN 'Contado' ELSE 'Crédito' END) AS 'tipo_documento', 
    f.tipo_factura,
    m.number AS 'muestra',

    GROUP_CONCAT(f.facturas_id ORDER BY f.facturas_id ASC) AS 'facturas_ids_grupo',
    
    CAST(SUM(fd.precio * fd.cantidad) AS DECIMAL(12,2)) AS 'total_precio',
    CAST(SUM(fd.cantidad) AS DECIMAL(12,2)) AS 'cantidad',
    CAST(SUM(fd.descuento) AS DECIMAL(12,2)) AS 'descuento',
    CAST(SUM(fd.isv_valor) AS DECIMAL(12,2)) AS 'isv_neto',
    CAST(SUM(fd.precio * fd.cantidad) + SUM(fd.isv_valor) - SUM(fd.descuento) AS DECIMAL(12,2)) AS 'total',

    CAST(SUM(fd.precio * fd.cantidad) AS DECIMAL(12,2)) AS 'precio',

    (CASE 
        WHEN COUNT(DISTINCT f.facturas_id) > 1 THEN 'Grupal'
        ELSE 'Individual'
    END) AS 'tipo_factura_agrupada',

    (CASE 
        WHEN MAX(pay.estado) IS NULL THEN 'Pago Pendiente' 
        WHEN MAX(pay.estado) = 1 THEN 'Pagada'
        ELSE 'Cancelada'
    END) AS 'estado_pago',

    $select_cambio_fecha

FROM facturas AS f
INNER JOIN pacientes AS p ON f.pacientes_id = p.pacientes_id
INNER JOIN secuencia_facturacion AS sc ON f.secuencia_facturacion_id = sc.secuencia_facturacion_id
INNER JOIN servicios AS s ON f.servicio_id = s.servicio_id
INNER JOIN colaboradores AS c ON f.colaborador_id = c.colaborador_id
INNER JOIN muestras AS m ON f.muestras_id = m.muestras_id
INNER JOIN facturas_detalle AS fd ON f.facturas_id = fd.facturas_id
LEFT JOIN pagos AS pay ON f.facturas_id = pay.facturas_id

$join_cambio_fecha

WHERE f.estado $in
$consulta_fecha
$busqueda_paciente
$consulta_datos

GROUP BY f.secuencia_facturacion_id, f.number

ORDER BY f.number DESC;
";

$result = $mysqli->query($consulta) or die($mysqli->error);

$arreglo = array('data' => []);

while ($data = $result->fetch_assoc()) {
    $numero = $data['numero'] == 0 ? 'Aún no se ha generado' : $data['prefijo'] . rellenarDigitos($data['numero'], $data['relleno']);
    $data['factura'] = $numero;

    if ($data['estado_factura'] == 2) {
        $estado_ = 'Pagada';
    } else if ($data['estado_factura'] == 3) {
        $estado_ = 'Cancelada';
    } else if ($data['estado_factura'] == 4) {
        $estado_ = 'Crédito';
    } else if ($data['estado_factura'] == 1) {
        $estado_ = 'Borrador';
    } else {
        $estado_ = '';
    }

    $data['estado'] = $estado_;

    $data['precio'] = number_format(floatval($data['precio']), 2, '.', '');
    $data['isv_neto'] = number_format(floatval($data['isv_neto']), 2, '.', '');
    $data['descuento'] = number_format(floatval($data['descuento']), 2, '.', '');
    $data['total'] = number_format(floatval($data['total']), 2, '.', '');

    $data['cantidad_cambios_fecha'] = intval($data['cantidad_cambios_fecha']);

    if (!isset($data['ultima_modificacion_fecha']) || $data['ultima_modificacion_fecha'] == null) {
        $data['ultima_modificacion_fecha'] = '';
    }

    $arreglo['data'][] = $data;
}

echo json_encode([
    'data' => $arreglo['data'],
], JSON_UNESCAPED_UNICODE);

$result->free();
$mysqli->close();