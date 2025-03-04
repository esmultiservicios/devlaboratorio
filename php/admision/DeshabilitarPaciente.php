<?php
session_start();
include "../funtions.php";

// CONEXIÓN A DB
$mysqli = connect_mysqli();

// VALIDAR SI SE RECIBEN LOS DATOS
if (!isset($_POST['pacientes_id'], $_POST['comentario'], $_POST['estado'])) {
    echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
    exit;
}

$pacientes_id = $_POST['pacientes_id'];
$comentario = $_POST['comentario'];
$estado = $_POST['estado'];
$usuario = $_SESSION['colaborador_id'];  // ID del colaborador
$codigo = 1;  // Código puede ser un identificador según el tipo de cambio
$servicio_id = 1;  // Asume un valor de servicio o configúralo según tu lógica
$fecha = date("Y-m-d");  // Fecha del cambio
$status = ($estado == 1) ? "Habilitado" : "Inhabilitado";  // Estado textual

// Obtener el expediente del paciente
$query_exp = "SELECT expediente FROM pacientes WHERE pacientes_id = ?";
$stmt_exp = $mysqli->prepare($query_exp);

if ($stmt_exp) {
    $stmt_exp->bind_param("i", $pacientes_id);
    $stmt_exp->execute();
    $stmt_exp->store_result();
    if ($stmt_exp->num_rows > 0) {
        $stmt_exp->bind_result($expediente);
        $stmt_exp->fetch();
    } else {
        echo json_encode(["status" => "error", "message" => "Paciente no encontrado"]);
        exit;
    }
    $stmt_exp->close();
} else {
    echo json_encode(["status" => "error", "message" => "Error al consultar el expediente del paciente"]);
    exit;
}

// INVERSIÓN DEL ESTADO
$estado = $estado == 1 ? 0 : 1;

$servicio_id = 0;
$modulo = "Clientes";

// PREPARAR CONSULTA PARA ACTUALIZAR EL ESTADO DEL PACIENTE
$query = "UPDATE pacientes SET estado = ? WHERE pacientes_id = ?";
$stmt = $mysqli->prepare($query);

if ($stmt) {
    $stmt->bind_param("ii", $estado, $pacientes_id);
    if ($stmt->execute()) {
		$historial_numero = historial();

        // Inserción del historial
        $query_historial = "INSERT INTO historial (historial_id, pacientes_id, expediente, modulo, codigo, colaborador_id, servicio_id, fecha, status, observacion, usuario, fecha_registro)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt_historial = $mysqli->prepare($query_historial);
        if ($stmt_historial) {
            $stmt_historial->bind_param("iiisiiisssi", $historial_numero, $pacientes_id, $expediente, $modulo, $pacientes_id, $usuario, $servicio_id, $fecha, $status, $comentario, $usuario);
            if ($stmt_historial->execute()) {
                echo json_encode(["status" => "success", "message" => "Registro actualizado correctamente y historial guardado"]);
            } else {
                echo json_encode(["status" => "error", "message" => "No se pudo guardar el historial"]);
            }
            $stmt_historial->close();
        } else {
            echo json_encode(["status" => "error", "message" => "Error al preparar la consulta de historial"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "No se pudo actualizar el registro del paciente"]);
    }
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Error en la preparación de la consulta"]);
}

$mysqli->close();