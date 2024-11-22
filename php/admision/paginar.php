<?php
session_start();   
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

$paginaActual = $_POST['partida'];
$dato = $_POST['dato'];

if($_POST['tipo'] == ""){
	$tipo = "1";
}else{
	$tipo = $_POST['tipo'];
}

if($_POST['estado'] == ""){
	$estado = "1";
}else{
	$estado = $_POST['estado'];
}

$query = "SELECT p.pacientes_id, CONCAT(p.nombre, ' ', p.apellido) AS 'nombre', p.edad, p.telefono1 AS 'telefono', p.telefono2 AS 'telefono1', p.email AS 'correo', p.localidad AS 'direccion', p.identidad AS 'identidad'
	FROM pacientes AS p
	WHERE p.estado = '$estado' AND p.tipo_paciente_id = '$tipo' AND (expediente LIKE '$dato%' OR nombre LIKE '$dato%' OR apellido LIKE '$dato%' OR CONCAT(apellido,' ',nombre) LIKE '%$dato%' OR CONCAT(nombre,' ',apellido) LIKE '%$dato%' OR telefono1 LIKE '$dato%' OR identidad LIKE '$dato%')
	ORDER BY p.pacientes_id";  

$result = $mysqli->query($query);
   
$nroLotes = 15;
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

$registro = "SELECT p.pacientes_id, CONCAT(p.nombre, ' ', p.apellido) AS 'nombre', p.edad, p.telefono1 AS 'telefono', p.telefono2 AS 'telefono1', p.email AS 'correo', p.localidad AS 'direccion', p.identidad AS 'identidad'
	FROM pacientes AS p
	WHERE p.estado = '$estado' AND  p.tipo_paciente_id = '$tipo' AND (expediente LIKE '$dato%' OR nombre LIKE '$dato%' OR apellido LIKE '$dato%' OR CONCAT(apellido,' ',nombre) LIKE '%$dato%' OR CONCAT(nombre,' ',apellido) LIKE '%$dato%' OR telefono1 LIKE '$dato%' OR identidad LIKE '$dato%')
    ORDER BY p.pacientes_id LIMIT $limit, $nroLotes";
  
$result = $mysqli->query($registro);

$tabla = $tabla.'<table class="table table-striped table-condensed table-hover">
		  <tr>
			<th width="2.09%">N°</th>
			<th width="9.09%">RTN</th>
			<th width="17.09%">Cliente</th>
			<th width="3.09%">Edad</th>
			<th width="6.09%">Teléfono 1</th>
			<th width="6.09%">Teléfono 2</th>
			<th width="17.09%">Correo</th>	
			<th width="15.09%">Dirección</th>						
			<th width="8.09%">Ver Mas</th>
			<th width="7.09%">Editar</th>
			<th width="8.09%">Eliminar</th>
		   </tr>';
			
$i=1;			
while($registro2 = $result->fetch_assoc()){
	$tabla = $tabla.'<tr>
	   <td>'.$i.'</td>		
	   <td>'.$registro2['identidad'].'</td>
	   <td>'.$registro2['nombre'].'</td>   
	   <td>'.$registro2['edad'].'</td>  
	   <td>'.$registro2['telefono'].'</td>   
	   <td>'.$registro2['telefono1'].'</td>
	   <td>'.$registro2['correo'].'</td>  
	   <td>'.$registro2['direccion'].'</td>     
	   <td>
	   		<a class="btn btn btn-secondary ml-2" href="javascript:showModalhistoriaMuestrasEmpresas('.$registro2['pacientes_id'].');void(0);"><div class="sb-nav-link-icon"></div><i class="fas fa-eye fa-lg"></i> Ver Más</a>
	   </td>
	   <td>
	   		<a class="btn btn btn-secondary ml-2" href="javascript:editarRegistro('.$registro2['pacientes_id'].');void(0);"><div class="sb-nav-link-icon"></div><i class="fas fa-user-edit fa-lg"></i> Editar</a>
	   </td>
	   <td>
	   		<a class="btn btn btn-secondary ml-2" href="javascript:modal_eliminar('.$registro2['pacientes_id'].');void(0);"><div class="sb-nav-link-icon"></div><i class="fas fa-trash fa-lg"></i> Eliminar</a>
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
	  <td colspan="13"><b><p ALIGN="center">Total de Registros Encontrados '.number_format($nroProductos).'</p></b>
   </tr>';		
}

$tabla = $tabla.'</table>';	

$array = array(0 => $tabla,
			   1 => $lista);

echo json_encode($array);

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN	
?>