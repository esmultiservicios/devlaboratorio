<?php
session_start();
include "../funtions.php";
$mysqli = connect_mysqli();

$colaborador_id = $_SESSION['colaborador_id'];
$paginaActual   = $_POST['partida'];
$estado         = isset($_POST['estado']) ? trim($_POST['estado']) : '';
$cliente        = isset($_POST['cliente']) ? trim($_POST['cliente']) : '';
$tipo_muestra   = isset($_POST['tipo_muestra']) ? trim($_POST['tipo_muestra']) : '';
$fechai         = $_POST['fecha_i'];
$fechaf         = $_POST['fecha_f'];
$dato           = isset($_POST['dato']) ? trim($_POST['dato']) : '';

$consulta_cliente = ($cliente !== '') ? " AND m.pacientes_id = '$cliente' " : '';
$consulta_tipo_muestra = ($tipo_muestra !== '') ? " AND m.tipo_muestra_id = '$tipo_muestra' " : '';
$consulta_datos = '';

if ($dato != "") { // <-- FIX aquí
  $dato_esc = $mysqli->real_escape_string($dato);
  $consulta_datos = " AND (
      p.expediente LIKE '%$dato_esc%'
      OR CONCAT(p.nombre,' ',p.apellido) LIKE '%$dato_esc%'
      OR p.identidad LIKE '$dato_esc%'
      OR p.apellido LIKE '$dato_esc%'
      OR m.number LIKE '$dato_esc%'  /* LPBX2025-394 te entra aquí */
  )";
}

/* arma el WHERE sin forzar estado cuando viene vacío */
$where = " WHERE m.fecha BETWEEN '$fechai' AND '$fechaf' ";
if ($estado !== '' && $estado !== null) {
  $estado_esc = $mysqli->real_escape_string($estado);
  $where .= " AND m.estado = '$estado_esc' ";
}
$where .= $consulta_cliente . $consulta_tipo_muestra . $consulta_datos;

$query = "SELECT
    p.pacientes_id AS pacientes_id,
    CONCAT(p.nombre,' ',p.apellido) AS paciente,
    m.fecha AS fecha,
    m.diagnostico_clinico AS diagnostico_clinico,
    m.material_eviando AS material_eviando,
    m.datos_clinico AS datos_clinico,
    (CASE WHEN m.estado = '1' THEN 'Atendido' ELSE 'Pendiente' END) AS estatus,
    m.muestras_id AS muestras_id,
    m.mostrar_datos_clinicos AS mostrar_datos_clinicos,
    m.number AS numero
  FROM muestras AS m
  INNER JOIN pacientes AS p ON m.pacientes_id = p.pacientes_id
  $where
  ORDER BY m.fecha DESC";

$result = $mysqli->query($query) or die($mysqli->error);

/* paginación */
$nroLotes = 200;
$nroProductos = $result->num_rows;
$nroPaginas = ceil($nroProductos / $nroLotes);
$lista = '';
$tabla = '';

if ($paginaActual > 1)  $lista .= '<li class="page-item"><a class="page-link" href="javascript:paginationMuestras(1);void(0);">Inicio</a></li>';
if ($paginaActual > 1)  $lista .= '<li class="page-item"><a class="page-link" href="javascript:paginationMuestras('.($paginaActual-1).');void(0);">Anterior '.($paginaActual-1).'</a></li>';
if ($paginaActual < $nroPaginas) $lista .= '<li class="page-item"><a class="page-link" href="javascript:paginationMuestras('.($paginaActual+1).');void(0);">Siguiente '.($paginaActual+1).' de '.$nroPaginas.'</a></li>';
if ($paginaActual > 1)  $lista .= '<li class="page-item"><a class="page-link" href="javascript:paginationMuestras('.$nroPaginas.');void(0);">Ultima</a></li>';

$limit = ($paginaActual <= 1) ? 0 : $nroLotes * ($paginaActual - 1);

$registro = $query . " LIMIT $limit, $nroLotes";
$result = $mysqli->query($registro) or die($mysqli->error);

$tabla .= '<table class="table table-striped table-condensed table-hover">
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

  $query_paciente = "SELECT p.pacientes_id, CONCAT(p.nombre,' ',p.apellido) AS paciente
    FROM muestras_hospitales AS mh
    INNER JOIN pacientes AS p ON mh.pacientes_id = p.pacientes_id
    WHERE mh.muestras_id = '$muestras_id'";
  $result_paciente = $mysqli->query($query_paciente) or die($mysqli->error);

  $pacientes_id_cliente_codigo = "";
  $pacientes_id_cliente = "";
  if($result_paciente->num_rows > 0){
    $valores_paciente = $result_paciente->fetch_assoc();
    $pacientes_id_cliente_codigo = $valores_paciente['pacientes_id'];
    $pacientes_id_cliente = $valores_paciente['paciente'];
  }

  $empresa = ($pacientes_id_cliente == "")
    ? '<a style="text-decoration:none;" href="javascript:showModalhistoriaMuestrasEmpresas('.$registro2['pacientes_id'].');void(0);">'.$registro2['paciente'].'</a>'
    : '<a style="text-decoration:none;" href="javascript:showModalhistoriaMuestrasEmpresas('.$registro2['pacientes_id'].');void(0);">'.$registro2['paciente'].'</a><b> Paciente: </b><a style="text-decoration:none;" href="javascript:showModalhistoriaMuestrasEmpresas('.$pacientes_id_cliente_codigo.');void(0);">('.$pacientes_id_cliente.')</a>';

  $tabla .= '<tr>
      <td>'.$i.'</td>
      <td>'.$registro2['fecha'].'</td>
      <td>'.$registro2['numero'].'</td>
      <td>'.$empresa.'</td>
      <td>'.$registro2['diagnostico_clinico'].'</td>
      <td>'.$registro2['material_eviando'].'</td>
      <td>'.$registro2['datos_clinico'].'</td>
      <td><a class="btn btn btn-secondary ml-2" href="javascript:modalCreateBill('.$registro2['muestras_id'].');void(0);"><i class="fas fa-file-invoice fa-lg"></i> Facturar</a></td>
      <td><a class="btn btn btn-secondary ml-2" href="javascript:modalAnularMuestras('.$registro2['pacientes_id'].','.$registro2['muestras_id'].');void(0);"><i class="fas fa-ban fa-lg"></i> Anular</a></td>
    </tr>';
  $i++;
}

if ($nroProductos == 0){
  $tabla .= '<tr><td colspan="12" style="color:#C7030D">No se encontraron resultados</td></tr>';
}else{
  $tabla .= '<tr><td colspan="12"><b><p ALIGN="center">Total de Registros Encontrados: '.$nroProductos.'</p></b></td></tr>';
}

$tabla .= '</table>';

echo json_encode([$tabla, $lista]);

$result->free();
$mysqli->close();