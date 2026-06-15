<?php
session_start(); 
include('../funtions.php');

//CONEXION A DB
$mysqli = connect_mysqli();

// Sanitizar entrada
$tipo_muestra_id = isset($_POST['tipo_muestra_id']) ? intval($_POST['tipo_muestra_id']) : 0;

// Prepared statement con JOIN opcional (si existe relación)
$consulta = "SELECT productos_id, nombre FROM productos WHERE tipo_muestra_id = ? ORDER BY nombre ASC";
$stmt = $mysqli->prepare($consulta);

if ($stmt) {
    $stmt->bind_param("i", $tipo_muestra_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        while($consulta2 = $result->fetch_assoc()) {
            echo '<option value="' . htmlspecialchars($consulta2['productos_id'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($consulta2['nombre'], ENT_QUOTES, 'UTF-8') . '</option>';
        }
    } else {
        echo '<option value="">No hay registros que mostrar</option>';
    }
    
    $stmt->close();
} else {
    echo '<option value="">Error en la consulta</option>';
}

$mysqli->close();