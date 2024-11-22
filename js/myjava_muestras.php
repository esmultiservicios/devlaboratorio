<script>
$('#form_main #fecha_i').on('change',function(){
  pagination(1);
});

$('#form_main #fecha_f').on('change',function(){
  pagination(1);
});

$('#form_main #bs_regis').on('keyup',function(){
  pagination(1);
});

$('#form_main #pacientesIDGrupo').on('change',function(){
  pagination(1);
});

$('#form_main #tipo_muestra').on('change',function(){
  pagination(1);
});

/*INICIO DE FUNCIONES PARA ESTABLECER EL FOCUS PARA LAS VENTANAS MODALES*/
$(document).ready(function(){
	$('.footer').show();
    $('.footer1').hide();

	empresa();
	puesto();
	getEstatus();

    $("#modal_busqueda_colaboradores").on('shown.bs.modal', function(){
        $(this).find('#formulario_busqueda_coloboradores #buscar').focus();
    });

    $("#modal_busqueda_hospitales").on('shown.bs.modal', function(){
        $(this).find('#formulario_busqueda_hospitales #buscar').focus();
    });

    $("#modal_busqueda_tipo_mmuestra").on('shown.bs.modal', function(){
        $(this).find('#formulario_busqueda_tipo_mmuestra #buscar').focus();
    });
	
	$("#modal_pacientes").on('shown.bs.modal', function(){
		$(this).find('#formulario_pacientes #name').focus();
	});

	$("#registrar_colaboradores").on('shown.bs.modal', function(){
		$(this).find('#formulario_colaboradores #nombre').focus();
	});

	$("#modalHospitales").on('shown.bs.modal', function(){
		$(this).find('#formularioHospitales #hospitales').focus();
	});

	$("#modal_busqueda_pacientes_main_muetras").on('shown.bs.modal', function(){
		$(this).find('#formulario_busqueda_pacientes_main_muestras #buscar').focus();
	});	

	$("#modal_busqueda_clientes_muestras").on('shown.bs.modal', function(){
		$(this).find('#formulario_busqueda_clientes_muestras #buscar').focus();
	});		

	$("#modal_busqueda_pacientes_muestras").on('shown.bs.modal', function(){
		$(this).find('#formulario_busqueda_pacientes_muestras #buscar').focus();
	});			
});
/*FIN DE FUNCIONES PARA ESTABLECER EL FOCUS PARA LAS VENTANAS MODALES*/

$(document).ready(function() {
	funciones()
	pagination(1);
	getServicio();
	listar_servicios_factura_buscar();
	listar_productos_facturas_buscar();
	listar_pacientes_buscar();
	listar_muestras_clientes_buscar();
	listar_muestras_pacientes_buscar();
});

function funciones(){
	getUbicacion();
	getPacientes();
	getPacientes_clientes();
	getHospitalClinica();
	getRemitente();
	getServicioMuestras();
	getTipoMuestra();
	getTipoPacienteGrupo();
	getTipoPacienteGrupoMuestras();
	getCategoriaMuestra();
}

$('#form_main #nuevo_registro').on('click', function(e){
	e.preventDefault();
	agregarMuestras();
	return false;
});

function agregarMuestras(){
	if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
		funciones();
		$('#formularioMuestras').attr({ 'data-form': 'save' });
		$('#formularioMuestras').attr({ 'action': '<?php echo SERVERURL; ?>php/muestras/agregarMuestras.php' });
		$('#reg_muestras').show();
		$('#edi_muestras').hide();
		$('#delete_muestras').hide();
		$('#formularioMuestras')[0].reset();
		$('#formularioMuestras #pro').val('Registro');
		$("#formularioMuestras #fecha").attr('readonly', false);

		$('#formularioMuestras #buscar_paciente_consulta_muestras').show();
		$('#formularioMuestras #buscar_paciente_muestras').show();
		$('#formularioMuestras #buscar_servicios_muestras').show();
		$('#formularioMuestras #buscar_tipo_muestras_id').show();
		$('#formularioMuestras #buscar_remitentes_muestras').show();
		$('#formularioMuestras #buscar_hospital_clinica').show();

		$('#formularioMuestras #empresa').hide();
		$('#formularioMuestras #paciente').hide();

		//HABILITAR OBJETOS
		$('#formularioMuestras #paciente_consulta').attr("disabled", false);
 		$('#formularioMuestras #fecha').attr("readonly", false);
		$('#formularioMuestras #diagonostico_muestra').attr("readonly", false);
		$('#formularioMuestras #material_muestra').attr("readonly", false);
		$('#formularioMuestras #datos_relevantes_muestras').attr("readonly", false);
		$('#formularioMuestras #mostrar_datos_clinicos').attr("readonly", false);
		$('#formularioMuestras #remitente').attr("disabled", false);
		$('#formularioMuestras #hospital_clinica').attr("disabled", false);
		$('#formularioMuestras #tipo_muestra_id').attr("disabled", false);
		$('#formularioMuestras #servicio_muestras').attr("disabled", false);
	    $('#formularioMuestras #sitio_muestra').attr("readonly", false);
		$('#formularioMuestras #fecha').attr("readonly", false);
		$('#formularioMuestras #paciente_consulta').attr("disabled", false);
		$('#formularioMuestras #tipo_muestra_id').attr("disabled", false);
		$('#formularioMuestras #paciente_muestras').attr("disabled", false);
		$('#formularioMuestras #servicio_muestras').attr("disabled", false);
		$('#formularioMuestras #remitente').attr("disabled", false);
		$('#formularioMuestras #hospital_clinica').attr("disabled", false);
		$('#formularioMuestras #categoria_muestras').attr("disabled", false);

		 $('#modal_muestras').modal({
			show:true,
			keyboard: false,
			backdrop:'static'
		});
	}else{
		swal({
			title: "Acceso Denegado",
			text: "No tiene permisos para ejecutar esta acción",
			type: "error",
			confirmButtonClass: 'btn-danger'
		});
	}
}

function editarRegistro(pacientes_id, muestras_id){
	var url = '<?php echo SERVERURL; ?>php/muestras/editarMuestras.php';
	$('#formularioMuestras')[0].reset();
	$('#formularioMuestras #pacientes_empresa_id').val(pacientes_id);
	$('#formularioMuestras #muestras_id').val(muestras_id);

	$.ajax({
		type:'POST',
		url:url,
		data:$('#formularioMuestras').serialize(),
		success: function(registro){
			var valores = eval(registro);
			$('#formularioMuestras').attr({ 'data-form': 'update' });
			$('#formularioMuestras').attr({ 'action': '<?php echo SERVERURL; ?>php/muestras/modificarMuestras.php' });
			$('#reg_muestras').hide();
			$('#edi_muestras').show();
			$('#delete_muestras').hide();	
			$('#formularioMuestras #empresa').val(valores[0]);
			$('#formularioMuestras #paciente').val(valores[1]);
			$('#formularioMuestras #diagonostico_muestra').val(valores[2]);
			$('#formularioMuestras #material_muestra').val(valores[3]);
			$('#formularioMuestras #datos_relevantes_muestras').val(valores[4]);
			$('#formularioMuestras #remitente').val(valores[6]);
			$('#formularioMuestras #hospital_clinica').val(valores[7]);
			$('#formularioMuestras #servicio_muestras').val(valores[8]);
			$('#formularioMuestras #sitio_muestra').val(valores[9]);
			$('#formularioMuestras #tipo_muestra_id').val(valores[10]);
			$('#formularioMuestras #categoria_muestras').val(valores[11]);
			$('#formularioMuestras #tipo_paciente_muestra').val(valores[12]);

		    if(getTipoPaciente(pacientes_id) == 2){
			   $('#formularioMuestras #cliente_muestra_grupo').show();
			   $('#formularioMuestras #pacientes_muestra_grupo').show();			   
			   $('#formularioMuestras #servicios_muestra_grupo').hide();

			   $('#formularioMuestras #empresa').show();
			   $('#formularioMuestras #paciente').show();

			   $('#formularioMuestras #paciente_consulta').hide();
			   $('#formularioMuestras #paciente_muestras').hide();
			   
			   $('#formularioMuestras #tipo_paciente_muestra').attr("disabled", true);
			   $('#formularioMuestras #empresa').attr("disabled", true);
			   $('#formularioMuestras #paciente').attr("disabled", true);
			   $('#formularioMuestras #pacientes_id').val(valores[13]);
		    }else{
			   $('#formularioMuestras #cliente_muestra_grupo').hide();
			   $('#formularioMuestras #pacientes_muestra_grupo').show();
			   $('#formularioMuestras #servicios_muestra_grupo').show();		   

			   $('#formularioMuestras #empresa').hide();
			   $('#formularioMuestras #paciente').show();

			   $('#formularioMuestras #paciente_consulta').hide();
			   $('#formularioMuestras #paciente_muestras').hide();

			   $('#formularioMuestras #tipo_paciente_muestra').attr("disabled", true);
			   $('#formularioMuestras #empresa').attr("disabled", true);
			   $('#formularioMuestras #paciente').attr("disabled", true);			   
		    }

			if(valores[5] == 1){
				$('#formularioMuestras #mostrar_datos_clinicos').prop('checked', true);
			}else{
				$('#formularioMuestras #mostrar_datos_clinicos').prop('checked', false);
			}

			$('#formularioMuestras #buscar_paciente_consulta_muestras').hide();
			$('#formularioMuestras #buscar_paciente_muestras').hide();
			$('#formularioMuestras #buscar_servicios_muestras').hide();
			$('#formularioMuestras #buscar_tipo_muestras_id').hide();
			$('#formularioMuestras #buscar_remitentes_muestras').hide();
			$('#formularioMuestras #buscar_hospital_clinica').hide();

			//HABILITAR OBJETOS
			$('#formularioMuestras #diagonostico_muestra').attr("readonly", false);
			$('#formularioMuestras #material_muestra').attr("readonly", false);
			$('#formularioMuestras #datos_relevantes_muestras').attr("readonly", false);
			$('#formularioMuestras #mostrar_datos_clinicos').attr("readonly", false);
			$('#formularioMuestras #remitente').attr("disabled", false);
			$('#formularioMuestras #hospital_clinica').attr("disabled", false);
			$('#formularioMuestras #servicio_muestras').attr("disabled", false);
			$('#formularioMuestras #sitio_muestra').attr("readonly", false);
			$('#formularioMuestras #servicio_muestras').attr("disabled", false);
			$('#formularioMuestras #remitente').attr("disabled", false);
			$('#formularioMuestras #hospital_clinica').attr("disabled", false);
			$('#formularioMuestras #paciente_muestras').attr("disabled", false);

			//DESHABILITAR OBJETOS
			$('#formularioMuestras #paciente_consulta').attr("disabled", false);
			$('#formularioMuestras #tipo_muestra_id').attr("disabled", true);
			$('#formularioMuestras #categoria_muestras').attr("disabled", true);

			$('#modal_muestras #pro').val("Editar");
			$('#modal_muestras').modal({
				show:true,
				keyboard: false,
				backdrop:'static'
			});
		}
	});
}

function eliminarRegistro(pacientes_id, muestras_id){
	var url = '<?php echo SERVERURL; ?>php/muestras/editarMuestras.php';
	$('#formularioMuestras')[0].reset();
	$('#formularioMuestras #pacientes_id').val(pacientes_id);
	$('#formularioMuestras #muestras_id').val(muestras_id);

	$.ajax({
		type:'POST',
		url:url,
		data:$('#formularioMuestras').serialize(),
		success: function(registro){
			var valores = eval(registro);
			$('#formularioMuestras').attr({ 'data-form': 'delete' });
			$('#formularioMuestras').attr({ 'action': '<?php echo SERVERURL; ?>php/muestras/eliminarMuestras.php' });
			$('#reg_muestras').hide();
			$('#edi_muestras').hide();
			$('#delete_muestras').show();
			$('#formularioMuestras #paciente_consulta').val(valores[0]);
			$('#formularioMuestras #fecha').val(valores[1]);
			$('#formularioMuestras #diagonostico_muestra').val(valores[2]);
			$('#formularioMuestras #material_muestra').val(valores[3]);
			$('#formularioMuestras #datos_relevantes_muestras').val(valores[4]);
			$('#formularioMuestras #remitente').val(valores[6]);
			$('#formularioMuestras #hospital_clinica').val(valores[7]);
			$('#formularioMuestras #servicio_muestras').val(valores[8]);
			$('#formularioMuestras #sitio_muestra').val(valores[9]);
			$('#formularioMuestras #categoria_muestras').val(valores[12]);

			if(valores[5] == 1){
				$('#formularioMuestras #mostrar_datos_clinicos').prop('checked', true);
			}else{
				$('#formularioMuestras #mostrar_datos_clinicos').prop('checked', false);
			}

			//DESHABILITAR OBJETOS
			$('#formularioMuestras #paciente_consulta').attr("disabled", true);
			$('#formularioMuestras #fecha').attr("readonly", true);
			$('#formularioMuestras #diagonostico_muestra').attr("readonly", true);
			$('#formularioMuestras #material_muestra').attr("readonly", true);
			$('#formularioMuestras #datos_relevantes_muestras').attr("readonly", true);
			$('#formularioMuestras #mostrar_datos_clinicos').attr("readonly", true);
			$('#formularioMuestras #remitente').attr("disabled", true);
			$('#formularioMuestras #hospital_clinica').attr("disabled", true);
			$('#formularioMuestras #servicio_muestras').attr("disabled", true);
			$('#formularioMuestras #sitio_muestra').attr("readonly", true);
			$('#formularioMuestras #tipo_muestra_id').attr("disabled", true);
			$('#formularioMuestras #categoria_muestras').attr("disabled", true);

			$('#modal_muestras #pro').val("Eliminar");
			$('#modal_muestras').modal({
				show:true,
				keyboard: false,
				backdrop:'static'
			});
		}
	});
}

function getUbicacion(){
    var url = '<?php echo SERVERURL; ?>php/almacen/getUbicacion.php';

	$.ajax({
        type: "POST",
        url: url,
	    async: true,
        success: function(data){
		    $('#formulario_almacen #ubicacion').html("");
			$('#formulario_almacen #ubicacion').html(data);
		}
     });
}

//INICIO FUNCION PARA OBTENER LOS HOSPITALES/CLINICAS
function getHospitalClinica(){
    var url = '<?php echo SERVERURL; ?>php/muestras/getHospitales.php';

	$.ajax({
        type: "POST",
        url: url,
	    async: true,
        success: function(data){
		    $('#formularioMuestras #hospital_clinica').html("");
			$('#formularioMuestras #hospital_clinica').html(data);
        }
     });
}
//FIN FUNCION PARA OBTENER LOS HOSPITALES/CLINICAS

//INICIO IMPRIMIR FACTURACION
function printMuestra(muestras_id){
	var url = '<?php echo SERVERURL; ?>php/muestras/generaMuestra.php?muestras_id='+muestras_id;
    window.open(url);
}
//FIN IMPRIMIR FACTURACION

$('#formularioMuestras #buscar_servicios_muestras').on('click', function(e){
	listar_servicios_buscar();
	 $('#modal_busqueda_servicios').modal({
		show:true,
		keyboard: false,
		backdrop:'static'
	});
});

$('#formularioMuestras #buscar_remitentes_muestras').on('click', function(e){
	listar_colaboradores_buscar();
	 $('#modal_busqueda_colaboradores').modal({
		show:true,
		keyboard: false,
		backdrop:'static'
	});
});

$('#formularioMuestras #buscar_hospital_clinica').on('click', function(e){
	listar_hospitales_buscar();
	 $('#modal_busqueda_hospitales').modal({
		show:true,
		keyboard: false,
		backdrop:'static'
	});
});

var listar_servicios_buscar = function(){
	var table_servicios_buscar = $("#dataTableServicios").DataTable({
		"destroy":true,
		"ajax":{
			"method":"POST",
			"url":"<?php echo SERVERURL; ?>php/muestras/getServiciosTable.php"
		},
		"columns":[
			{"defaultContent":"<button class='view btn btn-primary'><span class='fas fa-copy'></span></button>"},
			{"data":"nombre"}
		],
		"pageLength" : 5,
        "lengthMenu": lengthMenu,
		"stateSave": true,
		"bDestroy": true,
		"language": idioma_español,
	});
	table_servicios_buscar.search('').draw();
	$('#buscar').focus();

	view_servicios_busqueda_dataTable("#dataTableServicios tbody", table_servicios_buscar);
}

var view_servicios_busqueda_dataTable = function(tbody, table){
	$(tbody).off("click", "button.view");
	$(tbody).on("click", "button.view", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();
		$('#formularioMuestras #servicio_muestras').val(data.servicio_id);
		$('#modal_busqueda_servicios').modal('hide');
	});
}

var listar_colaboradores_buscar = function(){
	var table_colaboradores_buscar = $("#dataTableColaboradores").DataTable({
		"destroy":true,
		"ajax":{
			"method":"POST",
			"url":"<?php echo SERVERURL; ?>php/muestras/getColaboradoresTabla.php"
		},
		"columns":[
			{"defaultContent":"<button class='view btn btn-primary'><span class='fas fa-copy'></span></button>"},
			{"data":"colaborador"},
			{"data":"identidad"},
			{"data":"puesto"},
			{"defaultContent":"<button class='editar btn btn-warning'><span class='fas fa-edit'></span></button>"}
		],
		"pageLength" : 5,
        "lengthMenu": lengthMenu,
		"stateSave": true,
		"bDestroy": true,
		"language": idioma_español,
		"dom": dom,
		"buttons":[
			{
				text:      '<i class="fas fa-sync-alt fa-lg"></i> Actualizar',
				titleAttr: 'Actualizar Hospitales',
				className: 'actualizar btn btn-secondary',
				action: 	function(){
					listar_colaboradores_buscar();
				}
			},
			{
				text:      '<i class="fas fas fa-plus fa-lg crear"></i> Crear',
				titleAttr: 'Agregar Hospitales',
				className: 'crear btn btn-primary',
				action: 	function(){
					modal_colaboradores();
				}
			}
		]
	});
	table_colaboradores_buscar.search('').draw();
	$('#buscar').focus();

	view_colaboradores_busqueda_dataTable("#dataTableColaboradores tbody", table_colaboradores_buscar);
	editar_colaboradores_busqueda_dataTable("#dataTableColaboradores tbody", table_colaboradores_buscar);	
}

var view_colaboradores_busqueda_dataTable = function(tbody, table){
	$(tbody).off("click", "button.view");
	$(tbody).on("click", "button.view", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();
		$('#formularioMuestras #remitente').val(data.colaborador_id);
		$('#modal_busqueda_colaboradores').modal('hide');
	});
}

var editar_colaboradores_busqueda_dataTable = function(tbody, table){
	$(tbody).off("click", "button.editar");
	$(tbody).on("click", "button.editar", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();
		var url = '<?php echo SERVERURL; ?>php/colaboradores/editar.php';	
		var colaborador_id = data.colaborador_id;
		
		$.ajax({
			type:'POST',
			url:url,
			data:'id='+colaborador_id,
			success: function(registro){
				var datos = eval(registro);
				$('#formulario_colaboradores #pro').val('Edicion');
				$('#formulario_colaboradores #id-registro').val(colaborador_id);
				$('#formulario_colaboradores #nombre').val(datos[0]);	
				$('#formulario_colaboradores #apellido').val(datos[1]);
                $('#formulario_colaboradores #empresa').val(datos[2]);							
                $('#formulario_colaboradores #puesto').val(datos[3]);	
                $('#formulario_colaboradores #identidad').val(datos[4]);
                $('#formulario_colaboradores #estatus').val(datos[5]);
				
				$('#formulario_colaboradores').attr({ 'data-form': 'update' }); 
				$('#formulario_colaboradores').attr({ 'action': '<?php echo SERVERURL; ?>php/colaboradores/agregar_edicion.php' });	
				
				$('#reg_colaboradores').hide();
				$('#edi_colaboradores').show();				
				
				$('#registrar_colaboradores').modal({
					show:true,
					keyboard: false,
					backdrop:'static'
				});
			}
		});			
	});
}

var listar_hospitales_buscar = function(){
	var table_hospitales_buscar = $("#dataTableHospitales").DataTable({
		"destroy":true,
		"ajax":{
			"method":"POST",
			"url":"<?php echo SERVERURL; ?>php/muestras/getHospitalesTabla.php"
		},
		"columns":[
			{"defaultContent":"<button class='view btn btn-primary'><span class='fas fa-copy'></span></button>"},
			{"data":"nombre"},
			{"defaultContent":"<button class='editar btn btn-warning'><span class='fas fa-edit'></span></button>"}
		],
		"pageLength" : 5,
        "lengthMenu": lengthMenu,
		"stateSave": true,
		"bDestroy": true,
		"language": idioma_español,
		"dom": dom,
		"buttons":[
			{
				text:      '<i class="fas fa-sync-alt fa-lg"></i> Actualizar',
				titleAttr: 'Actualizar Hospitales',
				className: 'actualizar btn btn-secondary',
				action: 	function(){
					listar_hospitales_buscar();
					getHospitalClinica();
				}
			},
			{
				text:      '<i class="fas fas fa-plus fa-lg crear"></i> Crear',
				titleAttr: 'Agregar Hospitales',
				className: 'crear btn btn-primary',
				action: 	function(){
					modal_hospitales();
				}
			}
		]
	});
	table_hospitales_buscar.search('').draw();
	$('#buscar').focus();

	view_hospitales_busqueda_dataTable("#dataTableHospitales tbody", table_hospitales_buscar);
	editar_hospitales_busqueda_dataTable("#dataTableHospitales tbody", table_hospitales_buscar);	
}

var view_hospitales_busqueda_dataTable = function(tbody, table){
	$(tbody).off("click", "button.view");
	$(tbody).on("click", "button.view", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();
		$('#formularioMuestras #hospital_clinica').val(data.hospitales_id);
		$('#modal_busqueda_hospitales').modal('hide');
	});
}

var editar_hospitales_busqueda_dataTable = function(tbody, table){
	$(tbody).off("click", "button.editar");
	$(tbody).on("click", "button.editar", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();
		var url = '<?php echo SERVERURL; ?>php/hospitales/editarHospitales.php';	
		$('#formularioHospitales')[0].reset();
		$('#formularioHospitales #hospitales_id').val(data.hospitales_id);
			
		$.ajax({
			type:'POST',
			url:url,
			data:$('#formularioHospitales').serialize(),
			success: function(registro){
				var valores = eval(registro);
				$('#formularioHospitales').attr({ 'data-form': 'update' }); 
				$('#formularioHospitales').attr({ 'action': '<?php echo SERVERURL; ?>php/hospitales/modificarHospitales.php' }); 
				$('#reg_hospitales').hide();
				$('#edi_hospitales').show();
				$('#delete_hospitales').hide();
				$('#formularioHospitales #hospitales').val(valores[0]);

				//HABILITAR OBJETOS
				$('#formularioHospitales #hospitales').attr("readonly", false);	
				
				$('#formularioHospitales #pro').val("Editar");
				$('#modalHospitales').modal({
					show:true,
					keyboard: false,
					backdrop:'static'
				});
			}
		});			
	});
}

function getServicioMuestras(){
	var url = '<?php echo SERVERURL; ?>php/muestras/getServicio.php';

	$.ajax({
	   type:'POST',
	   url:url,
	   success:function(data){
	      $('#formularioMuestras #servicio_muestras').html("");
		  $('#formularioMuestras #servicio_muestras').html(data);
	  }
	});
	return false;
}

function getRemitente(){
	var url = '<?php echo SERVERURL; ?>php/muestras/getRemitentes.php';

	$.ajax({
	   type:'POST',
	   url:url,
	   success:function(data){
	      $('#formularioMuestras #remitente').html("");
		  $('#formularioMuestras #remitente').html(data);
	  }
	});
	return false;
}

function pagination(partida){
	var url = '<?php echo SERVERURL; ?>php/muestras/paginar.php';
    var fechai = $('#form_main #fecha_i').val();
	var fechaf = $('#form_main #fecha_f').val();
	var tipo_paciente_grupo = '';
	var pacientesIDGrupo = '';	
	var tipo_muestra = '';
	var dato = '';

    if($('#form_main #tipo_paciente_grupo').val() == "" || $('#form_main #tipo_paciente_grupo').val() == null){
		tipo_paciente_grupo = '';
	}else{
		tipo_paciente_grupo = $('#form_main #tipo_paciente_grupo').val();
	}

    if($('#form_main #pacientesIDGrupo').val() == "" || $('#form_main #pacientesIDGrupo').val() == null){
		pacientesIDGrupo = '';
	}else{
		pacientesIDGrupo = $('#form_main #pacientesIDGrupo').val();
	}
	
    if($('#form_main #tipo_muestra').val() == "" || $('#form_main #tipo_muestra').val() == null){
		tipo_muestra = '';
	}else{
		tipo_muestra = $('#form_main #tipo_muestra').val();
	}
	
	if($('#form_main #bs_regis').val() == "" || $('#form_main #bs_regis').val() == null){
		dato = '';
	}else{
		dato = $('#form_main #bs_regis').val();
	}

	$.ajax({
		type:'POST',
		url:url,
		async: true,
		data:'partida='+partida+'&fechai='+fechai+'&fechaf='+fechaf+'&dato='+dato+'&tipo_paciente_grupo='+tipo_paciente_grupo+'&pacientesIDGrupo='+pacientesIDGrupo+'&tipo_muestra='+tipo_muestra,
		success:function(data){
			var array = eval(data);
			$('#agrega-registros').html(array[0]);
			$('#pagination').html(array[1]);
		}
	});
	return false;
}

$('#ancla_volver').on('click', function(e){
	alert("Me haz pinchado");
	 e.preventDefault();
	 if($('#formulario_facturacion #cliente_nombre').val() != "" || $('#formulario_facturacion #colaborador_nombre').val() != ""){
		swal({
		  title: "Tiene datos en la factura",
		  text: "¿Esta seguro que desea volver, recuerde que tiene información en la factura la perderá?",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-warning",
		  confirmButtonText: "¡Si, deseo volver!",
		  closeOnConfirm: false
		},
		function(){
			$('#main_facturacion').show();
			$('#label_acciones_factura').html("");
			$('#facturacion').hide();
			$('#acciones_atras').addClass("breadcrumb-item active");
			$('#acciones_factura').removeClass("active");
			$('#formulario_facturacion')[0].reset();
			swal.close();
			$('.footer').show();
    		$('.footer1').hide();			
		});
	 }else{
		 $('#main_facturacion').show();
		 $('#label_acciones_factura').html("");
		 $('#facturacion').hide();
		 $('#acciones_atras').addClass("breadcrumb-item active");
		 $('#acciones_factura').removeClass("active");
		 $('.footer').show();
    	 $('.footer1').hide();			 
	 }
});

//FACTURA
function formFactura(){
	 $('#formulario_facturacion')[0].reset();
	 $('#main_facturacion').hide();
	 $('#facturacion').show();
	 $('#label_acciones_volver').html("Volver");
	 $('#acciones_atras').removeClass("active");
	 $('#acciones_factura').addClass("active");
	 $('#label_acciones_factura').html("Factura");
	 $('#formulario_facturacion #fecha').attr('disabled', false);
	 $('#formulario_facturacion').attr({ 'data-form': 'save' });
	 $('#formulario_facturacion').attr({ 'action': '<?php echo SERVERURL; ?>php/facturacion/addPreFactura.php' });
	 limpiarTabla();
	 $('.footer').show();
     $('.footer1').hide();	 
}

function volver(){
	$('#main_facturacion').show();
	$('#label_acciones_factura').html("");
	$('#facturacion').hide();
	$('#acciones_atras').addClass("breadcrumb-item active");
	$('#acciones_factura').removeClass("active");
	$('.footer').show();
    $('.footer1').hide();	
}

function createBill(muestras_id){
	if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
		if(getFacturaEmision(muestras_id) == ""){
			$('#formulario_facturacion')[0].reset();
			$("#formulario_facturacion #invoiceItem > tbody").empty();//limpia solo los registros del body
			var url = '<?php echo SERVERURL; ?>php/muestras/editarFacturasMuestras.php';

			var url = '<?php echo SERVERURL; ?>php/muestras/editarFacturasMuestras.php';
				$.ajax({
				type:'POST',
				url:url,
				data:'muestras_id='+muestras_id,
				success: function(valores){
					var datos = eval(valores);
					$('#formulario_facturacion #muestras_id').val(muestras_id);
					$('#formulario_facturacion #pacientes_id').val(datos[0]);
					$('#formulario_facturacion #cliente_nombre').val(datos[1]);
					$('#formulario_facturacion #fecha').val(getFechaActual());
					$('#formulario_facturacion #colaborador_id').val(datos[3]);
					$('#formulario_facturacion #colaborador_nombre').val(datos[4]);
					$('#formulario_facturacion #servicio_id').val(datos[5]);
					$('#formulario_facturacion #material_enviado_muestra').val(datos[6]);
					$('#formulario_facturacion #paciente_muestra_codigo').val(datos[7]);
					$('#formulario_facturacion #paciente_muestra').val(datos[8]);

					$('#formulario_facturacion #fecha').attr("readonly", true);
					$('#formulario_facturacion #validar').attr("disabled", false);
					$('#formulario_facturacion #addRows').attr("disabled", false);
					$('#formulario_facturacion #removeRows').attr("disabled", false);
					$('#formulario_facturacion #validar').show();
					$('#formulario_facturacion #editar').hide();
					$('#formulario_facturacion #eliminar').hide();

					if(getTipoPaciente(datos[0]) == 2){
						$('#formulario_facturacion #grupo_paciente_factura').show();
					}else{
						$('#formulario_facturacion #grupo_paciente_factura').hide();
					}

					$('#main_facturacion').hide();
					$('#facturacion').show();
					$('#label_acciones_volver').html("Volver");
					$('#acciones_atras').removeClass("active");
					$('#acciones_factura').addClass("active");
					$('#label_acciones_factura').html("Factura");
					$('#formulario_facturacion #fecha').attr('disabled', false);

					limpiarTabla();

					$('#main_facturacion').hide();
					$('#label_acciones_factura').html("Factura");
					$('#facturacion').show();
					
					$('.footer').hide();
    				$('.footer1').show();					

					return false;
				}
			});
		}else{
			swal({
				title: "Error",
				text: "Lo sentimos esta factura ya ha sido generada, por favor diríjase al módulo de facturación y realice le cobro de esta",
				type: "error",
				confirmButtonClass: 'btn-danger'
			});
		}
	}else{
		swal({
			title: "Acceso Denegado",
			text: "No tiene permisos para ejecutar esta acción",
			type: "error",
			confirmButtonClass: 'btn-danger'
		});
	}
}

$('#formulario_facturacion #validar').on('click', function(e){
	$('#formulario_facturacion').attr({ 'data-form': 'save' });
	$('#formulario_facturacion').attr({ 'action': '<?php echo SERVERURL; ?>php/facturacion/addPreFactura.php' });
	$("#formulario_facturacion").submit();
});

$('#formulario_facturacion #cobrar').on('click', function(e){
	$('#formulario_facturacion').attr({ 'data-form': 'save' });
	$('#formulario_facturacion').attr({ 'action': '<?php echo SERVERURL; ?>php/facturacion/addFactura.php' });
	$("#formulario_facturacion").submit();
});

function pago(facturas_id){
	var url = '<?php echo SERVERURL; ?>php/facturacion/editarPago.php';
	
	$.ajax({
		type:'POST',
		url:url,
		data:'facturas_id='+facturas_id,
		success: function(valores){
			var datos = eval(valores);
			$('#formEfectivoBill .border-right a:eq(0) a').tab('show');			
			$("#customer-name-bill").html("<b>Cliente:</b> " + datos[0]);
		    $("#customer_bill_pay").val(datos[2]);
			$('#bill-pay').html("L. " + parseFloat(datos[2]).toFixed(2));
			
			//EFECTIVO
			$('#formEfectivoBill')[0].reset();			
			$('#formEfectivoBill #monto_efectivo').val(datos[2]);
			$('#formEfectivoBill #factura_id_efectivo').val(facturas_id);
			$('#formEfectivoBill #pago_efectivo').attr('disabled', true);
			
			//TARJETA
			$('#formTarjetaBill')[0].reset();
			$('#formTarjetaBill #monto_efectivo').val(datos[2]);
			$('#formTarjetaBill #factura_id_tarjeta').val(facturas_id);
			$('#formTarjetaBill #pago_efectivo').attr('disabled', true);	

			//TRANSFERENCIA
			$('#formTransferenciaBill')[0].reset();
			$('#formTransferenciaBill #monto_efectivo').val(datos[2]);
			$('#formTransferenciaBill #factura_id_transferencia').val(facturas_id);
			$('#formTransferenciaBill #pago_efectivo').attr('disabled', true);				
			
			$('#modal_pagos').modal({
				show:true,
				keyboard: false,
				backdrop:'static'
			});

			return false;
		}
	});	
}

$(document).ready(function(){
	$("#tab1").on("click", function(){	
		$("#modal_pagos").on('shown.bs.modal', function(){
           $(this).find('#formTarjetaBill #efectivo_bill').focus();
		});			
	});
	
	$("#tab2").on("click", function(){	
		$("#modal_pagos").on('shown.bs.modal', function(){
           $(this).find('#formTarjetaBill #cr_bill').focus();
		});	
	});	
	
	$("#tab2").on("click", function(){	
		$("#modal_pagos").on('shown.bs.modal', function(){
           $(this).find('#formTarjetaBill #bk_nm').focus();
		});	
	});		
});

$(document).ready(function(){
	$('#formTarjetaBill #cr_bill').inputmask("9999");
});

$(document).ready(function(){
	$('#formTarjetaBill #exp').inputmask("99/99");
});

$(document).ready(function(){
	$('#formTarjetaBill #cvcpwd').inputmask("999999");
});

$(document).ready(function(){
	$("#formEfectivoBill #efectivo_bill").on("keyup", function(){	
		var efectivo = parseFloat($("#formEfectivoBill #efectivo_bill").val()).toFixed(2);
		var monto = parseFloat($("#formEfectivoBill #monto_efectivo").val()).toFixed(2);
		
		var total = efectivo - monto;				
		
		if(Math.floor(efectivo*100) >= Math.floor(monto*100)){			
			$('#formEfectivoBill #cambio_efectivo').val(parseFloat(total).toFixed(2));
			$('#formEfectivoBill #pago_efectivo').attr('disabled', false);				
		}else{
			$('#formEfectivoBill #cambio_efectivo').val(parseFloat(0).toFixed(2));
			$('#formEfectivoBill #pago_efectivo').attr('disabled', true);
		}				
	});
});

function printBill(facturas_id){
	var url = '<?php echo SERVERURL; ?>php/facturacion/generaFactura.php?facturas_id='+facturas_id;
    window.open(url);
}

function sendMail(facturas_id){
	var url = '<?php echo SERVERURL; ?>php/facturacion/correo_facturas.php';
	var bill = '';

	$.ajax({
	   type:'POST',
	   url:url,
	   async: false,
	   data:'facturas_id='+facturas_id,
	   success:function(data){
	      bill = data;
	      if(bill == 1){
				swal({
					title: "Success",
					text: "La factura ha sido enviada por correo satisfactoriamente",
					type: "success",
				});
		  }
	  }
	});
	return bill;
}
//FIN MODAL PAGOS

function getTipoMuestra(){
    var url = '<?php echo SERVERURL; ?>php/administrador_secuencias_muestras/getTipoMuestra.php';

	$.ajax({
        type: "POST",
        url: url,
	    async: true,
        success: function(data){
		    $('#formularioMuestras #tipo_muestra_id').html("");
			$('#formularioMuestras #tipo_muestra_id').html(data);
			
		    $('#form_main #tipo_muestra').html("");
			$('#form_main #tipo_muestra').html(data);			
        }
     });
}

function getCategoriaMuestra(){
    var url = '<?php echo SERVERURL; ?>php/muestras/getCategoriaMuestra.php';

	$.ajax({
        type: "POST",
        url: url,
	    async: true,
        success: function(data){
		    $('#formularioMuestras #categoria_muestras').html("");
			$('#formularioMuestras #categoria_muestras').html(data);			
        }
     });
}

function getTipoPaciente(pacientes_id){
	var url = '<?php echo SERVERURL; ?>php/muestras/getTipoPaciente.php';
	var tipo_paciente;

	$.ajax({
	    type:'POST',
		url:url,
		data:'pacientes_id='+pacientes_id,
		async: false,
		success:function(data){
          tipo_paciente = data;
		}
	});
	return tipo_paciente;
}

$(document).ready(function() {
	$('#formularioMuestras #tipo_paciente_muestra').on('change', function(){
		getPacientes();
	});
});

function getPacientes(){
    var url = '<?php echo SERVERURL; ?>php/muestras/getPacientes.php';
	var tipo_paciente = $("#formularioMuestras #tipo_paciente_muestra").val();

	$.ajax({
        type: "POST",
        url: url,
	    async: true,
		data:'tipo_paciente='+tipo_paciente,
        success: function(data){
		    $('#formularioMuestras #paciente_consulta').html("");
			$('#formularioMuestras #paciente_consulta').html(data);
        }
     });
}

function getPacientesEditar(tipo_paciente){
    var url = '<?php echo SERVERURL; ?>php/muestras/getPacientes.php';

	$.ajax({
        type: "POST",
        url: url,
	    async: true,
		data:'tipo_paciente='+tipo_paciente,
        success: function(data){
		    $('#formularioMuestras #paciente_consulta').html("");
			$('#formularioMuestras #paciente_consulta').html(data);
        }
     });
}

function getPacientes_clientes(){
    var url = '<?php echo SERVERURL; ?>php/atencion_pacientes/getPacientesClientes.php';

	$.ajax({
        type: "POST",
        url: url,
	    async: true,
        success: function(data){
		    $('#formularioMuestras #paciente_muestras').html("");
			$('#formularioMuestras #paciente_muestras').html(data);
        }
     });
}

$(document).ready(function() {
	$('#formularioMuestras #paciente_consulta').on('change', function(){
	  $('#formularioMuestras #tipo_paciente_muestra').val(getTipoPaciente($('#formularioMuestras #paciente_consulta').val()));
      if(getTipoPaciente($('#formularioMuestras #paciente_consulta').val()) == 2){
		  $('#formularioMuestras #pacientes_muestra_grupo').show();
		  $('#formularioMuestras #servicios_muestra_grupo').hide();
	  }else{
		  $('#formularioMuestras #pacientes_muestra_grupo').hide();
		  $('#formularioMuestras #servicios_muestra_grupo').show();
	  }
	  return false;
    });
});

//INICIO FORMULARIO DE BUSQUEDA CLIENTES MAIN
$('#form_main #buscar_cliente_muestras').on('click', function(e){
	$('#modal_busqueda_pacientes_main_muetras').modal({
		show:true,
		keyboard: false,
		backdrop:'static'
	});
});

var listar_pacientes_buscar = function(){
	var tipo_paciente = $("#form_main #tipo_paciente_grupo").val();

	if(tipo_paciente == "" || tipo_paciente == null){
		tipo_paciente = 1;
	}

	var table_pacientes_buscar = $("#dataTablePacientes_main_muestras").DataTable({
		"destroy":true,	
		"ajax":{
			"method":"POST",
			"url":"<?php echo SERVERURL; ?>php/muestras/getPacientesTabla.php",
			"data":{
				"tipo_paciente":tipo_paciente
			}			
		},
		"columns":[
			{"defaultContent":"<button class='view btn btn-primary'><span class='fas fa-copy'></span></button>"},
			{"data":"paciente"},
			{"data":"identidad"},
			{"data":"expediente"},
			{"data":"email"}
		],
		"pageLength" : 5,
        "lengthMenu": lengthMenu,
		"stateSave": true,
		"bDestroy": true,
		"language": idioma_español,
		"dom": dom,
		"buttons":[
			{
				text:      '<i class="fas fa-sync-alt fa-lg"></i> Actualizar',
			titleAttr: 'Actualizar Registro',
				className: 'table_actualizar btn btn-secondary',
				action: 	function(){
					listar_pacientes_buscar();
				}
			}		
		],
	});
	table_pacientes_buscar.search('').draw();
	$('#buscar').val('');
	$('#buscar').focus();

	view_pacientes_busqueda_dataTable("#dataTablePacientes_main_muestras tbody", table_pacientes_buscar);
}

var view_pacientes_busqueda_dataTable = function(tbody, table){
	$(tbody).off("click", "button.view");
	$(tbody).on("click", "button.view", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();
		$('#form_main #pacientesIDGrupo').val(data.pacientes_id);
		pagination(1);
		$('#modal_busqueda_pacientes_main_muetras').modal('hide');
	});
}
//INICIO FORMULARIO DE BUSQUEDA CLIENTES MAIN


//INICIO MODAL MUESTRAS
$('#formularioMuestras #tipo_paciente_muestra').on('change',function(){
	if ( $('#formularioMuestras #tipo_paciente_muestra').val() == 1){
		$('#formularioMuestras #cliente_muestra_grupo').show();
		$('#formularioMuestras #pacientes_muestra_grupo').hide();
		$('#formularioMuestras #servicios_muestra_grupo').show();
	}else{
		$('#formularioMuestras #cliente_muestra_grupo').show();
		$('#formularioMuestras #pacientes_muestra_grupo').show();
		$('#formularioMuestras #servicios_muestra_grupo').hide();		
	}
});

//EMPRESAS
$('#formularioMuestras #buscar_paciente_consulta_muestras').on('click', function(e){
	$('#modal_busqueda_clientes_muestras').modal({
		show:true,
		keyboard: false,
		backdrop:'static'
	});
});

var listar_muestras_clientes_buscar = function(){
	var tipo_paciente = $("#form_main #tipo_paciente_grupo").val();

	if(tipo_paciente == "" || tipo_paciente == null){
		tipo_paciente = 1;
	}

	var table_muestras_clientes_buscar = $("#dataTableClinetes_muestras").DataTable({
		"destroy":true,	
		"ajax":{
			"method":"POST",
			"url":"<?php echo SERVERURL; ?>php/muestras/getPacientesTabla.php",
			"data":{
				"tipo_paciente":tipo_paciente
			}			
		},
		"columns":[
			{"defaultContent":"<button class='view btn btn-primary'><span class='fas fa-copy'></span></button>"},
			{"data":"paciente"},
			{"data":"identidad"},
			{"data":"expediente"},
			{"data":"email"}
		],
		"pageLength" : 5,
        "lengthMenu": lengthMenu,
		"stateSave": true,
		"bDestroy": true,
		"language": idioma_español,
		"dom": dom,
		"buttons":[
			{
				text:      '<i class="fas fa-sync-alt fa-lg"></i> Actualizar',
			titleAttr: 'Actualizar Registro',
				className: 'table_actualizar btn btn-secondary',
				action: 	function(){
					listar_muestras_clientes_buscar();
				}
			}		
		],
	});
	table_muestras_clientes_buscar.search('').draw();
	$('#buscar').val('');
	$('#buscar').focus();

	view_busqueda_muestras_clientes_busqueda_dataTable("#dataTableClinetes_muestras tbody", table_muestras_clientes_buscar);
}

var view_busqueda_muestras_clientes_busqueda_dataTable = function(tbody, table){
	$(tbody).off("click", "button.view");
	$(tbody).on("click", "button.view", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();
		$('#formularioMuestras #paciente_consulta').val(data.pacientes_id);
		pagination(1);
		$('#modal_busqueda_clientes_muestras').modal('hide');
	});
}

//PACIENTES
$('#formularioMuestras #buscar_paciente_muestras').on('click', function(e){
	$('#modal_busqueda_pacientes_muestras').modal({
		show:true,
		keyboard: false,
		backdrop:'static'
	});
});

var listar_muestras_pacientes_buscar = function(){
	var tipo_paciente = $("#form_main #tipo_paciente_grupo").val();

	if(tipo_paciente == "" || tipo_paciente == null){
		tipo_paciente = 1;
	}

	var table_muestras_pacientes_buscar = $("#dataTablePacientes_muestras").DataTable({
		"destroy":true,	
		"ajax":{
			"method":"POST",
			"url":"<?php echo SERVERURL; ?>php/muestras/getPacientesTabla.php",
			"data":{
				"tipo_paciente":tipo_paciente
			}			
		},
		"columns":[
			{"defaultContent":"<button class='view btn btn-primary'><span class='fas fa-copy'></span></button>"},
			{"data":"paciente"},
			{"data":"identidad"},
			{"data":"expediente"},
			{"data":"email"}
		],
		"pageLength" : 5,
        "lengthMenu": lengthMenu,
		"stateSave": true,
		"bDestroy": true,
		"language": idioma_español,
		"dom": dom,
		"buttons":[
			{
				text:      '<i class="fas fa-sync-alt fa-lg"></i> Actualizar',
			titleAttr: 'Actualizar Registro',
				className: 'table_actualizar btn btn-secondary',
				action: 	function(){
					listar_muestras_pacientes_buscar();
				}
			}		
		],
	});
	table_muestras_pacientes_buscar.search('').draw();
	$('#buscar').val('');
	$('#buscar').focus();

	view_busqueda_muestras_pacientes_busqueda_dataTable("#dataTablePacientes_muestras tbody", table_muestras_pacientes_buscar);
}

var view_busqueda_muestras_pacientes_busqueda_dataTable = function(tbody, table){
	$(tbody).off("click", "button.view");
	$(tbody).on("click", "button.view", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();
		$('#formularioMuestras #paciente_muestras').val(data.pacientes_id);
		pagination(1);
		$('#modal_busqueda_pacientes_muestras').modal('hide');
	});
}
//FIN MODAL MUESTRAS

function getFacturaEmision(muestras_id){
	var url = '<?php echo SERVERURL; ?>php/muestras/getFacturaEmision.php';
	var disponible;

	$.ajax({
	    type:'POST',
		url:url,
		data:'muestras_id='+muestras_id,
		async: false,
		success:function(data){
          disponible = data;
		}
	});
	return disponible;
}

$('#formularioMuestras #buscar_tipo_muestras_id').on('click', function(e){
	listar_tipo_muestra_buscar();
	 $('#modal_busqueda_tipo_mmuestra').modal({
		show:true,
		keyboard: false,
		backdrop:'static'
	});
});

var listar_tipo_muestra_buscar = function(){
	var table_tipo_muestra_buscar = $("#dataTableTipoMuestra").DataTable({
		"destroy":true,
		"ajax":{
			"method":"POST",
			"url":"<?php echo SERVERURL; ?>php/muestras/getTipoMuestraTabla.php"
		},
		"columns":[
			{"defaultContent":"<button class='view btn btn-primary'><span class='fas fa-copy'></span></button>"},
			{"data":"nombre"}
		],
		"pageLength" : 5,
        "lengthMenu": lengthMenu,
		"stateSave": true,
		"bDestroy": true,
		"language": idioma_español,
	});
	table_tipo_muestra_buscar.search('').draw();
	$('#buscar').focus();

	view_tipo_muestra_busqueda_dataTable("#dataTableTipoMuestra tbody", table_tipo_muestra_buscar);
}

var view_tipo_muestra_busqueda_dataTable = function(tbody, table){
	$(tbody).off("click", "button.view");
	$(tbody).on("click", "button.view", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();
		$('#formularioMuestras #tipo_muestra_id').val(data.tipo_muestra_id);
		$('#modal_busqueda_tipo_mmuestra').modal('hide');
	});
}

function getTipoPacienteGrupo(){
    var url = '<?php echo SERVERURL; ?>php/facturacion/getTipoPaciente.php';

	$.ajax({
        type: "POST",
        url: url,
        success: function(data){
		    $('#form_main #tipo_paciente_grupo').html("");
			$('#form_main #tipo_paciente_grupo').html(data);
			getPacienteGrupo(1);
		}
     });
}

function getTipoPacienteGrupoMuestras(){
    var url = '<?php echo SERVERURL; ?>php/facturacion/getTipoPacienteMuestra.php';

	$.ajax({
        type: "POST",
        url: url,
        success: function(data){
		    $('#formularioMuestras #tipo_paciente_muestra').html("");
			$('#formularioMuestras #tipo_paciente_muestra').html(data);
			getPacienteGrupo(1);
		}
     });
}

$('#form_main #tipo_paciente_grupo').on('change',function(){
	getPacienteGrupo($('#form_main #tipo_paciente_grupo').val());
});

function getPacienteGrupo(tipo_paciente){
    var url = '<?php echo SERVERURL; ?>php/muestras/getPacienteGrupo.php';

	$.ajax({
        type: "POST",
        url: url,
		data:'tipo_paciente='+tipo_paciente,
        success: function(data){
		    $('#form_main #pacientesIDGrupo').html("");
			$('#form_main #pacientesIDGrupo').html(data);
		}
     });
}

function showModalhistoriaMuestrasEmpresas(pacientes_id){	
	$('#modal_historico_muestras #pacientes_id_muestras').val(pacientes_id);
	$('#modal_historico_muestras').modal({
		show:true,
		keyboard: false,
		backdrop:'static'
	});	
	historiaMuestrasEmpresas(1);
}

function historiaMuestrasEmpresas(partida){
	var url = '<?php echo SERVERURL; ?>php/muestras/paginar_historico_muestras_empresas.php';
	var pacientes_id = $('#modal_historico_muestras #pacientes_id_muestras').val();

	$.ajax({
		type:'POST',
		url:url,
		async: true,
		data:'partida='+partida+'&pacientes_id='+pacientes_id,
		success:function(data){
			var array = eval(data);
			$('#detalles-historico-muestras').html(array[0]);
			$('#pagination-historico-muestras').html(array[1]);
		}
	});
	return false;
}

function showModalhistoriaMuestrasPacientes(pacientes_id){	
	$('#modal_historico_muestras #pacientes_id_muestras').val(pacientes_id);
	$('#modal_historico_muestras').modal({
		show:true,
		keyboard: false,
		backdrop:'static'
	});	
	historiaMuestrasPacientes(1);
}

function historiaMuestrasPacientes(partida){
	var url = '<?php echo SERVERURL; ?>php/muestras/paginar_historico_muestras_pacientes.php';
	var pacientes_id = $('#modal_historico_muestras #pacientes_id_muestras').val();

	$.ajax({
		type:'POST',
		url:url,
		async: true,
		data:'partida='+partida+'&pacientes_id='+pacientes_id,
		success:function(data){
			var array = eval(data);
			$('#detalles-historico-muestras').html(array[0]);
			$('#pagination-historico-muestras').html(array[1]);
		}
	});
	return false;
}

function getFechaActual(){
	var url = '<?php echo SERVERURL; ?>php/muestras/getFechaActual.php';
	var fecha_actual;

	$.ajax({
	    type:'POST',
		url:url,
		async: false,
		success:function(data){
          fecha_actual = data;
		}
	});
	return fecha_actual;	
}

function modal_clientes(){
	if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
		$('#formulario_pacientes #reg').show();
		$('#formulario_pacientes #edi').hide();
		cleanPacientes();
		getDepartamento();
		$('#formulario_pacientes #grupo_expediente').hide();			
		$('#formulario_pacientes')[0].reset();	
		$('#formulario_pacientes #pro').val('Registro');
		$("#formulario_pacientes #fecha").attr('readonly', false);
		$('#formulario_pacientes #rtn').attr('readonly',false);
		$('#formulario_pacientes #validate').removeClass('bien_email');
		$('#formulario_pacientes #validate').removeClass('error_email');
		$("#formulario_pacientes #correo").css("border-color", "none");
		$('#formulario_pacientes #validate').html('');			
		$('#formulario_pacientes').attr({ 'data-form': 'save' }); 
		$('#formulario_pacientes').attr({ 'action': '<?php echo SERVERURL; ?>php/pacientes/agregarPacientes.php' });	
		$('#modal_pacientes').modal({
			show:true,
			keyboard: false,
			backdrop:'static'
		});
		return false;
	}else{
		swal({
			title: "Acceso Denegado", 
			text: "No tiene permisos para ejecutar esta acción",
			type: "error", 
			confirmButtonClass: 'btn-danger'
		});					 
	}	
}

function modal_colaboradores(){
	if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
		  $('#formulario_colaboradores')[0].reset();
		  $('#pro').val('Registro');
		  $('#edi').hide();
		  $('#reg').show();
		  empresa();
		  puesto();
		  getEstatus();
		  $('#formulario_colaboradores #pro').val("Registro");
		  $('#formulario_colaboradores').attr({ 'data-form': 'save' }); 
		  $('#formulario_colaboradores').attr({ 'action': '<?php echo SERVERURL; ?>php/colaboradores/agregar.php' });
			  
		  $('#registrar_colaboradores').modal({
			show:true,
			keyboard: false,
			backdrop:'static'
		  });
		  puesto();
		  empresa();
	}else{
		swal({
			title: "Acceso Denegado", 
			text: "No tiene permisos para ejecutar esta acción",
			type: "error", 
			confirmButtonClass: 'btn-danger'
		});	
	}
}

function modal_hospitales(){
	if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
		$('#formularioHospitales').attr({ 'data-form': 'save' });
		$('#formularioHospitales').attr({ 'action': '<?php echo SERVERURL; ?>php/hospitales/agregarHospitales.php' });			
		$('#reg_hospitales').show();
		$('#edi_hospitales').hide();
		$('#delete_hospitales').hide();		
		$('#formularioHospitales')[0].reset();	
		$('#formularioHospitales #pro').val('Registro');
		
		//HABILITAR OBJETOS
		 $('#formularioHospitales #hospitales').attr("readonly", false);				
				
		 $('#modalHospitales').modal({
			show:true,
			keyboard: false,
			backdrop:'static'
		});			
	}else{
		swal({
			title: "Acceso Denegado", 
			text: "No tiene permisos para ejecutar esta acción",
			type: "error", 
			confirmButtonClass: 'btn-danger'
		});				
	} 	
}

function getTotalFacturasDisponibles(){
	var url = '<?php echo SERVERURL; ?>php/facturacion/getTotalFacturasDisponibles.php';

	$.ajax({
	   type:'POST',
	   url:url,
	   async: false,
	   success:function(registro){
			var valores = eval(registro);
			var mensaje = "";
			if(valores[0] >=10 && valores[0] <= 30){
				mensaje = "Total Facturas disponibles: " + valores[0];

				$("#mensajeFacturas").html(mensaje).addClass("alert alert-warning");
				$("#mensajeFacturas").html(mensaje).removeClass("alert alert-danger");

				$("#mensajeFacturas").attr("disabled", true);
				$("#formulario_facturacion #validar").attr("disabled", false);
				$("#formulario_facturacion #cobrar").attr("disabled", false);	


			}else if(valores[0] >=1 && valores[0] <= 9){
				mensaje = "Total Facturas disponibles: " + valores[0];
				$("#mensajeFacturas").html(mensaje).addClass("alert alert-danger");
				$("#mensajeFacturas").html(mensaje).removeClass("alert alert-warning");
				$("#mensajeFacturas").attr("disabled", true);
				$("#formulario_facturacion #validar").attr("disabled", false);
				$("#formulario_facturacion #cobrar").attr("disabled", false);	
			}
			else{
				mensaje = "";

				$("#formulario_facturacion #validar").attr("disabled", false);	
				$("#formulario_facturacion #cobrar").attr("disabled", false);			
				$("#mensajeFacturas").html(mensaje).addClass("alert alert-danger");
				$("#mensajeFacturas").html(mensaje).removeClass("alert alert-warning");				
			}

			if(valores[0] == 0){
				mensaje = "No puede seguir facturando";

				$("#formulario_facturacion #validar").attr("disabled", false);	
				$("#formulario_facturacion #cobrar").attr("disabled", true);			
				$("#mensajeFacturas").html(mensaje).addClass("alert alert-danger");
				$("#mensajeFacturas").html(mensaje).removeClass("alert alert-warning");
			}
			
			if(valores[1] == 1){
				mensaje += "<br/>Su fecha límite es: " + valores[2];
				$("#formulario_facturacion #validar").attr("disabled", false);	
				$("#formulario_facturacion #cobrar").attr("disabled", false);				
				$("#mensajeFacturas").html(mensaje).addClass("alert alert-warning");
				$("#mensajeFacturas").html(mensaje).removeClass("alert alert-danger");			
			}

			if(valores[1] == 0){
				mensaje += "<br/>Ya alcanzo su fecha límite";
				$("#formulario_facturacion #validar").attr("disabled", false);	
				$("#formulario_facturacion #cobrar").attr("disabled", true);				
				$("#mensajeFacturas").html(mensaje).addClass("alert alert-danger");	
				$("#mensajeFacturas").html(mensaje).removeClass("alert alert-warning");		
			}			
	   }
	});
}

setInterval('getTotalFacturasDisponibles()',1000);
</script>