<?php
session_start();
include "../funtions.php";

header('Content-Type: application/json; charset=UTF-8');

// CONEXION A DB
$mysqli = connect_mysqli();
$mysqli->set_charset("utf8");

function limpiarTexto($valor){
	return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

$paginaActual = isset($_POST['partida']) ? intval($_POST['partida']) : 1;
if ($paginaActual <= 0) {
	$paginaActual = 1;
}

$estado = isset($_POST['estado']) ? trim($_POST['estado']) : '1';
$paciente = isset($_POST['paciente']) ? trim($_POST['paciente']) : '';
$tipo_paciente = isset($_POST['tipo_paciente']) ? trim($_POST['tipo_paciente']) : '1';
$dato = isset($_POST['dato']) ? trim($_POST['dato']) : '';

$nroLotes = 15;
$limit = ($paginaActual <= 1) ? 0 : $nroLotes * ($paginaActual - 1);

$condiciones = array();
$types = '';
$params = array();

$condiciones[] = "estado = ?";
$types .= "s";
$params[] = $estado;

$condiciones[] = "tipo_paciente_id = ?";
$types .= "s";
$params[] = $tipo_paciente;

if ($dato !== '') {
	$dato_inicio = $dato . '%';
	$dato_like = '%' . $dato . '%';

	$condiciones[] = "(
		expediente LIKE ?
		OR nombre LIKE ?
		OR apellido LIKE ?
		OR CONCAT(apellido, ' ', nombre) LIKE ?
		OR CONCAT(nombre, ' ', apellido) LIKE ?
		OR telefono1 LIKE ?
		OR telefono2 LIKE ?
		OR identidad LIKE ?
		OR email LIKE ?
		OR localidad LIKE ?
	)";

	$types .= "ssssssssss";
	$params[] = $dato_inicio;
	$params[] = $dato_inicio;
	$params[] = $dato_inicio;
	$params[] = $dato_like;
	$params[] = $dato_like;
	$params[] = $dato_inicio;
	$params[] = $dato_inicio;
	$params[] = $dato_inicio;
	$params[] = $dato_like;
	$params[] = $dato_like;
}

$where = " WHERE " . implode(" AND ", $condiciones);

function ejecutar_consulta($mysqli, $sql, $types = '', $params = array()){
	$stmt = $mysqli->prepare($sql);

	if (!$stmt) {
		echo json_encode(array(
			'<div style="color:#C7030D; font-weight:700;">Error al preparar consulta: ' . htmlspecialchars($mysqli->error, ENT_QUOTES, 'UTF-8') . '</div>',
			''
		), JSON_UNESCAPED_UNICODE);
		exit;
	}

	if ($types !== '' && count($params) > 0) {
		$bind = array();
		$bind[] = $types;

		for ($i = 0; $i < count($params); $i++) {
			$bind[] = &$params[$i];
		}

		call_user_func_array(array($stmt, 'bind_param'), $bind);
	}

	if (!$stmt->execute()) {
		echo json_encode(array(
			'<div style="color:#C7030D; font-weight:700;">Error al ejecutar consulta: ' . htmlspecialchars($stmt->error, ENT_QUOTES, 'UTF-8') . '</div>',
			''
		), JSON_UNESCAPED_UNICODE);
		exit;
	}

	$result = $stmt->get_result();

	if (!$result) {
		echo json_encode(array(
			'<div style="color:#C7030D; font-weight:700;">Error al obtener resultados: ' . htmlspecialchars($stmt->error, ENT_QUOTES, 'UTF-8') . '</div>',
			''
		), JSON_UNESCAPED_UNICODE);
		exit;
	}

	return array($stmt, $result);
}

// TOTAL DE REGISTROS
$query_count = "
	SELECT COUNT(*) AS total
	FROM pacientes
	$where
";

$count_exec = ejecutar_consulta($mysqli, $query_count, $types, $params);
$stmt_count = $count_exec[0];
$result_count = $count_exec[1];

$row_count = $result_count->fetch_assoc();
$nroProductos = isset($row_count['total']) ? intval($row_count['total']) : 0;

$stmt_count->close();

$nroPaginas = ($nroProductos > 0) ? ceil($nroProductos / $nroLotes) : 0;

$lista = '';
$tabla = '';

if ($paginaActual > 1) {
	$lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(1);void(0);">Inicio</a></li>';
}

if ($paginaActual > 1) {
	$lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(' . ($paginaActual - 1) . ');void(0);">Anterior ' . ($paginaActual - 1) . '</a></li>';
}

if ($paginaActual < $nroPaginas) {
	$lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(' . ($paginaActual + 1) . ');void(0);">Siguiente ' . ($paginaActual + 1) . ' de ' . $nroPaginas . '</a></li>';
}

if ($paginaActual > 1 && $nroPaginas > 0) {
	$lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(' . $nroPaginas . ');void(0);">Última</a></li>';
}

// CONSULTA PRINCIPAL
$query = "
	SELECT 
		pacientes_id,
		CONCAT(nombre, ' ', apellido) AS paciente,
		identidad,
		telefono1,
		telefono2,
		fecha,
		expediente AS expediente_,
		localidad,
		CASE WHEN estado = '1' THEN 'Activo' ELSE 'Inactivo' END AS estado_texto,
		CASE WHEN genero = 'H' THEN 'Hombre' ELSE 'Mujer' END AS genero_texto,
		CASE WHEN expediente = '0' THEN 'TEMP' ELSE expediente END AS expediente,
		email
	FROM pacientes
	$where
	ORDER BY expediente
	LIMIT ?, ?
";

$types_main = $types . "ii";
$params_main = $params;
$params_main[] = $limit;
$params_main[] = $nroLotes;

$main_exec = ejecutar_consulta($mysqli, $query, $types_main, $params_main);
$stmt = $main_exec[0];
$result = $main_exec[1];

if ($estado == "1") {
	$estado_label = "Inhabilitar";
	$icon = "fa fa-ban";
	$estado_accion_color = "text-warning";
} else {
	$estado_label = "Habilitar";
	$icon = "fa fa-check";
	$estado_accion_color = "text-success";
}

$tabla .= '
<style>
	.pacientes-table-wrapper {
		width: 100%;
		overflow-x: auto;
	}

	.pacientes-table {
		width: 100%;
		border-collapse: separate;
		border-spacing: 0;
		background: #fff;
		font-size: 14px;
	}

	.pacientes-table thead th {
		background: #129aaa;
		color: #fff;
		font-weight: 700;
		padding: 14px 12px;
		vertical-align: middle;
		border: none;
		white-space: nowrap;
	}

	.pacientes-table tbody td {
		padding: 14px 12px;
		vertical-align: middle;
		border-top: 1px solid #e8edf2;
		color: #222;
	}

	.pacientes-table tbody tr:nth-child(even) {
		background: #f4f6f8;
	}

	.pacientes-table tbody tr:hover {
		background: #eef9fb;
	}

	.badge-identidad-paciente {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		background: #eef7ff;
		border: 1px solid #b8dcff;
		color: #006992;
		border-radius: 12px;
		padding: 6px 10px;
		font-weight: 700;
		text-decoration: none !important;
		white-space: nowrap;
	}

	.badge-genero-hombre {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		background: #e9f3ff;
		border: 1px solid #b8dcff;
		color: #006992;
		border-radius: 12px;
		padding: 6px 10px;
		font-weight: 700;
		white-space: nowrap;
	}

	.badge-genero-mujer {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		background: #fff0f6;
		border: 1px solid #ffc2d8;
		color: #c2185b;
		border-radius: 12px;
		padding: 6px 10px;
		font-weight: 700;
		white-space: nowrap;
	}

	.paciente-nombre {
		font-weight: 700;
		color: #243447;
		line-height: 1.35;
	}

	.paciente-sub {
		font-size: 12px;
		color: #6c757d;
		margin-top: 3px;
	}

	.telefono-pill {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		background: #f1f8f4;
		color: #198754;
		border: 1px solid #bfe8cf;
		border-radius: 12px;
		padding: 6px 10px;
		font-weight: 700;
		white-space: nowrap;
	}

	.dato-empty {
		display: inline-flex;
		align-items: center;
		background: #f2f2f2;
		color: #888;
		border-radius: 12px;
		padding: 6px 10px;
		font-weight: 600;
		white-space: nowrap;
	}

	.correo-paciente {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		color: #006992;
		font-weight: 700;
		word-break: break-word;
	}

	.direccion-paciente {
		max-width: 360px;
		white-space: normal;
		line-height: 1.35;
		color: #333;
	}

	.badge-estado-activo {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		background: #eaf8ee;
		border: 1px solid #bfe8cf;
		color: #198754;
		border-radius: 14px;
		padding: 7px 12px;
		font-weight: 700;
		white-space: nowrap;
	}

	.badge-estado-inactivo {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		background: #fdecec;
		border: 1px solid #f7b9bf;
		color: #CC2936;
		border-radius: 14px;
		padding: 7px 12px;
		font-weight: 700;
		white-space: nowrap;
	}

	.btn-acciones-pacientes {
		background: #0d6efd;
		border-color: #0d6efd;
		color: #fff !important;
		font-weight: 700;
		border-radius: 7px;
		padding: 7px 12px;
		box-shadow: 0 2px 5px rgba(13, 110, 253, .20);
		white-space: nowrap;
		line-height: 1.2;
	}

	.btn-acciones-pacientes:hover {
		background: #0b5ed7;
		border-color: #0b5ed7;
		color: #fff !important;
	}

	.dropdown-menu-pacientes {
		border: 0;
		border-radius: 10px;
		box-shadow: 0 10px 25px rgba(0,0,0,.12);
		overflow: hidden;
		min-width: 210px;
	}

	.dropdown-menu-pacientes .dropdown-item {
		padding: 10px 14px;
		font-weight: 600;
		color: #333;
	}

	.dropdown-menu-pacientes .dropdown-item:hover {
		background: #f2f7ff;
	}

	.total-pacientes-row {
		background: #129aaa !important;
		color: #fff !important;
	}

	.total-pacientes-row td {
		color: #fff !important;
		font-weight: 700;
		padding: 13px !important;
		border-top: none !important;
	}
</style>

<div class="pacientes-table-wrapper">
<table class="pacientes-table table table-hover">
	<thead>
		<tr>
			<th width="4%">N°</th>
			<th width="10%">Identidad</th>
			<th width="18%">Paciente</th>
			<th width="8%">Género</th>
			<th width="10%">Teléfono 1</th>
			<th width="10%">Teléfono 2</th>
			<th width="16%">Correo</th>
			<th width="18%">Dirección</th>
			<th width="8%">Estado</th>
			<th width="8%">Acciones</th>
		</tr>
	</thead>
	<tbody>
';

$i = $limit + 1;

if ($result->num_rows > 0) {
	while ($registro2 = $result->fetch_assoc()) {
		$pacientes_id = intval($registro2['pacientes_id']);

		$identidad = limpiarTexto($registro2['identidad']);
		$paciente_nombre = limpiarTexto($registro2['paciente']);
		$genero = limpiarTexto($registro2['genero_texto']);
		$telefono1 = limpiarTexto($registro2['telefono1']);
		$telefono2 = limpiarTexto($registro2['telefono2']);
		$email = limpiarTexto($registro2['email']);
		$localidad = limpiarTexto($registro2['localidad']);
		$estado_texto = limpiarTexto($registro2['estado_texto']);
		$expediente = limpiarTexto($registro2['expediente']);

		$identidad_html = '
			<a class="badge-identidad-paciente" title="Información de Cliente" href="javascript:showExpediente(' . $pacientes_id . ');void(0);">
				<i class="fas fa-id-card"></i> ' . $identidad . '
			</a>
		';

		$genero_html = ($genero == 'Hombre')
			? '<span class="badge-genero-hombre"><i class="fas fa-mars"></i> Hombre</span>'
			: '<span class="badge-genero-mujer"><i class="fas fa-venus"></i> Mujer</span>';

		$telefono1_html = ($telefono1 !== '' && $telefono1 !== '0')
			? '<span class="telefono-pill"><i class="fas fa-phone-alt"></i> ' . $telefono1 . '</span>'
			: '<span class="dato-empty">Sin dato</span>';

		$telefono2_html = ($telefono2 !== '' && $telefono2 !== '0')
			? '<span class="telefono-pill"><i class="fas fa-phone-alt"></i> ' . $telefono2 . '</span>'
			: '<span class="dato-empty">Sin dato</span>';

		$email_html = ($email !== '')
			? '<span class="correo-paciente"><i class="fas fa-envelope"></i> ' . $email . '</span>'
			: '<span class="dato-empty">Sin correo</span>';

		$direccion_html = ($localidad !== '')
			? '<div class="direccion-paciente"><i class="fas fa-map-marker-alt text-danger"></i> ' . $localidad . '</div>'
			: '<span class="dato-empty">Sin dirección</span>';

		$estado_html = ($estado_texto == 'Activo')
			? '<span class="badge-estado-activo"><i class="fas fa-check-circle"></i> Activo</span>'
			: '<span class="badge-estado-inactivo"><i class="fas fa-ban"></i> Inactivo</span>';

		$tabla .= '
		<tr>
			<td><strong>' . $i . '</strong></td>

			<td>' . $identidad_html . '</td>

			<td>
				<div class="paciente-nombre">
					<i class="fas fa-user text-info"></i> ' . $paciente_nombre . '
				</div>
				<div class="paciente-sub">
					Expediente: ' . $expediente . '
				</div>
			</td>

			<td>' . $genero_html . '</td>
			<td>' . $telefono1_html . '</td>
			<td>' . $telefono2_html . '</td>
			<td>' . $email_html . '</td>
			<td>' . $direccion_html . '</td>
			<td>' . $estado_html . '</td>

			<td>
				<div class="btn-group">
					<button type="button" class="btn btn-sm btn-acciones-pacientes dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<i class="fas fa-cog"></i> Acciones
					</button>
					<div class="dropdown-menu dropdown-menu-right dropdown-menu-pacientes">
						<a class="dropdown-item" href="javascript:modal_muestras(' . $pacientes_id . ');void(0);">
							<i class="far fa-eye text-info mr-2"></i> Ver muestras
						</a>

						<a class="dropdown-item" href="javascript:modal_agregar_expediente_manual(' . $pacientes_id . ');void(0);">
							<i class="fas fa-id-card text-warning mr-2"></i> Editar RTN
						</a>

						<a class="dropdown-item" href="javascript:editarRegistro(' . $pacientes_id . ');void(0);">
							<i class="fas fa-user-edit text-primary mr-2"></i> Editar cliente
						</a>

						<a class="dropdown-item" href="javascript:DisableRegister(' . $pacientes_id . ');void(0);">
							<i class="' . $icon . ' ' . $estado_accion_color . ' mr-2"></i> ' . $estado_label . '
						</a>

						<div class="dropdown-divider"></div>

						<a class="dropdown-item text-danger" href="javascript:modal_eliminar(' . $pacientes_id . ');void(0);">
							<i class="fas fa-trash mr-2"></i> Eliminar
						</a>
					</div>
				</div>
			</td>
		</tr>';

		$i++;
	}

	$tabla .= '
	<tr class="total-pacientes-row">
		<td colspan="10" align="center">
			Total de registros encontrados: ' . number_format($nroProductos) . '
		</td>
	</tr>';
} else {
	$tabla .= '
	<tr>
		<td colspan="10" style="color:#C7030D; padding:18px; font-weight:700;">
			No se encontraron resultados
		</td>
	</tr>';
}

$tabla .= '
	</tbody>
</table>
</div>';

$stmt->close();
$mysqli->close();

$array = array(
	0 => $tabla,
	1 => $lista
);

echo json_encode($array, JSON_UNESCAPED_UNICODE);