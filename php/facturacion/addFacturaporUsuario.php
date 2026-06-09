<?php
// addFacturaporUsuario.php
session_start();
include '../funtions.php';

header('Content-Type: application/json; charset=utf-8');

// Evita que warnings/notices rompan el JSON hacia JavaScript
ini_set('display_errors', 0);
error_reporting(E_ALL);

// CONEXION A DB
$mysqli = connect_mysqli();
$mysqli->set_charset("utf8");

try {
    // =========================================================
    // VALIDAR SESIÓN
    // =========================================================
    if (!isset($_SESSION['colaborador_id']) || !isset($_SESSION['empresa_id'])) {
        throw new Exception("La sesión expiró. Por favor, inicie sesión nuevamente.");
    }

    // =========================================================
    // OBTENER DATOS DEL POST
    // =========================================================
    $facturas_id     = isset($_POST['facturas_id']) ? (int)$_POST['facturas_id'] : 0;
    $pacientes_id    = isset($_POST['pacientes_id']) ? (int)$_POST['pacientes_id'] : 0;
    $fecha           = date('Y-m-d');
    $colaborador_id  = isset($_POST['colaborador_id']) ? (int)$_POST['colaborador_id'] : 0;
    $servicio_id     = isset($_POST['servicio_id']) ? (int)$_POST['servicio_id'] : 0;
    $notes           = isset($_POST['notes']) ? cleanStringStrtolower($_POST['notes']) : '';
    $usuario         = (int)$_SESSION['colaborador_id'];
    $empresa_id      = (int)$_SESSION['empresa_id'];
    $fecha_registro  = date('Y-m-d H:i:s');
    $estado          = 4; // ESTADO FACTURA CREDITO
    $tipo            = '';

    if ($facturas_id <= 0) {
        throw new Exception("No se recibió el ID de la factura.");
    }

    if ($pacientes_id <= 0 || $colaborador_id <= 0 || $servicio_id <= 0) {
        throw new Exception("Lo sentimos, el Paciente, Profesional o Servicio no pueden quedar en blanco.");
    }

    // =========================================================
    // DETERMINAR TIPO DE FACTURA
    // =========================================================
    if (isset($_POST['facturas_activo'])) {
        $tipo_factura = ($_POST['facturas_activo'] == '') ? 2 : (int)$_POST['facturas_activo'];
        $tipo = ($_POST['facturas_activo'] == '') ? 'FacturacionCredito' : 'Facturacion';
    } else {
        $tipo_factura = 2;
        $tipo = 'FacturacionCredito';
    }

    // 1 = Factura Electrónica / contado
    // 4 = Proforma / crédito
    $documento_tipo = ($tipo_factura == 1) ? 1 : 4;

    // =========================================================
    // VALIDAR DETALLES RECIBIDOS
    // =========================================================
    if (
        !isset($_POST['productoID']) ||
        !isset($_POST['productName']) ||
        !isset($_POST['quantity']) ||
        !isset($_POST['price'])
    ) {
        throw new Exception("El detalle de la factura no fue recibido correctamente.");
    }

    if (
        !is_array($_POST['productoID']) ||
        !is_array($_POST['productName']) ||
        !is_array($_POST['quantity']) ||
        !is_array($_POST['price'])
    ) {
        throw new Exception("El detalle de la factura tiene un formato inválido.");
    }

    $productoIDArray   = $_POST['productoID'];
    $productNameArray  = $_POST['productName'];
    $quantityArray     = $_POST['quantity'];
    $priceArray        = $_POST['price'];
    $discountArray     = isset($_POST['discount']) && is_array($_POST['discount']) ? $_POST['discount'] : array();

    $countProductos = count($productoIDArray);
    $countNombres   = count($productNameArray);
    $countCantidad  = count($quantityArray);
    $countPrecios   = count($priceArray);

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

    // =========================================================
    // OBTENER ISV GENERAL
    // =========================================================
    $query_isv = "SELECT nombre FROM isv LIMIT 1";
    $result_isv = $mysqli->query($query_isv);

    if (!$result_isv) {
        throw new Exception("Error al obtener el ISV general: " . $mysqli->error);
    }

    $porcentajeISV = ($result_isv->num_rows > 0) ? (float)$result_isv->fetch_assoc()['nombre'] : 0;
    $porcentaje_isv = $porcentajeISV / 100;

    // =========================================================
    // PREPARAR DETALLES ANTES DE TOCAR LA BASE DE DATOS
    // IMPORTANTE: YA NO SE USA CONTINUE
    // =========================================================
    $detalles = array();

    $total_valor = 0;
    $descuentos  = 0;
    $isv_neto    = 0;

    for ($i = 0; $i < $countProductos; $i++) {
        $linea = $i + 1;

        $productoID  = isset($productoIDArray[$i]) ? (int)$productoIDArray[$i] : 0;
        $productName = isset($productNameArray[$i]) ? trim($productNameArray[$i]) : '';
        $quantityRaw = isset($quantityArray[$i]) ? trim((string)$quantityArray[$i]) : '';
        $priceRaw    = isset($priceArray[$i]) ? trim((string)$priceArray[$i]) : '';
        $discountRaw = isset($discountArray[$i]) ? trim((string)$discountArray[$i]) : '0';

        if ($productoID <= 0) {
            throw new Exception("Hay un producto sin ID en la línea " . $linea . ".");
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
        $price    = (float)$priceRaw;
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

        // VALIDAR QUE EL PRODUCTO EXISTA Y OBTENER SI APLICA ISV
        $query_producto = "SELECT productos_id, isv FROM productos WHERE productos_id = ? LIMIT 1";
        $stmt = $mysqli->prepare($query_producto);

        if (!$stmt) {
            throw new Exception("Error al preparar validación de producto: " . $mysqli->error);
        }

        $stmt->bind_param("i", $productoID);
        $stmt->execute();
        $result_producto = $stmt->get_result();

        if (!$result_producto || $result_producto->num_rows == 0) {
            $stmt->close();
            throw new Exception("El producto " . $productName . " no existe o fue eliminado.");
        }

        $rowProducto = $result_producto->fetch_assoc();
        $aplica_isv = (int)$rowProducto['isv'];
        $stmt->close();

        $isv_valor = ($aplica_isv == 1) ? ($subtotal * $porcentaje_isv) : 0.0;

        $total_valor += $subtotal;
        $descuentos  += $discount;
        $isv_neto    += $isv_valor;

        $detalles[] = array(
            'productos_id' => $productoID,
            'nombre'       => $productName,
            'cantidad'     => $quantity,
            'precio'       => $price,
            'isv_valor'    => $isv_valor,
            'descuento'    => $discount
        );
    }

    if (count($detalles) <= 0) {
        throw new Exception("No hay productos válidos para guardar en la factura.");
    }

    // =========================================================
    // TOTAL FINAL CALCULADO DESDE LOS DETALLES VALIDADOS
    // =========================================================
    $total_despues_isv = round(($total_valor + $isv_neto) - $descuentos, 2);

    if ($total_despues_isv <= 0) {
        throw new Exception("El total de la factura debe ser mayor a cero.");
    }

    // =========================================================
    // OBTENER NÚMERO DE FACTURA
    // =========================================================
    $numeroFactura = obtenerNumeroFactura($mysqli, $empresa_id, $documento_tipo);

    if (!isset($numeroFactura['error']) || $numeroFactura['error']) {
        $mensajeNumero = isset($numeroFactura['mensaje']) ? $numeroFactura['mensaje'] : 'No se pudo obtener el número de factura.';
        throw new Exception($mensajeNumero);
    }

    $secuencia_facturacion_id = (int)$numeroFactura['data']['secuencia_facturacion_id'];
    $numero  = (int)$numeroFactura['data']['numero'];
    $prefijo = $numeroFactura['data']['prefijo'];
    $relleno = (int)$numeroFactura['data']['relleno'];
    $no_factura = $prefijo . str_pad($numero, $relleno, "0", STR_PAD_LEFT);

    // =========================================================
    // IMPORTANTE:
    // Tus tablas son MyISAM. MyISAM NO hace rollback real.
    // Aun así dejamos begin_transaction por si luego migras a InnoDB.
    // Pero la protección real aquí es:
    // 1. validar todo antes
    // 2. no usar continue
    // 3. verificar el total real del detalle contra encabezado
    // =========================================================
    $mysqli->begin_transaction();

    // =========================================================
    // ACTUALIZAR FACTURA PRINCIPAL
    // Aquí ya se guarda el importe correcto calculado desde el detalle.
    // =========================================================
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
        registrarNumeroFallido($mysqli, $empresa_id, $documento_tipo, $numero);
        throw new Exception("Error al preparar actualización de factura: " . $mysqli->error);
    }

    $stmt->bind_param(
        'siiiisiiiii',
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
        registrarNumeroFallido($mysqli, $empresa_id, $documento_tipo, $numero);
        throw new Exception("Error al actualizar la factura: " . $errorStmt);
    }

    if ($stmt->affected_rows < 0) {
        $stmt->close();
        registrarNumeroFallido($mysqli, $empresa_id, $documento_tipo, $numero);
        throw new Exception("No se pudo actualizar la factura.");
    }

    $stmt->close();

    // =========================================================
    // LIMPIAR DETALLE EXISTENTE
    // =========================================================
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

    // =========================================================
    // OBTENER CORRELATIVO BASE UNA SOLA VEZ
    // Evita repetir correlativo() dentro del loop.
    // =========================================================
    $query_max_detalle = "SELECT IFNULL(MAX(facturas_detalle_id), 0) AS max_id FROM facturas_detalle";
    $result_max_detalle = $mysqli->query($query_max_detalle);

    if (!$result_max_detalle) {
        throw new Exception("Error al obtener correlativo de detalle: " . $mysqli->error);
    }

    $rowMaxDetalle = $result_max_detalle->fetch_assoc();
    $siguienteDetalleID = ((int)$rowMaxDetalle['max_id']) + 1;

    // =========================================================
    // INSERTAR DETALLES
    // =========================================================
    $insert_detalle = "INSERT INTO facturas_detalle
        (facturas_detalle_id, facturas_id, productos_id, cantidad, precio, isv_valor, descuento)
        VALUES (?,?,?,?,?,?,?)";

    $stmtDetalle = $mysqli->prepare($insert_detalle);

    if (!$stmtDetalle) {
        throw new Exception("Error al preparar inserción de detalle: " . $mysqli->error);
    }

    foreach ($detalles as $detalle) {
        $facturas_detalle_id = $siguienteDetalleID++;
        $productoID = (int)$detalle['productos_id'];
        $quantity   = (int)$detalle['cantidad'];
        $price      = (float)$detalle['precio'];
        $isv_valor  = (float)$detalle['isv_valor'];
        $discount   = (float)$detalle['descuento'];

        $stmtDetalle->bind_param(
            "iiiiddd",
            $facturas_detalle_id,
            $facturas_id,
            $productoID,
            $quantity,
            $price,
            $isv_valor,
            $discount
        );

        if (!$stmtDetalle->execute()) {
            $errorStmt = $stmtDetalle->error;
            $stmtDetalle->close();
            throw new Exception("Error al procesar detalle de factura: " . $errorStmt);
        }

        // =====================================================
        // PROCESAR INVENTARIO SI ES PRODUCTO Y FACTURA CONTADO
        // =====================================================
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
                // ACTUALIZAR STOCK
                $update_producto = "UPDATE productos SET cantidad = cantidad - ? WHERE productos_id = ?";
                $stmtStock = $mysqli->prepare($update_producto);

                if (!$stmtStock) {
                    throw new Exception("Error al preparar actualización de stock: " . $mysqli->error);
                }

                $stmtStock->bind_param("ii", $quantity, $productoID);

                if (!$stmtStock->execute()) {
                    $errorStmt = $stmtStock->error;
                    $stmtStock->close();
                    throw new Exception("Error al actualizar stock: " . $errorStmt);
                }

                $stmtStock->close();

                // REGISTRAR MOVIMIENTO
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
                $ultimoSaldo = ($resultSaldo && $resultSaldo->num_rows > 0) ? (int)$resultSaldo->fetch_assoc()['saldo'] : 0;
                $stmtSaldo->close();

                $saldo = $ultimoSaldo - $quantity;

                $movimientos_id = correlativo("movimientos_id", "movimientos");
                $doc_texto = "Factura " . $facturas_id;
                $cantidad_entrada = 0;
                $cantidad_salida = $quantity;

                $insert_movimiento = "INSERT INTO movimientos
                    (movimientos_id, productos_id, documento, cantidad_entrada, cantidad_salida, saldo, fecha_registro)
                    VALUES (?,?,?,?,?,?,?)";

                $stmtMov = $mysqli->prepare($insert_movimiento);

                if (!$stmtMov) {
                    throw new Exception("Error al preparar movimiento de inventario: " . $mysqli->error);
                }

                $stmtMov->bind_param(
                    "iisiiis",
                    $movimientos_id,
                    $productoID,
                    $doc_texto,
                    $cantidad_entrada,
                    $cantidad_salida,
                    $saldo,
                    $fecha_registro
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

    // =========================================================
    // VALIDACIÓN FINAL OBLIGATORIA:
    // EL TOTAL DEL DETALLE DEBE CUADRAR CON EL ENCABEZADO
    // =========================================================
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

    // =========================================================
    // REGISTRAR / ACTUALIZAR CUENTA POR COBRAR
    // =========================================================
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
            VALUES (?,?,?,?,?,?,?,?,?)";

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

    // =========================================================
    // CONFIRMAR
    // =========================================================
    $mysqli->commit();

    $datos = array(
        0  => 'Almacenado',
        1  => 'Registro Almacenado Correctamente',
        2  => 'success',
        3  => 'btn-primary',
        4  => 'formulario_facturacion',
        5  => 'Registro',
        6  => $tipo,
        7  => $no_factura,
        8  => $facturas_id,
        9  => $numero,
        10 => number_format($total_despues_isv, 2, '.', ''),
        11 => count($detalles)
    );

} catch (Exception $e) {
    $mysqli->rollback();

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


/**
 * Registra un número de factura fallido para su posible reutilización
 */
function registrarNumeroFallido($conexion, $empresa_id, $documento_id, $numero) {
    try {
        $insert = "INSERT INTO secuencia_factura_fallida 
                    (empresa_id, documento_id, numero, fecha_registro)
                   VALUES (?, ?, ?, NOW())";

        $stmt = $conexion->prepare($insert);

        if (!$stmt) {
            error_log("Error al preparar número fallido: " . $conexion->error);
            return false;
        }

        $stmt->bind_param("iii", $empresa_id, $documento_id, $numero);
        $stmt->execute();
        $stmt->close();

        return true;
    } catch (Exception $e) {
        error_log("Error al registrar número fallido: " . $e->getMessage());
        return false;
    }
}