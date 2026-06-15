<?php
// getClientes.php - Para Select2 (búsqueda AJAX + últimos clientes)
header('Content-Type: application/json; charset=UTF-8');
session_start();
include('../funtions.php');

$mysqli = connect_mysqli();

$search = isset($_GET['term']) ? trim($_GET['term']) : '';
$search = isset($_GET['q']) ? trim($_GET['q']) : $search;

$results = array();

if ($search !== '' && strlen($search) >= 2) {
    // BÚSQUEDA: por nombre, apellido O identidad
    $search_like = '%' . $search . '%';
    $consulta = "SELECT pacientes_id, 
                        CONCAT(nombre, ' ', apellido) AS nombre,
                        identidad
                 FROM pacientes
                 WHERE tipo_paciente_id = 1 AND estado = 1 
                 AND (nombre LIKE ? OR apellido LIKE ? OR identidad LIKE ?)
                 ORDER BY nombre ASC 
                 LIMIT 50";
    $stmt = $mysqli->prepare($consulta);
    $stmt->bind_param("sss", $search_like, $search_like, $search_like);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()) {
        $results[] = array(
            'id' => (int)$row['pacientes_id'],
            'text' => $row['nombre'] . ' - RTN: ' . $row['identidad'],
            'nombre' => $row['nombre'],
            'identidad' => $row['identidad']
        );
    }
    $stmt->close();
} else {
    // SIN BÚSQUEDA: últimos 20 clientes
    $consulta = "SELECT pacientes_id, 
                        CONCAT(nombre, ' ', apellido) AS nombre,
                        identidad
                 FROM pacientes 
                 WHERE tipo_paciente_id = 1 AND estado = 1
                 AND nombre IS NOT NULL AND nombre != '' AND TRIM(nombre) != ''
                 ORDER BY pacientes_id DESC 
                 LIMIT 20";
    $result = $mysqli->query($consulta);
    while($row = $result->fetch_assoc()) {
        $results[] = array(
            'id' => (int)$row['pacientes_id'],
            'text' => $row['nombre'] . ' - RTN: ' . $row['identidad'],
            'nombre' => $row['nombre'],
            'identidad' => $row['identidad']
        );
    }
}

$mysqli->close();
echo json_encode(array('results' => $results));