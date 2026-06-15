<?php
session_start();   
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

// Sanitizar entrada
$pacientes_id = isset($_POST['pacientes_id']) ? intval($_POST['pacientes_id']) : 0;

// Prepared statement
$query = "SELECT CONCAT(nombre, ' ', apellido) AS nombre FROM pacientes WHERE pacientes_id = ?";
$stmt = $mysqli->prepare($query);

$nombre = "";

if ($stmt) {
    $stmt->bind_param("i", $pacientes_id);
    $stmt->execute();
    $stmt->bind_result($nombre);
    $stmt->fetch();
    $stmt->close();
}

echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');

$mysqli->close();