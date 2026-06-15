<?php
//getRemitente.php
session_start();   
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

// Query optimizada con JOIN y ORDER BY
$consulta = "SELECT c.colaborador_id, CONCAT(c.nombre, ' ', c.apellido) AS colaborador
FROM colaboradores AS c
INNER JOIN puesto_colaboradores AS pc ON c.puesto_id = pc.puesto_id
WHERE pc.puesto_id IN (6)
ORDER BY colaborador ASC";

$result = $mysqli->query($consulta);

if($result->num_rows > 0) {
    while($consulta2 = $result->fetch_assoc()) {
        echo '<option value="' . htmlspecialchars($consulta2['colaborador_id'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($consulta2['colaborador'], ENT_QUOTES, 'UTF-8') . '</option>';
    }
} else {
    echo '<option value="">No hay registros que mostrar</option>';
}

$result->free();
$mysqli->close();