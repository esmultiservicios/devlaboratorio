<?php
// modificarRegistroEmpresas.php - Editar Empresas
session_start();   
include "../funtions.php";
    
//CONEXION A DB
$mysqli = connect_mysqli();

$pacientes_id = (int)$_POST['pacientes_id'];
$nombre = cleanString($_POST['empresa']);
$apellido = "";
$identidad = $_POST['rtn'];

// Validar nombre no vacío
if(trim($nombre) == ''){
    echo json_encode(array(
        0 => "Error", 
        1 => "El nombre de la empresa es requerido", 
        2 => "error",
        3 => "btn-danger"
    ));
    exit;
}

//CONSULTAR IDENTIDAD DEL USUARIO
if($identidad == 0){
    $flag_identidad = true;
    while($flag_identidad){
        $d = rand(1, 99999999);
        $query_identidadRand = "SELECT pacientes_id FROM pacientes WHERE identidad = '$d'";
        $result_identidad = $mysqli->query($query_identidadRand);
        if($result_identidad->num_rows == 0){
            $identidad = $d;
            $flag_identidad = false;
        }
    }
}

$localidad = cleanString($_POST['direccion']);
$telefono1 = $_POST['telefono1'];
$correo = strtolower(cleanString($_POST['correo']));

$update = "UPDATE pacientes SET nombre = '$nombre', identidad = '$identidad', localidad = '$localidad', telefono1 = '$telefono1', email = '$correo' WHERE pacientes_id = '$pacientes_id'";
$query = $mysqli->query($update);

if($query){
    $datos = array(
        0 => "Modificado", 
        1 => "Registro Modificado Correctamente", 
        2 => "success",
        3 => "btn-primary",
        4 => "formulario_admision_empresas",
        5 => "Registro",
        6 => "formEmpresas",
        7 => "modal_admision_empesas"
    );
} else {
    $datos = array(
        0 => "Error", 
        1 => "No se pudo modificar este registro", 
        2 => "error",
        3 => "btn-danger"
    );
}

echo json_encode($datos);
$mysqli->close();