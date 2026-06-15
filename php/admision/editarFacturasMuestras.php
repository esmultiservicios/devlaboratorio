<?php
// editarFacturasMuestras.php
session_start();   
include "../funtions.php";
    
//CONEXION A DB
$mysqli = connect_mysqli();

$muestras_id = (int)$_POST['muestras_id'];

$query = "SELECT m.pacientes_id, CONCAT(p.nombre, ' ', p.apellido) AS paciente, m.colaborador_id, CONCAT(c.nombre, ' ', c.apellido) AS profesional, m.servicio_id, m.fecha, m.material_eviando, m.number
    FROM muestras AS m
    INNER JOIN pacientes AS p ON m.pacientes_id = p.pacientes_id
    INNER JOIN colaboradores AS c ON m.colaborador_id = c.colaborador_id
    WHERE m.muestras_id = '$muestras_id'";

$result = $mysqli->query($query);

$pacientes_id = "";
$paciente_nombre = "";
$colaborador_id = "";
$fecha = "";
$profesional = "";
$servicio_id = "";
$material_eviando = "";
$number = "";
$pacientes_id_cliente_codigo = "";
$pacientes_id_cliente = "";

if($result->num_rows > 0){    
    $valores2 = $result->fetch_assoc();
    $pacientes_id = $valores2['pacientes_id'];
    $paciente_nombre = $valores2['paciente'];    
    $colaborador_id = $valores2['colaborador_id'];
    $profesional = $valores2['profesional'];
    $fecha = $valores2['fecha'];    
    $servicio_id = $valores2['servicio_id'];
    $material_eviando = $valores2['material_eviando'];
    $number = $valores2['number'];    

    //CONSULTAR PACIENTE DE EMPRESA
    $query_paciente = "SELECT p.pacientes_id, CONCAT(p.nombre, ' ', p.apellido) AS paciente
        FROM muestras_hospitales AS mh
        INNER JOIN pacientes AS p ON mh.pacientes_id = p.pacientes_id
        WHERE mh.muestras_id = '$muestras_id'";
    $result_paciente = $mysqli->query($query_paciente);

    if($result_paciente->num_rows > 0){    
        $valores_paciente = $result_paciente->fetch_assoc();
        $pacientes_id_cliente_codigo = $valores_paciente['pacientes_id'];
        $pacientes_id_cliente = $valores_paciente['paciente'];  
    }
}

$datos = array(
    0 => $pacientes_id,
    1 => $paciente_nombre,    
    2 => $fecha,
    3 => $colaborador_id,    
    4 => $profesional,
    5 => $servicio_id,
    6 => $material_eviando,
    7 => $pacientes_id_cliente_codigo,
    8 => $pacientes_id_cliente,    
    9 => $number,    
);    

echo json_encode($datos);
$mysqli->close();