<?php
session_start();
include "../php/funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

if( isset($_SESSION['colaborador_id']) == false ){
   header('Location: login.php');
}

$_SESSION['menu'] = "Colaboradores";

if(isset($_SESSION['colaborador_id'])){
 $colaborador_id = $_SESSION['colaborador_id'];
}else{
   $colaborador_id = "";
}

$type = $_SESSION['type'];

$nombre_host = gethostbyaddr($_SERVER['REMOTE_ADDR']);//HOSTNAME
$fecha = date("Y-m-d H:i:s");
$comentario = "Ingreso al Modulo de Colaboradores";

if($colaborador_id != "" || $colaborador_id != null){
   historial_acceso($comentario, $nombre_host, $colaborador_id);
}

//OBTENER NOMBRE DE EMPRESA
$usuario = $_SESSION['colaborador_id'];

$query_empresa = "SELECT e.nombre AS 'nombre'
	FROM users AS u
	INNER JOIN empresa AS e
	ON u.empresa_id = e.empresa_id
	WHERE u.colaborador_id = '$usuario'";
$result = $mysqli->query($query_empresa) or die($mysqli->error);
$consulta_registro = $result->fetch_assoc();

$empresa = '';

if($result->num_rows>0){
  $empresa = $consulta_registro['nombre'];
}

$mysqli->close();//CERRAR CONEXIÓN
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
    <title>Colaboradores :: <?php echo $empresa; ?></title>
	<?php include("script_css.php"); ?>
</head>
<body>
   <!--Ventanas Modales-->
   <!-- Small modal -->
  <?php include("templates/modals.php"); ?>

<!--INICIO MODAL-->
<div class="modal fade" id="registrar_colaboradores">
	<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Colaboradores</h4>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
        </div><div class="container"></div>
        <div class="modal-body">
			<form class="FormularioAjax" id="formulario_colaboradores" action="" method="POST" data-form="" autocomplete="off" enctype="multipart/form-data">
				<div class="form-row">
					<div class="col-md-12 mb-3">
					    <input type="hidden" required readonly id="colaborador_id" name="colaborador_id" />
					    <input type="hidden" id="id-registro" name="id-registro" class="form-control"/>
						<div class="input-group mb-3">
							<input type="text" required readonly id="pro" name="pro" class="form-control"/>
							<div class="input-group-append">
								<span class="input-group-text"><div class="sb-nav-link-icon"></div><i class="fa fa-plus-square"></i></span>
							</div>
						</div>
					</div>
				</div>
				<div class="form-row" id="grupo_expediente">
					<div class="col-md-4 mb-3">
					  <label for="expedoente">Nombre <span class="priority">*<span/></label>
				      <input type="text" required name="nombre" id="nombre" maxlength="100" class="form-control"/>
					</div>
					<div class="col-md-4 mb-3">
					  <label for="edad">Apellido <span class="priority">*<span/></label>
					  <input type="text" required name="apellido" id="apellido" maxlength="100" class="form-control"/>
					</div>
					<div class="col-md-4 mb-3">
					  <label for="edad">Identidad <span class="priority">*<span/></label>
					  <input type="text" required name="identidad" id="identidad" maxlength="100" class="form-control" data-toggle="tooltip" data-placement="top" title="Este número de Identidad debe estar exactamente igual al que se registro en Odoo en la ficha del Colaborador"/>
					</div>
				</div>
				<div class="form-row">
          <div class="col-md-6 mb-3">
					    <label for="empresa">Empresa <span class="priority">*<span/></label>
						<div class="input-group mb-3">
							<select class="selectpicker" id="empresa" name="empresa" required data-live-search="true" title="Empresa" data-size="3">
							</select>
						</div>
				  </div>
          <div class="col-md-3 mb-3">
					    <label for="puesto">Puesto <span class="priority">*<span/></label>
						<div class="input-group mb-3">
							<select class="selectpicker" id="puesto" name="puesto" required data-live-search="true" title="Puesto" data-size="3">
							</select>
						</div>
				  </div>
          <div class="col-md-3 mb-3">
					    <label for="estatus">Estado <span class="priority">*<span/></label>
						<div class="input-group mb-3">
							<select class="selectpicker" id="estatus" name="estatus" required data-live-search="true" title="Estado" data-size="3">
							</select>
						</div>
				  </div>
				</div>
			</form>
        </div>
		<div class="modal-footer">
			<button class="btn btn-primary ml-2" type="submit" id="reg_colaboradores" form="formulario_colaboradores"><div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar</button>
			<button class="btn btn-warning ml-2" type="submit" id="edi_colaboradores" form="formulario_colaboradores"><div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Modificar</button>
		</div>
      </div>
    </div>
</div>

<div class="modal fade" id="registrar_puestos">
	<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Puestos</h4>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
        </div><div class="container"></div>
        <div class="modal-body">
			<form class="FormularioAjax" id="formulario_puestos" action="" method="POST" data-form="" autocomplete="off" enctype="multipart/form-data">
				<div class="form-row">
					<div class="col-md-12 mb-3">
					  <input type="hidden" required readonly id="colaborador_id" name="colaborador_id" />
					  <input type="hidden" id="id-registro" name="id-registro" class="form-control"/>
					</div>
				</div>
				<div class="form-row" id="grupo_expediente">
					<div class="col-md-12 mb-3">
				      <input type="text" required name="puestosn" id="puestosn" placeholder="Puestos" maxlength="100" class="form-control"/>
					</div>
				</div>
				<div class="form-row">
					<div class="col-md-12 mb-3">
					  <div class="registros" id="agrega-registros_puestos"></div>
					</div>
					<div class="col-md-12 mb-3">
						<nav aria-label="Page navigation example">
							<ul class="pagination" id="pagination_servicio_colaborador"></ul>
						</nav>
					</div>
				</div>
			</form>
        </div>
		<div class="modal-footer">
			<button class="btn btn-primary ml-2" type="submit" id="reg" form="formulario_puestos"><div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar</button>
		</div>
      </div>
    </div>
</div>

<div class="modal fade" id="registrar_servicios">
	<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Servicios</h4>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
        </div><div class="container"></div>
        <div class="modal-body">
			<form class="FormularioAjax" id="formulario_servicios" action="" method="POST" data-form="" autocomplete="off" enctype="multipart/form-data">
				<div class="form-row">
					<div class="col-md-12 mb-3">
					  <input type="hidden" required readonly id="colaborador_id" name="colaborador_id" />
					  <input type="hidden" id="id-registro" name="id-registro" class="form-control"/>
					</div>
				</div>
				<div class="form-row" id="grupo_expediente">
					<div class="col-md-12 mb-3">
				      <input type="text" required name="servicios" id="servicios" placeholder="Servicios" maxlength="100" class="form-control"/>
					</div>
				</div>
				<div class="form-row">
					<div class="col-md-12 mb-3">
					  <div class="registros" id="agrega-registros_servicio"></div>
					</div>
					<div class="col-md-12 mb-3">
						<nav aria-label="Page navigation example">
							<ul class="pagination" id="pagination_servicio"></ul>
						</nav>
					</div>
				</div>
			</form>
        </div>
		<div class="modal-footer">
			<button class="btn btn-primary ml-2" type="submit" id="reg" form="formulario_servicios"><div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar</button>
		</div>
      </div>
    </div>
</div>

<div class="modal fade" id="registrar_servicios_colaboradores">
	<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Registrar Servicio a Colaborador</h4>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
        </div><div class="container"></div>
        <div class="modal-body">
			<form class="FormularioAjax" id="formulario_servicios_colaboradores" action="" method="POST" data-form="" autocomplete="off" enctype="multipart/form-data">
				<div class="form-row">
					<div class="col-md-12 mb-3">
					  <input type="hidden" id="id-registro" name="id-registro" class="form-control"/>
					</div>
				</div>
				<div class="form-row">
          <div class="col-md-3 mb-3">
            <label for="puesto_id">Puesto <span class="priority">*<span/></label>
            <div class="input-group mb-3">
              <select class="selectpicker" id="puesto_id" name="puesto_id" required data-live-search="true" title="Puesto">
              </select>
            </div>
          </div>
          <div class="col-md-3 mb-3">
            <label for="colaborador_id">Colaborador <span class="priority">*<span/></label>
            <div class="input-group mb-3">
              <select class="selectpicker" id="colaborador_id" name="colaborador_id" required data-live-search="true" title="Colaborador">
              </select>
            </div>
          </div>
          <div class="col-md-3 mb-3">
            <label for="jornada_id">Jornada <span class="priority">*<span/></label>
            <div class="input-group mb-3">
              <select class="selectpicker" id="jornada_id" name="jornada_id" required data-live-search="true" title="Jornada">
              </select>
            </div>
          </div>
				</div>
				<div class="form-row" id="grupo_expediente">
					<div class="col-md-4 mb-3">
						<label for="nombre">Nuevos <span class="priority">*<span/></label>
						<input type="number" class="form-control" required id="cantidad_nuevos" name="cantidad_nuevos" autofocus data-toggle="tooltip" data-placement="top" title="Cantidad de Usuarios Nuevos que vera el Profesional" placeholder="Nuevos" maxlength="100"/>
					</div>
					<div class="col-md-4 mb-3">
					   <label for="nombre">Subsiguiente <span class="priority">*<span/></label>
					   <input type="number" class="form-control" required id="cantidad_subsiguientes" name="cantidad_subsiguientes" autofocus data-toggle="tooltip" data-placement="top" title="Cantidad de Usuarios Subsiguientes que vera el Profesional" placeholder="Subsiguientes" maxlength="100"/>
					</div>
				</div>
				<div class="form-row">
					<div class="col-md-12 mb-3">
					  <div class="registros" id="agrega-registros_servicio_colaborador"></div>
					</div>
					<div class="col-md-12 mb-3">
						<nav aria-label="Page navigation example">
							<ul class="pagination" id="pagination_puestos"></ul>
						</nav>
					</div>
				</div>
			</form>
        </div>
		<div class="modal-footer">
			<button class="btn btn-primary ml-2" type="submit" id="reg" form="formulario_servicios_colaboradores"><div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar</button>
			<button class="btn btn-danger ml-2" type="submit" id="clean_datos" form="formulario_servicios_colaboradores"><div class="sb-nav-link-icon"></div><i class="fas fa-broom fa-lg"></i> Limpiar</button>
		</div>
      </div>
    </div>
</div>
   <?php include("modals/modals.php");?>
<!--FIN MODAL-->

   <!--Fin Ventanas Modales-->
	<!--MENU-->
       <?php include("templates/menu.php"); ?>
    <!--FIN MENU-->

<br><br><br>
<div class="container-fluid">
	<ol class="breadcrumb mt-2 mb-4">
		<li class="breadcrumb-item"><a class="breadcrumb-link" href="<?php echo SERVERURL; ?>vistas/inicio.php">Dashboard</a></li>
		<li class="breadcrumb-item active" id="acciones_factura"><span id="label_acciones_factura"></span>Colaboradores</li>
	</ol>

    <div class="card mb-4">
      <div class="card-header">
        <i class="fas fa-search  mr-1"></i>
        Búsqueda
      </div>
      <div class="card-body">
        <form id="main_form" class="form-inline">
          <div class="form-group mr-1">
            <div class="input-group">
              <div class="input-group-append">
                <span class="input-group-text"><div class="sb-nav-link-icon"></div>Estado</span>
              </div>
              <select id="status" name="status" class="selectpicker" title="Estado" data-live-search="true">
              </select>
            </div>
          </div>
          <div class="form-group mr-1">
            <div class="input-group">
             <input type="text" placeholder="Buscar por: Código, Nombre o Puesto" data-toggle="tooltip" data-placement="top" title="Buscar por: Código, Nombre o Puesto" id="bs-regis" size="50" autofocus class="form-control"/>
            </div>
          </div>
          <div class="form-group mr-1">
            <button class="btn btn-primary ml-2" type="submit" id="nuevo-registro-colaboradores"><div class="sb-nav-link-icon"></div><i class="fas fa-user-plus fa-lg"></i> Colaboradores</button>
          </div>
          <div class="form-group mr-1">
            <button class="btn btn-info ml-2" type="submit" id="nuevo-registro-puestos"><div class="sb-nav-link-icon"></div><i class="fas fa-network-wired fa-lg"></i> Puestos</button>
          </div>
          <div class="form-group mr-1">
            <button class="btn btn-warning ml-2" type="submit" id="nuevo-registro-servicios"><div class="sb-nav-link-icon"></div><i class="fab fa-servicestack fa-lg"></i> Servicios</button>
          </div>
          <div class="form-group mr-1">
            <button class="btn btn-danger ml-2" type="submit" id="nuevo-registro-colaborador-servicios"><div class="sb-nav-link-icon"></div><i class="fas fa-people-carry fa-lg"></i> Jornada</button>
          </div>
          <div class="form-group mr-1">
            <button class="btn btn-success ml-2" type="submit" id="reporte_excel"><div class="sb-nav-link-icon"></div><i class="fas fa-download fa-lg"></i> Exportar</button>
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
        <div class="form-group">
          <div class="col-sm-12">
            <div class="registros overflow-auto" id="agrega-registros"></div>
          </div>
        </div>
        <nav aria-label="Page navigation example">
          <ul class="pagination justify-content-center" id="pagination"></ul>
        </nav>
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
		include "../js/myjava_colaboradores.php";
		include "../js/select.php";
		include "../js/functions.php";
		include "../js/myjava_cambiar_pass.php";
	?>

</body>
</html>
