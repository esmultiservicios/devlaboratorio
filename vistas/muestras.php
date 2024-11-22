<?php
session_start(); 
include "../php/funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

if( isset($_SESSION['colaborador_id']) == false ){
   header('Location: login.php'); 
}    

$_SESSION['menu'] = "Muestras";

if(isset($_SESSION['colaborador_id'])){
 $colaborador_id = $_SESSION['colaborador_id'];  
}else{
   $colaborador_id = "";
}

$type = $_SESSION['type'];

$nombre_host = gethostbyaddr($_SERVER['REMOTE_ADDR']);//HOSTNAME	
$fecha = date("Y-m-d H:i:s"); 
$comentario = "Ingreso al Modulo de Muestras";   

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
    <title>Muestras :: <?php echo $empresa; ?></title>
	<?php include("script_css.php"); ?>
</head>
<body>
   <!--Ventanas Modales-->
   <!-- Small modal -->  
   <?php include("templates/modals.php"); ?>    

<!--INICIO MODAL PARA INGRESO DE MUESTRAS-->
<div class="modal fade" id="modal_muestras">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Muestras</h4>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
        </div>
        <div class="modal-body">		
			<form class="FormularioAjax" id="formularioMuestras" data-async data-target="#rating-modal" action="" method="POST" data-form="" autocomplete="off" enctype="multipart/form-data">			
				<div class="form-row">
					<div class="col-md-12 mb-3">
					   <input type="hidden" id="pacientes_empresa_id" name="pacientes_empresa_id" class="form-control"/>
					   <input type="hidden" id="pacientes_id" name="pacientes_id" class="form-control" />
					   <input type="hidden" id="muestras_id" name="muestras_id" class="form-control"/>	
						<div class="input-group mb-3">
							<input type="text" required readonly id="pro" name="pro" class="form-control"/>
							<div class="input-group-append">				
								<span class="input-group-text"><div class="sb-nav-link-icon"></div><i class="fa fa-plus-square"></i></span>
							</div>
						</div>	 
					</div>							
				</div>
				<div class="form-row">
					<div class="col-md-2 mb-3">
						<label for="tipo_paciente_muestra">Tipo <span class="priority">*<span/></label>
						<select id="tipo_paciente_muestra" name="tipo_paciente_muestra" class="custom-select" data-toggle="tooltip" data-placement="top" title="Tipo Paciente">
							<option value="">Seleccione</option>
						</select>																			   
					</div>
					<div class="col-md-5 mb-3" id="cliente_muestra_grupo">
					  <label for="paciente_consulta">Empresa <span class="priority">*<span/></label>
					  <div class="input-group mb-3">
						  <select id="paciente_consulta" name="paciente_consulta" class="custom-select" data-toggle="tooltip" data-placement="top" title="Empresas">
							<option value="">Seleccione</option>
						  </select>
						  <input type="text" name="empresa" id="empresa" class="form-control">	
						  <div class="input-group-append" id="buscar_paciente_consulta_muestras">				
							<a data-toggle="modal" href="#" class="btn btn-outline-success"><div class="sb-nav-link-icon"></div><i class="fas fa-search fa-lg"></i></a>
						  </div>
					   </div>						   
					</div>
					<div class="col-md-5 mb-3" style="display:none" id="pacientes_muestra_grupo">
						<label>Paciente <span class="priority">*<span/></label>
						<div class="input-group mb-3">
						  <select id="paciente_muestras" name="paciente_muestras" class="custom-select" data-toggle="tooltip" data-placement="top" title="Paciente"></select>
						  <input type="text" name="paciente" id="paciente" class="form-control">
						  <div class="input-group-append" id="buscar_paciente_muestras">				
							<a data-toggle="modal" href="#" class="btn btn-outline-success"><div class="sb-nav-link-icon"></div><i class="fas fa-search fa-lg"></i></a>
						  </div>
						</div>
					</div>	
					<div class="col-md-5 mb-3" id="servicios_muestra_grupo">
						<label>Servicio</label>
						<div class="input-group mb-3">
						  <select id="servicio_muestras" name="servicio_muestras" class="custom-select" data-toggle="tooltip" data-placement="top" title="Servicio"></select>
						  <div class="input-group-append" id="buscar_servicios_muestras">				
							<a data-toggle="modal" href="#" class="btn btn-outline-success"><div class="sb-nav-link-icon"></div><i class="fas fa-search fa-lg"></i></a>
						  </div>
						</div>
					</div>						
				</div>	
				<div class="form-row">	
					<div class="col-md-3 mb-3">
					  <label>Referencia </label>
					  <input type="text" id="referencia" name="referencia" class="form-control" placeholder="Referencia">
					</div>										
					<div class="col-md-3 mb-3">
						<label>Tipo Muestra <span class="priority">*<span/></label>
						<div class="input-group mb-3">
						  <select id="tipo_muestra_id" name="tipo_muestra_id" class="custom-select" data-toggle="tooltip" data-placement="top" title="Tipo Muestra" required></select>
						  <div class="input-group-append" id="buscar_tipo_muestras_id">				
							<a data-toggle="modal" href="#" class="btn btn-outline-success"><div class="sb-nav-link-icon"></div><i class="fas fa-search fa-lg"></i></a>
						  </div>
						</div>
					</div>				
					<div class="col-md-3 mb-3">
					  <label>Remitente <span class="priority">*<span/></label>
						<div class="input-group mb-3">
						  <select id="remitente" name="remitente" class="custom-select" data-toggle="tooltip" data-placement="top" title="Servicio" required></select>
						  <div class="input-group-append" id="buscar_remitentes_muestras">				
							<a data-toggle="modal" href="#" class="btn btn-outline-success"><div class="sb-nav-link-icon"></div><i class="fas fa-search fa-lg"></i></a>
						  </div>
						</div>
					</div>	
					<div class="col-md-3 mb-3">
					  <label>Hospital/Cínica <span class="priority">*<span/></label>
					  <div class="input-group mb-3">
						  <select id="hospital_clinica" name="hospital_clinica" class="custom-select" data-toggle="tooltip" data-placement="top" title="Hospitales o Clínicas" required>
							<option value="">Seleccione</option>
						  </select>
						  <div class="input-group-append" id="buscar_hospital_clinica">				
							<a data-toggle="modal" href="#" class="btn btn-outline-success"><div class="sb-nav-link-icon"></div><i class="fas fa-search fa-lg"></i></a>
						  </div>
					   </div>	
					</div>						
				</div>
				<div class="form-row">					
					<div class="col-md-12 mb-3">
					  <label>Sitio Preciso de la Muestra</label>
					  <input type="text" name="sitio_muestra" id="sitio_muestra" maxlength="250" oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" class="form-control">
					</div>					
				</div>				
				<div class="form-row">					
					<div class="col-md-12 mb-3">
					  <label>Diagnostico Clínico</label>
					  <input type="text" name="diagonostico_muestra" id="diagonostico_muestra" maxlength="250" oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" class="form-control">
					</div>					
				</div>
				<div class="form-row">					
					<div class="col-md-12 mb-3">
					  <label>Material Enviado</label>
					  <input type="text" name="material_muestra" id="material_muestra" maxlength="250" oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" class="form-control">
					</div>					
				</div>	
				<div class="form-row">					
					<div class="col-md-12 mb-3">
					  <label>Datos Clinicos Relevantes</label>
					  <input type="text" name="datos_relevantes_muestras" id="datos_relevantes_muestras" maxlength="250" oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" class="form-control">
					</div>					
				</div>
	
				<div class="form-row">					
					<div class="col-md-4 mb-3">
						<label for="categoria_muestras">Categoría</label>
						<div class="input-group mb-3">
							<select id="categoria_muestras" name="categoria_muestras" class="custom-select" data-toggle="tooltip" data-placement="top" title="Pacientes">
								<option value="">Seleccione</option>
							</select>
							<div class="input-group-append" id="buscar_categoria_muestras" style="display: none;">				
								<a data-toggle="modal" href="#" class="btn btn-outline-success"><div class="sb-nav-link-icon"></div><i class="fas fa-search fa-lg"></i></a>
							</div>
						</div>							   
					</div>						
				</div>				
			
				 <div class="form-check-inline">
					 <p for="end" class="col-sm-10 form-check-label">Mostrar Datos Clínicos</p>
					 <div class="col-sm-2">
						<input type="checkbox" class="form-check-input" id="mostrar_datos_clinicos" name="mostrar_datos_clinicos" value = "1">
					 </div>					 
				 </div>					
			</form>
        </div>		
		<div class="modal-footer">
			<button class="btn btn-primary ml-2" type="submit" id="reg_muestras" form="formularioMuestras"><div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar</button>
			<button class="btn btn-warning ml-2" type="submit" id="edi_muestras" form="formularioMuestras"><div class="sb-nav-link-icon"></div><i class="fas fa-edit fa-lg"></i> Editar</button>
			<button class="btn btn-danger ml-2" type="submit" id="delete_muestras" form="formularioMuestras"><div class="sb-nav-link-icon"></div><i class="fa fa-trash"></i> Eliminar</button>			
		</div>			
      </div>
    </div>
</div>
<!--FIN MODAL PARA INGRESO DE MUESTRAS-->

<!--INICIO MODAL PACIENTES MAIN MUESTRAS-->
<div class="modal fade" id="modal_busqueda_pacientes_main_muetras" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Búsqueda de Pacientes</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
			<form id="formulario_busqueda_pacientes_main_muestras">		
				<div class="table-responsive">
					<table id="dataTablePacientes_main_muestras" class="table table-striped table-condensed table-hover" style="width:100%">
						<thead align="center">
							<tr>
								<th>Seleecionar</th>
								<th>Paciente</th>
								<th>Identidad</th>
								<th>Expediente</th>
								<th>Correo</th>						
							</tr>
						</thead>
					</table>  
				</div>			
			  </div>															  
			</form>
      </div>
    </div>
  </div>
</div>
<!--FIN MODAL PACIENTES-->

<!--INICIO MODAL CLIENTES-->
<div class="modal fade" id="modal_busqueda_clientes_muestras" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Búsqueda de Pacientes</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
			<form id="formulario_busqueda_clientes_muestras">		
				<div class="table-responsive">
					<table id="dataTableClinetes_muestras" class="table table-striped table-condensed table-hover" style="width:100%">
						<thead align="center">
							<tr>
								<th>Seleecionar</th>
								<th>Paciente</th>
								<th>Identidad</th>
								<th>Expediente</th>
								<th>Correo</th>						
							</tr>
						</thead>
					</table>  
				</div>			
			  </div>															  
			</form>
      </div>
    </div>
  </div>
</div>
<!--FIN MODAL PACIENTES-->

<!--INICIO MODAL PACIENTES-->
<div class="modal fade" id="modal_busqueda_pacientes_muestras" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Búsqueda de Pacientes</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
			<form id="formulario_busqueda_pacientes_muestras">		
				<div class="table-responsive">
					<table id="dataTablePacientes_muestras" class="table table-striped table-condensed table-hover" style="width:100%">
						<thead align="center">
							<tr>
								<th>Seleecionar</th>
								<th>Paciente</th>
								<th>Identidad</th>
								<th>Expediente</th>
								<th>Correo</th>						
							</tr>
						</thead>
					</table>  
				</div>			
			  </div>															  
			</form>
      </div>
    </div>
  </div>
</div>
<!--FIN MODAL PACIENTES-->

<div class="modal fade" id="mensaje_show" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
	<div class="modal-content">
		<div class="modal-header">
		<h5 class="modal-title">Información Muestras Clientes</h5>
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
		<button class="btn btn-success ml-2" type="submit" id="okay" data-dismiss="modal"><div class="sb-nav-link-icon"></div><i class="fas fa-thumbs-up fa-lg"></i> Okay</button>	
		<button class="btn btn-danger ml-2" type="submit" id="bad" data-dismiss="modal"><div class="sb-nav-link-icon"></div><i class="fas fa-thumbs-up fa-lg"></i> Okay</button>				
		</div>	  
	</div>
	</div>
</div>

	<!--INICIO MODAL-->
    <?php include("modals/modals.php");?>	
		
   <!--Fin Ventanas Modales-->
	<!--MENU-->	  
       <?php include("templates/menu.php"); ?>
    <!--FIN MENU--> 
	
<br><br><br>
<div class="container-fluid">
	<ol class="breadcrumb mt-2 mb-4">
		<li class="breadcrumb-item" id="acciones_atras"><a id="ancla_volver" class="breadcrumb-link" href="#">Muestras</a></li>
		<li class="breadcrumb-item active" id="acciones_factura"><span id="label_acciones_factura"></span></li>
	</ol>
	
	<div id="main_facturacion">
		<form class="form-inline" id="form_main">  
		  <div class="form-group mr-1">
			<div class="input-group">				
				<div class="input-group-append">				
					<span class="input-group-text"><div class="sb-nav-link-icon"></div>Cliente</span>
				</div>
				<select id="tipo_paciente_grupo" name="tipo_paciente_grupo" class="custom-select" style="width:100px;" data-toggle="tooltip" data-placement="top" title="Empresa">   				   		 
					<option value="">Tipo</option>
				</select>		
			</div>
		  </div>
		  <div class="form-group mr-1">
			<div class="input-group">
			  <select id="pacientesIDGrupo" name="pacientesIDGrupo" class="custom-select" style="width:170px;" data-toggle="tooltip" data-placement="top" title="Pacientes" required>
				<option value="">Cliente</option>
			  </select>
			  <div class="input-group-append" id="buscar_cliente_muestras">				
				<a data-toggle="modal" href="#" class="btn btn-outline-success"><div class="sb-nav-link-icon"></div><i class="fas fa-search fa-lg"></i></a>
			  </div>
			</div>			 
		  </div>
		  <div class="form-group mr-1">
		  	<div class="input-group">				
				<div class="input-group-append">				
					<span class="input-group-text"><div class="sb-nav-link-icon"></div>Muestra</span>
				</div>
				<select id="tipo_muestra" name="tipo_muestra" class="custom-select" style="width:150px;" data-toggle="tooltip" data-placement="top" title="Tipo Muestra">
				  <option value="">Tipo Muestra</option>
				 </select>		
			</div>		 			 
		  </div>		  
		  <div class="form-group mr-1">
			<div class="input-group">				
				<div class="input-group-append">				
					<span class="input-group-text"><div class="sb-nav-link-icon"></div>Inicio</span>
				</div>
				<input type="date" required="required" id="fecha_i" name="fecha_i" style="width:160px;" value="<?php 
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
			 <input type="text" placeholder="Buscar por: Nombre, Identidad, Tipo de Muestra" data-toggle="tooltip" data-placement="top" title="Buscar por: Nombre, Identidad, Tipo de Muestra" id="bs_regis" autofocus class="form-control" size="42"/>
		  </div>
		  <div class="form-group">
			<button class="btn btn-primary ml-1" type="submit" id="nuevo_registro"><div class="sb-nav-link-icon"></div><i class="fas fa-vials fa-lg"></i> Crear</button>
		  </div>	   
		</form>	
		<hr/>   
		<div class="form-group">
		  <div class="col-sm-12">
			<div class="registros overflow-auto" id="agrega-registros"></div>
		   </div>		   
		</div>
		<nav aria-label="Page navigation example">
			<ul class="pagination justify-content-center" id="pagination"></ul>
		</nav>
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
		include "../js/myjava_muestras.php"; 		
		include "../js/select.php"; 	
		include "../js/functions.php"; 
		include "../js/myjava_cambiar_pass.php"; 		
	?>	
	  
</body>
</html>