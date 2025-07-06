<!-- Navigation -->
<nav class="navbar navbar-expand-md navbar-dark bg-primary fixed-top">
    <a class="navbar-brand" href="#">
        <img src="<?php echo SERVERURL; ?>img/cami_logo_menu.svg" class="logo" alt="" width="90%"/>
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarsExampleDefault">
        <!-- Menú principal izquierdo -->
        <ul class="navbar-nav mr-auto">
            <?php if ($_SESSION['type']==3): //CAJA ?>
            <li class="nav-item dropdown active">
                <a class="nav-link dropdown-toggle" href="#" id="dropdown01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa-solid fa-hospital-user fa-lg"></i>&nbsp;Recepción</a>
                <div class="dropdown-menu" aria-labelledby="dropdown01">
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/admision.php">Admision</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/pacientes.php">Pacientes</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/pacientes.php">Clientes</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/citas.php">Calendario</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/hospitales.php">Hospitales</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/facturacion.php">Facturación</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/cobrarClientes.php">Cobrar Clientes</a>
                </div>
            </li>
            <li class="nav-item dropdown active">
                <a class="nav-link dropdown-toggle" href="#" id="dropdown04" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa-solid fa-warehouse fa-lg"></i>&nbsp;Almacén</a>
                <div class="dropdown-menu" aria-labelledby="dropdown05">
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/productos.php">Productos</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/movimientos.php">Movimientos</a>
                </div>
            </li>
            <li class="nav-item dropdown active">
                <a class="nav-link dropdown-toggle" href="#" id="dropdown04" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa-solid fa-chart-bar fa-lg"></i>&nbsp;Reportes</a>
                <div class="dropdown-menu" aria-labelledby="dropdown05">
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/reporte_facturacion.php">Reporte de Facturación</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/reporte_facturacion_grupal.php">Facturas Grupales</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/reporte_pagos.php">Reporte de Pagos</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/reportes_muestras.php">Reporte de Muestras</a>
                </div>
            </li>
            <?php endif; ?>

            <?php if ($_SESSION['type']==4): //CONTADOR GENERAL ?>
            <li class="nav-item dropdown active">
                <a class="nav-link dropdown-toggle" href="#" id="dropdown04" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa-solid fa-chart-bar fa-lg"></i>&nbsp;Reportes</a>
                <div class="dropdown-menu" aria-labelledby="dropdown05">
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/reporte_facturacion.php">Reporte de Facturación</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/reporte_facturacion_grupal.php">Facturas Grupales</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/reporte_pagos.php">Reporte de Pagos</a>
                </div>
            </li>
            <?php endif; ?>

            <?php if ($_SESSION['type']==6): //CLINICA/HOSPITALES ?>
            <li class="nav-item dropdown active">
                <a class="nav-link dropdown-toggle" href="#" id="dropdown04" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa-solid fa-chart-bar fa-lg"></i>&nbsp;Reportes</a>
                <div class="dropdown-menu" aria-labelledby="dropdown05">
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/reportes_atenciones_medicas.php">Reporte de Atenciones</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/reportes_muestras_medicos.php">Reporte de Muestras</a>
                </div>
            </li>
            <?php endif; ?>

            <?php if ($_SESSION['type']==1 || $_SESSION['type']==2): //Super Administrador y Administrador ?>
            <li class="nav-item active">
                <a class="nav-link" href="<?php echo SERVERURL; ?>vistas/inicio.php"><i class="fa-solid fa-gauge fa-lg"></i>&nbsp;Dashboard <span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item dropdown active">
                <a class="nav-link dropdown-toggle" href="#" id="dropdown01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa-solid fa-hospital-user fa-lg"></i>&nbsp;Recepción</a>
                <div class="dropdown-menu" aria-labelledby="dropdown01">
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/admision.php">Admision</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/pacientes.php">Pacientes</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/pacientes.php">Clientes</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/citas.php">Calendario</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/hospitales.php">Hospitales</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/facturacion.php">Facturación</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/cobrarClientes.php">Cobrar Clientes</a>
                </div>
            </li>
            <li class="nav-item dropdown active">
                <a class="nav-link dropdown-toggle" href="#" id="dropdown03" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa-solid fa-user-doctor fa-lg"></i>&nbsp;Profesionales</a>
                <div class="dropdown-menu" aria-labelledby="dropdown03">
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/atencion_medica.php">Atenciones</a>
                </div>
            </li>
            <li class="nav-item dropdown active">
                <a class="nav-link dropdown-toggle" href="#" id="dropdown04" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa-solid fa-warehouse fa-lg"></i>&nbsp;Almacén</a>
                <div class="dropdown-menu" aria-labelledby="dropdown05">
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/productos.php">Productos</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/movimientos.php">Movimientos</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/almacen.php">Almacén</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/ubicacion.php">Ubicación</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/medidas.php">Medidas</a>
                </div>
            </li>
            <li class="nav-item dropdown active">
                <a class="nav-link dropdown-toggle" href="#" id="dropdown04" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa-solid fa-chart-bar fa-lg"></i>&nbsp;Reportes</a>
                <div class="dropdown-menu" aria-labelledby="dropdown05">
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/reportes_atenciones_medicas.php">Reporte de Atenciones</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/reportes_muestras.php">Reporte de Muestras</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/reporte_facturacion.php">Reporte de Facturación</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/reporte_facturacion_grupal.php">Facturas Grupales</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/reporte_pagos.php">Reporte de Pagos</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/reportes_sms.php">Reporte SMS</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/reportes_transito.php">Reporte Tránsito</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/historial_accesos.php">Historial de Accesos</a>
                </div>
            </li>
            <?php endif; ?>
        </ul>

        <!-- Menú de configuración y usuario -->
        <ul class="navbar-nav">
            <?php if ($_SESSION['type']==4): //CONTADOR GENERAL ?>
            <li class="nav-item dropdown active">
                <a class="nav-link dropdown-toggle" href="#" id="dropdown04" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa-solid fa-gears fa-lg"></i>&nbsp;Configuración</a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown05">
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/secuencia_facturacion.php">Secuencia Facturación</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/empresas.php">Empresa</a>
                </div>
            </li>
            <?php endif; ?>

            <?php if ($_SESSION['type']==1 || $_SESSION['type']==2): //Super Administrador y Administrador ?>
            <li class="nav-item dropdown active">
                <a class="nav-link dropdown-toggle" href="#" id="dropdown04" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa-solid fa-gears fa-lg"></i>&nbsp;Configuración</a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown05">
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/colaboradores.php">Colaboradores</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/users.php">Usuarios</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/config_varios.php">Varios</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/config_mails.php">Correo</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/plantillas.php">Plantillas</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/secuencia_facturacion.php">Secuencia Facturación</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/administrador_secuencias_muestras.php">Administrador de Secuencia Muestras</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/administrador_precios.php">Administrador de Precios</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/empresas.php">Empresa</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/categoria_muestras.php">Categoria Muestras</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/limite_muestras.php">Limite Muestras</a>
                </div>
            </li>
            <?php endif; ?>

            <?php if ($_SESSION['type']==3): //CAJA ?>
            <li class="nav-item dropdown active">
                <a class="nav-link dropdown-toggle" href="#" id="dropdown04" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa-solid fa-gears fa-lg"></i>&nbsp;Configuración</a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown05">
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/colaboradores.php">Colaboradores</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/administrador_secuencias_muestras.php">Administrador de Secuencia Muestras</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/administrador_precios.php">Administrador de Precios</a>
                    <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/categoria_muestras.php">Categoria Muestras</a>
                </div>
            </li>
            <?php endif; ?>

            <!-- Notificaciones y menú de usuario -->
            <li class="nav-item dropdown mx-1">
              <a class="nav-link dropdown-toggle position-relative" id="notification-bell" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="far fa-bell fa-lg"></i>
                  <span id="notification-count" class="position-absolute top-0 start-100 translate-middle badge bg-danger rounded-pill">0</span>
              </a>
              <div class="dropdown-menu dropdown-menu-right notification-dropdown" aria-labelledby="notification-bell">
                  <h6 class="dropdown-header d-flex justify-content-between align-items-center">
                      <span>Notificaciones</span>
                      <small><a href="<?php echo SERVERURL; ?>vistas/DetallesFacturacion.php">Ver todas</a></small>
                  </h6>
                  <a class="dropdown-item d-flex align-items-center py-2" href="<?php echo SERVERURL; ?>vistas/DetallesFacturacion.php?estado_factura=3">
                      <i class="fas fa-file-invoice mr-2 text-warning"></i>
                      <span class="flex-grow-1">Facturas pendientes</span>
                      <span id="notification-dropdown-count" class="badge bg-danger">0</span>
                  </a>
              </div>
          </li>

            <li class="nav-item dropdown active">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-user-circle fa-lg mr-1"></i>
                    <span id="saludo_sistema" class="d-none d-md-inline"><?php echo $_SESSION['user_name'] ?? ''; ?></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                    <a class="dropdown-item d-flex align-items-center" href="#" id="mostrar_cambiar_contraseña">
                        <i class="fas fa-key mr-2"></i>
                        <span>Modificar Contraseña</span>
                    </a>
                    <a class="dropdown-item d-flex align-items-center" href="<?php echo SERVERURL; ?>vistas/perfil.php">
                        <i class="fas fa-user mr-2"></i>
                        <span>Mi Perfil</span>
                    </a>
                    <a class="dropdown-item d-flex align-items-center" href="<?php echo SERVERURL; ?>vistas/DetallesFacturacion.php">
                        <i class="fas fa-file-invoice mr-2"></i>
                        <span>Detalles Facturacion</span>
                        <span id="badge-facturas-pendientes-dropdown" class="badge bg-danger ml-auto" style="display: none;">0</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item d-flex align-items-center" href="#" id="salir_sistema">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        <span>Cerrar sesión</span>
                    </a>
                </div>
            </li>
        </ul>
    </div>
</nav>

<?php if (SISTEMA_PRUEBA=="SI"): ?>
    <span class="container-fluid prueba-sistema">SISTEMA DE PRUEBA</span>
<?php endif; ?>