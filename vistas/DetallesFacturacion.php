<?php
session_start();
include "../php/funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

if( isset($_SESSION['colaborador_id']) == false ){
   header('Location: login.php');
}

$_SESSION['menu'] = "Facturación";

if(isset($_SESSION['colaborador_id'])){
 $colaborador_id = $_SESSION['colaborador_id'];
}else{
   $colaborador_id = "";
}

$type = $_SESSION['type'];

$nombre_host = gethostbyaddr($_SERVER['REMOTE_ADDR']);//HOSTNAME
$fecha = date("Y-m-d H:i:s");
$comentario = "Ingreso al Modulo de Facturación";

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
    <title>Facturación :: <?php echo $empresa; ?></title>
    <?php include("script_css.php"); ?>
</head>
<body>
   <!--Ventanas Modales-->
   <!-- Small modal -->
  <?php include("templates/modals.php"); ?>

<!-- Modal para ver detalles de factura -->
<div class="modal fade" id="modalDetalleFactura" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de Factura <span id="numero-factura-modal"></span></h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <h6><strong>Fecha:</strong> <span id="fecha-factura"></span></h6>
                        <h6><strong>Cliente:</strong> <span id="cliente-factura"></span></h6>
                    </div>
                    <div class="col-md-4">
                        <h6><strong>Tipo:</strong> <span id="tipo-factura"></span></h6>
                        <h6><strong>Estado:</strong> <span id="estado-factura"></span></h6>
                    </div>
                    <div class="col-md-4 text-right">
                        <h6><strong>Subtotal:</strong> <span id="subtotal-factura"></span></h6>
                        <h6><strong>Total:</strong> <span id="total-factura"></span></h6>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-header-gradient table-striped table-condensed table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>Producto/Servicio</th>
                                <th width="10%">Cantidad</th>
                                <th width="15%">Precio Unitario</th>
                                <th width="15%">ISV</th>
                                <th width="15%">Descuento</th>
                                <th width="15%">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="detalle-factura-body">
                        </tbody>
                    </table>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-12">
                        <h6><strong>Notas:</strong></h6>
                        <p id="notas-factura" class="text-muted"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cerrar
                </button>
                <button type="button" id="btn-imprimir-factura" class="btn btn-primary">
                    <i class="fas fa-print mr-1"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<!--FIN MODAL PARA EL INGRESO DE FACTURAS-->
   <?php include("modals/modals.php");?>
<!--FIN VENTANAS MODALES-->

<?php include("templates/menu.php"); ?>

<br><br><br>
<div class="container-fluid">
	<ol class="breadcrumb mt-2 mb-4">
		<li class="breadcrumb-item"><a class="breadcrumb-link" href="inicio.php">Dashboard</a></li>
		<li class="breadcrumb-item active" id="acciones_factura"><span id="label_acciones_factura"></span>Detalles de facturación</li>
	</ol>    

    <!-- Filtros de búsqueda -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter mr-1"></i>
            Filtros de Búsqueda
        </div>
        <div class="card-body">
            <form id="form-filtros-facturas">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="fecha_inicio">Fecha Inicio</label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="fecha_fin">Fecha Fin</label>
                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="tipo_factura">Tipo de Factura</label>
                        <select class="form-control selectpicker" id="tipo_factura" name="tipo_factura" data-live-search="true">
                            <option value="">Todos</option>
                            <option value="1">Contado</option>
                            <option value="2">Crédito</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="estado_factura">Estado</label>
                        <select class="form-control selectpicker" id="estado_factura" name="estado_factura" data-live-search="true">
                            <option value="">Todos</option>
                            <option value="2">Pagadas</option>
                            <option value="3">Crédito</option>
                            <option value="4">Canceladas</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="numero_factura">Número de Factura</label>
                        <input type="text" class="form-control" id="numero_factura" name="numero_factura" placeholder="Buscar por número">
                    </div>
                    <div class="form-group col-md-6 text-right align-self-end">
                        <button type="submit" id="btn-buscar-facturas" class="btn btn-primary">
                            <i class="fas fa-filter mr-1 fa-lg"></i> Filtrar
                        </button>                        
                        <button type="button" id="btn-limpiar-filtros" class="btn btn-secondary mr-2">
                            <i class="fas fa-broom mr-1 fa-lg"></i> Limpiar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- DataTable para mostrar facturas -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-file-invoice-dollar mr-1"></i>
            Historial de Facturación
            <div class="float-right">
                <span class="badge bg-light text-dark">
                    <i class="fas fa-sync-alt mr-1 fa-lg"></i>
                    <span id="contador-actualizacion"></span>
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="dataTableFacturas" class="table table-header-gradient table-striped table-condensed table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Número</th>
                            <th>Cliente</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Subtotal</th>
                            <th>ISV</th>
                            <th>Descuento</th>
                            <th>Total</th>
                            <th width="20%">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <div class="card-footer small text-muted">

        </div>
    </div>
</div>

    <?php include("templates/footer.php"); ?>
</div>

    <!-- add javascripts -->
    <?php
        include "script.php";

        include "../js/main.php";
        include "../js/DetallesFacturacion.php";
        include "../js/select.php";
        include "../js/functions.php";
        include "../js/myjava_cambiar_pass.php";
    ?>
</body>
</html>