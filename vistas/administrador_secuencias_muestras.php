<?php
session_start();
include "../php/funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

if( isset($_SESSION['colaborador_id']) == false ){
   header('Location: login.php');
}

$_SESSION['menu'] = "Administrador de Secuencia";

if(isset($_SESSION['colaborador_id'])){
 $colaborador_id = $_SESSION['colaborador_id'];
}else{
   $colaborador_id = "";
}

$type = $_SESSION['type'];

$nombre_host = gethostbyaddr($_SERVER['REMOTE_ADDR']);//HOSTNAME
$fecha = date("Y-m-d H:i:s");
$comentario = "Ingreso al Modulo de Administrador de Secuencia";

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
    <title>Administrador de Secuencias :: <?php echo $empresa; ?></title>
	<?php include("script_css.php"); ?>
</head>
<body>
   <!--Ventanas Modales-->
   <!-- Small modal -->
  <?php include("templates/modals.php"); ?>

<!--INICIO MODAL-->
<div class="modal fade" id="modalAdministradorSecuencias">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Administrador Secuencias</h4>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
        </div>
        <div class="modal-body">
			<form class="FormularioAjax" id="formularioAdministradorSecuencias" data-async data-target="#rating-modal" action="" method="POST" data-form="" autocomplete="off" enctype="multipart/form-data">
				<div class="form-row">
					<div class="col-md-12 mb-3">
					    <input type="hidden" id="tabla" name="tabla" class="form-control">
					    <input type="hidden" id="secuencias_id" name="secuencias_id" class="form-control">
						<div class="input-group mb-3">
							<input type="text" required readonly id="pro" name="pro" class="form-control"/>
							<div class="input-group-append">
								<span class="input-group-text"><div class="sb-nav-link-icon"></div><i class="fa fa-plus-square"></i></span>
							</div>
						</div>
					</div>
				</div>
				<div class="form-row">
          <div class="col-md-3 mb-3">
					    <label for="empresa">Empresa <span class="priority">*<span/></label>
						<div class="input-group mb-3">
							<select class="selectpicker" id="empresa" name="empresa" required data-live-search="true" title="Empresa">
							</select>
						</div>
					</div>
          <div class="col-md-3 mb-3">
					    <label for="tipo_muestra_id">Tipo Muestra <span class="priority">*<span/></label>
						<div class="input-group mb-3">
							<select class="selectpicker" id="tipo_muestra_id" name="tipo_muestra_id" required data-live-search="true" title="Tipo Muestra">
							</select>
						</div>
					</div>
          <div class="col-md-3 mb-3">
					  <label for="prefijo">Prefijo</label>
					  <div class="input-group mb-3">
						  <input type="text" name="prefijo" id="prefijo" class="form-control" placeholder="Prefijo" data-toggle="tooltip" data-placement="top" title="Este es el número incial de la facturación según el documento entregado el cual inicia por ejemplo: 000-001-01-">
							<div class="input-group-append">
								<span class="input-group-text"><div class="sb-nav-link-icon"></div><i class="fab fa-autoprefixer fa-lg"></i></span>
							</div>
					   </div>
					</div>
          <div class="col-md-3 mb-3">
					  <label for="sufijo">Sufijo</label>
					  <div class="input-group mb-3">
						  <input type="text" name="sufijo" id="sufijo" class="form-control" placeholder="Sufijo" data-toggle="tooltip" data-placement="top">
							<div class="input-group-append">
								<span class="input-group-text"><div class="sb-nav-link-icon"></div><i class="fab fa-autoprefixer fa-lg"></i></span>
							</div>
					   </div>
					</div>
				</div>
				<div class="form-row">
          <div class="col-md-3 mb-3">
					  <label for="relleno">Relleno</label>
					  <div class="input-group mb-3">
						  <input type="number" name="relleno" id="relleno" class="form-control" placeholder="Relleno" data-toggle="tooltip" data-placement="top">
							<div class="input-group-append">
								<span class="input-group-text"><div class="sb-nav-link-icon"></div><i class="fab fa-autoprefixer fa-lg"></i></span>
							</div>
					   </div>
					</div>
          <div class="col-md-3 mb-3">
					  <label for="incremento">Incremento</label>
					  <div class="input-group mb-3">
						  <input type="number" name="incremento" id="incremento" class="form-control" placeholder="Incremento" data-toggle="tooltip" data-placement="top">
							<div class="input-group-append">
								<span class="input-group-text"><div class="sb-nav-link-icon"></div><i class="fab fa-autoprefixer fa-lg"></i></span>
							</div>
					   </div>
					</div>
          <div class="col-md-3 mb-3">
					  <label for="siguiente">Siguiente</label>
					  <div class="input-group mb-3">
						  <input type="number" name="siguiente" id="siguiente" class="form-control" placeholder="Siguiente" data-toggle="tooltip" data-placement="top">
							<div class="input-group-append">
								<span class="input-group-text"><div class="sb-nav-link-icon"></div><i class="fas fa-list-ol fa-lg"></i></span>
							</div>
					   </div>
					</div>
          <div class="col-md-3 mb-3">
					    <label for="estado">Estado <span class="priority">*<span/></label>
						<div class="input-group mb-3">
							<select class="selectpicker" id="estado" name="estado" required data-live-search="true" title="Estado">
							</select>
						</div>
				</div>
				</div>
				<div class="form-row">
          <div class="col-md-12 mb-3">
					  <label for="comentario">Comentario</label>
					  <div class="input-group mb-3">
						   <input type="text" name="comentario" id="comentario" class="form-control" placeholder="Comentario" maxlength="150" oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);">
							<div class="input-group-append">
								<span class="input-group-text"><div class="sb-nav-link-icon"></div><i class="fas fa-comments fa-lg"></i></span>
							</div>
					   </div>
					</div>
				</div>
				<div class="form-row">
					<div class="col-md-12 mb-3">
					  <label style="color: #077A2F;" align="center"><b>Leyendas para Prefijo y Sufijo</b></label>
					</div>
				</div>
				<div class="form-row">
					<div class="col-md-3 mb-3">
					  <label data-toggle="tooltip" data-placement="top" title="Permite obtener el año actual. Esta leyenda se escribe tal cual esta en el prefijo o sufijo de ser necearias: ejemplo: @año_actual"><b>Año actual:</b> @año_actual</label>
					</div>
					<div class="col-md-3 mb-3">
					  <label data-toggle="tooltip" data-placement="top" title="Permite obtener el mes actual. Esta leyenda se escribe tal cual esta en el prefijo o sufijo de ser necearias: ejemplo: @mes_actual"><b>Mes actual:</b> @mes_actual</label>
					</div>
					<div class="col-md-3 mb-3">
					  <label data-toggle="tooltip" data-placement="top" title="Permite obtener el día actual. Esta leyenda se escribe tal cual esta en el prefijo o sufijo de ser necearias: ejemplo: @dia_actual"><b>Dia actual:</b> @dia_actual</label>
					</div>
					<div class="col-md-3 mb-3">
					  <label data-toggle="tooltip" data-placement="top" title="Permite obtener el día de la semana actual. Esta leyenda se escribe tal cual esta en el prefijo o sufijo de ser necearias: ejemplo: @dia_semana"><b>Dia de la semana:</b> @dia_semana</label>
					</div>
				</div>
			</form>
        </div>
		<div class="modal-footer">
			<button class="btn btn-primary ml-2" form="formularioAdministradorSecuencias" type="submit" id="reg"><div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar</button>
			<button class="btn btn-warning ml-2" form="formularioAdministradorSecuencias" type="submit" id="edi"><div class="sb-nav-link-icon"></div><i class="fas fa-edit fa-lg"></i> Modificar</button>
			<button class="btn btn-danger ml-2" form="formularioAdministradorSecuencias" type="submit" id="delete"><div class="sb-nav-link-icon"></div><i class="fas fa-trash fa-lg"></i> Eliminar</button>
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
		<li class="breadcrumb-item"><a class="breadcrumb-link" href="inicio.php">Dashboard</a></li>
		<li class="breadcrumb-item active" id="acciones_factura"><span id="label_acciones_factura"></span>Administrador de Secuencias</li>
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
                <span class="input-group-text"><div class="sb-nav-link-icon"></div>Empresa</span>
              </div>
              <select id="empresa" name="empresa" class="selectpicker" title="Empresa" data-live-search="true">
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
      				<input type="date" required="required" id="fecha_b" name="fecha_b" style="width:160px;" value="<?php echo date ("Y-m-d");?>" class="form-control"/>
      			</div>
          </div>
          <div class="form-group mr-1">
            <div class="input-group">
      				<div class="input-group-append">
      					<span class="input-group-text"><div class="sb-nav-link-icon"></div>Fin</span>
      				</div>
      				<input type="date" required="required" id="fecha_f" name="fecha_f" style="width:160px;" value="<?php echo date ("Y-m-d");?>" class="form-control"/>
      			</div>
          </div>
          <div class="form-group mr-1">
            <div class="input-group">
              <input type="text" placeholder="Buscar por: Empresa" title="Buscar por: Empresa" id="bs_regis" autofocus class="form-control" size="40"/>
            </div>
          </div>
          <div class="form-group mr-1">
            <button class="btn btn-primary ml-2" type="submit" id="nuevo_registro"><div class="sb-nav-link-icon"></div><i class="fas fa-plus-circle fa-lg"></i> Crear</button>
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
		include "../js/myjava_administrador_secuencias_muestras.php";
		include "../js/select.php";
		include "../js/functions.php";
		include "../js/myjava_cambiar_pass.php";
	?>

</body>
</html>
