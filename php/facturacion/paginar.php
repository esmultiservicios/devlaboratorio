<?php
session_start();
include "../funtions.php";

// CONEXION A DB
$mysqli = connect_mysqli();

// Obtener parámetros con valores por defecto
$colaborador_id = $_SESSION['colaborador_id'];
$paginaActual = isset($_POST['partida']) ? (int)$_POST['partida'] : 1;
$fechai = isset($_POST['fechai']) ? $_POST['fechai'] : date('Y-m-01');
$fechaf = isset($_POST['fechaf']) ? $_POST['fechaf'] : date('Y-m-d');
$dato = isset($_POST['dato']) ? $_POST['dato'] : '';
$tipo_paciente_grupo = isset($_POST['tipo_paciente_grupo']) ? (int)$_POST['tipo_paciente_grupo'] : '';
$pacientesIDGrupo = isset($_POST['pacientesIDGrupo']) ? (int)$_POST['pacientesIDGrupo'] : '';
$estado = isset($_POST['estado']) ? (int)$_POST['estado'] : 1;

// Inicializar variables de búsqueda
$busqueda_tipo_paciente_grupo = "";
$busqueda_pacientesIDGrupo = "";
$consulta_datos = "";
$busqueda_usuario = "";

// Construir condiciones de búsqueda seguras
if(!empty($dato)){
    $dato = $mysqli->real_escape_string($dato);
    $consulta_datos = "AND (p.expediente LIKE '%$dato%' OR p.nombre LIKE '%$dato%' OR p.apellido LIKE '%$dato%' OR CONCAT(p.apellido,' ',p.nombre) LIKE '%$dato%' OR f.number LIKE '%$dato%')";
}

if(!empty($tipo_paciente_grupo)){
    $busqueda_tipo_paciente_grupo = "AND p.tipo_paciente_id = '$tipo_paciente_grupo'";
}

if(!empty($pacientesIDGrupo)){
    $busqueda_pacientesIDGrupo = "AND p.pacientes_id = '$pacientesIDGrupo'";
}

// Filtrar por usuario actual si el estado es 2 (Pagada) o 4 (Crédito)
if($estado == 2 || $estado == 4){
    $busqueda_usuario = "AND f.usuario = '$colaborador_id'";
}

// Configuración de paginación
$nroLotes = 200;
$limit = ($paginaActual <= 1) ? 0 : $nroLotes * ($paginaActual - 1);

// Consulta optimizada con JOIN para obtener detalles
$registro = "SELECT SQL_CALC_FOUND_ROWS 
    f.facturas_id, 
    DATE_FORMAT(f.fecha, '%d/%m/%Y') AS 'fecha', 
    CONCAT(p.nombre,' ',p.apellido) AS 'empresa', 
    p.identidad AS 'identidad', 
    CONCAT(c.nombre,' ',c.apellido) AS 'profesional', 
    f.estado, 
    s.nombre AS 'consultorio', 
    sc.prefijo, 
    f.number, 
    sc.relleno, 
    CONCAT(p1.nombre,' ',p1.apellido) AS 'paciente', 
    p1.pacientes_id AS 'codigoPacienteEmpresa', 
    f.muestras_id, 
    c.colaborador_id, 
    m.number AS 'muestra',
    COALESCE(SUM(fd.precio), 0) AS total_precio,
    COALESCE(SUM(fd.cantidad), 0) AS total_cantidad,
    COALESCE(SUM(fd.descuento), 0) AS total_descuento,
    COALESCE(SUM(fd.isv_valor), 0) AS total_isv,
    COALESCE(SUM(fd.precio * fd.cantidad), 0) AS neto_antes_isv
    FROM facturas AS f
    INNER JOIN pacientes AS p ON f.pacientes_id = p.pacientes_id
    INNER JOIN secuencia_facturacion AS sc ON f.secuencia_facturacion_id = sc.secuencia_facturacion_id
    INNER JOIN servicios AS s ON f.servicio_id = s.servicio_id
    INNER JOIN colaboradores AS c ON f.colaborador_id = c.colaborador_id
    LEFT JOIN muestras_hospitales AS mh ON f.muestras_id = mh.muestras_id
    LEFT JOIN pacientes AS p1 ON mh.pacientes_id = p1.pacientes_id
    LEFT JOIN muestras AS m ON f.muestras_id = m.muestras_id
    LEFT JOIN facturas_detalle AS fd ON f.facturas_id = fd.facturas_id
    WHERE f.estado = '$estado' 
    AND f.fecha BETWEEN '$fechai' AND '$fechaf'
    $busqueda_usuario
    $busqueda_tipo_paciente_grupo
    $busqueda_pacientesIDGrupo
    $consulta_datos
    GROUP BY f.facturas_id
    ORDER BY f.fecha DESC, f.facturas_id DESC
    LIMIT $limit, $nroLotes";

$result = $mysqli->query($registro) or die($mysqli->error);

// Obtener el total de registros de manera eficiente
$total_registros = $mysqli->query("SELECT FOUND_ROWS()")->fetch_row()[0];
$nroPaginas = ceil($total_registros / $nroLotes);

// Construir paginación
$lista = '';
if($nroPaginas > 1){
    if($paginaActual > 1){
        $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(1);">Inicio</a></li>';
        $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination('.($paginaActual-1).');">Anterior '.($paginaActual-1).'</a></li>';
    }
    
    if($paginaActual < $nroPaginas){
        $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination('.($paginaActual+1).');">Siguiente '.($paginaActual+1).' de '.$nroPaginas.'</a></li>';
        $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination('.($nroPaginas).');">Ultima</a></li>';
    }
}

// Configurar textos según estado
$estados_config = [
    1 => ['estado_' => "Borrador", 'texto1' => "Facturar", 'texto2' => "Eliminar"],
    2 => ['estado_' => "Pagada", 'texto1' => "Enviar", 'texto2' => "Imprimir"],
    4 => ['estado_' => "Crédito", 'texto1' => "Imprimir", 'texto2' => "Cobrar"],
    3 => ['estado_' => "Cancelada", 'texto1' => "Imprimir", 'texto2' => ""]
];

$config_estado = $estados_config[$estado] ?? ['estado_' => "", 'texto1' => "", 'texto2' => ""];
$estado_ = $config_estado['estado_'];
$texto1 = $config_estado['texto1'];
$texto2 = $config_estado['texto2'];

// Plantillas de botones por estado
$botones_por_estado = [
    1 => [
        'texto1' => '<a class="btn btn-secondary ml-2" href="javascript:pay(%d);"><i class="fas fa-file-invoice fa-lg"></i> Facturar</a>',
        'texto2' => '<a class="btn btn-secondary ml-2" href="javascript:deleteBill(%d);"><i class="fas fa-trash fa-lg"></i> Eliminar</a>'
    ],
    2 => [
        'texto1' => '<a class="btn btn-secondary ml-2" href="javascript:mailBill(%d);"><i class="far fa-paper-plane fa-lg"></i> Enviar</a>',
        'texto2' => '<a class="btn btn-secondary ml-2" href="javascript:printBill(%d);"><i class="fas fa-print fa-lg"></i> Imprimir</a>'
    ],
    4 => [
        'texto1' => '<a class="btn btn-secondary ml-2" href="javascript:printBill(%d);"><i class="fas fa-print fa-lg"></i> Imprimir</a>',
        'texto2' => '<a class="btn btn-secondary ml-2" href="javascript:pago(%d);"><i class="fab fa-amazon-pay fa-lg"></i> Cobrar</a>'
    ],
    3 => [
        'texto1' => '<a class="btn btn-secondary ml-2" href="javascript:printBill(%d);"><i class="fas fa-print fa-lg"></i> Imprimir</a>',
        'texto2' => ''
    ]
];

// Construir tabla
$tabla = '<table class="table table-striped table-condensed table-hover">
    <thead>
        <tr>
            <th width="2.66%"><input id="checkAllFactura" class="formcontrol" type="checkbox"></th>
            <th width="2.66%">No.</th>
            <th width="4.66%">Fecha</th>
            <th width="7.66%">Muestra</th>
            <th width="8.66%">Factura</th>
            <th width="8.66%">Empresa</th>
            <th width="6.66%">Identidad</th>
            <th width="6.66%">Profesional</th>
            <th width="6.66%">Importe</th>
            <th width="6.66%">ISV</th>
            <th width="6.66%">Descuento</th>
            <th width="6.66%">Neto</th>
            <th width="3.66%">Estado</th>
            <th width="8.66%">'.$texto1.'</th>
            <th width="8.66%">'.$texto2.'</th>
        </tr>
    </thead>
    <tbody>';

if($total_registros > 0){
    $i = 1;
    while($registro2 = $result->fetch_assoc()){
        $total = ($registro2['neto_antes_isv'] + $registro2['total_isv']) - $registro2['total_descuento'];
        $numero = ($registro2['number'] == 0) ? "Aún no se ha generado" : $registro2['prefijo'].''.rellenarDigitos($registro2['number'], $registro2['relleno']);
        
        // Formatear información del paciente/empresa
        $paciente = $registro2['paciente'];
        $empresa = ($paciente != "") ? $registro2['empresa']." (<b>Paciente</b>: ".$paciente.")" : $registro2['empresa'];
        
        // Obtener botones según estado
        $botones = $botones_por_estado[$registro2['estado']] ?? [];
        $texto1_btn = isset($botones['texto1']) ? sprintf($botones['texto1'], $registro2['facturas_id']) : '';
        $texto2_btn = isset($botones['texto2']) ? sprintf($botones['texto2'], $registro2['facturas_id']) : '';
        
        // Construir fila
        $tabla .= sprintf('<tr>
            <td><input class="itemRowFactura" type="checkbox" name="itemFactura" id="itemFactura_%d" value="%d"></td>
            <td>%d</td>
            <td>%s</td>
            <td>%s</td>
            <td>%s</td>
            <td>%s</td>
            <td>%s</td>
            <td>%s</td>
            <td>%s</td>
            <td>%s</td>
            <td>%s</td>
            <td>
                <div name="quantyGrupoQuantityValor" id="quantyGrupoQuantityValor_%d" data-value="%d"></div>
                <div name="profesionalIDGrupo" id="profesionalIDGrupo_%d" data-value="%d"></div>
                <div name="muestraGrupo" id="muestraGrupo_%d" data-value="%d"></div>
                <div name="codigoFacturaGrupo" id="codigoFacturaGrupo_%d" data-value="%d"></div>
                <div name="pacientesIDFacturaGrupo" id="pacientesIDFacturaGrupo_%d" data-value="%d"></div>
                <div name="importeFacturaGrupo" id="importeFacturaGrupo_%d" data-value="%.2f"></div>%.2f
                <div name="ISVFacturaGrupo" id="precioFacturaGrupo_%d" data-value="%.2f"></div>
                <div name="ISVFacturaGrupo" id="ISVFacturaGrupo_%d" data-value="%.2f"></div>
                <div name="DescuentoFacturaGrupo" id="DescuentoFacturaGrupo_%d" data-value="%.2f"></div>
                <div name="DescuentoFacturaGrupo" id="netoAntesISVFacturaGrupo_%d" data-value="%.2f"></div>
            </td>
            <td>%s</td>
            <td>%s</td>
            <td>%s</td>
        </tr>',
        $i-1, $registro2['facturas_id'], // Para el checkbox
        $i++, // Número de fila
        $registro2['fecha'],
        $registro2['muestra'],
        $numero,
        $empresa,
        $registro2['identidad'],
        $registro2['profesional'],
        number_format($registro2['total_precio'], 2),
        number_format($registro2['total_isv'], 2),
        number_format($registro2['total_descuento'], 2),
        $registro2['facturas_id'], $registro2['total_cantidad'],
        $registro2['facturas_id'], $registro2['colaborador_id'],
        $registro2['facturas_id'], $registro2['muestras_id'],
        $registro2['facturas_id'], $registro2['facturas_id'],
        $registro2['facturas_id'], $registro2['codigoPacienteEmpresa'],
        $registro2['facturas_id'], $total,
        $total,
        $registro2['facturas_id'], $registro2['total_precio'],
        $registro2['facturas_id'], $registro2['total_isv'],
        $registro2['facturas_id'], $registro2['total_descuento'],
        $registro2['facturas_id'], $registro2['neto_antes_isv'],
        $estado_,
        $texto1_btn,
        $texto2_btn);
    }
} else {
    $tabla .= '<tr><td colspan="15" style="color:#C7030D">No se encontraron resultados para los filtros seleccionados</td></tr>';
}

$tabla .= '</tbody></table>';

// Agregar total de registros si hay resultados
if($total_registros > 0){
    $tabla .= '<div class="text-center mt-3"><b>Total de Registros Encontrados: '.$total_registros.'</b></div>';
}

// Preparar respuesta JSON
$array = array(
    0 => $tabla,
    1 => $lista
);

echo json_encode($array);

// Liberar recursos
if(isset($result)) $result->free();
$mysqli->close();