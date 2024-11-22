<!-- Navigation -->
    <nav class="navbar navbar-expand-md navbar-dark bg-primary fixed-top">
    <a class="navbar-brand" href="#">
        <a href="#">
            <img src="<?php echo SERVERURL; ?>img/logo.svg" class="logo" alt="" />
        </a>
    </a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarsExampleDefault">
    <ul class="navbar-nav mr-auto">
	  <?php
		 if ($_SESSION['type']==3){//CAJA
	  ?>
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
		     <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/reporte_pagos.php">Reporte de Pagos</a>
         <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/reporte_facturacion_grupal.php">Reporte de Facturación Grupal</a>
		     <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/reportes_muestras.php">Reporte de Muestras</a>
        </div>
      </li>
      <?php
	     }
	  ?>

	  <?php
		 if ($_SESSION['type']==4){//CONTADOR GENERAL
	  ?>
      <li class="nav-item dropdown active">
        <a class="nav-link dropdown-toggle" href="#" id="dropdown04" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa-solid fa-chart-bar fa-lg"></i>&nbsp;Reportes</a>
        <div class="dropdown-menu" aria-labelledby="dropdown05">
          <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/reporte_facturacion.php">Reporte de Facturación</a>
		  <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/reporte_pagos.php">Reporte de Pagos</a>
        </div>
      </li>
      <?php
	     }
	  ?>

	  <?php
		 if ($_SESSION['type']==6){//CLINICA/HOSPITALES
	  ?>
      <li class="nav-item dropdown active">
        <a class="nav-link dropdown-toggle" href="#" id="dropdown04" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa-solid fa-chart-bar fa-lg"></i>&nbsp;Reportes</a>
        <div class="dropdown-menu" aria-labelledby="dropdown05">
          <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/reportes_atenciones_medicas.php">Reporte de Atenciones</a>
          <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/reportes_muestras_medicos.php">Reporte de Muestras</a>
        </div>
      </li>
      <?php
	     }
	  ?>

	  <?php
		 if ($_SESSION['type']==1 || $_SESSION['type']==2){//Super Administrador y Administrador
	  ?>
      <li class="nav-item active active">
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
		      <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/reporte_facturacion_grupal.php">Reporte de Facturación Grupal</a>
		      <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/reporte_pagos.php">Reporte de Pagos</a>
          <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/reportes_sms.php">Reporte SMS</a>
          <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/reportes_transito.php">Reporte Tránsito</a>
          <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/historial_accesos.php">Historial de Accesos</a>
        </div>
      </li>
      <li class="nav-item dropdown active">
        <a class="nav-link dropdown-toggle" href="#" id="dropdown04" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-tools fa-lg"></i>&nbsp;Clinicare</a>
        <div class="dropdown-menu" aria-labelledby="dropdown05">
          <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/perfil.php">Perfil</a>
          <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/misFacturas.php">Mis Facturas</a>
        </div>
      </li>      
      
	  <?php
	     }
	  ?>
    </ul>
    <form class="form-inline my-2 my-lg-0">
	  <ul class="navbar-nav mr-auto">
		<?php
		 if ($_SESSION['type']==4){//CONTADOR GENRAL
	    ?>
		  <li class="nav-item dropdown active">
			<a class="nav-link dropdown-toggle" href="#" id="dropdown04" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa-solid fa-gears fa-lg"></i>&nbsp;Configuración</a>
			<div class="dropdown-menu" aria-labelledby="dropdown05">
			  <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/secuencia_facturacion.php">Secuencia Facturación</a>
			  <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/empresas.php">Empresa</a>
			</div>
		  </li>
		  <?php
			 }
		  ?>

		<?php
		 if ($_SESSION['type']==1 || $_SESSION['type']==2){//Super Administrador y Administrador
	    ?>
		  <li class="nav-item dropdown active">
			<a class="nav-link dropdown-toggle" href="#" id="dropdown04" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa-solid fa-gears fa-lg"></i>&nbsp;Configuración</a>
			<div class="dropdown-menu" aria-labelledby="dropdown05">
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
		  <?php
			 }
		  ?>

		<?php
		 if ($_SESSION['type']==3){ //CAJA
	    ?>
		  <li class="nav-item dropdown active">
			<a class="nav-link dropdown-toggle" href="#" id="dropdown04" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa-solid fa-gears fa-lg"></i>&nbsp;Configuración</a>
			<div class="dropdown-menu" aria-labelledby="dropdown05">
			  <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/colaboradores.php">Colaboradores</a>
			  <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/administrador_secuencias_muestras.php">Administrador de Secuencia Muestras</a>
			  <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/administrador_precios.php">Administrador de Precios</a>
        <a class="dropdown-item" href="<?php echo SERVERURL; ?>vistas/categoria_muestras.php">Categoria Muestras</a>
			</div>
		  </li>
		  <?php
			 }
		  ?>

		  <li class="nav-item dropdown active">
			<a class="nav-link dropdown-toggle" href="#" id="dropdown04" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span id="saludo_sistema">></span></a>
			<div class="dropdown-menu" aria-labelledby="dropdown05">
			  <a class="dropdown-item" href="#" id="mostrar_cambiar_contraseña">Modificar Contraseña</a>
			  <a class="dropdown-item" href="#" id="salir_sistema">Sign Out</a>
			</div>
		  </li>
	  </ul>
      <!--<input class="form-control mr-sm-2" type="text" placeholder="Search" aria-label="Search">
      <button class="btn btn-secondary my-2 my-sm-0" type="submit">Search</button>-->
    </form>
  </div>
</nav>
<br/>

<?php
  if (SISTEMA_PRUEBA=="SI"){ //CAJA
?>
    <span class="container-fluid prueba-sistema">SISTEMA DE PRUEBA</span>
<?php
  }
?>
