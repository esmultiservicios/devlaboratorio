<?php
//DetallesFacturacion.php
include '../funtions.php';

// Procesamiento normal para la tabla de facturas
try {
    // Verificar sesión
    // Instanciar mainModel
    $insMainModel = new mainModel();

    // Validar sesión primero
    $validacion = $insMainModel->validarSesion();
    if($validacion['error']) {
        echo json_encode([
            'type' => 'error',
            'title' => 'Error de sesión',
            'message' => $validacion['mensaje']
        ]);
        exit();
    }

    $users_id = $_SESSION['colaborador_id']; // Obtén directamente el ID aquí
    
    // 1. Obtener server_customers_id del usuario
    $conexionPrincipal = $insMainModel->connection();
    $queryUsuario = "SELECT server_customers_id FROM users WHERE id = ?";
    $stmtUsuario = $conexionPrincipal->prepare($queryUsuario);
    $stmtUsuario->bind_param("i", $users_id);
    $stmtUsuario->execute();
    $resultUsuario = $stmtUsuario->get_result();
    
    if ($resultUsuario->num_rows == 0) {
        $stmtUsuario->close();
        $conexionPrincipal->close();
        echo json_encode([
            'echo' => 1,
            'totalrecords' => 0,
            'totaldisplayrecords' => 0,
            'data' => [],
            'type' => 'error',
            'title' => 'Error de usuario',
            'message' => 'Usuario no tiene una db asociada'
        ]);
        exit();
    }
    
    $usuarioData = $resultUsuario->fetch_assoc();
    $serverCustomersId = $usuarioData['server_customers_id'];
    $stmtUsuario->close();

    // 2. Conectar a la base de datos del cliente
    $configCliente = [
        'host' => SERVER,
        'user' => USER,
        'pass' => PASS,
        'name' => DB_MAIN
    ];
    
    $conexionCliente = $insMainModel->connectToDatabase($configCliente);
    
    if (!$conexionCliente) {
        echo json_encode([
            'echo' => 1,
            'totalrecords' => 0,
            'totaldisplayrecords' => 0,
            'data' => [],
            'type' => 'error',
            'title' => 'Error de conexión',
            'message' => 'No se pudo conectar a la base de datos del cliente'
        ]);
        exit();
    }

    // 3. Obtener cliente_id
    $queryCliente = "SELECT clientes_id FROM server_customers WHERE server_customers_id = ?";
    $stmtCliente = $conexionCliente->prepare($queryCliente);
    $stmtCliente->bind_param("i", $serverCustomersId);
    $stmtCliente->execute();
    $resultCliente = $stmtCliente->get_result();
    
    if ($resultCliente->num_rows == 0) {
        $stmtCliente->close();
        $conexionCliente->close();
        echo json_encode([
            'echo' => 1,
            'totalrecords' => 0,
            'totaldisplayrecords' => 0,
            'data' => [],
            'type' => 'error',
            'title' => 'Error de usuario',
            'message' => 'Usuario no tiene un cliente asociado'
        ]);
        exit();
    }            

    $clienteData = $resultCliente->fetch_assoc();
    $clientes_id = $clienteData['clientes_id'];
    $stmtCliente->close();

    // 4. Consulta para obtener las facturas del cliente
    $where = "f.clientes_id = ?";
    $params = [$clientes_id];
    $paramTypes = "i";
    
    // Aplicar filtros si existen
    if(isset($_GET['fecha_inicio']) && !empty($_GET['fecha_inicio'])) {
        $where .= " AND f.fecha >= ?";
        $params[] = $_GET['fecha_inicio'];
        $paramTypes .= "s";
    }
    
    if(isset($_GET['fecha_fin']) && !empty($_GET['fecha_fin'])) {
        $where .= " AND f.fecha <= ?";
        $params[] = $_GET['fecha_fin'];
        $paramTypes .= "s";
    }
    
    if(isset($_GET['tipo_factura']) && !empty($_GET['tipo_factura'])) {
        $where .= " AND f.tipo_factura = ?";
        $params[] = $_GET['tipo_factura'];
        $paramTypes .= "i";
    }
    
    if(isset($_GET['estado_factura']) && !empty($_GET['estado_factura'])) {
        $where .= " AND f.estado = ?";
        $params[] = $_GET['estado_factura'];
        $paramTypes .= "i";
    }
    
    if(isset($_GET['numero_factura']) && !empty($_GET['numero_factura'])) {
        $where .= " AND CONCAT(sf.prefijo, LPAD(f.number, sf.relleno, 0)) LIKE ?";
        $params[] = '%' . $_GET['numero_factura'] . '%';
        $paramTypes .= "s";
    }

    // Consulta modificada para incluir estado de cobrar_clientes
    $query = "SELECT 
        f.facturas_id, 
        DATE_FORMAT(f.fecha, '%d/%m/%Y') AS fecha, 
        c.nombre AS cliente,
        CASE 
            WHEN d.documento_id = 4 THEN CONCAT('PROFORMA-', sf.prefijo, LPAD(f.number, sf.relleno, 0)) 
            ELSE CONCAT(sf.prefijo, '', LPAD(f.number, sf.relleno, 0))
        END AS numero, 
        f.importe AS total,
        CASE 
            WHEN f.tipo_factura = 1 THEN 'Contado' 
            ELSE 'Crédito' 
        END AS tipo_documento,
        co.nombre AS vendedor,
        co1.nombre AS facturador,
        (SELECT SUM(fd.cantidad * fd.precio) FROM facturas_detalles AS fd WHERE fd.facturas_id = f.facturas_id) AS subtotal,
        (SELECT SUM(fd.cantidad * p.precio_compra) FROM facturas_detalles AS fd 
            INNER JOIN productos AS p ON fd.productos_id = p.productos_id WHERE fd.facturas_id = f.facturas_id) AS subCosto,
        (SELECT SUM(fd.isv_valor) FROM facturas_detalles AS fd WHERE fd.facturas_id = f.facturas_id) AS isv,
        (SELECT SUM(fd.descuento) FROM facturas_detalles AS fd WHERE fd.facturas_id = f.facturas_id) AS descuento,
        (SELECT COUNT(*) FROM cobrar_clientes WHERE facturas_id = f.facturas_id AND estado = 2) AS pagos_realizados,
        f.estado,
        CASE
            WHEN d.documento_id = 4 AND (SELECT COUNT(*) FROM cobrar_clientes WHERE facturas_id = f.facturas_id AND estado = 1) > 0 THEN 'Pendiente de pago'
            WHEN d.documento_id = 4 THEN 'Proforma cerrada'
            WHEN f.estado = 1 THEN 'Borrador'
            WHEN f.estado = 2 THEN 'Pagada'
            WHEN f.estado = 3 THEN 'Crédito'
            WHEN f.estado = 4 THEN 'Cancelada'
            ELSE 'Borrador'
        END AS estado_texto,
        d.documento_id, -- Añadir documento_id para identificar proformas
        (SELECT COUNT(*) FROM cobrar_clientes WHERE facturas_id = f.facturas_id AND estado = 1) AS tiene_pendiente,
        f.notas,
        ? AS db_name
    FROM 
        facturas AS f
        INNER JOIN clientes AS c ON f.clientes_id = c.clientes_id
        INNER JOIN colaboradores AS co ON f.colaboradores_id = co.colaboradores_id
        INNER JOIN colaboradores AS co1 ON f.usuario = co1.colaboradores_id
        INNER JOIN secuencia_facturacion AS sf ON f.secuencia_facturacion_id = sf.secuencia_facturacion_id
        INNER JOIN documento AS d ON sf.documento_id = d.documento_id
    WHERE $where
    ORDER BY f.number DESC";
    
    // Agregar el nombre de la base de datos como primer parámetro
    array_unshift($params, DB_MAIN);
    $paramTypes = "s" . $paramTypes;
    
    $stmt = $conexionCliente->prepare($query);
    $stmt->bind_param($paramTypes, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while($row = $result->fetch_assoc()) {
        $ganancia = doubleval($row['subtotal']) - doubleval($row['subCosto']) - doubleval($row['isv']) - doubleval($row['descuento']);
        
        $data[] = [
            'facturas_id' => $row['facturas_id'],
            'fecha' => $row['fecha'],
            'tipo_documento' => $row['tipo_documento'],
            'cliente' => $row['cliente'],
            'numero' => $row['numero'],
            'subtotal' => $row['subtotal'],
            'ganancia' => $ganancia,
            'isv' => $row['isv'],
            'descuento' => $row['descuento'],
            'total' => $row['total'],
            'vendedor' => $row['vendedor'],
            'facturador' => $row['facturador'],
            'estado' => $row['estado'],
            'estado_texto' => $row['estado_texto'],
            'documento_id' => $row['documento_id'],
            'tiene_pendiente' => $row['tiene_pendiente'],
            'notas' => $row['notas'],
            'db_name' => $row['db_name']
        ];
    }

    // 5. Devolver respuesta en formato DataTables
    $response = [
        'echo' => 1,
        'totalrecords' => count($data),
        'totaldisplayrecords' => count($data),
        'data' => $data,
        'type' => 'success',
        'title' => 'Éxito',
        'message' => 'Datos cargados correctamente'
    ];
    
    echo json_encode($response);
    $conexionCliente->close();
    
} catch(Exception $e) {
    echo json_encode([
        'echo' => 1,
        'totalrecords' => 0,
        'totaldisplayrecords' => 0,
        'data' => [],
        'type' => 'error',
        'title' => 'Error del sistema',
        'message' => 'Ocurrió un error inesperado: ' . $e->getMessage()
    ]);
}