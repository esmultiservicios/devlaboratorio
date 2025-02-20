<?php
session_start();
include '../funtions.php';

// CONEXIÓN A DB
$mysqli = connect_mysqli();

$colaborador_id = $_SESSION['colaborador_id'];
$type = $_SESSION['type'];
$fechai = $_POST['fechai'];
$fechaf = $_POST['fechaf'];
$pacientesIDGrupo = $_POST['pacientesIDGrupo'];
$estado = $_POST['estado'];
$usuario = $_SESSION['colaborador_id'];

if ($estado == 1) {
    $in = 'IN(2,4)';
} else if ($estado == 4) {
    $in = 'IN(4)';
} else {
    $in = 'IN(3)';
}

$busqueda_paciente = '';

if ($pacientesIDGrupo != '') {
    $busqueda_paciente = "AND f.pacientes_id = '$pacientesIDGrupo'";
}

$consulta = "
SELECT 
    f.facturas_id AS 'facturas_id', 
    DATE_FORMAT(f.fecha, '%d/%m/%Y') AS 'fecha', 
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
        WHEN pay.estado IS NULL THEN 'Pago Pendiente' 
        WHEN pay.estado = 1 THEN 'Pagada'
        ELSE 'Cancelada'
    END) AS 'estado_pago'

FROM facturas AS f
INNER JOIN pacientes AS p ON f.pacientes_id = p.pacientes_id
INNER JOIN secuencia_facturacion AS sc ON f.secuencia_facturacion_id = sc.secuencia_facturacion_id
INNER JOIN servicios AS s ON f.servicio_id = s.servicio_id
INNER JOIN colaboradores AS c ON f.colaborador_id = c.colaborador_id
INNER JOIN muestras AS m ON f.muestras_id = m.muestras_id
INNER JOIN facturas_detalle AS fd ON f.facturas_id = fd.facturas_id
-- Cambié el alias de la tabla pagos a 'pay'
LEFT JOIN pagos AS pay ON f.facturas_id = pay.facturas_id
WHERE f.fecha BETWEEN '$fechai' AND '$fechaf' 
AND f.estado $in
$busqueda_paciente
GROUP BY f.number;
";

$result = $mysqli->query($consulta) or die($mysqli->error);

$arreglo = array('data' => []);

while ($data = $result->fetch_assoc()) {
    $facturas_id = $data['facturas_id'];

    $numero = $data['numero'] == 0 ? 'Aún no se ha generado' : $data['prefijo'] . rellenarDigitos($data['numero'], $data['relleno']);
    $data['factura'] = $numero;

    $estado_ = match ($estado) {
        1 => 'Borrador',
        2 => 'Pagada',
        3 => 'Cancelada',
        4 => 'Crédito',
        default => ''
    };

    $data['estado'] = $estado_;

    $arreglo['data'][] = $data;
}

echo json_encode([
    'data' => $arreglo['data'],
]);

$result->free();
$mysqli->close();