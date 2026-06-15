<?php
session_start();   
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

// Sanitizar entrada
$muestras_id = isset($_POST['muestras_id']) ? intval($_POST['muestras_id']) : 0;

// Prepared statement
$query = "SELECT number FROM muestras WHERE muestras_id = ?";
$stmt = $mysqli->prepare($query);

$numeroMuestra = "";

if ($stmt) {
    $stmt->bind_param("i", $muestras_id);
    $stmt->execute();
    $stmt->bind_result($numeroMuestra);
    $stmt->fetch();
    $stmt->close();
}

echo htmlspecialchars($numeroMuestra, ENT_QUOTES, 'UTF-8');

$mysqli->close();