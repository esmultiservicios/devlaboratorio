<?php
// addFacturaporUsuario.php
session_start();
include '../funtions.php';

header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 0);
error_reporting(E_ALL);

$mysqli = connect_mysqli();
$mysqli->set_charset("utf8");

$numero = 0;
$documento_tipo = 0;
$empresa_id = 0;
$numeroFacturaObtenido = false;
$facturaActualizada = false;
$detalleFacturaTocado = false;
$lockSecuenciaNombre = '';
$lockSecuenciaAdquirido = false;
$numeroFacturaData = null;

try {
    if (!isset($_SESSION['colaborador_id']) || !isset($_SESSION['empresa_id'])) {
        throw new Exception("La sesión expiró. Por favor, inicie sesión nuevamente.");
    }

    $facturas_id    = isset($_POST['facturas_id']) ? (int)$_POST['facturas_id'] : 0;
    $pacientes_id   = isset($_POST['pacientes_id']) ? (int)$_POST['pacientes_id'] : 0;
    $fecha          = date('Y-m-d');
    $colaborador_id = isset($_POST['colaborador_id']) ? (int)$_POST['colaborador_id'] : 0;
    $servicio_id    = isset($_POST['servicio_id']) ? (int)$_POST['servicio_id'] : 0;
    $notes          = isset($_POST['notes']) ? cleanStringStrtolower($_POST['notes']) : '';
    $usuario        = (int)$_SESSION['colaborador_id'];
    $empresa_id     = (int)$_SESSION['empresa_id'];
    $fecha_registro = date('Y-m-d H:i:s');
    $estado         = 4;
    $tipo           = '';

    if ($facturas_id <= 0) {
        throw new Exception("No se recibió el ID de la factura.");
    }

    if ($pacientes_id <= 0 || $colaborador_id <= 0 || $servicio_id <= 0) {
        throw new Exception("Lo sentimos, el Paciente, Profesional o Servicio no pueden quedar en blanco.");
    }

    if (isset($_POST['facturas_activo'])) {
        $tipo_factura = ($_POST['facturas_activo'] == '') ? 2 : (int)$_POST['facturas_activo'];
        $tipo = ($_POST['facturas_activo'] == '') ? 'FacturacionCredito' : 'Facturacion';
    } else {
        $tipo_factura = 2;
        $tipo = 'FacturacionCredito';
    }

    $documento_tipo = ($tipo_factura == 1) ? 1 : 4;

    $porcentaje_isv = obtenerPorcentajeISVFactura($mysqli);
    $detalles = prepararDetallesFactura($mysqli, $_POST, $porcentaje_isv);

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
        throw new Exception("El total de la factura no puede ser negativo.");
    }

    $lockSecuenciaNombre = adquirirLockSecuenciaFactura($mysqli, $empresa_id, $documento_tipo, 20);
    $lockSecuenciaAdquirido = true;

    $numeroFactura = obtenerNumeroFacturaConRecuperacion($mysqli, $empresa_id, $documento_tipo, $usuario, 'addFacturaporUsuario');

    if (!isset($numeroFactura['error']) || $numeroFactura['error']) {
        $mensajeNumero = isset($numeroFactura['mensaje']) ? $numeroFactura['mensaje'] : 'No se pudo obtener el número de factura.';
        throw new Exception($mensajeNumero);
    }

    $numeroFactura['empresa_id'] = $empresa_id;
    $numeroFactura['documento_id'] = $documento_tipo;
    $numeroFacturaData = $numeroFactura;
    $numeroFacturaObtenido = true;

    $secuencia_facturacion_id = (int)$numeroFactura['data']['secuencia_facturacion_id'];
    $numero = (int)$numeroFactura['data']['numero'];
    $prefijo = $numeroFactura['data']['prefijo'];
    $relleno = (int)$numeroFactura['data']['relleno'];
    $no_factura = $prefijo . str_pad($numero, $relleno, "0", STR_PAD_LEFT);

    $mysqli->begin_transaction();

    $delete = "DELETE FROM facturas_detalle WHERE facturas_id = ?";
    $stmt = $mysqli->prepare($delete);

    if (!$stmt) {
        throw new Exception("Error al preparar limpieza de detalle: " . $mysqli->error);
    }

    $stmt->bind_param("i", $facturas_id);

    if (!$stmt->execute()) {
        $errorStmt = $stmt->error;
        $stmt->close();
        throw new Exception("Error al limpiar detalle de factura: " . $errorStmt);
    }

    $stmt->close();
    $detalleFacturaTocado = true;

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
        throw new Exception("Error al preparar inserción de detalle: " . $mysqli->error);
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
            throw new Exception("Error al procesar detalle de factura: " . $errorStmt);
        }

        if ($tipo_factura == 1) {
            $query_categoria = "SELECT cp.nombre AS categoria
                                FROM productos AS p
                                INNER JOIN categoria_producto AS cp 
                                    ON p.categoria_producto_id = cp.categoria_producto_id
                                WHERE p.productos_id = ?
                                GROUP BY p.productos_id";

            $stmtCat = $mysqli->prepare($query_categoria);

            if (!$stmtCat) {
                throw new Exception("Error al preparar consulta de categoría: " . $mysqli->error);
            }

            $stmtCat->bind_param("i", $productoID);
            $stmtCat->execute();
            $resultCat = $stmtCat->get_result();
            $rowCat = $resultCat ? $resultCat->fetch_assoc() : null;
            $stmtCat->close();

            $esProducto = ($rowCat && $rowCat['categoria'] === "Producto");

            if ($esProducto) {
                $update_producto = "UPDATE productos SET cantidad = cantidad - ? WHERE productos_id = ?";
                $stmtStock = $mysqli->prepare($update_producto);

                if (!$stmtStock) {
                    throw new Exception("Error al preparar actualización de stock: " . $mysqli->error);
                }

                $stmtStock->bind_param("ii", $cantidad, $productoID);

                if (!$stmtStock->execute()) {
                    $errorStmt = $stmtStock->error;
                    $stmtStock->close();
                    throw new Exception("Error al actualizar stock: " . $errorStmt);
                }

                $stmtStock->close();

                $query_saldo = "SELECT saldo 
                                FROM movimientos 
                                WHERE productos_id = ? 
                                ORDER BY movimientos_id DESC 
                                LIMIT 1";

                $stmtSaldo = $mysqli->prepare($query_saldo);

                if (!$stmtSaldo) {
                    throw new Exception("Error al preparar consulta de saldo: " . $mysqli->error);
                }

                $stmtSaldo->bind_param("i", $productoID);
                $stmtSaldo->execute();
                $resultSaldo = $stmtSaldo->get_result();

                if ($resultSaldo && $resultSaldo->num_rows > 0) {
                    $rowSaldo = $resultSaldo->fetch_assoc();
                    $saldo = ((float)$rowSaldo['saldo']) - $cantidad;
                } else {
                    $saldo = 0 - $cantidad;
                }

                $stmtSaldo->close();

                $movimientos_id = correlativo("movimientos_id", "movimientos");
                $documentoMovimiento = "Factura " . $facturas_id;
                $comentario = "Salida por Facturación";
                $cantidad_entrada = 0;
                $cantidad_salida = $cantidad;

                $insert_movimiento = "INSERT INTO movimientos
                    (movimientos_id, productos_id, documento, cantidad_entrada, cantidad_salida, saldo, fecha_registro, comentario)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

                $stmtMov = $mysqli->prepare($insert_movimiento);

                if (!$stmtMov) {
                    throw new Exception("Error al preparar movimiento de inventario: " . $mysqli->error);
                }

                $stmtMov->bind_param(
                    "iisiidss",
                    $movimientos_id,
                    $productoID,
                    $documentoMovimiento,
                    $cantidad_entrada,
                    $cantidad_salida,
                    $saldo,
                    $fecha_registro,
                    $comentario
                );

                if (!$stmtMov->execute()) {
                    $errorStmt = $stmtMov->error;
                    $stmtMov->close();
                    throw new Exception("Error al registrar movimiento de inventario: " . $errorStmt);
                }

                $stmtMov->close();
            }
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
            "La factura no se guardó completa. Productos enviados: " . count($detalles) .
            ", productos guardados: " . $cantidad_lineas_bd . "."
        );
    }

    if (abs($total_detalle_bd - $total_despues_isv) > 0.01) {
        throw new Exception(
            "La factura no cuadra. Encabezado: " . number_format($total_despues_isv, 2, '.', '') .
            ", detalle: " . number_format($total_detalle_bd, 2, '.', '') . "."
        );
    }

    $update = "UPDATE facturas SET
                    fecha = ?,
                    tipo_factura = ?,
                    number = ?,
                    secuencia_facturacion_id = ?,
                    estado = ?,
                    notas = ?,
                    colaborador_id = ?,
                    servicio_id = ?,
                    importe = ?,
                    usuario = ?
               WHERE facturas_id = ?";

    $stmt = $mysqli->prepare($update);

    if (!$stmt) {
        throw new Exception("Error al preparar actualización de factura: " . $mysqli->error);
    }

    $stmt->bind_param(
        "siiiisiidii",
        $fecha,
        $tipo_factura,
        $numero,
        $secuencia_facturacion_id,
        $estado,
        $notes,
        $colaborador_id,
        $servicio_id,
        $total_despues_isv,
        $usuario,
        $facturas_id
    );

    if (!$stmt->execute()) {
        $errorStmt = $stmt->error;
        $stmt->close();
        throw new Exception("Error al actualizar la factura: " . $errorStmt);
    }

    $stmt->close();
    $facturaActualizada = true;

    $query_cxc = "SELECT cobrar_clientes_id 
                  FROM cobrar_clientes 
                  WHERE facturas_id = ? 
                  LIMIT 1";

    $stmtCxc = $mysqli->prepare($query_cxc);

    if (!$stmtCxc) {
        throw new Exception("Error al preparar consulta de cuenta por cobrar: " . $mysqli->error);
    }

    $stmtCxc->bind_param("i", $facturas_id);
    $stmtCxc->execute();
    $resultCxc = $stmtCxc->get_result();
    $stmtCxc->close();

    if ($resultCxc && $resultCxc->num_rows > 0) {
        $rowCxc = $resultCxc->fetch_assoc();
        $cobrar_clientes_id = (int)$rowCxc['cobrar_clientes_id'];

        $update_cxc = "UPDATE cobrar_clientes 
                       SET saldo = ?, estado = 1, usuario = ?, empresa_id = ?
                       WHERE cobrar_clientes_id = ?";

        $stmtUpdateCxc = $mysqli->prepare($update_cxc);

        if (!$stmtUpdateCxc) {
            throw new Exception("Error al preparar actualización de cuenta por cobrar: " . $mysqli->error);
        }

        $stmtUpdateCxc->bind_param(
            "diii",
            $total_despues_isv,
            $usuario,
            $empresa_id,
            $cobrar_clientes_id
        );

        if (!$stmtUpdateCxc->execute()) {
            $errorStmt = $stmtUpdateCxc->error;
            $stmtUpdateCxc->close();
            throw new Exception("Error al actualizar cuenta por cobrar: " . $errorStmt);
        }

        $stmtUpdateCxc->close();
    } else {
        $cobrar_clientes_id = correlativo("cobrar_clientes_id", "cobrar_clientes");
        $estado_cxc = 1;

        $insert_cxc = "INSERT INTO cobrar_clientes
            (cobrar_clientes_id, pacientes_id, facturas_id, fecha, saldo, estado, usuario, empresa_id, fecha_registro)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmtInsertCxc = $mysqli->prepare($insert_cxc);

        if (!$stmtInsertCxc) {
            throw new Exception("Error al preparar cuenta por cobrar: " . $mysqli->error);
        }

        $stmtInsertCxc->bind_param(
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

        if (!$stmtInsertCxc->execute()) {
            $errorStmt = $stmtInsertCxc->error;
            $stmtInsertCxc->close();
            throw new Exception("Error al registrar cuenta por cobrar: " . $errorStmt);
        }

        $stmtInsertCxc->close();
    }

    confirmarNumeroReutilizado($mysqli, $numeroFacturaData, $usuario, $facturas_id, null);

    $mysqli->commit();

    if ($lockSecuenciaAdquirido) {
        liberarLockSecuenciaFactura($mysqli, $lockSecuenciaNombre);
        $lockSecuenciaAdquirido = false;
    }

    $datos = array(
        0  => 'Almacenado',
        1  => 'Registro Almacenado Correctamente',
        2  => 'success',
        3  => 'btn-primary',
        4  => 'formulario_facturacion',
        5  => 'Registro',
        6  => $tipo,
        7  => '',
        8  => $facturas_id,
        9  => $numero,
        10 => $no_factura,
        11 => number_format($total_despues_isv, 2, '.', ''),
        12 => count($detalles)
    );

} catch (Exception $e) {
    $mysqli->rollback();

    // MyISAM no revierte cambios. Si ya tocamos el detalle y no se emitió la factura,
    // limpiamos para no dejar una factura con encabezado y detalle incompleto.
    if (!$facturaActualizada && isset($facturas_id) && (int)$facturas_id > 0 && isset($detalleFacturaTocado) && $detalleFacturaTocado) {
        limpiarDetalleFacturaFallida($mysqli, (int)$facturas_id);
    }

    if ($numeroFacturaObtenido && !$facturaActualizada && $numero > 0 && $empresa_id > 0 && $documento_tipo > 0) {
        registrarNumeroFallidoCompleto($mysqli, $numeroFacturaData, $e->getMessage(), 'addFacturaporUsuario', $facturas_id, null, $usuario);
    }

    if ($lockSecuenciaAdquirido) {
        liberarLockSecuenciaFactura($mysqli, $lockSecuenciaNombre);
        $lockSecuenciaAdquirido = false;
    }

    error_log("Error addFacturaporUsuario.php: " . $e->getMessage());

    $datos = array(
        0 => 'Error',
        1 => $e->getMessage(),
        2 => 'error',
        3 => 'btn-danger',
        4 => '',
        5 => ''
    );
}

echo json_encode($datos);
exit;


function obtenerPorcentajeISVFactura($conexion) {
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


function prepararDetallesFactura($conexion, $post, $porcentaje_isv) {
    if (
        !isset($post['productoID']) ||
        !isset($post['productName']) ||
        !isset($post['quantity']) ||
        !isset($post['price'])
    ) {
        throw new Exception("El detalle de la factura no fue recibido correctamente.");
    }

    if (
        !is_array($post['productoID']) ||
        !is_array($post['productName']) ||
        !is_array($post['quantity']) ||
        !is_array($post['price'])
    ) {
        throw new Exception("El detalle de la factura tiene un formato inválido.");
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
        throw new Exception("El detalle de la factura no puede quedar vacío.");
    }

    if (
        $countProductos != $countNombres ||
        $countProductos != $countCantidad ||
        $countProductos != $countPrecios
    ) {
        throw new Exception("El detalle de la factura llegó incompleto. Por favor, vuelva a cargar los productos antes de emitir.");
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
        throw new Exception("No hay productos válidos para guardar en la factura.");
    }

    return $detalles;
}


/**
 * Limpia el detalle cuando MyISAM deja una operación a medias.
 */
function limpiarDetalleFacturaFallida($conexion, $facturas_id) {
    try {
        $stmt = $conexion->prepare("DELETE FROM facturas_detalle WHERE facturas_id = ?");

        if (!$stmt) {
            error_log("Error al preparar limpieza manual de detalle: " . $conexion->error);
            return false;
        }

        $stmt->bind_param("i", $facturas_id);
        $stmt->execute();
        $stmt->close();

        return true;
    } catch (Exception $e) {
        error_log("Error limpiando detalle fallido: " . $e->getMessage());
        return false;
    }
}


/**
 * Registra un número de factura fallido para su posible reutilización.
 */
/**
 * Bloqueo lógico por empresa/documento.
 * Sirve aunque las tablas principales sean MyISAM.
 * Evita que dos cajeros tomen el mismo número al mismo tiempo.
 */
function adquirirLockSecuenciaFactura($conexion, $empresa_id, $documento_id, $timeout = 20) {
    $lockName = 'facturacion_' . (int)$empresa_id . '_' . (int)$documento_id;

    $stmt = $conexion->prepare("SELECT GET_LOCK(?, ?) AS lock_obtenido");

    if (!$stmt) {
        throw new Exception("Error al preparar bloqueo de secuencia: " . $conexion->error);
    }

    $stmt->bind_param("si", $lockName, $timeout);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;

    $stmt->close();

    if (!$row || (int)$row['lock_obtenido'] !== 1) {
        throw new Exception("No se pudo asegurar la secuencia de facturación. Intente nuevamente en unos segundos.");
    }

    return $lockName;
}

function liberarLockSecuenciaFactura($conexion, $lockName) {
    if ($lockName === '' || $lockName === null) {
        return false;
    }

    $stmt = $conexion->prepare("SELECT RELEASE_LOCK(?) AS lock_liberado");

    if (!$stmt) {
        error_log("Error al preparar liberación de bloqueo: " . $conexion->error);
        return false;
    }

    $stmt->bind_param("s", $lockName);
    $stmt->execute();
    $stmt->close();

    return true;
}

/**
 * Verifica si un número ya está realmente usado.
 * Si está en facturas o facturas_grupal, NO debe estar disponible como fallido.
 */
function numeroFacturaYaUsado($conexion, $secuencia_facturacion_id, $numero) {
    $secuencia_facturacion_id = (int)$secuencia_facturacion_id;
    $numero = (int)$numero;

    if ($secuencia_facturacion_id <= 0 || $numero <= 0) {
        return true;
    }

    $queryFactura = "SELECT facturas_id
                     FROM facturas
                     WHERE secuencia_facturacion_id = ?
                       AND number = ?
                       AND number > 0
                     LIMIT 1";

    $stmt = $conexion->prepare($queryFactura);

    if (!$stmt) {
        throw new Exception("Error al validar número en facturas: " . $conexion->error);
    }

    $stmt->bind_param("ii", $secuencia_facturacion_id, $numero);
    $stmt->execute();
    $res = $stmt->get_result();
    $usado = ($res && $res->num_rows > 0);
    $stmt->close();

    if ($usado) {
        return true;
    }

    $queryFacturaGrupal = "SELECT facturas_grupal_id
                           FROM facturas_grupal
                           WHERE secuencia_facturacion_id = ?
                             AND CAST(number AS UNSIGNED) = ?
                             AND CAST(number AS UNSIGNED) > 0
                           LIMIT 1";

    $stmt = $conexion->prepare($queryFacturaGrupal);

    if (!$stmt) {
        throw new Exception("Error al validar número en facturas grupales: " . $conexion->error);
    }

    $stmt->bind_param("ii", $secuencia_facturacion_id, $numero);
    $stmt->execute();
    $res = $stmt->get_result();
    $usado = ($res && $res->num_rows > 0);
    $stmt->close();

    return $usado;
}

/**
 * Primero intenta recuperar un número fallido disponible.
 * Si no hay, toma el siguiente número normal con obtenerNumeroFactura().
 */
function obtenerNumeroFacturaConRecuperacion($conexion, $empresa_id, $documento_id, $usuario, $origen) {
    $empresa_id = (int)$empresa_id;
    $documento_id = (int)$documento_id;
    $usuario = (int)$usuario;

    for ($intento = 0; $intento < 50; $intento++) {
        $queryFallida = "SELECT
                            secuencia_factura_fallida_id,
                            empresa_id,
                            secuencia_facturacion_id,
                            documento_id,
                            numero,
                            prefijo,
                            relleno
                         FROM secuencia_factura_fallida
                         WHERE empresa_id = ?
                           AND documento_id = ?
                           AND estado = 1
                         ORDER BY numero ASC, secuencia_factura_fallida_id ASC
                         LIMIT 1";

        $stmt = $conexion->prepare($queryFallida);

        if (!$stmt) {
            throw new Exception("Error al preparar búsqueda de números fallidos: " . $conexion->error);
        }

        $stmt->bind_param("ii", $empresa_id, $documento_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        if (!$row) {
            break;
        }

        $fallida_id = (int)$row['secuencia_factura_fallida_id'];
        $secuencia_facturacion_id = (int)$row['secuencia_facturacion_id'];
        $numero = (int)$row['numero'];

        if (numeroFacturaYaUsado($conexion, $secuencia_facturacion_id, $numero)) {
            $motivo = "Número bloqueado automáticamente porque ya existe en facturas o facturas_grupal.";
            $estadoBloqueado = 3;

            $update = "UPDATE secuencia_factura_fallida
                       SET estado = ?,
                           motivo = ?,
                           usuario_reutilizo = ?,
                           fecha_reutilizado = NOW()
                       WHERE secuencia_factura_fallida_id = ?";

            $stmtUpdate = $conexion->prepare($update);

            if (!$stmtUpdate) {
                throw new Exception("Error al bloquear número fallido ya usado: " . $conexion->error);
            }

            $stmtUpdate->bind_param("isii", $estadoBloqueado, $motivo, $usuario, $fallida_id);
            $stmtUpdate->execute();
            $stmtUpdate->close();

            continue;
        }

        return array(
            'error' => false,
            'mensaje' => 'Número recuperado correctamente.',
            'reutilizado' => true,
            'fallida_id' => $fallida_id,
            'data' => array(
                'secuencia_facturacion_id' => $secuencia_facturacion_id,
                'numero' => $numero,
                'prefijo' => (string)$row['prefijo'],
                'relleno' => (int)$row['relleno']
            )
        );
    }

    $numeroFactura = obtenerNumeroFactura($conexion, $empresa_id, $documento_id);

    if (!isset($numeroFactura['error']) || $numeroFactura['error']) {
        return $numeroFactura;
    }

    $numeroFactura['reutilizado'] = false;
    $numeroFactura['fallida_id'] = 0;

    return $numeroFactura;
}

/**
 * Marca un número recuperado como reutilizado solo cuando la factura ya quedó guardada.
 */
function confirmarNumeroReutilizado($conexion, $numeroFacturaData, $usuario, $facturas_id = null, $facturas_grupal_id = null) {
    if (!is_array($numeroFacturaData)) {
        return false;
    }

    if (!isset($numeroFacturaData['reutilizado']) || !$numeroFacturaData['reutilizado']) {
        return true;
    }

    $fallida_id = isset($numeroFacturaData['fallida_id']) ? (int)$numeroFacturaData['fallida_id'] : 0;

    if ($fallida_id <= 0) {
        return true;
    }

    $estado = 2;
    $usuario = (int)$usuario;
    $facturas_id = !is_null($facturas_id) ? (int)$facturas_id : null;
    $facturas_grupal_id = !is_null($facturas_grupal_id) ? (int)$facturas_grupal_id : null;

    $update = "UPDATE secuencia_factura_fallida
               SET estado = ?,
                   usuario_reutilizo = ?,
                   fecha_reutilizado = NOW(),
                   facturas_id = ?,
                   facturas_grupal_id = ?
               WHERE secuencia_factura_fallida_id = ?
                 AND estado = 1";

    $stmt = $conexion->prepare($update);

    if (!$stmt) {
        throw new Exception("Error al confirmar número reutilizado: " . $conexion->error);
    }

    $stmt->bind_param("iiiii", $estado, $usuario, $facturas_id, $facturas_grupal_id, $fallida_id);

    if (!$stmt->execute()) {
        $errorStmt = $stmt->error;
        $stmt->close();
        throw new Exception("Error al marcar número reutilizado: " . $errorStmt);
    }

    $stmt->close();

    return true;
}

/**
 * Registra un número como fallido solo si todavía no existe en facturas/facturas_grupal.
 */
function registrarNumeroFallidoCompleto($conexion, $numeroFacturaData, $motivo, $origen, $facturas_id, $facturas_grupal_id, $usuario) {
    try {
        if (!is_array($numeroFacturaData) || !isset($numeroFacturaData['data'])) {
            return false;
        }

        if (isset($numeroFacturaData['reutilizado']) && $numeroFacturaData['reutilizado']) {
            // Si era un número recuperado y falló antes de usarse, se queda disponible en estado 1.
            return true;
        }

        $empresa_id = isset($numeroFacturaData['empresa_id']) ? (int)$numeroFacturaData['empresa_id'] : 0;
        $documento_id = isset($numeroFacturaData['documento_id']) ? (int)$numeroFacturaData['documento_id'] : 0;
        $secuencia_facturacion_id = isset($numeroFacturaData['data']['secuencia_facturacion_id']) ? (int)$numeroFacturaData['data']['secuencia_facturacion_id'] : 0;
        $numero = isset($numeroFacturaData['data']['numero']) ? (int)$numeroFacturaData['data']['numero'] : 0;
        $prefijo = isset($numeroFacturaData['data']['prefijo']) ? (string)$numeroFacturaData['data']['prefijo'] : '';
        $relleno = isset($numeroFacturaData['data']['relleno']) ? (int)$numeroFacturaData['data']['relleno'] : 8;

        if ($empresa_id <= 0 || $documento_id <= 0 || $secuencia_facturacion_id <= 0 || $numero <= 0) {
            return false;
        }

        if (numeroFacturaYaUsado($conexion, $secuencia_facturacion_id, $numero)) {
            return false;
        }

        $estadoDisponible = 1;

        $queryExiste = "SELECT secuencia_factura_fallida_id
                        FROM secuencia_factura_fallida
                        WHERE empresa_id = ?
                          AND documento_id = ?
                          AND numero = ?
                          AND estado = 1
                        LIMIT 1";

        $stmtExiste = $conexion->prepare($queryExiste);

        if (!$stmtExiste) {
            error_log("Error al preparar verificación de número fallido: " . $conexion->error);
            return false;
        }

        $stmtExiste->bind_param("iii", $empresa_id, $documento_id, $numero);
        $stmtExiste->execute();
        $resExiste = $stmtExiste->get_result();
        $rowExiste = $resExiste ? $resExiste->fetch_assoc() : null;
        $stmtExiste->close();

        $motivo = substr((string)$motivo, 0, 255);
        $origen = substr((string)$origen, 0, 80);
        $facturas_id = !is_null($facturas_id) ? (int)$facturas_id : null;
        $facturas_grupal_id = !is_null($facturas_grupal_id) ? (int)$facturas_grupal_id : null;
        $usuario = (int)$usuario;

        if ($rowExiste) {
            $fallida_id = (int)$rowExiste['secuencia_factura_fallida_id'];

            $update = "UPDATE secuencia_factura_fallida
                       SET motivo = ?,
                           origen = ?,
                           facturas_id = ?,
                           facturas_grupal_id = ?,
                           usuario = ?,
                           fecha_registro = NOW()
                       WHERE secuencia_factura_fallida_id = ?";

            $stmtUpdate = $conexion->prepare($update);

            if (!$stmtUpdate) {
                error_log("Error al preparar actualización de número fallido: " . $conexion->error);
                return false;
            }

            $stmtUpdate->bind_param("ssiiii", $motivo, $origen, $facturas_id, $facturas_grupal_id, $usuario, $fallida_id);
            $stmtUpdate->execute();
            $stmtUpdate->close();

            return true;
        }

        $insert = "INSERT INTO secuencia_factura_fallida
            (empresa_id, secuencia_facturacion_id, documento_id, numero, prefijo, relleno, estado, motivo, origen, facturas_id, facturas_grupal_id, usuario, fecha_registro)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $conexion->prepare($insert);

        if (!$stmt) {
            error_log("Error al preparar número fallido completo: " . $conexion->error);
            return false;
        }

        $stmt->bind_param(
            "iiiisiissiii",
            $empresa_id,
            $secuencia_facturacion_id,
            $documento_id,
            $numero,
            $prefijo,
            $relleno,
            $estadoDisponible,
            $motivo,
            $origen,
            $facturas_id,
            $facturas_grupal_id,
            $usuario
        );

        $stmt->execute();
        $stmt->close();

        return true;
    } catch (Exception $e) {
        error_log("Error al registrar número fallido completo: " . $e->getMessage());
        return false;
    }
}

/**
 * Compatibilidad con llamadas anteriores dentro del proyecto.
 */
function registrarNumeroFallido($conexion, $empresa_id, $documento_id, $numero) {
    try {
        $querySecuencia = "SELECT secuencia_facturacion_id, prefijo, relleno
                           FROM secuencia_facturacion
                           WHERE empresa_id = ?
                             AND documento_id = ?
                             AND activo = 1
                           LIMIT 1";

        $stmt = $conexion->prepare($querySecuencia);

        if (!$stmt) {
            error_log("Error al preparar secuencia para número fallido: " . $conexion->error);
            return false;
        }

        $stmt->bind_param("ii", $empresa_id, $documento_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        if (!$row) {
            return false;
        }

        $numeroFacturaData = array(
            'reutilizado' => false,
            'empresa_id' => (int)$empresa_id,
            'documento_id' => (int)$documento_id,
            'data' => array(
                'secuencia_facturacion_id' => (int)$row['secuencia_facturacion_id'],
                'numero' => (int)$numero,
                'prefijo' => (string)$row['prefijo'],
                'relleno' => (int)$row['relleno']
            )
        );

        return registrarNumeroFallidoCompleto($conexion, $numeroFacturaData, 'Registro de compatibilidad', 'compatibilidad', null, null, 0);
    } catch (Exception $e) {
        error_log("Error al registrar número fallido compatible: " . $e->getMessage());
        return false;
    }
}