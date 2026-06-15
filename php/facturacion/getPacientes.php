<?php
// getPacientes.php
// Select2 AJAX para pacientes internos de facturación
// Carga inicial: últimos 300 pacientes
// Búsqueda: nombre, apellido, nombre completo, identidad/RTN o expediente
// Recibe: term / q
// Devuelve: JSON { results: [...] }

header('Content-Type: application/json; charset=UTF-8');

session_start();
include "../funtions.php";

// CONEXION A DB
$mysqli = connect_mysqli();

$search = "";

if (isset($_POST['term'])) {
    $search = trim($_POST['term']);
}

if (isset($_GET['term'])) {
    $search = trim($_GET['term']);
}

if (isset($_POST['q'])) {
    $search = trim($_POST['q']);
}

if (isset($_GET['q'])) {
    $search = trim($_GET['q']);
}

$results = array();

if ($search !== '' && strlen($search) >= 2) {
    $search_like = '%' . $search . '%';

    $consulta = "
        SELECT 
            pacientes_id,
            TRIM(COALESCE(nombre, '')) AS nombre,
            TRIM(COALESCE(apellido, '')) AS apellido,
            TRIM(COALESCE(identidad, '')) AS identidad,
            TRIM(COALESCE(expediente, '')) AS expediente
        FROM pacientes
        WHERE estado = 1
          AND expediente > 0
          AND (
                TRIM(COALESCE(nombre, '')) LIKE ?
                OR TRIM(COALESCE(apellido, '')) LIKE ?
                OR TRIM(COALESCE(identidad, '')) LIKE ?
                OR TRIM(COALESCE(expediente, '')) LIKE ?
                OR TRIM(CONCAT(TRIM(COALESCE(nombre, '')), ' ', TRIM(COALESCE(apellido, '')))) LIKE ?
          )
        ORDER BY nombre ASC
        LIMIT 100
    ";

    $stmt = $mysqli->prepare($consulta);

    if ($stmt) {
        $stmt->bind_param(
            "sssss",
            $search_like,
            $search_like,
            $search_like,
            $search_like,
            $search_like
        );

        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $nombre = trim($row['nombre']);
            $apellido = trim($row['apellido']);
            $identidad = trim($row['identidad']);
            $expediente = trim($row['expediente']);

            if ($apellido !== '') {
                $nombre_completo = trim($nombre . ' ' . $apellido);
            } else {
                $nombre_completo = trim($nombre);
            }

            if ($nombre_completo === '') {
                $nombre_completo = 'Sin nombre';
            }

            $results[] = array(
                'id' => (int)$row['pacientes_id'],
                'text' => $nombre_completo . ' - RTN: ' . $identidad,
                'nombre' => $nombre_completo,
                'identidad' => $identidad,
                'expediente' => $expediente
            );
        }

        $stmt->close();
    }
} else {
    $consulta = "
        SELECT 
            pacientes_id,
            TRIM(COALESCE(nombre, '')) AS nombre,
            TRIM(COALESCE(apellido, '')) AS apellido,
            TRIM(COALESCE(identidad, '')) AS identidad,
            TRIM(COALESCE(expediente, '')) AS expediente
        FROM pacientes
        WHERE estado = 1
          AND expediente > 0
          AND nombre IS NOT NULL
          AND TRIM(nombre) != ''
        ORDER BY pacientes_id DESC
        LIMIT 300
    ";

    $result = $mysqli->query($consulta) or die($mysqli->error);

    while ($row = $result->fetch_assoc()) {
        $nombre = trim($row['nombre']);
        $apellido = trim($row['apellido']);
        $identidad = trim($row['identidad']);
        $expediente = trim($row['expediente']);

        if ($apellido !== '') {
            $nombre_completo = trim($nombre . ' ' . $apellido);
        } else {
            $nombre_completo = trim($nombre);
        }

        if ($nombre_completo === '') {
            $nombre_completo = 'Sin nombre';
        }

        $results[] = array(
            'id' => (int)$row['pacientes_id'],
            'text' => $nombre_completo . ' - RTN: ' . $identidad,
            'nombre' => $nombre_completo,
            'identidad' => $identidad,
            'expediente' => $expediente
        );
    }

    $result->free();
}

$mysqli->close();

echo json_encode(array(
    'results' => $results
), JSON_UNESCAPED_UNICODE);