<?php
session_start();
include('../funtions.php');

//CONEXION A DB
$mysqli = connect_mysqli();

// Sanitizar entrada
$tipo = isset($_POST['tipo']) ? intval($_POST['tipo']) : 0;

// Query con prepared statement por seguridad
$consulta = "SELECT pacientes_id, CONCAT(nombre, ' ', apellido) AS nombre
FROM pacientes
WHERE tipo_paciente_id = ? AND estado = 1
ORDER BY nombre ASC";

$stmt = $mysqli->prepare($consulta);
if ($stmt) {
    $stmt->bind_param("i", $tipo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        while($consulta2 = $result->fetch_assoc()) {
            echo '<option value="' . htmlspecialchars($consulta2['pacientes_id'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($consulta2['nombre'], ENT_QUOTES, 'UTF-8') . '</option>';
        }
    } else {
        echo '<option value="">No hay registros que mostrar</option>';
    }
    
    $stmt->close();
} else {
    echo '<option value="">Error en la consulta</option>';
}

$mysqli->close();