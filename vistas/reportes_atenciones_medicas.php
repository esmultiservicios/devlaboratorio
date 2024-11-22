<?php
session_start();
include "../php/funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

if( isset($_SESSION['colaborador_id']) == false ){
   header('Location: login.php');
}

$_SESSION['menu'] = "Reporte de Atenciones Medicas";

if(isset($_SESSION['colaborador_id'])){
 $colaborador_id = $_SESSION['colaborador_id'];
}else{
   $colaborador_id = "";
}

$type = $_SESSION['type'];

$nombre_host = gethostbyaddr($_SERVER['REMOTE_ADDR']);//HOSTNAME
$fecha = date("Y-m-d H:i:s");
$comentario = "Ingreso al Modulo de Reporte de Atenciones Medicas";

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
    <title>Reporte de Atenciones Medicas :: <?php echo $empresa; ?></title>
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
		<li class="breadcrumb-item active" id="acciones_factura"><span id="label_acciones_factura"></span>Reporte Atenciones Medicas</li>
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
            <select id="colaborador" name="colaborador" class="selectpicker" title="Profesional" data-live-search="true">
            </select>
          </div>
        </div>
        <div class="form-group mr-1">
          <div class="input-group">
    				<div class="input-group-append">
    					<span class="input-group-text"><div class="sb-nav-link-icon"></div>Inicio</span>
    				</div>
    				<input type="date" required="required" id="fecha_i" name="fecha_i" data-toggle="tooltip" data-placement="top" title="Fecha Inicial" value="<?php echo date ("Y-m-d");?>" title="Fecha Inicial" class="form-control" />
    			</div>
        </div>
        <div class="form-group mr-1">
          <div class="input-group">
    				<div class="input-group-append">
    					<span class="input-group-text"><div class="sb-nav-link-icon"></div>Fin</span>
    				</div>
    				<input type="date" required="required" id="fecha_f" name="fecha_f" data-toggle="tooltip" data-placement="top" title="Fecha Final" value="<?php echo date ("Y-m-d");?>" title="Fecha Final" class="form-control"/>
    			</div>
        </div>
        <div class="form-group mr-1">
          <div class="input-group">
		         <input type="text" placeholder="Buscar por: Expediente, Nombre o Identidad" data-toggle="tooltip" data-placement="top" title="Buscar por: Expediente, Nombre, Apellido o Identidad" id="bs_regis" name="bs_regis" autofocus class="form-control" size="45" autofocus />
    			</div>
        </div>
        <div class="form-group">
          <button class="btn btn-success ml-2" type="submit" id="reporte_excel"><div class="sb-nav-link-icon"></div><i class="fas fa-download fa-lg"></i> General</button>
        </div>
        <div class="form-group">
            <button class="btn btn-success ml-2" type="submit" id="reporte_diario"><div class="sb-nav-link-icon"></div><i class="fas fa-download fa-lg"></i> Diario</button>
        </div>
        <div class="form-group">
            <button class="btn btn-danger ml-2" type="submit" id="limpiar"><div class="sb-nav-link-icon"></div><i class="fas fa-search fa-lg"></i> Limpiar</button>
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
		include "../js/myjava_reportes_atenciones_medicas.php";
		include "../js/select.php";
		include "../js/functions.php";
		include "../js/myjava_cambiar_pass.php";
	?>

</body>
</html>
