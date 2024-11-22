<?php
session_start();   
include "../funtions.php";

header("Content-Type: text/html;charset=utf-8");

//CONEXION A DB
$mysqli = connect_mysqli();
	
$paginaActual = $_POST['partida'];
$pacientes_id = $_POST['pacientes_id'];
$dato = $_POST['dato'];

$query_row = "SELECT m.fecha AS 'fecha', CONCAT(p.nombre, ' ', p.apellido) AS 'paciente', m.number AS 'mustra', m.sitio_muestra AS 'sitio', m.diagnostico_clinico AS 'diagnostico', m.material_eviando AS 'material', m.datos_clinico AS 'datos'
FROM muestras AS m
INNER JOIN pacientes AS p
ON m.pacientes_id = p.pacientes_id
WHERE m.pacientes_id = '$pacientes_id'
UNION
SELECT m.fecha AS 'fecha', CONCAT(p.nombre, ' ', p.apellido) AS 'paciente', m.number AS 'mustra', m.sitio_muestra AS 'sitio', m.diagnostico_clinico AS 'diagnostico', m.material_eviando AS 'material', m.datos_clinico AS 'datos'
FROM muestras AS m
INNER JOIN muestras_hospitales AS mh
ON mh.muestras_id = m.muestras_id
INNER JOIN pacientes AS p
ON mh.pacientes_id = p.pacientes_id
WHERE mh.pacientes_id = '$pacientes_id'";	

$result = $mysqli->query($query_row);     

$nroProductos=$result->num_rows; 
$nroLotes = 15;
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

$query = "SELECT m.fecha AS 'fecha', CONCAT(p.nombre, ' ', p.apellido) AS 'paciente', m.number AS 'mustra', m.sitio_muestra AS 'sitio', m.diagnostico_clinico AS 'diagnostico', m.material_eviando AS 'material', m.datos_clinico AS 'datos', CONCAT(' ') AS 'empresa'
FROM muestras AS m
INNER JOIN pacientes AS p
ON m.pacientes_id = p.pacientes_id
WHERE m.pacientes_id = '$pacientes_id'
UNION
SELECT m.fecha AS 'fecha', CONCAT(p.nombre, ' ', p.apellido) AS 'paciente', m.number AS 'mustra', m.sitio_muestra AS 'sitio', m.diagnostico_clinico AS 'diagnostico', m.material_eviando AS 'material', m.datos_clinico AS 'datos', CONCAT(p1.nombre, ' ', p1.apellido) AS 'empresa'
FROM muestras AS m
INNER JOIN muestras_hospitales AS mh
ON mh.muestras_id = m.muestras_id
INNER JOIN pacientes AS p
ON mh.pacientes_id = p.pacientes_id
INNER JOIN pacientes AS p1
ON mh.pacientes_empresa_id = p1.pacientes_id
WHERE mh.pacientes_id = '$pacientes_id' LIMIT $limit, $nroLotes
";
$result = $mysqli->query($query);    
  
$tabla = $tabla.'<table class="table table-striped table-condensed table-hover">
					<tr>
					   <th width="1.11%">N°</th>
					   <th width="11.11%">Fecha</th>
					   <th width="11.11%">Empresa</th>
					   <th width="16.11%">Paciente</th>
					   <th width="16.11%">Muestra</th>					   
					   <th width="11.11%">Sitio</th>
					   <th width="11.11">Diagnostico</th>	
					   <th width="11.11%">Material</th>
					   <th width="11.11%">Datos</th>
					</tr>';

$i=1;						
while($registro2 = $result->fetch_assoc()){
 
	$tabla = $tabla.'<tr>
	   <td>'.$i.'</td>
	   <td>'.$registro2['fecha'].'
	   <td>'.$registro2['empresa'].'
	   <td>'.$registro2['paciente'].'
	   <td>'.$registro2['mustra'].'</td>
	   <td>'.$registro2['sitio'].'</td>
	   <td>'.$registro2['diagnostico'].'</td>
	   <td>'.$registro2['material'].'</td>	   
	   <td>'.$registro2['datos'].'</td>		              		  
	</tr>';
	$i++;
}

if($nroProductos == 0){
	$tabla = $tabla.'<tr>
	   <td colspan="11" style="color:#C7030D">No se encontraron resultados</td>
	</tr>';		
}else{
   $tabla = $tabla.'<tr>
	  <td colspan="11"><b><p ALIGN="center">Total de Registros Encontrados '.number_format($nroProductos).'</p></b>
   </tr>';		
}   

$tabla = $tabla.'</table>';

$array = array(0 => $tabla,
			   1 => $lista);

echo json_encode($array);
?>