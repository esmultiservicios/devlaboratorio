<?php
session_start();
include "../funtions.php";

header('Content-Type: application/json; charset=UTF-8');

// CONEXION A DB
$mysqli = connect_mysqli();
$mysqli->set_charset("utf8");

function h($valor){
	return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function obtenerNumeroFacturaBuscadoGrupo($texto){
	$texto = trim($texto);

	if($texto == ""){
		return "";
	}

	preg_match_all('/\d+/', $texto, $matches);

	if(!isset($matches[0]) || count($matches[0]) == 0){
		return "";
	}

	$ultimo = end($matches[0]);

	return strval(intval($ultimo));
}

$colaborador_id = isset($_SESSION['colaborador_id']) ? $_SESSION['colaborador_id'] : 0;
$type = isset($_SESSION['type']) ? $_SESSION['type'] : 0;

$paginaActual = isset($_POST['partida']) ? intval($_POST['partida']) : 1;
if($paginaActual <= 0){
	$paginaActual = 1;
}

$fechai = isset($_POST['fechai']) ? trim($_POST['fechai']) : '';
$fechaf = isset($_POST['fechaf']) ? trim($_POST['fechaf']) : '';
$dato = isset($_POST['dato']) ? trim($_POST['dato']) : '';
$pacientesIDGrupo = isset($_POST['pacientesIDGrupo']) ? trim($_POST['pacientesIDGrupo']) : '';
$estado = isset($_POST['estado']) ? trim($_POST['estado']) : 1;
$usuario = isset($_SESSION['colaborador_id']) ? $_SESSION['colaborador_id'] : 0;

if($estado == 0){
	$in = "IN(1)";
}else if($estado == 1){
	$in = "IN(2,4)";
}else if($estado == 4){
	$in = "IN(4)";
}else{
	$in = "IN(3)";
}

$busqueda_paciente = "";
$consulta_datos = "";
$consulta_fecha = "";

if($pacientesIDGrupo != ""){
	$pacientesIDGrupo_esc = $mysqli->real_escape_string($pacientesIDGrupo);
	$busqueda_paciente = " AND f.pacientes_id = '$pacientesIDGrupo_esc' ";
}

/*
	BÚSQUEDA INTELIGENTE:
	- Si NO hay texto en buscar, respeta el filtro de fecha.
	- Si SÍ hay texto en buscar, busca global sin obligar fecha.
*/
if($dato == ""){
	if($fechai != "" && $fechaf != ""){
		$fechai_esc = $mysqli->real_escape_string($fechai);
		$fechaf_esc = $mysqli->real_escape_string($fechaf);
		$consulta_fecha = " AND f.fecha BETWEEN '$fechai_esc' AND '$fechaf_esc' ";
	}
}else{
	$dato_esc = $mysqli->real_escape_string($dato);
	$dato_like = "%" . $dato_esc . "%";
	$dato_inicio = $dato_esc . "%";

	$numero_factura_buscado = obtenerNumeroFacturaBuscadoGrupo($dato);
	$numero_factura_buscado = ($numero_factura_buscado != "") ? intval($numero_factura_buscado) : -1;

	$consulta_datos = "
		AND (
			CONCAT(p.nombre,' ',p.apellido) LIKE '$dato_like'
			OR p.nombre LIKE '$dato_inicio'
			OR p.apellido LIKE '$dato_inicio'
			OR p.identidad LIKE '$dato_inicio'
			OR f.number LIKE '$dato_inicio'
			OR f.number = '$numero_factura_buscado'
			OR CONCAT(sc.prefijo, LPAD(f.number, sc.relleno, '0')) LIKE '$dato_like'
		)
	";
}

/* FROM BASE */
$from = "
	FROM facturas_grupal AS f
	INNER JOIN pacientes AS p
		ON f.pacientes_id = p.pacientes_id
	INNER JOIN secuencia_facturacion AS sc
		ON f.secuencia_facturacion_id = sc.secuencia_facturacion_id
	INNER JOIN servicios AS s
		ON f.servicio_id = s.servicio_id
	INNER JOIN colaboradores AS c
		ON f.colaborador_id = c.colaborador_id
	LEFT JOIN (
		SELECT 
			facturas_grupal_id,
			SUM(importe) AS precio,
			SUM(cantidad) AS cantidad,
			SUM(descuento) AS descuento,
			SUM(importe * cantidad) AS neto_antes_isv,
			SUM(isv_valor) AS isv_neto
		FROM facturas_grupal_detalle
		GROUP BY facturas_grupal_id
	) AS fd
		ON fd.facturas_grupal_id = f.facturas_grupal_id
	WHERE f.estado $in
	$consulta_fecha
	$busqueda_paciente
	$consulta_datos
";

/* TOTAL */
$query_total = "
	SELECT COUNT(*) AS total
	$from
";

$result_total = $mysqli->query($query_total) or die($mysqli->error);
$row_total = $result_total->fetch_assoc();
$nroProductos = isset($row_total['total']) ? intval($row_total['total']) : 0;

$nroLotes = 25;
$nroPaginas = ($nroProductos > 0) ? ceil($nroProductos / $nroLotes) : 0;

$lista = '';
$tabla = '';

if($paginaActual > 1){
	$lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination(1);void(0);">Inicio</a></li>';
}

if($paginaActual > 1){
	$lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination('.($paginaActual-1).');void(0);">Anterior '.($paginaActual-1).'</a></li>';
}

if($paginaActual < $nroPaginas){
	$lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination('.($paginaActual+1).');void(0);">Siguiente '.($paginaActual+1).' de '.$nroPaginas.'</a></li>';
}

if($paginaActual > 1 && $nroPaginas > 0){
	$lista .= '<li class="page-item"><a class="page-link" href="javascript:pagination('.$nroPaginas.');void(0);">Última</a></li>';
}

$limit = ($paginaActual <= 1) ? 0 : $nroLotes * ($paginaActual - 1);

/* CONSULTA PRINCIPAL */
$registro = "
	SELECT 
		f.facturas_grupal_id AS facturas_id,
		f.fecha AS fecha,
		p.identidad AS identidad,
		CONCAT(p.nombre,' ',p.apellido) AS paciente,
		sc.prefijo AS prefijo,
		f.number AS numero,
		s.nombre AS servicio,
		CONCAT(c.nombre,' ',c.apellido) AS profesional,
		sc.relleno AS relleno,
		DATE_FORMAT(f.fecha, '%d/%m/%Y') AS fecha1,
		f.pacientes_id AS pacientes_id,
		f.cierre AS cierre,
		COALESCE(fd.precio, 0) AS precio,
		COALESCE(fd.cantidad, 0) AS cantidad,
		COALESCE(fd.descuento, 0) AS descuento,
		COALESCE(fd.neto_antes_isv, 0) AS neto_antes_isv,
		COALESCE(fd.isv_neto, 0) AS isv_neto
	$from
	ORDER BY f.number DESC
	LIMIT $limit, $nroLotes
";

$result = $mysqli->query($registro) or die($mysqli->error);

$tabla .= '
<style>
	.grupal-table-wrapper {
		width: 100%;
		overflow-x: auto;
	}

	.grupal-table {
		width: 100%;
		border-collapse: separate;
		border-spacing: 0;
		background: #fff;
		font-size: 14px;
	}

	.grupal-table thead th {
		background: #129aaa;
		color: #fff;
		font-weight: 700;
		padding: 14px 12px;
		vertical-align: middle;
		border: none;
		white-space: nowrap;
	}

	.grupal-table tbody td {
		padding: 14px 12px;
		vertical-align: middle;
		border-top: 1px solid #e8edf2;
		color: #222;
	}

	.grupal-table tbody tr:nth-child(even) {
		background: #f4f6f8;
	}

	.grupal-table tbody tr:hover {
		background: #eef9fb;
	}

	.badge-fecha-grupal,
	.badge-numero-grupal,
	.badge-identidad-grupal,
	.badge-cierre-grupal {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		border-radius: 12px;
		padding: 6px 10px;
		font-weight: 700;
		white-space: nowrap;
		line-height: 1;
	}

	.badge-fecha-grupal {
		background: #f7f7f7;
		border: 1px solid #d8dde3;
		color: #333;
		text-decoration: none !important;
	}

	.badge-numero-grupal {
		background: #fff7e6;
		border: 1px solid #ffd98a;
		color: #9a6500;
	}

	.badge-identidad-grupal {
		background: #f3f7fa;
		border: 1px solid #d9e4ec;
		color: #333;
	}

	.badge-cierre-ok {
		background: #eaf8ee;
		border: 1px solid #bfe8cf;
		color: #198754;
	}

	.badge-cierre-no {
		background: #fff3cd;
		border: 1px solid #ffe08a;
		color: #8a6400;
	}

	.paciente-grupal {
		font-weight: 700;
		color: #243447;
		line-height: 1.35;
	}

	.profesional-grupal {
		font-weight: 600;
		color: #333;
		line-height: 1.35;
	}

	.money-normal {
		font-weight: 700;
		color: #333;
		white-space: nowrap;
	}

	.money-isv {
		font-weight: 700;
		color: #006992;
		white-space: nowrap;
	}

	.money-descuento {
		font-weight: 700;
		color: #CC2936;
		white-space: nowrap;
	}

	.money-neto {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		min-width: 86px;
		border: 2px solid #A5BF13;
		color: #7f960c;
		border-radius: 12px;
		padding: 6px 10px;
		font-weight: 800;
		white-space: nowrap;
	}

	.btn-acciones-grupal {
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

	.btn-acciones-grupal:hover {
		background: #0b5ed7;
		border-color: #0b5ed7;
		color: #fff !important;
	}

	.dropdown-menu-grupal {
		border: 0;
		border-radius: 10px;
		box-shadow: 0 10px 25px rgba(0,0,0,.12);
		overflow: hidden;
		min-width: 185px;
	}

	.dropdown-menu-grupal .dropdown-item {
		padding: 10px 14px;
		font-weight: 600;
		color: #333;
	}

	.dropdown-menu-grupal .dropdown-item:hover {
		background: #f2f7ff;
	}

	.factura-empty {
		display: inline-flex;
		align-items: center;
		background: #f2f2f2;
		color: #888;
		border-radius: 12px;
		padding: 6px 10px;
		font-weight: 600;
		white-space: nowrap;
	}

	.total-grupal-row {
		background: #129aaa !important;
		color: #fff !important;
	}

	.total-grupal-row td {
		color: #fff !important;
		font-weight: 700;
		padding: 13px !important;
		border-top: none !important;
	}
</style>

<div class="grupal-table-wrapper">
<table class="grupal-table table table-hover">
	<thead>
		<tr>
			<th width="4%">No.</th>
			<th width="8%">Fecha</th>
			<th width="10%">Identidad</th>
			<th width="18%">Paciente / Empresa</th>
			<th width="12%">Número</th>
			<th width="8%">Importe</th>
			<th width="8%">ISV</th>
			<th width="8%">Descuento</th>
			<th width="8%">Neto</th>
			<th width="10%">Servicio</th>
			<th width="12%">Profesional</th>
			<th width="8%">Cierre</th>
			<th width="8%">Acciones</th>
		</tr>
	</thead>
	<tbody>
';

$i = $limit + 1;

if($result->num_rows > 0){
	while($registro2 = $result->fetch_assoc()){
		$facturas_id = intval($registro2['facturas_id']);

		$precio = floatval($registro2['precio']);
		$descuento = floatval($registro2['descuento']);
		$isv_neto = floatval($registro2['isv_neto']);
		$neto_antes_isv = floatval($registro2['neto_antes_isv']);
		$total = ($neto_antes_isv + $isv_neto) - $descuento;

		$pago = false;
		$tiene_numero = false;

		if($registro2['numero'] != ""){
			$numero = $registro2['prefijo'] . rellenarDigitos($registro2['numero'], $registro2['relleno']);
			$tiene_numero = true;

			$query_pago = "SELECT pagos_id FROM pagos WHERE facturas_id = '$facturas_id' LIMIT 1";
			$result_pago = $mysqli->query($query_pago) or die($mysqli->error);

			if($result_pago->num_rows > 0){
				$pago = true;
			}
		}else{
			$numero = "Aún no se ha generado";
		}

		$fecha1 = h($registro2['fecha1']);
		$identidad = h($registro2['identidad']);
		$paciente = h($registro2['paciente']);
		$numero_html = h($numero);
		$servicio = h($registro2['servicio']);
		$profesional = h($registro2['profesional']);
		$numero_raw = h($registro2['numero']);
		$pacientes_id = intval($registro2['pacientes_id']);

		if($numero == "Aún no se ha generado"){
			$numero_badge = '<span class="factura-empty">' . $numero_html . '</span>';
		}else{
			$numero_badge = '<span class="badge-numero-grupal"><i class="fas fa-file-invoice"></i> ' . $numero_html . '</span>';
		}

		if($registro2['cierre'] == 1){
			$cierre_badge = '<span class="badge-cierre-grupal badge-cierre-ok" data-toggle="tooltip" data-placement="right" title="La factura ha sido cerrada"><i class="fas fa-check-double"></i> Cerrada</span>';
		}else{
			$cierre_badge = '<span class="badge-cierre-grupal badge-cierre-no" data-toggle="tooltip" data-placement="right" title="No se ha cerrado la factura"><i class="fas fa-check"></i> Abierta</span>';
		}

		$tabla .= '
		<tr>
			<td><strong>' . $i . '</strong></td>

			<td>
				<a class="badge-fecha-grupal" href="javascript:invoicesDetails(' . $facturas_id . ');void(0);">
					<i class="fas fa-calendar-alt"></i> ' . $fecha1 . '
				</a>
			</td>

			<td>
				<span class="badge-identidad-grupal">
					<i class="fas fa-id-card"></i> ' . $identidad . '
				</span>
			</td>

			<td>
				<div class="paciente-grupal">
					<i class="fas fa-user text-info"></i> ' . $paciente . '
				</div>
			</td>

			<td>' . $numero_badge . '</td>

			<td><span class="money-normal">L ' . number_format($precio, 2) . '</span></td>
			<td><span class="money-isv">L ' . number_format($isv_neto, 2) . '</span></td>
			<td><span class="money-descuento">L ' . number_format($descuento, 2) . '</span></td>
			<td><span class="money-neto">L ' . number_format($total, 2) . '</span></td>

			<td>' . $servicio . '</td>

			<td>
				<div class="profesional-grupal">
					<i class="fas fa-user-md text-primary"></i> ' . $profesional . '
				</div>
			</td>

			<td>' . $cierre_badge . '</td>

			<td>
				<div class="btn-group">
					<button type="button" class="btn btn-sm btn-acciones-grupal dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<i class="fas fa-cog"></i> Acciones
					</button>
					<div class="dropdown-menu dropdown-menu-right dropdown-menu-grupal">
						<a class="dropdown-item" href="javascript:mailBillGroup(' . $facturas_id . ');void(0);">
							<i class="far fa-paper-plane text-success mr-2"></i> Enviar correo
						</a>
						<a class="dropdown-item" href="javascript:printBillGroup(' . $numero_raw . ');void(0);">
							<i class="fas fa-print text-primary mr-2"></i> Imprimir
						</a>
						<div class="dropdown-divider"></div>
						<a class="dropdown-item text-danger" href="javascript:modal_rollback(' . $facturas_id . ',' . $pacientes_id . ');void(0);">
							<i class="fas fa-undo mr-2"></i> Revertir
						</a>
					</div>
				</div>
			</td>
		</tr>';

		$i++;
	}

	$tabla .= '
	<tr class="total-grupal-row">
		<td colspan="13" align="center">
			Total de registros encontrados: ' . number_format($nroProductos) . '
		</td>
	</tr>';
}else{
	$tabla .= '
	<tr>
		<td colspan="13" style="color:#C7030D; padding:18px; font-weight:700;">
			No se encontraron resultados, seleccione un profesional para verificar si hay registros almacenados
		</td>
	</tr>';
}

$tabla .= '
	</tbody>
</table>
</div>';

$array = array(
	0 => $tabla,
	1 => $lista
);

echo json_encode($array, JSON_UNESCAPED_UNICODE);

if($result){
	$result->free();
}

$mysqli->close();