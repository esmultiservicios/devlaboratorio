<?php
session_start();
include "../php/funtions.php";

// CONEXION A DB
$mysqli = connect_mysqli();

if (isset($_SESSION['colaborador_id']) == false) {
    header('Location: login.php');
    exit;
}

$_SESSION['menu'] = "Citas";

if (isset($_SESSION['colaborador_id'])) {
    $colaborador_id = $_SESSION['colaborador_id'];
} else {
    $colaborador_id = "";
}

$type = $_SESSION['type'];

$nombre_host = gethostbyaddr($_SERVER['REMOTE_ADDR']); // HOSTNAME
$fecha = date("Y-m-d H:i:s");
$comentario = "Ingreso al Menu Citas";

// OBTENER CORRELATIVO
$query = "SELECT MAX(acceso_id) AS max, COUNT(acceso_id) AS count FROM historial_acceso";

$result = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));

$correlativo2 = mysqli_fetch_array($result);

$numero = $correlativo2['max'];
$cantidad = $correlativo2['count'];

if ($cantidad == 0) {
    $numero = 1;
} else {
    $numero = $numero + 1;
}

if ($colaborador_id != "" || $colaborador_id != null) {
    historial_acceso($comentario, $nombre_host, $colaborador_id);
}

$fecha = date("Y-m-d");
$mes = nombremes(date("m", strtotime($fecha)));
$año = date("Y", strtotime($fecha));

// OBTENER NOMBRE DE EMPRESA
$usuario = $_SESSION['colaborador_id'];

$query_empresa = "SELECT e.nombre AS nombre
    FROM users AS u
    INNER JOIN empresa AS e
        ON u.empresa_id = e.empresa_id
    WHERE u.colaborador_id = '$usuario'";

$result = $mysqli->query($query_empresa) or die($mysqli->error);

$empresa = '';

if ($result->num_rows > 0) {
    $consulta_registro = $result->fetch_assoc();
    $empresa = $consulta_registro['nombre'];
}

$mysqli->close(); // CERRAR CONEXIÓN
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta name="author" content="Script Tutorials"/>
    <meta name="description" content="Responsive Websites Orden Hospitalaria de San Juan de Dios">
    <meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=Edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <title>Citas :: <?php echo $empresa; ?></title>

    <?php include("script_css.php"); ?>

    <!-- FULLCALENDAR 3.10.2 CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css">

    <style>
        body {
            background: #f4f7fb;
        }

        .container-fluid {
            padding-left: 22px;
            padding-right: 22px;
        }

        .breadcrumb {
            background: #ffffff;
            border-radius: 12px;
            padding: 14px 18px;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06);
            border: 1px solid #e8edf3;
        }

        .card-citas,
        .calendar-card {
            border: 0;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
            overflow: hidden;
            background: #ffffff;
            border: 1px solid #e8edf3;
        }

        .card-citas .card-header,
        .calendar-card .card-header {
            background: linear-gradient(90deg, #ffffff 0%, #f7fbff 100%);
            font-weight: 800;
            color: #172033;
            padding: 16px 20px;
            border-bottom: 1px solid #e8edf3;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-citas .card-body,
        .calendar-card .card-body {
            padding: 20px;
        }

        .input-group-text {
            font-weight: 700;
            background: #f3f8ff;
            color: #1f3b57;
            border: 1px solid #d9e6f5;
            border-radius: 10px 0 0 10px;
        }

        #bs_regis {
            border-radius: 10px;
            border: 1px solid #d9e6f5;
            min-height: 42px;
            box-shadow: none;
        }

        #bs_regis:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.12);
        }

        .btn-citas-clientes {
            font-weight: 800;
            border-radius: 10px;
            padding: 9px 18px;
            box-shadow: 0 6px 14px rgba(13, 110, 253, 0.22);
        }

        .bootstrap-select .dropdown-toggle {
            min-height: 42px;
            border-radius: 0 10px 10px 0;
            border: 1px solid #d9e6f5;
            background: #ffffff;
            font-weight: 600;
        }

        #calendar {
            width: 100%;
            min-height: 720px;
            background: #ffffff;
            border-radius: 14px;
            padding: 14px;
            position: relative;
            z-index: 1;
            border: 1px solid #e5edf6;
        }

        .fc-toolbar {
            margin-bottom: 18px !important;
            padding: 4px 2px;
        }

        .fc-toolbar h2 {
            font-size: 24px;
            font-weight: 900;
            color: #172033;
            text-transform: capitalize;
            letter-spacing: .2px;
        }

        .fc-button {
            border-radius: 10px !important;
            font-weight: 800 !important;
            border: 1px solid #d8e2ee !important;
            background: #ffffff !important;
            color: #1d2b3a !important;
            box-shadow: 0 3px 10px rgba(15, 23, 42, 0.06) !important;
            text-shadow: none !important;
            padding: 8px 13px !important;
            height: auto !important;
        }

        .fc-button:hover {
            background: #f0f7ff !important;
            border-color: #b8d7ff !important;
            color: #0d6efd !important;
        }

        .fc-state-active,
        .fc-button.fc-state-active {
            background: #0d6efd !important;
            color: #ffffff !important;
            border-color: #0d6efd !important;
            box-shadow: 0 6px 16px rgba(13, 110, 253, 0.28) !important;
        }

        .fc-view-container {
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid #dfe8f3;
        }

        .fc-unthemed th,
        .fc-unthemed td,
        .fc-unthemed thead,
        .fc-unthemed tbody,
        .fc-unthemed .fc-divider,
        .fc-unthemed .fc-row,
        .fc-unthemed .fc-content,
        .fc-unthemed .fc-popover,
        .fc-unthemed .fc-list-view,
        .fc-unthemed .fc-list-heading td {
            border-color: #e3ebf5;
        }

        .fc-head-container {
            background: #0d95a8;
        }

        .fc-widget-header {
            background: #0d95a8;
            color: #ffffff;
            font-size: 14px;
            font-weight: 900;
            padding: 9px 5px !important;
            text-transform: capitalize;
            border-color: rgba(255,255,255,.22) !important;
        }

        .fc-axis {
            background: #f8fbff;
            color: #334155;
            font-weight: 700;
            font-size: 13px;
        }

        .fc-time-grid .fc-slats td {
            height: 2.35em;
        }

        .fc-time-grid .fc-slats .fc-minor td {
            border-top-style: dashed;
            border-top-color: #edf2f7;
        }

        .fc-unthemed td.fc-today {
            background: #eaf4ff !important;
        }

        .fc-day.fc-sat,
        .fc-day.fc-sun {
            background: #f8fafc;
        }

        .fc-event {
            border: 0 !important;
            border-radius: 10px !important;
            padding: 4px 6px !important;
            font-size: 12px !important;
            font-weight: 800 !important;
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.16);
            cursor: pointer;
        }

        .fc-event .fc-content {
            color: #ffffff;
        }

        .fc-time-grid-event .fc-time {
            font-weight: 900;
            font-size: 11px;
            opacity: .95;
        }

        .fc-time-grid-event .fc-title {
            padding-top: 3px;
            line-height: 1.25;
        }

        .fc-nonbusiness {
            background: rgba(15, 23, 42, 0.035);
        }

        .fc-highlight {
            background: rgba(13, 110, 253, 0.14) !important;
            border-radius: 8px;
        }

        .fc-more {
            color: #0d6efd;
            font-weight: 800;
        }

        .fc-popover {
            border-radius: 12px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.18);
            border: 1px solid #dce7f3;
            overflow: hidden;
        }

        .fc-popover .fc-header {
            background: #0d95a8;
            color: #ffffff;
            padding: 10px;
            font-weight: 800;
        }

        @media (max-width: 991px) {
            .fc-toolbar {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .fc-toolbar .fc-left,
            .fc-toolbar .fc-center,
            .fc-toolbar .fc-right {
                float: none;
                text-align: center;
            }

            .fc-toolbar h2 {
                font-size: 20px;
            }

            #calendar {
                padding: 8px;
                min-height: 620px;
            }
        }
    </style>
</head>

<body>

<!-- Ventanas Modales -->
<?php include("templates/modals.php"); ?>
<?php include("modals/modals_citas.php"); ?>
<?php include("modals/modals.php"); ?>
<!-- Fin Ventanas Modales -->

<?php include("templates/menu.php"); ?>

<br><br><br>

<div class="container-fluid">
    <ol class="breadcrumb mt-2 mb-4">
        <li class="breadcrumb-item">
            <a class="breadcrumb-link" href="inicio.php">Dashboard</a>
        </li>
        <li class="breadcrumb-item active" id="acciones_factura">
            <span id="label_acciones_factura"></span>Citas
        </li>
    </ol>

    <div class="card card-citas mb-4">
        <div class="card-header">
            <i class="fas fa-search mr-1"></i>
            Búsqueda
        </div>

        <div class="card-body">
            <form id="botones_citas" class="form-inline">
                <div class="form-group mr-2 mb-2">
                    <div class="input-group">
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <div class="sb-nav-link-icon"></div>
                                <i class="fas fa-vial mr-1"></i> Tipo Muestra
                            </span>
                        </div>

                        <select id="tipo_muestra" name="tipo_muestra" class="selectpicker" title="Tipo Muestra" data-live-search="true">
                        </select>
                    </div>
                </div>

                <div class="form-group mr-2 mb-2">
                    <div class="input-group">
                        <input 
                            type="text"
                            placeholder="Buscar por: Nombre, Apellido, Identidad o Teléfono Principal"
                            data-toggle="tooltip"
                            data-placement="top"
                            title="Buscar por: Expediente, Nombre, Apellido, Identidad o Teléfono Principal"
                            id="bs_regis"
                            autofocus
                            class="form-control"
                            size="70">
                    </div>
                </div>

                <div class="form-group mr-2 mb-2">
                    <button class="btn btn-primary btn-citas-clientes ml-2" type="submit" id="refresh">
                        <div class="sb-nav-link-icon"></div>
                        <i class="fas fa-users fa-lg"></i> Clientes
                    </button>
                </div>
            </form>
        </div>

        <div class="card-footer small text-muted"></div>
    </div>

    <div class="card calendar-card mb-4">
        <div class="card-header">
            <i class="fas fa-calendar-alt mr-1"></i>
            Calendario de Citas
        </div>

        <div class="card-body">
            <div id="calendar" class="col-centered"></div>
        </div>
    </div>

    <?php include("templates/footer.php"); ?>
</div>

<!-- JS GENERAL DEL SISTEMA -->
<?php include "script.php"; ?>

<script src="<?php echo SERVERURL; ?>js/query/menu-despelgable.js"></script>

<?php
    include "../js/main.php";
    include "../js/select.php";
    include "../js/functions.php";
    include "../js/myjava_cambiar_pass.php";
?>

<!-- FULLCALENDAR 3.10.2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/locale-all.min.js"></script>

<!-- JS DE CITAS SIEMPRE AL FINAL -->
<?php
    include "../js/myjava_citas.php";
?>

</body>
</html>