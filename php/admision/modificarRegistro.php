<?php
// modificarRegistro.php - Editar Clientes
session_start();
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

$pacientes_id = (int)$_POST['pacientes_id'];
$nombre = cleanString($_POST['name']);
$apellido = "";
$identidad = $_POST['rtn'];

// Validar nombre no vacío
if(trim($nombre) == ''){
    echo json_encode(array(
        0 => "Error",
        1 => "El nombre del cliente es requerido",
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

$fecha_nacimiento = $_POST['fecha_nac'];
$edad = $_POST['edad'];
$telefono1 = $_POST['telefono1'];
$telefono2 = $_POST['telefono2'];
$genero = $_POST['genero'];
$localidad = cleanString($_POST['direccion']);
$correo = strtolower(cleanString($_POST['correo']));
$fecha = date("Y-m-d");
$usuario = $_SESSION['colaborador_id'];
$fecha_registro = date("Y-m-d H:i:s");

$update = "UPDATE pacientes SET nombre = '$nombre', apellido = '$apellido', identidad = '$identidad', edad = '$edad', telefono1 = '$telefono1', telefono2 = '$telefono2', genero = '$genero', localidad = '$localidad', email = '$correo' WHERE pacientes_id = '$pacientes_id'";
$query = $mysqli->query($update);

if($query){
    //HISTORIAL
    $historial_numero = historial();
    $estado_historial = "Modificar";
    $observacion_historial = "Se ha modificado el cliente: $nombre";
    $modulo = "Pacientes";
    
    $insert_historial = "INSERT INTO historial VALUES ('$historial_numero','0','0','$modulo','$pacientes_id','$usuario','0','$fecha','$estado_historial','$observacion_historial','$usuario','$fecha_registro')";
    $mysqli->query($insert_historial);

    $datos = array(
        0 => "Modificado",
        1 => "Registro Modificado Correctamente",
        2 => "success",
        3 => "btn-primary",
        4 => "",
        5 => "Registro",
        6 => "formulario_admision_clientes_editar",
        7 => "modal_admision_clientes_editar"
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