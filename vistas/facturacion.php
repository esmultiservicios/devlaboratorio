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
$comentario = "Ingreso al Modulo de Facturacion";

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
    <meta charset="utf-8" />
    <meta name="author" content="Script Tutorials" />
    <meta name="description" content="Responsive Websites Orden Hospitalaria de San Juan de Dios">
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=Edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Facturación :: <?php echo $empresa; ?></title>
    <?php include("script_css.php"); ?>
</head>

<body>
    <!--Ventanas Modales-->
    <!-- Small modal -->
    <!--INICIO VENTANA MODALES-->
    <?php include("modals/modals.php");?>
    <!--FIN VENTANA MODALES-->

    <?php include("templates/menu.php"); ?>
    <?php include("templates/modals.php"); ?>

    <br><br><br>
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mt-2 mb-4">
                <li class="breadcrumb-item" id="acciones_atras"><a id="ancla_volver" class="breadcrumb-link"
                        style="text-decoration: none;" href="#"><span id="label_acciones_volver"></a></li>
                <li class="breadcrumb-item active" id="acciones_factura"><span id="label_acciones_factura"></span></li>
            </ol>
        </nav>

        <div class="" id="main_facturacion">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-search  mr-1"></i>
                    Búsqueda
                </div>
                <div class="card-body">
                    <form id="form_main_facturas" class="form-inline">
                        <div class="form-group mr-1">
                            <div class="input-group">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <div class="sb-nav-link-icon"></div>Tipo Cliente
                                    </span>
                                </div>
                                <select id="tipo_paciente_grupo" name="tipo_paciente_grupo" class="selectpicker"
                                    title="Tipo Cliente" data-live-search="true">
                                </select>
                            </div>
                        </div>
                        <div class="form-group mr-1">
                            <div class="input-group">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <div class="sb-nav-link-icon"></div>Cliente
                                    </span>
                                </div>
                                <select id="pacientesIDGrupo" name="pacientesIDGrupo" class="selectpicker"
                                    title="Cliente" data-live-search="true">
                                </select>
                            </div>
                        </div>
                        <div class="form-group mr-1">
                            <div class="input-group">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <div class="sb-nav-link-icon"></div>Estado
                                    </span>
                                </div>
                                <select id="estado" name="estado" class="selectpicker" title="Estado"
                                    data-live-search="true">
                                </select>
                            </div>
                        </div>
                        <div class="form-group mr-1">
                            <div class="input-group">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <div class="sb-nav-link-icon"></div>Fecha Inicio
                                    </span>
                                </div>
                                <input type="date" required="required" id="fecha_b" name="fecha_b" style="width:160px;"
                                    data-toggle="tooltip" data-placement="top" title="Fecha Inicial" value="<?php
                                        $fecha = date ("Y-m-d");

                                        $año = date("Y", strtotime($fecha));
                                        $mes = date("m", strtotime($fecha));
                                        $dia = date("d", mktime(0,0,0, $mes+1, 0, $año));

                                        $dia1 = date('d', mktime(0,0,0, $mes, 1, $año)); //PRIMER DIA DEL MES
                                        $dia2 = date('d', mktime(0,0,0, $mes, $dia, $año)); // ULTIMO DIA DEL MES

                                        $fecha_inicial = date("Y-m-d", strtotime($año."-".$mes."-".$dia1));
                                        $fecha_final = date("Y-m-d", strtotime($año."-".$mes."-".$dia2));

                                        echo $fecha_inicial;
                                    ?>" class="form-control" />
                            </div>
                        </div>
                        <div class="form-group mr-1">
                            <div class="input-group">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <div class="sb-nav-link-icon"></div>Fecha Fin
                                    </span>
                                </div>
                                <input type="date" required="required" id="fecha_f" name="fecha_f" style="width:160px;"
                                    value="<?php echo date ("Y-m-d");?>" data-toggle="tooltip" data-placement="top"
                                    title="Fecha Final" class="form-control" />
                            </div>
                        </div>
                        <div class="form-group mr-1">
                            <input type="text" placeholder="Buscar por: Paciente, Identidad o Factura"
                                data-toggle="tooltip" data-placement="top"
                                title="Buscar por: Expediente, Nombre, Apellido, Identidad o Número de Factura"
                                id="bs_regis" autofocus class="form-control mt-2 mr-1" size="55" />
                        </div>
                        <div class="form-group mr-1">
                            <button class="btn btn-primary mt-2 mr-1" type="submit" id="buscar" data-toggle="tooltip"
                                data-placement="top" title="Buscar">
                                <div class="sb-nav-link-icon"></div><i class="fas fa-search fa-lg"></i> Buscar
                            </button>
                        </div>
                        <div class="form-group" style="display:none" id="factura_manual">
                            <button class="btn btn-primary mt-2 mr-1" type="submit" id="nuevo_registro">
                                <div class="sb-nav-link-icon"></div><i class="fas fa-file-invoice fa-lg"></i> Factura
                            </button>
                        </div>
                        <div class="form-group mr1-1">
                            <button class="btn btn-primary mt-2 mr-1" type="submit" id="cierre" data-toggle="tooltip"
                                data-placement="top" title="Realizar Cierre">
                                <div class="sb-nav-link-icon"></div><i class="fas fa-cash-register fa-lg"></i> Cierre
                            </button>
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

        <div class="container-fluid">

        </div>

        <div id="grupo_facturacion" style="display:none;">
            <form class="FormularioAjax" id="formGrupoFacturacion" data-async data-target="#rating-modal" action=""
                method="POST" data-form="" autocomplete="off" enctype="multipart/form-data">
                <div class="form-group row">
                    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
                        <button class="btn btn-primary" type="submit" id="validar" data-toggle="tooltip"
                            data-placement="top" title="Registrar la Factura">
                            <div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar
                        </button>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputCliente" class="col-sm-1 col-form-label-md">Empresa <span
                            class="priority">*<span /></label>
                    <div class="col-sm-5">
                        <div class="input-group mb-3">
                            <input type="hidden" class="form-control" placeholder="Profesional" id="clienteIDGrupo"
                                name="clienteIDGrupo" readonly required>
                            <input type="text" class="form-control" placeholder="Paciente" id="clienteNombreGrupo"
                                name="clienteNombreGrupo" readonly required>
                            <input type="hidden" class="form-control" placeholder="Tamaño" id="tamano" name="tamano"
                                readonly required>
                            <div class="input-group-append" id="grupo_buscar_colaboradores">
                                <a data-toggle="modal" href="#" class="btn btn-outline-success"
                                    id="buscar_pacienteGrupo">
                                    <div class="sb-nav-link-icon"></div><i class="fas fa-search-plus fa-lg"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <label for="inputFecha" class="col-sm-1 col-form-label-md">Fecha <span
                            class="priority">*<span /></label>
                    <div class="col-sm-3">
                        <input type="date" class="form-control" value="<?php echo date('Y-m-d');?>" id="fechaGrupo"
                            name="fechaGrupo">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputCliente" class="col-sm-1 col-form-label-md">Profesional <span
                            class="priority">*<span /></label>
                    <div class="col-sm-5">
                        <div class="input-group mb-3">
                            <input type="hidden" class="form-control" placeholder="Profesional" id="colaborador_idGrupo"
                                name="colaborador_idGrupo" readonly required>
                            <input type="text" class="form-control" placeholder="Profesional"
                                id="colaborador_nombreGrupo" name="colaborador_nombreGrupo" readonly required>
                            <div class="input-group-append" id="grupo_buscar_colaboradores">
                                <a data-toggle="modal" href="#" class="btn btn-outline-success"
                                    id="buscar_colaboradoresGrupo">
                                    <div class="sb-nav-link-icon"></div><i class="fas fa-search-plus fa-lg"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <label for="inputFecha" class="col-sm-1 col-form-label-md">Servicio <span
                            class="priority">*<span /></label>
                    <div class="col-sm-3">
                        <div class="input-group mb-3">
                            <select id="servicio_idGrupo" name="servicio_idGrupo" class="custom-select"
                                data-toggle="tooltip" data-placement="top" title="Servicio" required></select>
                            <div class="input-group-append">
                                <a data-toggle="modal" href="#" class="btn btn-outline-success"
                                    id="buscar_serviciosGrupo">
                                    <div class="sb-nav-link-icon"></div><i class="fab fa-servicestack fa-lg"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <label class="switch mb-3" data-toggle="tooltip" data-placement="top"
                        title="Tipo de Factura, Contado o Crédito">
                        <input type="checkbox" id="facturas_grupal_activo" name="facturas_grupal_activo" value="1"
                            checked>
                        <div class="slider round"></div>
                    </label>
                    <span class="question mb-2" id="label_facturas_grupal_activo"></span>
                </div>
                <div class="form-group row table-responsive-xl tableFixHead table table-hover">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <table class="table table-bordered table-hover" id="invoiceItemGrupo">
                            <thead align="center" class="table-success">
                                <tr>
                                    <th width="30%">Paciente</th>
                                    <th width="20%">Saldo</th>
                                    <th width="20%">Descuento</th>
                                    <th width="20%">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <input type="text" name="codigoFacturaGrupo[]" id="codigoFacturaGrupo_0"
                                            class="form-control" placeholder="Cantidad" readonly autocomplete="off">
                                        <input type="hidden" name="quantyGrupoQuantity[]" id="quantyGrupoQuantity_0"
                                            class="form-control" placeholder="Cantidad" readonly autocomplete="off">
                                        <input type="hidden" name="billGrupoMuestraID[]" id="billGrupoMuestraID_0"
                                            class="form-control" placeholder="Muestra ID" readonly autocomplete="off">
                                        <input type="hidden" name="billGrupoMaterial[]" id="billGrupoMaterial_0"
                                            class="form-control" placeholder="Material Enviado" readonly
                                            autocomplete="off">
                                        <input type="hidden" name="billGrupoDescuento[]" id="billGrupoDescuento_0"
                                            class="form-control" readonly placeholder="Descuento" readonly
                                            autocomplete="off">
                                        <input type="hidden" name="billGrupoISV[]" id="billGrupoISV_0"
                                            class="form-control" placeholder="ISV" readonly value="0"
                                            autocomplete="off">
                                        <input type="hidden" name="billGrupoID[]" id="billGrupoID_0"
                                            class="form-control" placeholder="Código Factura" readonly
                                            autocomplete="off">
                                        <input type="hidden" name="pacienteIDBillGrupo[]" id="pacienteIDBillGrupo_0"
                                            class="form-control" readonly placeholder="Paciente" autocomplete="off">
                                        <input type="text" name="pacienteBillGrupo[]" id="pacienteBillGrupo_0"
                                            class="form-control" readonly placeholder="Paciente" autocomplete="off">
                                    </td>
                                    <td>
                                        <input type="number" name="importeBillGrupo[]" id="importeBillGrupo_0"
                                            class="form-control" step="0.01" readonly placeholder="Saldo"
                                            autocomplete="off">
                                    </td>
                                    <td>
                                        <input type="number" name="discountBillGrupo[]" id="discountBillGrupo_0"
                                            class="form-control" step="0.01" value="0" placeholder="Descuento"
                                            autocomplete="off">
                                    </td>
                                    <td>
                                        <input type="number" name="totalBillGrupo[]" id="totalBillGrupo_0"
                                            class="form-control total" placeholder="Total" readonly autocomplete="off">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="form-row col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="col-sm-12 col-md-12">
                            <h3>Notas: </h3>
                            <div class="form-group">
                                <textarea class="form-control txt" rows="5" name="notesBillGrupo" id="notesBillGrupo"
                                    placeholder="Notas" maxlength="255"></textarea>
                                <p id="charNum_notas">255 Caracteres</p>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-4" style="display: none;">
                            <div class="row">
                                <div class="col-sm-3 form-inline">
                                    <label>Subtotal:</label>
                                </div>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <div class="input-group-append mb-1">
                                            <span class="input-group-text">
                                                <div class="sb-nav-link-icon"></div>L</i>
                                            </span>
                                        </div>
                                        <input value="" type="number" class="form-control" name="subTotalBillGrupo"
                                            step="0.01" id="subTotalBillGrupo" readonly placeholder="Subtotal">
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="display: none">
                                <div class="col-sm-3 form-inline">
                                    <label>Porcentaje:</label>
                                </div>
                                <div class="col-sm-9">
                                    <div class="input-group mb-1">
                                        <input value="0" type="number" class="form-control" name="taxRateBillGrupo"
                                            id="taxRateBillGrupo" step="0.01" readonly placeholder="Tasa de Impuestos">
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <div class="sb-nav-link-icon"></div>%</i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-3 form-inline">
                                    <label>ISV:</label>
                                </div>
                                <div class="col-sm-9">
                                    <div class="input-group mb-1">
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <div class="sb-nav-link-icon"></div>L</i>
                                            </span>
                                        </div>
                                        <input value="" type="number" class="form-control" name="taxAmountBillGrupo"
                                            id="taxAmountBillGrupo" step="0.01" readonly value="0.00"
                                            placeholder="Monto del Impuesto">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-3 form-inline">
                                    <label>Descuento:</label>
                                </div>
                                <div class="col-sm-9">
                                    <div class="input-group mb-1">
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <div class="sb-nav-link-icon"></div>L</i>
                                            </span>
                                        </div>
                                        <input value="" type="number" class="form-control" name="taxDescuentoBillGrupo"
                                            id="taxDescuentoBillGrupo" step="0.01" readonly value="0.00"
                                            placeholder="Descuento Otorgado">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-3 form-inline">
                                    <label>Total:</label>
                                </div>
                                <div class="col-sm-9">
                                    <div class="input-group mb-1">
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <div class="sb-nav-link-icon"></div>L</i>
                                            </span>
                                        </div>
                                        <input value="" type="number" class="form-control" name="totalAftertaxBillGrupo"
                                            id="totalAftertaxBillGrupo" step="0.01" value="0.00" readonly
                                            placeholder="Total">
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="display: none;">
                                <div class="col-sm-3 form-inline">
                                    <label>Cantidad pagada:</label>
                                </div>
                                <div class="col-sm-9">
                                    <div class="input-group mb-1">
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <div class="sb-nav-link-icon"></div>L</i>
                                            </span>
                                        </div>
                                        <input value="" type="number" class="form-control" name="amountPaidBillGrupo"
                                            id="amountPaidBillGrupo" readonly step="0.01" placeholder="Cantidad pagada">
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="display: none;">
                                <div class="col-sm-3 form-inline">
                                    <label>Cantidad debida:</label>
                                </div>
                                <div class="col-sm-9">
                                    <div class="input-group mb-1">
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <div class="sb-nav-link-icon"></div>L</i>
                                            </span>
                                        </div>
                                        <input value="" type="number" class="form-control" name="amountDueBillGrupo"
                                            id="amountDueBillGrupo" readonly step="0.01" placeholder="Cantidad debida">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div>
        <?php include("templates/factura.php"); ?>

        <?php include("templates/footer.php"); ?>
        <?php include("templates/footer_facturas.php"); ?>
    </div>

    <!-- add javascripts -->
    <?php
		include "script.php";

		include "../js/main.php";
		include "../js/invoice.php";
		include "../js/myjava_facturacion.php";
		include "../js/select.php";
		include "../js/functions.php";
		include "../js/myjava_cambiar_pass.php";
	?>

</body>

</html>