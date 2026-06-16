<?php
session_start();
include "../php/funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

if (isset($_SESSION['colaborador_id']) == false) {
   header('Location: login.php');
}

$_SESSION['menu'] = "Clientes";

if (isset($_SESSION['colaborador_id'])) {
   $colaborador_id = $_SESSION['colaborador_id'];
} else {
   $colaborador_id = "";
}

$type = $_SESSION['type'];

$nombre_host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
$fecha = date("Y-m-d H:i:s");
$comentario = "Ingreso al Modulo de Clientes";

if ($colaborador_id != "" || $colaborador_id != null) {
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

if ($result->num_rows > 0) {
  $empresa = $consulta_registro['nombre'];
}

//FECHAS PARA MUESTRAS
$fecha_hoy = date("Y-m-d");
$mes_actual = (int)date("m");

if ($mes_actual === 1) {
  $fecha_inicial_muestras = date("Y-m-01", strtotime("-4 months"));
} else {
  $fecha_inicial_muestras = date("Y-01-01");
}

$mysqli->close();
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

    <title>Clientes :: <?php echo $empresa; ?></title>

	  <?php include("script_css.php"); ?>

    <style>
      /* =========================================================
         FILTROS MUESTRAS - ORDENADO, INLINE Y RESPONSIVE
         ========================================================= */

      #form_main_admision_muestras {
        width: 100%;
      }

      .filtros-muestras-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        width: 100%;
      }

      .filtro-muestra-item {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
      }

      .filtro-muestra-item .input-group {
        flex-wrap: nowrap;
        width: 100%;
      }

      .filtro-muestra-item .input-group-text {
        height: 38px;
        white-space: nowrap;
        font-size: 14px;
      }

      .filtro-muestra-item .form-control {
        height: 38px;
      }

      .filtro-estado {
        width: 170px;
      }

      .filtro-tipo {
        width: 170px;
      }

      .filtro-cliente {
        width: 260px;
      }

      .filtro-muestra {
        width: 260px;
      }

      .filtro-fecha {
        width: 230px;
      }

      .filtro-buscar {
        flex: 1 1 420px;
        min-width: 360px;
      }

      .filtro-boton {
        width: auto;
      }

      #form_main_admision_muestras select[name="estado"],
      #form_main_admision_muestras select[name="tipo"] {
        width: 110px !important;
      }

      #form_main_admision_muestras select[name="cliente"] {
        width: 170px !important;
      }

      #form_main_admision_muestras select[name="tipo_muestra"] {
        width: 170px !important;
      }

      #form_main_admision_muestras #fecha_i,
      #form_main_admision_muestras #fecha_f {
        width: 130px !important;
      }

      #form_main_admision_muestras #bs_regis {
        width: 100%;
      }

      #form_main_admision_muestras .select2-container {
        height: 38px !important;
      }

      #form_main_admision_muestras .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid #ced4da;
        display: flex;
        align-items: center;
      }

      #form_main_admision_muestras .select2-container .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: 10px;
      }

      #form_main_admision_muestras .select2-container .select2-selection__arrow {
        height: 36px !important;
      }

      #form_main_admision_muestras #buscar_registro {
        height: 38px;
        white-space: nowrap;
      }

      @media (max-width: 1400px) {
        .filtro-buscar {
          flex: 1 1 100%;
          min-width: 100%;
        }
      }

      @media (max-width: 768px) {
        .filtro-muestra-item,
        .filtro-estado,
        .filtro-tipo,
        .filtro-cliente,
        .filtro-muestra,
        .filtro-fecha,
        .filtro-buscar,
        .filtro-boton {
          width: 100%;
          min-width: 100%;
        }

        #form_main_admision_muestras select[name="estado"],
        #form_main_admision_muestras select[name="tipo"],
        #form_main_admision_muestras select[name="cliente"],
        #form_main_admision_muestras select[name="tipo_muestra"],
        #form_main_admision_muestras #fecha_i,
        #form_main_admision_muestras #fecha_f {
          width: 100% !important;
        }

        #form_main_admision_muestras #buscar_registro {
          width: 100%;
        }
      }
    </style>
</head>

<body>
   <!--Ventanas Modales-->
   <?php include("templates/modals.php"); ?>

<div class="modal fade" id="mensaje_show" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Información Clientes</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
			  <form id="mensaje_sistema">
				  <div class="form-row">
					  <div class="col-md-12 mb-3">
					     <div class="modal-title" id="mensaje_mensaje_show"></div>
					  </div>
				  </div>
			  </form>
      </div>

	    <div class="modal-footer">
		    <button class="btn btn-success ml-2" type="submit" id="okay" data-dismiss="modal">
          <div class="sb-nav-link-icon"></div><i class="fas fa-thumbs-up fa-lg"></i> Okay
        </button>

		    <button class="btn btn-danger ml-2" type="submit" id="bad" data-dismiss="modal">
          <div class="sb-nav-link-icon"></div><i class="fas fa-thumbs-up fa-lg"></i> Okay
        </button>
	    </div>
    </div>
  </div>
</div>

<?php include("modals/modals.php");?>

<?php include("templates/menu.php"); ?>

<br><br><br>

<div class="container-fluid">

	<ol class="breadcrumb mt-2 mb-4">
	  <li class="breadcrumb-item" id="acciones_atras">
      <a id="ancla_volver" class="breadcrumb-link" href="#">Clientes</a>
    </li>
		<li class="breadcrumb-item active" id="acciones_factura">
      <span id="label_acciones_factura"></span>
    </li>
	</ol>

  <!-- =========================================================
       VISTA PRINCIPAL CLIENTES
       ========================================================= -->
  <div id="main_facturacion">

    <div class="card mb-4">
      <div class="card-header">
        <i class="fas fa-search mr-1"></i>
        Búsqueda
      </div>

      <div class="card-body">
        <form id="form_main_admision" class="form-inline">

          <div class="form-group mr-1 mb-2">
            <div class="input-group">
              <div class="input-group-append">
                <span class="input-group-text">
                  <div class="sb-nav-link-icon"></div>Estado
                </span>
              </div>
              <select id="estado" name="estado" style="width:130px;"></select>
            </div>
          </div>

          <div class="form-group mr-1 mb-2">
            <div class="input-group">
              <div class="input-group-append">
                <span class="input-group-text">
                  <div class="sb-nav-link-icon"></div>Tipo
                </span>
              </div>
              <select id="tipo" name="tipo" style="width:130px;"></select>
            </div>
          </div>

          <div class="form-group mr-1 mb-2">
            <div class="input-group">
              <input type="text"
                     placeholder="Buscar por: Nombre, Apellido, Identidad o Teléfono Principal"
                     data-toggle="tooltip"
                     data-placement="top"
                     title="Buscar por: Expediente, Nombre, Apellido, Identidad o Teléfono Principal"
                     id="bs_regis"
                     autofocus
                     class="form-control"
                     size="70" />
            </div>
          </div>

          <div class="form-group mr-1 mb-2">
            <button class="btn btn-primary ml-2" type="submit" id="registrar_cliente">
              <div class="sb-nav-link-icon"></div><i class="fas fa-user-plus fa-lg"></i> Clientes
            </button>
          </div>

          <div class="form-group mr-1 mb-2">
            <button class="btn btn-primary ml-2" type="submit" id="registrar_empresa">
              <div class="sb-nav-link-icon"></div><i class="fas fa-user-plus fa-lg"></i> Empresa
            </button>
          </div>

          <div class="form-group mr-1 mb-2">
            <button class="btn btn-primary ml-2" type="submit" id="registrar_productos">
              <div class="sb-nav-link-icon"></div><i class="fas fa-user-plus fa-lg"></i> Productos
            </button>
          </div>

          <div class="form-group mb-2">
            <button class="btn btn-primary ml-2" type="submit" id="ver_muestras">
              <div class="sb-nav-link-icon"></div><i class="fas fa-search fa-lg"></i> Muestras
            </button>
          </div>

        </form>
      </div>

      <div class="card-footer small text-muted"></div>
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

      <div class="card-footer small text-muted"></div>
    </div>

  </div>

  <!-- =========================================================
       VISTA MUESTRAS
       ========================================================= -->
  <div id="main_admision_muestras" style="display:none;">

    <div class="card mb-4">
      <div class="card-header">
        <i class="fas fa-search mr-1"></i>
        Búsqueda
      </div>

      <div class="card-body">

        <form id="form_main_admision_muestras">

          <div class="filtros-muestras-row">

            <div class="filtro-muestra-item filtro-estado">
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">
                    <div class="sb-nav-link-icon"></div>Estado
                  </span>
                </div>
                <select id="estado" name="estado" class="form-control" style="width:110px;"></select>
              </div>
            </div>

            <div class="filtro-muestra-item filtro-tipo">
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">
                    <div class="sb-nav-link-icon"></div>Tipo
                  </span>
                </div>
                <select id="tipo" name="tipo" class="form-control" style="width:110px;"></select>
              </div>
            </div>

            <div class="filtro-muestra-item filtro-cliente">
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">
                    <div class="sb-nav-link-icon"></div>Cliente
                  </span>
                </div>
                <select id="cliente" name="cliente" class="form-control" style="width:170px;"></select>
              </div>
            </div>

            <div class="filtro-muestra-item filtro-muestra">
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">
                    <div class="sb-nav-link-icon"></div>Muestra
                  </span>
                </div>
                <select id="tipo_muestra" name="tipo_muestra" class="form-control" style="width:170px;"></select>
              </div>
            </div>

            <div class="filtro-muestra-item filtro-fecha">
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">
                    <div class="sb-nav-link-icon"></div>Inicio
                  </span>
                </div>
                <input type="date"
                       required="required"
                       id="fecha_i"
                       name="fecha_i"
                       style="width:130px;"
                       data-toggle="tooltip"
                       data-placement="top"
                       title="Fecha Inicial"
                       value="<?php echo $fecha_inicial_muestras; ?>"
                       class="form-control"/>
              </div>
            </div>

            <div class="filtro-muestra-item filtro-fecha">
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">
                    <div class="sb-nav-link-icon"></div>Fin
                  </span>
                </div>
                <input type="date"
                       required="required"
                       id="fecha_f"
                       name="fecha_f"
                       style="width:130px;"
                       value="<?php echo $fecha_hoy; ?>"
                       data-toggle="tooltip"
                       data-placement="top"
                       title="Fecha Final"
                       class="form-control"/>
              </div>
            </div>

            <div class="filtro-muestra-item filtro-buscar">
              <input type="text"
                     placeholder="Buscar por: número de muestra, nombre, identidad o tipo de muestra"
                     data-toggle="tooltip"
                     data-placement="top"
                     title="Buscar por: número de muestra, nombre, identidad o tipo de muestra"
                     id="bs_regis"
                     class="form-control"/>
            </div>

            <div class="filtro-muestra-item filtro-boton">
              <button class="btn btn-primary"
                      type="submit"
                      id="buscar_registro"
                      data-toggle="tooltip"
                      data-placement="top"
                      title="Presione aquí para buscar">
                <div class="sb-nav-link-icon"></div>
                <i class="fas fa-search fa-lg"></i> Buscar
              </button>
            </div>

          </div>

        </form>

      </div>

      <div class="card-footer small text-muted"></div>
    </div>

    <div class="card mb-4">
      <div class="card-header">
        <i class="fab fa-sellsy mr-1"></i>
        Resultado
      </div>

      <div class="card-body">
        <div class="form-group">
   		    <div class="col-sm-12">
   			    <div class="registros overflow-auto" id="agrega-registros_muestras"></div>
   		    </div>
   		  </div>

   		  <nav aria-label="Page navigation example">
   			  <ul class="pagination justify-content-center" id="pagination_muestras"></ul>
   		  </nav>
      </div>

      <div class="card-footer small text-muted"></div>
    </div>

	</div>

	<?php include("templates/factura.php"); ?>
	<?php include("templates/footer.php"); ?>
	<?php include("templates/footer_facturas.php"); ?>

</div>

<?php
	include "script.php";
	include "../js/main.php";
	include "../js/invoice.php";
	include "../js/myjava_productos.php";
	include "../js/myjava_admision.php";
	include "../js/select.php";
	include "../js/functions.php";
	include "../js/myjava_cambiar_pass.php";
	include "../js/myjava_cambiar_pass.php";
?>

</body>
</html>