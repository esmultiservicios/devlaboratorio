<?php
session_start();   
include "../funtions.php";
	
// CONEXION A DB
$mysqli = connect_mysqli();
 
$pacientes_id = $_POST['pacientes_id'];

$paciente = ""; // Initialize variable

$query = "SELECT CONCAT(nombre,' ',apellido) AS 'paciente' 
    FROM pacientes 
    WHERE pacientes_id = '$pacientes_id'";
    
$result = $mysqli->query($query);   

if ($result && $result->num_rows > 0) {
    $consulta2 = $result->fetch_assoc(); 
    $paciente = $consulta2['paciente'];
} else {
    $paciente = "Paciente no encontrado";
}

echo $paciente;

if ($result) {
    $result->free(); // LIMPIAR RESULTADO
}
$mysqli->close(); // CERRAR CONEXIÓN