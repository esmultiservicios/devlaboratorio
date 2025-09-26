<?php
session_start();
include "../php/funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

if( isset($_SESSION['colaborador_id']) == false ){
   header('Location: login.php');
}

$_SESSION['menu'] = "Secuencia de Facturación";

if(isset($_SESSION['colaborador_id'])){
 $colaborador_id = $_SESSION['colaborador_id'];
}else{
   $colaborador_id = "";
}

$type = $_SESSION['type'];

$nombre_host = gethostbyaddr($_SERVER['REMOTE_ADDR']);//HOSTNAME
$fecha = date("Y-m-d H:i:s");
$comentario = "Ingreso al Modulo Secuencia de Facturacion";

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
    <title>Secuencia de Facturación :: <?php echo $empresa; ?></title>
	<?php include("script_css.php"); ?>
</head>
<body>
   <!--Ventanas Modales-->
   <!-- Small modal -->
  <?php include("templates/modals.php"); ?>

<!--INICIO MODAL-->
<div class="modal fade" id="secuenciaFacturacion">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Secuencia de Facturación</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <form class="FormularioAjax" id="formularioSecuenciaFacturacion" data-async data-target="#rating-modal" action="" method="POST" data-form="" autocomplete="off" enctype="multipart/form-data">
          <div class="form-row">
            <div class="col-md-12 mb-3">
              <input type="hidden" id="secuencia_facturacion_id" name="secuencia_facturacion_id" class="form-control">
              <div class="input-group mb-1">
                <input type="text" required readonly id="pro" name="pro" class="form-control"/>
                <div class="input-group-append">
                  <span class="input-group-text"><i class="fa fa-plus-square"></i></span>
                </div>
              </div>
              <small class="form-text text-muted">Indica si es registro, edición o eliminación.</small>
            </div>
          </div>

          <div class="form-row">
            <!-- Empresa -->
            <div class="col-md-3 mb-3">
              <label for="empresa">Empresa <span class="priority">*</span></label>
              <div class="input-group mb-1">
                <select class="selectpicker" id="empresa" name="empresa" required data-live-search="true" title="Empresa"></select>
              </div>
              <small class="form-text text-muted">Empresa a la que aplicará esta secuencia.</small>
            </div>

            <!-- Documento -->
            <div class="col-md-3 mb-3">
              <label for="documento_id">Documento <span class="priority">*</span></label>
              <div class="input-group mb-1">
                <select class="selectpicker" id="documento_id" name="documento_id" required data-live-search="true" title="Documento"></select>
              </div>
              <small class="form-text text-muted">Tipo de documento que usará esta numeración.</small>
            </div>

            <!-- CAI -->
            <div class="col-md-6 mb-3">
              <label for="cai">CAI</label>
              <div class="input-group mb-1">
                <input type="text" name="cai" id="cai" class="form-control" placeholder="CAI"
                       data-toggle="tooltip" data-placement="top"
                       title="Formato ejemplo: 57606A-B57ED1-224B98-7DA363-38B33B-B1">
                <div class="input-group-append">
                  <span class="input-group-text"><i class="far fa-id-card fa-lg"></i></span>
                </div>
              </div>
              <small class="form-text text-muted">Obligatorio solo para <em>Factura Electrónica</em>.</small>
            </div>
          </div>

          <div class="form-row">
            <!-- Prefijo -->
            <div class="col-md-4 mb-3">
              <label for="prefijo">Prefijo</label>
              <div class="input-group mb-1">
                <input type="text" name="prefijo" id="prefijo" class="form-control" placeholder="Prefijo"
                       data-toggle="tooltip" data-placement="top"
                       title="Prefijo autorizado. Ej.: 000-001-01-">
                <div class="input-group-append">
                  <span class="input-group-text"><i class="fab fa-autoprefixer fa-lg"></i></span>
                </div>
              </div>
              <small class="form-text text-muted">Opcional. Se antepone al número mostrado.</small>
            </div>

            <!-- Relleno -->
            <div class="col-md-4 mb-3">
              <label for="relleno">Relleno <span class="priority">*</span></label>
              <div class="input-group mb-1">
                <input type="number" min="1" step="1" name="relleno" id="relleno" class="form-control" placeholder="Relleno" required
                       data-toggle="tooltip" data-placement="top"
                       title="Cantidad total de dígitos del número, completando con ceros a la izquierda.">
                <div class="input-group-append">
                  <span class="input-group-text"><i class="fas fa-fill fa-lg"></i></span>
                </div>
              </div>
              <small class="form-text text-muted">Ej.: con <strong>8</strong>, el 1 se mostrará como <code>00000001</code>.</small>
            </div>

            <!-- Incremento -->
            <div class="col-md-4 mb-3">
              <label for="incremento">Incremento <span class="priority">*</span></label>
              <div class="input-group mb-1">
                <input type="number" min="1" step="1" name="incremento" id="incremento" class="form-control" placeholder="Incremento" required
                       data-toggle="tooltip" data-placement="top"
                       title="Cantidad en que aumenta el correlativo cada vez.">
                <div class="input-group-append">
                  <span class="input-group-text"><i class="fas fa-arrow-right fa-lg"></i></span>
                </div>
              </div>
              <small class="form-text text-muted">Normalmente <strong>1</strong> (de uno en uno).</small>
            </div>
          </div>

          <div class="form-row">
            <!-- Siguiente -->
            <div class="col-md-4 mb-3">
              <label for="siguiente">Siguiente <span class="priority">*</span></label>
              <div class="input-group mb-1">
                <input type="number" min="1" step="1" name="siguiente" id="siguiente" class="form-control"
                       placeholder="Siguiente" required
                       data-toggle="tooltip" data-placement="top" title="Próximo número real a emitir (entero).">
                <div class="input-group-append">
                  <span class="input-group-text"><i class="fas fa-caret-right fa-lg"></i></span>
                </div>
              </div>
              <small class="form-text text-muted">Debe estar dentro del rango y no existir ya en <em>Facturas</em> para esta empresa/documento.</small>
            </div>

            <!-- Rango Inicial -->
            <div class="col-md-4 mb-3">
              <label for="rango_inicial">Rango Inicial <span class="priority">*</span></label>
              <div class="input-group mb-1">
                <input type="text" name="rango_inicial" id="rango_inicial" class="form-control" placeholder="Rango Inicial" required>
                <div class="input-group-append">
                  <span class="input-group-text"><i class="fas fa-list-ol fa-lg"></i></span>
                </div>
              </div>
              <small class="form-text text-muted">Escribe solo el número (sin ceros). Se rellenará según el <strong>Relleno</strong>.</small>
            </div>

            <!-- Rango Final -->
            <div class="col-md-4 mb-3">
              <label for="rango_final">Rango Final <span class="priority">*</span></label>
              <div class="input-group mb-1">
                <input type="number" min="1" step="1" name="rango_final" id="rango_final" class="form-control" placeholder="Rango Final" required>
                <div class="input-group-append">
                  <span class="input-group-text"><i class="fas fa-list-ol fa-lg"></i></span>
                </div>
              </div>
              <small class="form-text text-muted">Solo número (sin ceros). El sistema completará con ceros por el <strong>Relleno</strong>.</small>
            </div>
          </div>

          <div class="form-row">
            <!-- Fecha de Activación -->
            <div class="col-md-4 mb-3">
              <label for="fecha_activacion">Fecha de Activación <span class="priority">*</span></label>
              <input type="date" required id="fecha_activacion" name="fecha_activacion" value="<?php echo date('Y-m-d');?>" class="form-control"/>
              <small class="form-text text-muted">Desde cuándo se pueden emitir documentos con esta numeración.</small>
            </div>

            <!-- Fecha Límite -->
            <div class="col-md-4 mb-3">
              <label for="fecha_limite">Fecha Límite <span class="priority">*</span></label>
              <input type="date" required id="fecha_limite" name="fecha_limite" value="<?php echo date('Y-m-d');?>" class="form-control"/>
              <small class="form-text text-muted">Fecha máxima de validez de esta autorización.</small>
            </div>

            <!-- Estado -->
            <div class="col-md-3 mb-3">
              <label for="estado">Estado <span class="priority">*</span></label>
              <div class="input-group mb-1">
                <select class="selectpicker" id="estado" name="estado" required data-live-search="true" title="Estado"></select>
              </div>
              <small class="form-text text-muted">Define si la secuencia queda activa o inactiva.</small>
            </div>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button class="btn btn-primary ml-2" form="formularioSecuenciaFacturacion" type="submit" id="reg">
          <i class="far fa-save fa-lg"></i> Registrar
        </button>
        <button class="btn btn-warning ml-2" form="formularioSecuenciaFacturacion" type="submit" id="edi">
          <i class="fas fa-edit fa-lg"></i> Modificar
        </button>
        <button class="btn btn-danger ml-2" form="formularioSecuenciaFacturacion" type="submit" id="delete">
          <i class="fas fa-trash fa-lg"></i> Eliminar
        </button>
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
		<li class="breadcrumb-item"><a class="breadcrumb-link" href="inicio.php">Dashboard</a></li>
		<li class="breadcrumb-item active" id="acciones_factura"><span id="label_acciones_factura"></span>Secuencia de Facturación</li>
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
                <span class="input-group-text"><div class="sb-nav-link-icon"></div>Documento</span>
              </div>
				<select id="documento" name="documento" class="selectpicker" title="Documento" data-live-search="true">
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
              <input type="date" required="required" id="fechaf" name="fechaf" style="width: 159px;" value="<?php echo date ("Y-m-d");?>" data-toggle="tooltip" data-placement="top" title="Fecha Inicial" class="form-control"/>
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

	</div>
	<?php include("templates/factura.php"); ?>
	<?php include("templates/footer.php"); ?>
</div>

    <!-- add javascripts -->
	<?php
		include "script.php";

		include "../js/main.php";
		include "../js/myjava_secuencia_facturacion.php";
		include "../js/select.php";
		include "../js/functions.php";
		include "../js/myjava_cambiar_pass.php";
	?>

</body>
</html>
