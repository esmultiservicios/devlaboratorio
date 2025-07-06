<?php
session_start();
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

$colaborador_id = $_SESSION['colaborador_id'];
$paginaActual = isset($_POST['partida']) ? $_POST['partida'] : 1;
$fechai = isset($_POST['fechai']) ? $_POST['fechai'] : date('Y-m-01');
$fechaf = isset($_POST['fechaf']) ? $_POST['fechaf'] : date('Y-m-d');
$dato = isset($_POST['dato']) ? $_POST['dato'] : '';
$tipo_paciente_grupo = isset($_POST['tipo_paciente_grupo']) ? $_POST['tipo_paciente_grupo'] : '';
$pacientesIDGrupo = isset($_POST['pacientesIDGrupo']) ? $_POST['pacientesIDGrupo'] : '';
$estado = isset($_POST['estado']) ? $_POST['estado'] : 1;
$usuario = $_SESSION['colaborador_id'];

// Inicializar variables de búsqueda
$busqueda_tipo_paciente_grupo = "";
$busqueda_pacientesIDGrupo = "";
$consulta_datos = "";
$busqueda_usuario = "";

// Construir condiciones de búsqueda
if(!empty($dato)){
    $consulta_datos = "AND (p.expediente LIKE '%$dato%' OR p.nombre LIKE '%$dato%' OR p.apellido LIKE '%$dato%' OR CONCAT(p.apellido,' ',p.nombre) LIKE '%$dato%' OR f.number LIKE '%$dato%')";
}

if(!empty($tipo_paciente_grupo)){
    $busqueda_tipo_paciente_grupo = "AND p.tipo_paciente_id = '$tipo_paciente_grupo'";
}

if(!empty($pacientesIDGrupo)){
    $busqueda_pacientesIDGrupo = "AND p.pacientes_id = '$pacientesIDGrupo'";
}

// Si el estado es 2 (Pagada) o 4 (Crédito), filtrar por usuario actual
if($estado == 2 || $estado == 4){
    $busqueda_usuario = "AND f.usuario = '$colaborador_id'";
}

// Consulta principal para contar registros
$query_count = "SELECT COUNT(f.facturas_id) AS total
    FROM facturas AS f
    INNER JOIN pacientes AS p ON f.pacientes_id = p.pacientes_id
    WHERE f.estado = '$estado' 
    AND f.fecha BETWEEN '$fechai' AND '$fechaf'
    $busqueda_usuario
    $busqueda_tipo_paciente_grupo
    $busqueda_pacientesIDGrupo
    $consulta_datos";

$result_count = $mysqli->query($query_count) or die($mysqli->error);
$total_registros = $result_count->fetch_assoc()['total'];

// Configuración de paginación
$nroLotes = 200;
$nroPaginas = ceil($total_registros/$nroLotes);
$lista = '';
$tabla = '';

// Construir paginación
if($paginaActual > 1){
    $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(1);void(0);">Inicio</a></li>';
}

if($paginaActual > 1){
    $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination('.($paginaActual-1).');void(0);">Anterior '.($paginaActual-1).'</a></li>';
}

if($paginaActual < $nroPaginas){
    $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination('.($paginaActual+1).');void(0);">Siguiente '.($paginaActual+1).' de '.$nroPaginas.'</a></li>';
}

if($paginaActual > 1){
    $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination('.($nroPaginas).');void(0);">Ultima</a></li>';
}

// Calcular límite para la consulta
$limit = ($paginaActual <= 1) ? 0 : $nroLotes*($paginaActual-1);

// Consulta para obtener los registros
$registro = "SELECT f.facturas_id AS facturas_id, 
    DATE_FORMAT(f.fecha, '%d/%m/%Y') AS 'fecha', 
    CONCAT(p.nombre,' ',p.apellido) AS 'empresa', 
    p.identidad AS 'identidad', 
    CONCAT(c.nombre,' ',c.apellido) AS 'profesional', 
    f.estado AS 'estado', 
    s.nombre AS 'consultorio', 
    sc.prefijo AS 'prefijo', 
    f.number AS 'numero', 
    sc.relleno AS 'relleno', 
    CONCAT(p1.nombre,' ',p1.apellido) AS 'paciente', 
    p1.pacientes_id AS 'codigoPacienteEmpresa', 
    f.muestras_id AS 'muestras_id', 
    c.colaborador_id AS 'colaborador_id', 
    m.number AS 'muestra'
    FROM facturas AS f
    INNER JOIN pacientes AS p ON f.pacientes_id = p.pacientes_id
    INNER JOIN secuencia_facturacion AS sc ON f.secuencia_facturacion_id = sc.secuencia_facturacion_id
    INNER JOIN servicios AS s ON f.servicio_id = s.servicio_id
    INNER JOIN colaboradores AS c ON f.colaborador_id = c.colaborador_id
    LEFT JOIN muestras_hospitales AS mh ON f.muestras_id = mh.muestras_id
    LEFT JOIN pacientes AS p1 ON mh.pacientes_id = p1.pacientes_id
    LEFT JOIN muestras AS m ON f.muestras_id = m.muestras_id
    WHERE f.estado = '$estado' 
    AND f.fecha BETWEEN '$fechai' AND '$fechaf'
    $busqueda_usuario
    $busqueda_tipo_paciente_grupo
    $busqueda_pacientesIDGrupo
    $consulta_datos
    ORDER BY f.fecha DESC, f.facturas_id DESC
    LIMIT $limit, $nroLotes";
    
$result = $mysqli->query($registro) or die($mysqli->error);

// Configurar textos según estado
$estado_ = "";
$texto1 = "";
$texto2 = "";

switch($estado){
    case 1:
        $estado_ = "Borrador";
        $texto1 = "Facturar";
        $texto2 = "Eliminar";
        break;
    case 2:
        $estado_ = "Pagada";
        $texto1 = "Enviar";
        $texto2 = "Imprimir";
        break;
    case 4:
        $estado_ = "Crédito";
        $texto1 = "Imprimir";
        $texto2 = "Cobrar";
        break;
    default:
        $estado_ = "Cancelada";
        $texto1 = "Imprimir";
        break;
}

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

$i = 1;
$fila = 0;

if($total_registros > 0){
    while($registro2 = $result->fetch_assoc()){
        $facturas_id = $registro2['facturas_id'];

        // Consultar detalles de la factura
        $query_detalle = "SELECT cantidad, precio, descuento, isv_valor
            FROM facturas_detalle
            WHERE facturas_id = '$facturas_id'";
        $result_detalles = $mysqli->query($query_detalle) or die($mysqli->error);

        $cantidad = 0;
        $descuento = 0;
        $precio = 0;
        $total_precio = 0;
        $total = 0;
        $isv_neto = 0;
        $neto_antes_isv = 0;
        $total_neto_general = 0;
        $cantidad_ = 0;

        while($registrodetalles = $result_detalles->fetch_assoc()){
            $precio += $registrodetalles["precio"];
            $cantidad += $registrodetalles["cantidad"];
            $descuento += $registrodetalles["descuento"];
            $total_precio = $registrodetalles["precio"] * $registrodetalles["cantidad"];
            $neto_antes_isv += $total_precio;
            $isv_neto += $registrodetalles["isv_valor"];
            $cantidad_ = $registrodetalles["cantidad"];
        }

        $total = ($neto_antes_isv + $isv_neto) - $descuento;

        // Formatear número de factura
        $numero = ($registro2['numero'] == 0) ? "Aún no se ha generado" : $registro2['prefijo'].''.rellenarDigitos($registro2['numero'], $registro2['relleno']);

        // Configurar botones según estado
        $estado = $registro2['estado'];
        $factura = $factura1 = $eliminar = $pay = $send_mail = $pay_credit = $factura2 = "";

        switch($estado){
            case 1:
                $eliminar = '<a class="btn btn btn-secondary ml-2" href="javascript:deleteBill('.$registro2['facturas_id'].');void(0);"><div class="sb-nav-link-icon"></div><i class="fas fa-trash fa-lg"></i> Eliminar</a>';
                $pay = '<a class="btn btn btn-secondary ml-2" href="javascript:pay('.$registro2['facturas_id'].');void(0);"><div class="sb-nav-link-icon"></div><i class="fas fa-file-invoice fa-lg"></i> Facturar</a>';
                break;
            case 2:
                $factura1 = '<a class="btn btn btn-secondary ml-2" href="javascript:printBill('.$registro2['facturas_id'].');void(0);"><div class="sb-nav-link-icon"></div><i class="fas fa-print fa-lg"></i> Imprimir</a>';
                $send_mail = '<a class="btn btn btn-secondary ml-2" href="javascript:mailBill('.$registro2['facturas_id'].');void(0);"><div class="sb-nav-link-icon"></div><i class="far fa-paper-plane fa-lg" title="Enviar Factura por Correo"></i> Enviar</a>';
                break;
            case 3:
                $factura = '<a class="btn btn btn-secondary ml-2" href="javascript:printBill('.$registro2['facturas_id'].');void(0);"><div class="sb-nav-link-icon"></div><i class="fas fa-print fa-lg"></i> Imprimir</a>';
                break;
            case 4:
                $pay_credit = '<a class="btn btn btn-secondary ml-2" href="javascript:pago('.$registro2['facturas_id'].');void(0);"><div class="sb-nav-link-icon"></div><i class="fab fa-amazon-pay fa-lg" title="Pagar Factura"></i> Cobrar</a>';
                $factura2 = '<a class="btn btn btn-secondary ml-2" href="javascript:printBill('.$registro2['facturas_id'].');void(0);"><div class="sb-nav-link-icon"></div><i class="fas fa-print fa-lg"></i> Imprimir</a>';    
                break;
        }

        // Formatear información del paciente/empresa
        $paciente = $registro2['paciente'];
        $empresa = ($paciente != "") ? $registro2['empresa']." (<b>Paciente</b>: ".$paciente.")" : $registro2['empresa'];

        $paciente_empresa = $registro2['codigoPacienteEmpresa'];
        $muestras_id = $registro2['muestras_id'];
        $profesional = $registro2['profesional'];
        $colaborador_id = $registro2['colaborador_id'];

        // Agregar fila a la tabla
        $tabla .= '<tr>
            <td><input class="itemRowFactura" type="checkbox" name="itemFactura" id="itemFactura_'.$fila.'" value="'.$facturas_id.'"></td>
            <td>'.$i.'</td>
            <td>'.$registro2['fecha'].'</td>
            <td>'.$registro2['muestra'].'</td>
            <td>'.$numero.'</td>
            <td>'.$empresa.'</td>
            <td>'.$registro2['identidad'].'</td>
            <td>'.$registro2['profesional'].'</td>
            <td>'.number_format($precio,2).'</td>
            <td>'.number_format($isv_neto,2).'</td>
            <td>'.number_format($descuento,2).'</td>
            <td>
                <div name="quantyGrupoQuantityValor" id="quantyGrupoQuantityValor_'.$facturas_id.'" data-value='.$cantidad_.'></div>
                <div name="profesionalIDGrupo" id="profesionalIDGrupo_'.$facturas_id.'" data-value='.$colaborador_id.'></div>
                <div name="muestraGrupo" id="muestraGrupo_'.$facturas_id.'" data-value='.$muestras_id.'></div>
                <div name="codigoFacturaGrupo" id="codigoFacturaGrupo_'.$facturas_id.'" data-value='.$facturas_id.'></div>
                <div name="pacientesIDFacturaGrupo" id="pacientesIDFacturaGrupo_'.$facturas_id.'" data-value='.$paciente_empresa.'></div>
                <div name="importeFacturaGrupo" id="importeFacturaGrupo_'.$facturas_id.'" data-value='.$total.'></div>'.number_format($total,2).'
                <div name="ISVFacturaGrupo" id="precioFacturaGrupo_'.$facturas_id.'" data-value='.$precio.'></div>
                <div name="ISVFacturaGrupo" id="ISVFacturaGrupo_'.$facturas_id.'" data-value='.$isv_neto.'></div>
                <div name="DescuentoFacturaGrupo" id="DescuentoFacturaGrupo_'.$facturas_id.'" data-value='.$descuento.'></div>
                <div name="DescuentoFacturaGrupo" id="netoAntesISVFacturaGrupo_'.$facturas_id.'" data-value='.$neto_antes_isv.'></div>
            </td>
            <td>'.$estado_.'</td>
            <td>
              '.$pay.'
                '.$send_mail.'
                '.$factura.'
                '.$factura2.'
            </td>
            <td>
                '.$pay_credit.'
                '.$eliminar.'
                '.$factura1.'
            </td>
        </tr>';
        $i++;
        $fila++;
    }
} else {
    $tabla .= '<tr>
        <td colspan="15" style="color:#C7030D">No se encontraron resultados para los filtros seleccionados</td>
    </tr>';
}

$tabla .= '</tbody></table>';

// Agregar total de registros si hay resultados
if($total_registros > 0){
    $tabla .= '<div class="text-center mt-3">
        <b>Total de Registros Encontrados: '.$total_registros.'</b>
    </div>';
}

$array = array(
    0 => $tabla,
    1 => $lista
);

echo json_encode($array);

// Liberar recursos
if(isset($result)) $result->free();
if(isset($result_count)) $result_count->free();
$mysqli->close();