<?php
session_start(); 
include('../funtions.php');

//CONSULTA AÑO, MES y DIA DEL PACIENTE
$fecha_nacimiento = $_POST['fecha_nac'];
$valores_array = getEdad($fecha_nacimiento);
$anos = $valores_array['anos'];
$meses = $valores_array['meses'];	  
$dias = $valores_array['dias'];	
/*********************************************************************************/

echo $anos;
?>