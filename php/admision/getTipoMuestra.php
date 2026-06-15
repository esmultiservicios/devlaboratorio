<?php
//getTipoMuestra.php
session_start(); 
include('../funtions.php');

//CONEXION A DB
$mysqli = connect_mysqli();

// Query optimizada con ORDER BY
$consulta = "SELECT tipo_muestra_id, nombre FROM tipo_muestra ORDER BY nombre ASC";
$result = $mysqli->query($consulta);

if($result->num_rows > 0) {
    echo '<option value="0">Sin selección</option>';
    while($consulta2 = $result->fetch_assoc()) {
        echo '<option value="' . htmlspecialchars($consulta2['tipo_muestra_id'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($consulta2['nombre'], ENT_QUOTES, 'UTF-8') . '</option>';
    }
} else {
    echo '<option value="">No hay registros que mostrar</option>';
}

$result->free();
$mysqli->close();