<script>
/*INICIO DE FUNCIONES PARA ESTABLECER EL FOCUS PARA LAS VENTANAS MODALES*/
$(document).ready(function(){
    $("#eliminar").on('shown.bs.modal', function(){
        $(this).find('#form_eliminar #motivo').focus();
    });
});

$(document).ready(function(){
    $("#cobros").on('shown.bs.modal', function(){
        $(this).find('#formCobros #comentario').focus();
    });
});
/*FIN DE FUNCIONES PARA ESTABLECER EL FOCUS PARA LAS VENTANAS MODALES*/
/****************************************************************************************************************************************************************/

//INICIO CONTROLES DE ACCION
$(document).ready(function() {
	funciones();

	//INICIO ABRIR VENTANA MODAL PARA EL REGISTRO DE LAS FACTURAS
	$('#form_main_facturacion_reportes #factura').off('click.reporteFacturacion').on('click.reporteFacturacion',function(e){
		e.preventDefault();

		if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3 || getUsuarioSistema() == 4){
			var profesional = '';

			if($('#form_main_facturacion_reportes #profesional').val() == "" || $('#form_main_facturacion_reportes #profesional').val() == null){
				profesional = getColaboradorConsultaID();
			}else{
				profesional = $('#form_main_facturacion_reportes #profesional').val();
			}

            $('#formCobros')[0].reset();
			$("#formCobros #generar").attr('disabled', false);
            $('#formCobros #colaborador_id').val(profesional);
			$('#formCobros #fechai').val($('#form_main_facturacion_reportes #fecha_b').val());
			$('#formCobros #fechaf').val($('#form_main_facturacion_reportes #fecha_f').val());
            $('#formCobros #profesional').val(getColaboradorNombre(profesional));
			$('#formCobros #pro').val("Registro");

		    $('#cobros').modal({
				show:true,
				keyboard: false,
				backdrop:'static'
		    });
		}else{
			swal({
				title: "Acceso Denegado",
				text: "No tiene permisos para ejecutar esta acción",
				icon: "error",
				dangerMode: true,
				closeOnEsc: false,
				closeOnClickOutside: false
			});

	        return false;
        }
	});
	//FIN ABRIR VENTANA MODAL PARA EL REGISTRO DE LAS FACTURAS

	//INICIO PARA EL REGISTRO DE COBROS A PROFESIONALES
	$('#formCobros #generar').off('click.reporteFacturacion').on('click.reporteFacturacion', function(e){
		 if ($('#formCobros #comentario').val() != ""){
			e.preventDefault();
			agregarCobros();
			return false;
		 }else{
			swal({
				title: "Error",
				text: "Hay registros en blanco, por favor corregir",
				icon: "error",
				dangerMode: true,
				closeOnEsc: false,
				closeOnClickOutside: false
			});

			return false;
		 }
	});
	//FIN PARA EL REGISTRO DE COBROS A PROFESIONALES

    //INICIO PAGINATION
	$('#form_main_facturacion_reportes #estado')
	.off('change.reporteFacturacion changed.bs.select.reporteFacturacion')
	.on('change.reporteFacturacion changed.bs.select.reporteFacturacion',function(){
		listar_reporte_facturacion();
	});

	$('#form_main_facturacion_reportes #fecha_b').off('change.reporteFacturacion').on('change.reporteFacturacion',function(){
		listar_reporte_facturacion();
	});

	$('#form_main_facturacion_reportes #fecha_f').off('change.reporteFacturacion').on('change.reporteFacturacion',function(){
		listar_reporte_facturacion();
	});
	//FIN PAGINATION
});
//FIN CONTROLES DE ACCION
/****************************************************************************************************************************************************************/

$('#form_eliminar #Si').off('click.reporteFacturacion').on('click.reporteFacturacion', function(e){
	if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 4){
		e.preventDefault();

		if($('#form_eliminar #motivo').val() != ""){
			rollback();
		}else{
			swal({
				title: "Error",
				text: "Hay registros en blanco, por favor corregir",
				icon: "error",
				dangerMode: true,
				closeOnEsc: false,
				closeOnClickOutside: false
			});

			return false;
		}
	}else{
		swal({
			title: "Acceso Denegado",
			text: "No tiene permisos para ejecutar esta acción",
			icon: "error",
			dangerMode: true,
			closeOnEsc: false,
			closeOnClickOutside: false
		});
	}
});

//INICIO AGRUPAR FUNCIONES
function funciones(){
	initModalCambioFechaFactura();
	listar_reporte_facturacion();
	getEstado();
	getTipoPacienteGrupo();
}
//FIN AGRUPAR FUNCIONES

//INICIO OBTENER COLABORADOR CONSULTA
function getColaboradorConsulta(){
    var url = '<?php echo SERVERURL; ?>php/facturacion/getMedicoConsulta.php';
	var colaborador_id;

	$.ajax({
	    type:'POST',
		url:url,
		async: false,
		success:function(data){
		  var datos = eval(data);
          colaborador_id = datos[0];
		}
	});

	return colaborador_id;
}
//FIN OBTENER COLABORADOR CONSULTA

//INICIO FUNCION PARA OBTENER LOS COLABORADORES
function getColaboradorConsultaID(){
	var url = '<?php echo SERVERURL; ?>php/facturacion/getMedicoConsulta.php';
	var colaborador_id = '';

	$.ajax({
		type:'POST',
		url:url,
		async: false,
		success: function(valores){
			var datos = eval(valores);
			colaborador_id = datos[0];
		}
	});

	return colaborador_id;
}
//FIN FUNCION PARA OBTENER LOS COLABORADORES

//FUNCTION PARA OBTENER EL NOMBRE DEL COLABORADOR
function getColaboradorNombre(colaborador_id){
	var url = '<?php echo SERVERURL; ?>php/reporte_facturacion/getColaboradorNombre.php';
    var colaborador_nombre = '';

	$.ajax({
		type:'POST',
		url:url,
		async: false,
		data:'colaborador_id='+colaborador_id,
		success: function(valores){
			colaborador_nombre = valores;
		}
	});

	return colaborador_nombre;
}
//FIN PARA OBTENER EL NOMBRE DEL COLABORADOR

//INICIO PARA AGREGAR LA FACTURACION DE LOS USUARIOS DE FORMA MANUAL
function agregarCobros(){
	var url = '<?php echo SERVERURL; ?>php/reporte_facturacion/agregarCargos.php';

	$.ajax({
		type:'POST',
		url:url,
		data:$('#formCobros').serialize(),
		success: function(registro){
			if(registro == 1){
				swal({
					title: "Success",
					text: "Valores generados correctamente",
					icon: "success",
					closeOnEsc: false,
					closeOnClickOutside: false					
				});

				$('#formCobros #comentario').val("");
				$("#formCobros #generar").attr('disabled', true);
				listar_reporte_facturacion();

				return false;
			}else if(registro == 2){
				swal({
					title: "Error",
					text: "Error, no se puedieron generar los valores, por favor corregir",
					icon: "error",
					dangerMode: true,
					closeOnEsc: false,
					closeOnClickOutside: false
				});

				return false;
			}else if(registro == 3){
				swal({
					title: "Error",
					text: "Error, este registro ya existe",
					icon: "error",
					dangerMode: true,
					closeOnEsc: false,
					closeOnClickOutside: false
				});

				return false;
			}else{
				swal({
					title: "Error",
					text: "Error al procesar su solicitud, por favor intentelo de nuevo mas tarde",
					icon: "error",
					dangerMode: true,
					closeOnEsc: false,
					closeOnClickOutside: false
				});

				return false;
			}
		}
	});

	return false;
}
//FIN PARA AGREGAR LA FACTURACION DE LOS USUARIOS DE FORMA MANUAL

//INICIO DETALLES DE FACTURA
function invoicesDetails(facturas_id){
	var url = '<?php echo SERVERURL; ?>php/reporte_facturacion/detallesFactura.php';

	if (!facturas_id || facturas_id <= 0) {
		showNotify("error", "Error", "No se recibió la factura.");
		return false;
	}

	$.ajax({
		type:'POST',
		url:url,
		data:'facturas_id=' + facturas_id,
		beforeSend:function(){
			$('#mensaje_show').modal({
				show:true,
				keyboard: false,
				backdrop:'static'
			});

			$('#mensaje_mensaje_show').html(
				'<div class="text-center p-4">' +
					'<i class="fas fa-spinner fa-spin fa-2x text-primary"></i>' +
					'<div class="mt-2">Cargando detalle de factura...</div>' +
				'</div>'
			);

			$('#bad').hide();
			$('#okay').show();
		},
		success:function(data){
		   $('#mensaje_mensaje_show').html(data);
		   $('#bad').hide();
		   $('#okay').show();
		},
		error:function(xhr, status, err){
			var info = xhr && xhr.responseText ? xhr.responseText.toString().slice(0, 300) : String(err || status);

			$('#mensaje_mensaje_show').html(
				'<div class="alert alert-danger">' +
					'<b>Error:</b> No se pudo cargar el detalle de la factura.<br>' +
					rfEscapeHtml(info) +
				'</div>'
			);

			$('#bad').hide();
		    $('#okay').show();
		}
	});

	return false;
}
//FIN DETALLES DE FACTURA

//INICIO ROLLBACK
function modal_rollback(facturas_id, pacientes_id){
	if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
		swal({
			title: "¿Esta seguro?",
			text: "¿Desea anular la factura para este registro: Paciente: " + consultarNombre(pacientes_id) + ". Factura N°:  " + getNumeroFactura(facturas_id) + "?",
			content: {
				element: "input",
				attributes: {
					placeholder: "Comentario",
					type: "text",
				},
			},
			icon: "warning",
			buttons: {
				cancel: "Cancelar",
				confirm: {
					text: "¡Sí, anular la factura!",
					closeModal: false,
				},
			},
			dangerMode: true,
			closeOnEsc: false,
			closeOnClickOutside: false			
		}).then((value) => {
			if (value === null || value.trim() === "") {
				swal("¡Necesita escribir algo!", { icon: "error" });
				return false;
			}

			rollback(facturas_id, value);
		});
	}else{
		swal({
			title: "Acceso Denegado",
			text: "No tiene permisos para ejecutar esta acción",
			icon: "error",
			dangerMode: true,
			closeOnEsc: false,
			closeOnClickOutside: false
		});

		return false;
	}
}

function rollback(facturas_id, comentario) {
	try {
		var fecha = getFechaFactura(facturas_id);
		var hoy = new Date();
		var fecha_actual = convertDate(hoy);

		var url = '<?php echo SERVERURL; ?>php/reporte_facturacion/rollback.php';

		if (fecha > fecha_actual) {
			showNotify("error", "Error", "No se puede ejecutar esta acción fuera de esta fecha");
			return false;
		}

		$.ajax({
			type: 'POST',
			url: url,
			dataType: 'json',
			data: {
				facturas_id: facturas_id,
				comentario: comentario
			},
			success: function (resp) {
				if (resp && resp.status === true) {
					listar_reporte_facturacion();
					showNotify("success", "Success", resp.message || "Registro anulado correctamente");
				} else {
					var title = (resp && resp.title) ? resp.title : "Error";
					var msg = (resp && resp.message) ? resp.message : "Error al ejecutar esta acción";
					showNotify("error", title, msg);
				}
			},
			error: function (xhr, status, err) {
				let info = (xhr && xhr.responseText) ? xhr.responseText.toString().slice(0, 300) + "..." : String(err || status);
				showNotify("error", "Error", "Error al ejecutar la solicitud. " + info);
			}
		});

		return false;
	} catch (e) {
		showNotify("error", "Error", "Excepción no controlada: " + (e && e.message ? e.message : e));
		return false;
	}
}

function consultarNombre(pacientes_id){
    var url = '<?php echo SERVERURL; ?>php/pacientes/getNombre.php';
	var resp;

	$.ajax({
	    type:'POST',
		url:url,
		data:'pacientes_id=' + pacientes_id,
		async: false,
		success:function(data){
          resp = data;
		}
	});

	return resp;
}

function getNumeroFactura(facturas_id){
    var url = '<?php echo SERVERURL; ?>php/reporte_facturacion/getNumeroFactura.php';
	var resp;

	$.ajax({
	    type:'POST',
		url:url,
		data:'facturas_id=' + facturas_id,
		async: false,
		success:function(data){
          resp = data;
		}
	});

	return resp;
}
//FIN ROLLBACK

//INICIO GET FECHA FACTURA
function getFechaFactura(facturas_id){
    var url = '<?php echo SERVERURL; ?>php/facturacion/getFechaFactura.php';
	var fecha;

	$.ajax({
	    type:'POST',
		url:url,
		data:'facturas_id=' + facturas_id,
		async: false,
		success:function(data){
		  var datos = eval(data);
		  fecha = datos[0];
		}
	});

	return fecha;
}
//FIN GET FECHA FACTURA

function convertDate(inputFormat) {
	function pad(s) { 
		return (s < 10) ? '0' + s : s; 
	}

	var d = new Date(inputFormat);

	return [d.getFullYear(), pad(d.getMonth()+1), pad(d.getDate())].join('-');
}

/******************************************************************************************************************************************************************************/
// INICIO HELPERS PARA SELECTPICKER Y FILTROS
/******************************************************************************************************************************************************************************/

function rfRefreshSelectpicker($select) {
	if (!$select || !$select.length) {
		return;
	}

	if ($.fn && $.fn.selectpicker) {
		try {
			if (!$select.data('selectpicker')) {
				$select.selectpicker();
			}

			$select.selectpicker('refresh');
		} catch (e) {
			console.warn('No se pudo refrescar selectpicker:', e);
		}
	}
}

function rfParseJsonSeguro(data) {
	if (typeof data === 'object') {
		return data;
	}

	try {
		return JSON.parse(data);
	} catch(e) {
		return null;
	}
}

function rfEscapeHtml(value) {
	if (value === null || value === undefined) {
		return '';
	}

	return String(value)
		.replace(/&/g, '&amp;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;')
		.replace(/"/g, '&quot;')
		.replace(/'/g, '&#039;');
}

function rfConstruirOptionsClientes(data) {
	var html = '<option value="">Cliente</option>';

	if (data === null || typeof data === 'undefined') {
		return html;
	}

	if (typeof data === 'string') {
		var limpio = $.trim(data);

		if (limpio === '') {
			return html;
		}

		var json = rfParseJsonSeguro(limpio);

		if (json !== null) {
			return rfConstruirOptionsClientes(json);
		}

		return limpio;
	}

	if (data.results && $.isArray(data.results)) {
		$.each(data.results, function(i, item) {
			var id = item.id || item.pacientes_id || item.value || '';
			var text = item.text || item.nombre || item.paciente || item.label || '';

			if (id !== '' && text !== '') {
				html += '<option value="' + rfEscapeHtml(id) + '">' + rfEscapeHtml(text) + '</option>';
			}
		});

		return html;
	}

	if (data.data && $.isArray(data.data)) {
		$.each(data.data, function(i, item) {
			var id = item.id || item.pacientes_id || item.value || '';
			var text = item.text || item.nombre || item.paciente || item.label || '';

			if (id !== '' && text !== '') {
				html += '<option value="' + rfEscapeHtml(id) + '">' + rfEscapeHtml(text) + '</option>';
			}
		});

		return html;
	}

	if ($.isArray(data)) {
		$.each(data, function(i, item) {
			if (typeof item === 'object') {
				var id = item.id || item.pacientes_id || item.value || '';
				var text = item.text || item.nombre || item.paciente || item.label || '';

				if (id !== '' && text !== '') {
					html += '<option value="' + rfEscapeHtml(id) + '">' + rfEscapeHtml(text) + '</option>';
				}
			}
		});

		return html;
	}

	return html;
}

/******************************************************************************************************************************************************************************/
// FIN HELPERS PARA SELECTPICKER Y FILTROS
/******************************************************************************************************************************************************************************/

/******************************************************************************************************************************************************************************/
// INICIO CARGA DE FILTROS
/******************************************************************************************************************************************************************************/

function getEstado(){
    var url = '<?php echo SERVERURL; ?>php/reporte_facturacion/getEstado.php';
	var $estado = $('#form_main_facturacion_reportes #estado');

	$.ajax({
        type: "POST",
        url: url,
	    async: true,
        success: function(data){
		    $estado.html(data);

			if (($estado.val() === null || $estado.val() === '' || typeof $estado.val() === 'undefined') && $estado.find('option[value="1"]').length > 0) {
				$estado.val('1');
			}

			rfRefreshSelectpicker($estado);
        },
		error: function(xhr, status, error){
			console.error('Error cargando estados:', xhr.responseText || error);
			showNotify("error", "Error", "No se pudieron cargar los estados.");
		}
     });
}

function getTipoPacienteGrupo(){
    var url = '<?php echo SERVERURL; ?>php/facturacion/getTipoPaciente.php';
	var $tipo = $('#form_main_facturacion_reportes #tipo_paciente_grupo');

	$.ajax({
        type: "POST",
        url: url,
        success: function(data){
		    $tipo.html(data);

			if (($tipo.val() === null || $tipo.val() === '' || typeof $tipo.val() === 'undefined') && $tipo.find('option[value="1"]').length > 0) {
				$tipo.val('1');
			}

			rfRefreshSelectpicker($tipo);

			var tipoSeleccionado = $tipo.val();

			if (tipoSeleccionado === null || tipoSeleccionado === '' || typeof tipoSeleccionado === 'undefined') {
				tipoSeleccionado = 1;
			}

			getPacienteGrupo(tipoSeleccionado);
		},
		error: function(xhr, status, error){
			console.error('Error cargando tipo de cliente:', xhr.responseText || error);
			showNotify("error", "Error", "No se pudieron cargar los tipos de cliente.");
		}
     });
}

function getPacienteGrupo(tipo_paciente){
    var url = '<?php echo SERVERURL; ?>php/facturacion/getPacienteGrupo.php';
	var $cliente = $('#form_main_facturacion_reportes #pacientesIDGrupo');

	if (tipo_paciente === null || tipo_paciente === '' || typeof tipo_paciente === 'undefined') {
		tipo_paciente = 1;
	}

	$cliente.prop('disabled', true);
	$cliente.html('<option value="">Cargando clientes...</option>');
	$cliente.val('');
	rfRefreshSelectpicker($cliente);

	$.ajax({
		type: "POST",
		url: url,
		dataType: "json",
		cache: false,
		data: {
			tipo_paciente: tipo_paciente
		},
		success: function(data){
			var html = rfConstruirOptionsClientes(data);

			$cliente.html(html);
			$cliente.val('');
			$cliente.prop('disabled', false);

			rfRefreshSelectpicker($cliente);

			listar_reporte_facturacion();
		},
		error: function(xhr, status, error){
			console.error('Error cargando clientes:', xhr.responseText || error);

			$cliente.html('<option value="">Cliente</option>');
			$cliente.val('');
			$cliente.prop('disabled', false);

			rfRefreshSelectpicker($cliente);

			showNotify("error", "Error", "No se pudieron cargar los clientes.");
		}
	});
}

$(document)
.off('change.reporteTipoPacienteGrupo changed.bs.select.reporteTipoPacienteGrupo', '#form_main_facturacion_reportes #tipo_paciente_grupo')
.on('change.reporteTipoPacienteGrupo changed.bs.select.reporteTipoPacienteGrupo', '#form_main_facturacion_reportes #tipo_paciente_grupo', function(){
	var tipo = $(this).val();

	if (tipo === null || tipo === '' || typeof tipo === 'undefined') {
		tipo = 1;
	}

	var $cliente = $('#form_main_facturacion_reportes #pacientesIDGrupo');

	$cliente.val('');
	$cliente.html('<option value="">Cliente</option>');
	rfRefreshSelectpicker($cliente);

	getPacienteGrupo(tipo);
});

$(document)
.off('change.reportePacienteGrupo changed.bs.select.reportePacienteGrupo', '#form_main_facturacion_reportes #pacientesIDGrupo')
.on('change.reportePacienteGrupo changed.bs.select.reportePacienteGrupo', '#form_main_facturacion_reportes #pacientesIDGrupo', function(){
	listar_reporte_facturacion();
});

/******************************************************************************************************************************************************************************/
// FIN CARGA DE FILTROS
/******************************************************************************************************************************************************************************/

var table_reporte_facturacion_global = null;

function initReporteFacturacionStyles() {
	if ($('#reporteFacturacionStyles').length > 0) {
		return;
	}

	$('head').append(`
		<style id="reporteFacturacionStyles">
			#dataTableReporteFacturacionMain {
				width: 100% !important;
				border-collapse: separate !important;
				border-spacing: 0 !important;
				font-size: 14px;
			}

			#dataTableReporteFacturacionMain thead th {
				background: #129aaa !important;
				color: #fff !important;
				font-weight: 700 !important;
				padding: 14px 12px !important;
				vertical-align: middle !important;
				border: none !important;
				white-space: nowrap !important;
			}

			#dataTableReporteFacturacionMain tbody td {
				padding: 14px 12px !important;
				vertical-align: middle !important;
				border-top: 1px solid #e8edf2 !important;
				color: #222 !important;
			}

			#dataTableReporteFacturacionMain tbody tr:nth-child(even) {
				background: #f4f6f8 !important;
			}

			#dataTableReporteFacturacionMain tbody tr:hover {
				background: #eef9fb !important;
			}

			.rf-link-fecha {
				display: inline-flex;
				align-items: center;
				gap: 6px;
				background: #f7f7f7;
				border: 1px solid #d8dde3;
				color: #007bff !important;
				border-radius: 12px;
				padding: 6px 10px;
				font-weight: 700;
				text-decoration: none !important;
				white-space: nowrap;
				cursor: pointer;
			}

			.rf-link-fecha:hover {
				background: #eef7ff;
				border-color: #b8dcff;
				color: #0056b3 !important;
			}

			.rf-badge {
				display: inline-flex;
				align-items: center;
				justify-content: center;
				gap: 6px;
				border-radius: 14px;
				padding: 7px 12px;
				font-weight: 700;
				line-height: 1;
				white-space: nowrap;
			}

			.rf-badge-contado {
				background: #ffc107;
				color: #111;
				border: 1px solid #e0a800;
			}

			.rf-badge-credito {
				background: #17a2b8;
				color: #fff;
				border: 1px solid #138496;
			}

			.rf-badge-individual {
				background: #eef7ff;
				border: 2px solid #006992;
				color: #006992;
			}

			.rf-badge-grupal {
				background: #eaf8ee;
				border: 2px solid #198754;
				color: #198754;
			}

			.rf-muestra {
				display: inline-flex;
				align-items: center;
				gap: 6px;
				background: #eef7ff;
				border: 1px solid #b8dcff;
				color: #006992;
				border-radius: 12px;
				padding: 6px 10px;
				font-weight: 700;
				white-space: nowrap;
			}

			.rf-factura {
				display: inline-flex;
				align-items: center;
				gap: 6px;
				background: #fff7e6;
				border: 1px solid #ffd98a;
				color: #9a6500;
				border-radius: 12px;
				padding: 6px 10px;
				font-weight: 700;
				white-space: nowrap;
			}

			.rf-cliente {
				font-weight: 700;
				color: #243447;
				line-height: 1.35;
			}

			.rf-identidad {
				display: inline-flex;
				align-items: center;
				gap: 6px;
				background: #f3f7fa;
				border: 1px solid #d9e4ec;
				color: #333;
				border-radius: 12px;
				padding: 6px 10px;
				font-weight: 600;
				white-space: nowrap;
			}

			.rf-profesional {
				font-weight: 600;
				color: #333;
				line-height: 1.35;
			}

			.rf-money {
				font-weight: 700;
				white-space: nowrap;
			}

			.rf-money-normal {
				color: #333;
			}

			.rf-money-isv {
				color: #006992;
			}

			.rf-money-descuento {
				color: #cc2936;
			}

			.rf-money-neto {
				display: inline-flex;
				align-items: center;
				justify-content: center;
				min-width: 86px;
				border: 2px solid #a5bf13;
				color: #7f960c;
				border-radius: 12px;
				padding: 6px 10px;
				font-weight: 800;
				white-space: nowrap;
				cursor: help;
			}

			.rf-btn-acciones {
				background: #0d6efd !important;
				border-color: #0d6efd !important;
				color: #fff !important;
				font-weight: 700 !important;
				border-radius: 7px !important;
				padding: 7px 12px !important;
				box-shadow: 0 2px 5px rgba(13, 110, 253, .20);
				white-space: nowrap;
				line-height: 1.2;
			}

			.rf-btn-acciones:hover {
				background: #0b5ed7 !important;
				border-color: #0b5ed7 !important;
				color: #fff !important;
			}

			.rf-dropdown-menu {
				border: 0 !important;
				border-radius: 10px !important;
				box-shadow: 0 10px 25px rgba(0,0,0,.12) !important;
				overflow: hidden;
				min-width: 190px;
			}

			.rf-dropdown-menu .dropdown-item {
				padding: 10px 14px !important;
				font-weight: 600 !important;
				color: #333;
			}

			.rf-dropdown-menu .dropdown-item:hover {
				background: #f2f7ff !important;
			}

			.rf-historial-fecha {
				display: inline-flex;
				align-items: center;
				justify-content: center;
				margin-left: 5px;
				padding: 6px 8px;
				border-radius: 8px;
				background: #ffc107;
				border: 1px solid #e0a800;
				color: #111;
				cursor: help;
			}

			#dataTableReporteFacturacionMain tfoot th,
			#dataTableReporteFacturacionMain tfoot td {
				background: #129aaa !important;
				color: #fff !important;
				font-weight: 800 !important;
				padding: 13px 12px !important;
				border: none !important;
			}

			.dataTables_wrapper .dt-buttons .btn {
				border-radius: 7px !important;
				font-weight: 600 !important;
				padding: 8px 13px !important;
				margin-right: 4px !important;
				box-shadow: 0 2px 5px rgba(0,0,0,.10);
			}

			.dataTables_filter input {
				border: 1px solid #b8dcff !important;
				border-radius: 7px !important;
				padding: 7px 10px !important;
				outline: none !important;
			}

			.dataTables_filter input:focus {
				border-color: #0d6efd !important;
				box-shadow: 0 0 0 2px rgba(13,110,253,.15) !important;
			}
		</style>
	`);
}

function initModalCambioFechaFactura() {
	if ($('#modalCambioFechaFactura').length > 0) {
		return;
	}

	$('body').append(`
		<div class="modal fade" id="modalCambioFechaFactura" tabindex="-1" role="dialog" aria-labelledby="modalCambioFechaFacturaLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<form id="formCambioFechaFactura">
						<div class="modal-header bg-primary text-white">
							<h5 class="modal-title" id="modalCambioFechaFacturaLabel">
								<i class="fas fa-calendar-alt"></i> Cambiar fecha de factura
							</h5>
							<button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>

						<div class="modal-body">
							<input type="hidden" id="facturas_id" name="facturas_id">

							<div class="alert alert-warning mb-3">
								<div style="font-weight:700; margin-bottom:6px;">
									<i class="fas fa-exclamation-triangle"></i> Importante
								</div>

								<div>
									Al cambiar la fecha de esta factura, el correlativo puede quedar fuera del orden cronológico de emisión.
								</div>

								<div class="mt-1">
									Esta acción quedará registrada en auditoría con la fecha anterior, la fecha nueva, el usuario que realizó el cambio y el comentario obligatorio.
								</div>

								<div class="mt-1">
									Si la factura es grupal, el cambio aplicará a todas las facturas relacionadas al mismo número.
								</div>
							</div>

							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label><b>Factura</b></label>
										<input type="text" class="form-control" id="factura" readonly>
									</div>
								</div>

								<div class="col-md-6">
									<div class="form-group">
										<label><b>Tipo</b></label>
										<input type="text" class="form-control" id="tipo_factura_agrupada" readonly>
									</div>
								</div>

								<div class="col-md-6">
									<div class="form-group">
										<label><b>Paciente</b></label>
										<input type="text" class="form-control" id="paciente" readonly>
									</div>
								</div>

								<div class="col-md-6">
									<div class="form-group">
										<label><b>Identidad</b></label>
										<input type="text" class="form-control" id="identidad" readonly>
									</div>
								</div>

								<div class="col-md-6">
									<div class="form-group">
										<label><b>Fecha actual</b></label>
										<input type="text" class="form-control" id="fecha_actual_texto" readonly>
									</div>
								</div>

								<div class="col-md-6">
									<div class="form-group">
										<label><b>Nueva fecha</b> <span class="text-danger">*</span></label>
										<input type="date" class="form-control" id="fecha_nueva" name="fecha_nueva" required>
									</div>
								</div>

								<div class="col-md-6">
									<div class="form-group">
										<label><b>Facturas afectadas</b></label>
										<input type="text" class="form-control" id="total_facturas_afectadas" readonly>
									</div>
								</div>

								<div class="col-md-6">
									<div class="form-group">
										<label><b>Pago registrado</b></label>
										<input type="text" class="form-control" id="tiene_pago" readonly>
									</div>
								</div>

								<div class="col-md-12">
									<div class="alert alert-info mb-3">
										<i class="fas fa-info-circle"></i>
										El pago no se modificará. Solo se cambiará la fecha del documento/factura.
									</div>
								</div>

								<div class="col-md-12">
									<div class="form-group">
										<label>
											<b>Comentario / motivo del cambio</b> 
											<span class="text-danger">*</span>
										</label>

										<textarea 
											class="form-control" 
											id="comentario" 
											name="comentario" 
											rows="4" 
											maxlength="500" 
											placeholder="Ejemplo: Se corrige fecha por solicitud del cliente, ya que la factura debía quedar registrada con fecha anterior..."
											required></textarea>

										<small class="text-muted">
											Este comentario es obligatorio y quedará guardado como respaldo de auditoría.
										</small>
									</div>
								</div>
							</div>

							<div id="detalleFacturaGrupalCambioFecha" class="mt-2" style="display:none;"></div>
						</div>

						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-dismiss="modal">
								<i class="fas fa-times"></i> Cancelar
							</button>

							<button type="submit" class="btn btn-primary" id="btnGuardarCambioFechaFactura">
								<i class="fas fa-save"></i> Guardar cambio
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	`);

	$("#modalCambioFechaFactura").on('shown.bs.modal', function(){
		$(this).find('#formCambioFechaFactura #fecha_nueva').focus();
	});
}

function rfToNumber(value) {
	if (value === null || value === undefined || value === '') {
		return 0;
	}

	var limpio = String(value).replace(/,/g, '');
	var numero = parseFloat(limpio);

	return isNaN(numero) ? 0 : numero;
}

function rfMoney(value, claseExtra) {
	var numero = rfToNumber(value);

	return '<span class="rf-money ' + claseExtra + '">' + numero.toFixed(2) + '</span>';
}

var listar_reporte_facturacion = function(){
	initReporteFacturacionStyles();

	if ($.fn.DataTable.isDataTable("#dataTableReporteFacturacionMain")) {
		table_reporte_facturacion_global.ajax.reload(null, false);
		return false;
	}
	
	table_reporte_facturacion_global = $("#dataTableReporteFacturacionMain").DataTable({
		"destroy": true,
		"processing": true,
		"ajax": {
			"method": "POST",
			"url": "<?php echo SERVERURL; ?>php/reporte_facturacion/llenarDataTableReporteFacturas.php",
			"data": function(d) {
				d.fechai = $('#form_main_facturacion_reportes #fecha_b').val();
				d.fechaf = $('#form_main_facturacion_reportes #fecha_f').val();
				d.pacientesIDGrupo = $('#form_main_facturacion_reportes #pacientesIDGrupo').val() || '';
				d.estado = $('#form_main_facturacion_reportes #estado').val() || 1;
				d.dato = $('#dataTableReporteFacturacionMain_filter input').val() || '';
			}
		},
		"columns": [
			{
				"data": "fecha",
				"render": function(data, type, row) {
					if (type !== 'display') {
						return data;
					}

					var badgeCambio = '';

					if (parseInt(row.cantidad_cambios_fecha || 0) > 0) {
						badgeCambio = 
							'<span class="rf-historial-fecha" data-toggle="tooltip" data-placement="top" title="Fecha modificada. Último cambio: ' + rfEscapeHtml(row.ultima_modificacion_fecha || '') + '">' +
								'<i class="fas fa-history"></i>' +
							'</span>';
					}

					return '<a href="#" class="showInvoiceDetail rf-link-fecha">' +
						'<i class="fas fa-calendar-alt"></i>' +
						'<span>' + rfEscapeHtml(data) + '</span>' +
					'</a>' + badgeCambio;
				}
			},
			{
				"data": "tipo_documento",
				"render": function(data, type, row) {
					if (type !== 'display') {
						return data;
					}

					if (data === 'Contado') {
						return '<span class="rf-badge rf-badge-contado">' +
							'<i class="fas fa-file-invoice-dollar"></i>' +
							'<span>Contado</span>' +
						'</span>';
					}

					return '<span class="rf-badge rf-badge-credito">' +
						'<i class="fas fa-credit-card"></i>' +
						'<span>Crédito</span>' +
					'</span>';
				}
			},
			{
				"data": "muestra",
				"render": function(data, type, row) {
					if (type !== 'display') {
						return data;
					}

					if (!data) {
						return '<span class="rf-badge" style="background:#f2f2f2; color:#888;">Sin muestra</span>';
					}

					return '<span class="rf-muestra">' +
						'<i class="fas fa-vial"></i>' +
						'<span>' + rfEscapeHtml(data) + '</span>' +
					'</span>';
				}
			},
			{
				"data": "factura",
				"render": function(data, type, row) {
					if (type !== 'display') {
						return data;
					}

					if (!data || data === 'Aún no se ha generado') {
						return '<span class="rf-badge" style="background:#f2f2f2; color:#888;">Aún no se ha generado</span>';
					}

					return '<span class="rf-factura">' +
						'<i class="fas fa-file-invoice"></i>' +
						'<span>' + rfEscapeHtml(data) + '</span>' +
					'</span>';
				}
			},
			{
				"data": "paciente",
				"render": function(data, type, row) {
					if (type !== 'display') {
						return data;
					}

					return '<div class="rf-cliente">' +
						'<i class="fas fa-user text-info"></i> ' + rfEscapeHtml(data) +
					'</div>';
				}
			},
			{
				"data": "identidad",
				"render": function(data, type, row) {
					if (type !== 'display') {
						return data;
					}

					return '<span class="rf-identidad">' +
						'<i class="fas fa-id-card"></i>' +
						'<span>' + rfEscapeHtml(data) + '</span>' +
					'</span>';
				}
			},
			{
				"data": "profesional",
				"render": function(data, type, row) {
					if (type !== 'display') {
						return data;
					}

					return '<div class="rf-profesional">' +
						'<i class="fas fa-user-md text-primary"></i> ' + rfEscapeHtml(data) +
					'</div>';
				}
			},
			{
				"data": "precio",
				"className": "text-right",
				"render": function(data, type, row) {
					if (type !== 'display') {
						return rfToNumber(data);
					}

					return rfMoney(data, 'rf-money-normal');
				}
			},
			{
				"data": "isv_neto",
				"className": "text-right",
				"render": function(data, type, row) {
					if (type !== 'display') {
						return rfToNumber(data);
					}

					return rfMoney(data, 'rf-money-isv');
				}
			},
			{
				"data": "descuento",
				"className": "text-right",
				"render": function(data, type, row) {
					if (type !== 'display') {
						return rfToNumber(data);
					}

					return rfMoney(data, 'rf-money-descuento');
				}
			},
			{
				"data": "total",
				"className": "text-right",
				"render": function(data, type, row) {
					if (type !== 'display') {
						return rfToNumber(data);
					}

					var estadoPago = row.estado_pago;
					var tooltip = 'Estado desconocido';

					if (estadoPago === 'Pago Pendiente') {
						tooltip = 'Pago Pendiente.';
					} else if (estadoPago === 'Pagada') {
						tooltip = 'Pago Realizado.';
					}

					return '<span class="rf-money-neto" data-toggle="tooltip" data-placement="top" title="' + tooltip + '">' +
						rfToNumber(data).toFixed(2) +
					'</span>';
				}
			},
			{
				"data": "servicio",
				"render": function(data, type, row) {
					if (type !== 'display') {
						return data;
					}

					return '<span style="font-weight:600; color:#333;">' + rfEscapeHtml(data) + '</span>';
				}
			},
			{
				"data": "tipo_factura_agrupada",
				"render": function(data, type, row) {
					if (type !== 'display') {
						return data;
					}

					if (data === 'Grupal') {
						return '<span class="rf-badge rf-badge-grupal">' +
							'<i class="fas fa-layer-group"></i>' +
							'<span>Grupal</span>' +
						'</span>';
					}

					return '<span class="rf-badge rf-badge-individual">' +
						'<i class="fas fa-user"></i>' +
						'<span>Individual</span>' +
					'</span>';
				}
			},
			{
				"data": null,
				"orderable": false,
				"className": "text-center",
				"defaultContent": 
					'<div class="btn-group">' +
						'<button type="button" class="btn btn-primary btn-sm dropdown-toggle rf-btn-acciones" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' +
							'<i class="fas fa-cog"></i> Acciones' +
						'</button>' +
						'<div class="dropdown-menu dropdown-menu-right rf-dropdown-menu">' +
							'<a class="dropdown-item printBill" href="#"><i class="fas fa-print text-primary mr-2"></i> Imprimir</a>' +
							'<a class="dropdown-item closeBill" href="#"><i class="fas fa-calculator text-success mr-2"></i> Cierre</a>' +
							'<a class="dropdown-item changeDateBill" href="#"><i class="fas fa-calendar-alt text-warning mr-2"></i> Cambiar fecha</a>' +
							'<div class="dropdown-divider"></div>' +
							'<a class="dropdown-item deleteBill text-danger" href="#"><i class="fas fa-undo mr-2"></i> Anular</a>' +
						'</div>' +
					'</div>'
			}
		],
		"footerCallback": function(row, data, start, end, display) {
			var api = this.api();

			$('#footer-importe').html('');
			$('#footer-isv').html('');
			$('#footer-descuento').html('');
			$('#footer-neto').html('');
			$('#tipo_pago').html('');
			$('#total_pago').html('');

			var sumaColumna = function(index) {
				return api.column(index, { page: 'current' })
					.data()
					.reduce(function(a, b) {
						return rfToNumber(a) + rfToNumber(b);
					}, 0);
			};

			var totalImporte = sumaColumna(7);
			var totalISV = sumaColumna(8);
			var totalDescuento = sumaColumna(9);
			var totalNeto = sumaColumna(10);

			var formatter = new Intl.NumberFormat('es-HN', {
				style: 'currency',
				currency: 'HNL',
				minimumFractionDigits: 2
			});

			$('#footer-importe').html(formatter.format(totalImporte));
			$('#footer-isv').html(formatter.format(totalISV));
			$('#footer-descuento').html(formatter.format(totalDescuento));
			$('#footer-neto').html(formatter.format(totalNeto));
		},
		"createdRow": function(row, data, dataIndex) {
			$(row).find('td').css('vertical-align', 'middle');
		},
		"order": [],
		"lengthMenu": lengthMenu20,
		"stateSave": true,
		"bDestroy": true,
		"language": idioma_español,
		"dom": dom,
		"buttons": [
			{
				text: '<i class="fas fa-sync-alt fa-lg"></i> Actualizar',
				titleAttr: 'Actualizar Facturas',
				className: 'btn btn-info',
				action: function(){
					if (table_reporte_facturacion_global !== null) {
						table_reporte_facturacion_global.ajax.reload(null, false);
					}
				}
			},
			{
				text: '<i class="fas fa-calculator fa-lg"></i> Cierre',
				titleAttr: 'Cierre de Caja',
				className: 'btn btn-primary',
				action: function(){
					cierreBill();
				}
			},
			{
				text: '<i class="fa-solid fa-file-pdf fa-lg"></i> Reporte PDF',
				titleAttr: 'Reporte de Facturación PDF',
				className: 'btn btn-danger',
				action: function(){
					reporteFacturacion();
				}
			},
			{
				text: '<i class="fa-solid fa-file-excel fa-lg"></i> Reporte Excel',
				titleAttr: 'Reporte de Facturación Excel',
				className: 'btn btn-success',
				action: function(){
					reporteFacturacionExcel();
				}
			}
		]
	});

	$('#dataTableReporteFacturacionMain').off('draw.dt').on('draw.dt', function() {
		$('[data-toggle="tooltip"]').tooltip();
	});

	$(document).off('keyup', '#dataTableReporteFacturacionMain_filter input').on('keyup', '#dataTableReporteFacturacionMain_filter input', function(){
		clearTimeout(window.timerBusquedaFacturaReporte);

		window.timerBusquedaFacturaReporte = setTimeout(function(){
			if (table_reporte_facturacion_global !== null) {
				table_reporte_facturacion_global.ajax.reload(null, true);
			}
		}, 500);
	});

	$('#buscar').focus();

	show_invoice_detail_dataTable("#dataTableReporteFacturacionMain tbody", table_reporte_facturacion_global);
	print_bill_dataTable("#dataTableReporteFacturacionMain tbody", table_reporte_facturacion_global);
	close_bill_dataTable("#dataTableReporteFacturacionMain tbody", table_reporte_facturacion_global);
	change_date_bill_dataTable("#dataTableReporteFacturacionMain tbody", table_reporte_facturacion_global);
	delete_bill_dataTable("#dataTableReporteFacturacionMain tbody", table_reporte_facturacion_global);

	return false;
}

var show_invoice_detail_dataTable = function(tbody, table){
	$(tbody).off("click", "a.showInvoiceDetail");

	$(tbody).on("click", "a.showInvoiceDetail", function(e){
		e.preventDefault();

		var data = table.row($(this).parents("tr")).data();

		if (!data || !data.facturas_id) {
			showNotify("error", "Error", "No se pudo obtener la información de la factura.");
			return false;
		}

		invoicesDetails(data.facturas_id);

		return false;
	});
}

var print_bill_dataTable = function(tbody, table){
	$(tbody).off("click", "a.printBill");

	$(tbody).on("click", "a.printBill", function(e){
		e.preventDefault();

		var data = table.row($(this).parents("tr")).data();

		if(data.tipo_factura_agrupada === "Individual") {
			printBill(data.facturas_id);
			return false;
		}	
		
		printBillGroup(data.numero);

		return false;
	});
}

var close_bill_dataTable = function(tbody, table){
	$(tbody).off("click", "a.closeBill");

	$(tbody).on("click", "a.closeBill", function(e){
		e.preventDefault();

		var data = table.row($(this).parents("tr")).data();
		
		swal({
			title: "Información",
			text: "Esta opción se encuentra en desarrollo",
			icon: "warning",
			dangerMode: true,
			closeOnEsc: false,
			closeOnClickOutside: false			
		});

		return false;
	});
}

var delete_bill_dataTable = function(tbody, table){
	$(tbody).off("click", "a.deleteBill");

	$(tbody).on("click", "a.deleteBill", function(e){
		e.preventDefault();

		var data = table.row($(this).parents("tr")).data();

		if (!data || !data.facturas_id) {
			showNotify("error", "Error", "No se pudo obtener la factura seleccionada.");
			return false;
		}
		
		modal_rollback(data.facturas_id, data.pacientes_id);

		return false;
	});
}

var change_date_bill_dataTable = function(tbody, table){
	$(tbody).off("click", "a.changeDateBill");

	$(tbody).on("click", "a.changeDateBill", function(e){
		e.preventDefault();

		var data = table.row($(this).parents("tr")).data();

		if (!data || !data.facturas_id) {
			showNotify("error", "Error", "No se pudo obtener la factura seleccionada.");
			return false;
		}

		modalCambiarFechaFactura(data.facturas_id);

		return false;
	});
}

function modalCambiarFechaFactura(facturas_id) {
	if (!(getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 4)) {
		swal({
			title: "Acceso Denegado",
			text: "No tiene permisos para cambiar la fecha de una factura",
			icon: "error",
			dangerMode: true,
			closeOnEsc: false,
			closeOnClickOutside: false
		});

		return false;
	}

	var url = '<?php echo SERVERURL; ?>php/reporte_facturacion/getInfoFacturaCambioFecha.php';

	$.ajax({
		type: 'POST',
		url: url,
		dataType: 'json',
		data: {
			facturas_id: facturas_id
		},
		success: function(resp) {
			if (resp && resp.status === true) {
				var item = resp.data;

				$('#formCambioFechaFactura')[0].reset();

				$('#formCambioFechaFactura #facturas_id').val(item.facturas_id);
				$('#formCambioFechaFactura #factura').val(item.factura);
				$('#formCambioFechaFactura #tipo_factura_agrupada').val(item.tipo_factura_agrupada);
				$('#formCambioFechaFactura #paciente').val(item.paciente);
				$('#formCambioFechaFactura #identidad').val(item.identidad);
				$('#formCambioFechaFactura #fecha_actual_texto').val(item.fecha_actual_formato);
				$('#formCambioFechaFactura #fecha_nueva').val(item.fecha_actual);
				$('#formCambioFechaFactura #total_facturas_afectadas').val(item.total_facturas_afectadas);
				$('#formCambioFechaFactura #tiene_pago').val(item.tiene_pago == 1 ? 'Sí, tiene pago registrado' : 'No tiene pago registrado');

				if (item.tipo_factura_agrupada === 'Grupal' && item.facturas_grupo && item.facturas_grupo.length > 0) {
					var html = '';

					html += '<div class="alert alert-info">';
					html += '<b><i class="fas fa-layer-group"></i> Detalle de factura grupal</b><br>';
					html += 'Al guardar, se cambiará la fecha de estos registros:';
					html += '</div>';

					html += '<div class="table-responsive">';
					html += '<table class="table table-sm table-bordered">';
					html += '<thead class="thead-light">';
					html += '<tr>';
					html += '<th>Factura ID</th>';
					html += '<th>Paciente</th>';
					html += '<th>Identidad</th>';
					html += '<th>Fecha actual</th>';
					html += '</tr>';
					html += '</thead>';
					html += '<tbody>';

					$.each(item.facturas_grupo, function(i, row) {
						html += '<tr>';
						html += '<td>' + rfEscapeHtml(row.facturas_id) + '</td>';
						html += '<td>' + rfEscapeHtml(row.paciente) + '</td>';
						html += '<td>' + rfEscapeHtml(row.identidad) + '</td>';
						html += '<td>' + rfEscapeHtml(row.fecha) + '</td>';
						html += '</tr>';
					});

					html += '</tbody>';
					html += '</table>';
					html += '</div>';

					$('#detalleFacturaGrupalCambioFecha').html(html).show();
				} else {
					$('#detalleFacturaGrupalCambioFecha').html('').hide();
				}

				$('#btnGuardarCambioFechaFactura').attr('disabled', false);

				$('#modalCambioFechaFactura').modal({
					show: true,
					keyboard: false,
					backdrop: 'static'
				});
			} else {
				var title = resp && resp.title ? resp.title : 'Error';
				var message = resp && resp.message ? resp.message : 'No se pudo obtener la información de la factura.';

				showNotify("error", title, message);
			}
		},
		error: function(xhr, status, err) {
			var info = xhr && xhr.responseText ? xhr.responseText.toString().slice(0, 300) : String(err || status);
			showNotify("error", "Error", "Error al consultar la factura. " + info);
		}
	});

	return false;
}

$(document).off('submit', '#formCambioFechaFactura').on('submit', '#formCambioFechaFactura', function(e) {
	e.preventDefault();

	var facturas_id = $('#formCambioFechaFactura #facturas_id').val();
	var fecha_nueva = $('#formCambioFechaFactura #fecha_nueva').val();
	var comentario = $.trim($('#formCambioFechaFactura #comentario').val());

	if (facturas_id == '') {
		showNotify("error", "Error", "No se encontró la factura seleccionada.");
		return false;
	}

	if (fecha_nueva == '') {
		showNotify("error", "Fecha requerida", "Debe seleccionar la nueva fecha de la factura.");
		$('#formCambioFechaFactura #fecha_nueva').focus();
		return false;
	}

	if (comentario == '') {
		showNotify("error", "Comentario requerido", "Debe escribir el motivo del cambio de fecha.");
		$('#formCambioFechaFactura #comentario').focus();
		return false;
	}

	if (comentario.length < 5) {
		showNotify("error", "Comentario muy corto", "Debe escribir un comentario más claro.");
		$('#formCambioFechaFactura #comentario').focus();
		return false;
	}

	swal({
		title: "¿Confirmar cambio de fecha?",
		text: "Esta acción puede hacer que el correlativo de la factura quede fuera del orden cronológico de emisión. El cambio quedará registrado en auditoría con el comentario indicado.",
		icon: "warning",
		buttons: {
			cancel: "Cancelar",
			confirm: {
				text: "Sí, cambiar fecha",
				closeModal: true
			}
		},
		dangerMode: true,
		closeOnEsc: false,
		closeOnClickOutside: false
	}).then((confirmado) => {
		if (confirmado) {
			guardarCambioFechaFactura();
		}
	});

	return false;
});

function guardarCambioFechaFactura() {
	var url = '<?php echo SERVERURL; ?>php/reporte_facturacion/cambiarFechaFactura.php';

	$('#btnGuardarCambioFechaFactura').attr('disabled', true);

	$.ajax({
		type: 'POST',
		url: url,
		dataType: 'json',
		data: $('#formCambioFechaFactura').serialize(),
		success: function(resp) {
			if (resp && resp.status === true) {
				$('#modalCambioFechaFactura').modal('hide');

				showNotify("success", resp.title || "Fecha actualizada", resp.message || "La fecha fue actualizada correctamente.");

				if (table_reporte_facturacion_global !== null) {
					table_reporte_facturacion_global.ajax.reload(null, false);
				}
			} else {
				$('#btnGuardarCambioFechaFactura').attr('disabled', false);

				var title = resp && resp.title ? resp.title : 'Error';
				var message = resp && resp.message ? resp.message : 'No se pudo cambiar la fecha.';

				showNotify("error", title, message);
			}
		},
		error: function(xhr, status, err) {
			$('#btnGuardarCambioFechaFactura').attr('disabled', false);

			var info = xhr && xhr.responseText ? xhr.responseText.toString().slice(0, 300) : String(err || status);
			showNotify("error", "Error", "Error al guardar el cambio. " + info);
		}
	});

	return false;
}

function reporteFacturacion() {
    var fechai = $('#form_main_facturacion_reportes #fecha_b').val();
    var fechaf = $('#form_main_facturacion_reportes #fecha_f').val();  
    var clientes = $('#form_main_facturacion_reportes #pacientesIDGrupo').val() || '';
    var profesional = $('#form_main_facturacion_reportes #profesional').val() || '';
    var estado = $('#form_main_facturacion_reportes #estado').val() || 1;

    var params = {
        "estado": estado,
        "type": "Reporte_facturas_laboratorio_cami",
        "fechai": fechai,
        "fechaf": fechaf,
        "clientes": clientes,
        "profesional": profesional,
		"documento_id": 1,
        "db": "<?php echo DB; ?>"
    };

    viewReport(params);
}

function reporteFacturacionExcel() {
    var fechai = $('#form_main_facturacion_reportes #fecha_b').val();
    var fechaf = $('#form_main_facturacion_reportes #fecha_f').val();  
    var clientes = $('#form_main_facturacion_reportes #pacientesIDGrupo').val() || '';
    var profesional = $('#form_main_facturacion_reportes #profesional').val() || '';
    var estado = $('#form_main_facturacion_reportes #estado').val() || 1;

    var params = {
        "estado": estado,
        "type": "Reporte_facturas_laboratorio_cami",
        "fechai": fechai,
        "fechaf": fechaf,
        "clientes": clientes,
        "profesional": profesional,
		"tipo": "Excel",
		"documento_id": 1,
        "db": "<?php echo DB; ?>"
    };

    viewReport(params);
}
</script>