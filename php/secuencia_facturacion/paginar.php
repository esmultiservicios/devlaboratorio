<?php 
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$colaborador_id = $_SESSION['colaborador_id'];
$paginaActual = $_POST['partida'];
$dato = $_POST['dato'];
$empresa = $_POST['empresa'];
$estado = $_POST['estado'];
$documento = $_POST['documento'];

// Inicializamos la parte común de la consulta
$where = "WHERE sf.activo = '$estado' AND (e.nombre LIKE '%$dato%' OR sf.siguiente LIKE '$dato%')";

// Comprobamos si $documento no está vacío
if($documento !== ''){
    $where .= " AND sf.documento_id = '$documento'";
}

// Comprobamos si $empresa es distinto de 0
if($empresa != 0){
    $where .= " AND sf.empresa_id = '$empresa'";
}

$query = "SELECT sf.secuencia_facturacion_id AS 'secuencia_facturacion_id', e.nombre AS 'empresa', d.nombre AS 'documento', sf.cai AS 'cai', e.rtn AS 'rtn', sf.prefijo AS 'prefijo', sf.siguiente AS 'siguiente', CONCAT(sf.prefijo, '', sf.rango_inicial) AS 'rango_inicial', CONCAT(sf.prefijo, '', sf.rango_final) AS 'rango_final', sf.fecha_limite AS 'fecha_limite', sf.fecha_activacion AS 'fecha_activacion',
(CASE WHEN sf.activo = '1' THEN 'Sí' ELSE 'No' END) AS 'activo',
CAST(sf.fecha_registro AS DATE) AS 'fecha_registro', sf.relleno AS 'relleno'
	FROM secuencia_facturacion AS sf
	INNER JOIN empresa AS e
	ON sf.empresa_id = e.empresa_id
	INNER JOIN documento AS d
	ON sf.documento_id = d.documento_id
	".$where."
	ORDER BY sf.secuencia_facturacion_id ASC";
$result = $mysqli->query($query);

$nroLotes = 25;
$nroProductos = $result->num_rows;
$nroPaginas = ceil($nroProductos/$nroLotes);
$lista = '';
$tabla = '';

if($paginaActual > 1){
	$lista = $lista.'<li class="page-item"><a class="page-link" href="javascript:pagination('.(1).');void(0);">Inicio</a></li>';
}

if($paginaActual > 1){
	$lista = $lista.'<li class="page-item"><a class="page-link" href="javascript:pagination('.($paginaActual-1).');void(0);">Anterior '.($paginaActual-1).'</a></li>';
}

if($paginaActual < $nroPaginas){
	$lista = $lista.'<li class="page-item"><a class="page-link" href="javascript:pagination('.($paginaActual+1).');void(0);">Siguiente '.($paginaActual+1).' de '.$nroPaginas.'</a></li>';
}

if($paginaActual > 1){
	$lista = $lista.'<li class="page-item"><a class="page-link" href="javascript:pagination('.($nroPaginas).');void(0);">Ultima</a></li>';
}

if($paginaActual <= 1){
	$limit = 0;
}else{
	$limit = $nroLotes*($paginaActual-1);
}

$registro = "SELECT sf.secuencia_facturacion_id AS 'secuencia_facturacion_id', e.nombre AS 'empresa', d.nombre AS 'documento', sf.cai AS 'cai', e.rtn AS 'rtn', sf.prefijo AS 'prefijo', sf.siguiente AS 'siguiente', CONCAT(sf.prefijo, '', sf.rango_inicial) AS 'rango_inicial', CONCAT(sf.prefijo, '', sf.rango_final) AS 'rango_final', sf.fecha_limite AS 'fecha_limite', sf.fecha_activacion AS 'fecha_activacion',
(CASE WHEN sf.activo = '1' THEN 'Sí' ELSE 'No' END) AS 'activo',
CAST(sf.fecha_registro AS DATE) AS 'fecha_registro', sf.relleno AS 'relleno'
	FROM secuencia_facturacion AS sf
	INNER JOIN empresa AS e
	ON sf.empresa_id = e.empresa_id
	INNER JOIN documento AS d
	ON sf.documento_id = d.documento_id
	".$where."
	ORDER BY sf.secuencia_facturacion_id ASC
	LIMIT $limit, $nroLotes";
$result = $mysqli->query($registro);


$tabla = $tabla.'<table class="table table-striped table-condensed table-hover">
			<tr>
			<th width="2.69">No.</th>
			<th width="14.69%">Empresa</th>	
			<th width="9.69%">Documento</th>				
			<th width="12.69%">CAI</th>
			<th width="7.69%">RTN</th>
			<th width="7.69%">Número Siguiente</th>
			<th width="7.69%">Rango Inicial</th>
			<th width="7.69%">Rango Final</th>
			<th width="7.69%">Fecha Activación</th>				
			<th width="7.69%">Fecha Limite</th>
			<th width="4.69%">Activo</th>
			<th width="4.69%">Editar</th>				
            <th width="4.69%">Eliminar</th>				
			</tr>';
$i = 1;				
while($registro2 = $result->fetch_assoc()){ 
    $relleno = $registro2['relleno']; 
	$prefijo = $registro2['prefijo'];
    $siguiente = $registro2['siguiente'];
    $numero = 	$registro2['prefijo'].''.str_pad($siguiente, $relleno, "0", STR_PAD_LEFT);
	
	$tabla = $tabla.'<tr>
			<td>'.$i.'</td> 
			<td>'.$registro2['empresa'].'</td>	
			<td>'.$registro2['documento'].'</td>
			<td>'.$registro2['cai'].'</td>	
			<td>'.$registro2['rtn'].'</td>	
			<td>'.$numero.'</td>	
			<td>'.$registro2['rango_inicial'].'</td>	
			<td>'.$registro2['rango_final'].'</td>				
			<td>'.$registro2['fecha_activacion'].'</td>			
			<td>'.$registro2['fecha_limite'].'</td>	
			<td>'.$registro2['activo'].'</td>				
			<td>
				<a class="btn btn btn-secondary ml-2" href="javascript:editarRegistro('.$registro2['secuencia_facturacion_id'].');void(0);"><div class="sb-nav-link-icon"></div><i class="fas fa-edit fa-lg"></i> Editar</a>
			</td>
			<td>
				<a class="btn btn btn-secondary ml-2" href="javascript:modal_eliminar('.$registro2['secuencia_facturacion_id'].');void(0);"><div class="sb-nav-link-icon"></div><i class="fas fa-trash fa-lg"></i> Eliminar</a>
			</td>			
			</tr>';	
			$i++;				
}

if($nroProductos == 0){
	$tabla = $tabla.'<tr>
	   <td colspan="13" style="color:#C7030D">No se encontraron resultados</td>
	</tr>';		
}else{
   $tabla = $tabla.'<tr>
	  <td colspan="13"><b><p ALIGN="center">Total de Registros Encontrados '.$nroProductos.'</p></b>
   </tr>';		
}        

$tabla = $tabla.'</table>';

$array = array(0 => $tabla,
			   1 => $lista);

echo json_encode($array);

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN