<?php
// getPacienteGrupo.php
// Select2 AJAX para Facturación
// Carga inicial: últimos 300 pacientes/empresas según tipo_paciente_id
// Búsqueda: nombre, apellido, nombre completo, identidad/RTN o expediente
// Recibe: tipo_paciente, term / q
// Devuelve: JSON { results: [...] }

header('Content-Type: application/json; charset=UTF-8');

session_start();
include "../funtions.php";

// CONEXION A DB
$mysqli = connect_mysqli();

$tipo_paciente = 1;
$search = "";

// Tipo de cliente: 1 = Paciente, 2 = Empresa
if (isset($_POST['tipo_paciente']) && $_POST['tipo_paciente'] !== '') {
    $tipo_paciente = intval($_POST['tipo_paciente']);
}

if (isset($_GET['tipo_paciente']) && $_GET['tipo_paciente'] !== '') {
    $tipo_paciente = intval($_GET['tipo_paciente']);
}

// Texto buscado por Select2
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

if ($tipo_paciente <= 0) {
    $tipo_paciente = 1;
}

$results = array();

if ($search !== '' && strlen($search) >= 2) {
    // BÚSQUEDA EN BASE DE DATOS
    $search_like = '%' . $search . '%';

    $consulta = "
        SELECT 
            pacientes_id,
            TRIM(COALESCE(nombre, '')) AS nombre,
            TRIM(COALESCE(apellido, '')) AS apellido,
            TRIM(COALESCE(identidad, '')) AS identidad,
            TRIM(COALESCE(expediente, '')) AS expediente,
            tipo_paciente_id
        FROM pacientes
        WHERE estado = 1
          AND tipo_paciente_id = ?
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
            "isssss",
            $tipo_paciente,
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

            // Siempre concatenar nombre + apellido, también para empresas.
            // Si apellido está vacío, no deja doble espacio.
            $nombre_completo = trim($nombre . ' ' . $apellido);

            if ($nombre_completo === '') {
                $nombre_completo = 'Sin nombre';
            }

            $results[] = array(
                'id' => (int)$row['pacientes_id'],
                'text' => $nombre_completo . ' - RTN: ' . $identidad,
                'nombre' => $nombre_completo,
                'identidad' => $identidad,
                'expediente' => $expediente,
                'tipo_paciente_id' => (int)$row['tipo_paciente_id']
            );
        }

        $stmt->close();
    }
} else {
    // CARGA INICIAL: últimos 300 según tipo de cliente
    $consulta = "
        SELECT 
            pacientes_id,
            TRIM(COALESCE(nombre, '')) AS nombre,
            TRIM(COALESCE(apellido, '')) AS apellido,
            TRIM(COALESCE(identidad, '')) AS identidad,
            TRIM(COALESCE(expediente, '')) AS expediente,
            tipo_paciente_id
        FROM pacientes
        WHERE estado = 1
          AND tipo_paciente_id = ?
          AND nombre IS NOT NULL
          AND TRIM(nombre) != ''
        ORDER BY pacientes_id DESC
        LIMIT 300
    ";

    $stmt = $mysqli->prepare($consulta);

    if ($stmt) {
        $stmt->bind_param("i", $tipo_paciente);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $nombre = trim($row['nombre']);
            $apellido = trim($row['apellido']);
            $identidad = trim($row['identidad']);
            $expediente = trim($row['expediente']);

            // Siempre concatenar nombre + apellido, también para empresas.
            // Si apellido está vacío, no deja doble espacio.
            $nombre_completo = trim($nombre . ' ' . $apellido);

            if ($nombre_completo === '') {
                $nombre_completo = 'Sin nombre';
            }

            $results[] = array(
                'id' => (int)$row['pacientes_id'],
                'text' => $nombre_completo . ' - RTN: ' . $identidad,
                'nombre' => $nombre_completo,
                'identidad' => $identidad,
                'expediente' => $expediente,
                'tipo_paciente_id' => (int)$row['tipo_paciente_id']
            );
        }

        $stmt->close();
    }
}

$mysqli->close();

echo json_encode(array(
    'results' => $results
), JSON_UNESCAPED_UNICODE);