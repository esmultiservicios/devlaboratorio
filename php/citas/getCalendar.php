<?php
session_start();
include "../funtions.php";

header('Content-Type: application/json; charset=utf-8');

$mysqli = connect_mysqli();

$fecha_registro = date('Y-m-d');
$tipo_muestra = isset($_POST['tipo_muestra']) ? $_POST['tipo_muestra'] : '';

$where = "WHERE CAST(c.fecha_cita AS DATE) >= '$fecha_registro'";

if ($tipo_muestra != "") {
    $tipo_muestra = $mysqli->real_escape_string($tipo_muestra);
    $where .= " AND m.tipo_muestra_id = '$tipo_muestra'";
}

$sql = "SELECT 
            c.calendario_id AS calendario_id,
            m.number AS muestra,
            c.fecha_cita AS start,
            c.fecha_cita_end AS end,
            c.color AS color,
            CONCAT(p.nombre, ' ', p.apellido) AS cliente
        FROM calendario AS c
        INNER JOIN pacientes AS p
            ON c.pacientes_id = p.pacientes_id
        INNER JOIN muestras AS m
            ON c.muestras_id = m.muestras_id
        $where";

$result = $mysqli->query($sql);

$events = array();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $events[] = array(
            'id' => $row['calendario_id'],
            'title' => $row['muestra'] . '-' . $row['cliente'],
            'start' => $row['start'],
            'end' => $row['end'],
            'color' => $row['color']
        );
    }

    $result->free();
}

echo json_encode($events);

$mysqli->close();
exit;