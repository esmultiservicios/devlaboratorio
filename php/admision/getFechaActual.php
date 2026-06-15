<?php
session_start();   
include "../funtions.php";

// No necesita conexión a DB
$fecha_registro = date("Y-m-d");

echo $fecha_registro;