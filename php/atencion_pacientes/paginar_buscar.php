<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$colaborador_id = $_SESSION['colaborador_id'];
$paginaActual = $_POST['partida'];
$dato = $_POST['dato'];
	
$where = "WHERE am.colaborador_id = '$colaborador_id' AND (p.expediente LIKE '%$dato%' OR CONCAT(p.nombre,' ',p.apellido) LIKE '%$dato%' OR p.apellido LIKE '$dato%' OR p.identidad LIKE '$dato%')";

$query = "SELECT p.pacientes_id AS 'pacientes_id', am.atencion_id AS 'atencion_id', DATE_FORMAT(am.fecha, '%d/%m/%Y') AS 'fecha', p.identidad AS 'identidad', CONCAT(p.nombre,' ',p.apellido) AS 'paciente', s.nombre AS 'servicio', am.antecedentes AS 'antecedentes', am.historia_clinica AS 'historia_clinica', am.examen_fisico AS 'examen_fisico', am.diagnostico AS 'diagnostico'
	FROM atenciones_medicas AS am
	INNER JOIN pacientes AS p
	ON am.pacientes_id = p.pacientes_id
	INNER JOIN servicios AS s
	ON am.servicio_id = s.servicio_id
	".$where."
	ORDER BY am.fecha DESC";	

$result = $mysqli->query($query) or die($mysqli->error);

$nroLotes = 5;
$nroProductos = $result->num_rows;
$nroPaginas = ceil($nroProductos/$nroLotes);
$lista = '';
$tabla = '';

if($paginaActual > 1){
	$lista = $lista.'<li class="page-item"><a class="page-link" href="javascript:paginationBusqueda('.(1).');void(0);">Inicio</a></li>';
}

if($paginaActual > 1){
	$lista = $lista.'<li class="page-item"><a class="page-link" href="javascript:paginationBusqueda('.($paginaActual-1).');void(0);">Anterior '.($paginaActual-1).'</a></li>';
}

if($paginaActual < $nroPaginas){
	$lista = $lista.'<li class="page-item"><a class="page-link" href="javascript:paginationBusqueda('.($paginaActual+1).');void(0);">Siguiente '.($paginaActual+1).' de '.$nroPaginas.'</a></li>';
}

if($paginaActual > 1){
	$lista = $lista.'<li class="page-item"><a class="page-link" href="javascript:paginationBusqueda('.($nroPaginas).');void(0);">Ultima</a></li>';
}

if($paginaActual <= 1){
	$limit = 0;
}else{
	$limit = $nroLotes*($paginaActual-1);
}

$registro = "SELECT p.pacientes_id AS 'pacientes_id', am.atencion_id AS 'atencion_id', DATE_FORMAT(am.fecha, '%d/%m/%Y') AS 'fecha', p.identidad AS 'identidad', CONCAT(p.nombre,' ',p.apellido) AS 'paciente', s.nombre AS 'servicio', am.antecedentes AS 'antecedentes', am.historia_clinica AS 'historia_clinica', am.examen_fisico AS 'examen_fisico', am.diagnostico AS 'diagnostico'
	FROM atenciones_medicas AS am
	INNER JOIN pacientes AS p
	ON am.pacientes_id = p.pacientes_id
	INNER JOIN servicios AS s
	ON am.servicio_id = s.servicio_id
	".$where."
	ORDER BY am.fecha DESC
	LIMIT $limit, $nroLotes";
$result = $mysqli->query($registro) or die($mysqli->error);


$tabla = $tabla.'<table class="table table-striped table-condensed table-hover">
			<tr>
			<th width="1.5%">No.</th>
			<th width="9.5%">Fecha</th>				
			<th width="16.5%">Paciente</th>
			<th width="15.5%">Diagnostico</th>
			<th width="13.5%">Factores Pronóstico</th>
			<th width="13.5%">Descripción Macroscópica</th>
			<th width="13.5%">Descripción Microscopica</th>	
			<th width="10.5%">Servicio</th>
			</tr>';
$i = 1;				
while($registro2 = $result->fetch_assoc()){  
	$tabla = $tabla.'<tr>
			<td>'.$i.'</td> 
			<td>'.$registro2['fecha'].'</td>            
            <td><a style="text-decoration:none" title = "Obtener la Historia Clínica de Este Paciente" href="javascript:detallesAtencion('.$registro2['pacientes_id'].');">'.$registro2['paciente'].'</a></td>			
			<td>'.$registro2['antecedentes'].'</td>
			<td>'.$registro2['historia_clinica'].'</td>
			<td>'.$registro2['examen_fisico'].'</td>
			<td>'.$registro2['diagnostico'].'</td>			
			<td>'.$registro2['servicio'].'</td>
			</tr>';	
			$i++;				
}

if($nroProductos == 0){
	$tabla = $tabla.'<tr>
	   <td colspan="8" style="color:#C7030D">No se encontraron resultados</td>
	</tr>';		
}else{
   $tabla = $tabla.'<tr>
	  <td colspan="8"><b><p ALIGN="center">Total de Registros Encontrados: '.$nroProductos.'</p></b>
   </tr>';		
}        

$tabla = $tabla.'</table>';

$array = array(0 => $tabla,
			   1 => $lista);

echo json_encode($array);

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN	
?>