<?php
// agregarRegistro.php - Clientes + Muestra
session_start();
include "../funtions.php";

$mysqli = connect_mysqli();

if(isset($_POST['cliente_admision'])){
    $cliente_admision = ($_POST['cliente_admision'] == "") ? 0 : (int)$_POST['cliente_admision'];
} else {
    $cliente_admision = 0;
}

$nombre = cleanStringConverterCase($_POST['name']);
$apellido = "";
$identidad = $_POST['rtn'];

if(trim($nombre) == ''){
    echo json_encode(array(0 => "Error", 1 => "El nombre del cliente es requerido", 2 => "error", 3 => "btn-danger"));
    exit;
}

if($identidad == 0){
    $flag_identidad = true;
    while($flag_identidad){
        $d = rand(1, 99999999);
        $result_identidad = $mysqli->query("SELECT pacientes_id FROM pacientes WHERE identidad = '$d'");
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
$departamento_id = 0;
$municipio_id = 0;
$localidad = cleanString($_POST['direccion']);
$correo = strtolower(cleanString($_POST['correo']));
$fecha = date("Y-m-d");
$usuario = $_SESSION['colaborador_id'];
$estado = 1;
$fecha_registro = date("Y-m-d H:i:s");

if($cliente_admision == 0){
    $select = "SELECT pacientes_id FROM pacientes WHERE identidad = '$identidad' AND nombre = '$nombre' AND genero = '$genero'";
    $result = $mysqli->query($select);
    
    if($result->num_rows == 0){
        $pacientes_id = correlativo('pacientes_id', 'pacientes');
        $expediente = correlativo('expediente', 'pacientes');
        $insert = "INSERT INTO pacientes VALUES ('$pacientes_id','$expediente','$identidad','$nombre','$apellido','$genero','$telefono1','$telefono2','$fecha_nacimiento','$edad','$correo','$fecha','$departamento_id','$municipio_id','$localidad','0','0','$usuario','$estado','1','$fecha_registro')";
        $query = $mysqli->query($insert);
    } else {
        $row = $result->fetch_assoc();
        $cliente_admision_id = $row['pacientes_id'];
        $pacientes_id = $cliente_admision_id;
        $update = "UPDATE pacientes SET telefono1 = '$telefono1', edad = '$edad', email = '$correo' WHERE pacientes_id = '$cliente_admision_id'";
        $query = $mysqli->query($update);
    }
} else {
    $update = "UPDATE pacientes SET telefono1 = '$telefono1', edad = '$edad', email = '$correo' WHERE pacientes_id = '$cliente_admision'";
    $pacientes_id = $cliente_admision;
    $query = $mysqli->query($update);
}

if($query){
    // DATOS DE LA MUESTRA
    $servicio_id = 1;
    $colaborador_id = (isset($_POST['remitente']) && $_POST['remitente'] != "") ? (int)$_POST['remitente'] : 0;
    $tipo_muestra_id = (isset($_POST['tipo_muestra']) && $_POST['tipo_muestra'] != "") ? (int)$_POST['tipo_muestra'] : 0;
    $referencia = cleanString($_POST['referencia']);
    $estado_muestra = 0;
    $sitio_muestra = cleanString($_POST['sitio_muestra']);
    $diagnostico_clinico = cleanString($_POST['diagnostico_clinico']);
    $material_enviado = cleanString($_POST['material_enviado']);
    $datos_clinicos = cleanString($_POST['datos_clinicos']);
    $mostrar_datos_clinicos = (isset($_POST['mostrar_datos_clinicos']) && $_POST['mostrar_datos_clinicos'] != "") ? 1 : 2;
    $empresa = (isset($_POST['empresa']) && $_POST['empresa'] != "") ? (int)$_POST['empresa'] : 0;
    $hospital_clinica = (isset($_POST['hospital']) && $_POST['hospital'] != "") ? (int)$_POST['hospital'] : 0;
    $categoria_muestras = (isset($_POST['categoria']) && $_POST['categoria'] != "") ? (int)$_POST['categoria'] : 0;
    $producto = (isset($_POST['producto']) && $_POST['producto'] != "") ? (int)$_POST['producto'] : 0;

    // SECUENCIA
    $query_secuencia = "SELECT * FROM secuencias_muestas WHERE tipo_muestra_id = '$tipo_muestra_id' AND estado = 1";
    $result_secuencia = $mysqli->query($query_secuencia);
    $number = "";
    $secuencias_id = 0;
    $incremento = 0;
    $siguiente = 0;
    $flag = false;

    if($result_secuencia->num_rows > 0){
        $flag = true;
        $consulta = $result_secuencia->fetch_assoc();
        $prefijo = $consulta["prefijo"];
        $sufijo = $consulta["sufijo"];
        $relleno = $consulta["relleno"];
        $incremento = $consulta["incremento"];
        $siguiente = $consulta["siguiente"];
        $secuencias_id = $consulta["secuencias_id"];
        $numero = str_pad($siguiente, $relleno, "0", STR_PAD_LEFT);
        $año_actual = date("Y");
        $mes_actual = date("m");
        $dia_actual = date("d");
        $dia_semana = date("N");
        $prefijo = str_replace("@año_actual", $año_actual, $prefijo);
        $prefijo = str_replace("@mes_actual", $mes_actual, $prefijo);
        $prefijo = str_replace("@dia_actual", $dia_actual, $prefijo);
        $prefijo = str_replace("@dia_semana", $dia_semana, $prefijo);
        $number = $prefijo . $numero;
        $sufijo = str_replace("@año_actual", $año_actual, $sufijo);
        $sufijo = str_replace("@mes_actual", $mes_actual, $sufijo);
        $sufijo = str_replace("@dia_actual", $dia_actual, $sufijo);
        $sufijo = str_replace("@dia_semana", $dia_semana, $sufijo);
        $number .= $sufijo;
    }

    $pacientes_id_muestra = ($empresa != 0) ? $empresa : $pacientes_id;

    // LIMITE DE MUESTRAS
    $existe = 0;
    $tipoPacienteConsulta = "";
    $resultLimiteMuestras = $mysqli->query("SELECT limite FROM limite_muetras");

    if($resultLimiteMuestras->num_rows == 0){
        $existe = 0;
    } else {
        $valoresLimite = $resultLimiteMuestras->fetch_assoc();
        $limiteMuestras = $valoresLimite['limite'];
        
        if($empresa == 0){
            $resultMuestrasCliente = $mysqli->query("SELECT muestras_id FROM muestras WHERE pacientes_id = '$cliente_admision' AND estado = 0 AND tipo_muestra_id = '$tipo_muestra_id'");
            if($resultMuestrasCliente->num_rows >= $limiteMuestras){
                $existe = 1;
                $tipoPacienteConsulta = "el Cliente";
            }
        } else {
            $resultMuestrasEmpresa = $mysqli->query("SELECT mh.muestras_hospitales_id FROM muestras_hospitales AS mh INNER JOIN muestras AS m ON mh.muestras_id = m.muestras_id WHERE m.pacientes_id = '$empresa' AND mh.pacientes_id = '$cliente_admision' AND m.estado = 0 AND m.tipo_muestra_id = '$tipo_muestra_id'");
            if($resultMuestrasEmpresa->num_rows >= $limiteMuestras){
                $existe = 1;
                $tipoPacienteConsulta = "La empresa";
            }
        }
    }

    if($existe == 0){
        $muestras_id = correlativo('muestras_id', 'muestras');
        $insert_muestra = "INSERT INTO muestras VALUES ('$muestras_id','$pacientes_id_muestra','$secuencias_id','$servicio_id','$colaborador_id','$tipo_muestra_id','$number','$referencia','$fecha','$estado_muestra','$sitio_muestra','$diagnostico_clinico','$material_enviado','$datos_clinicos','$mostrar_datos_clinicos','$hospital_clinica','$categoria_muestras','$usuario','$fecha_registro')";
        $mysqli->query($insert_muestra);

        if($flag){
            $siguiente += $incremento;
            $mysqli->query("UPDATE secuencias_muestas SET siguiente = '$siguiente' WHERE secuencias_id = '$secuencias_id'");
        }

        // PREFACTURA
        $nombre_producto = "";
        $precio_venta = 0;
        $isv = 0;
        if($producto != 0){
            $result_producto = $mysqli->query("SELECT nombre, precio_venta, isv FROM productos WHERE productos_id = '$producto'");
            if($result_producto->num_rows > 0){
                $valores2 = $result_producto->fetch_assoc();
                $nombre_producto = $valores2['nombre'];
                $precio_venta = $valores2['precio_venta'];
                $isv = $valores2['isv'];
            }
        }

        // EMPRESA
        if($empresa != 0 && $pacientes_id != 0){
            $muestras_hospitales_id = correlativo('muestras_hospitales_id', 'muestras_hospitales');
            $mysqli->query("INSERT INTO muestras_hospitales VALUES ('$muestras_hospitales_id','$empresa','$pacientes_id','$muestras_id','$fecha','$usuario','$fecha_registro')");
        }

        // HISTORIAL CLIENTE
        $resultColaborador = $mysqli->query("SELECT CONCAT(nombre, ' ', apellido) AS colaborador FROM colaboradores WHERE colaborador_id = '$usuario'");
        $consultaColaborador = $resultColaborador->fetch_assoc();
        $NombreColaborador = $consultaColaborador['colaborador'];
        $historial_numero = historial();
        $mysqli->query("INSERT INTO historial VALUES ('$historial_numero','0','0','Clientes','$pacientes_id','$usuario','0','$fecha','Agregar','Se ha agregado un nuevo cliente: $nombre, por el usuario: $NombreColaborador','$usuario','$fecha_registro')");

        // HISTORIAL MUESTRA
        $historial_numero = historial();
        $mysqli->query("INSERT INTO historial VALUES ('$historial_numero','0','0','Muestras','$muestras_id','$usuario','0','$fecha','Agregar','Se ha agregado una nueva muestra con diagnóstico: $diagnostico_clinico','$usuario','$fecha_registro')");

        $datos = array(
            0 => "Almacenado",
            1 => "Registro Almacenado Correctamente",
            2 => "success",
            3 => "btn-primary",
            4 => "formulario_admision",
            5 => "Registro",
            6 => "formPacientesAdmision",
            7 => "modal_admision_clientes",
            8 => "",
            9 => "Guardar",
            10 => $muestras_id,
            11 => $producto,
            12 => $nombre_producto,
            13 => $precio_venta,
            14 => $isv,
            15 => 'Muestra'
        );
    } else {
        $datos = array(
            0 => "Error",
            1 => "Lo sentimos $tipoPacienteConsulta, cuenta con este tipo de muestra, por favor debe procesar por completo la factura antes de agregar otra muestra de este tipo.",
            2 => "error",
            3 => "btn-danger",
        );
    }
} else {
    $datos = array(
        0 => "Error",
        1 => "No se pudo almacenar este registro",
        2 => "error",
        3 => "btn-danger",
    );
}

echo json_encode($datos);
$mysqli->close();