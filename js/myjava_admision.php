<script>
// myjava_admision optimizado y ordenado

/****************************************************************************************************************************************************************/
// HELPERS GLOBALES
/****************************************************************************************************************************************************************/

var requestPaginationAdmision = null;
var requestPaginationMuestras = null;
var requestClientesPorTipoMuestras = null;
var requestDatosClienteAdmision = null;

var timerPaginationAdmision = null;
var timerPaginationMuestras = null;
var timerHistoricoMuestras = null;

var cachePacienteTipo = {};
var cacheNombrePaciente = {};
var cacheExpedientePaciente = {};
var cacheNumeroMuestra = {};
var cacheDatosClienteAdmision = {};

var catalogosModalClienteCargados = false;
var catalogosMuestrasCargados = false;
var clientesAdmisionCargados = false;
var generoAdmisionCargado = false;
var modalClienteAbriendose = false;

function safeRefresh($el){
	if ($el && $el.length && $.fn && $.fn.selectpicker) {
		$el.selectpicker('refresh');
	}
}

function safeShowLoading(texto){
	if (typeof showLoading === 'function') {
		showLoading(texto || "Por favor espere...");
	}
}

function safeHideLoading(){
	if (typeof hideLoading === 'function') {
		hideLoading();
	}
}

function showErrorAjax(mensaje){
	if (typeof swal === 'function') {
		swal({
			title: 'Error',
			text: mensaje || 'No se pudo completar la solicitud',
			icon: 'error',
			dangerMode: true,
			closeOnEsc: false,
			closeOnClickOutside: false
		});
	} else {
		console.error(mensaje || 'No se pudo completar la solicitud');
	}
}

function jsonResponse(data){
	if (typeof data === 'string') {
		return JSON.parse(data);
	}

	return data;
}

function debouncePaginationAdmision(){
	clearTimeout(timerPaginationAdmision);

	timerPaginationAdmision = setTimeout(function(){
		pagination(1);
	}, 450);
}

function debouncePaginationMuestras(){
	clearTimeout(timerPaginationMuestras);

	timerPaginationMuestras = setTimeout(function(){
		paginationMuestras(1);
	}, 450);
}

function debounceHistoricoMuestras(){
	clearTimeout(timerHistoricoMuestras);

	timerHistoricoMuestras = setTimeout(function(){
		var pacientes_id = $('#form_main_historico_muestras #pacientes_id_muestras').val();

		if (getPacienteTipo(pacientes_id) == 1) {
			historiaMuestrasPacientes(1);
		} else {
			historiaMuestrasEmpresas(1);
		}
	}, 450);
}

/****************************************************************************************************************************************************************/
// CARGAS INICIALES / CATÁLOGOS
/****************************************************************************************************************************************************************/

function getEstadoMuestra(){
	var url = '<?php echo SERVERURL; ?>php/admision/getStatusMuestra.php';

	return $.ajax({
		type: 'POST',
		url: url,
		cache: false
	}).done(function(data){
		var $s = $('#form_main_admision_muestras #estado');

		$s.html(data);
		safeRefresh($s);
	});
}

function getEstadoPaciente(){
	var url = '<?php echo SERVERURL; ?>php/admision/getStatusPaciente.php';

	return $.ajax({
		type: 'POST',
		url: url,
		cache: false
	}).done(function(data){
		var $s = $('#form_main_admision #estado');

		$s.html(data);
		safeRefresh($s);
	});
}

function getTipoMuestra(){
	var url = '<?php echo SERVERURL; ?>php/admision/getTipoMuestra.php';

	return $.ajax({
		type: 'POST',
		url: url,
		cache: false
	}).done(function(data){
		var $a = $('#formulario_admision #tipo_muestra');
		var $b = $('#form_main_admision_muestras #tipo_muestra');

		$a.html(data);
		$b.html(data);

		safeRefresh($a);
		safeRefresh($b);
	});
}

function getGenero(){
	var url = '<?php echo SERVERURL; ?>php/admision/getSexo.php';

	var $a = $('#formulario_admision #genero');
	var $b = $('#formulario_admision_clientes_editar #genero');

	if (generoAdmisionCargado === true && $a.children('option').length > 0) {
		safeRefresh($a);
		safeRefresh($b);
		return $.Deferred().resolve().promise();
	}

	return $.ajax({
		type: 'POST',
		url: url,
		cache: false
	}).done(function(data){
		$a.html(data);
		$b.html(data);

		generoAdmisionCargado = true;

		safeRefresh($a);
		safeRefresh($b);
	});
}

function getClientesAdmision(){
	var url = '<?php echo SERVERURL; ?>php/admision/getClientes.php';
	var $s = $('#formulario_admision #cliente_admision');

	if (clientesAdmisionCargados === true && $s.children('option').length > 0) {
		safeRefresh($s);
		return $.Deferred().resolve().promise();
	}

	$s.prop('disabled', true);
	$s.html('<option value="">Cargando clientes...</option>');
	safeRefresh($s);

	return $.ajax({
		type: 'POST',
		url: url,
		cache: false
	}).done(function(data){
		$s.html(data);
		clientesAdmisionCargados = true;
	}).fail(function(){
		$s.html('<option value="">Error al cargar clientes</option>');
	}).always(function(){
		$s.prop('disabled', false);
		safeRefresh($s);
	});
}

function getEmpresa(){
	var url = '<?php echo SERVERURL; ?>php/admision/getEmpresa.php';

	return $.ajax({
		type: 'POST',
		url: url,
		cache: false
	}).done(function(data){
		var $s = $('#formulario_admision #empresa');

		$s.html(data);
		safeRefresh($s);
	});
}

function getTipo(){
	var url = '<?php echo SERVERURL; ?>php/admision/getTipoPaciente.php';

	return $.ajax({
		type: 'POST',
		url: url,
		cache: false
	}).done(function(data){
		var $s = $('#formulario_admision #paciente_tipo');

		$s.html(data);
		safeRefresh($s);
	});
}

function getRemitente(){
	var url = '<?php echo SERVERURL; ?>php/admision/getRemitente.php';

	return $.ajax({
		type: 'POST',
		url: url,
		cache: false
	}).done(function(data){
		var $s = $('#formulario_admision #remitente');

		$s.html(data);
		safeRefresh($s);
	});
}

function getHospitales(){
	var url = '<?php echo SERVERURL; ?>php/admision/getHospitales.php';

	return $.ajax({
		type: 'POST',
		url: url,
		cache: false
	}).done(function(data){
		var $a = $('#formulario_admision #hospital');
		var $b = $('#formulario_admision_empresas #hospital_empresa');

		$a.html(data);
		$b.html(data);

		safeRefresh($a);
		safeRefresh($b);
	});
}

function getCategorias(){
	var url = '<?php echo SERVERURL; ?>php/admision/getCategoriaMuestra.php';

	return $.ajax({
		type: 'POST',
		url: url,
		cache: false
	}).done(function(data){
		var $s = $('#formulario_admision #categoria');

		$s.html(data);
		safeRefresh($s);
	});
}

function getTipoPacienteSelect(){
	var url = '<?php echo SERVERURL; ?>php/admision/getTipoPaciente.php';

	return $.ajax({
		type: 'POST',
		url: url,
		cache: false
	}).done(function(data){
		var $a = $('#form_main_admision #tipo');
		var $b = $('#form_main_admision_muestras #tipo');

		$a.html(data);
		$b.html(data);

		safeRefresh($a);
		safeRefresh($b);
	});
}

function cargarCatalogosModalCliente(callback){
	if (catalogosModalClienteCargados === true) {
		if (typeof callback === 'function') {
			callback();
		}

		return false;
	}

	safeShowLoading("Cargando catálogos...");

	var cargas = [
		getGenero(),
		getTipoMuestra(),
		getEmpresa(),
		getRemitente(),
		getHospitales(),
		getCategorias(),
		getClientesAdmision(),
		getTipo()
	];

	if (typeof getServicio === 'function') {
		cargas.push(getServicio());
	}

	$.when.apply($, cargas).always(function(){
		catalogosModalClienteCargados = true;
		safeHideLoading();

		if (typeof callback === 'function') {
			callback();
		}
	});

	return true;
}

function cargarCatalogosMuestras(callback){
	if (catalogosMuestrasCargados === true) {
		if (typeof callback === 'function') {
			callback();
		}

		return false;
	}

	safeShowLoading("Cargando filtros...");

	var cargas = [
		getEstadoMuestra(),
		getTipoMuestra()
	];

	$.when.apply($, cargas).always(function(){
		catalogosMuestrasCargados = true;
		safeHideLoading();

		if (typeof callback === 'function') {
			callback();
		}
	});

	return true;
}

function initPage(){
	var essentials = [
		getEstadoPaciente(),
		getTipoPacienteSelect()
	];

	$.when.apply($, essentials).always(function(){
		pagination(1, true);
	});
}

/****************************************************************************************************************************************************************/
// EVENTOS PRINCIPALES
/****************************************************************************************************************************************************************/

$(document).ready(function(){

	$("#modal_historico_muestras").off('shown.bs.modal').on('shown.bs.modal', function(){
		$(this).find('#form_main_historico_muestras #bs_regis').focus();
	});

	$("#modal_admision_clientes").off('shown.bs.modal').on('shown.bs.modal', function(){
		$(this).find('#formulario_admision #name').focus();
	});

	$("#modal_admision_clientes_editar").off('shown.bs.modal').on('shown.bs.modal', function(){
		$(this).find('#formulario_admision_clientes_editar #name').focus();
	});

	$("#modal_admision_empesas").off('shown.bs.modal').on('shown.bs.modal', function(){
		$(this).find('#formulario_admision_empresas #empresa').focus();
	});

	$("#modal_admision_clientes").off('hidden.bs.modal.optimizado').on('hidden.bs.modal.optimizado', function(){
		modalClienteAbriendose = false;

		if (requestDatosClienteAdmision !== null) {
			requestDatosClienteAdmision.abort();
			requestDatosClienteAdmision = null;
		}

		safeHideLoading();
	});

	$('#form_main_admision #bs_regis').off('keyup').on('keyup', function(){
		debouncePaginationAdmision();
	});

	$('#form_main_admision #estado').off('change').on('change', function(){
		pagination(1);
	});

	$('#form_main_admision #tipo').off('change').on('change', function(){
		pagination(1);
	});

	$('#form_main_admision_muestras #estado').off('change').on('change', function(){
		paginationMuestras(1);
	});

	$('#form_main_admision_muestras #cliente').off('change').on('change', function(){
		paginationMuestras(1);
	});

	$('#form_main_admision_muestras #tipo_muestra').off('change').on('change', function(){
		paginationMuestras(1);
	});

	$('#form_main_admision_muestras #bs_regis').off('keyup').on('keyup', function(){
		debouncePaginationMuestras();
	});

	$('#form_main_admision_muestras #fecha_i, #form_main_admision_muestras #fecha_f').off('change').on('change', function(){
		paginationMuestras(1);
	});

	$('#form_main_admision_muestras #buscar_registro').off('click').on('click', function(e){
		e.preventDefault();
		paginationMuestras(1);
	});

	$('#formulario_admision #fecha_nac').off('change').on('change', function(){
		CalcularEdadClientes();
	});

	$('#form_main_historico_muestras #bs_regis').off('keyup').on('keyup', function(){
		debounceHistoricoMuestras();
	});

	$('#form_main_admision #registrar_cliente').off('click').on('click', function(e){
		e.preventDefault();
		modalClientes();
	});

	$('#form_main_admision #registrar_empresa').off('click').on('click', function(e){
		e.preventDefault();
		modaEmpresa();
	});

	$('#formulario_admision #add_empresa').off('click').on('click', function(e){
		e.preventDefault();
		modaEmpresa();
	});

	$('#form_main_admision #ver_muestras').off('click').on('click', function(e){
		e.preventDefault();

		ModalVerMas();

		cargarCatalogosMuestras(function(){
			paginationMuestras(1);
		});
	});

	$('#acciones_atras').off('click').on('click', function(e){
		e.preventDefault();
		volver();
	});

	$('#registrar_productos').off('click').on('click', function(e){
		e.preventDefault();

		if (typeof agregarProductos === 'function') {
			agregarProductos();
		}
	});

	$('#formulario_facturacion #validar').off('click').on('click', function(){
		$('#formulario_facturacion').attr({ 'data-form': 'save' });
		$('#formulario_facturacion').attr({ 'action': '<?php echo SERVERURL; ?>php/facturacion/addPreFactura.php' });
		$("#formulario_facturacion").submit();
	});

	$('#formulario_facturacion #cobrar').off('click').on('click', function(){
		$('#formulario_facturacion').attr({ 'data-form': 'save' });
		$('#formulario_facturacion').attr({ 'action': '<?php echo SERVERURL; ?>php/facturacion/addFactura.php' });
		$("#formulario_facturacion").submit();
	});

	$('#formulario_admision #nuevo_admision').off('click').on('click', function(e){
		e.preventDefault();
		limpiarFormularioClienteSinRecargarCatalogos();
	});

	$('#formulario_admision_empresas #nuevo_admision_empresa').off('click').on('click', function(e){
		e.preventDefault();

		$('#formulario_admision_empresas #empresa').val("");
		$('#formulario_admision_empresas #rtn').val(0);
		$('#formulario_admision_empresas #telefono1').val("");
		$('#formulario_admision_empresas #direccion').val("");
		$('#formulario_admision_empresas #correo').val("");
		$('#formulario_admision_empresas #empresa').focus();
	});

	$('#formulario_admision #nuevo_admision_muestra').off('click').on('click', function(e){
		e.preventDefault();

		$('#formulario_admision #sitio_muestra').val("");
		$('#formulario_admision #diagnostico_clinico').val("");
		$('#formulario_admision #material_enviado').val("");
		$('#formulario_admision #datos_clinicos').val("");

		$('#formulario_admision #producto').html("");
		safeRefresh($('#formulario_admision #producto'));

		cargarCatalogosModalCliente();
	});

	$('#form_main_admision_muestras #tipo').off('change').on('change', function(){
		cargarClientesPorTipoMuestras();
	});

	$('#formulario_admision #cliente_admision').off('change').on('change', function(){
		cargarDatosClienteAdmision();
	});

	$('#formulario_admision #tipo_muestra').off('change').on('change', function(){
		cargarProductosPorTipoMuestra();
	});

	$("#tab1").off("click").on("click", function(){
		$("#modal_pagos").one('shown.bs.modal', function(){
			$(this).find('#formTarjetaBill #efectivo_bill').focus();
		});
	});

	$("#tab2").off("click").on("click", function(){
		$("#modal_pagos").one('shown.bs.modal', function(){
			$(this).find('#formTarjetaBill #cr_bill').focus();
		});
	});

	$("#tab3").off("click").on("click", function(){
		$("#modal_pagos").one('shown.bs.modal', function(){
			$(this).find('#formTarjetaBill #bk_nm').focus();
		});
	});

	$("#tab4").off("click").on("click", function(){
		$("#modal_pagos").one('shown.bs.modal', function(){
			$(this).find('#formChequeBill #bk_nm_chk').focus();
		});
	});

	$("#tab5").off("click").on("click", function(){
		$("#modal_pagos").one('shown.bs.modal', function(){
			$(this).find('#formMixtoBill #efectivo_bill_mixto').focus();
		});
	});

	if ($.fn.inputmask) {
		$('#formMixtoPurchaseBill #cr_bill_mixtoPurchase').inputmask("9999");
		$('#formMixtoPurchaseBill #exp_mixtoPurchase').inputmask("99/99");
		$('#formMixtoPurchaseBill #cvcpwd_mixtoPurchase').inputmask("999999");

		$('#formTarjetaBill #cr_bill').inputmask("9999");
		$('#formTarjetaBill #exp').inputmask("99/99");
		$('#formTarjetaBill #cvcpwd').inputmask("999999");
	}

	$("#formEfectivoBill #efectivo_bill").off("keyup").on("keyup", function(){
		var efectivo = parseFloat($("#formEfectivoBill #efectivo_bill").val() || 0);
		var monto = parseFloat($("#formEfectivoBill #monto_efectivo").val() || 0);
		var total = efectivo - monto;

		if (Math.floor(efectivo * 100) >= Math.floor(monto * 100)) {
			$('#formEfectivoBill #cambio_efectivo').val(parseFloat(total).toFixed(2));
			$('#formEfectivoBill #pago_efectivo').attr('disabled', false);
		} else {
			$('#formEfectivoBill #cambio_efectivo').val(parseFloat(0).toFixed(2));
			$('#formEfectivoBill #pago_efectivo').attr('disabled', true);
		}
	});

	$("#formMixtoBill #efectivo_bill_mixto").off("keyup").on("keyup", function(){
		var efectivo = parseFloat($("#formMixtoBill #efectivo_bill_mixto").val() || 0);
		var monto = parseFloat($("#formMixtoBill #monto_efectivo_mixto").val() || 0);

		if (Math.floor(efectivo * 100) >= Math.floor(monto * 100)) {
			$('#formMixtoBill #pago_efectivo_mixto').attr('disabled', true);
			$('#formMixtoBill #monto_tarjeta').val(parseFloat(0).toFixed(2));
			$('#formMixtoBill #monto_tarjeta').attr('disabled', true);
		} else {
			var tarjeta = monto - efectivo;

			$('#formMixtoBill #monto_tarjeta').val(parseFloat(tarjeta).toFixed(2));
			$('#formMixtoBill #cambio_efectivo_mixto').val(parseFloat(0).toFixed(2));
			$('#formMixtoBill #pago_efectivo_mixto').attr('disabled', false);
		}
	});

});

/****************************************************************************************************************************************************************/
// UTILIDADES
/****************************************************************************************************************************************************************/

function CalcularEdadClientes(){
	var url = '<?php echo SERVERURL; ?>php/admision/calcularEdad.php';

	$.ajax({
		type: 'POST',
		url: url,
		cache: false,
		data: {
			fecha_nac: $('#formulario_admision #fecha_nac').val()
		},
		success: function(data){
			$('#formulario_admision #edad').val(data);
		}
	});

	return false;
}

function getFechaActual(){
	var url = '<?php echo SERVERURL; ?>php/admision/getFechaActual.php';
	var fecha_actual = '';

	$.ajax({
		type: 'POST',
		url: url,
		cache: false,
		async: false,
		success: function(data){
			fecha_actual = data;
		}
	});

	return fecha_actual;
}

function getPacienteTipo(pacientes_id){
	if (!pacientes_id) {
		return '';
	}

	if (cachePacienteTipo[pacientes_id] !== undefined) {
		return cachePacienteTipo[pacientes_id];
	}

	var url = '<?php echo SERVERURL; ?>php/admision/getPacienteTipo.php';
	var tipo_paciente = '';

	$.ajax({
		type: 'POST',
		url: url,
		cache: false,
		data: {
			pacientes_id: pacientes_id
		},
		async: false,
		success: function(data){
			tipo_paciente = data;
			cachePacienteTipo[pacientes_id] = data;
		}
	});

	return tipo_paciente;
}

function consultarExpediente(pacientes_id){
	if (!pacientes_id) {
		return '';
	}

	if (cacheExpedientePaciente[pacientes_id] !== undefined) {
		return cacheExpedientePaciente[pacientes_id];
	}

	var url = '<?php echo SERVERURL; ?>php/pacientes/getExpedienteInformacion.php';
	var resp = '';

	$.ajax({
		type: 'POST',
		url: url,
		cache: false,
		data: {
			pacientes_id: pacientes_id
		},
		async: false,
		success: function(data){
			resp = data;
			cacheExpedientePaciente[pacientes_id] = data;
		}
	});

	return resp;
}

function consultarNumeroMuestra(muestras_id){
	if (!muestras_id) {
		return '';
	}

	if (cacheNumeroMuestra[muestras_id] !== undefined) {
		return cacheNumeroMuestra[muestras_id];
	}

	var url = '<?php echo SERVERURL; ?>php/admision/getNumeroMuestra.php';
	var resp = '';

	$.ajax({
		type: 'POST',
		url: url,
		cache: false,
		data: {
			muestras_id: muestras_id
		},
		async: false,
		success: function(data){
			resp = data;
			cacheNumeroMuestra[muestras_id] = data;
		}
	});

	return resp;
}

function consultarNombre(pacientes_id){
	if (!pacientes_id) {
		return '';
	}

	if (cacheNombrePaciente[pacientes_id] !== undefined) {
		return cacheNombrePaciente[pacientes_id];
	}

	var url = '<?php echo SERVERURL; ?>php/pacientes/getNombre.php';
	var resp = '';

	$.ajax({
		type: 'POST',
		url: url,
		cache: false,
		data: {
			pacientes_id: pacientes_id
		},
		async: false,
		success: function(data){
			resp = data;
			cacheNombrePaciente[pacientes_id] = data;
		}
	});

	return resp;
}

function getHospitalCodigo(){
	var url = '<?php echo SERVERURL; ?>php/pacientes/getHospitalCodigo.php';
	var resp = '';

	$.ajax({
		type: 'POST',
		url: url,
		cache: false,
		async: false,
		success: function(data){
			resp = data;
		}
	});

	return resp;
}

function getRemitenteCodigo(){
	var url = '<?php echo SERVERURL; ?>php/pacientes/getRemitenteCodigo.php';
	var resp = '';

	$.ajax({
		type: 'POST',
		url: url,
		cache: false,
		async: false,
		success: function(data){
			resp = data;
		}
	});

	return resp;
}

function getFacturaEmision(muestras_id){
	var url = '<?php echo SERVERURL; ?>php/muestras/getFacturaEmision.php';
	var disponible = '';

	$.ajax({
		type: 'POST',
		url: url,
		cache: false,
		data: {
			muestras_id: muestras_id
		},
		async: false,
		success: function(data){
			disponible = data;
		}
	});

	return disponible;
}

function getEstadoFactura(muestras_id){
	var url = '<?php echo SERVERURL; ?>php/muestras/getEstadoFactura.php';
	var disponible = '';

	$.ajax({
		type: 'POST',
		url: url,
		cache: false,
		data: {
			muestras_id: muestras_id
		},
		async: false,
		success: function(data){
			disponible = data;
		}
	});

	return disponible;
}

function convertDate(inputFormat){
	function pad(s){
		return (s < 10) ? '0' + s : s;
	}

	var d = new Date(inputFormat);
	return [d.getFullYear(), pad(d.getMonth() + 1), pad(d.getDate())].join('-');
}

/****************************************************************************************************************************************************************/
// SELECTS DEPENDIENTES
/****************************************************************************************************************************************************************/

function cargarClientesPorTipoMuestras(){
	var url = '<?php echo SERVERURL; ?>php/admision/getEmpresaCliente.php';
	var tipo = $('#form_main_admision_muestras #tipo').val() || '';

	if (requestClientesPorTipoMuestras !== null) {
		requestClientesPorTipoMuestras.abort();
		requestClientesPorTipoMuestras = null;
	}

	var $c = $('#form_main_admision_muestras #cliente');

	$c.html('<option value="">Cargando...</option>');
	safeRefresh($c);

	requestClientesPorTipoMuestras = $.ajax({
		type: 'POST',
		url: url,
		cache: false,
		data: {
			tipo: tipo
		},
		success: function(data){
			$c.html(data);
			safeRefresh($c);
			paginationMuestras(1);
		},
		error: function(xhr, status){
			if (status !== 'abort') {
				$c.html('<option value="">Error al cargar</option>');
				safeRefresh($c);
			}
		},
		complete: function(){
			requestClientesPorTipoMuestras = null;
		}
	});
}

function cargarDatosClienteAdmision(){
	var url = '<?php echo SERVERURL; ?>php/admision/consultarClientes.php';
	var pacientes_id = $('#formulario_admision #cliente_admision').val();

	if (!pacientes_id) {
		return false;
	}

	if (cacheDatosClienteAdmision[pacientes_id] !== undefined) {
		llenarDatosClienteAdmision(cacheDatosClienteAdmision[pacientes_id]);
		return false;
	}

	if (requestDatosClienteAdmision !== null) {
		requestDatosClienteAdmision.abort();
		requestDatosClienteAdmision = null;
	}

	var $form = $('#formulario_admision');

	$form.find('#name, #lastname, #rtn, #edad, #telefono1, #direccion, #correo').prop('readonly', true);
	$form.find('#genero').prop('disabled', true);
	safeRefresh($form.find('#genero'));

	requestDatosClienteAdmision = $.ajax({
		type: 'POST',
		url: url,
		cache: false,
		data: {
			pacientes_id: pacientes_id
		},
		success: function(data){
			var valores = jsonResponse(data);

			cacheDatosClienteAdmision[pacientes_id] = valores;
			llenarDatosClienteAdmision(valores);
		},
		error: function(xhr, status){
			if (status !== 'abort') {
				showNotify("error", "Error", "No se pudo cargar la información del cliente");
			}
		},
		complete: function(){
			requestDatosClienteAdmision = null;

			$form.find('#name, #lastname, #rtn, #edad, #telefono1, #direccion, #correo').prop('readonly', false);
			$form.find('#genero').prop('disabled', false);
			safeRefresh($form.find('#genero'));
		}
	});

	return false;
}

function llenarDatosClienteAdmision(valores){
	$('#formulario_admision #name').val(valores[0] || '');
	$('#formulario_admision #lastname').val(valores[1] || '');
	$('#formulario_admision #rtn').val(valores[2] || '');
	$('#formulario_admision #edad').val(valores[3] || '');
	$('#formulario_admision #telefono1').val(valores[4] || '');
	$('#formulario_admision #genero').val(valores[5] || '');
	safeRefresh($('#formulario_admision #genero'));
	$('#formulario_admision #direccion').val(valores[6] || '');
	$('#formulario_admision #correo').val(valores[7] || '');
}

function limpiarFormularioClienteSinRecargarCatalogos(){
	$('#formulario_admision #name').val("");
	$('#formulario_admision #lastname').val("");
	$('#formulario_admision #rtn').val(0);
	$('#formulario_admision #edad').val("");
	$('#formulario_admision #telefono1').val("");
	$('#formulario_admision #telefono2').val("");
	$('#formulario_admision #direccion').val("");
	$('#formulario_admision #correo').val("");
	$('#formulario_admision #fecha_nac').val("");
	$('#formulario_admision #cliente_admision').val("");
	$('#formulario_admision #genero').val("");

	safeRefresh($('#formulario_admision #cliente_admision'));
	safeRefresh($('#formulario_admision #genero'));

	$('#formulario_admision #name').focus();
}

function cargarProductosPorTipoMuestra(){
	var url = '<?php echo SERVERURL; ?>php/admision/getProductos.php';
	var tipo_muestra_id = $('#formulario_admision #tipo_muestra').val();

	var $p = $('#formulario_admision #producto');

	$p.html('<option value="">Cargando...</option>');
	safeRefresh($p);

	$.ajax({
		type: 'POST',
		url: url,
		cache: false,
		data: {
			tipo_muestra_id: tipo_muestra_id
		},
		success: function(data){
			$p.html(data);
			safeRefresh($p);
		},
		error: function(){
			$p.html('<option value="">Error al cargar productos</option>');
			safeRefresh($p);
		}
	});
}

/****************************************************************************************************************************************************************/
// PAGINACIÓN CLIENTES
/****************************************************************************************************************************************************************/

function pagination(partida, firstLoad){
	var url = '<?php echo SERVERURL; ?>php/admision/paginar.php';

	var tipo = $('#form_main_admision #tipo').val() || 1;
	var dato = $('#form_main_admision #bs_regis').val() || '';
	var estado = $('#form_main_admision #estado').val() || 1;

	if (requestPaginationAdmision !== null) {
		requestPaginationAdmision.abort();
		requestPaginationAdmision = null;
	}

	requestPaginationAdmision = $.ajax({
		type: 'POST',
		url: url,
		cache: false,
		dataType: 'json',
		data: {
			partida: partida,
			tipo: tipo,
			dato: dato,
			estado: estado
		},
		success: function(array){
			$('#agrega-registros').html(array[0]);
			$('#pagination').html(array[1]);
		},
		error: function(xhr, status){
			if (status === 'abort') {
				return false;
			}

			if (firstLoad) {
				setTimeout(function(){
					pagination(partida, false);
				}, 200);

				return false;
			}

			console.error('Error en pagination:', xhr.responseText);
		},
		complete: function(){
			requestPaginationAdmision = null;
		}
	});

	return false;
}

/****************************************************************************************************************************************************************/
// PAGINACIÓN MUESTRAS
/****************************************************************************************************************************************************************/

function paginationMuestras(partida){
	var url = '<?php echo SERVERURL; ?>php/admision/paginarMuestras.php';

	var estado = $('#form_main_admision_muestras #estado').val();
	var cliente = $('#form_main_admision_muestras #cliente').val() || '';
	var tipo_muestra = $('#form_main_admision_muestras #tipo_muestra').val() || '';
	var fecha_i = $('#form_main_admision_muestras #fecha_i').val() || '';
	var fecha_f = $('#form_main_admision_muestras #fecha_f').val() || '';
	var dato = $('#form_main_admision_muestras #bs_regis').val() || '';

	if (estado === null || estado === undefined) {
		estado = '';
	}

	if (requestPaginationMuestras !== null) {
		requestPaginationMuestras.abort();
		requestPaginationMuestras = null;
	}

	requestPaginationMuestras = $.ajax({
		type: 'POST',
		url: url,
		cache: false,
		dataType: 'json',
		data: {
			partida: partida,
			estado: estado,
			cliente: cliente,
			tipo_muestra: tipo_muestra,
			fecha_i: fecha_i,
			fecha_f: fecha_f,
			dato: dato
		},
		beforeSend: function(){
			safeShowLoading("Por favor espere...");
		},
		success: function(array){
			$('#agrega-registros_muestras').html(array[0]);
			$('#pagination_muestras').html(array[1]);
		},
		error: function(xhr, status){
			if (status === 'abort') {
				return false;
			}

			console.error('Error en paginationMuestras:', xhr.responseText);
			showErrorAjax('No se enviaron los datos, favor corregir');
		},
		complete: function(){
			safeHideLoading();
			requestPaginationMuestras = null;
		}
	});

	return false;
}

/****************************************************************************************************************************************************************/
// ANULAR / ELIMINAR MUESTRAS Y PACIENTES
/****************************************************************************************************************************************************************/

function anularRegistroMuestra(muestras_id, pacientes_id, comentario){
	var url = '<?php echo SERVERURL; ?>php/admision/anularMuestras.php';

	$.ajax({
		type: 'POST',
		url: url,
		cache: false,
		data: {
			muestras_id: muestras_id,
			pacientes_id: pacientes_id,
			comentario: comentario
		},
		success: function(registro){
			if (registro == 1) {
				showNotify("success", "Success", "Registro anulado correctamente");
				paginationMuestras(1);
			} else if (registro == 2) {
				showNotify("error", "Error", "Lo sentimos ya existe una factura para esta muestra, por favor anule la factura antes de proceder.");
			} else if (registro == 3) {
				showNotify("error", "Error", "No se puede anular este registro");
			} else {
				showNotify("error", "Error", "Error al completar el registro");
			}
		}
	});

	return false;
}

function eliminarRegistroMuestra(muestras_id, pacientes_id, comentario){
	var url = '<?php echo SERVERURL; ?>php/admision/eliminarMuestras.php';

	$.ajax({
		type: 'POST',
		url: url,
		cache: false,
		data: {
			muestras_id: muestras_id,
			pacientes_id: pacientes_id,
			comentario: comentario
		},
		success: function(registro){
			if (registro == 1) {
				showNotify("success", "Success", "Registro eliminado correctamente");
				paginationMuestras(1);
			} else if (registro == 2) {
				showNotify("error", "Error", "No se puede eliminar este registro");
			} else if (registro == 3) {
				showNotify("error", "Error", "No se puede eliminar este registro, cuenta con información almacenada");
			} else {
				showNotify("error", "Error", "Error al completar el registro");
			}
		}
	});

	return false;
}

function eliminarRegistro(pacientes_id, comentario){
	var url = '<?php echo SERVERURL; ?>php/admision/eliminar.php';

	$.ajax({
		type: 'POST',
		url: url,
		cache: false,
		data: {
			id: pacientes_id,
			comentario: comentario
		},
		success: function(registro){
			if (registro == 1) {
				showNotify("success", "Success", "Registro eliminado correctamente");
				pagination(1);
			} else if (registro == 2) {
				showNotify("error", "Error", "No se puede eliminar este registro");
			} else if (registro == 3) {
				showNotify("error", "Error", "No se puede eliminar este registro, cuenta con información almacenada");
			} else {
				showNotify("error", "Error", "Error al completar el registro");
			}
		}
	});

	return false;
}

/****************************************************************************************************************************************************************/
// HABILITAR / INHABILITAR
/****************************************************************************************************************************************************************/

function DisableRegister(pacientes_id){
	var nombre_usuario = consultarNombre(pacientes_id);
	var expediente_usuario = consultarExpediente(pacientes_id);
	var estado = $('#form_main_admision #estado').val();
	var estado_label = '';
	var dato = '';

	if (estado == "1") {
		estado_label = "Inhabilitar";
	} else {
		estado_label = "Habilitar";
	}

	if (expediente_usuario == 0) {
		dato = nombre_usuario;
	} else {
		dato = nombre_usuario + " (Expediente: " + expediente_usuario + ")";
	}

	swal({
		title: "¿Estas seguro?",
		text: "¿Desea " + estado_label + " este cliente: " + dato + "?",
		content: {
			element: "input",
			attributes: {
				placeholder: "Comentario",
				type: "text"
			}
		},
		icon: "warning",
		buttons: {
			cancel: "Cancelar",
			confirm: {
				text: "¡Sí, " + estado_label + " el cliente!",
				closeModal: false
			}
		},
		dangerMode: true,
		closeOnEsc: false,
		closeOnClickOutside: false
	}).then(function(value){
		if (value === null || value.trim() === "") {
			return false;
		}

		deshabilitarPaciente(pacientes_id, value, estado);
	});
}

function deshabilitarPaciente(pacientes_id, comentario, estado){
	var url = '<?php echo SERVERURL; ?>php/admision/DeshabilitarPaciente.php';

	estado = (estado === null || estado === '') ? 1 : estado;

	$.ajax({
		type: 'POST',
		url: url,
		cache: false,
		dataType: "json",
		data: {
			pacientes_id: pacientes_id,
			comentario: comentario,
			estado: estado
		},
		success: function(response){
			if (response.status === "success") {
				showNotify("success", "Success", response.message);
				pagination(1);
			} else {
				showNotify("error", "Error", response.message);
			}
		},
		error: function(){
			showNotify("error", "Error", "Error en la comunicación con el servidor");
		}
	});
}

/****************************************************************************************************************************************************************/
// MODALES ELIMINAR / ANULAR
/****************************************************************************************************************************************************************/

function modal_eliminar(pacientes_id){
	if (!(getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3)) {
		showNotify("error", "Acceso Denegado", "No tiene permisos para ejecutar esta acción");
		return false;
	}

	var nombre_usuario = consultarNombre(pacientes_id);
	var expediente_usuario = consultarExpediente(pacientes_id);
	var dato = (expediente_usuario == 0) ? nombre_usuario : (nombre_usuario + " (Expediente: " + expediente_usuario + ")");

	swal({
		title: "¿Estas seguro?",
		text: "¿Desea eliminar este cliente: " + dato + "?",
		content: {
			element: "input",
			attributes: {
				placeholder: "Comentario",
				type: "text"
			}
		},
		icon: "warning",
		buttons: {
			cancel: "Cancelar",
			confirm: {
				text: "¡Sí, eliminar el cliente!",
				closeModal: false
			}
		},
		dangerMode: true,
		closeOnEsc: false,
		closeOnClickOutside: false
	}).then(function(value){
		if (value === null || value.trim() === "") {
			showNotify("error", "Error", "¡Necesita escribir algo!");
			return false;
		}

		eliminarRegistro(pacientes_id, value);
	});
}

function modal_eliminarMuestras(pacientes_id, muestras_id){
	if (!(getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3)) {
		showNotify("error", "Acceso Denegado", "No tiene permisos para ejecutar esta acción");
		return false;
	}

	var nombre_usuario = consultarNombre(pacientes_id);
	var numero_muestra = consultarNumeroMuestra(muestras_id);
	var dato = nombre_usuario + " (Muestra: " + numero_muestra + ")";

	swal({
		title: "¿Estas seguro?",
		text: "¿Desea eliminar esta muestra para el cliente: " + dato + "?",
		content: {
			element: "input",
			attributes: {
				placeholder: "Comentario",
				type: "text"
			}
		},
		icon: "warning",
		buttons: {
			cancel: "Cancelar",
			confirm: {
				text: "¡Sí, eliminar la muestra!",
				closeModal: false
			}
		},
		dangerMode: true,
		closeOnEsc: false,
		closeOnClickOutside: false
	}).then(function(value){
		if (value === null || value.trim() === "") {
			showNotify("error", "Error", "¡Necesita escribir algo!");
			return false;
		}

		eliminarRegistroMuestra(muestras_id, pacientes_id, value);
	});
}

function modalAnularMuestras(pacientes_id, muestras_id){
	if (!(getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3)) {
		showNotify("error", "Acceso Denegado", "No tiene permisos para ejecutar esta acción");
		return false;
	}

	var nombre_usuario = consultarNombre(pacientes_id);
	var numero_muestra = consultarNumeroMuestra(muestras_id);
	var dato = nombre_usuario + " (Muestra: " + numero_muestra + ")";

	swal({
		title: "¿Estas seguro?",
		text: "¿Desea anular esta muestra para el cliente: " + dato + "?",
		content: {
			element: "input",
			attributes: {
				placeholder: "Comentario",
				type: "text"
			}
		},
		icon: "warning",
		buttons: {
			cancel: "Cancelar",
			confirm: {
				text: "¡Sí, anular la muestra!",
				closeModal: false
			}
		},
		dangerMode: true,
		closeOnEsc: false,
		closeOnClickOutside: false
	}).then(function(value){
		if (value === null || value.trim() === "") {
			showNotify("error", "Error", "¡Necesita escribir algo!");
			return false;
		}

		anularRegistroMuestra(muestras_id, pacientes_id, value);
	});
}

/****************************************************************************************************************************************************************/
// EDITAR REGISTROS
/****************************************************************************************************************************************************************/

function editarRegistro(pacientes_id){
	if (!(getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3)) {
		showNotify("error", "Acceso Denegado", "No tiene permisos para ejecutar esta acción");
		return false;
	}

	var url = '<?php echo SERVERURL; ?>php/admision/consultarClientes.php';

	$.ajax({
		type: 'POST',
		url: url,
		cache: false,
		data: {
			pacientes_id: pacientes_id
		},
		success: function(valores){
			var datos = jsonResponse(valores);

			if ($('#form_main_admision #tipo').val() == 1 || $('#form_main_admision #tipo').val() == "") {
				var nombre_completo = datos[0] + " " + datos[1];

				$('#formulario_admision_clientes_editar #edi_admision').show();
				$('#formulario_admision_clientes_editar #pacientes_id').val(pacientes_id);
				$('#formulario_admision_clientes_editar #name').val(nombre_completo.trim());
				$('#formulario_admision_clientes_editar #lastname').val(datos[1]);
				$('#formulario_admision_clientes_editar #rtn').val(datos[2]);
				$('#formulario_admision_clientes_editar #edad').val(datos[3]);
				$('#formulario_admision_clientes_editar #telefono1').val(datos[4]);
				$('#formulario_admision_clientes_editar #telefono2').val(datos[8]);
				$('#formulario_admision_clientes_editar #genero').val(datos[5]);
				safeRefresh($('#formulario_admision_clientes_editar #genero'));
				$('#formulario_admision_clientes_editar #direccion').val(datos[6]);
				$('#formulario_admision_clientes_editar #correo').val(datos[7]);

				$('#formulario_admision_clientes_editar').attr({ 'data-form': 'update' });
				$('#formulario_admision_clientes_editar').attr({ 'action': '<?php echo SERVERURL; ?>php/admision/modificarRegistro.php' });

				$('#formulario_admision_clientes_editar #name').attr('readonly', false);
				$('#formulario_admision_clientes_editar #lastname').attr('readonly', false);
				$('#formulario_admision_clientes_editar #rtn').attr('readonly', false);
				$('#formulario_admision_clientes_editar #fecha_nac').attr('disabled', false);
				$('#formulario_admision_clientes_editar #edad').attr('readonly', false);
				$('#formulario_admision_clientes_editar #telefono1').attr('readonly', false);
				$('#formulario_admision_clientes_editar #genero').attr('disabled', false);
				$('#formulario_admision_clientes_editar #direccion').attr('readonly', false);
				$('#formulario_admision_clientes_editar #correo').attr('readonly', false);

				$('#modal_admision_clientes_editar').modal({
					show: true,
					keyboard: false,
					backdrop: 'static'
				});
			} else {
				$('#reg_admisionemp').hide();
				$('#edi_admisionemp').show();

				$('#formulario_admision_empresas #pacientes_id').val(pacientes_id);
				$('#formulario_admision_empresas #empresa').val(datos[0]);
				$('#formulario_admision_empresas #rtn').val(datos[2]);
				$('#formulario_admision_empresas #edad').val(datos[3]);
				$('#formulario_admision_empresas #telefono1').val(datos[4]);
				$('#formulario_admision_empresas #direccion').val(datos[6]);
				$('#formulario_admision_empresas #correo').val(datos[7]);

				$('#formulario_admision_empresas').attr({ 'data-form': 'update' });
				$('#formulario_admision_empresas').attr({ 'action': '<?php echo SERVERURL; ?>php/admision/modificarRegistroEmpresas.php' });

				$('#formulario_admision_empresas #name').attr('readonly', false);
				$('#formulario_admision_empresas #rtn').attr('readonly', false);
				$('#formulario_admision_empresas #telefono1').attr('readonly', false);
				$('#formulario_admision_empresas #direccion').attr('readonly', false);
				$('#formulario_admision_empresas #correo').attr('readonly', false);

				$('#modal_admision_empesas').modal({
					show: true,
					keyboard: false,
					backdrop: 'static'
				});
			}

			return false;
		},
		error: function(){
			showNotify("error", "Error", "No se pudo cargar el registro");
		}
	});
}

function modalEditar(pacientes_id){
	$('#formulario_admision_clientes_editar').attr({ 'data-form': 'update' });
	$('#formulario_admision_clientes_editar').attr({ 'action': '<?php echo SERVERURL; ?>php/admision/agregarRegistro.php' });
	$('#formulario_admision_clientes_editar')[0].reset();
	$('#formulario_admision_clientes_editar #pro_admision').val("Registro");

	$('#reg_admision').show();
	$('#edi_admision').hide();
	$('#delete_admision').hide();

	$('#formulario_admision_clientes_editar #paciente_tipo').val(1);
	safeRefresh($('#formulario_admision_clientes_editar #paciente_tipo'));

	$('#formulario_admision #hospital').val(getHospitalCodigo());
	safeRefresh($('#formulario_admision #hospital'));
}

/****************************************************************************************************************************************************************/
// HISTORIAL DE MUESTRAS
/****************************************************************************************************************************************************************/

function showModalhistoriaMuestrasEmpresas(pacientes_id){
	if (!(getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3)) {
		showNotify("error", "Acceso Denegado", "No tiene permisos para ejecutar esta acción");
		return false;
	}

	$('#form_main_historico_muestras #pacientes_id_muestras').val(pacientes_id);

	$('#modal_historico_muestras').modal({
		show: true,
		keyboard: false,
		backdrop: 'static'
	});

	var tipo = getPacienteTipo(pacientes_id);

	if (tipo == 1) {
		historiaMuestrasPacientes(1);
	} else {
		historiaMuestrasEmpresas(1);
	}
}

function historiaMuestrasEmpresas(partida){
	var url = '<?php echo SERVERURL; ?>php/admision/paginar_historico_muestras_empresas.php';

	var pacientes_id = $('#modal_historico_muestras #pacientes_id_muestras').val();
	var dato = $('#form_main_historico_muestras #bs_regis').val();

	$.ajax({
		type: 'POST',
		url: url,
		cache: false,
		dataType: 'json',
		data: {
			partida: partida,
			pacientes_id: pacientes_id,
			dato: dato
		},
		success: function(array){
			$('#detalles-historico-muestras').html(array[0]);
			$('#pagination-historico-muestras').html(array[1]);
		},
		error: function(xhr){
			console.error('Error historiaMuestrasEmpresas:', xhr.responseText);
		}
	});

	return false;
}

function historiaMuestrasPacientes(partida){
	var url = '<?php echo SERVERURL; ?>php/admision/paginar_historico_muestras_pacientes.php';

	var pacientes_id = $('#form_main_historico_muestras #pacientes_id_muestras').val();
	var dato = $('#form_main_historico_muestras #bs_regis').val();

	$.ajax({
		type: 'POST',
		url: url,
		cache: false,
		dataType: 'json',
		data: {
			partida: partida,
			pacientes_id: pacientes_id,
			dato: dato
		},
		success: function(array){
			$('#detalles-historico-muestras').html(array[0]);
			$('#pagination-historico-muestras').html(array[1]);
		},
		error: function(xhr){
			console.error('Error historiaMuestrasPacientes:', xhr.responseText);
		}
	});

	return false;
}

/****************************************************************************************************************************************************************/
// MODALES CLIENTES / EMPRESAS
/****************************************************************************************************************************************************************/

function modalClientes(){
	if (modalClienteAbriendose === true) {
		return false;
	}

	modalClienteAbriendose = true;

	$('#formulario_admision').attr({ 'data-form': 'save' });
	$('#formulario_admision').attr({ 'action': '<?php echo SERVERURL; ?>php/admision/agregarRegistro.php' });
	$('#formulario_admision')[0].reset();
	$('#formulario_admision #pro_admision').val("Registro");

	$('#reg_admision').show();

	$('#formulario_admision #paciente_tipo').val(1);
	safeRefresh($('#formulario_admision #paciente_tipo'));

	$('#formulario_admision #name').attr('readonly', false);
	$('#formulario_admision #lastname').attr('readonly', false);
	$('#formulario_admision #rtn').attr('readonly', false);
	$('#formulario_admision #fecha_nac').attr('disabled', false);
	$('#formulario_admision #edad').attr('readonly', false);
	$('#formulario_admision #telefono1').attr('readonly', false);
	$('#formulario_admision #genero').attr('disabled', false);
	$('#formulario_admision #direccion').attr('readonly', false);
	$('#formulario_admision #correo').attr('readonly', false);
	$('#formulario_admision #hospital').attr('disabled', false);
	$('#formulario_admision #empresa').attr('disabled', false);
	$('#formulario_admision #referencia').attr('readonly', false);
	$('#formulario_admision #tipo_muestra').attr('disabled', false);
	$('#formulario_admision #remitente').attr('readonly', false);
	$('#formulario_admision #categoria').attr('disabled', false);
	$('#formulario_admision #sitio_muestra').attr('readonly', false);
	$('#formulario_admision #diagnostico_clinico').attr('readonly', false);
	$('#formulario_admision #material_enviado').attr('readonly', false);
	$('#formulario_admision #datos_clinicos').attr('readonly', false);
	$('#formulario_admision #mostrar_datos_clinicos').attr('disabled', false);

	cargarCatalogosModalCliente(function(){
		$('#formulario_admision #hospital').val(getHospitalCodigo());
		safeRefresh($('#formulario_admision #hospital'));

		$('#formulario_admision #remitente').val(getRemitenteCodigo());
		safeRefresh($('#formulario_admision #remitente'));

		$('#modal_admision_clientes').modal({
			show: true,
			keyboard: false,
			backdrop: 'static'
		});

		modalClienteAbriendose = false;
	});

	return false;
}

function modaEmpresa(){
	$('#formulario_admision_empresas').attr({ 'data-form': 'save' });
	$('#formulario_admision_empresas').attr({ 'action': '<?php echo SERVERURL; ?>php/admision/agregarRegistroEmpresas.php' });
	$('#formulario_admision_empresas')[0].reset();
	$('#formulario_admision_empresas #pro_admision').val("Registro");

	$('#reg_admisionemp').show();
	$('#edi_admisionemp').hide();
	$('#delete_admisionemp').hide();

	$('#formulario_admision_empresas #paciente_tipo').val(1);
	safeRefresh($('#formulario_admision_empresas #paciente_tipo'));

	$('#formulario_admision_empresas #name').attr('readonly', false);
	$('#formulario_admision_empresas #lastname').attr('readonly', false);
	$('#formulario_admision_empresas #rtn').attr('readonly', false);
	$('#formulario_admision_empresas #fecha_nac').attr('disabled', false);
	$('#formulario_admision_empresas #edad').attr('readonly', false);
	$('#formulario_admision_empresas #telefono1').attr('readonly', false);
	$('#formulario_admision_empresas #genero').attr('disabled', false);
	$('#formulario_admision_empresas #direccion').attr('readonly', false);
	$('#formulario_admision_empresas #correo').attr('readonly', false);

	getHospitales();

	$('#modal_admision_empesas').modal({
		show: true,
		keyboard: false,
		backdrop: 'static'
	});
}

/****************************************************************************************************************************************************************/
// FACTURACIÓN
/****************************************************************************************************************************************************************/

function formFactura(){
	$('#formulario_facturacion')[0].reset();
	$('#main_facturacion').hide();
	$('#facturacion').show();

	$('#label_acciones_volver').html("Volver");
	$('#acciones_atras').removeClass("active");
	$('#acciones_factura').addClass("active");
	$('#label_acciones_factura').html("Factura");

	$('#formulario_facturacion #fact_eval').val(0);
	$('#formulario_facturacion #fecha').attr('disabled', false);
	$('#formulario_facturacion').attr({ 'data-form': 'save' });
	$('#formulario_facturacion').attr({ 'action': '<?php echo SERVERURL; ?>php/facturacion/addPreFactura.php' });

	if (typeof limpiarTabla === 'function') {
		limpiarTabla();
	}

	$('.footer').show();
	$('.footer1').hide();
}

function ModalVerMas(){
	$('#main_facturacion').hide();
	$('#facturacion').hide();
	$('#main_admision_muestras').show();

	$('#label_acciones_volver').html("Volver");
	$('#acciones_atras').removeClass("active");
	$('#acciones_factura').addClass("active");
	$('#label_acciones_factura').html("Muestras");

	$('.footer').show();
	$('.footer1').hide();
}

function volver(){
	$('#main_facturacion').show();
	$('#label_acciones_factura').html("");

	$('#facturacion').hide();
	$('#main_admision_muestras').hide();

	$('#acciones_atras').addClass("breadcrumb-item active");
	$('#acciones_factura').removeClass("active");

	$('.footer').show();
	$('.footer1').hide();

	$('#agrega-registros_muestras').html("");
	$('#pagination_muestras').html("");

	if (requestPaginationMuestras !== null) {
		requestPaginationMuestras.abort();
		requestPaginationMuestras = null;
	}

	safeHideLoading();
}

function modalCreateBill(muestras_id, producto, nombre_producto, precio_venta, isv){
	var estadoMuestra = $('#form_main_admision_muestras #estado').val();

	if (estadoMuestra == 0) {
		if (getEstadoFactura(muestras_id) === "") {
			createBill(muestras_id, producto, nombre_producto, precio_venta, isv);
		} else {
			showNotify("error", "Error", "Lo sentimos esta factura ya ha sido emitida, por favor diríjase al módulo de facturación y realice el cobro de esta.");
		}
	} else if (estadoMuestra == 1) {
		if (getEstadoFactura(muestras_id) == "") {
			createBill(muestras_id, producto, nombre_producto, precio_venta, isv);
		} else {
			showNotify("error", "Error", "Lo sentimos esta factura ya ha sido emitida, por favor diríjase al reporte de facturación para buscarla, puede usar el número de muestra como referencia.");
		}
	} else {
		showNotify("error", "Error", "Lo sentimos no puede generar factura a una muestra anulada.");
	}
}

function createBill(muestras_id, producto, nombre_producto, precio_venta, isv, muestra){
	if (!(getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3)) {
		showNotify("error", "Acceso Denegado", "No tiene permisos para ejecutar esta acción");
		return false;
	}

	if (getFacturaEmision(muestras_id) != "") {
		showNotify("error", "Error", "Lo sentimos esta factura ya ha sido generada, por favor diríjase al módulo de facturación y realice el cobro de esta");
		return false;
	}

	$('#formulario_facturacion')[0].reset();
	$("#formulario_facturacion #invoiceItem > tbody").empty();

	var url = '<?php echo SERVERURL; ?>php/muestras/editarFacturasMuestras.php';

	$.ajax({
		type: 'POST',
		url: url,
		cache: false,
		data: {
			muestras_id: muestras_id,
			producto: producto
		},
		success: function(valores){
			var datos = jsonResponse(valores);

			$('#formulario_facturacion #fact_eval').val(0);
			$('#formulario_facturacion #muestras_id').val(muestras_id);
			$('#formulario_facturacion #pacientes_id').val(datos[0]);
			$('#formulario_facturacion #cliente_nombre').val(datos[1]);
			$('#formulario_facturacion #fecha').val(getFechaActual());
			$('#formulario_facturacion #colaborador_id').val(datos[3]);
			$('#formulario_facturacion #colaborador_nombre').val(datos[4]);
			$('#formulario_facturacion #servicio_id').val(datos[5]);
			safeRefresh($('#formulario_facturacion #servicio_id'));
			$('#formulario_facturacion #material_enviado_muestra').val(datos[6]);
			$('#formulario_facturacion #paciente_muestra_codigo').val(datos[7]);
			$('#formulario_facturacion #paciente_muestra').val(datos[8]);
			$('#formulario_facturacion #muestras_numero').val(datos[9]);

			$('#formulario_facturacion #fecha').attr("readonly", true);
			$('#formulario_facturacion #validar').attr("disabled", false);
			$('#formulario_facturacion #addRows').attr("disabled", false);
			$('#formulario_facturacion #removeRows').attr("disabled", false);

			$('#cobrar').hide();

			if (muestra === "Muestra") {
				$('.counter-container').hide();
			}

			$('#formulario_facturacion #validar').show();
			$('#formulario_facturacion #editar').hide();
			$('#formulario_facturacion #eliminar').hide();

			if (getPacienteTipo(datos[0]) == 2) {
				$('#formulario_facturacion #grupo_paciente_factura').show();
			} else {
				$('#formulario_facturacion #grupo_paciente_factura').hide();
			}

			$('#main_facturacion').hide();
			$('#main_admision_muestras').hide();
			$('#facturacion').show();

			$('#label_acciones_volver').html("Volver");
			$('#acciones_atras').removeClass("active");
			$('#acciones_factura').addClass("active");
			$('#label_acciones_factura').html("Factura");

			$('#formulario_facturacion #fecha').attr('disabled', false);

			if (typeof limpiarTabla === 'function') {
				limpiarTabla();
			}

			$('#formulario_facturacion #invoiceItem #productoID_0').val(producto);
			$('#formulario_facturacion #invoiceItem #productName_0').val(nombre_producto);
			$('#formulario_facturacion #invoiceItem #quantity_0').val(1);
			$('#formulario_facturacion #invoiceItem #discount_0').val(0);
			$('#formulario_facturacion #invoiceItem #price_0').val(precio_venta);
			$('#formulario_facturacion #invoiceItem #total_0').val(precio_venta);

			if (muestra === "Muestra") {
				$('#facturas-counter').hide();
			}

			var porcentaje_isv = 0;
			var porcentaje_calculo = 0;

			if (isv == 1) {
				porcentaje_isv = parseFloat(getPorcentajeISV("Facturas") / 100);
				porcentaje_calculo = (parseFloat(precio_venta) * porcentaje_isv).toFixed(2);

				$('#formulario_facturacion #invoiceItem #isv_0').val(isv);
				$('#formulario_facturacion #invoiceItem #valor_isv_0').val(porcentaje_calculo);
				$('#formulario_facturacion #taxAmount').val(porcentaje_calculo);
			} else {
				$('#formulario_facturacion #invoiceItem #isv_0').val(isv);
				$('#formulario_facturacion #invoiceItem #valor_isv_0').val(0);
				$('#formulario_facturacion #taxAmount').val(0);
			}

			var neto = (parseFloat(precio_venta) + parseFloat(porcentaje_calculo || 0)).toFixed(2);

			$('#formulario_facturacion #subTotal').val(precio_venta);
			$('#formulario_facturacion #taxAmount').val(porcentaje_calculo);
			$('#formulario_facturacion #taxDescuento').val(0);
			$('#formulario_facturacion #totalAftertax').val(neto);

			$('#subTotalFooter').val(precio_venta);
			$('#taxAmountFooter').val(porcentaje_calculo);
			$('#taxDescuentoFooter').val(0);
			$('#totalAftertaxFooter').val(neto);

			$('.footer').hide();
			$('.footer1').show();

			return false;
		},
		error: function(){
			showNotify("error", "Error", "No se pudo cargar la muestra para facturar");
		}
	});

	return false;
}

/****************************************************************************************************************************************************************/
// PAGOS
/****************************************************************************************************************************************************************/

function pago(facturas_id){
	var url = '<?php echo SERVERURL; ?>php/facturacion/editarPago.php';

	$.ajax({
		type: 'POST',
		url: url,
		cache: false,
		data: {
			facturas_id: facturas_id
		},
		success: function(valores){
			var datos = jsonResponse(valores);

			$('#formEfectivoBill .border-right a:eq(0) a').tab('show');

			$("#customer-name-bill").html("<b>Cliente:</b> " + datos[0]);
			$("#customer_bill_pay").val(datos[2]);
			$('#bill-pay').html("L. " + parseFloat(datos[2]).toFixed(2));

			$('#formEfectivoBill')[0].reset();
			$('#formEfectivoBill #monto_efectivo').val(datos[2]);
			$('#formEfectivoBill #factura_id_efectivo').val(facturas_id);
			$('#formEfectivoBill #pago_efectivo').attr('disabled', true);

			$('#formTarjetaBill')[0].reset();
			$('#formTarjetaBill #monto_efectivo').val(datos[2]);
			$('#formTarjetaBill #factura_id_tarjeta').val(facturas_id);

			$('#formTransferenciaBill')[0].reset();
			$('#formTransferenciaBill #monto_efectivo').val(datos[2]);
			$('#formTransferenciaBill #factura_id_transferencia').val(facturas_id);

			$('#formMixtoBill')[0].reset();
			$('#formMixtoBill #monto_efectivo_mixto').val(datos[2]);
			$('#formMixtoBill #factura_id_mixto').val(facturas_id);
			$('#formMixtoBill #pago_efectivo_mixto').attr('disabled', true);

			$('#formChequeBill')[0].reset();
			$('#formChequeBill #monto_efectivo').val(datos[2]);
			$('#formChequeBill #factura_id_cheque').val(facturas_id);

			$('#modal_pagos').modal({
				show: true,
				keyboard: false,
				backdrop: 'static'
			});

			return false;
		},
		error: function(){
			showNotify("error", "Error", "No se pudo cargar la información del pago");
		}
	});
}

/****************************************************************************************************************************************************************/
// IMPRESIÓN
/****************************************************************************************************************************************************************/

function printBill(facturas_id){
	var url = '<?php echo SERVERURL; ?>php/facturacion/generaFactura.php?facturas_id=' + facturas_id;
	window.open(url);
}

function printBillGroup(facturas_id){
	var url = '<?php echo SERVERURL; ?>php/facturacion/generaFacturaGrupal.php?facturas_id=' + facturas_id;
	window.open(url);
}

/****************************************************************************************************************************************************************/
// INICIAR PÁGINA
/****************************************************************************************************************************************************************/

$(window).on('load', function(){
	initPage();
});
</script>