<?php
// addFacturaporUsuario.php
session_start();
include '../funtions.php';

// CONEXION A DB
$mysqli = connect_mysqli();

// Iniciar transacción
$mysqli->begin_transaction();

try {
    // Obtener datos del POST
    $facturas_id   = $_POST['facturas_id'];
    $pacientes_id  = $_POST['pacientes_id'];
    $fecha         = date('Y-m-d');
    $colaborador_id = $_POST['colaborador_id'];
    $servicio_id    = $_POST['servicio_id'];
    $notes          = cleanStringStrtolower($_POST['notes']);
    $usuario        = $_SESSION['colaborador_id'];
    $empresa_id     = $_SESSION['empresa_id'];
    $fecha_registro = date('Y-m-d H:i:s');
    $fact_eval      = $_POST['fact_eval'] ?? 1;
    $activo         = 2;
    $estado         = 4;  // ESTADO FACTURA CREDITO
    $importe        = 0;
    $tipo           = '';

    // Determinar tipo de factura
    if (isset($_POST['facturas_activo'])) {
        $tipo_factura = ($_POST['facturas_activo'] == '') ? 2 : $_POST['facturas_activo'];
        $tipo = ($_POST['facturas_activo'] == '') ? 'FacturacionCredito' : 'Facturacion';
    } else {
        $tipo_factura = 2;
        $tipo = 'FacturacionCredito';
    }

    // 1=Factura Electrónica (contado), 4=Proforma (crédito)
    $documento_tipo = ($tipo_factura == 1) ? 1 : 4;

    // Validar datos requeridos
    if (empty($pacientes_id) || empty($colaborador_id) || empty($servicio_id)) {
        throw new Exception("Lo sentimos, el Paciente, Profesional o Servicio no pueden quedar en blanco");
    }

    // OBTENER NÚMERO DE FACTURA USANDO LA NUEVA LÓGICA
    $numeroFactura = obtenerNumeroFactura($mysqli, $empresa_id, $documento_tipo);
    if ($numeroFactura['error']) {
        throw new Exception($numeroFactura['mensaje']);
    }

    $secuencia_facturacion_id = $numeroFactura['data']['secuencia_facturacion_id'];
    $numero  = $numeroFactura['data']['numero'];
    $prefijo = $numeroFactura['data']['prefijo'];
    $relleno = $numeroFactura['data']['relleno'];
    $no_factura = $prefijo . str_pad($numero, $relleno, "0", STR_PAD_LEFT);

    // VALIDAR DETALLES
    if (
        !isset($_POST['productName'])
        || empty($_POST['productName'][0])
        || empty($_POST['quantity'][0])
        || empty($_POST['price'][0])
    ) {
        throw new Exception("El detalle de la factura no puede quedar vacío");
    }

    // ACTUALIZAR FACTURA PRINCIPAL CON LA SECUENCIA CORRECTA
    $update = "UPDATE facturas SET
                fecha = ?,
                tipo_factura = ?,
                number = ?,
                secuencia_facturacion_id = ?,
                estado = ?,
                notas = ?,
                colaborador_id = ?,
                servicio_id = ?
            WHERE facturas_id = ?";

    $stmt = $mysqli->prepare($update);
    // s i i i i s i i i
    $stmt->bind_param(
        'siiiisiii',
        $fecha,
        $tipo_factura,
        $numero,
        $secuencia_facturacion_id,
        $estado,
        $notes,
        $colaborador_id,
        $servicio_id,
        $facturas_id
    );

    if (!$stmt->execute()) {
        // Registrar número como fallido si no se pudo actualizar
        registrarNumeroFallido($mysqli, $empresa_id, $documento_tipo, $numero);
        throw new Exception("Error al actualizar la factura: " . $stmt->error);
    }
    $stmt->close();

    // OBTENER ISV GENERAL
    $query_isv = "SELECT nombre FROM isv LIMIT 1";
    $result_isv = $mysqli->query($query_isv);
    $porcentajeISV = ($result_isv && $result_isv->num_rows > 0) ? $result_isv->fetch_assoc()['nombre'] : 0;
    $porcentaje_isv = $porcentajeISV / 100;

    // LIMPIAR DETALLES EXISTENTES PARA EVITAR DUPLICADOS
    $delete = "DELETE FROM facturas_detalle WHERE facturas_id = ?";
    $stmt = $mysqli->prepare($delete);
    $stmt->bind_param("i", $facturas_id);
    $stmt->execute();
    $stmt->close();

    // PROCESAR DETALLES
    $total_valor = 0;
    $descuentos  = 0;
    $isv_neto    = 0;

    $countItems = count($_POST['productName']);
    for ($i = 0; $i < $countItems; $i++) {
        if (
            empty($_POST['productoID'][$i]) ||
            empty($_POST['productName'][$i]) ||
            $_POST['quantity'][$i] === '' ||
            $_POST['price'][$i] === ''
        ) {
            continue;
        }

        $productoID = (int)$_POST['productoID'][$i];
        $quantity   = (int)$_POST['quantity'][$i];
        $price      = (float)$_POST['price'][$i];
        $discount   = (float)($_POST['discount'][$i] ?? 0);

        // CALCULAR ISV PARA ESTE PRODUCTO
        $query_isv_activo = "SELECT isv FROM productos WHERE productos_id = ?";
        $stmt = $mysqli->prepare($query_isv_activo);
        $stmt->bind_param("i", $productoID);
        $stmt->execute();
        $result = $stmt->get_result();
        $aplica_isv = ($result && $result->num_rows > 0) ? (int)$result->fetch_assoc()['isv'] : 0;
        $stmt->close();

        $isv_valor = ($aplica_isv == 1) ? ($price * $quantity * $porcentaje_isv) : 0.0;

        // INSERTAR DETALLE (lista de columnas explícita)
        $facturas_detalle_id = correlativo("facturas_detalle_id", "facturas_detalle");
        $insert_detalle = "INSERT INTO facturas_detalle
            (facturas_detalle_id, facturas_id, productos_id, cantidad, precio, isv_valor, descuento)
            VALUES (?,?,?,?,?,?,?)";
        $stmt = $mysqli->prepare($insert_detalle);
        // i i i i d d d
        $stmt->bind_param(
            "iiiiddd",
            $facturas_detalle_id,
            $facturas_id,
            $productoID,
            $quantity,
            $price,
            $isv_valor,
            $discount
        );
        if (!$stmt->execute()) {
            throw new Exception("Error al procesar detalle de factura: " . $stmt->error);
        }
        $stmt->close();

        // PROCESAR INVENTARIO SI ES PRODUCTO Y FACTURA DE CONTADO
        if ($tipo_factura == 1) {
            $query_categoria = "SELECT cp.nombre AS categoria
                                FROM productos AS p
                                INNER JOIN categoria_producto AS cp ON p.categoria_producto_id = cp.categoria_producto_id
                                WHERE p.productos_id = ?
                                GROUP BY p.productos_id";
            $stmt = $mysqli->prepare($query_categoria);
            $stmt->bind_param("i", $productoID);
            $stmt->execute();
            $result = $stmt->get_result();
            $rowCat = $result ? $result->fetch_assoc() : null;
            $stmt->close();

            $esProducto = ($rowCat && $rowCat['categoria'] === "Producto");
            if ($esProducto) {
                // ACTUALIZAR STOCK
                $update_producto = "UPDATE productos SET cantidad = cantidad - ? WHERE productos_id = ?";
                $stmt = $mysqli->prepare($update_producto);
                $stmt->bind_param("ii", $quantity, $productoID);
                $stmt->execute();
                $stmt->close();

                // REGISTRAR MOVIMIENTO
                $query_saldo = "SELECT saldo FROM movimientos WHERE productos_id = ? ORDER BY movimientos_id DESC LIMIT 1";
                $stmt = $mysqli->prepare($query_saldo);
                $stmt->bind_param("i", $productoID);
                $stmt->execute();
                $result = $stmt->get_result();
                $ultimoSaldo = ($result && $result->num_rows > 0) ? (int)$result->fetch_assoc()['saldo'] : 0;
                $stmt->close();

                $saldo = $ultimoSaldo - $quantity;

                $movimientos_id = correlativo("movimientos_id", "movimientos");
                $doc_texto      = "Factura " . $facturas_id;
                $cantidad_entrada = 0;
                $cantidad_salida  = $quantity;

                // Tabla MOVIMIENTOS (7 columnas)
                $insert_movimiento = "INSERT INTO movimientos
                    (movimientos_id, productos_id, documento, cantidad_entrada, cantidad_salida, saldo, fecha_registro)
                    VALUES (?,?,?,?,?,?,?)";
                $stmt = $mysqli->prepare($insert_movimiento);
                // i i s i i i s
                $stmt->bind_param(
                    "iisiiis",
                    $movimientos_id,
                    $productoID,
                    $doc_texto,
                    $cantidad_entrada,
                    $cantidad_salida,
                    $saldo,
                    $fecha_registro
                );
                if (!$stmt->execute()) {
                    throw new Exception("Error al registrar movimiento de inventario: " . $stmt->error);
                }
                $stmt->close();
            }
        }

        // CALCULAR TOTALES
        $subtotal     = $price * $quantity;
        $total_valor += $subtotal;
        $descuentos  += $discount;
        $isv_neto    += $isv_valor;
    }

    // CALCULAR TOTAL FINAL
    $total_despues_isv = ($total_valor + $isv_neto) - $descuentos;

    // ACTUALIZAR IMPORTE FACTURA
    $update = "UPDATE facturas SET importe = ?, usuario = ? WHERE facturas_id = ?";
    $stmt = $mysqli->prepare($update);
    $stmt->bind_param("dii", $total_despues_isv, $usuario, $facturas_id);
    if (!$stmt->execute()) {
        throw new Exception("Error al actualizar importe de factura: " . $stmt->error);
    }
    $stmt->close();

    // REGISTRAR CUENTA POR COBRAR (SOLO SI NO EXISTE)
    $query_cxc = "SELECT cobrar_clientes_id FROM cobrar_clientes WHERE facturas_id = ?";
    $stmt = $mysqli->prepare($query_cxc);
    $stmt->bind_param("i", $facturas_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if (!$result || $result->num_rows == 0) {
        $cobrar_clientes_id = correlativo("cobrar_clientes_id", "cobrar_clientes");
        $estado_cxc = 1; // Pendiente de Cobrar

        // Tabla COBRAR_CLIENTES (9 columnas; campo monetario es 'saldo')
        $insert_cxc = "INSERT INTO cobrar_clientes
            (cobrar_clientes_id, pacientes_id, facturas_id, fecha, saldo, estado, usuario, empresa_id, fecha_registro)
            VALUES (?,?,?,?,?,?,?,?,?)";
        $stmt = $mysqli->prepare($insert_cxc);
        // i i i s d i i i s
        $stmt->bind_param(
            "iiisdiiss",
            $cobrar_clientes_id,
            $pacientes_id,
            $facturas_id,
            $fecha,
            $total_despues_isv,
            $estado_cxc,
            $usuario,
            $empresa_id,
            $fecha_registro
        );
        if (!$stmt->execute()) {
            throw new Exception("Error al registrar cuenta por cobrar: " . $stmt->error);
        }
        $stmt->close();
    }

    // CONFIRMAR TRANSACCIÓN
    $mysqli->commit();

    $datos = array(
        0 => 'Almacenado',
        1 => 'Registro Almacenado Correctamente',
        2 => 'success',
        3 => 'btn-primary',
        4 => 'formulario_facturacion',
        5 => 'Registro',
        6 => $tipo,
        7 => '',
        8 => $facturas_id,
    );

} catch (Exception $e) {
    // REVERTIR TRANSACCIÓN
    $mysqli->rollback();

    $datos = array(
        0 => 'Error',
        1 => $e->getMessage(),
        2 => 'error',
        3 => 'btn-danger',
        4 => '',
        5 => '',
    );
}

echo json_encode($datos);


/**
 * Registra un número de factura fallido para su posible reutilización
 */
function registrarNumeroFallido($conexion, $empresa_id, $documento_id, $numero) {
    try {
        $insert = "INSERT INTO secuencia_factura_fallida (empresa_id, documento_id, numero, fecha_registro)
                   VALUES (?, ?, ?, NOW())";
        $stmt = $conexion->prepare($insert);
        $stmt->bind_param("iii", $empresa_id, $documento_id, $numero);
        $stmt->execute();
        $stmt->close();
        return true;
    } catch (Exception $e) {
        error_log("Error al registrar número fallido: " . $e->getMessage());
        return false;
    }
}