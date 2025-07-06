<?php
//addFactura.php
session_start();
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

// Iniciar transacción
$mysqli->begin_transaction();

try {
    // Obtener datos del POST
    $pacientes_id = $_POST['pacientes_id'];
    $muestras_id = $_POST['muestras_id'] ?? '';
    $fecha = date("Y-m-d");
    $colaborador_id = $_POST['colaborador_id'];
    $servicio_id = $_POST['servicio_id'];
    $notes = cleanStringStrtolower($_POST['notes']);
    $usuario = $_SESSION['colaborador_id'];
    $empresa_id = $_SESSION['empresa_id'];
    $fecha_registro = date("Y-m-d H:i:s");
    $activo = 1;
    $estado = 4; //ESTADO FACTURA CREDITO
    $cierre = 2;
    $importe = 0;
    $tipo = "";
    $estado_factura = 1; //BORRADOR
    $numero = 0; //NUMERO DE FACTURA AUN NO GENERADO

    // Determinar tipo de factura
    if(isset($_POST['facturas_activo'])) {
        if($_POST['facturas_activo'] == "") {
            $tipo_factura = 2;
            $tipo = "FacturacionCredito";
        } else {
            $tipo_factura = $_POST['facturas_activo'];
            $tipo = "Facturacion";
        }
    } else {
        $tipo_factura = 2;
        $tipo = "FacturacionCredito";
    }

    $documento = ($tipo_factura === "1") ? "1" : "4"; //1=Factura Electronica, 4=Factura Proforma

    // Validar datos requeridos
    if(empty($pacientes_id) || empty($colaborador_id) || empty($servicio_id)) {
        throw new Exception("Lo sentimos, el Paciente, Profesional o Servicio no pueden quedar en blanco");
    }

    // OBTENER Y BLOQUEAR SECUENCIA DE FACTURACIÓN
    $query_secuencia = "SELECT secuencia_facturacion_id, prefijo, siguiente AS 'numero', 
                        rango_final, fecha_limite, incremento, relleno
                        FROM secuencia_facturacion
                        WHERE activo = ? AND empresa_id = ? AND documento_id = ? 
                        LIMIT 1 FOR UPDATE";
    
    $stmt = $mysqli->prepare($query_secuencia);
    $stmt->bind_param("iii", $activo, $empresa_id, $documento);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows == 0) {
        throw new Exception("No se encontró una secuencia de facturación activa");
    }
    
    $secuencia = $result->fetch_assoc();
    $stmt->close();

    $secuencia_facturacion_id = $secuencia['secuencia_facturacion_id'];
    $prefijo = $secuencia['prefijo'];
    $numero = $secuencia['numero'];
    $rango_final = $secuencia['rango_final'];
    $incremento = $secuencia['incremento'];
    $no_factura = $prefijo."".str_pad($numero, $secuencia['relleno'], "0", STR_PAD_LEFT);

    // Verificar rango disponible
    $nuevo_numero = $numero + $incremento;
    if($nuevo_numero > $rango_final) {
        throw new Exception("Se ha alcanzado el límite del rango autorizado de facturación");
    }

    // Validar detalles de factura
    $tamano_tabla = 0;
    if(isset($_POST['productName'])) {
        if($_POST['productName'][0] != "" && $_POST['quantity'][0] != "" && $_POST['price'][0] != "") {
            $tamano_tabla = count($_POST['productName']);
        }
    }

    if($tamano_tabla <= 0) {
        throw new Exception("El detalle de la factura no puede quedar vacío");
    }

    // Insertar factura con la secuencia correcta
    $facturas_id = correlativo("facturas_id", "facturas");
    $insert = "INSERT INTO facturas VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $mysqli->prepare($insert);
    $stmt->bind_param(
        "iiiiiiisssiiiss", 
        $facturas_id,
        $secuencia_facturacion_id, // Asegurando que se guarde la referencia correcta
        $muestras_id,
        $numero,
        $tipo_factura,
        $pacientes_id,
        $colaborador_id,
        $servicio_id,
        $importe,
        $notes,
        $fecha,
        $estado_factura,
        $cierre,
        $usuario,
        $empresa_id,
        $fecha_registro
    );
    
    if(!$stmt->execute()) {
        throw new Exception("Error al guardar la factura");
    }
    $stmt->close();

    // Procesar detalles de factura
    $total_valor = 0;
    $descuentos = 0;
    $isv_neto = 0;

    for ($i = 0; $i < count($_POST['productName']); $i++) {
        if(empty($_POST['productoID'][$i]) || empty($_POST['productName'][$i]) || 
           empty($_POST['quantity'][$i]) || empty($_POST['price'][$i]) || empty($_POST['total'][$i])) {
            continue;
        }

        $productoID = $_POST['productoID'][$i];
        $productName = $_POST['productName'][$i];
        $quantity = $_POST['quantity'][$i];
        $price = $_POST['price'][$i];
        $discount = $_POST['discount'][$i] ?? 0;
        $total = $_POST['total'][$i];
        $isv_valor = 0;

        // CALCULAR ISV
        $query_isv = "SELECT nombre FROM isv LIMIT 1";
        $result_isv = $mysqli->query($query_isv);
        $porcentajeISV = ($result_isv->num_rows > 0) ? $result_isv->fetch_assoc()['nombre'] : 0;

        $query_isv_activo = "SELECT isv FROM productos WHERE productos_id = ?";
        $stmt = $mysqli->prepare($query_isv_activo);
        $stmt->bind_param("i", $productoID);
        $stmt->execute();
        $result = $stmt->get_result();
        $aplica_isv = ($result->num_rows > 0) ? $result->fetch_assoc()['isv'] : 0;
        $stmt->close();

        if ($aplica_isv == 1) {
            $porcentaje_isv = ($porcentajeISV / 100);
            $isv_valor = $price * $quantity * $porcentaje_isv;
        }

        // INSERTAR DETALLE FACTURA
        $facturas_detalle_id = correlativo("facturas_detalle_id", "facturas_detalle");
        $insert_detalle = "INSERT INTO facturas_detalle VALUES(?,?,?,?,?,?,?)";
        $stmt = $mysqli->prepare($insert_detalle);
        $stmt->bind_param("iiiiddd", $facturas_detalle_id, $facturas_id, $productoID, $quantity, $price, $isv_valor, $discount);
        
        if(!$stmt->execute()) {
            throw new Exception("Error al guardar detalle de factura");
        }
        $stmt->close();

        // PROCESAR INVENTARIO SI ES PRODUCTO
        if($tipo_factura == 1) {
            $query_categoria = "SELECT cp.nombre AS 'categoria'
                              FROM productos AS p
                              INNER JOIN categoria_producto AS cp ON p.categoria_producto_id = cp.categoria_producto_id
                              WHERE p.productos_id = ?
                              GROUP BY p.productos_id";
            $stmt = $mysqli->prepare($query_categoria);
            $stmt->bind_param("i", $productoID);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if($result->num_rows > 0 && $result->fetch_assoc()['categoria'] == "Producto") {
                $stmt->close();
                
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
                $saldo = ($result->num_rows > 0) ? $result->fetch_assoc()['saldo'] - $quantity : -$quantity;
                $stmt->close();
                
                $movimientos_id = correlativo("movimientos_id", "movimientos");
                $documento = "Factura ".$facturas_id;
                $comentario = "Salida por Facturación";
                $insert_movimiento = "INSERT INTO movimientos VALUES(?,?,?,?,?,?,?,?)";
                $stmt = $mysqli->prepare($insert_movimiento);
                $cantidad_entrada = 0;
                $cantidad_salida = $quantity;
                $stmt->bind_param("iisiidss", $movimientos_id, $productoID, $documento, $cantidad_entrada, $cantidad_salida, $saldo, $fecha_registro, $comentario);
                $stmt->execute();
                $stmt->close();
            } else {
                $stmt->close();
            }
        }

        $total_valor += ($price * $quantity);
        $descuentos += $discount;
        $isv_neto += $isv_valor;
    }

    $total_despues_isv = ($total_valor + $isv_neto) - $descuentos;

    // ACTUALIZAR IMPORTE FACTURA
    $update = "UPDATE facturas SET importe = ?, usuario = ? WHERE facturas_id = ?";
    $stmt = $mysqli->prepare($update);
    $stmt->bind_param("dii", $total_despues_isv, $usuario, $facturas_id);
    
    if(!$stmt->execute()) {
        throw new Exception("Error al actualizar importe de factura");
    }
    $stmt->close();

    // ACTUALIZAR SECUENCIA - Asegurar que se actualice solo si todo lo demás fue exitoso
    $update = "UPDATE secuencia_facturacion SET siguiente = ? WHERE secuencia_facturacion_id = ?";
    $stmt = $mysqli->prepare($update);
    $stmt->bind_param("ii", $nuevo_numero, $secuencia_facturacion_id);
    
    if(!$stmt->execute()) {
        throw new Exception("Error al actualizar secuencia de facturación");
    }
    $stmt->close();

    // REGISTRAR CUENTA POR COBRAR
    $query_cxc = "SELECT cobrar_clientes_id FROM cobrar_clientes WHERE facturas_id = ?";
    $stmt = $mysqli->prepare($query_cxc);
    $stmt->bind_param("i", $facturas_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    if($result->num_rows == 0) {
        $cobrar_clientes_id = correlativo("cobrar_clientes_id", "cobrar_clientes");
        $insert_cxc = "INSERT INTO cobrar_clientes VALUES(?,?,?,?,?,?,?,?,?)";
        $stmt = $mysqli->prepare($insert_cxc);
        $estado_cxc = 1;
        $stmt->bind_param("iiisdiiss", $cobrar_clientes_id, $pacientes_id, $facturas_id, $fecha, $total_despues_isv, $estado_cxc, $usuario, $empresa_id, $fecha_registro);
        
        if(!$stmt->execute()) {
            throw new Exception("Error al registrar cuenta por cobrar");
        }
        $stmt->close();
    }

    // CONFIRMAR TRANSACCIÓN
    $mysqli->commit();

    $datos = array(
        0 => "Almacenado",
        1 => "Registro Almacenado Correctamente",
        2 => "success",
        3 => "btn-primary",
        4 => "formulario_facturacion",
        5 => "Registro",
        6 => $tipo,
        7 => "",
        8 => $facturas_id,
    );

} catch (Exception $e) {
    // REVERTIR TRANSACCIÓN
    $mysqli->rollback();
    
    $datos = array(
        0 => "Error",
        1 => $e->getMessage(),
        2 => "error",
        3 => "btn-danger",
        4 => "",
        5 => "",
    );
}

echo json_encode($datos);