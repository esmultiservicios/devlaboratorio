<?php
session_start();
include "../php/funtions.php";
//CONEXION A DB
$mysqli = connect_mysqli();

if( isset($_SESSION['colaborador_id']) == false ){
   header('Location: login.php');
}

$_SESSION['menu'] = "Citas";

if(isset($_SESSION['colaborador_id'])){
 $colaborador_id = $_SESSION['colaborador_id'];
}else{
   $colaborador_id = "";
}

$type = $_SESSION['type'];

$nombre_host = gethostbyaddr($_SERVER['REMOTE_ADDR']);//HOSTNAME
$fecha = date("Y-m-d H:i:s");
$comentario = "Ingreso al Menu Citas";

//OBTENER CORRELATIVO
$query = "SELECT MAX(acceso_id) AS max, COUNT(acceso_id) AS count FROM historial_acceso";

$result = mysqli_query($mysqli,$query) or die(mysqli_error());

$correlativo2=mysqli_fetch_array($result);

$numero = $correlativo2['max'];
$cantidad = $correlativo2['count'];

if ( $cantidad == 0 )
  $numero = 1;
else
  $numero = $numero + 1;

if($colaborador_id != "" || $colaborador_id != null){
   historial_acceso($comentario, $nombre_host, $colaborador_id);
}

$fecha = date("Y-m-d");
$mes=nombremes(date("m", strtotime($fecha)));
$año=date("Y", strtotime($fecha));

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

<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Citas :: <?php echo $empresa; ?></title>
	<link href="<?php echo SERVERURL; ?>/css/estilo-paginacion.css" rel="stylesheet">
    <link href="<?php echo SERVERURL; ?>bootstrap/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous"/>
    <link href="<?php echo SERVERURL; ?>bootstrap/css/bootstrap-select.min.css" rel="stylesheet" crossorigin="anonymous"/>
    <link href="<?php echo SERVERURL; ?>bootstrap/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous"/>
    <link href="<?php echo SERVERURL; ?>fontawesome/css/all.css" rel="stylesheet">
    <link href="<?php echo SERVERURL; ?>css/style.css" rel="stylesheet"/>
    <link rel="shortcut icon" href="<?php echo SERVERURL; ?>img/logo_icono.png">
    <link href="<?php echo SERVERURL; ?>sweetalert/sweetalert.css" rel="stylesheet" crossorigin="anonymous"/>

	<!-- FullCalendar -->
	<link href='<?php echo SERVERURL; ?>css/fullcalendar.css' rel='stylesheet' />
</head>
<body>
<?php include("templates/menu.php"); ?>
<?php include("templates/modals.php"); ?>
<?php include("modals/modals_citas.php");?>
<?php include("modals/modals.php");?>
<!-- Page Content -->
<div class="container-fluid">
<br><br><br>
		<ol class="breadcrumb mt-2 mb-4">
			<li class="breadcrumb-item"><a class="breadcrumb-link" href="inicio.php">Dashboard</a></li>
			<li class="breadcrumb-item active" id="acciones_factura"><span id="label_acciones_factura"></span>Citas</li>
		</ol>

    <div class="card mb-4">
      <div class="card-header">
        <i class="fas fa-search  mr-1"></i>
        Búsqueda
      </div>
      <div class="card-body">
        <form id="botones_citas" class="form-inline">
          <div class="form-group mr-1">
            <div class="input-group">
              <div class="input-group-append">
                <span class="input-group-text"><div class="sb-nav-link-icon"></div>Tipo Muestra</span>
              </div>
              <select id="tipo_muestra" name="tipo_muestra" class="selectpicker" title="Tipo Muestra" data-live-search="true">
              </select>
            </div>
          </div>
          <div class="form-group mr-1">
            <div class="input-group">
              <input type="text" placeholder="Buscar por: Nombre, Apellido, Identidad o Teléfono Principal" data-toggle="tooltip" data-placement="top" title="Buscar por: Expediente, Nombre, Apellido, Identidad o Teléfono Principal" id="bs_regis" autofocus class="form-control" size="70" autofocus />
            </div>
          </div>
          <div class="form-group mr-1">
            <button class="btn btn-primary ml-2" type="submit" id="refresh"><div class="sb-nav-link-icon"></div><i class="refresh fa-lg"></i> Clientes</button>
          </div>
        </form>
      </div>
      <div class="card-footer small text-muted">

      </div>
    </div>

		<div class="col-sm-12">
			<div id="calendar" class="col-centered" style="position: absolute; z-index:0;"></div>
		</div>
		<?php include("templates/footer.php"); ?>
</div>
<!-- /.container -->

<!--Librerias Java Script-->
<script src="<?php echo SERVERURL; ?>js/query/jquery.js" crossorigin="anonymous"></script>
<script src="<?php echo SERVERURL; ?>bootstrap/js/popper.min.js" crossorigin="anonymous"></script>
<script src="<?php echo SERVERURL; ?>bootstrap/js/bootstrap.min.js" crossorigin="anonymous"></script>
<script src="<?php echo SERVERURL; ?>js/main.js" crossorigin="anonymous"></script>
<script src="<?php echo SERVERURL; ?>sweetalert/sweetalert.min.js" crossorigin="anonymous"></script>

<script src="<?php echo SERVERURL; ?>bootstrap/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="<?php echo SERVERURL; ?>bootstrap/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
<script src="<?php echo SERVERURL; ?>bootstrap/js/dataTables.buttons.min.js" crossorigin="anonymous"></script>
<script src="<?php echo SERVERURL; ?>bootstrap/js/bootstrap-select.min.js" crossorigin="anonymous"></script>
<script src="<?php echo SERVERURL; ?>bootstrap/js/jszip.min.js" crossorigin="anonymous"></script>
<script src="<?php echo SERVERURL; ?>bootstrap/js/pdfmake.min.js" crossorigin="anonymous"></script>
<script src="<?php echo SERVERURL; ?>bootstrap/js/vfs_fonts.js" crossorigin="anonymous"></script>
<script src="<?php echo SERVERURL; ?>bootstrap/js/buttons.html5.min.js" crossorigin="anonymous"></script>
<script src="<?php echo SERVERURL; ?>bootstrap/js/buttons.print.min.js" crossorigin="anonymous"></script>

<script src='<?php echo SERVERURL; ?>js/query/moment.min.js'></script>
<script src='<?php echo SERVERURL; ?>/js/query/fullcalendar.min.js'></script>
<script src="<?php echo SERVERURL; ?>js/query/menu-despelgable.js"></script>
<script src="<?php echo SERVERURL; ?>js/query/arriba.js"></script>

<?php
	include "../js/main.php";
	include "../js/myjava_citas.php";
	include "../js/select.php";
	include "../js/functions.php";
	include "../js/myjava_cambiar_pass.php";
?>

<span class="ir-arriba" data-toggle="tooltip" data-placement="top" title="Ir Arriba"><i class="fas fa-chevron-up fa-xs"></i></span>

</body>
</html>
