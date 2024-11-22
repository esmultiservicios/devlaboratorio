<?php
session_start();
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

$colaborador_id = $_SESSION['colaborador_id'];
$paginaActual = $_POST['partida'];
$estado = $_POST['estado'];
$cliente = $_POST['cliente'];
$tipo_muestra = $_POST['tipo_muestra'];
$fechai = $_POST['fecha_i'];
$fechaf = $_POST['fecha_f'];
$dato = $_POST['dato'];

$consulta_cliente = '';
$consulta_tipo_muestra = '';
$consulta_datos = "";

if($cliente != ''){
	$consulta_cliente = "AND m.pacientes_id = '$cliente'";
}

if($tipo_muestra != ''){
	$consulta_tipo_muestra = "AND m.tipo_muestra_id = '$tipo_muestra'";
}

if($dato == !""){
	$consulta_datos = "AND (p.expediente LIKE '%$dato%' OR CONCAT(p.nombre,' ',p.apellido) LIKE '%$dato%' OR p.identidad LIKE '$dato%' OR p.apellido LIKE '$dato%' OR m.number LIKE '$dato%')";
}

$query = "SELECT p.pacientes_id AS 'pacientes_id', CONCAT(p.nombre, ' ', p.apellido) AS paciente, m.fecha AS 'fecha', m.diagnostico_clinico AS 'diagnostico_clinico', m.material_eviando As 'material_eviando', m.datos_clinico As 'datos_clinico',
(CASE WHEN m.estado = '1' THEN 'Atendido' ELSE 'Pendiente' END) AS 'estatus', m.muestras_id  As 'muestras_id', m.mostrar_datos_clinicos As 'mostrar_datos_clinicos', m.number AS 'numero'
	FROM muestras AS m
	INNER JOIN pacientes AS p
	ON m.pacientes_id = p.pacientes_id
	WHERE m.estado = $estado AND m.fecha BETWEEN '$fechai' AND '$fechaf'
	$consulta_cliente
	$consulta_tipo_muestra
	$consulta_datos
	ORDER BY m.fecha DESC";

$result = $mysqli->query($query) or die($mysqli->error);

$nroLotes = 200;
$nroProductos = $result->num_rows;
$nroPaginas = ceil($nroProductos/$nroLotes);
$lista = '';
$tabla = '';

if($paginaActual > 1){
	$lista = $lista.'<li class="page-item"><a class="page-link" href="javascript:paginationMuestras('.(1).');void(0);">Inicio</a></li>';
}

if($paginaActual > 1){
	$lista = $lista.'<li class="page-item"><a class="page-link" href="javascript:paginationMuestras('.($paginaActual-1).');void(0);">Anterior '.($paginaActual-1).'</a></li>';
}

if($paginaActual < $nroPaginas){
	$lista = $lista.'<li class="page-item"><a class="page-link" href="javascript:paginationMuestras('.($paginaActual+1).');void(0);">Siguiente '.($paginaActual+1).' de '.$nroPaginas.'</a></li>';
}

if($paginaActual > 1){
	$lista = $lista.'<li class="page-item"><a class="page-link" href="javascript:paginationMuestras('.($nroPaginas).');void(0);">Ultima</a></li>';
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
	WHERE m.estado = $estado AND m.fecha BETWEEN '$fechai' AND '$fechaf'
	$consulta_cliente
	$consulta_tipo_muestra
	$consulta_datos
	ORDER BY m.fecha DESC
	LIMIT $limit, $nroLotes";
$result = $mysqli->query($registro) or die($mysqli->error);

$tabla = $tabla.'<table class="table table-striped table-condensed table-hover">
			<tr>
				<th width="1%">No.</th>
				<th width="7%">Fecha</th>
				<th width="9%">Número</th>
				<th width="25%">Paciente</th>
				<th width="13%">Diagnostico Clínico</th>
				<th width="13%">Material Enviado</th>
				<th width="10%">Datos Clínicos</th>
				<th width="7%">Generar</th>
				<th width="8%">Eliminar</th>
			</tr>';
$i = 1;
while($registro2 = $result->fetch_assoc()){
	$muestras_id = $registro2['muestras_id'];
	//CONSULTAR EL PACIENTE SI ES ENVIADO POR UNA EMPRESA O CLINIC
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
		$empresa = '<a style="text-decoration:none;" href="javascript:showModalhistoriaMuestrasEmpresas('.$registro2['pacientes_id'].');void(0);">'.$registro2['paciente'].'</a><b> Paciente: </b><a style="text-decoration:none;" href="javascript:showModalhistoriaMuestrasEmpresas('.$pacientes_id_cliente_codigo.');void(0);">('.$pacientes_id_cliente.')</a>';
	}

	$tabla = $tabla.'<tr>
				<td>'.$i.'</td>
				<td>'.$registro2['fecha'].'</td>
				<td>'.$registro2['numero'].'</td>
				<td>'.$empresa.'</td>
				<td>'.$registro2['diagnostico_clinico'].'</td>
				<td>'.$registro2['material_eviando'].'</td>
				<td>'.$registro2['datos_clinico'].'</td>
				<td>
						<a class="btn btn btn-secondary ml-2" href="javascript:modalCreateBill('.$registro2['muestras_id'].');void(0);"><div class="sb-nav-link-icon"></div><i class="fas fa-file-invoice fa-lg"></i> Facturar</a>
				</td>
				<td>
						<a class="btn btn btn-secondary ml-2" href="javascript:modalAnularMuestras('.$registro2['pacientes_id'].','.$registro2['muestras_id'].');void(0);"><div class="sb-nav-link-icon"></div><i class="fas fa-ban fa-lg"></i> Anular</a>
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
