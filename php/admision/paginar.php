<?php
session_start();
include "../funtions.php";

// Cabecera JSON para evitar que el navegador “adivine” otro tipo
header('Content-Type: application/json; charset=UTF-8');

// CONEXION A DB
$mysqli = connect_mysqli();

// Sanitizar/normalizar entradas
$paginaActual = isset($_POST['partida']) ? (int)$_POST['partida'] : 1;
$dato         = isset($_POST['dato']) ? trim($_POST['dato']) : '';

$tipo   = (isset($_POST['tipo'])   && $_POST['tipo']   !== '') ? $_POST['tipo']   : '1';
$estado = (isset($_POST['estado']) && $_POST['estado'] !== '') ? $_POST['estado'] : '1';

// Consulta base
$where = "
  p.estado = '$estado'
  AND p.tipo_paciente_id = '$tipo'
  AND (
      expediente LIKE '$dato%'
      OR nombre   LIKE '$dato%'
      OR apellido LIKE '$dato%'
      OR CONCAT(apellido,' ',nombre) LIKE '%$dato%'
      OR CONCAT(nombre,' ',apellido) LIKE '%$dato%'
      OR telefono1 LIKE '$dato%'
      OR identidad LIKE '$dato%'
  )
";

$query = "
  SELECT p.pacientes_id,
         CONCAT(p.nombre, ' ', p.apellido) AS nombre,
         p.edad,
         p.telefono1 AS telefono,
         p.telefono2 AS telefono1,
         p.email     AS correo,
         p.localidad AS direccion,
         p.identidad AS identidad
  FROM pacientes AS p
  WHERE $where
  ORDER BY p.pacientes_id
";

$result = $mysqli->query($query);
$nroLotes      = 15;
$nroProductos  = $result ? $result->num_rows : 0;
$nroPaginas    = $nroProductos > 0 ? (int)ceil($nroProductos / $nroLotes) : 1;
$lista         = '';
$tabla         = '';

if ($paginaActual > 1) {
  $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(1);void(0);">Inicio</a></li>';
  $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(' . ($paginaActual - 1) . ');void(0);">Anterior ' . ($paginaActual - 1) . '</a></li>';
}

if ($paginaActual < $nroPaginas) {
  $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(' . ($paginaActual + 1) . ');void(0);">Siguiente ' . ($paginaActual + 1) . ' de ' . $nroPaginas . '</a></li>';
  $lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(' . $nroPaginas . ');void(0);">Última</a></li>';
}

$limit = ($paginaActual <= 1) ? 0 : $nroLotes * ($paginaActual - 1);

$registro = "
  SELECT p.pacientes_id,
         CONCAT(p.nombre, ' ', p.apellido) AS nombre,
         p.edad,
         p.telefono1 AS telefono,
         p.telefono2 AS telefono1,
         p.email     AS correo,
         p.localidad AS direccion,
         p.identidad AS identidad
  FROM pacientes AS p
  WHERE $where
  ORDER BY p.pacientes_id
  LIMIT $limit, $nroLotes
";

$result = $mysqli->query($registro);

// Etiqueta del botón de estado
if ($estado === "1") {
  $estado_label = "Inhabilitar";
  $icon = "fa fa-ban";
} else {
  $estado_label = "Habilitar";
  $icon = "fa fa-check";
}

$tabla .= '<table class="table table-striped table-condensed table-hover">
  <tr>
    <th width="2%">N°</th>
    <th width="8%">RTN</th>
    <th width="18%">Cliente</th>
    <th width="4%">Edad</th>
    <th width="5%">Teléfono 1</th>
    <th width="5%">Teléfono 2</th>
    <th width="18%">Correo</th>
    <th width="45%">Dirección</th>
    <th width="9.69%">' . $estado_label . '</th>
    <th width="6%">Muestras</th>
    <th width="8%">Editar</th>
    <th width="9%">Eliminar</th>
  </tr>
';

$i = 1;
if ($result && $result->num_rows > 0) {
  while ($registro2 = $result->fetch_assoc()) {
    $tabla .= '<tr>
      <td>' . $i . '</td>
      <td>' . htmlspecialchars($registro2['identidad']) . '</td>
      <td>' . htmlspecialchars($registro2['nombre']) . '</td>
      <td>' . htmlspecialchars($registro2['edad']) . '</td>
      <td>' . htmlspecialchars($registro2['telefono']) . '</td>
      <td>' . htmlspecialchars($registro2['telefono1']) . '</td>
      <td>' . htmlspecialchars($registro2['correo']) . '</td>
      <td>' . htmlspecialchars($registro2['direccion']) . '</td>
      <td>
        <a class="btn btn btn-secondary ml-2" href="javascript:DisableRegister(' . (int)$registro2['pacientes_id'] . ');void(0);"><div class="sb-nav-link-icon"></div><i class="' . $icon . ' fa-lg"></i> ' . $estado_label . '</a>
      </td>
      <td>
        <a class="btn btn btn-secondary ml-2" href="javascript:showModalhistoriaMuestrasEmpresas(' . (int)$registro2['pacientes_id'] . ');void(0);"><div class="sb-nav-link-icon"></div><i class="fas fa-eye fa-lg"></i> Ver</a>
      </td>
      <td>
        <a class="btn btn btn-secondary ml-2" href="javascript:editarRegistro(' . (int)$registro2['pacientes_id'] . ');void(0);"><div class="sb-nav-link-icon"></div><i class="fas fa-user-edit fa-lg"></i> Editar</a>
      </td>
      <td>
        <a class="btn btn btn-secondary ml-2" href="javascript:modal_eliminar(' . (int)$registro2['pacientes_id'] . ');void(0);"><div class="sb-nav-link-icon"></div><i class="fas fa-trash fa-lg"></i> Eliminar</a>
      </td>
    </tr>';
    $i++;
  }

  $tabla .= '<tr>
    <td colspan="14"><b><p align="center">Total de Registros Encontrados ' . number_format($nroProductos) . '</p></b></td>
  </tr>';
} else {
  $tabla .= '<tr>
    <td colspan="14" style="color:#C7030D">No se encontraron resultados</td>
  </tr>';
}

$tabla .= '</table>';

echo json_encode([$tabla, $lista], JSON_UNESCAPED_UNICODE);

if ($result) $result->free();
$mysqli->close();