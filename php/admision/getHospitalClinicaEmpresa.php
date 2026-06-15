<?php
session_start(); 
include('../funtions.php');

//CONEXION A DB
$mysqli = connect_mysqli();

// Sanitizar entrada
$pacientes_id = isset($_POST['pacientes_id']) ? intval($_POST['pacientes_id']) : 0;

// CORREGIDO: la consulta tenía 'pacientes_id' como string literal
$consulta = "SELECT hospitales_id FROM hospitales WHERE pacientes_id = ?";
$stmt = $mysqli->prepare($consulta);

$hospital = "";

if ($stmt) {
    $stmt->bind_param("i", $pacientes_id);
    $stmt->execute();
    $stmt->bind_result($hospital);
    $stmt->fetch();
    $stmt->close();
}

echo htmlspecialchars($hospital, ENT_QUOTES, 'UTF-8');

$mysqli->close();