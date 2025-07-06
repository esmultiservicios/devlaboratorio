<?php
session_start();
include "../funtions.php";

// CONEXION A DB
$mysqli = connect_mysqli();

// Iniciar transacción
$mysqli->begin_transaction();

try {
    // 1. VALIDACIONES BÁSICAS DE ENTRADA
    // --------------------------------------------------
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método de solicitud no válido");
    }

    // 2. VALIDAR CAMPOS OBLIGATORIOS
    // --------------------------------------------------
    $campos_requeridos = [
        'pacientes_id' => 'ID de Paciente',
        'colaborador_id' => 'ID de Colaborador',
        'servicio_id' => 'ID de Servicio',
        'fecha' => 'Fecha',
        'productName' => 'Productos',
        'quantity' => 'Cantidades',
        'price' => 'Precios'
    ];

    $errores = [];
    $datos = [];

    foreach ($campos_requeridos as $campo => $nombre) {
        if (!isset($_POST[$campo])) {
            $errores[] = "El campo {$nombre} es requerido";
        } else {
            if (is_array($_POST[$campo])) {
                $datos[$campo] = $_POST[$campo];
            } else {
                $datos[$campo] = trim($_POST[$campo]);
            }
        }
    }

    if (!empty($errores)) {
        throw new Exception(implode("\n", $errores));
    }

    // 3. VALIDACIÓN DE TIPOS DE DATOS Y VALORES
    // --------------------------------------------------
    $pacientes_id = (int)$datos['pacientes_id'];
    $colaborador_id = (int)$datos['colaborador_id'];
    $servicio_id = (int)$datos['servicio_id'];
    $muestras_id = isset($datos['muestras_id']) ? (int)$datos['muestras_id'] : 0;
    $fecha = $datos['fecha'];
    $notes = cleanStringStrtolower($datos['notes'] ?? '');
    $usuario = (int)$_SESSION['colaborador_id'];
    $empresa_id = (int)$_SESSION['empresa_id'];
    
    // SOLUCIÓN DEFINITIVA PARA server_customers_id
    $server_customers_id = isset($_SESSION['server_customers_id']) ? (int)$_SESSION['server_customers_id'] : 0;

    // Validar IDs
    if ($pacientes_id <= 0 || $colaborador_id <= 0 || $servicio_id <= 0) {
        throw new Exception("IDs de paciente, colaborador o servicio no válidos");
    }

    // 4. VALIDAR DETALLES DE FACTURA
    // --------------------------------------------------
    $importe_total = 0;
    $detalles_validados = [];

    foreach ($datos['productName'] as $index => $producto) {
        $cantidad = (float)$datos['quantity'][$index];
        $precio = (float)$datos['price'][$index];
        $descuento = isset($datos['discount'][$index]) ? (float)$datos['discount'][$index] : 0;
        $isv = isset($datos['isv'][$index]) ? (float)$datos['isv'][$index] : 0;

        $subtotal = ($cantidad * $precio) - $descuento + $isv;
        $importe_total += $subtotal;

        $detalles_validados[] = [
            'producto' => $producto,
            'cantidad' => $cantidad,
            'precio' => $precio,
            'descuento' => $descuento,
            'isv' => $isv,
            'subtotal' => $subtotal
        ];
    }

    // 5. MANEJO DE SECUENCIA (sin actualización)
    // --------------------------------------------------
    $tipo_factura = isset($datos['facturas_activo']) ? ($datos['facturas_activo'] == "" ? 2 : (int)$datos['facturas_activo']) : 2;
    $documento_id = 1;

    $query_secuencia = "SELECT secuencia_facturacion_id FROM secuencia_facturacion 
                       WHERE activo = 1 AND empresa_id = ? AND documento_id = ? LIMIT 1";
    
    $stmt = $mysqli->prepare($query_secuencia);
    $stmt->bind_param("ii", $empresa_id, $documento_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows == 0) {
        throw new Exception("No se encontró secuencia de facturación activa");
    }
    
    $secuencia = $result->fetch_assoc();
    $secuencia_facturacion_id = (int)$secuencia['secuencia_facturacion_id'];
    $stmt->close();

    // 6. INSERTAR FACTURA (CON NÚMERO 0)
    // --------------------------------------------------
    $facturas_id = correlativo("facturas_id", "facturas");
    $estado = 1; // Borrador
    $cierre = 2; // No cerrado
    $numero = 0; // Número de factura fijado a 0 como solicitaste
    $fecha_registro = date('Y-m-d H:i:s');  // Formato DATETIME de MySQL

    // 6. INSERTAR FACTURA (AHORA SÍ CON LAS 16 COLUMNAS)
    $insert = "INSERT INTO facturas (
        facturas_id, secuencia_facturacion_id, muestras_id, number, tipo_factura,
        pacientes_id, colaborador_id, servicio_id, importe, notas, fecha,
        estado, cierre, usuario, empresa_id, fecha_registro
    ) VALUES (
        ?, ?, ?, ?, ?, 
        ?, ?, ?, ?, ?, 
        ?, ?, ?, ?, ?, ?
    )";

    $stmt = $mysqli->prepare($insert);
    if (!$stmt) {
        throw new Exception("Error al preparar consulta: " . $mysqli->error);
    }

    // Ajustado a 16 parámetros (el tipo de fecha_registro es 's' por ser DATETIME)
    $stmt->bind_param(
        "iiiisiiiidsiiiss", // 16 caracteres = 16 parámetros (añadí una 's' extra al final)
        $facturas_id, $secuencia_facturacion_id, $muestras_id, $numero, $tipo_factura,
        $pacientes_id, $colaborador_id, $servicio_id, $importe_total, $notes, $fecha,
        $estado, $cierre, $usuario, $empresa_id, $fecha_registro
    );

    if (!$stmt->execute()) {
        throw new Exception("Error al insertar factura: " . $stmt->error);
    }
    $stmt->close();

    // 7. INSERTAR DETALLES
    // --------------------------------------------------
    $facturas_detalle_id = correlativo("facturas_detalle_id", "facturas_detalle");

    $insert_detalle = "INSERT INTO facturas_detalle (
        facturas_detalle_id, facturas_id, productos_id, cantidad, precio, descuento, isv_valor
    ) VALUES (?, ?, ?, ?, ?, ?, ?)";

    foreach ($detalles_validados as $detalle) {
        $producto_id = 0; // Obtener el ID real si es necesario
        
        $stmt = $mysqli->prepare($insert_detalle);
        $stmt->bind_param(
            "iiiiddd", // Ajustado a 7 parámetros (eliminé el último parámetro para producto_isv)
            $facturas_detalle_id, // Agregado este campo que faltaba
            $facturas_id,
            $producto_id,
            $detalle['cantidad'],
            $detalle['precio'],
            $detalle['descuento'],
            $detalle['isv'] // isv_valor en la tabla
        );
        
        if(!$stmt->execute()) {
            throw new Exception("Error al guardar detalle: ".$stmt->error);
        }
        $stmt->close();
    }

    $mysqli->commit();

    $datos = [
        0 => "Almacenado",
        1 => "Factura generada correctamente",
        2 => "success",
        3 => "btn-primary",
        4 => "formulario_facturacion",
        5 => "Registro",
        6 => "FacturaAtenciones",
        7 => $facturas_id
    ];

} catch (Exception $e) {
    $mysqli->rollback();
    
    $datos = [
        0 => "Error",
        1 => $e->getMessage(),
        2 => "error",
        3 => "btn-danger",
        4 => "",
        5 => ""
    ];
}

header('Content-Type: application/json');
echo json_encode($datos);