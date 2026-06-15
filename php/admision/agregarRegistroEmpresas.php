<?php
// agregarRegistroEmpresas.php - Solo Empresas
session_start();
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

$nombre = cleanStringConverterCase($_POST['empresa']);
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

$fecha_nacimiento = date("Y-m-d");
$edad = 0;
$telefono1 = $_POST['telefono1'];
$telefono2 = "";
$genero = "";
$departamento_id = 0;
$municipio_id = 0;
$localidad = cleanString($_POST['direccion']);
$correo = strtolower(cleanString($_POST['correo']));
$fecha = date("Y-m-d");
$religion_id = 0;
$profesion_id = 0;
$paciente_tipo = 2;
$usuario = $_SESSION['colaborador_id'];
$estado = 1;
$fecha_registro = date("Y-m-d H:i:s");

//VERIFICAR SI EXISTE
$select = "SELECT pacientes_id FROM pacientes WHERE identidad = '$identidad' AND nombre = '$nombre' AND apellido = '$apellido' AND genero = '$genero'";
$result = $mysqli->query($select);

if($result->num_rows == 0){
    $pacientes_id = correlativo('pacientes_id', 'pacientes');
    $expediente = correlativo('expediente', 'pacientes');
    
    $insert = "INSERT INTO pacientes VALUES ('$pacientes_id','$expediente','$identidad','$nombre','$apellido','$genero','$telefono1','$telefono2','$fecha_nacimiento','$edad','$correo','$fecha','$departamento_id','$municipio_id','$localidad','$religion_id','$profesion_id','$usuario','$estado','$paciente_tipo','$fecha_registro')";
    $query = $mysqli->query($insert);

    if($query){
        //HISTORIAL
        $resultColaborador = $mysqli->query("SELECT CONCAT(nombre, ' ', apellido) AS colaborador FROM colaboradores WHERE colaborador_id = '$usuario'");
        $consultaColaborador = $resultColaborador->fetch_assoc();
        $NombreColaborador = $consultaColaborador['colaborador'];

        $historial_numero = historial();
        $estado_historial = "Agregar";
        $observacion_historial = "Se ha agregado una nueva empresa: $nombre, por el usuario: $NombreColaborador";
        $modulo = "Clientes";
        
        $insert_historial = "INSERT INTO historial VALUES ('$historial_numero','0','0','$modulo','$pacientes_id','$usuario','0','$fecha','$estado_historial','$observacion_historial','$usuario','$fecha_registro')";
        $mysqli->query($insert_historial);

        $datos = array(
            0 => "Almacenado",
            1 => "Registro Almacenado Correctamente",
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
            1 => "No se pudo almacenar este registro",
            2 => "error",
            3 => "btn-danger"
        );
    }
} else {
    $datos = array(
        0 => "Error",
        1 => "Lo sentimos este registro ya existe no se puede almacenar",
        2 => "error",
        3 => "btn-danger"
    );
}

echo json_encode($datos);
$mysqli->close();