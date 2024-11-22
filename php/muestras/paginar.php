<?php 
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$colaborador_id = $_SESSION['colaborador_id'];
$paginaActual = $_POST['partida'];
$fechai = $_POST['fechai'];
$fechaf = $_POST['fechaf'];
$dato = $_POST['dato'];
$tipo_paciente_grupo = $_POST['tipo_paciente_grupo'];
$pacientesIDGrupo = $_POST['pacientesIDGrupo'];
$tipo_muestra = $_POST['tipo_muestra'];
$estado = 0;

if($pacientesIDGrupo == "" && $tipo_muestra == ""){
	$where = "WHERE m.fecha BETWEEN '$fechai' AND '$fechaf' AND m.estado = '$estado' AND (p.expediente LIKE '%$dato%' OR CONCAT(p.nombre,' ',p.apellido) LIKE '%$dato%' OR p.identidad LIKE '$dato%' OR p.apellido LIKE '$dato%' OR m.number LIKE '$dato%')";	
}else if($pacientesIDGrupo != "" && $tipo_muestra == ""){
	$where = "WHERE m.pacientes_id = '$pacientesIDGrupo' AND m.estado = '$estado' AND (p.expediente LIKE '%$dato%' OR CONCAT(p.nombre,' ',p.apellido) LIKE '%$dato%' OR p.identidad LIKE '$dato%' OR p.apellido LIKE '$dato%' OR m.number LIKE '$dato%')";
}else if($pacientesIDGrupo != "" && $tipo_muestra != ""){
	$where = "WHERE m.pacientes_id = '$pacientesIDGrupo' AND m.fecha BETWEEN '$fechai' AND '$fechaf' AND m.estado = '$estado' AND m.tipo_muestra_id = '$tipo_muestra' AND (p.expediente LIKE '%$dato%' OR CONCAT(p.nombre,' ',p.apellido) LIKE '%$dato%' OR p.identidad LIKE '$dato%' OR p.apellido LIKE '$dato%' OR m.number LIKE '$dato%')";	
}

$query = "SELECT p.pacientes_id AS 'pacientes_id', CONCAT(p.nombre, ' ', p.apellido) AS paciente, m.fecha AS 'fecha', m.diagnostico_clinico AS 'diagnostico_clinico', m.material_eviando As 'material_eviando', m.datos_clinico As 'datos_clinico',
(CASE WHEN m.estado = '1' THEN 'Atendido' ELSE 'Pendiente' END) AS 'estatus', m.muestras_id  As 'muestras_id', m.mostrar_datos_clinicos As 'mostrar_datos_clinicos', m.number AS 'numero'
	FROM muestras AS m
	INNER JOIN pacientes AS p
	ON m.pacientes_id = p.pacientes_id
	".$where."
	ORDER BY m.fecha DESC";	
$result = $mysqli->query($query) or die($mysqli->error);

$nroLotes = 200;
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

$registro = "SELECT p.pacientes_id AS 'pacientes_id', CONCAT(p.nombre, ' ', p.apellido) As paciente, m.fecha AS 'fecha', m.diagnostico_clinico AS 'diagnostico_clinico', m.material_eviando As 'material_eviando', m.datos_clinico As 'datos_clinico',
(CASE WHEN m.estado = '1' THEN 'Atendido' ELSE 'Pendiente' END) AS 'estatus', m.muestras_id  As 'muestras_id', m.mostrar_datos_clinicos As 'mostrar_datos_clinicos', m.number AS 'numero'
	FROM muestras AS m
	INNER JOIN pacientes AS p
	ON m.pacientes_id = p.pacientes_id
	".$where."
	ORDER BY m.fecha DESC
	LIMIT $limit, $nroLotes";
$result = $mysqli->query($registro) or die($mysqli->error);


$tabla = $tabla.'<table class="table table-striped table-condensed table-hover">
			<tr>
			<th width="1.11%">No.</th>
			<th width="8.11%">Fecha</th>
			<th width="10.11%">Número</th>			
			<th width="23.11%">Paciente</th>
			<th width="16.11%">Diagnostico Clínico</th>
			<th width="16.11%">Material Enviado</th>
			<th width="16.11%">Datos Clínicos</th>
			<th width="5.11%">Factura</th>
			<th width="5.11%">Opciones</th>
			</tr>';
$i = 1;				
while($registro2 = $result->fetch_assoc()){ 
	$muestras_id = $registro2['muestras_id'];
	//CONSULTAR EL PACIENTE SI ES ENVIADO POR UNA EMPRESA O CLINICA
	$query_paciente = "SELECT p.pacientes_id, CONCAT(p.nombre, ' ', p.apellido) As 'paciente'
		FROM muestras_hospitales AS mh
		INNER JOIN pacientes AS p
		ON mh.pacientes_id = p.pacientes_id
		WHERE mh.muestras_id = '$muestras_id'";
	$result_paciente = $mysqli->query($query_paciente) or die($mysqli->error);

	$pacientes_id_cliente_codigo = "";
	$pacientes_id_cliente = "";

	if($result_paciente->num_rows>0){	
		$valores_paciente = $result_paciente->fetch_assoc();
		$pacientes_id_cliente_codigo = $valores_paciente['pacientes_id'];
		$pacientes_id_cliente = $valores_paciente['paciente'];	
	}
	
	$empresa = "";
	
	if($pacientes_id_cliente == ""){
		$empresa = '<a style="text-decoration:none;" href="javascript:showModalhistoriaMuestrasEmpresas('.$registro2['pacientes_id'].');void(0);">'.$registro2['paciente'].'</a>';
	}else{
	$empresa = '<a style="text-decoration:none;" href="javascript:showModalhistoriaMuestrasEmpresas('.$registro2['pacientes_id'].');void(0);">'.$registro2['paciente'].'</a><b> Paciente: </b><a style="text-decoration:none;" href="javascript:showModalhistoriaMuestrasPacientes('.$pacientes_id_cliente_codigo.');void(0);">('.$pacientes_id_cliente.')</a>';
	}

	//CONSULTAMOS SI LA MUESTRA ESTA EN LA Factura
	$consulta_muestra_fact = "SELECT muestras_id
		FROM facturas
		WHERE muestras_id = '$muestras_id' AND estado NOT IN(2,3)";
	$result_muestra_fact = $mysqli->query($consulta_muestra_fact) or die($mysqli->error);

	$factura_muestra = "";
	$title_factura = "";

	$factura = '<a style="text-decoration:none;" data-toggle="tooltip" data-placement="right" title = "Crear Factura" href="javascript:createBill('.$registro2['muestras_id'].');void(0);" class="fas fa-file-invoice fa-lg"></a>';	


	if($result_muestra_fact->num_rows>0){
		$factura_muestra = "Generada";
		$title_factura = "Esta factura ya ha sido generada, verifique en el módulo de facturación para emitir el pago";
		$factura = "";
	}	
	
	$tabla = $tabla.'<tr>
			<td>'.$i.'</td> 
			<td>'.$registro2['fecha'].'</td>	
			<td>'.$registro2['numero'].'</td>	
			<td>
			  '.$empresa.'
			</td>
			<td>'.$registro2['diagnostico_clinico'].'</td>
			<td>'.$registro2['material_eviando'].'</td>
            <td>'.$registro2['datos_clinico'].'</td>
			<td title="'.$title_factura.'">'.$factura_muestra.'</td>
			<td>
			  '.$factura.'
			  <a style="text-decoration:none;" data-toggle="tooltip" data-placement="right" title = "Editar Muestra" href="javascript:editarRegistro('.$registro2['pacientes_id'].','.$registro2['muestras_id'].');void(0);" class="fas fa-edit fa-lg"></a>			  			  
			  <a style="text-decoration:none;" data-toggle="tooltip" data-placement="right" title = "Eliminar Muestra" href="javascript:eliminarRegistro('.$registro2['pacientes_id'].','.$registro2['muestras_id'].');void(0);" class="fas fa-trash fa-lg"></a> 
			</td>			
			</tr>';	
			$i++;				
}

if($nroProductos == 0){
	$tabla = $tabla.'<tr>
	   <td colspan="12" style="color:#C7030D">No se encontraron resultados</td>
	</tr>';		
}else{
   $tabla = $tabla.'<tr>
	  <td colspan="12"><b><p ALIGN="center">Total de Registros Encontrados: '.$nroProductos.'</p></b>
   </tr>';		
}        

$tabla = $tabla.'</table>';

$array = array(0 => $tabla,
			   1 => $lista);

echo json_encode($array);

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN	
?>