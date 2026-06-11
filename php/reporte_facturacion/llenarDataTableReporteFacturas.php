<?php
//llenarDataTableReporteFacturas.php
session_start();
include '../funtions.php';

header('Content-Type: application/json; charset=UTF-8');

// CONEXIÓN A DB
$mysqli = connect_mysqli();

$colaborador_id = $_SESSION['colaborador_id'];
$type = $_SESSION['type'];

$fechai = isset($_POST['fechai']) ? $_POST['fechai'] : '';
$fechaf = isset($_POST['fechaf']) ? $_POST['fechaf'] : '';
$pacientesIDGrupo = isset($_POST['pacientesIDGrupo']) ? $_POST['pacientesIDGrupo'] : '';
$estado = isset($_POST['estado']) ? $_POST['estado'] : 1;
$usuario = $_SESSION['colaborador_id'];

$dato = '';

if (isset($_POST['dato'])) {
    $dato = trim($_POST['dato']);
}

if (isset($_POST['search']['value']) && trim($_POST['search']['value']) != '') {
    $dato = trim($_POST['search']['value']);
}

if ($estado == 1) {
    $in = 'IN(2,4)';
} else if ($estado == 4) {
    $in = 'IN(4)';
} else {
    $in = 'IN(3)';
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
$consulta_usuario = '';

if ($pacientesIDGrupo != '') {
    $pacientesIDGrupo = $mysqli->real_escape_string($pacientesIDGrupo);
    $busqueda_paciente = "AND f.pacientes_id = '$pacientesIDGrupo'";
}

if ($dato == '') {
    $fechai = $mysqli->real_escape_string($fechai);
    $fechaf = $mysqli->real_escape_string($fechaf);

    $consulta_fecha = "AND f.fecha BETWEEN '$fechai' AND '$fechaf'";
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

if (!($type == 1 || $type == 2 || $type == 4)) {
    $usuario = $mysqli->real_escape_string($usuario);
    $consulta_usuario = "AND f.usuario = '$usuario'";
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
    (CASE WHEN f.tipo_factura = 1 THEN 'Contado' ELSE 'Crédito' END) AS 'tipo_documento', 
    f.tipo_factura,
    m.number AS 'muestra',

    GROUP_CONCAT(f.facturas_id ORDER BY f.facturas_id ASC) AS 'facturas_ids_grupo',
    
    CAST(SUM(fd.precio * fd.cantidad) AS DECIMAL(12,2)) AS 'total_precio',
    CAST(SUM(fd.cantidad) AS DECIMAL(12,2)) AS 'cantidad',
    CAST(SUM(fd.descuento) AS DECIMAL(12,2)) AS 'descuento',
    CAST(SUM(fd.isv_valor) AS DECIMAL(12,2)) AS 'isv_neto',
    CAST(SUM(fd.precio * fd.cantidad) + SUM(fd.isv_valor) - SUM(fd.descuento) AS DECIMAL(12,2)) AS 'total',

    CAST(SUM(fd.precio) AS DECIMAL(12,2)) AS 'precio',

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
$consulta_usuario
$consulta_datos

GROUP BY f.secuencia_facturacion_id, f.number

ORDER BY f.number DESC;
";

$result = $mysqli->query($consulta) or die($mysqli->error);

$arreglo = array('data' => []);

while ($data = $result->fetch_assoc()) {
    $numero = $data['numero'] == 0 ? 'Aún no se ha generado' : $data['prefijo'] . rellenarDigitos($data['numero'], $data['relleno']);
    $data['factura'] = $numero;

    if ($estado == 1) {
        $estado_ = 'Borrador';
    } else if ($estado == 2) {
        $estado_ = 'Pagada';
    } else if ($estado == 3) {
        $estado_ = 'Cancelada';
    } else if ($estado == 4) {
        $estado_ = 'Crédito';
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