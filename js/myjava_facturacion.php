<script>
/****************************************************************************************************************************************************************/
//INICIO CONTROLES DE ACCION
$(document).ready(function() {
	$('.footer').show();
    $('.footer1').hide();
	getTotalFacturasDisponibles();

	//LLAMADA A LAS FUNCIONES
	funciones();

    //INICIO PAGINATION (PARA LAS BUSQUEDAS SEGUN SELECCIONES)
	/*$('#form_main_facturas #bs_regis').on('keyup',function(){
	  pagination(1);
	});

	$('#form_main_facturas #fecha_b').on('change',function(){
	  pagination(1);
	});

	$('#form_main_facturas #fecha_f').on('change',function(){
	  pagination(1);
	});

	$('#form_main_facturas #tipo_paciente_grupo').on('change',function(){
	  pagination(1);
	});

	$('#form_main_facturas #pacientesIDGrupo').on('change',function(){
	  pagination(1);
	});

	$('#form_main_facturas #estado').on('change',function(){
		pagination(1);
	});*/

	$("#form_main_facturas #buscar").on("click", function(e){
		e.preventDefault();
		pagination(1);
	});

	//FIN PAGINATION (PARA LAS BUSQUEDAS SEGUN SELECCIONES)
});
//FIN CONTROLES DE ACCION
/****************************************************************************************************************************************************************/

/***************************************************************************************************************************************************************************/
//INICIO FUNCIONES

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

//INICIO FUNCION PARA OBTENER LAS FUNCIONES
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
}
//FIN FUNCION PARA OBTENER LAS FUNCIONES

//INICIO PAGINACION DE REGISTROS
function pagination(partida){
	var url = '<?php echo SERVERURL; ?>php/facturacion/paginar.php';

	var fechai = $('#form_main_facturas #fecha_b').val();
	var fechaf = $('#form_main_facturas #fecha_f').val();
	var dato = $('#form_main_facturas #bs_regis').val();
	var tipo_paciente_grupo = $('#form_main_facturas #tipo_paciente_grupo').val();
	var pacientesIDGrupo = $('#form_main_facturas #pacientesIDGrupo').val();
	var estado = '';

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
			swal.close();
		}
	});
	return false;
}
//FIN PAGINACION DE REGISTROS

//INICIO FUNCION PARA OBTENER LOS PACIENTES
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
//FIN FUNCION PARA OBTENER LOS PACIENTES

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
//FIN FUNCION PARA OBTENER LOS COLABORADORES

//INICIO FUNCION PARA OBTENER LOS BANCOS DISPONIBLES
function getEstado(){
    var url = '<?php echo SERVERURL; ?>php/facturacion/getEstado.php';

	$.ajax({
        type: "POST",
        url: url,
	    async: true,
        success: function(data){
		    $('#form_main_facturas #estado').html("");
			$('#form_main_facturas #estado').html(data);
			$('#form_main_facturas #estado').selectpicker('refresh');
        }
     });
}
//FIN FUNCION PARA OBTENER LOS BANCOS DISPONIBLES

//INICIO FUNCION PARA OBTENER LOS PROFESIONALES
function getColaborador(){
    var url = '<?php echo SERVERURL; ?>php/citas/getMedico.php';

	$.ajax({
        type: "POST",
        url: url,
        success: function(data){
		    $('#form_main_facturas #profesional').html("");
			  $('#form_main_facturas #profesional').html(data);
				$('#form_main_facturas #profesional').selectpicker('refresh');
		}
     });
}
//FIN FUNCION PARA OBTENER LOS PROFESIONALES

//INICIO ENVIAR FACTURA POR CORREO ELECTRONICO
function mailBill(facturas_id){
	swal({
	  title: "¿Estas seguro?",
	  text: "¿Desea enviar este numero de factura: # " + getNumeroFactura(facturas_id) + "?",
	  type: "info",
	  showCancelButton: true,
	  confirmButtonClass: "btn-primary",
	  confirmButtonText: "¡Sí, enviar la factura!",
	  cancelButtonText: "Cancelar",
	  closeOnConfirm: false
	},
	function(){
		sendMail(facturas_id);
	});
}

function mailBillGroup(facturas_id){
	swal({
	  title: "¿Estas seguro?",
	  text: "¿Desea enviar este numero de factura: # " + getNumeroFacturaGroup(facturas_id) + "?",
	  type: "info",
	  showCancelButton: true,
	  confirmButtonClass: "btn-primary",
	  confirmButtonText: "¡Sí, enviar la factura!",
	  cancelButtonText: "Cancelar",
	  closeOnConfirm: false
	},
	function(){
		sendMailGroup(facturas_id);
	});
}
//FIN ENVIAR FACTURA POR CORREO ELECTRONICO

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
//FIN ENVIAR FACTURA POR CORREO ELECTRONICO
//FIN FUNCIONES

/*
###########################################################################################################################################################
###########################################################################################################################################################
###########################################################################################################################################################
*/
/*															INICIO FACTURACIÓN				   															 */
//INICIOS FORMULARIOS
$('#acciones_atras').on('click', function(e){
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
			$('#grupo_facturacion').hide();
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
		 $('#grupo_facturacion').hide();
		 $('#acciones_atras').addClass("breadcrumb-item active");
		 $('#acciones_factura').removeClass("active");
		 $('.footer').show();
    	 $('.footer1').hide();
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
	 $('#formulario_facturacion #fact_eval').val(1);//ESTO VIENE DE UNA FACTURA
	 $('#formulario_facturacion #fecha').attr('disabled', false);
	 $('#formulario_facturacion').attr({ 'data-form': 'save' });
	 $('#formulario_facturacion').attr({ 'action': '<?php echo SERVERURL; ?>php/facturacion/addFactura.php' });
	 limpiarTabla();
	 $('#formulario_facturacion #addRows').show();
	 $('#formulario_facturacion #removeRows').show();
	 $('#formulario_facturacion #buscar_paciente').show();
	 $('#formulario_facturacion #buscar_colaboradores').show();
	 $('#formulario_facturacion #buscar_servicios').show();
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

	//ENVIAMOS EL TAMAÑO AL FORMULARIO DE FACTURAS, QUE POSTERIOR MENTE SE USARA PARA SABER EL TAMAÑO DE LA TABLA Y PODER ITERAR LOS DETALLES DE ESTA
	$('#formGrupoFacturacion #tamano').val(tamaño);
	netoGrupo = (subTotal + ISVGrupo) - descuentoGrupo;

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
	$("#formGrupoFacturacion #invoiceItemGrupo > tbody").empty();//limpia solo los registros del body
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
//FIN BUSQUEDA PACIENTES

//INICIO BUSQUEDA COLABORADORES
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
		var data = table.row( $(this).parents("tr") ).data();
		$('#formulario_facturacion #colaborador_id').val(data.colaborador_id);
		$('#formulario_facturacion #colaborador_nombre').val(data.colaborador);
		$('#modal_busqueda_colaboradores').modal('hide');
	});
}
//FIN BUSQUEDA COLABORADORES

//INCIO ELIMINAR FACTURA BORRADOR
function deleteBill(facturas_id){
	if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
		swal({
		  title: "¿Estas seguro?",
		  text: "¿Desea eliminar la factura para el paciente: " + getNumeroNombrePaciente(facturas_id) + "?",
		  type: "info",
		  showCancelButton: true,
		  confirmButtonClass: "btn-primary",
		  confirmButtonText: "¡Sí, Eliminar la!",
		  cancelButtonText: "Cancelar",
		  closeOnConfirm: false
		},
		function(){
			eliminarFacturaBorrador(facturas_id);
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
					type: "success",
					timer: 3000,
				});
				pagination(1);
			   return false;
			}else if(registro == 2){
				swal({
					title: "Error al eliminar el registro, por favor intentelo de nuevo o verifique que no tenga información almacenada",
					text: "No tiene permisos para ejecutar esta acción",
					type: "error",
					confirmButtonClass: 'btn-danger'
				});
			    return false;
			}else{
				swal({
					title: "No se puede procesar su solicitud, por favor intentelo de nuevo mas tarde",
					text: "No tiene permisos para ejecutar esta acción",
					type: "error",
					confirmButtonClass: 'btn-danger'
				});
			    return false;
			}
  		}
	});
	return false;
}
//FIN ELIMINAR FACTURA BORRADOR

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
			id = id.replace("importeFacturaGrupo_",'');
			var importe_factura = parseFloat($('#importeFacturaGrupo_'+id).attr('data-value'));
			total_factura += importe_factura;
		});

	   	$("[id^='codigoFacturaGrupo_']").each(function() {
			var id = $(this).attr('id');
			id = id.replace("codigoFacturaGrupo_",'');
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

function getTipoPacienteGrupo(){
    var url = '<?php echo SERVERURL; ?>php/facturacion/getTipoPaciente.php';

	$.ajax({
        type: "POST",
        url: url,
        success: function(data){
		    $('#form_main_facturas #tipo_paciente_grupo').html("");
			  $('#form_main_facturas #tipo_paciente_grupo').html(data);
			  $('#form_main_facturas #tipo_paciente_grupo').selectpicker('refresh');
			getPacienteGrupo(1);
		}
     });
}

function getPacienteGrupo(tipo_paciente){
    var url = '<?php echo SERVERURL; ?>php/facturacion/getPacienteGrupo.php';

	$.ajax({
		type: "POST",
		url: url,
		data:'tipo_paciente='+tipo_paciente,
		success: function(data){
			$('#form_main_facturas #pacientesIDGrupo').html("");
			$('#form_main_facturas #pacientesIDGrupo').html(data);
			$('#form_main_facturas #pacientesIDGrupo').selectpicker('refresh');
		}
     });
}

$('#form_main_facturas #tipo_paciente_grupo').on('change',function(){
	getPacienteGrupo($('#form_main_facturas #tipo_paciente_grupo').val());
});

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

$('#form_main_facturas #buscar_cliente_muestras').on('click', function(e){
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
		var data = table.row( $(this).parents("tr") ).data();
		$('#form_main_facturas #pacientesIDGrupo').val(data.pacientes_id);
		//pagination(1);
		$('#modal_busqueda_pacientes').modal('hide');
	});
}
/*														 	FIN FACTURACIÓN				   															 	*/
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

//INICIO FORMULARIO PAGO FACTURAS
function pay(facturas_id){
	if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
		$('#formulario_facturacion')[0].reset();
		$("#formulario_facturacion #invoiceItem > tbody").empty();//limpia solo los registros del body

		var url = '<?php echo SERVERURL; ?>php/facturacion/editarFactura.php';
			$.ajax({
			type:'POST',
			url:url,
			data:'facturas_id='+facturas_id,
			success: function(valores){
				var datos = eval(valores);
				$('#formulario_facturacion #fact_eval').val(1);//ESTO VIENE DE UNA FACTURA
				$('#formulario_facturacion #facturas_id').val(facturas_id);
				$('#formulario_facturacion #pacientes_id').val(datos[0]);
				$('#formulario_facturacion #cliente_nombre').val(datos[1]);
				$('#formulario_facturacion #colaborador_id').val(datos[3]);
				$('#formulario_facturacion #colaborador_nombre').val(datos[4]);
				$('#formulario_facturacion #servicio_id').val(datos[5]);
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
					$('#formulario_facturacion #invoiceItem #isv_'+ fila).val(data.producto_isv);

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
			type: "error",
			confirmButtonClass: 'btn-danger'
		});
	}

	//MOSTRAMOS EL FORMULARIO PARA EL METODO DE PAGO
}
//FIN FORMULARIO PAGO FACTURAS

//INICIO FORMULARIO DE BUSQUEDA CLIENTES
$('#formulario_facturacion #buscar_paciente').on('click', function(e){
	e.preventDefault();
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
	$('#buscar').focus();

	view_pacientes_busqueda_dataTable("#dataTablePacientes_main_muestras tbody", table_pacientes_buscar);
}

var view_pacientes_busqueda_dataTable = function(tbody, table){
	$(tbody).off("click", "button.view");
	$(tbody).on("click", "button.view", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();
		$('#form_main_facturas #pacientesIDGrupo').val(data.pacientes_id);
		//pagination(1);
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
				$("#formGrupoFacturacion #validar").attr("disabled", false);
			}else if(valores[0] >=0 && valores[0] <= 9){
				mensaje = "Total Facturas disponibles: " + valores[0];
				$("#mensajeFacturas").html(mensaje).addClass("alert alert-danger");
				$("#mensajeFacturas").html(mensaje).removeClass("alert alert-warning");
				$("#mensajeFacturas").attr("disabled", true);
				$("#formulario_facturacion #validar").attr("disabled", false);
				$("#formulario_facturacion #cobrar").attr("disabled", false);
				$("#formGrupoFacturacion #validar").attr("disabled", false);
			}
			else{
				mensaje = "";

				$("#formulario_facturacion #validar").attr("disabled", false);
				$("#formulario_facturacion #cobrar").attr("disabled", false);
				$("#formGrupoFacturacion #validar").attr("disabled", false);
				$("#mensajeFacturas").html(mensaje).addClass("alert alert-danger");
				$("#mensajeFacturas").html(mensaje).removeClass("alert alert-warning");
			}

			if(valores[0] ==0){
				mensaje = "Total Facturas disponibles: " + valores[0];
				mensaje += "<br/>Solo esta factura puede realizar";
				$("#mensajeFacturas").html(mensaje).addClass("alert alert-danger");
				$("#mensajeFacturas").html(mensaje).removeClass("alert alert-warning");
				$("#mensajeFacturas").attr("disabled", true);
				$("#formulario_facturacion #validar").attr("disabled", false);
				$("#formulario_facturacion #cobrar").attr("disabled", false);
				$("#formGrupoFacturacion #validar").attr("disabled", false);
			}

			if(valores[0] < 0){
				mensaje = "No puede seguir facturando";

				$("#formulario_facturacion #cobrar").attr("disabled", true);
				$("#formulario_facturacion #validar").attr("disabled", true);
				$("#formGrupoFacturacion #validar").attr("disabled", true);
				$("#mensajeFacturas").html(mensaje).addClass("alert alert-danger");
				$("#mensajeFacturas").html(mensaje).removeClass("alert alert-warning");
			}

			if(valores[1] == 1){
				mensaje += "<br/>Su fecha límite es: " + valores[2];
				$("#formulario_facturacion #validar").attr("disabled", false);
				$("#formulario_facturacion #cobrar").attr("disabled", false);
				$("#formGrupoFacturacion #validar").attr("disabled", false);
				$("#mensajeFacturas").html(mensaje).addClass("alert alert-warning");
				$("#mensajeFacturas").html(mensaje).removeClass("alert alert-danger");
			}

			if(valores[1] == 0){
				mensaje += "<br/>Su fecha limite de facturación es hoy";
				$("#mensajeFacturas").html(mensaje).addClass("alert alert-danger");
				$("#mensajeFacturas").html(mensaje).removeClass("alert alert-warning");
			}

			if(valores[1] < 0){
				mensaje += "<br/>Ya alcanzo su fecha límite";
				$("#formulario_facturacion #validar").attr("disabled", true);
				$("#formulario_facturacion #cobrar").attr("disabled", true);
				$("#formGrupoFacturacion #validar").attr("disabled", true);
				$("#mensajeFacturas").html(mensaje).addClass("alert alert-danger");
				$("#mensajeFacturas").html(mensaje).removeClass("alert alert-warning");
			}
	   }
	});
}

setInterval('getTotalFacturasDisponibles()',1000);
</script>
