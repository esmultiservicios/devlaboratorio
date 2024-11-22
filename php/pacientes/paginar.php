<?php
session_start();
include "../funtions.php";

header("Content-Type: text/html;charset=utf-8");

//CONEXION A DB
$mysqli = connect_mysqli();

$paginaActual = $_POST['partida'];
$estado = $_POST['estado'];
$paciente = $_POST['paciente'];
$tipo_paciente = $_POST['tipo_paciente'];
$dato = $_POST['dato'];

$query_row = "SELECT pacientes_id, CONCAT(nombre,' ',apellido) AS 'paciente', identidad, telefono1, telefono2, fecha_nacimiento, expediente AS 'expediente_', localidad,
(CASE WHEN estado = '1' THEN 'Activo' ELSE 'Inactivo' END) AS 'estado',
(CASE WHEN genero = 'H' THEN 'Hombre' ELSE 'Mujer' END) AS 'genero',
(CASE WHEN expediente = '0' THEN 'TEMP' ELSE expediente END) AS 'expediente', email
FROM pacientes
WHERE estado = '$estado' AND tipo_paciente_id = '$tipo_paciente' AND (expediente LIKE '$dato%' OR nombre LIKE '$dato%' OR apellido LIKE '$dato%' OR CONCAT(apellido,' ',nombre) LIKE '%$dato%' OR CONCAT(nombre,' ',apellido) LIKE '%$dato%' OR telefono1 LIKE '$dato%' OR identidad LIKE '$dato%')
ORDER BY expediente";

$result = $mysqli->query($query_row);

$nroProductos=$result->num_rows;
$nroLotes = 15;
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

$query = "SELECT pacientes_id, CONCAT(nombre,' ',apellido) AS 'paciente', identidad, telefono1, telefono2, fecha, expediente AS 'expediente_', localidad,
(CASE WHEN estado = '1' THEN 'Activo' ELSE 'Inactivo' END) AS 'estado',
(CASE WHEN genero = 'H' THEN 'Hombre' ELSE 'Mujer' END) AS 'genero',
(CASE WHEN expediente = '0' THEN 'TEMP' ELSE expediente END) AS 'expediente', email
FROM pacientes
WHERE estado = '$estado' AND tipo_paciente_id = '$tipo_paciente' AND (expediente LIKE '$dato%' OR nombre LIKE '$dato%' OR apellido LIKE '$dato%' OR CONCAT(apellido,' ',nombre) LIKE '%$dato%' OR CONCAT(nombre,' ',apellido) LIKE '%$dato%' OR telefono1 LIKE '$dato%' OR identidad LIKE '$dato%')
ORDER BY expediente LIMIT $limit, $nroLotes
";
$result = $mysqli->query($query);

$tabla = $tabla.'<table class="table table-striped table-condensed table-hover">
					<tr>
					   <th width="2.69%">N°</th>
					   <th width="6.69%">Identidad</th>
					   <th width="14.69%">Paciente</th>
					   <th width="2.69%">Genero</th>
					   <th width="6.69%">Telefono1</th>
					   <th width="5.69">Telefono2</th>
					   <th width="7.69%">Correo</th>
					   <th width="18.69%">Dirección</th>
					   <th width="2.69%">Estado</th>
					   <th width="7.69%">Ver Muestras</th>
					   <th width="7.69%">Editar RTN</th>
					   <th width="7.69%">Editar Cliente</th>
					   <th width="8.69%">Eliminar</th>
					</tr>';

$i=1;
while($registro2 = $result->fetch_assoc()){

	$tabla = $tabla.'<tr>
	   <td>'.$i.'</td>
	   <td><a style="text-decoration:none" title = "Información de Cliente" href="javascript:showExpediente('.$registro2['pacientes_id'].');">'.$registro2['identidad'].'</a></td>
	   <td>'.$registro2['paciente'].'
	   <td>'.$registro2['genero'].'</td>
	   <td>'.$registro2['telefono1'].'</td>
	   <td>'.$registro2['telefono2'].'</td>
	   <td>'.$registro2['email'].'</td>
	   <td>'.$registro2['localidad'].'</td>
	   <td>'.$registro2['estado'].'</td>
	   <td>
		   <a class="btn btn btn-secondary ml-2" href="javascript:modal_muestras('.$registro2['pacientes_id'].');void(0);"><div class="sb-nav-link-icon"></div><i class="far fa-eye fa-lg"></i> Ver</a>
	   </td>
	   <td>
		   <a class="btn btn btn-secondary ml-2" href="javascript:modal_agregar_expediente_manual('.$registro2['pacientes_id'].');void(0);"><div class="sb-nav-link-icon"></div><i class="fas fa-edit fa-lg"></i> Editar</a>
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
//<a style="text-decoration:none;" title="Asignar Expediente a Cliente" href="javascript:modal_agregar_expediente('.$registro2['pacientes_id'].','.$registro2['expediente_'].');void(0);" class="fas fas fa-plus fa-lg"></a>

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
?>
