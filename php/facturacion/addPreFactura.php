<?php
// addPrefactura.php
session_start();
include "../funtions.php";

header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 0);
error_reporting(E_ALL);

$mysqli = connect_mysqli();
$mysqli->set_charset("utf8");

$facturas_id_creada = 0;

try {
    if (!isset($_SESSION['colaborador_id']) || !isset($_SESSION['empresa_id'])) {
        throw new Exception("La sesión expiró. Por favor, inicie sesión nuevamente.");
    }

    $pacientes_id = isset($_POST['pacientes_id']) ? (int)$_POST['pacientes_id'] : 0;
    $muestras_id = isset($_POST['muestras_id']) ? (int)$_POST['muestras_id'] : 0;
    $fecha = isset($_POST['fecha']) && $_POST['fecha'] != '' ? $_POST['fecha'] : date('Y-m-d');
    $colaborador_id = isset($_POST['colaborador_id']) ? (int)$_POST['colaborador_id'] : 0;
    $servicio_id = isset($_POST['servicio_id']) ? (int)$_POST['servicio_id'] : 0;
    $notes = isset($_POST['notes']) ? cleanStringStrtolower($_POST['notes']) : '';
    $usuario = (int)$_SESSION['colaborador_id'];
    $fecha_registro = date("Y-m-d H:i:s");
    $estado = 1;
    $numero = 0;
    $secuencia_facturacion_id = 1;
    $cierre = 2;
    $tipo = "";
    $empresa_id = (int)$_SESSION['empresa_id'];

    if (isset($_POST['facturas_activo'])) {
        if ($_POST['facturas_activo'] == "") {
            $tipo_factura = 2;
            $tipo = "FacturacionCredito";
        } else {
            $tipo_factura = (int)$_POST['facturas_activo'];
            $tipo = "Facturacion";
        }
    } else {
        $tipo_factura = 2;
        $tipo = "FacturacionCredito";
    }

    if ($pacientes_id <= 0 || $colaborador_id <= 0 || $servicio_id <= 0) {
        throw new Exception("Lo sentimos, el Paciente, Profesional o Servicio no pueden quedar en blanco, por favor corregir.");
    }

    $porcentaje_isv = obtenerPorcentajeISVPrefactura($mysqli);
    $detalles = prepararDetallesPrefactura($mysqli, $_POST, $porcentaje_isv);

    if (count($detalles) <= 0) {
        throw new Exception("El detalle de la factura no puede quedar vacío.");
    }

    $total_valor = 0;
    $descuentos = 0;
    $isv_neto = 0;

    foreach ($detalles as $detalle) {
        $total_valor += ($detalle['precio'] * $detalle['cantidad']);
        $descuentos += $detalle['descuento'];
        $isv_neto += $detalle['isv_valor'];
    }

    $total_despues_isv = round(($total_valor + $isv_neto) - $descuentos, 2);

    if ($total_despues_isv < 0) {
        throw new Exception("El total de la prefactura no puede ser negativo.");
    }

    $mysqli->begin_transaction();

    $facturas_id = correlativo("facturas_id", "facturas");
    $facturas_id_creada = $facturas_id;

    $insert_factura = "INSERT INTO facturas
        (facturas_id, secuencia_facturacion_id, muestras_id, number, tipo_factura, pacientes_id, colaborador_id, servicio_id, importe, notas, fecha, estado, cierre, usuario, empresa_id, fecha_registro)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmtFactura = $mysqli->prepare($insert_factura);

    if (!$stmtFactura) {
        throw new Exception("Error al preparar registro de prefactura: " . $mysqli->error);
    }

    $stmtFactura->bind_param(
        "iiiiiiiidssiiiis",
        $facturas_id,
        $secuencia_facturacion_id,
        $muestras_id,
        $numero,
        $tipo_factura,
        $pacientes_id,
        $colaborador_id,
        $servicio_id,
        $total_despues_isv,
        $notes,
        $fecha,
        $estado,
        $cierre,
        $usuario,
        $empresa_id,
        $fecha_registro
    );

    if (!$stmtFactura->execute()) {
        $errorStmt = $stmtFactura->error;
        $stmtFactura->close();
        throw new Exception("No se pudo almacenar este registro: " . $errorStmt);
    }

    $stmtFactura->close();

    $resultMaxDetalle = $mysqli->query("SELECT IFNULL(MAX(facturas_detalle_id), 0) AS max_id FROM facturas_detalle");

    if (!$resultMaxDetalle) {
        throw new Exception("Error al obtener correlativo del detalle: " . $mysqli->error);
    }

    $rowMaxDetalle = $resultMaxDetalle->fetch_assoc();
    $siguienteDetalleID = ((int)$rowMaxDetalle['max_id']) + 1;

    $insert_detalle = "INSERT INTO facturas_detalle
        (facturas_detalle_id, facturas_id, productos_id, cantidad, precio, isv_valor, descuento)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmtDetalle = $mysqli->prepare($insert_detalle);

    if (!$stmtDetalle) {
        throw new Exception("Error al preparar detalle de prefactura: " . $mysqli->error);
    }

    foreach ($detalles as $detalle) {
        $facturas_detalle_id = $siguienteDetalleID++;
        $productoID = (int)$detalle['productos_id'];
        $cantidad = (int)$detalle['cantidad'];
        $precio = (float)$detalle['precio'];
        $isv_valor = (float)$detalle['isv_valor'];
        $descuento = (float)$detalle['descuento'];

        $stmtDetalle->bind_param(
            "iiiiddd",
            $facturas_detalle_id,
            $facturas_id,
            $productoID,
            $cantidad,
            $precio,
            $isv_valor,
            $descuento
        );

        if (!$stmtDetalle->execute()) {
            $errorStmt = $stmtDetalle->error;
            $stmtDetalle->close();
            throw new Exception("Error al registrar detalle de prefactura: " . $errorStmt);
        }
    }

    $stmtDetalle->close();

    $query_total_detalle = "SELECT 
                                ROUND(IFNULL(SUM((cantidad * precio) + isv_valor - descuento), 0), 2) AS total_detalle,
                                COUNT(*) AS cantidad_lineas
                            FROM facturas_detalle
                            WHERE facturas_id = ?";

    $stmtTotal = $mysqli->prepare($query_total_detalle);

    if (!$stmtTotal) {
        throw new Exception("Error al preparar validación final de total: " . $mysqli->error);
    }

    $stmtTotal->bind_param("i", $facturas_id);
    $stmtTotal->execute();
    $resultTotal = $stmtTotal->get_result();
    $rowTotal = $resultTotal ? $resultTotal->fetch_assoc() : null;
    $stmtTotal->close();

    if (!$rowTotal) {
        throw new Exception("No se pudo validar el total final del detalle.");
    }

    $total_detalle_bd = round((float)$rowTotal['total_detalle'], 2);
    $cantidad_lineas_bd = (int)$rowTotal['cantidad_lineas'];

    if ($cantidad_lineas_bd != count($detalles)) {
        throw new Exception(
            "La prefactura no se guardó completa. Productos enviados: " . count($detalles) .
            ", productos guardados: " . $cantidad_lineas_bd . "."
        );
    }

    if (abs($total_detalle_bd - $total_despues_isv) > 0.01) {
        throw new Exception(
            "La prefactura no cuadra. Encabezado: " . number_format($total_despues_isv, 2, '.', '') .
            ", detalle: " . number_format($total_detalle_bd, 2, '.', '') . "."
        );
    }

    $mysqli->commit();

    $datos = array(
        0  => "Almacenado",
        1  => "Registro Almacenado Correctamente",
        2  => "success",
        3  => "btn-primary",
        4  => "formulario_facturacion",
        5  => "Registro",
        6  => "FacturaAtenciones",
        7  => "",
        8  => $facturas_id,
        9  => number_format($total_despues_isv, 2, '.', ''),
        10 => count($detalles)
    );

} catch (Exception $e) {
    $mysqli->rollback();

    if ($facturas_id_creada > 0) {
        try {
            $stmtCleanDetalle = $mysqli->prepare("DELETE FROM facturas_detalle WHERE facturas_id = ?");
            if ($stmtCleanDetalle) {
                $stmtCleanDetalle->bind_param("i", $facturas_id_creada);
                $stmtCleanDetalle->execute();
                $stmtCleanDetalle->close();
            }

            $stmtCleanFactura = $mysqli->prepare("DELETE FROM facturas WHERE facturas_id = ?");
            if ($stmtCleanFactura) {
                $stmtCleanFactura->bind_param("i", $facturas_id_creada);
                $stmtCleanFactura->execute();
                $stmtCleanFactura->close();
            }
        } catch (Exception $cleanupException) {
            error_log("Error limpiando prefactura fallida: " . $cleanupException->getMessage());
        }
    }

    error_log("Error addPrefactura.php: " . $e->getMessage());

    $datos = array(
        0 => "Error",
        1 => $e->getMessage(),
        2 => "error",
        3 => "btn-danger",
        4 => "",
        5 => ""
    );
}

echo json_encode($datos);
exit;


function obtenerPorcentajeISVPrefactura($conexion) {
    $query = "SELECT nombre FROM isv LIMIT 1";
    $result = $conexion->query($query);

    if (!$result) {
        throw new Exception("Error al obtener el ISV general: " . $conexion->error);
    }

    if ($result->num_rows <= 0) {
        return 0;
    }

    $row = $result->fetch_assoc();
    return ((float)$row['nombre']) / 100;
}


function prepararDetallesPrefactura($conexion, $post, $porcentaje_isv) {
    if (
        !isset($post['productoID']) ||
        !isset($post['productName']) ||
        !isset($post['quantity']) ||
        !isset($post['price'])
    ) {
        throw new Exception("El detalle de la prefactura no fue recibido correctamente.");
    }

    if (
        !is_array($post['productoID']) ||
        !is_array($post['productName']) ||
        !is_array($post['quantity']) ||
        !is_array($post['price'])
    ) {
        throw new Exception("El detalle de la prefactura tiene un formato inválido.");
    }

    $productoIDArray = $post['productoID'];
    $productNameArray = $post['productName'];
    $quantityArray = $post['quantity'];
    $priceArray = $post['price'];
    $discountArray = isset($post['discount']) && is_array($post['discount']) ? $post['discount'] : array();

    $countProductos = count($productoIDArray);
    $countNombres = count($productNameArray);
    $countCantidad = count($quantityArray);
    $countPrecios = count($priceArray);

    if ($countProductos <= 0) {
        throw new Exception("El detalle de la prefactura no puede quedar vacío.");
    }

    if (
        $countProductos != $countNombres ||
        $countProductos != $countCantidad ||
        $countProductos != $countPrecios
    ) {
        throw new Exception("El detalle de la prefactura llegó incompleto. Por favor, vuelva a cargar los productos antes de guardar.");
    }

    $detalles = array();

    for ($i = 0; $i < $countProductos; $i++) {
        $linea = $i + 1;

        $productoID = isset($productoIDArray[$i]) ? (int)$productoIDArray[$i] : 0;
        $productName = isset($productNameArray[$i]) ? trim($productNameArray[$i]) : '';
        $quantityRaw = isset($quantityArray[$i]) ? trim((string)$quantityArray[$i]) : '';
        $priceRaw = isset($priceArray[$i]) ? trim((string)$priceArray[$i]) : '';
        $discountRaw = isset($discountArray[$i]) ? trim((string)$discountArray[$i]) : '0';

        $filaVacia = ($productoID <= 0 && $productName === '' && $quantityRaw === '' && $priceRaw === '');

        if ($filaVacia) {
            continue;
        }

        if ($productoID <= 0) {
            throw new Exception("Hay un producto sin código interno en la línea " . $linea . ".");
        }

        if ($productName === '') {
            throw new Exception("Hay un producto sin nombre en la línea " . $linea . ".");
        }

        if ($quantityRaw === '' || !is_numeric($quantityRaw)) {
            throw new Exception("La cantidad del producto " . $productName . " no es válida.");
        }

        if ($priceRaw === '' || !is_numeric($priceRaw)) {
            throw new Exception("El precio del producto " . $productName . " no es válido.");
        }

        if ($discountRaw === '') {
            $discountRaw = '0';
        }

        if (!is_numeric($discountRaw)) {
            throw new Exception("El descuento del producto " . $productName . " no es válido.");
        }

        $quantity = (int)$quantityRaw;
        $price = (float)$priceRaw;
        $discount = (float)$discountRaw;

        if ($quantity <= 0) {
            throw new Exception("La cantidad del producto " . $productName . " debe ser mayor a cero.");
        }

        if ($price < 0) {
            throw new Exception("El precio del producto " . $productName . " no puede ser negativo.");
        }

        if ($discount < 0) {
            throw new Exception("El descuento del producto " . $productName . " no puede ser negativo.");
        }

        $subtotal = $price * $quantity;

        if ($discount > $subtotal) {
            throw new Exception("El descuento del producto " . $productName . " no puede ser mayor al subtotal.");
        }

        $query_producto = "SELECT productos_id, isv FROM productos WHERE productos_id = ? LIMIT 1";
        $stmt = $conexion->prepare($query_producto);

        if (!$stmt) {
            throw new Exception("Error al preparar validación de producto: " . $conexion->error);
        }

        $stmt->bind_param("i", $productoID);
        $stmt->execute();
        $resultProducto = $stmt->get_result();

        if (!$resultProducto || $resultProducto->num_rows <= 0) {
            $stmt->close();
            throw new Exception("El producto " . $productName . " no existe o fue eliminado.");
        }

        $rowProducto = $resultProducto->fetch_assoc();
        $aplica_isv = (int)$rowProducto['isv'];
        $stmt->close();

        $isv_valor = ($aplica_isv == 1) ? round($subtotal * $porcentaje_isv, 2) : 0.00;

        $detalles[] = array(
            'productos_id' => $productoID,
            'nombre' => $productName,
            'cantidad' => $quantity,
            'precio' => $price,
            'isv_valor' => $isv_valor,
            'descuento' => $discount
        );
    }

    if (count($detalles) <= 0) {
        throw new Exception("No hay productos válidos para guardar en la prefactura.");
    }

    return $detalles;
}