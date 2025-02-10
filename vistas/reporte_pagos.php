<?php
session_start();
include '../php/funtions.php';

if (isset($_SESSION['colaborador_id']) == false) {
	header('Location: login.php');
}

$_SESSION['menu'] = 'Reporte de Pagos';

if (isset($_SESSION['colaborador_id'])) {
	$colaborador_id = $_SESSION['colaborador_id'];
} else {
	$colaborador_id = '';
}

$type = $_SESSION['type'];

$nombre_host = gethostbyaddr($_SERVER['REMOTE_ADDR']);  // HOSTNAME
$fecha = date('Y-m-d H:i:s');
$comentario = mb_convert_case('Ingreso al Modulo de Reporte de Pagos', MB_CASE_TITLE, 'UTF-8');

if ($colaborador_id != '' || $colaborador_id != null) {
	historial_acceso($comentario, $nombre_host, $colaborador_id);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta name="author" content="Script Tutorials" />
    <meta name="description" content="Responsive Websites Orden Hospitalaria de San Juan de Dios">
	<meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=Edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Reporte de Pagos :: <?php echo SERVEREMPRESA; ?></title>
	<?php include ('script_css.php'); ?>
</head>
<body>
   <!--Ventanas Modales-->
   <!-- Small modal -->
  <?php include ('templates/modals.php'); ?>

<!--INICIO MODAL-->
<div class="modal fade" id="mensaje_show">
	<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Detalles de Pagos</h4>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
        </div><div class="container"></div>
        <div class="modal-body">
			<form class="FormularioAjax" id="formCobros" action="" method="POST" data-form="" autocomplete="off" enctype="multipart/form-data">
				<div class="form-row">
					<div class="col-md-12 mb-3">
					   <span id="mensaje_mensaje_show"></span>
					</div>
				</div>
			  <div class="modal-footer">
				<button class="btn btn-primary ml-2" type="button" id="okay" data-dismiss="modal"><div class="sb-nav-link-icon" id="okay"></div><i class="fas fa-thumbs-up fa-lg"></i> Okay</button>
				<button class="btn btn-danger ml-2" type="button" id="bad" data-dismiss="modal"><div class="sb-nav-link-icon" id="bad"></div><i class="fas fa-times-circle fa-lg"></i> Okay</button>
			  </div>
			</form>
        </div>
      </div>
    </div>
</div>

<div class="modal fade" id="modal_editar_pagos">
	<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Tipo Pago</h4>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
        </div><div class="container"></div>
        <div class="modal-body">
			<form class="FormularioAjax" id="formulario_reporte_pagos" action="" method="POST" data-form="" autocomplete="off" enctype="multipart/form-data">
				<div class="form-row">
					<div class="col-md-12 mb-3">
					    <input type="hidden" required readonly id="pagos_id" name="pagos_id" />
						<div class="input-group mb-3">
							<input type="text" required readonly id="pro" name="pro" class="form-control"/>
							<div class="input-group-append">
								<span class="input-group-text"><div class="sb-nav-link-icon"></div><i class="fa fa-plus-square"></i></span>
							</div>
						</div>
					</div>
				</div>
				<div class="form-row">
					<div class="col-md-4 mb-3">
					  <label for="fecha_reporte_pago">Fecha</label>
					  <input type="date" id="fecha_reporte_pago" name="fecha_reporte_pago" step="0.01" class="form-control"/>
					</div>
					<div class="col-md-4 mb-3">
					  <label for="paciente_reporte_pago">Paciente</label>
					  <input type="text" id="paciente_reporte_pago" name="paciente_reporte_pago" step="0.01"class="form-control"/>
					  </select>
					</div>
					<div class="col-md-4 mb-3">
					  <label for="factura_reporte_pago">Factura</label>
					  <input type="text" id="factura_reporte_pago" name="factura_reporte_pago" step="0.01" class="form-control"/>
					</div>
				</div>
				<div class="form-row">
          <div class="col-md-3 mb-3">
					    <label for="tipo_pago_reporte">Tipo Pago <span class="priority">*<span/></label>
						<div class="input-group mb-3">
							<select class="selectpicker" id="tipo_pago_reporte" name="tipo_pago_reporte" required data-live-search="true" title="Tipo Pago" data-size="5">
							</select>
						</div>
				  </div>
					<div class="col-md-4 mb-3">
					  <label for="paciente_reporte_efectivo">Efectivo</label>
					  <input type="text" id="paciente_reporte_efectivo" name="paciente_reporte_efectivo" step="0.01" class="form-control"/>
					  </select>
					</div>
					<div class="col-md-4 mb-3">
					  <label for="factura_reporte_tarjeta">Tarjeta</label>
					  <input type="text" id="factura_reporte_tarjeta" name="factura_reporte_tarjeta" readonly step="0.01" class="form-control"/>
					</div>
				</div>
				<div class="form-row">
					<div class="col-md-4 mb-3">
					  <label for="tipo_pago_importe">Importe</label>
					  <input type="text" id="tipo_pago_importe" name="tipo_pago_importe" step="0.01" class="form-control"/>
                      </select>
					</div>
				</div>
			</form>
        </div>
		<div class="modal-footer">
			<button class="btn btn-primary ml-2" type="submit" id="reg_reporte_pagos" form="formulario_reporte_pagos"><div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar</button>
		</div>
      </div>
    </div>
</div>
   <?php include ('modals/modals.php'); ?>
<!--FIN MODAL-->

   <!--Fin Ventanas Modales-->
	<!--MENU-->
       <?php include ('templates/menu.php'); ?>
    <!--FIN MENU-->

<br><br><br>
<div class="container-fluid">
	<ol class="breadcrumb mt-2 mb-4">
		<li class="breadcrumb-item"><a class="breadcrumb-link" href="inicio.php">Dashboard</a></li>
		<li class="breadcrumb-item active" id="acciones_factura"><span id="label_acciones_factura"></span>Reporte de Pagos</li>
	</ol>

  <div class="card mb-4">
    <div class="card-header">
      <i class="fas fa-search  mr-1"></i>
      Búsqueda
    </div>
    <div class="card-body">
      <form id="form_main" class="form-inline">
        <div class="form-group mr-1">
          <div class="input-group">
            <div class="input-group-append">
              <span class="input-group-text"><div class="sb-nav-link-icon"></div>Profesional</span>
            </div>
            <select id="profesional" name="profesional" class="selectpicker" title="Profesional" data-live-search="true">
            </select>
          </div>
        </div>        
        <div class="form-group mr-1">
          <div class="input-group">
            <div class="input-group-append">
              <span class="input-group-text"><div class="sb-nav-link-icon"></div>Cliente</span>
            </div>
            <select id="clientes" name="clientes" class="selectpicker" title="Cliente" data-live-search="true">
            </select>
          </div>
        </div>
        <div class="form-group mr-1">
          <div class="input-group">
            <div class="input-group-append">
            <span class="input-group-text"><div class="sb-nav-link-icon"></div>Estado</span>
            </div>
            <select id="estado" name="estado" class="selectpicker" title="Estado" data-live-search="true">
            </select>
          </div>
        </div>
        <div class="form-group mr-1">
          <div class="input-group">
    				<div class="input-group-append">
    					<span class="input-group-text"><div class="sb-nav-link-icon"></div>Inicio</span>
    				</div>
            <input type="date" required="required" id="fecha_b" name="fecha_b" data-toggle="tooltip" data-placement="top" title="Fecha Inicial" value="<?php
$fecha = date('Y-m-d');

$año = date('Y', strtotime($fecha));
$mes = date('m', strtotime($fecha));
$dia = date('d', mktime(0, 0, 0, $mes + 1, 0, $año));

$dia1 = date('d', mktime(0, 0, 0, $mes, 1, $año));  // PRIMER DIA DEL MES
$dia2 = date('d', mktime(0, 0, 0, $mes, $dia, $año));  // ULTIMO DIA DEL MES

$fecha_inicial = date('Y-m-d', strtotime($año . '-' . $mes . '-' . $dia1));
$fecha_final = date('Y-m-d', strtotime($año . '-' . $mes . '-' . $dia2));

echo $fecha_inicial;
?>" class="form-control"/>
    			</div>
        </div>
        <div class="form-group mr-1">
          <div class="input-group">
    				<div class="input-group-append">
    					<span class="input-group-text"><div class="sb-nav-link-icon"></div>Fin</span>
    				</div>
    				<input type="date" required="required" id="fecha_f" name="fecha_f" style="width:160px;" value="<?php echo date('Y-m-d'); ?>" data-toggle="tooltip" data-placement="top" title="Fecha Final" class="form-control"/>
    			</div>
        </div>
      </form>
    </div>
    <div class="card-footer small text-muted">

    </div>
  </div>

  <div class="card mb-4">
		<div class="card-header">
			<i class="fab fa-sellsy mr-1"></i>
			Resultado
		</div>
		<div class="card-body">
			<div class="table-responsive">
				<form id="formPrincipal">
					<div class="col-md-12 mb-3">
						<table id="dataTableReportePagosMain" class="table table-striped table-condensed table-hover" style="width:100%">
							<thead>
								<tr>
									<th>Fecha</th>
									<th>Tipo Pago</th>
									<th>Paciente</th>
									<th>Identidad</th>
									<th>Factura</th>
									<th>Pago Recibido</th>
									<th>Efectivo</th>
									<th>Tarjeta</th>
									<th>Acciones</th>
								</tr>
							</thead>
							<tfoot>
								<tr>
									<th colspan="4"></th>
									<th colspan="1">Totales:</th>
									<th id="footerImporte"></th>
									<th id="footerEfectivo"></th>
									<th id="footerTarjeta"></th>
									<th></th>
								</tr>
							</tfoot>						
						</table>
					</div>
				</form>
			</div>
		</div>
		<div class="card-footer small text-muted">
		</div>	
	</div>
    <?php include ('templates/footer.php'); ?>
</div>

    <!-- add javascripts -->
	<?php
	include 'script.php';

	include '../js/main.php';
	include '../js/myjava_reporte_pagos.php';
	include '../js/select.php';
	include '../js/functions.php';
	include '../js/myjava_cambiar_pass.php';
	?>

</body>
</html>
