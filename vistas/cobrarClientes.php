<?php
session_start();
include "../php/funtions.php";

if( isset($_SESSION['colaborador_id']) == false ){
   header('Location: login.php');
}

$_SESSION['menu'] = "Almacen";

if(isset($_SESSION['colaborador_id'])){
 $colaborador_id = $_SESSION['colaborador_id'];
}else{
   $colaborador_id = "";
}

$type = $_SESSION['type'];

$nombre_host = gethostbyaddr($_SERVER['REMOTE_ADDR']);//HOSTNAME
$fecha = date("Y-m-d H:i:s");
$comentario = "Ingreso al Modulo de Cuentas por Cobrar Clientes";

if($colaborador_id != "" || $colaborador_id != null){
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
	<title>Cobrar Clientes :: <?php echo SERVEREMPRESA;?></title>
	<?php include("script_css.php"); ?>
</head>
<body>
   <!--Ventanas Modales-->
   <!-- Small modal -->
  <?php include("templates/modals.php"); ?>

<!--INICIO MODAL-->
   <?php include("modals/modals.php");?>
<!--FIN MODAL-->

   <!--Fin Ventanas Modales-->
	<!--MENU-->
       <?php include("templates/menu.php"); ?>
    <!--FIN MENU-->

<br><br><br>
<div class="container-fluid">
	<ol class="breadcrumb mt-2 mb-4">
		<li class="breadcrumb-item"><a class="breadcrumb-link" href="inicio.php">Dashboard</a></li>
		<li class="breadcrumb-item active" id="acciones_factura"><span id="label_acciones_factura"></span>Cobrar Clientes</li>
	</ol>

	<div class="card mb-4">
    <div class="card-header">
      <i class="fas fa-search  mr-1"></i>
      Búsqueda
    </div>
    <div class="card-body">
      <form id="form_main_cobrar_clientes" class="form-inline">
        <div class="form-group mr-1">
          <div class="input-group">
            <div class="input-group-append">
              <span class="input-group-text"><div class="sb-nav-link-icon"></div>Estado</span>
            </div>
            <select id="cobrar_clientes_estado" name="cobrar_clientes_estado" class="selectpicker" title="Estado" data-live-search="true">
				<option value="1">Pendientes</option>
				<option value="2">Pagadas</option>
            </select>
          </div>
        </div>
        <div class="form-group mr-1">
          <div class="input-group">
            <div class="input-group-append">
              <span class="input-group-text"><div class="sb-nav-link-icon"></div>Cliente</span>
            </div>
            <select id="cobrar_clientes" name="cobrar_clientes" class="selectpicker" title="Cliente" data-live-search="true">
            </select>
          </div>
        </div>
        <div class="form-group mr-1">
          <div class="input-group">
            <div class="input-group-append">
              <span class="input-group-text"><div class="sb-nav-link-icon"></div>Fecha Inicio</span>
            </div>
            <input type="date" required="required" id="fechai" name="fechai" style="width:160px;" data-toggle="tooltip" data-placement="top" title="Fecha Inicial" value="<?php
                $fecha = date ("Y-m-d");

                $año = date("Y", strtotime($fecha));
                $mes = date("m", strtotime($fecha));
                $dia = date("d", mktime(0,0,0, $mes+1, 0, $año));

                $dia1 = date('d', mktime(0,0,0, $mes, 1, $año)); //PRIMER DIA DEL MES
                $dia2 = date('d', mktime(0,0,0, $mes, $dia, $año)); // ULTIMO DIA DEL MES

                $fecha_inicial = date("Y-m-d", strtotime($año."-".$mes."-".$dia1));
                $fecha_final = date("Y-m-d", strtotime($año."-".$mes."-".$dia2));

                echo $fecha_inicial;
              ?>" class="form-control"/>
          </div>
        </div>
        <div class="form-group mr-1">
          <div class="input-group">
            <div class="input-group-append">
              <span class="input-group-text"><div class="sb-nav-link-icon"></div>Fecha Fin</span>
            </div>
            <input type="date" required="required" id="fechaf" name="fechaf" style="width:160px;" value="<?php echo date ("Y-m-d");?>" data-toggle="tooltip" data-placement="top" title="Fecha Final" class="form-control"/>
          </div>
        </div>
        <div class="form-group">
            <button class="btn btn-primary" type="submit" id="buscar" data-toggle="tooltip" data-placement="top" title="Exportar"><div class="sb-nav-link-icon"></div><i class="fas fa-search fa-lg"></i> Buscar</button>
        </div>
      </form>
    </div>
    <div class="card-footer small text-muted">

    </div>
  </div>

	<div class="card mb-4">
	<div class="card-header">
		<i class="fas fa-sliders-h mr-1"></i>
		Cuentas por Cobrar Clientes
	</div>
	<div class="card-body"> 
		<div class="table-responsive">
			<table id="dataTableCuentasPorCobrarClientes" class="table table-striped table-condensed table-hover" style="width:100%">
				<thead>
					<tr>
						<th>Fecha</th>
						<th>Cliente</th>
						<th>Factura</th>
						<th>Crédito</th>
						<th>Abonos</th>
						<th>Saldo</th>		
						<th>Vendedor</th>
						<th>Abonar</th>
						<th>Abonos Realizados</th>							
						<th>Factura</th>				
					</tr>
				</thead>
				<tfoot class="bg-info text-white font-weight-bold">
					<tr>
						<td colspan='1'>Total</td>
						<td colspan="2"></td>
						<td id="credito-cxc"></td>
						<td id="abono-cxc"></td>
						<td colspan='1' id='total-footer-cxc'></td>
						<td colspan="4"></td>
					</tr>
				</tfoot>
			</table>  
		</div>                   
	</div>
    <div class="card-footer small text-muted">

    </div>
  </div>
    <?php include("templates/footer.php"); ?>
</div>

    <!-- add javascripts -->
	<?php
		include "script.php";

		include "../js/main.php";
    include "../js/invoice.php";
		include "../js/myjava_cobrarClientes.php";
		include "../js/select.php";
		include "../js/functions.php";
		include "../js/myjava_cambiar_pass.php";
	?>

</body>
</html>
