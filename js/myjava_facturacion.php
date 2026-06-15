<script>
/****************************************************************************************************************************************************************/
// SELECT2 HELPERS - REEMPLAZO LIMPIO DE BOOTSTRAP SELECT
/****************************************************************************************************************************************************************/

var requestPacienteGrupo = null;
var ultimoTipoPacienteGrupo = null;

function aplicarSelect2Facturacion($select, opciones){
	if (!$select || !$select.length) return;

	if (!$.fn.select2) {
		setTimeout(function(){
			aplicarSelect2Facturacion($select, opciones);
		}, 150);
		return;
	}

	$select.each(function(){
		var $s = $(this);
		var valorActual = $s.val();

		if ($.fn.selectpicker && $s.data('selectpicker')) {
			try {
				$s.selectpicker('destroy');
			} catch(e) {}
		}

		$s.removeClass('selectpicker');
		$s.removeAttr('data-live-search');
		$s.removeAttr('data-size');
		$s.removeAttr('data-width');
		$s.removeAttr('title');

		if ($s.data('select2')) {
			$s.select2('destroy');
		}

		$s.next('.select2').remove();

		var config = $.extend({
			width: $s.attr('style') && $s.attr('style').indexOf('width') >= 0 ? 'style' : '100%',
			placeholder: 'Seleccione',
			minimumResultsForSearch: 0,
			dropdownAutoWidth: false,
			allowClear: false
		}, opciones || {});

		$s.select2(config);

		if (valorActual !== null && valorActual !== undefined && valorActual !== '') {
			$s.val(valorActual).trigger('change.select2');
		}
	});
}

function refrescarSelectPicker(selector){
	var $select = $(selector);

	if (!$select.length) return;

	if ($.fn.selectpicker && $select.data('selectpicker')) {
		try {
			$select.selectpicker('destroy');
		} catch(e) {}
	}

	$select.removeClass('selectpicker');
	$select.removeAttr('data-live-search');
	$select.removeAttr('data-size');
	$select.removeAttr('data-width');
	$select.removeAttr('title');

	if ($select.attr('id') === 'tipo_paciente_grupo') {
		aplicarSelect2Facturacion($select, {
			width: '170px',
			placeholder: 'Tipo Cliente',
			minimumResultsForSearch: 0
		});
	} else if ($select.attr('id') === 'pacientesIDGrupo') {
		inicializarSelect2PacienteGrupo();
	} else if ($select.attr('id') === 'estado') {
		aplicarSelect2Facturacion($select, {
			width: '150px',
			placeholder: 'Estado',
			minimumResultsForSearch: 0
		});
	} else if ($select.attr('id') === 'servicio_idGrupo') {
		aplicarSelect2Facturacion($select, {
			width: '100%',
			placeholder: 'Servicio',
			minimumResultsForSearch: 0
		});
	} else {
		aplicarSelect2Facturacion($select, {
			width: '100%',
			placeholder: 'Seleccione',
			minimumResultsForSearch: 0
		});
	}
}

function reforzarSelect2Facturacion(){
	aplicarSelect2Facturacion($('#form_main_facturas #tipo_paciente_grupo'), {
		width: '170px',
		placeholder: 'Tipo Cliente',
		minimumResultsForSearch: 0
	});

	aplicarSelect2Facturacion($('#form_main_facturas #estado'), {
		width: '150px',
		placeholder: 'Estado',
		minimumResultsForSearch: 0
	});

	if ($('#form_main_facturas #pacientesIDGrupo').length && !$('#form_main_facturas #pacientesIDGrupo').hasClass('select2-hidden-accessible')) {
		inicializarSelect2PacienteGrupo();
	}

	if ($('#formGrupoFacturacion #servicio_idGrupo').length) {
		aplicarSelect2Facturacion($('#formGrupoFacturacion #servicio_idGrupo'), {
			width: '100%',
			placeholder: 'Servicio',
			minimumResultsForSearch: 0
		});
	}
}

$(document).on('select2:open', function(){
	setTimeout(function(){
		var search = document.querySelector('.select2-container--open .select2-search__field');
		if (search) search.focus();
	}, 50);
});

$(document).on('mousedown', '#form_main_facturas select, #formGrupoFacturacion select', function(e){
	var $s = $(this);

	if (!$.fn.select2) return;

	if (!$s.hasClass('select2-hidden-accessible')) {
		e.preventDefault();

		refrescarSelectPicker('#' + $s.attr('id'));

		setTimeout(function(){
			$s.select2('open');
		}, 50);
	}
});

/****************************************************************************************************************************************************************/
// HELPERS PARA PACIENTE / CLIENTE
/****************************************************************************************************************************************************************/

function setPacienteFacturaNormal(pacientes_id, paciente_nombre){
	pacientes_id = $.trim(pacientes_id || '');
	paciente_nombre = $.trim(paciente_nombre || '');

	if (pacientes_id === '' || pacientes_id === '0') {
		swal({
			title: "Error",
			text: "No se recibió el código interno del paciente.",
			icon: "error",
			dangerMode: true,
			closeOnEsc: false,
			closeOnClickOutside: false
		});
		return false;
	}

	$('#formulario_facturacion #pacientes_id').val(pacientes_id);
	$('#formulario_facturacion #cliente_nombre').val(paciente_nombre);

	if (typeof getTipoPaciente === 'function') {
		if (getTipoPaciente(pacientes_id) == 2) {
			$('#formulario_facturacion #grupo_paciente_factura').show();
		} else {
			$('#formulario_facturacion #grupo_paciente_factura').hide();
		}
	}

	return true;
}

function setPacienteFiltroPrincipal(pacientes_id, paciente_nombre){
	pacientes_id = $.trim(pacientes_id || '');
	paciente_nombre = $.trim(paciente_nombre || '');

	if (pacientes_id === '' || pacientes_id === '0') {
		swal({
			title: "Error",
			text: "No se recibió el código interno del cliente.",
			icon: "error",
			dangerMode: true,
			closeOnEsc: false,
			closeOnClickOutside: false
		});
		return false;
	}

	var $cliente = $('#form_main_facturas #pacientesIDGrupo');

	if (!$cliente.length) {
		return false;
	}

	if ($cliente.find("option[value='" + pacientes_id + "']").length === 0) {
		var option = new Option(paciente_nombre, pacientes_id, true, true);
		$cliente.append(option);
	}

	$cliente.val(pacientes_id).trigger('change');

	if ($.fn.select2 && $cliente.hasClass('select2-hidden-accessible')) {
		$cliente.trigger('change.select2');
	}

	return true;
}

/****************************************************************************************************************************************************************/
// INICIO CONTROLES DE ACCION
/****************************************************************************************************************************************************************/

$(document).ready(function() {
	$('.footer').show();
    $('.footer1').hide();

	getTotalFacturasDisponibles();

	funciones();

	$("#form_main_facturas #buscar").on("click", function(e){
		e.preventDefault();
		pagination(1);
	});

	setTimeout(function(){
		reforzarSelect2Facturacion();
	}, 300);

	setTimeout(function(){
		reforzarSelect2Facturacion();
	}, 800);

	setTimeout(function(){
		reforzarSelect2Facturacion();
	}, 1500);
});

/****************************************************************************************************************************************************************/
// INICIO FUNCIONES
/****************************************************************************************************************************************************************/

function getColaboradorConsulta(){
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

function funciones(){
    pagination(1);
	getColaborador();
	getTipoPacienteGrupo();
	getEstado();
	getPacientes();

	getServicio();
	getBanco();
	listar_pacientes_buscar();
	listar_colaboradores_buscar();
	listar_servicios_factura_buscar();
	listar_productos_facturas_buscar();

	setTimeout(function(){
		reforzarSelect2Facturacion();
	}, 500);
}

function pagination(partida){
	var url = '<?php echo SERVERURL; ?>php/facturacion/paginar.php';

	var fechai = $('#form_main_facturas #fecha_b').val();
	var fechaf = $('#form_main_facturas #fecha_f').val();
	var dato = $('#form_main_facturas #bs_regis').val();
	var tipo_paciente_grupo;
	var pacientesIDGrupo;
	var estado = '';

	if($('#form_main_facturas #tipo_paciente_grupo').val() == "" || $('#form_main_facturas #tipo_paciente_grupo').val() == null){
		tipo_paciente_grupo = "";
	}else{
		tipo_paciente_grupo = $('#form_main_facturas #tipo_paciente_grupo').val();
	}

	if($('#form_main_facturas #pacientesIDGrupo').val() == "" || $('#form_main_facturas #pacientesIDGrupo').val() == null){
		pacientesIDGrupo = "";
	}else{
		pacientesIDGrupo = $('#form_main_facturas #pacientesIDGrupo').val();
	}

	if($('#form_main_facturas #estado').val() == "" || $('#form_main_facturas #estado').val() == null){
		estado = 1;
	}else{
		estado = $('#form_main_facturas #estado').val();
	}

	$.ajax({
		type:'POST',
		url:url,
		async: true,
		data:'partida='+partida+'&fechai='+fechai+'&fechaf='+fechaf+'&dato='+dato+'&tipo_paciente_grupo='+tipo_paciente_grupo+'&pacientesIDGrupo='+pacientesIDGrupo+'&estado='+estado,
		success:function(data){
			var array = eval(data);
			$('#agrega-registros').html(array[0]);
			$('#pagination').html(array[1]);

			setTimeout(function(){
				reforzarSelect2Facturacion();
			}, 50);
		}
	});

	return false;
}

function getPacientes(){
    var url = '<?php echo SERVERURL; ?>php/facturacion/getPacientes.php';

	$.ajax({
        type: "POST",
        url: url,
	    async: true,
        success: function(data){
		    $('#formularioFactura #paciente').html("");
			$('#formularioFactura #paciente').html(data);
        }
     });
}

function getEstado(){
    var url = '<?php echo SERVERURL; ?>php/facturacion/getEstado.php';

	$.ajax({
        type: "POST",
        url: url,
	    async: true,
        success: function(data){
		    $('#form_main_facturas #estado').html("");
			$('#form_main_facturas #estado').html(data);

			aplicarSelect2Facturacion($('#form_main_facturas #estado'), {
				width: '150px',
				placeholder: 'Estado',
				minimumResultsForSearch: 0
			});
        }
     });
}

function getColaborador(){
    var url = '<?php echo SERVERURL; ?>php/citas/getMedico.php';

	$.ajax({
        type: "POST",
        url: url,
        success: function(data){
		    $('#form_main_facturas #profesional').html("");
			$('#form_main_facturas #profesional').html(data);

			if ($('#form_main_facturas #profesional').length) {
				aplicarSelect2Facturacion($('#form_main_facturas #profesional'), {
					width: '180px',
					placeholder: 'Profesional',
					minimumResultsForSearch: 0
				});
			}
		}
     });
}

function mailBill(facturas_id){
	swal({
		title: "¿Estas seguro?",
		text: "¿Desea enviar este numero de factura: # " + getNumeroFactura(facturas_id) + "?",
		icon: "warning",
		buttons: {
			cancel: {
				text: "Cancelar",
				visible: true
			},
			confirm: {
				text: "¡Sí, enviar la factura!",
			}
		},
		dangerMode: true,
		closeOnEsc: false,
		closeOnClickOutside: false	
	}).then((willConfirm) => {
		if (willConfirm === true) {
			sendMail(facturas_id);
		}
	});
}

function mailBillGroup(facturas_id){
	swal({
		title: "¿Estas seguro?",
		text: "¿Desea enviar este numero de factura: # " + getNumeroFacturaGroup(facturas_id) + "?",
		icon: "warning",
		buttons: {
			cancel: {
				text: "Cancelar",
				visible: true
			},
			confirm: {
				text: "¡Sí, enviar la factura!",
			}
		},
		dangerMode: true,
		closeOnEsc: false,
		closeOnClickOutside: false	
	}).then((willConfirm) => {
		if (willConfirm === true) {
			sendMailGroup(facturas_id);
		}
	});
}

function getNumeroFactura(facturas_id){
	var url = '<?php echo SERVERURL; ?>php/facturacion/getNoFactura.php';
	var noFactura = '';

	$.ajax({
	   type:'POST',
	   url:url,
	   async: false,
	   data:'facturas_id='+facturas_id,
	   success:function(data){
			var datos = eval(data);
			noFactura = datos[0];
	  }
	});

	return noFactura;
}

function getNumeroFacturaGroup(facturas_id){
	var url = '<?php echo SERVERURL; ?>php/facturacion/getNoFacturaGroup.php';
	var noFactura = '';

	$.ajax({
	   type:'POST',
	   url:url,
	   async: false,
	   data:'facturas_id='+facturas_id,
	   success:function(data){
			var datos = eval(data);
			noFactura = datos[0];
	  }
	});

	return noFactura;
}

function getNumeroNombrePaciente(facturas_id){
	var url = '<?php echo SERVERURL; ?>php/facturacion/getNombrePaciente.php';
	var noFactura = '';

	$.ajax({
	   type:'POST',
	   url:url,
	   async: false,
	   data:'facturas_id='+facturas_id,
	   success:function(data){
			var datos = eval(data);
			noFactura = datos[0];
	  }
	});

	return noFactura;
}

/*
###########################################################################################################################################################
###########################################################################################################################################################
###########################################################################################################################################################
*/
/*															INICIO FACTURACIÓN				   															 */

$('#acciones_atras').on('click', function(e){
	 e.preventDefault();

	 if($('#formulario_facturacion #cliente_nombre').val() != "" || $('#formulario_facturacion #colaborador_nombre').val() != ""){
		swal({
			title: "Tiene datos en la factura",
			text: "¿Esta seguro que desea volver, recuerde que tiene información en la factura la perderá?",
			icon: "warning",
			buttons: {
				cancel: {
					text: "Cancelar",
					visible: true
				},
				confirm: {
					text: "¡Si, deseo volver!",
				}
			},
			dangerMode: true,
			closeOnEsc: false,
			closeOnClickOutside: false
		}).then((willConfirm) => {
			if (willConfirm === true) {
				$('#main_facturacion').show();
				$('#label_acciones_factura').html("");
				$('#facturacion').hide();
				$('#grupo_facturacion').hide();
				$('#acciones_atras').addClass("breadcrumb-item active");
				$('#acciones_factura').removeClass("active");
				$('#formulario_facturacion')[0].reset();
				$('.footer').show();
				$('.footer1').hide();

				setTimeout(function(){
					reforzarSelect2Facturacion();
				}, 100);
			}
		});
	 }else{
		 $('#main_facturacion').show();
		 $('#label_acciones_factura').html("");
		 $('#facturacion').hide();
		 $('#grupo_facturacion').hide();
		 $('#acciones_atras').addClass("breadcrumb-item active");
		 $('#acciones_factura').removeClass("active");
		 $('.footer').show();
    	 $('.footer1').hide();

		 setTimeout(function(){
			reforzarSelect2Facturacion();
		 }, 100);
	 }
});

$('#form_main_facturas #factura').on('click', function(e){
	e.preventDefault();
	formFactura();
});

function modal_pagos(){
	$('#modal_pagos').modal({
		show:true,
		keyboard: false,
		backdrop:'static'
	});
}

function formFactura(){
	 $('#formulario_facturacion')[0].reset();
	 $('#main_facturacion').hide();
	 $('#facturacion').show();
	 $('#label_acciones_volver').html("Facturación");
	 $('#acciones_atras').removeClass("active");
	 $('#acciones_factura').addClass("active");
	 $('#label_acciones_factura').html("Factura");
	 $('#formulario_facturacion #fact_eval').val(1);
	 $('#formulario_facturacion #fecha').attr('disabled', false);
	 $('#formulario_facturacion').attr({ 'data-form': 'save' });
	 $('#formulario_facturacion').attr({ 'action': '<?php echo SERVERURL; ?>php/facturacion/addFactura.php' });
	 limpiarTabla();
	 $('#formulario_facturacion #addRows').show();
	 $('#formulario_facturacion #removeRows').show();
	 $('#formulario_facturacion #buscar_paciente').show();
	 $('#formulario_facturacion #buscar_colaboradores').show();
	 $('#formulario_facturacion #buscar_servicios').show();

	 $('#formulario_facturacion #pacientes_id').val('');
	 $('#formulario_facturacion #cliente_nombre').val('');
	 $('#formulario_facturacion #paciente_muestra').val('');
	 $('#formulario_facturacion #muestras_numero').val('');
}

$(document).ready(function() {
	$('#form_main_facturas #nuevo_registro').on('click',function(e){
		e.preventDefault();
		$('.footer').hide();
    	$('.footer1').show();
		formFacturaGrupo();
	});
});

function formFacturaGrupo(){
	$('#formGrupoFacturacion')[0].reset();
	$('#main_facturacion').hide();
	$('#facturacion').hide();
	$('#grupo_facturacion').show();
	$('#label_acciones_volver').html("Facturación");
	$('#acciones_atras').removeClass("active");
	$('#acciones_factura').addClass("active");
	$('#label_acciones_factura').html("Factura");
	$('#formGrupoFacturacion #fechaGrupo').attr('disabled', false);
	$('#formGrupoFacturacion').attr({ 'data-form': 'save' });
	$('#formGrupoFacturacion').attr({ 'action': '<?php echo SERVERURL; ?>php/facturacion/addGrupoFactura.php' });

	$('#formGrupoFacturacion #fechaGrupo').attr('readonly', 'true');

	$('#formGrupoFacturacion #invoiceItemGrupo > tbody').empty();
	$('#formGrupoFacturacion #clienteIDGrupo').val($('#form_main_facturas #pacientesIDGrupo').val());
	$('#formGrupoFacturacion #clienteNombreGrupo').val(getPacienteNombre($('#main_facturacion #pacientesIDGrupo').val()));

	$('#formGrupoFacturacion #buscar_pacienteGrupo').hide();
	$('#formGrupoFacturacion #buscar_colaboradoresGrupo').hide();
	$('#formGrupoFacturacion #buscar_serviciosGrupo').hide();

	var tamaño = 0;
	var subTotal = 0;
	var ISVGrupo = 0;
	var descuentoGrupo  = 0;

	$('.registros .itemRowFactura').each(function(){
		if(this.checked){
			llenarTablaFacturaFacturaGrupo(tamaño);

			$('#formGrupoFacturacion #colaborador_idGrupo').val($('#profesionalIDGrupo_'+this.value).attr('data-value'));
			$('#formGrupoFacturacion #colaborador_nombreGrupo').val(getProfesionalNombre($('#profesionalIDGrupo_'+this.value).attr('data-value')));
			$('#formGrupoFacturacion #invoiceItemGrupo #quantyGrupoQuantity_'+tamaño).val($('#quantyGrupoQuantityValor_'+this.value).attr('data-value'));
			$('#formGrupoFacturacion #invoiceItemGrupo #billGrupoMuestraID_'+tamaño).val($('#muestraGrupo_'+this.value).attr('data-value'));
			$('#formGrupoFacturacion #invoiceItemGrupo #billGrupoMaterial_'+tamaño).val(getMaterialEnviado($('#muestraGrupo_'+this.value).attr('data-value')));
			$('#formGrupoFacturacion #invoiceItemGrupo #billGrupoID_'+tamaño).val($('#codigoFacturaGrupo_'+this.value).attr('data-value'));
			$('#formGrupoFacturacion #invoiceItemGrupo #pacienteIDBillGrupo_'+tamaño).val($('#pacientesIDFacturaGrupo_'+this.value).attr('data-value'));
			$('#formGrupoFacturacion #invoiceItemGrupo #pacienteBillGrupo_'+tamaño).val(getPacienteNombre($('#pacientesIDFacturaGrupo_'+this.value).attr('data-value')));
			$('#formGrupoFacturacion #invoiceItemGrupo #importeBillGrupo_'+tamaño).val(parseFloat($('#precioFacturaGrupo_'+this.value).attr('data-value')).toFixed(2));
			$('#formGrupoFacturacion #invoiceItemGrupo #discountBillGrupo_'+tamaño).val(parseFloat($('#DescuentoFacturaGrupo_'+this.value).attr('data-value')).toFixed(2));
			$('#formGrupoFacturacion #invoiceItemGrupo #totalBillGrupo_'+tamaño).val(parseFloat($('#precioFacturaGrupo_'+this.value).attr('data-value')).toFixed(2));

			subTotal += parseFloat($('#netoAntesISVFacturaGrupo_'+this.value).attr('data-value'));
			ISVGrupo += parseFloat($('#ISVFacturaGrupo_'+this.value).attr('data-value'));
			descuentoGrupo += parseFloat($('#DescuentoFacturaGrupo_'+this.value).attr('data-value'));

			console.log(tamaño);
			tamaño++;
		}
	});

	$('#formGrupoFacturacion #tamano').val(tamaño);
	netoGrupo = (subTotal + ISVGrupo) - descuentoGrupo;

	$('#formGrupoFacturacion #servicio_idGrupo').val(1);

	aplicarSelect2Facturacion($('#formGrupoFacturacion #servicio_idGrupo'), {
		width: '100%',
		placeholder: 'Servicio',
		minimumResultsForSearch: 0
	});

	$('#formGrupoFacturacion #servicio_idGrupo').val(1).trigger('change.select2');

	$('#formGrupoFacturacion #subTotalBillGrupo').val(parseFloat(subTotal).toFixed(2));
	$('#formGrupoFacturacion #taxAmountBillGrupo').val(parseFloat(ISVGrupo).toFixed(2));
	$('#formGrupoFacturacion #taxDescuentoBillGrupo').val(parseFloat(descuentoGrupo).toFixed(2));
	$('#formGrupoFacturacion #totalAftertaxBillGrupo').val(parseFloat(netoGrupo).toFixed(2));

	$('#subTotalFooter').val(parseFloat(subTotal).toFixed(2));
	$('#taxAmountFooter').val(parseFloat(ISVGrupo).toFixed(2));
	$('#taxDescuentoFooter').val(parseFloat(descuentoGrupo).toFixed(2));
	$('#totalAftertaxFooter').val(parseFloat(netoGrupo).toFixed(2));
}

function llenarTablaFacturaFacturaGrupo(count){
	var htmlRows = '';
	htmlRows += '<tr>';
	htmlRows += '<td><input type="hidden" name="quantyGrupoQuantity[]" id="quantyGrupoQuantity_'+count+'" class="form-control" placeholder="Cantidad" readonly autocomplete="off"><input type="hidden" name="billGrupoMuestraID[]" id="billGrupoMuestraID_'+count+'" class="form-control" placeholder="Muestra ID" readonly autocomplete="off"><input type="hidden" name="billGrupoMaterial[]" id="billGrupoMaterial_'+count+'" class="form-control" placeholder="Material Enviado" readonly autocomplete="off"><input type="hidden" name="billGrupoDescuento[]" id="billGrupoDescuento_'+count+'" class="form-control" placeholder="Descuento" readonly autocomplete="off"><input type="hidden" name="billGrupoISV[]" id="billGrupoISV_'+count+'" value="0" class="form-control" placeholder="ISV" readonly autocomplete="off"><input type="hidden" name="billGrupoID[]" id="billGrupoID_'+count+'" class="form-control" placeholder="Código Factura" readonly autocomplete="off"><input type="hidden" name="pacienteIDBillGrupo[]" id="pacienteIDBillGrupo_'+count+'" class="form-control" readonly placeholder="Paciente" autocomplete="off"><input type="text" name="pacienteBillGrupo[]" id="pacienteBillGrupo_'+count+'" class="form-control" readonly placeholder="Paciente" autocomplete="off"></td>';
	htmlRows += '<td><input type="number" name="importeBillGrupo[]" id="importeBillGrupo_'+count+'" class="form-control" readonly placeholder="Saldo" autocomplete="off"></td>';
	htmlRows += '<td><input type="number" name="discountBillGrupo[]" id="discountBillGrupo_'+count+'" readonly value="0" class="form-control" placeholder="Descuento" autocomplete="off"></td>';
	htmlRows += '<td><input type="number" name="totalBillGrupo[]" id="totalBillGrupo_'+count+'" class="form-control total" placeholder="Total" readonly autocomplete="off"></td>';
	htmlRows += '</tr>';

	$('#formGrupoFacturacion #invoiceItemGrupo').append(htmlRows);
}

function limpiarTablaFacturaGrupo(){
	$("#formGrupoFacturacion #invoiceItemGrupo > tbody").empty();

	var count = 0;
	var htmlRows = '';

	htmlRows += '<tr>';
	htmlRows += '<td><input type="hidden" name="quantyGrupoQuantity[]" id="quantyGrupoQuantity_'+count+'" class="form-control" placeholder="Cantidad" readonly autocomplete="off"><input type="hidden" name="billGrupoMuestraID[]" id="billGrupoMuestraID_'+count+'" class="form-control" placeholder="Muestra ID" readonly autocomplete="off"><input type="hidden" name="billGrupoMaterial[]" id="billGrupoMaterial_'+count+'" class="form-control" placeholder="Material Enviado" readonly autocomplete="off"><input type="hidden" name="billGrupoDescuento[]" id="billGrupoDescuento_'+count+'" class="form-control" placeholder="Descuento" readonly autocomplete="off"><input type="hidden" name="billGrupoISV[]" id="billGrupoISV_'+count+'" value="0" class="form-control" placeholder="ISV" readonly autocomplete="off"><input type="hidden" name="billGrupoID[]" id="billGrupoID_'+count+'" class="form-control" placeholder="Código Factura" readonly autocomplete="off"><input type="hidden" name="pacienteIDBillGrupo[]" id="pacienteIDBillGrupo_'+count+'" class="form-control" readonly placeholder="Paciente" autocomplete="off"><input type="text" name="pacienteBillGrupo[]" id="pacienteBillGrupo_'+count+'" class="form-control" readonly placeholder="Paciente" autocomplete="off"></td>';
	htmlRows += '<td><input type="number" name="importeBillGrupo[]" id="importeBillGrupo_'+count+'" class="form-control" readonly placeholder="Saldo" autocomplete="off"></td>';
	htmlRows += '<td><input type="number" name="discountBillGrupo[]" id="discountBillGrupo_'+count+'" readonly value="0" class="form-control" placeholder="Descuento" autocomplete="off"></td>';
	htmlRows += '<td><input type="number" name="totalBillGrupo[]" id="totalBillGrupo_'+count+'" class="form-control total" placeholder="Total" readonly autocomplete="off"></td>';
	htmlRows += '</tr>';

	$('#formGrupoFacturacion #invoiceItemGrupo').append(htmlRows);
}

$(document).ready(function() {
	$('#label_acciones_volver').html("Facturación");
	$('#acciones_atras').addClass("active");
	$('#label_acciones_factura').html("");
});

/****************************************************************************************************************************************************************/
// BUSQUEDA COLABORADORES
/****************************************************************************************************************************************************************/

$('#formulario_facturacion #buscar_colaboradores').on('click', function(e){
	e.preventDefault();

	listar_colaboradores_buscar();

	$('#modal_busqueda_colaboradores').modal({
		show:true,
		keyboard: false,
		backdrop:'static'
	});
});

var listar_colaboradores_buscar = function(){
	var table_colaboradores_buscar = $("#dataTableColaboradores").DataTable({
		"destroy":true,
		"ajax":{
			"method":"POST",
			"url":"<?php echo SERVERURL; ?>php/facturacion/getColaboradoresTabla.php"
		},
		"columns":[
			{"defaultContent":"<button class='view btn btn-primary'><span class='fas fa-copy'></span></button>"},
			{"data":"colaborador"},
			{"data":"identidad"},
			{"data":"puesto"}
		],
		"pageLength" : 5,
        "lengthMenu": lengthMenu,
		"stateSave": true,
		"bDestroy": true,
		"language": idioma_español,
	});

	table_colaboradores_buscar.search('').draw();
	$('#buscar').focus();

	view_colaboradores_busqueda_dataTable("#dataTableColaboradores tbody", table_colaboradores_buscar);
}

var view_colaboradores_busqueda_dataTable = function(tbody, table){
	$(tbody).off("click", "button.view");
	$(tbody).on("click", "button.view", function(e){
		e.preventDefault();

		var data = table.row($(this).parents("tr")).data();

		$('#formulario_facturacion #colaborador_id').val(data.colaborador_id);
		$('#formulario_facturacion #colaborador_nombre').val(data.colaborador);
		$('#modal_busqueda_colaboradores').modal('hide');
	});
}

/****************************************************************************************************************************************************************/
// ELIMINAR FACTURA BORRADOR
/****************************************************************************************************************************************************************/

function deleteBill(facturas_id){
	if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
		swal({
			title: "¿Estas seguro?",
			text: "¿Desea eliminar la factura para el paciente: " + getNumeroNombrePaciente(facturas_id) + "?",
			icon: "warning",
			buttons: {
				cancel: {
					text: "Cancelar",
					visible: true
				},
				confirm: {
					text: "¡Si, deseo eliminar la factura!",
				}
			},
			dangerMode: true,
			closeOnEsc: false,
			closeOnClickOutside: false
		}).then((willConfirm) => {
			if (willConfirm === true) {
				eliminarFacturaBorrador(facturas_id);
			}
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
	}
}

function eliminarFacturaBorrador(facturas_id){
	var url = '<?php echo SERVERURL; ?>php/facturacion/eliminar.php';

	$.ajax({
		type:'POST',
		url:url,
		data:'facturas_id='+facturas_id,
		success: function(registro){
			if(registro == 1){
				swal({
					title: "Success",
					text: "Registro eliminado correctamente",
					icon: "success",
					timer: 3000,
					closeOnEsc: false,
					closeOnClickOutside: false					
				});

				pagination(1);
			    return false;
			}else if(registro == 2){
				swal({
					title: "Error al eliminar el registro, por favor intentelo de nuevo o verifique que no tenga información almacenada",
					text: "No tiene permisos para ejecutar esta acción",
					icon: "error",
					dangerMode: true,
					closeOnEsc: false,
					closeOnClickOutside: false
				});

			    return false;
			}else{
				swal({
					title: "No se puede procesar su solicitud, por favor intentelo de nuevo mas tarde",
					text: "No tiene permisos para ejecutar esta acción",
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

$(document).ready(function(){
	$(document).on('click', '#checkAllFactura', function(){
		if($('#form_main_facturas #tipo_paciente_grupo').val() == 2 && $('#form_main_facturas #pacientesIDGrupo').val() != ""){
			$(".itemRowFactura").prop("checked", this.checked);

			if ($('#checkAllFactura').is(':checked') ){
				$('#main_facturacion #factura_manual').show();
				calcularTotalFactura();
			}else{
				$('#main_facturacion #factura_manual').hide();
			}
		}
	});

	$(document).on('click', '.itemRowFactura', function(){
		if($('#form_main_facturas #tipo_paciente_grupo').val() == 2 && $('#form_main_facturas #pacientesIDGrupo').val() != ""){
			if ($('.itemRowFactura').is(':checked') ){
				$('#main_facturacion #factura_manual').show();
				calcularTotalFactura();
			}else{
				$('#main_facturacion #factura_manual').hide();
			}

			if ($('.itemRowFactura:checked').length == $('.itemRowFactura').length) {
				$('#checkAllFactura').prop('checked', true);
			} else {
				$('#checkAllFactura').prop('checked', false);
			}
		}
	});

	var count = $(".itemRowFactura").length;
});

function calcularTotalFactura(){
	var total_factura = 0;

	if($('#form_main_facturas #tipo_paciente_grupo').val() == 2 && $('#form_main_facturas #pacientesIDGrupo').val() != ""){
	   	$("[id^='importeFacturaGrupo_']").each(function() {
			var id = $(this).attr('id');
			id = id.replace("importeFacturaGrupo_", '');
			var importe_factura = parseFloat($('#importeFacturaGrupo_'+id).attr('data-value'));
			total_factura += importe_factura;
		});

	   	$("[id^='codigoFacturaGrupo_']").each(function() {
			var id = $(this).attr('id');
			id = id.replace("codigoFacturaGrupo_", '');
			var importe_factura = parseFloat($('#codigoFacturaGrupo_'+id).attr('data-value'));
			total_factura += importe_factura;
		});
	}
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

function getPacienteNombre(pacientes_id){
	var url = '<?php echo SERVERURL; ?>php/muestras/getPacienteNombre.php';
	var paciente_nombre;

	$.ajax({
	    type:'POST',
		url:url,
		data:'pacientes_id='+pacientes_id,
		async: false,
		success:function(data){
          paciente_nombre = data;
		}
	});

	return paciente_nombre;
}

function getProfesionalNombre(colaborador_id){
	var url = '<?php echo SERVERURL; ?>php/muestras/getProfesionalNombre.php';
	var colaborador_nombre;

	$.ajax({
	    type:'POST',
		url:url,
		data:'colaborador_id='+colaborador_id,
		async: false,
		success:function(data){
          colaborador_nombre = data;
		}
	});

	return colaborador_nombre;
}

function getMaterialEnviado(muestras_id){
	var url = '<?php echo SERVERURL; ?>php/muestras/getMaterialEnviado.php';
	var material_enviado;

	$.ajax({
	    type:'POST',
		url:url,
		data:'muestras_id='+muestras_id,
		async: false,
		success:function(data){
		  var datos = eval(data);
          material_enviado = datos[0];
		}
	});

	return material_enviado;
}

/****************************************************************************************************************************************************************/
// INICIO TIPO CLIENTE / CLIENTE GRUPO CON SELECT2 Y MISMO PHP ORIGINAL
/****************************************************************************************************************************************************************/

function limpiarSelectPacienteGrupo(mensaje){
	var texto = mensaje || 'Seleccione un cliente';

	var $cliente = $('#form_main_facturas #pacientesIDGrupo');

	if ($cliente.data('select2')) {
		$cliente.select2('destroy');
	}

	$cliente.next('.select2').remove();

	$cliente.html('<option value="">' + texto + '</option>').val('');

	inicializarSelect2PacienteGrupo();
}

function bloquearSelectPacienteGrupo(bloquear){
	$('#form_main_facturas #pacientesIDGrupo').prop('disabled', bloquear);

	if ($('#form_main_facturas #pacientesIDGrupo').data('select2')) {
		$('#form_main_facturas #pacientesIDGrupo').trigger('change.select2');
	}
}

function getTipoPacienteGrupo(){
	var url = '<?php echo SERVERURL; ?>php/facturacion/getTipoPaciente.php';

	$.ajax({
		type: 'POST',
		url: url,
		cache: false,
		beforeSend: function(){
			var $tipo = $('#form_main_facturas #tipo_paciente_grupo');

			$tipo.html('<option value="">Cargando...</option>').val('');

			aplicarSelect2Facturacion($tipo, {
				width: '170px',
				placeholder: 'Tipo Cliente',
				minimumResultsForSearch: 0
			});

			limpiarSelectPacienteGrupo('Seleccione primero un tipo de cliente');
			bloquearSelectPacienteGrupo(true);
		},
		success: function(data){
			var $tipo = $('#form_main_facturas #tipo_paciente_grupo');

			$tipo.html(data);

			var tipoInicial = $tipo.find('option:first').val();

			if (tipoInicial === '' || tipoInicial === null || typeof tipoInicial === 'undefined') {
				tipoInicial = '1';
			}

			$tipo.val(tipoInicial);

			aplicarSelect2Facturacion($tipo, {
				width: '170px',
				placeholder: 'Tipo Cliente',
				minimumResultsForSearch: 0
			});

			inicializarSelect2PacienteGrupo();
			bloquearSelectPacienteGrupo(false);
		},
		error: function(xhr, status, error){
			console.error('Error al cargar tipos de cliente:', error);

			var $tipo = $('#form_main_facturas #tipo_paciente_grupo');

			$tipo.html('<option value="">Error al cargar</option>').val('');

			aplicarSelect2Facturacion($tipo, {
				width: '170px',
				placeholder: 'Tipo Cliente',
				minimumResultsForSearch: 0
			});

			limpiarSelectPacienteGrupo('No se pudieron cargar los clientes');
			bloquearSelectPacienteGrupo(false);
		}
	});
}

function inicializarSelect2PacienteGrupo(){
	var $cliente = $('#form_main_facturas #pacientesIDGrupo');

	if (!$cliente.length) return;

	if (!$.fn.select2) {
		setTimeout(function(){
			inicializarSelect2PacienteGrupo();
		}, 150);
		return;
	}

	if ($.fn.selectpicker && $cliente.data('selectpicker')) {
		try {
			$cliente.selectpicker('destroy');
		} catch(e) {}
	}

	$cliente.removeClass('selectpicker');
	$cliente.removeAttr('data-live-search');
	$cliente.removeAttr('data-size');
	$cliente.removeAttr('data-width');
	$cliente.removeAttr('title');

	if ($cliente.data('select2')) {
		$cliente.select2('destroy');
	}

	$cliente.next('.select2').remove();

	$cliente.select2({
		width: '260px',
		placeholder: 'Cliente',
		minimumInputLength: 0,
		minimumResultsForSearch: 0,
		allowClear: false,
		ajax: {
			url: '<?php echo SERVERURL; ?>php/facturacion/getPacienteGrupo.php',
			type: 'POST',
			dataType: 'json',
			delay: 300,
			data: function(params){
				return {
					tipo_paciente: $('#form_main_facturas #tipo_paciente_grupo').val() || 1,
					term: params.term || ''
				};
			},
			processResults: function(data){
				return {
					results: data.results || []
				};
			},
			cache: true
		},
		templateResult: formatPacienteGrupoResult,
		templateSelection: formatPacienteGrupoSelection
	});
}

function formatPacienteGrupoResult(paciente){
	if (paciente.loading) return 'Cargando...';

	var $container = $('<div>');

	$container.append(
		$('<strong>').text(paciente.nombre || paciente.text || '')
	);

	if (paciente.identidad) {
		$container.append(
			$('<small>')
				.css('display', 'block')
				.css('color', '#6c757d')
				.text('RTN: ' + paciente.identidad)
		);
	}

	return $container;
}

function formatPacienteGrupoSelection(paciente){
	if (paciente.nombre) return paciente.nombre;
	if (paciente.text) return paciente.text.split(' - ')[0];
	return paciente.id || '';
}

function getPacienteGrupo(tipo_paciente){
	tipo_paciente = $.trim(tipo_paciente);

	if (tipo_paciente === '' || tipo_paciente === null || typeof tipo_paciente === 'undefined') {
		limpiarSelectPacienteGrupo('Seleccione primero un tipo de cliente');
		bloquearSelectPacienteGrupo(false);
		return false;
	}

	ultimoTipoPacienteGrupo = tipo_paciente;

	if (requestPacienteGrupo !== null) {
		requestPacienteGrupo.abort();
		requestPacienteGrupo = null;
	}

	limpiarSelectPacienteGrupo('Cliente');
	bloquearSelectPacienteGrupo(false);
	inicializarSelect2PacienteGrupo();

	return true;
}

$(document).ready(function(){
    $('#form_main_facturas #tipo_paciente_grupo').off('change').on('change', function(){
        var tipoPaciente = $(this).val();

        ultimoTipoPacienteGrupo = tipoPaciente;

        $('#form_main_facturas #pacientesIDGrupo').val(null).trigger('change');
        inicializarSelect2PacienteGrupo();

        $('#main_facturacion #factura_manual').hide();
        $('#checkAllFactura').prop('checked', false);
        $('.itemRowFactura').prop('checked', false);

		pagination(1);
    });

    $('#form_main_facturas #pacientesIDGrupo').off('change').on('change', function(){
        $('#main_facturacion #factura_manual').hide();
        $('#checkAllFactura').prop('checked', false);
        $('.itemRowFactura').prop('checked', false);

		pagination(1);
    });

	$('#form_main_facturas #estado').off('change').on('change', function(){
		pagination(1);
	});

	$('#form_main_facturas #fecha_b').off('change').on('change', function(){
		pagination(1);
	});

	$('#form_main_facturas #fecha_f').off('change').on('change', function(){
		pagination(1);
	});

	$('#form_main_facturas #bs_regis').off('keyup').on('keyup', function(){
		pagination(1);
	});
});

/****************************************************************************************************************************************************************/
// FIN TIPO CLIENTE / CLIENTE GRUPO
/****************************************************************************************************************************************************************/

function cierreCaja(){
	$('#formularioCierreCaja #pro').val("Cierre de Caja");

	$('#modalCierreCaja').modal({
		show:true,
		keyboard: false,
		backdrop:'static'
	});

	$('#formularioCierreCaja').attr({ 'data-form': 'save' });
	$('#formularioCierreCaja').attr({ 'action': '<?php echo SERVERURL; ?>php/facturacion/addPago.php' });
}

$('#form_main_facturas #cierre').on('click', function(e){
	e.preventDefault();
	cierreCaja();
});

$('#generarCierreCaja').on('click', function(e){
	e.preventDefault();

	var fecha = $('#formularioCierreCaja #fechaCierreCaja').val();
	var url = '<?php echo SERVERURL; ?>php/facturacion/generaCierreCaja.php?fecha='+fecha;

    window.open(url);
	$('#modalCierreCaja').modal('hide');
});

/****************************************************************************************************************************************************************/
// BUSQUEDA CLIENTE PARA FILTRO PRINCIPAL / FACTURA GRUPAL
/****************************************************************************************************************************************************************/

$('#form_main_facturas #buscar_cliente_muestras').off('click').on('click', function(e){
	e.preventDefault();

	listar_pacientesfacturas_tipo_buscar();

	$('#modal_busqueda_pacientes').modal({
		show:true,
		keyboard: false,
		backdrop:'static'
	});
});

var listar_pacientesfacturas_tipo_buscar = function(){
	var tipo_paciente = 1;

	if($("#form_main_facturas #tipo_paciente_grupo").val() != ""){
		tipo_paciente = $("#form_main_facturas #tipo_paciente_grupo").val();
	}

	var table_pacientes_facturas_tipo_buscar = $("#dataTablePacientes").DataTable({
		"destroy":true,
		"ajax":{
			"method":"POST",
			"url":"<?php echo SERVERURL; ?>php/muestras/getPacienteGrupoTabla.php",
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
	});

	table_pacientes_facturas_tipo_buscar.search('').draw();
	$('#buscar').focus();

	view_pacientes_facturas_tipo_busqueda_dataTable("#dataTablePacientes tbody", table_pacientes_facturas_tipo_buscar);
}

var view_pacientes_facturas_tipo_busqueda_dataTable = function(tbody, table){
	$(tbody).off("click", "button.view");
	$(tbody).on("click", "button.view", function(e){
		e.preventDefault();

		var data = table.row($(this).parents("tr")).data();

		if (!data) {
			swal({
				title: "Error",
				text: "No se pudo obtener la información del cliente seleccionado.",
				icon: "error",
				dangerMode: true,
				closeOnEsc: false,
				closeOnClickOutside: false
			});
			return false;
		}

		setPacienteFiltroPrincipal(data.pacientes_id, data.paciente);

		$('#modal_busqueda_pacientes').modal('hide');
	});
}

/*
###########################################################################################################################################################
###########################################################################################################################################################
###########################################################################################################################################################
*/

$(document).ready(function() {
	$('#formulario_facturacion #label_facturas_activo').html("Contado");

    $('#formulario_facturacion .switch').change(function(){
        if($('input[name=facturas_activo]').is(':checked')){
            $('#formulario_facturacion #label_facturas_activo').html("Contado");
            return true;
        }
        else{
            $('#formulario_facturacion #label_facturas_activo').html("Crédito");
            return false;
        }
    });

	$('#formGrupoFacturacion #label_facturas_grupal_activo').html("Contado");

    $('#formGrupoFacturacion .switch').change(function(){
        if($('input[name=facturas_grupal_activo]').is(':checked')){
            $('#formGrupoFacturacion #label_facturas_grupal_activo').html("Contado");
            return true;
        }
        else{
            $('#formGrupoFacturacion #label_facturas_grupal_activo').html("Crédito");
            return false;
        }
    });
});

function pay(facturas_id){
	if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
		$('#formulario_facturacion')[0].reset();
		$("#formulario_facturacion #invoiceItem > tbody").empty();

		var url = '<?php echo SERVERURL; ?>php/facturacion/editarFactura.php';

		$.ajax({
			type:'POST',
			url:url,
			data:'facturas_id='+facturas_id,
			success: function(valores){
				var datos = eval(valores);

				$('#formulario_facturacion #fact_eval').val(1);
				$('#formulario_facturacion #facturas_id').val(facturas_id);
				$('#formulario_facturacion #pacientes_id').val(datos[0]);
				$('#formulario_facturacion #cliente_nombre').val(datos[1]);
				$('#formulario_facturacion #colaborador_id').val(datos[3]);
				$('#formulario_facturacion #colaborador_nombre').val(datos[4]);
				$('#formulario_facturacion #servicio_id').val(datos[5]);

				if ($('#formulario_facturacion #servicio_id').length && $.fn.select2) {
					$('#formulario_facturacion #servicio_id').trigger('change.select2');
				}

				$('#formulario_facturacion #notes').val(datos[6]);
				$('#formulario_facturacion #paciente_muestra').val(datos[7]);
				$('#formulario_facturacion #muestras_numero').val(datos[8]);

				if(getTipoPaciente(datos[0]) == 2){
					$('#formulario_facturacion #grupo_paciente_factura').show();
				}else{
					$('#formulario_facturacion #grupo_paciente_factura').hide();
				}

				$('#formulario_facturacion #fecha').attr("readonly", true);
				$('#formulario_facturacion #validar').attr("disabled", false);
				$('#formulario_facturacion #addRows').attr("disabled", false);
				$('#formulario_facturacion #removeRows').attr("disabled", false);
				$('#formulario_facturacion #validar').hide();
				$('#formulario_facturacion #cobrar').show();
				$('#formulario_facturacion #editar').hide();
				$('#formulario_facturacion #eliminar').hide();
				$('#formulario_facturacion #buscar_paciente').hide();
	 			$('#formulario_facturacion #buscar_colaboradores').hide();
				$('#formulario_facturacion #buscar_servicios').hide();

				$('#grupo_facturacion').hide();
				$('#main_facturacion').hide();
				$('#label_acciones_factura').html("Factura");
				$('#facturacion').show();

				$('#formulario_facturacion').attr({ 'data-form': 'save' });
				$('#formulario_facturacion').attr({ 'action': '<?php echo SERVERURL; ?>php/facturacion/addFacturaporUsuario.php' });

				$('.footer').hide();
     			$('.footer1').show();

				return false;
			}
		});

		var url = '<?php echo SERVERURL; ?>php/facturacion/editarFacturaDetalles.php';
		var isv_valor = 0.0;

		$.ajax({
			type:'POST',
			url:url,
			data:'facturas_id='+facturas_id,
			success:function(data){
				var datos = eval(data);

				for(var fila=0; fila < datos.length; fila++){
					var facturas_detalle_id = datos[fila]["facturas_detalle_id"];
					var productoID = datos[fila]["productos_id"];
					var productName = datos[fila]["producto"];
					var quantity = datos[fila]["cantidad"];
					var price = datos[fila]["precio"];
					var discount = datos[fila]["descuento"];
					var isv = datos[fila]["isv_valor"];
					var producto_isv = datos[fila]["producto_isv"];

					isv_valor = parseFloat(isv_valor) + parseFloat(datos[fila]["isv_valor"]);

					llenarTablaFactura(fila);

					$('#formulario_facturacion #invoiceItem #facturas_detalle_id_'+ fila).val(facturas_detalle_id);
					$('#formulario_facturacion #invoiceItem #productoID_'+ fila).val(productoID);
					$('#formulario_facturacion #invoiceItem #productName_'+ fila).val(productName);
					$('#formulario_facturacion #invoiceItem #quantity_'+ fila).val(quantity);
					$('#formulario_facturacion #invoiceItem #price_'+ fila).val(price);
					$('#formulario_facturacion #invoiceItem #discount_'+ fila).val(discount);
					$('#formulario_facturacion #invoiceItem #valor_isv_'+ fila).val(isv);
					$('#formulario_facturacion #invoiceItem #isv_'+ fila).val(producto_isv);

					$('#formulario_facturacion #invoiceItem #productName_'+ fila).attr("readonly", true);
					$('#formulario_facturacion #invoiceItem #quantity_'+ fila).attr("readonly", true);
					$('#formulario_facturacion #invoiceItem #price_'+ fila).attr("readonly", true);
					$('#formulario_facturacion #invoiceItem #discount_'+ fila).attr("readonly", true);
					$('#formulario_facturacion #invoiceItem #grupo_buscar_productos').hide();
					$('#formulario_facturacion #addRows').hide();
					$('#formulario_facturacion #removeRows').hide();
				}

				$('#formulario_facturacion #taxAmount').val(isv_valor);
				calculateTotal();
			}
		});

		return false;
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
}

/****************************************************************************************************************************************************************/
// BUSQUEDA PACIENTE PARA FACTURA NORMAL
/****************************************************************************************************************************************************************/

$('#formulario_facturacion #buscar_paciente').off('click').on('click', function(e){
	e.preventDefault();

	listar_pacientes_buscar();

	$('#modal_busqueda_pacientes').modal({
		show:true,
		keyboard: false,
		backdrop:'static'
	});
});

var listar_pacientes_buscar = function(){
	var tipo_paciente = $("#form_main_facturas #tipo_paciente_grupo").val();

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
				text: '<i class="fas fa-sync-alt fa-lg"></i> Actualizar',
				titleAttr: 'Actualizar Registro',
				className: 'table_actualizar btn btn-secondary',
				action: function(){
					listar_pacientes_buscar();
				}
			}
		],
	});

	table_pacientes_buscar.search('').draw();
	$('#buscar').focus();

	view_pacientes_busqueda_dataTable("#dataTablePacientes_main_muestras tbody", table_pacientes_buscar);
}

var view_pacientes_busqueda_dataTable = function(tbody, table){
	$(tbody).off("click", "button.view");
	$(tbody).on("click", "button.view", function(e){
		e.preventDefault();

		var data = table.row($(this).parents("tr")).data();

		if (!data) {
			swal({
				title: "Error",
				text: "No se pudo obtener la información del paciente seleccionado.",
				icon: "error",
				dangerMode: true,
				closeOnEsc: false,
				closeOnClickOutside: false
			});
			return false;
		}

		setPacienteFacturaNormal(data.pacientes_id, data.paciente);

		$('#modal_busqueda_pacientes').modal('hide');
		$('#modal_busqueda_pacientes_main_muetras').modal('hide');
	});
}

$('#formularioMuestras #buscar_paciente_consulta_muestras').on('click', function(e){
	$('#modal_busqueda_pacientes_muestras').modal({
		show:true,
		keyboard: false,
		backdrop:'static'
	});
});

function getTotalFacturasDisponibles(){
    var url = '<?php echo SERVERURL; ?>php/facturacion/getTotalFacturasDisponibles.php';

    $.ajax({
        type: 'POST',
        url: url,
        async: true,
        dataType: 'json',
        success: function(valores) {
            function formatNumber(num) {
                num = parseInt(num, 10) || 0;
                return num.toLocaleString('en-US');
            }

            var facturasDisponibles = parseInt(valores[0], 10) || 0;
            var diasRestantes = parseInt(valores[1], 10) || 0;
            var rangoFinal = formatNumber(valores[3] || "0");
            var rangoInicial = formatNumber(valores[4] || "0");

            updateCounter('facturas-counter-1', facturasDisponibles, diasRestantes, rangoInicial, rangoFinal);
            updateCounter('facturas-counter-2', facturasDisponibles, diasRestantes, rangoInicial, rangoFinal);
        },
        error: function(xhr, status, error) {
            console.error("Error al obtener facturas disponibles:", error);
            handleCounterError('facturas-counter-1');
            handleCounterError('facturas-counter-2');
        }
    });
}

function updateCounter(counterId, facturasDisponibles, diasRestantes, rangoInicial, rangoFinal) {
    var counter = $('#' + counterId);
    var counterHeader = $('#' + counterId + ' #counter-header');
    var counterStatus = $('#' + counterId + ' #counter-status');
    var counterNumber = $('#' + counterId + ' #counter-number');
    var counterFooter = $('#' + counterId + ' #counter-footer');
    var counterIcon = $('#' + counterId + ' .counter-icon i');
    
    $('#' + counterId + ' #rango-inicial').text(rangoInicial);
    $('#' + counterId + ' #rango-final').text(rangoFinal);
    
    if(facturasDisponibles < 0 || diasRestantes < 0) {
        counterHeader.html('<strong>LÍMITE AUTORIZADO EXCEDIDO</strong>');
        counterStatus.html('Ha superado el máximo de facturas permitidas');
        counterNumber.text('0');
        counterFooter.html(`<small>Rango autorizado: ${rangoInicial} al ${rangoFinal}</small>`);
        
        counter.addClass('counter-danger').removeClass('counter-normal counter-warning');
        counterIcon.removeClass().addClass('fas fa-ban').css('color', '#dc3545');
        
        $("#validar, #cobrar").prop("disabled", true).css({
            'opacity': '0.6',
            'cursor': 'not-allowed'
        });
    } 
    else if(facturasDisponibles <= 10 || diasRestantes <= 3) {
        counterHeader.html('<strong>ALERTA: FACTURAS DISPONIBLES</strong>');
        counterStatus.html(`${formatNumber(facturasDisponibles)} restantes de ${rangoFinal}`);
        counterNumber.text(formatNumber(facturasDisponibles));
        counterFooter.html(`<small>Quedan ${diasRestantes} días para usar el rango autorizado</small>`);
        
        counter.addClass('counter-warning').removeClass('counter-normal counter-danger');
        counterIcon.removeClass().addClass('fas fa-exclamation-triangle').css('color', '#ffc107');
        
        $("#validar, #cobrar").prop("disabled", false).css({
            'opacity': '1',
            'cursor': 'pointer'
        });
    }
    else if(facturasDisponibles <= 30 || diasRestantes <= 7) {
        counterHeader.html('<strong>FACTURAS DISPONIBLES</strong>');
        counterStatus.html(`${formatNumber(facturasDisponibles)} restantes de ${rangoFinal}`);
        counterNumber.text(formatNumber(facturasDisponibles));
        counterFooter.html(`<small>Rango autorizado: ${rangoInicial} al ${rangoFinal}</small>`);
        
        counter.addClass('counter-warning').removeClass('counter-normal counter-danger');
        counterIcon.removeClass().addClass('fas fa-info-circle').css('color', '#ffc107');
        
        $("#validar, #cobrar").prop("disabled", false).css({
            'opacity': '1',
            'cursor': 'pointer'
        });
    }
    else {
        counterHeader.html('<strong>TOTAL DISPONIBLE AUTORIZADO</strong>');
        counterStatus.html(`${formatNumber(facturasDisponibles)} facturas disponibles`);
        counterNumber.text(formatNumber(facturasDisponibles));
        counterFooter.html(`<small>Rango autorizado: ${rangoInicial} al ${rangoFinal}</small>`);
        
        counter.addClass('counter-normal').removeClass('counter-warning counter-danger');
        counterIcon.removeClass().addClass('fas fa-check-circle').css('color', '#28a745');
        
        $("#validar, #cobrar").prop("disabled", false).css({
            'opacity': '1',
            'cursor': 'pointer'
        });
    }
}

function handleCounterError(counterId) {
    $('#' + counterId + ' #counter-header').html('<strong>ERROR DE CONEXIÓN</strong>');
    $('#' + counterId + ' #counter-status').text('No se pueden cargar los datos').addClass('status-danger');
    $('#' + counterId + ' #counter-number').text('0');
}

function formatNumber(num) {
    num = parseInt(num, 10) || 0;
    return num.toLocaleString('en-US');
}

/****************************************************************************************************************************************************************/
// VALIDACION EXTRA ANTES DE ENVIAR FACTURA NORMAL
/****************************************************************************************************************************************************************/

$(document).off('submit.validarPacienteFacturaNormal', '#formulario_facturacion');
$(document).on('submit.validarPacienteFacturaNormal', '#formulario_facturacion', function(e){
	var pacientes_id = $.trim($('#formulario_facturacion #pacientes_id').val() || '');
	var cliente_nombre = $.trim($('#formulario_facturacion #cliente_nombre').val() || '');

	if (pacientes_id === '' || pacientes_id === '0' || cliente_nombre === '') {
		e.preventDefault();

		swal({
			title: "Paciente requerido",
			text: "Debe seleccionar el paciente antes de registrar la factura.",
			icon: "warning",
			dangerMode: true,
			closeOnEsc: false,
			closeOnClickOutside: false
		});

		return false;
	}

	return true;
});

/****************************************************************************************************************************************************************/
// VALIDACION EXTRA ANTES DE ENVIAR FACTURA GRUPAL
/****************************************************************************************************************************************************************/

$(document).off('submit.validarPacienteFacturaGrupal', '#formGrupoFacturacion');
$(document).on('submit.validarPacienteFacturaGrupal', '#formGrupoFacturacion', function(e){
	var clienteIDGrupo = $.trim($('#formGrupoFacturacion #clienteIDGrupo').val() || '');
	var clienteNombreGrupo = $.trim($('#formGrupoFacturacion #clienteNombreGrupo').val() || '');
	var tamano = parseInt($('#formGrupoFacturacion #tamano').val() || 0, 10);

	if (clienteIDGrupo === '' || clienteIDGrupo === '0' || clienteNombreGrupo === '') {
		e.preventDefault();

		swal({
			title: "Cliente requerido",
			text: "Debe seleccionar el cliente o empresa antes de registrar la factura grupal.",
			icon: "warning",
			dangerMode: true,
			closeOnEsc: false,
			closeOnClickOutside: false
		});

		return false;
	}

	if (tamano <= 0) {
		e.preventDefault();

		swal({
			title: "Detalle requerido",
			text: "Debe seleccionar al menos una factura para generar la factura grupal.",
			icon: "warning",
			dangerMode: true,
			closeOnEsc: false,
			closeOnClickOutside: false
		});

		return false;
	}

	return true;
});

$(function () {
    getTotalFacturasDisponibles();
    setInterval(getTotalFacturasDisponibles, 60000);
});

$(window).on('load', function(){
	setTimeout(function(){
		reforzarSelect2Facturacion();
	}, 300);

	setTimeout(function(){
		reforzarSelect2Facturacion();
	}, 800);

	setTimeout(function(){
		reforzarSelect2Facturacion();
	}, 1500);
});
</script>