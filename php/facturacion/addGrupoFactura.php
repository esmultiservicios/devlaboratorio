<?php
//addGrupoFactura.php
session_start();
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

// Iniciar transacción
$mysqli->begin_transaction();

try {
    // Obtener datos del POST
    $pacientes_id = $_POST['clienteIDGrupo'];
    $fecha = date("Y-m-d");
    $colaborador_id = $_POST['colaborador_idGrupo'];
    $servicio_id = $_POST['servicio_idGrupo'];
    $notes = cleanStringStrtolower($_POST['notesBillGrupo']);
    $tamano = intval($_POST['tamano']);
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
    if(isset($_POST['facturas_grupal_activo'])) {
        if($_POST['facturas_grupal_activo'] == "") {
            $tipo_factura = 2;
            $tipo = "facturacionGrupalCredito";
        } else {
            $tipo_factura = $_POST['facturas_grupal_activo'];
            $tipo = "facturacionGrupal";
        }
    } else {
        $tipo_factura = 2;
        $tipo = "facturacionGrupalCredito";
    }

    $documento = "";
    if($tipo_factura === "1") {
        $documento = "1";//Factura Electronica
    } else {
       $documento = "4";//Factura Proforma
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

    // VERIFICAR RANGO
    $nuevo_numero = $numero + $incremento;
    if($nuevo_numero > $rango_final) {
        throw new Exception("Se ha alcanzado el límite del rango autorizado de facturación");
    }

    // VALIDAR DETALLES
    $tamano_tabla = 0;
    if(isset($_POST['pacienteIDBillGrupo'])) {
        if($_POST['pacienteIDBillGrupo'][0] != "" && $_POST['importeBillGrupo'][0] != "" && $_POST['totalBillGrupo'][0] != "") {
            $tamano_tabla = $tamano;
        }
    }

    if($tamano_tabla <= 0) {
        throw new Exception("El detalle de la factura no puede quedar vacío");
    }

    // INSERTAR FACTURA GRUPAL
    $facturas_grupal_id = correlativo("facturas_grupal_id","facturas_grupal");
    $insert = "INSERT INTO facturas_grupal
        VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    
    $stmt = $mysqli->prepare($insert);
    $stmt->bind_param(
        "iiiiiiisssiiiss", 
        $facturas_grupal_id,
        $secuencia_facturacion_id,
        $numero,
        $tipo_factura,
        $pacientes_id,
        $colaborador_id,
        $servicio_id,
        $importe,
        $notes,
        $fecha,
        $estado,
        $cierre,
        $usuario,
        $empresa_id,
        $fecha_registro
    );
    
    if(!$stmt->execute()) {
        throw new Exception("Error al guardar la factura grupal");
    }
    $stmt->close();

    // PROCESAR DETALLES
    $total_valor = 0;
    $descuentos = 0;
    $isv_neto = 0;

    for ($i = 0; $i < $tamano; $i++) {
        if(empty($_POST['billGrupoID'][$i]) || empty($_POST['pacienteIDBillGrupo'][$i]) || 
           empty($_POST['importeBillGrupo'][$i]) || empty($_POST['totalBillGrupo'][$i])) {
            continue;
        }

        $lineaFactura_id = $_POST['billGrupoID'][$i];
        $lineaPacientes_id = $_POST['pacienteIDBillGrupo'][$i];
        $lineaImporte = $_POST['importeBillGrupo'][$i];
        $lineaISV = $_POST['billGrupoISV'][$i] ?? 0;
        $lineaDescuento = $_POST['discountBillGrupo'][$i] ?? 0;
        $lineaTotal = $_POST['totalBillGrupo'][$i];
        $lineaCantidad = $_POST['quantyGrupoQuantity'][$i] ?? 1;
        $muestra_id = $_POST['billGrupoMuestraID'][$i] ?? '';

        // ACTUALIZAR FACTURA INDIVIDUAL CON SECUENCIA CORRECTA
        $update = "UPDATE facturas
            SET
                fecha = ?,
                number = ?,
                secuencia_facturacion_id = ?,
                estado = '2'
            WHERE facturas_id = ?";
        
        $stmt = $mysqli->prepare($update);
        $stmt->bind_param("siii", $fecha, $numero, $secuencia_facturacion_id, $lineaFactura_id);
        
        if(!$stmt->execute()) {
            throw new Exception("Error al actualizar factura individual");
        }
        $stmt->close();

        // INSERTAR DETALLE GRUPAL
        $facturas_grupal_detalle_id = correlativo("facturas_grupal_detalle_id","facturas_grupal_detalle");
        $insert_detalle = "INSERT INTO facturas_grupal_detalle
            VALUES(?,?,?,?,?,?,?,?,?)";
        
        $stmt = $mysqli->prepare($insert_detalle);
        $stmt->bind_param(
            "iiiisiddd", 
            $facturas_grupal_detalle_id,
            $facturas_grupal_id,
            $lineaFactura_id,
            $lineaPacientes_id,
            $muestra_id,
            $lineaCantidad,
            $lineaImporte,
            $lineaISV,
            $lineaDescuento
        );
        
        if(!$stmt->execute()) {
            throw new Exception("Error al guardar detalle de factura grupal");
        }
        $stmt->close();

        $total_valor += $lineaImporte;
        $descuentos += $lineaDescuento;
        $isv_neto += $lineaISV;
    }

    $total_despues_isv = ($total_valor + $isv_neto) - $descuentos;

    // ACTUALIZAR IMPORTE GRUPAL
    $update = "UPDATE facturas_grupal
        SET
            importe = ?,
            usuario = ?
        WHERE facturas_grupal_id = ?";
    
    $stmt = $mysqli->prepare($update);
    $stmt->bind_param("dii", $total_despues_isv, $usuario, $facturas_grupal_id);
    
    if(!$stmt->execute()) {
        throw new Exception("Error al actualizar importe de factura grupal");
    }
    $stmt->close();

    // ACTUALIZAR SECUENCIA
    $update = "UPDATE secuencia_facturacion
        SET
            siguiente = ?
        WHERE secuencia_facturacion_id = ?";
    
    $stmt = $mysqli->prepare($update);
    $stmt->bind_param("ii", $nuevo_numero, $secuencia_facturacion_id);
    
    if(!$stmt->execute()) {
        throw new Exception("Error al actualizar secuencia de facturación");
    }
    $stmt->close();

    // REGISTRAR CUENTA POR COBRAR GRUPAL
    $query_cxc = "SELECT cobrar_clientes_id FROM cobrar_clientes_grupales WHERE facturas_id = ?";
    $stmt = $mysqli->prepare($query_cxc);
    $stmt->bind_param("i", $facturas_grupal_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if($result->num_rows == 0) {
        $cobrar_clientes_id = correlativo("cobrar_clientes_id","cobrar_clientes_grupales");
        $insert_cxc = "INSERT INTO cobrar_clientes_grupales 
            (`cobrar_clientes_id`, `pacientes_id`, `facturas_id`, `fecha`, `saldo`, `estado`, `usuario`, `empresa_id`, `fecha_registro`) 
            VALUES(?,?,?,?,?,?,?,?,?)";
        
        $estado_cxc = 1;
        $stmt = $mysqli->prepare($insert_cxc);
        $stmt->bind_param(
            "iiisdiiss", 
            $cobrar_clientes_id,
            $pacientes_id,
            $facturas_grupal_id,
            $fecha,
            $total_despues_isv,
            $estado_cxc,
            $usuario,
            $empresa_id,
            $fecha_registro
        );
        
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
        4 => "formGrupoFacturacion",
        5 => "Registro",
        6 => $tipo,
        7 => "",
        8 => $facturas_grupal_id,
        9 => $numero,
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