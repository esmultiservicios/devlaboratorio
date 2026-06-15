<?php
session_start();   
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

// Sanitizar entrada
$muestras_id = isset($_POST['muestras_id']) ? intval($_POST['muestras_id']) : 0;

// Prepared statement
$query = "SELECT facturas_id FROM facturas WHERE muestras_id = ? AND estado = 2";
$stmt = $mysqli->prepare($query);

$facturas_id = "";

if ($stmt) {
    $stmt->bind_param("i", $muestras_id);
    $stmt->execute();
    $stmt->bind_result($facturas_id);
    $stmt->fetch();
    $stmt->close();
}

echo htmlspecialchars($facturas_id, ENT_QUOTES, 'UTF-8');

$mysqli->close();