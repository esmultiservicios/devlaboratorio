<?php
session_start();   
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli(); 

$colaborador_id = $_SESSION['colaborador_id'];
$paginaActual = $_POST['partida'];

$paginaActual = $_POST['partida'];
$desde = $_POST['desde'];
$hasta = $_POST['hasta'];
$dato = $_POST['dato'];	
$servicio = $_POST['servicio'];	
$colaborador = $_POST['colaborador'];
$colaborador_id = $_SESSION['colaborador_id'];

if ($_SESSION['type']==6){
	if($dato != ""){
		$where = "WHERE am.colaborador_id = '$colaborador_id' AND (CONCAT(p.nombre,' ',p.apellido) LIKE '%$dato%' OR p.apellido LIKE '$dato%' OR p.identidad LIKE '$dato%')";
	}else{
		$where = "WHERE am.colaborador_id = '$colaborador_id' AND am.fecha BETWEEN '$desde' AND '$hasta'";
	}		
}else{
	if($colaborador != ""){
		$where = "WHERE am.fecha BETWEEN '$desde' AND '$hasta' AND am.colaborador_id = '$colaborador'";
	}else if($dato != ""){
		$where = "WHERE CONCAT(p.nombre,' ',p.apellido) LIKE '%$dato%' OR p.apellido LIKE '$dato%' OR p.identidad LIKE '$dato%'";
	}else{
		$where = "WHERE am.fecha BETWEEN '$desde' AND '$hasta'";
	}	
}

$query = "SELECT am.atencion_id AS 'atencion_id',  DATE_FORMAT(am.fecha, '%d/%m/%Y') AS 'fecha', CONCAT(p.nombre,' ',p.apellido) AS 'paciente', p.identidad AS 'identidad', am.historia_clinica AS 'historia_clinica',CONCAT(c.nombre,' ',c.apellido) AS 'colaborador', s.nombre AS 'servicio', (CASE WHEN p.genero = 'H' THEN 'Hombre' ELSE 'Mujer' END) AS 'sexo',
(CASE WHEN am.paciente = 'N' THEN 'N' ELSE 'S' END) AS 'paciente_tipo', m.number AS 'numero', am.muestras_id AS 'muestras_id'
	FROM muestras AS m
    INNER JOIN atenciones_medicas AS am
    ON m.muestras_id = am.muestras_id
	INNER JOIN pacientes AS p
	ON am.pacientes_id = p.pacientes_id
	INNER JOIN colaboradores AS c
	ON am.colaborador_id = c.colaborador_id
	INNER JOIN servicios AS s
	ON am.servicio_id = s.servicio_id
	".$where."
    ORDER BY am.fecha ASC";
$result = $mysqli->query($query);
$nroProductos = $result->num_rows;
  
$nroLotes = 15;
$nroPaginas = ceil($nroProductos/$nroLotes);
$lista = '';
$tabla = '';

if($paginaActual > 1){
	$lista = $lista.'<li class="page-item"><a class="page-link" href="javascript:pagination_transito('.(1).');">Inicio</a></li>';
}

if($paginaActual > 1){
	$lista = $lista.'<li class="page-item"><a class="page-link" href="javascript:pagination_transito('.($paginaActual-1).');">Anterior '.($paginaActual-1).'</a></li>';
}

if($paginaActual < $nroPaginas){
	$lista = $lista.'<li class="page-item"><a class="page-link" href="javascript:pagination_transito('.($paginaActual+1).');">Siguiente '.($paginaActual+1).' de '.$nroPaginas.'</a></li>';
}

if($paginaActual > 1){
	$lista = $lista.'<li class="page-item"><a class="page-link" href="javascript:pagination_transito('.($nroPaginas).');">Ultima</a></li>';
}	

if($paginaActual <= 1){
	$limit = 0;
}else{
	$limit = $nroLotes*($paginaActual-1);
}

$registro = "SELECT am.atencion_id AS 'atencion_id',  DATE_FORMAT(am.fecha, '%d/%m/%Y') AS 'fecha', CONCAT(p.nombre,' ',p.apellido) AS 'paciente', p.identidad AS 'identidad', am.historia_clinica AS 'historia_clinica',CONCAT(c.nombre,' ',c.apellido) AS 'colaborador', s.nombre AS 'servicio', (CASE WHEN p.genero = 'H' THEN 'Hombre' ELSE 'Mujer' END) AS 'sexo',
(CASE WHEN am.paciente = 'N' THEN 'N' ELSE 'S' END) AS 'paciente_tipo', m.number AS 'numero', am.muestras_id AS 'muestras_id'
	FROM muestras AS m
    INNER JOIN atenciones_medicas AS am
    ON m.muestras_id = am.muestras_id
	INNER JOIN pacientes AS p
	ON am.pacientes_id = p.pacientes_id
	INNER JOIN colaboradores AS c
	ON am.colaborador_id = c.colaborador_id
	INNER JOIN servicios AS s
	ON am.servicio_id = s.servicio_id	
	".$where."
    ORDER BY am.fecha ASC
	LIMIT $limit, $nroLotes";
$result = $mysqli->query($registro);

$tabla = $tabla.'<table class="table table-striped table-condensed table-hover">
			<tr>
			<th width="7%">Fecha</th>
			<th width="13%">Número</th>			
			<th width="34%">Cliente</th>
			<th width="12%">Identidad</th>
			<th width="8%">Sexo</th>
			<th width="5%">Paciente</th>
			<th width="10%">Colaborador</th>
			<th width="13%">Servicio</th>		
			<th width="5%">Opciones</th>					
			</tr>';			
			
while($registro2 = $result->fetch_assoc()){	
	$tabla = $tabla.'<tr>
	   <td>'.$registro2['fecha'].'</td>
	   <td>'.$registro2['numero'].'</td>	   
	   <td>'.$registro2['paciente'].'</td>		   
	   <td>'.$registro2['identidad'].'</td>	
       <td>'.$registro2['sexo'].'</td>	
       <td>'.$registro2['paciente_tipo'].'</td>		   
	   <td>'.$registro2['colaborador'].'</td>		   
	   <td>'.$registro2['servicio'].'</td>
	   <td>
		  <a style="text-decoration:none;" data-toggle="tooltip" data-placement="right" title = "Imprimir Reporte" href="javascript:printReport('.$registro2['muestras_id'].');void(0);" class="fas fa-print fa-lg"></a>			  			  
	   </td>	   
	</tr>';	        
}

if($nroProductos == 0){
	$tabla = $tabla.'<tr>
	   <td colspan="17" style="color:#C7030D">No se encontraron resultados</td>
	</tr>';		
}else{
   $tabla = $tabla.'<tr>
	  <td colspan="17"><b><p ALIGN="center">Total de Registros Encontrados '.$nroProductos.'</p></b>
   </tr>';		
}        

$tabla = $tabla.'</table>';

$array = array(0 => $tabla,
			   1 => $lista);

echo json_encode($array);

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN	
?>