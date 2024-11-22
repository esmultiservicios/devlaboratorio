<?php
session_start();
include "../php/funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

if( isset($_SESSION['colaborador_id']) == false ){
   header('Location: login.php');
}

$_SESSION['menu'] = "Atenciones Medicas";

if(isset($_SESSION['colaborador_id'])){
 $colaborador_id = $_SESSION['colaborador_id'];
}else{
   $colaborador_id = "";
}

$type = $_SESSION['type'];

$nombre_host = gethostbyaddr($_SERVER['REMOTE_ADDR']);//HOSTNAME
$fecha = date("Y-m-d H:i:s");
$comentario = "Ingreso al Modulo de Atenciones Medicas";

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
    <title>Atenciones Medicas :: <?php echo $empresa; ?></title>
	<?php include("script_css.php"); ?>
	<style>
		.main-section{
			margin:0 auto;
			padding: 20px;
			margin-top: 10px;
			background-color: #fff;
			box-shadow: 0px 0px 20px #c1c1c1;
		}
		.fileinput-remove,
		.fileinput-upload{
			/*display: none;*/
		}
	</style>
</head>
<body>
   <!--Ventanas Modales-->
   <!-- Small modal -->
  <?php include("templates/modals.php"); ?>

<!--INICIO MODAL-->
<div class="modal fade" id="modal_registro_atenciones">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Atenciones</h4>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
        </div><div class="container"></div>
        <div class="modal-body">
			<form class="FormularioAjax" id="formulario_atenciones" action="" method="POST" data-form="" autocomplete="off" enctype="multipart/form-data">
				<input type="hidden" id="pacientes_id" name="pacientes_id" class="form-control" required="required">
				<input type="hidden" id="muestras_id" name="muestras_id" class="form-control" required="required">
				<input type="hidden" id="colaborador_id" name="colaborador_id" class="form-control" required="required">
				<div class="form-row">
					<div class="col-md-2 mb-3">
						<label for="fecha">Número <span class="priority">*<span/></label>
						<input type="text" required id="bioxia_numero" name="bioxia_numero" readonly class="form-control"/>
					</div>
					<div class="col-md-4 mb-3">
						<label for="cliente_admision">Clientes</label>
						<div class="input-group mb-3">
						<select class="selectpicker" id="paciente_consulta" name="paciente_consulta" data-live-search="true" title="Clientes" data-size="10">
						</select>
						</div>
					</div>
					<div class="col-md-2 mb-3">
					  <label for="fecha">Fecha <span class="priority">*<span/></label>
					  <input type="date" required id="fecha" name="fecha" value="<?php echo date ("Y-m-d");?>" class="form-control"/>
					</div>
          <div class="col-md-2 mb-3">
            <label for="cliente_admision">Servicio</label>
            <div class="input-group mb-3">
              <select class="selectpicker" id="servicio_id" name="servicio_id" data-live-search="true" title="Servicio" data-size="10">
              </select>
            </div>
          </div>
					<div class="col-md-12 mb-3">
						<label>Plantilla Diagnóstico</label>
						<div class="input-group mb-3">
              <select class="selectpicker" id="plantilla_disgnostico" name="plantilla_disgnostico" data-live-search="true" title="Plantilla para Diagnostico" data-size="10">
              </select>
						</div>
					</div>
					<div class="col-md-12 mb-3">
						<div class="card">
						  <div class="card-header text-white bg-info mb-3" align="center">
							Diagnósticos
						  </div>
						  <div class="card-body">
							<div class="input-group">
							  <textarea id="antecedentes" name="antecedentes" placeholder="Diagnósticos" class="form-control" maxlength="3200" rows="10"></textarea>
							  <div class="input-group-prepend">
								<span class="input-group-text">
									<i class="btn btn-outline-success fas fa-microphone-alt" id="search_antecedentes_start"></i>
									<i class="btn btn-outline-success fas fa-microphone-slash" id="search_antecedentes_stop"></i>
								</span>
							  </div>
							</div>
							<p id="charNum_antecedentes">3200 Caracteres</p>
						  </div>
						</div>
					</div>
					<div class="col-md-12 mb-3">
						<label>Plantilla Factores Pronóstico</label>
						<div class="input-group mb-3">
              <select class="selectpicker" id="plantilla_factores_pronostico" name="plantilla_factores_pronostico" data-live-search="true" title="Plantilla para Factores Pronóstico" data-size="10">
              </select>
						</div>
					</div>
					<div class="col-md-12 mb-3">
						<div class="card">
						  <div class="card-header text-white bg-info mb-3" align="center">
							Factores Pronóstico
						  </div>
						  <div class="card-body">
							<div class="input-group">
							  <textarea id="historia_clinica" name="historia_clinica" placeholder="Factores Diagnósticos" class="form-control" maxlength="3200" rows="10"></textarea>
							  <div class="input-group-prepend">
								<span class="input-group-text">
									<i class="btn btn-outline-success fas fa-microphone-alt" id="search_historia_clinica_start"></i>
									<i class="btn btn-outline-success fas fa-microphone-slash" id="search_historia_clinica_stop"></i>
							  </div>
							</div>
							<p id="charNum_historia">3200 Caracteres</p>
						  </div>
						</div>
					</div>
					<div class="col-md-12 mb-3">
						<label>Plantilla Descripción Macroscópica</label>
						<div class="input-group mb-3">
              <select class="selectpicker" id="plantilla_descripcion_macroscopica" name="plantilla_descripcion_macroscopica" data-live-search="true" title="Plantilla para Diagnostico" data-size="10">
              </select>
						</div>
					</div>
					<div class="col-md-12 mb-3">
						<div class="card">
						  <div class="card-header text-white bg-info mb-3" align="center">
							Descripción Macroscópica
						  </div>
						  <div class="card-body">
							<div class="input-group">
							  <textarea id="exame_fisico" name="exame_fisico" placeholder="Descripción Macroscopica" class="form-control" maxlength="3200" rows="10"></textarea>
							  <div class="input-group-prepend">
								<span class="input-group-text">
									<i class="btn btn-outline-success fas fa-microphone-alt" id="search_exame_fisico_start"></i>
									<i class="btn btn-outline-success fas fa-microphone-slash" id="search_exame_fisico_stop"></i>
							  </div>
							</div>
							<p id="charNum_examen">3200 Caracteres</p>
						  </div>
						</div>
					</div>
					<div class="col-md-12 mb-3">
						<label>Plantilla Descripción Microscópica</label>
						<div class="input-group mb-3">
              <select class="selectpicker" id="plantilla_descripcion_microscopica" name="plantilla_descripcion_microscopica" data-live-search="true" title="Plantilla para Diagnostico" data-size="10">
              </select>
						</div>
					</div>
					<div class="col-md-12 mb-3">
						<div class="card">
						  <div class="card-header text-white bg-info mb-3" align="center">
							Descripción Microscópica
						  </div>
						  <div class="card-body">
							<div class="input-group">
							  <textarea id="diagnostico" name="diagnostico" placeholder="Descripción Microscopica" class="form-control" maxlength="3200" rows="10"></textarea>
							  <div class="input-group-prepend">
								<span class="input-group-text">
									<i class="btn btn-outline-success fas fa-microphone-alt" id="search_diagnostico_start"></i>
									<i class="btn btn-outline-success fas fa-microphone-slash" id="search_diagnostico_stop"></i>
							  </div>
							</div>
							<p id="charNum_diagnostico">3200 Caracteres</p>
						  </div>
						</div>
					</div>
					<div class="col-md-12 mb-3">
						<label>Plantilla Comentario</label>
						<div class="input-group mb-3">
              <select class="selectpicker" id="plantilla_comentario" name="plantilla_comentario" data-live-search="true" title="Plantilla para Diagnostico" data-size="10">
              </select>
						</div>
					</div>
					<div class="col-md-12 mb-3">
						<div class="card">
						  <div class="card-header text-white bg-info mb-3" align="center">
							Comentario
						  </div>
						  <div class="card-body">
							<div class="input-group">
							  <textarea id="seguimiento" name="seguimiento" placeholder="Comentario" class="form-control" maxlength="3200" rows="10"></textarea>
							  <div class="input-group-prepend">
								<span class="input-group-text">
									<i class="btn btn-outline-success fas fa-microphone-alt" id="search_seguimiento_start"></i>
									<i class="btn btn-outline-success fas fa-microphone-slash" id="search_seguimiento_stop"></i>
							  </div>
							</div>
							<p id="charNum_seguimiento">3200 Caracteres</p>
						  </div>
						</div>
					</div>

					<div class="col-md-12 mb-3">
						<div class="col-lg-12 col-sm-12 col-12 main-section">
							<h1 class="text-center text-danger">Imágenes</h1><br>
							<div class="form-group">
								<div class="file-loading">
									<input type="file" id="file-1" name="files[]" multiple class="custom-file-label">
								</div>
							</div>
						</div>
                	</div>
					<div class="col-md-12 mb-3">
						<div class="form-check form-check-inline" data-toggle="tooltip" data-placement="top" title="Marque esta casilla únicamente cuando no necesita realizar ediciones a este registro">
						  <input class="form-check-input" type="checkbox" value="1" id="editar_atencion" name="editar_atencion">
						  <label class="form-check-label" for="defaultCheck1">¿Finalizar Registro?</label>
						</div>
					</div>
				</div>
			</form>
        </div>
		<div class="modal-footer">
			<button class="btn btn-primary ml-2" type="submit" id="reg_atencion" form="formulario_atenciones"><div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar</button>
			<button class="btn btn-primary ml-2" type="submit" id="edi_atencion" form="formulario_atenciones"><div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar</button>
		</div>
      </div>
    </div>
</div>

<!--MODAL BUSCAR ATENCIONES-->
<div class="modal fade" id="buscar_atencion">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Búsqueda de Atenciones</h4>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
        </div>
        <div class="modal-body">
			<form class="FormularioAjax" id="formulario_buscarAtencion" data-async data-target="#rating-modal" action="" method="POST" data-form="" autocomplete="off" enctype="multipart/form-data">
				<div class="form-row">
					<div class="col-md-12 mb-3">
					  <input type="hidden" id="atencion_id" name="atencion_id" class="form-control" required="required">
					  <input type="hidden" id="pacientes_id" name="pacientes_id" class="form-control" required="required">
					</div>
				</div>
				<div class="form-row" id="grupo_expediente">
					<div class="col-md-12 mb-3">
					  <input type="text" name="busqueda" id="busqueda" placeholder="Buscar por: Nombre, Apellido o Identidad" data-toggle="tooltip" data-placement="top" title="Búsqueda de Atenciones por: Nombre, Apellido o Identidad" class="form-control">
					</div>
				</div>
				<div class="form-row">
					<div class="col-md-12 mb-3">
					   <div class="registros overflow-auto" id="agrega_registros_busqueda"></div>
					</div>
				</div>
				<div class="form-row">
					<div class="col-md-12 mb-3">
						<nav aria-label="Page navigation example">
							<ul class="pagination justify-content-center" id="pagination_busqueda"></ul>
						</nav>
					</div>
				</div>
				<div class="form-row">
					<div class="col-md-12 mb-3">
					   <div class="registros overflow-auto" id="agrega_registros_busqueda_"></div>
					</div>
				</div>
				<div class="form-row">
					<div class="col-md-12 mb-3">
						<nav aria-label="Page navigation example">
							<ul class="pagination justify-content-center" id="pagination_busqueda_"></ul>
						</nav>
					</div>
				</div>
			</form>
        </div>
		<div class="modal-footer">

		</div>
      </div>
    </div>
</div>
<!-- FIN MODAL BUSCAR ATENCIONES

<!--INICIO MODAL TRANSITO-->
<div class="modal fade" id="registro_transito_eviada">
	<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Transito Enviada</h4>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
        </div>
        <div class="modal-body">
			<form class="FormularioAjax" id="formulario_transito_enviada" data-async data-target="#rating-modal" action="" method="POST" data-form="" autocomplete="off" enctype="multipart/form-data">
				<div class="form-row">
					<div class="col-md-12 mb-3">
					    <input type="hidden" id="pacientes_id" name="pacientes_id" class="form-control" required="required">
					    <input type="hidden" id="colaborador_id" name="colaborador_id" class="form-control" required="required">
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
            <label for="cliente_admision">Cliente</label>
            <div class="input-group mb-3">
              <select class="selectpicker" id="paciente_te" name="paciente_te" data-live-search="true" title="Cliente" data-size="10">
              </select>
            </div>
          </div>
					<div class="col-md-3 mb-3">
					  <label for="edad">Fecha <span class="priority">*<span/></label>
					  <input type="date" required id="fecha" name="fecha" value="<?php echo date ("Y-m-d");?>" class="form-control"/>
					</div>
          <div class="col-md-6 mb-3">
					  <label for="nombre">Identidad</label>
					  <input type="text" name="identidad" id="identidad" placeholder="Identidad" readonly class="form-control"/>
					</div>
				</div>
				<div class="form-row">
          <div class="col-md-3 mb-3">
            <label for="cliente_admision">Enviada a</label>
            <div class="input-group mb-3">
              <select class="selectpicker" id="enviada" name="enviada" data-live-search="true" title="Enviada a" data-size="10">
              </select>
            </div>
          </div>
				</div>
				<div class="form-row">
					<div class="col-md-12 mb-3">
					  <label for="direccion">Motivo <span class="priority">*<span/></label>
					  <textarea id="motivo" name="motivo" required placeholder="Motivo de la Referencia" class="form-control" maxlength="255" rows="3"></textarea>
					  <p id="charNumMotivoTE">255 Caracteres</p>
					</div>
				</div>


			</form>
        </div>
		<div class="modal-footer">
			<button class="btn btn-primary ml-2" form="formulario_transito_enviada" type="submit" id="reg_transitoe"><div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar</button>
		</div>
      </div>
    </div>
</div>

<div class="modal fade" id="registro_transito_recibida">
	<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Transito Recibida</h4>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
        </div>
        <div class="modal-body">
			<form class="FormularioAjax" id="formulario_transito_recibida" data-async data-target="#rating-modal" action="" method="POST" data-form="" autocomplete="off" enctype="multipart/form-data">
				<div class="form-row">
					<div class="col-md-12 mb-3">
					    <input type="hidden" id="pacientes_id" name="pacientes_id" class="form-control" required="required">
					    <input type="hidden" id="colaborador_id" name="colaborador_id" class="form-control" required="required">
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
            <label for="cliente_admision">Cliente</label>
            <div class="input-group mb-3">
              <select class="selectpicker" id="paciente_tr" name="paciente_tr" data-live-search="true" title="Cliente" data-size="10">
              </select>
            </div>
          </div>
					<div class="col-md-3 mb-3">
					  <label for="edad">Fecha <span class="priority">*<span/></label>
					  <input type="date" required id="fecha" name="fecha" value="<?php echo date ("Y-m-d");?>" class="form-control"/>
					</div>
          <div class="col-md-6 mb-3">
					  <label for="nombre">Identidad</label>
					  <input type="text" name="identidad" id="identidad" placeholder="Identidad" readonly class="form-control"/>
					</div>
				</div>
				<div class="form-row">
          <div class="col-md-3 mb-3">
            <label for="cliente_admision">Recibida de</label>
            <div class="input-group mb-3">
              <select class="selectpicker" id="recibida" name="recibida" data-live-search="true" title="Recibida de" data-size="10">
              </select>
            </div>
          </div>
				</div>
				<div class="form-row">
					<div class="col-md-12 mb-3">
					  <label for="direccion">Motivo <span class="priority">*<span/></label>
					  <textarea id="motivo" name="motivo" required placeholder="Motivo de la Referencia" class="form-control" maxlength="255" rows="3"></textarea>
					  <p id="charNumMotivoTE">255 Caracteres</p>
					</div>
				</div>


			</form>
        </div>
		<div class="modal-footer">
			<button class="btn btn-primary ml-2" form="formulario_transito_recibida" type="submit" id="reg_transitor"><div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar</button>
		</div>
      </div>
    </div>
</div>
   <?php include("modals/modals.php");?>

   <!--Fin Ventanas Modales-->
	<!--MENU-->
       <?php include("templates/menu.php"); ?>
    <!--FIN MENU-->

<br><br><br>
<div class="container-fluid">
	<ol class="breadcrumb mt-2 mb-4">
		<li class="breadcrumb-item" id="acciones_atras"><a id="ancla_volver" class="breadcrumb-link" href="#">Atenciones Medicas</a></li>
		<li class="breadcrumb-item active" id="acciones_factura"><span id="label_acciones_factura"></span></li>
	</ol>

	<div id="main_facturacion">
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
      				<input type="date" required="required" id="fecha_b" name="fecha_b" style="width:160px;" value="<?php
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
      					<span class="input-group-text"><div class="sb-nav-link-icon"></div>Fin</span>
      				</div>
      				<input type="date" required="required" id="fecha_f" name="fecha_f" style="width:160px;" value="<?php echo date ("Y-m-d");?>" class="form-control"/>
      			</div>
          </div>
          <div class="form-group mr-1">
             <input type="text" placeholder="Buscar por: Expediente, Nombre, o Identidad" data-toggle="tooltip" data-placement="top" title="Buscar por: Expediente, Nombre, Apellido o Identidad" id="bs_regis" autofocus class="form-control" size="45"/>
          </div>
          <div class="form-group mr-1">
            <button class="btn btn-primary ml-2" type="submit" id="transito_enviada"><div class="sb-nav-link-icon"></div><i class="fas fa-plus fa-lg"></i> Transito Enviada</button>
          </div>
          <div class="form-group mr-1">
            <button class="btn btn-primary ml-2" type="submit" id="transito_recibida"><div class="sb-nav-link-icon"></div><i class="fas fa-plus fa-lg"></i> Transito Recibida</button>
          </div>
          <div class="form-group mr-1">
            <button class="btn btn-success ml-2" type="submit" id="historial"><div class="sb-nav-link-icon"></div><i class="fas fa-search fa-lg"></i> Buscar</button>
          </div>
        </form>
      </div>
      <div class="card-footer small text-muted">

      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header">
        <i class="fas fa-search  mr-1"></i>
        Búsqueda
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
	</div>
	<?php include("templates/factura.php"); ?>
	<?php include("templates/footer.php"); ?>
</div>

    <!-- add javascripts -->
	<?php
		include "script.php";

		include "../js/main.php";
		include "../js/invoice.php";
		include "../js/myjava_atencion_medica.php";
		include "../js/select.php";
		include "../js/functions.php";
		include "../js/myjava_cambiar_pass.php";
	?>
</body>
</html>
