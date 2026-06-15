<?php
session_start();   
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

// Query optimizada con ORDER BY
$consulta = "SELECT hospitales_id, nombre FROM hospitales ORDER BY nombre ASC";
$result = $mysqli->query($consulta);

if($result->num_rows > 0) {
    while($consulta2 = $result->fetch_assoc()) {
        echo '<option value="' . htmlspecialchars($consulta2['hospitales_id'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($consulta2['nombre'], ENT_QUOTES, 'UTF-8') . '</option>';
    }
} else {
    echo '<option value="">No hay registros que mostrar</option>';
}

$result->free();
$mysqli->close();