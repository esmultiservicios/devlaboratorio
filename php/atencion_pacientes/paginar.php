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
$estado = $_POST['estado'];

$fecha = date("Y-m-d");

if($fechai == $fecha){
	$where = "WHERE m.estado = '$estado' AND (p.expediente LIKE '%$dato%' OR CONCAT(p.nombre,' ',p.apellido) LIKE '%$dato%' OR p.identidad LIKE '$dato%' OR p.apellido LIKE '$dato%')";
}else{
	$where = "WHERE m.fecha BETWEEN '$fechai' AND '$fechaf' AND m.estado = '$estado' AND (p.expediente LIKE '%$dato%' OR CONCAT(p.nombre,' ',p.apellido) LIKE '%$dato%' OR p.identidad LIKE '$dato%' OR p.apellido LIKE '$dato%')";
}

$query = "SELECT p.pacientes_id AS 'pacientes_id', CONCAT(p.nombre, ' ', p.apellido) AS 'empresa', m.fecha AS 'fecha', m.diagnostico_clinico AS 'diagnostico_clinico', m.material_eviando As 'material_eviando', m.datos_clinico As 'datos_clinico',
(CASE WHEN m.estado = '1' THEN 'Atendido' ELSE 'Pendiente' END) AS 'estatus', m.muestras_id  As 'muestras_id', m.mostrar_datos_clinicos As 'mostrar_datos_clinicos', m.number AS 'numero', CONCAT(p1.nombre, ' ', p1.apellido) As 'paciente'
	FROM muestras AS m
	INNER JOIN pacientes AS p
	ON m.pacientes_id = p.pacientes_id
	LEFT JOIN muestras_hospitales mh
	ON m.muestras_id = mh.muestras_id
	LEFT JOIN pacientes AS p1
	ON mh.pacientes_id = p1.pacientes_id
	".$where."
	ORDER BY CONCAT(p.nombre, ' ', p.apellido) ASC";
$result = $mysqli->query($query) or die($mysqli->error);

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

$registro = "SELECT p.pacientes_id AS 'pacientes_id', CONCAT(p.nombre, ' ', p.apellido) AS 'empresa', m.fecha AS 'fecha', m.diagnostico_clinico AS 'diagnostico_clinico', m.material_eviando As 'material_eviando', m.datos_clinico As 'datos_clinico',
(CASE WHEN m.estado = '1' THEN 'Atendido' ELSE 'Pendiente' END) AS 'estatus', m.muestras_id  As 'muestras_id', m.mostrar_datos_clinicos As 'mostrar_datos_clinicos', m.number AS 'numero', CONCAT(p1.nombre, ' ', p1.apellido) As 'paciente'
	FROM muestras AS m
	INNER JOIN pacientes AS p
	ON m.pacientes_id = p.pacientes_id
	LEFT JOIN muestras_hospitales mh
	ON m.muestras_id = mh.muestras_id
	LEFT JOIN pacientes AS p1
	ON mh.pacientes_id = p1.pacientes_id
	".$where."
	ORDER BY CONCAT(p.nombre, ' ', p.apellido) ASC
	LIMIT $limit, $nroLotes";
$result = $mysqli->query($registro) or die($mysqli->error);


$tabla = $tabla.'<table class="table table-striped table-condensed table-hover">
			<tr>
			<th width="2.11">No.</th>
			<th width="8.11%">Fecha</th>
			<th width="11.11%">Número</th>
			<th width="20.11%">Paciente</th>
			<th width="14.11%">Diagnostico Clínico</th>
			<th width="12.11%">Material Enviado</th>
			<th width="12.11%">Datos Clínicos</th>
			<th width="10.11%">Atención</th>
			<th width="10.11%">Ausencia</th>
			</tr>';
$i = 1;
while($registro2 = $result->fetch_assoc()){
	$paciente = $registro2['paciente'];
	$empresa = "";
	if($paciente != ""){
		$empresa = $registro2['empresa']." (<b>Paciente</b>: ".$paciente.")";
	}else{
		$empresa = $registro2['empresa'];
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
 		   <a class="btn btn btn-secondary ml-2" href="javascript:editarRegistro('.$registro2['pacientes_id'].','.$registro2['muestras_id'].');void(0);"><div class="sb-nav-link-icon"></div><i class="fas fa-book-medical fa-lg"></i> Atencion</a>
 	    </td>
 	    <td>
 		   <a class="btn btn btn-secondary ml-2" href="javascript:nosePresentoRegistro('.$registro2['pacientes_id'].','.$registro2['muestras_id'].');void(0);"><div class="sb-nav-link-icon"></div><i class="fas fa-times-circle fa-lg"></i> Ausencia</a>
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
