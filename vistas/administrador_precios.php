<?php
session_start();
include "../php/funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

if( isset($_SESSION['colaborador_id']) == false ){
   header('Location: login.php');
}

$_SESSION['menu'] = "Administrador de Precios";

if(isset($_SESSION['colaborador_id'])){
 $colaborador_id = $_SESSION['colaborador_id'];
}else{
   $colaborador_id = "";
}

$type = $_SESSION['type'];

$nombre_host = gethostbyaddr($_SERVER['REMOTE_ADDR']);//HOSTNAME
$fecha = date("Y-m-d H:i:s");
$comentario = "Ingreso al Modulo de Administrador de Precios";

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
    <title>Secuencia de Precios :: <?php echo $empresa; ?></title>
	<?php include("script_css.php"); ?>
</head>
<body>
   <!--Ventanas Modales-->
   <!-- Small modal -->
  <?php include("templates/modals.php"); ?>

<!--INICIO MODAL-->
<div class="modal fade" id="modalAdministradorPrecios">
	<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Administrador de Precios</h4>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
        </div>
        <div class="modal-body">
			<form class="FormularioAjax" id="formularioAdministradorPrecios" data-async data-target="#rating-modal" action="" method="POST" data-form="" autocomplete="off" enctype="multipart/form-data">
				<div class="form-row">
					<div class="col-md-12 mb-3">
					    <input type="hidden" id="hospitales_id_consulta" name="hospitales_id_consulta" class="form-control">
					    <input type="hidden" id="administrador_precios_id" name="administrador_precios_id" class="form-control">
						<div class="input-group mb-3">
							<input type="text" required readonly id="pro" name="pro" class="form-control"/>
							<div class="input-group-append">
								<span class="input-group-text"><div class="sb-nav-link-icon"></div><i class="fa fa-plus-square"></i></span>
							</div>
						</div>
					</div>
				</div>
				<div class="form-row">
				  <div class="col-md-8 mb-3">
						<label for="empresa">Hospital / Clínica <span class="priority">*<span/></label>
						<div class="input-group mb-3">
							<select class="selectpicker" id="hospitales_id" name="hospitales_id" required data-live-search="true" title="Hospital / Clínica" data-size="5">
							</select>
						</div>
					</div>
					<div class="col-md-4 mb-3">
					  <label>Precio <span class="priority">*<span/></label>
					  <div class="input-group mb-3">
							<select class="selectpicker" id="precio" name="precio" required data-live-search="true" title="Categoría" data-size="5">
							</select>
					   </div>
					</div>
				</div>
				
				<div class="form-row">
					<br/>
				</div>	

				<div class="form-row">
					<br/>
				</div>	

				<div class="form-row">
					<br/>
				</div>	

				<div class="form-row">
					<br/>
				</div>					
			</form>
        </div>
		<div class="modal-footer">
			<button class="btn btn-primary ml-2" form="formularioAdministradorPrecios" type="submit" id="reg_precios"><div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar</button>
			<button class="btn btn-warning ml-2" form="formularioAdministradorPrecios" type="submit" id="edi_precios"><div class="sb-nav-link-icon"></div><i class="fas fa-edit fa-lg"></i> Modificar</button>
			<button class="btn btn-danger ml-2" form="formularioAdministradorPrecios" type="submit" id="delete_precios"><div class="sb-nav-link-icon"></div><i class="fas fa-trash fa-lg"></i> Eliminar</button>
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
		<li class="breadcrumb-item active" id="acciones_factura"><span id="label_acciones_factura"></span>Administrador de Precios</li>
	</ol>

  <div class="card mb-4">
    <div class="card-header">
      <i class="fab fa-sellsy mr-1"></i>
      Resultado
    </div>
    <div class="card-body">
      <div class="table-responsive">
    		<form id="formPrincipal">
    			<div class="col-md-12 mb-3">
    				<table id="dataTableAdministadorPrecios" class="table table-striped table-condensed table-hover" style="width:100%">
    					<thead>
    						<tr>
    							<th>Cliente</th>
    							<th>Precio</th>
    							<th>Editar</th>
    							<th>Eliminar</th>
    						</tr>
    					</thead>
    				</table>
    			</div>
    		<form>
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
		include "../js/myjava_administrador_precios.php";
		include "../js/select.php";
		include "../js/functions.php";
		include "../js/myjava_cambiar_pass.php";
	?>

</body>
</html>
