<?php
session_start();   
include "../funtions.php";

// CONEXION A DB
$mysqli = connect_mysqli();

// Inicializar variables
$numeroAnterior = 0;
$numeroMaximo = 0;
$rangoInicial = 0;
$contador = 0;
$empresa_id = $_SESSION['empresa_id'];
$fecha_limite = date('Y-m-d');

$query = "SELECT 
            siguiente AS 'numero', 
            rango_inicial, 
            rango_final,
            fecha_limite,
            DATEDIFF(fecha_limite, CURDATE()) AS 'dias_restantes'
          FROM secuencia_facturacion
          WHERE activo = 1 AND empresa_id = ?
          ORDER BY siguiente DESC 
          LIMIT 1";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $empresa_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0) {
    $consulta = $result->fetch_assoc();
    $numeroAnterior = empty($consulta['numero']) ? 0 : $consulta['numero'];
    $rangoInicial = $consulta['rango_inicial'];
    $numeroMaximo = $consulta['rango_final'];
    $fecha_limite = $consulta['fecha_limite'];
    $contador = $consulta['dias_restantes'];
}

// Calcular facturas pendientes
$facturasPendientes = (int)$numeroMaximo - (int)$numeroAnterior;

// Preparar respuesta
$datos = array(
    0 => $facturasPendientes,
    1 => $contador,    
    2 => $fecha_limite,
    3 => $numeroMaximo,
    4 => $rangoInicial
);

header('Content-Type: application/json');
echo json_encode($datos);

$stmt->close();
$mysqli->close();