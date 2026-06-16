<?php
// getPacienteGrupo.php
// Carga clientes/pacientes para filtros y Select2/Selectpicker
// Tipo cliente:
// 1 = Paciente
// 2 = Empresa
//
// Recibe:
// POST/GET tipo_paciente
// POST/GET term / q
//
// Devuelve:
// JSON { results: [...] }
//
// Importante:
// - Ya no limita a últimos 300, porque en reportes con selectpicker faltaban registros.
// - Busca por nombre, apellido, nombre completo, identidad/RTN y expediente.
// - Si se escribe "jose", devuelve todos los registros que contengan "jose", no exacto.
// - Empresas también pueden usar nombre + apellido si por datos viejos quedaron partidas.

header('Content-Type: application/json; charset=UTF-8');

session_start();
include "../funtions.php";

// CONEXION A DB
$mysqli = connect_mysqli();

if ($mysqli) {
    $mysqli->set_charset("utf8");
}

$tipo_paciente = 1;
$search = "";

// Tipo de cliente: 1 = Paciente, 2 = Empresa
if (isset($_POST['tipo_paciente']) && $_POST['tipo_paciente'] !== '') {
    $tipo_paciente = intval($_POST['tipo_paciente']);
}

if (isset($_GET['tipo_paciente']) && $_GET['tipo_paciente'] !== '') {
    $tipo_paciente = intval($_GET['tipo_paciente']);
}

// Texto buscado por Select2 / Selectpicker
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

// Seguridad: solo permitir 1 o 2
if ($tipo_paciente != 1 && $tipo_paciente != 2) {
    $tipo_paciente = 1;
}

$results = array();

try {
    if ($search !== '') {
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
            ORDER BY 
                TRIM(COALESCE(nombre, '')) ASC,
                TRIM(COALESCE(apellido, '')) ASC,
                pacientes_id ASC
        ";

        $stmt = $mysqli->prepare($consulta);

        if (!$stmt) {
            throw new Exception("Error al preparar consulta de clientes: " . $mysqli->error);
        }

        $stmt->bind_param(
            "isssss",
            $tipo_paciente,
            $search_like,
            $search_like,
            $search_like,
            $search_like,
            $search_like
        );
    } else {
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
                    TRIM(COALESCE(nombre, '')) != ''
                    OR TRIM(COALESCE(apellido, '')) != ''
                    OR TRIM(COALESCE(identidad, '')) != ''
                    OR TRIM(COALESCE(expediente, '')) != ''
              )
            ORDER BY 
                TRIM(COALESCE(nombre, '')) ASC,
                TRIM(COALESCE(apellido, '')) ASC,
                pacientes_id ASC
        ";

        $stmt = $mysqli->prepare($consulta);

        if (!$stmt) {
            throw new Exception("Error al preparar consulta de clientes: " . $mysqli->error);
        }

        $stmt->bind_param("i", $tipo_paciente);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $pacientes_id = (int)$row['pacientes_id'];
        $nombre = trim($row['nombre']);
        $apellido = trim($row['apellido']);
        $identidad = trim($row['identidad']);
        $expediente = trim($row['expediente']);
        $tipo_paciente_id = (int)$row['tipo_paciente_id'];

        // Para pacientes y empresas se respeta nombre + apellido.
        // Esto cubre empresas viejas que quedaron partidas entre nombre y apellido.
        $nombre_completo = trim($nombre . ' ' . $apellido);

        if ($nombre_completo === '') {
            if ($identidad !== '') {
                $nombre_completo = $identidad;
            } else if ($expediente !== '') {
                $nombre_completo = $expediente;
            } else {
                $nombre_completo = 'Sin nombre';
            }
        }

        $texto = $nombre_completo;

        if ($identidad !== '') {
            $texto .= ' - RTN/Identidad: ' . $identidad;
        }

        if ($expediente !== '' && $expediente !== '0') {
            $texto .= ' - Exp: ' . $expediente;
        }

        $results[] = array(
            'id' => $pacientes_id,
            'text' => $texto,
            'nombre' => $nombre_completo,
            'identidad' => $identidad,
            'expediente' => $expediente,
            'tipo_paciente_id' => $tipo_paciente_id
        );
    }

    $stmt->close();

} catch (Exception $e) {
    error_log("Error getPacienteGrupo.php: " . $e->getMessage());

    echo json_encode(array(
        'results' => array(),
        'error' => true,
        'message' => $e->getMessage()
    ), JSON_UNESCAPED_UNICODE);

    if ($mysqli) {
        $mysqli->close();
    }

    exit;
}

if ($mysqli) {
    $mysqli->close();
}

echo json_encode(array(
    'results' => $results
), JSON_UNESCAPED_UNICODE);
exit;