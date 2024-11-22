<script>
function reportePDF(agenda_id){
	if (getUsuarioSistema() == 1 || getUsuarioSistema() == 3 || getUsuarioSistema() == 4 || getUsuarioSistema() == 5 || getUsuarioSistema() == 8 || getUsuarioSistema() == 9){
	    window.open('<?php echo SERVERURL; ?>php/citas/tickets.php?agenda_id='+agenda_id);
	}else{
		swal({
			title: "Acceso Denegado",
			text: "No tiene permisos para ejecutar esta acción",
			type: "error",
			confirmButtonClass: 'btn-danger'
		});
        return false;
    }
}

function sendEmailReprogramación(agenda_id){
    var url = '<?php echo SERVERURL; ?>php/mail/correo_reprogramaciones.php';
	$.ajax({
	    type:'POST',
		url:url,
		data:'agenda_id='+agenda_id,
		success: function(valores){

		}
	});
}

function getUsuarioSistema(){
    var url = '<?php echo SERVERURL; ?>php/sesion/sistema_tipo_usuario.php';
	var usuario;
	$.ajax({
	    type:'POST',
		url:url,
		async: false,
		success:function(data){
          usuario = data;
		}
	});
	return usuario;
}

$(function () {
  $('[data-toggle="tooltip"]').tooltip({
	  trigger: "hover"
  })
});

function getMonth(){
	const hoy = new Date()
	return hoy.toLocaleString('default', { month: 'long' });
}
/*
###########################################################################################################################################################
###########################################################################################################################################################
###########################################################################################################################################################
*/
/*															INICIO FACTURACIÓN				   															 */
//INICIO BUSQUEDA PACIENTES
//FIN BUSQUEDA PACIENTES

//INICIO BUSQUEDA SERVICIOS
$('#formulario_facturacion #buscar_servicios').on('click', function(e){
	e.preventDefault();
	listar_servicios_factura_buscar();
	$('#modal_busqueda_servicios').modal({
		show:true,
		keyboard: false,
		backdrop:'static'
	});
});
//FIN BUSQUEDA SERVICIOS

//INICIO BUSQUEDA PRODUCTOS FACTURA
$(document).ready(function(){
    $("#formulario_facturacion #invoiceItem").on('click', '.buscar_producto', function() {
		  listar_productos_facturas_buscar();
		  var row_index = $(this).closest("tr").index();
		  var col_index = $(this).closest("td").index();

		  $('#formulario_busqueda_productos_facturas #row').val(row_index);
		  $('#formulario_busqueda_productos_facturas #col').val(col_index);
		  $('#modal_busqueda_productos_facturas').modal({
			show:true,
			keyboard: false,
			backdrop:'static'
		  });
	});
});
//FIN BUSQUEDA PRODUCTOS FACTURA

//EVALUAMOS LA CANTIDAD PARA REALIZAR EL CALCULO
$(document).ready(function(){
    $("#formulario_facturacion #invoiceItem").on('blur', '.buscar_cantidad', function() {
		var row_index = $(this).closest("tr").index();
		var col_index = $(this).closest("td").index();

		var impuesto_venta = parseFloat($('#formulario_facturacion #invoiceItem #isv_'+ row_index).val());
		var cantidad = parseFloat($('#formulario_facturacion #invoiceItem #quantity_'+ row_index).val());
		var precio = parseFloat($('#formulario_facturacion #invoiceItem #price_'+ row_index).val());
		var total = parseFloat($('#formulario_facturacion #invoiceItem #total_'+ row_index).val());

		var isv = 0;
		var isv_total = 0;
		var porcentaje_isv = 0;
		var porcentaje_calculo = 0;
		var isv_neto = 0;

		if(impuesto_venta == 1){
			porcentaje_isv = parseFloat(getPorcentajeISV() / 100);
			if(total == "" || total == 0){
				porcentaje_calculo = (parseFloat(precio) * parseFloat(cantidad) * porcentaje_isv).toFixed(2);
				isv_neto = parseFloat(porcentaje_calculo).toFixed(2);
				$('#formulario_facturacion #invoiceItem #valor_isv_'+ row_index).val(porcentaje_calculo);
			}else{
				isv_total = parseFloat($('#formulario_facturacion #taxAmount').val());
				porcentaje_calculo = (parseFloat(precio) * parseFloat(cantidad) * porcentaje_isv).toFixed(2);
				isv_neto = parseFloat(isv_total) + parseFloat(porcentaje_calculo);
				$('#formulario_facturacion #invoiceItem #valor_isv_'+ row_index).val(porcentaje_calculo);
			}
		}

		calculateTotal();
	});
});

$(document).ready(function(){
    $("#formulario_facturacion #invoiceItem").on('keyup', '.buscar_cantidad', function() {
		var row_index = $(this).closest("tr").index();
		var col_index = $(this).closest("td").index();

		var impuesto_venta = parseFloat($('#formulario_facturacion #invoiceItem #isv_'+ row_index).val());
		var cantidad = parseFloat($('#formulario_facturacion #invoiceItem #quantity_'+ row_index).val());
		var precio = parseFloat($('#formulario_facturacion #invoiceItem #price_'+ row_index).val());
		var total = parseFloat($('#formulario_facturacion #invoiceItem #total_'+ row_index).val());

		var isv = 0;
		var isv_total = 0;
		var porcentaje_isv = 0;
		var porcentaje_calculo = 0;
		var isv_neto = 0;

		if(impuesto_venta == 1){
			porcentaje_isv = parseFloat(getPorcentajeISV() / 100);
			if(total == "" || total == 0){
				porcentaje_calculo = (parseFloat(precio) * parseFloat(cantidad) * porcentaje_isv).toFixed(2);
				isv_neto = parseFloat(porcentaje_calculo).toFixed(2);
				$('#formulario_facturacion #invoiceItem #valor_isv_'+ row_index).val(porcentaje_calculo);
			}else{
				isv_total = parseFloat($('#formulario_facturacion #taxAmount').val());
				porcentaje_calculo = (parseFloat(precio) * parseFloat(cantidad) * porcentaje_isv).toFixed(2);
				isv_neto = parseFloat(isv_total) + parseFloat(porcentaje_calculo);
				$('#formulario_facturacion #invoiceItem #valor_isv_'+ row_index).val(porcentaje_calculo);
			}
		}

		calculateTotal();
	});
});
//FIN FORMULARIOS

//INICIO FUNCIONES PARA LLENAR DATOS EN LA TABLA
var listar_servicios_factura_buscar = function(){
	var table_servicios_factura_buscar = $("#dataTableServicios").DataTable({
		"destroy":true,
		"ajax":{
			"method":"POST",
			"url":"<?php echo SERVERURL; ?>php/facturacion/getServiciosTabla.php"
		},
		"columns":[
			{"defaultContent":"<button class='view btn btn-primary'><span class='fas fa-copy'></span></button>"},
			{"data":"nombre"},
		],
		"pageLength" : 5,
        "lengthMenu": lengthMenu,
		"stateSave": true,
		"bDestroy": true,
		"language": idioma_español,
	});
	table_servicios_factura_buscar.search('').draw();
	$('#buscar').focus();

	view_servicios_busqueda_dataTable("#dataTableServicios tbody", table_servicios_factura_buscar);
}

var view_servicios_busqueda_dataTable = function(tbody, table){
	$(tbody).off("click", "button.view");
	$(tbody).on("click", "button.view", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();
		$('#formulario_facturacion #servicio_id').val(data.servicio_id);
		$('#modal_busqueda_servicios').modal('hide');
	});
}

var listar_productos_facturas_buscar = function(){
	var table_productos_buscar = $("#dataTableProductosFacturas").DataTable({
		"destroy":true,
		"ajax":{
			"method":"POST",
			"url":"<?php echo SERVERURL; ?>php/facturacion/getProductosFacturaTabla.php"
		},
		"columns":[
			{"defaultContent":"<button class='editar btn btn-primary'><span class='fas fa-copy'></span></button>"},
			{"data":"producto"},
			{"data":"descripcion"},
			{"data":"concentracion"},
			{"data":"medida"},
			{"data":"cantidad"},
			{"data":"precio_venta"}	,
			{"data":"precio_venta2"},
			{"data":"precio_venta3"},
			{"data":"precio_venta4"}
		],
		"pageLength" : 5,
        "lengthMenu": lengthMenu,
		"stateSave": true,
		"bDestroy": true,
		"language": idioma_español,
	});
	table_productos_buscar.search('').draw();
	$('#buscar').focus();

	editar_productos_busqueda_dataTable("#dataTableProductosFacturas tbody", table_productos_buscar);
}

var editar_productos_busqueda_dataTable = function(tbody, table){
	$(tbody).off("click", "button.editar");
	$(tbody).on("click", "button.editar", function(e){
		e.preventDefault();
		if($("#formulario_facturacion #cliente_nombre").val() != ""){
			var isv = 0;
			var isv_total = 0;
			var porcentaje_isv = 0;
			var porcentaje_calculo = 0;
			var isv_neto = 0;
			var data = table.row( $(this).parents("tr") ).data();
			var row = $('#formulario_busqueda_productos_facturas #row').val();
		    var hospitales_id = getHospitalClinicaConsulta($("#formulario_facturacion #muestras_id").val());
			var consultaPrecio = getPrecioHospitalConsulta(hospitales_id);

			if (data.categoria == "Servicio"){
				$('#formulario_facturacion #invoiceItem #productName_'+ row).val(data.producto);
			}else{
				$('#formulario_facturacion #invoiceItem #productName_'+ row).val(data.producto + ' ' + data.concentracion + ' ' + data.medida);
			}

			$('#formulario_facturacion #invoiceItem #productoID_'+ row).val(data.productos_id);

			if(consultaPrecio == "Precio1"){
				$('#formulario_facturacion #invoiceItem #price_'+ row).val(data.precio_venta);
			}else if(consultaPrecio == "Precio2"){
				$('#formulario_facturacion #invoiceItem #price_'+ row).val(data.precio_venta2);
			}else if(consultaPrecio == "Precio3"){
				$('#formulario_facturacion #invoiceItem #price_'+ row).val(data.precio_venta3);
			}else if(consultaPrecio == "Precio4"){
				$('#formulario_facturacion #invoiceItem #price_'+ row).val(data.precio_venta4);
			}else{
				$('#formulario_facturacion #invoiceItem #price_'+ row).val(data.precio_venta);
			}

			$('#formulario_facturacion #invoiceItem #isv_'+ row).val(data.impuesto_venta);
			$('#formulario_facturacion #invoiceItem #discount_'+ row).val(0);
			$('#formulario_facturacion #invoiceItem #quantity_'+ row).val(1);
			$('#formulario_facturacion #invoiceItem #quantity_'+ row).focus();

			if(data.impuesto_venta == 1){
				porcentaje_isv = parseFloat(getPorcentajeISV() / 100);
				if($('#formulario_facturacion #taxAmount').val() == "" || $('#formulario_facturacion #taxAmount').val() == 0){
					porcentaje_calculo = (parseFloat(data.precio_venta) * porcentaje_isv).toFixed(2);
					isv_neto = porcentaje_calculo;
					$('#formulario_facturacion #taxAmount').val(porcentaje_calculo);
					$('#formulario_facturacion #invoiceItem #valor_isv_'+ row).val(porcentaje_calculo);
				}else{
					isv_total = parseFloat($('#formulario_facturacion #taxAmount').val());
					porcentaje_calculo = (parseFloat(data.precio_venta) * porcentaje_isv).toFixed(2);
					isv_neto = parseFloat(isv_total) + parseFloat(porcentaje_calculo);
					$('#formulario_facturacion #taxAmount').val(isv_neto);
					$('#formulario_facturacion #invoiceItem #valor_isv_'+ row).val(porcentaje_calculo);
				}
			}

			//CONSULTAMOS SI EL USUARIO ES DE LA TERCERA EDAD PENDIENTE

			calculateTotal();
			addRow();
			$('#modal_busqueda_productos_facturas').modal('hide');
		}else{
			swal({
				title: "Error",
				text: "Lo sentimos no se puede seleccionar un producto, por favor seleccione un cliente antes de poder continuar",
				type: "error",
				confirmButtonClass: "btn-danger"
			});
		}
	});
}
//FIN FUNCIONES PARA LLENAR DATOS EN LA TABLA

function getServicio(){
    var url = '<?php echo SERVERURL; ?>php/agenda_pacientes/servicios.php';

	$.ajax({
        type: "POST",
        url: url,
	    async: true,
        success: function(data){
		    $('#formulario_facturacion #servicio_id').html("");
			$('#formulario_facturacion #servicio_id').html(data);

		    $('#formGrupoFacturacion #servicio_idGrupo').html("");
			$('#formGrupoFacturacion #servicio_idGrupo').html(data);
		}
     });
}

$(document).ready(function(){
    $("#modal_busqueda_pacientes").on('shown.bs.modal', function(){
        $(this).find('#formulario_busqueda_pacientes #buscar').focus();
    });
});

$(document).ready(function(){
    $("#modal_busqueda_colaboradores").on('shown.bs.modal', function(){
        $(this).find('#formulario_busqueda_coloboradores #buscar').focus();
    });
});

$(document).ready(function(){
    $("#modal_busqueda_productos_facturas").on('shown.bs.modal', function(){
        $(this).find('#formulario_busqueda_productos_facturas #buscar').focus();
    });
});

$(document).ready(function(){
    $("#modal_busqueda_servicios").on('shown.bs.modal', function(){
        $(this).find('#formulario_busqueda_servicios #buscar').focus();
    });
});

/*INICIO AUTO COMPLETAR*/
/*INICIO SUGGESTION PRODUCTO*/
$("#formulario_facturacion #invoiceItem").on('click', '.producto', function() {
	var row = $(this).closest("tr").index();
	var col = $(this).closest("td").index();

    $('#formulario_facturacion #productName_'+ row).on('keyup', function() {
	   if($("#formulario_facturacion #cliente_nombre").val() != ""){
		   if($('#formulario_facturacion #invoiceItem #productName_'+ row).val() != ""){
				 var key = $(this).val();
				 var dataString = 'key='+key;
				 var url = '<?php echo SERVERURL; ?>php/productos/autocompletarProductos.php';

				$.ajax({
				   type: "POST",
				   url: url,
				   data: dataString,
				   success: function(data) {
					  //Escribimos las sugerencias que nos manda la consulta
					  $('#formulario_facturacion #invoiceItem #suggestions_producto_'+ row).fadeIn(1000).html(data);
					  //Al hacer click en algua de las sugerencias
					  $('.suggest-element').on('click', function(){
							//Obtenemos la id unica de la sugerencia pulsada
							var producto_id = $(this).attr('id');

							//Editamos el valor del input con data de la sugerencia pulsada
							$('#formulario_facturacion #invoiceItem #productName_'+ row).val($('#'+producto_id).attr('data'));
							$('#formulario_facturacion #invoiceItem #quantity_'+ row).val(1);
							$('#formulario_facturacion #invoiceItem #quantity_'+ row).focus();
							//Hacemos desaparecer el resto de sugerencias
							$('#formulario_facturacion #invoiceItem #suggestions_producto_'+ row).fadeOut(1000);
							addRow();

							//OBTENEMOS DATOS DEL PRODUCTO
							var url = '<?php echo SERVERURL; ?>php/productos/editarProductos.php';

							$.ajax({
								type: "POST",
								url: url,
								data: "productos_id=" + producto_id,
								async: true,
								success: function(data){
									var datos = eval(data);
									$('#formulario_facturacion #invoiceItem #productoID_'+ row).val(producto_id);
									$('#formulario_facturacion #invoiceItem #price_'+ row).val(datos[7]);

									var isv = 0;
									var isv_total = 0;
									var porcentaje_isv = 0;
									var porcentaje_calculo = 0;
									var isv_neto = 0;

									if(getISVEstadoProductos(producto_id) == 1){
										porcentaje_isv = parseFloat(getPorcentajeISV() / 100);

										if($('#formulario_facturacion #taxAmount').val() == 0){
											porcentaje_calculo = (parseFloat(datos[7]) * porcentaje_isv).toFixed(2);
											$('#formulario_facturacion #taxAmount').val(porcentaje_calculo);
										}else{
											isv_total = parseFloat($('#formulario_facturacion #taxAmount').val());
											porcentaje_calculo = (parseFloat(datos[7]) * porcentaje_isv).toFixed(2);
											isv_neto = parseFloat(isv_total) + parseFloat(porcentaje_calculo);
											$('#formulario_facturacion #taxAmount').val(isv_neto);
										}
									}

									calculateTotal();
								}
							 });

							return false;
					 });
				  }
			   });
		   }else{
			   $('#formulario_facturacion #invoiceItem #suggestions_producto_'+ row).fadeIn(1000).html("");
			   $('#formulario_facturacion #invoiceItem #suggestions_producto_'+ row).fadeOut(1000);
		   }
	   }else{
			swal({
				title: "Error",
				text: "Lo sentimos no se puede efectuar la búsqueda, por favor seleccione un cliente antes de poder continuar",
				type: "error",
				confirmButtonClass: "btn-danger"
			});
	   }
	 });

	//OCULTAR EL SUGGESTION
    $('#formulario_facturacion #invoiceItem #productName_'+ row).on('blur', function() {
	   $('#formulario_facturacion #invoiceItem #suggestions_producto_'+ row).fadeOut(1000);
    });

    $('#formulario_facturacion #invoiceItem #productName_'+ row).on('click', function() {
	   if($("#formulario_facturacion #cliente_nombre").val() != ""){
		   if($('#formulario_facturacion #invoiceItem #productName_1').val() != ""){
				 var key = $(this).val();
				 var dataString = 'key='+key;
				 var url = '<?php echo SERVERURL; ?>php/productos/autocompletarProductos.php';

				$.ajax({
				   type: "POST",
				   url: url,
				   data: dataString,
				   success: function(data) {
					  //Escribimos las sugerencias que nos manda la consulta
					  $('#formulario_facturacion #invoiceItem #suggestions_producto_'+ row).fadeIn(1000).html(data);
					  //Al hacer click en algua de las sugerencias
					  $('.suggest-element').on('click', function(){
							//Obtenemos la id unica de la sugerencia pulsada
							var producto_id = $(this).attr('id');

							//Editamos el valor del input con data de la sugerencia pulsada
							$('#formulario_facturacion #invoiceItem #productName_'+ row).val($('#'+producto_id).attr('data'));
							$('#formulario_facturacion #invoiceItem #quantity_'+ row).val(1);
							$('#formulario_facturacion #invoiceItem #quantity_'+ row).focus();
							//Hacemos desaparecer el resto de sugerencias
							$('#formulario_facturacion #invoiceItem #suggestions_producto_'+ row).fadeOut(1000);
							addRow();

							//OBTENEMOS DATOS DEL PRODUCTO
							var url = '<?php echo SERVERURL; ?>php/productos/editarProductos.php';

							$.ajax({
								type: "POST",
								url: url,
								data: "productos_id=" + producto_id,
								async: true,
								success: function(data){
									var datos = eval(data);
									$('#formulario_facturacion #invoiceItem #productoID_'+ row).val(producto_id);
									$('#formulario_facturacion #invoiceItem #price_'+ row).val(datos[7]);

									var isv = 0;
									var isv_total = 0;
									var porcentaje_isv = 0;
									var porcentaje_calculo = 0;
									var isv_neto = 0;

									if(getISVEstadoProductos(producto_id) == 1){
										porcentaje_isv = parseFloat(getPorcentajeISV() / 100);

										if($('#formulario_facturacion #taxAmount').val() == 0){
											porcentaje_calculo = (parseFloat(datos[7]) * porcentaje_isv).toFixed(2);
											$('#formulario_facturacion #taxAmount').val(porcentaje_calculo);
										}else{
											isv_total = parseFloat($('#formulario_facturacion #taxAmount').val());
											porcentaje_calculo = (parseFloat(datos[7]) * porcentaje_isv).toFixed(2);
											isv_neto = parseFloat(isv_total) + parseFloat(porcentaje_calculo);
											$('#formulario_facturacion #taxAmount').val(isv_neto);
										}
									}

									calculateTotal();
								}
							 });

							return false;
					 });
				  }
			   });
		   }else{
			   $('#formulario_facturacion #invoiceItem #suggestions_producto_'+ row).fadeIn(1000).html("");
			   $('#formulario_facturacion #invoiceItem #suggestions_producto_'+ row).fadeOut(1000);
		   }
	   }else{
			swal({
				title: "Error",
				text: "Lo sentimos no se puede efectuar la búsqueda, por favor seleccione un cliente antes de poder continuar",
				type: "error",
				confirmButtonClass: "btn-danger"
			});
	   }
	});
});
/*FIN SUGGESTION PRODUCTO*/
/*FIN AUTO COMPLETAR*/

//INICIO BOTOES RECETA MEDICA
$('#formulario_facturacion #bt_add').on('click', function(e){
	e.preventDefault();
});

$('#formulario_facturacion #bt_del').on('click', function(e){
	e.preventDefault();
});
//FIN BOTONES RECETA MEDICA
/*														 	FIN FACTURACIÓN				   															 	*/
/*
###########################################################################################################################################################
###########################################################################################################################################################
###########################################################################################################################################################
*/

//REFRESCAR LA SESION CADA CIERTO TIEMPO PARA QUE NO EXPIRE
/*document.addEventListener("DOMContentLoaded", function(){
    // Invocamos cada 5 segundos ;)
    const milisegundos = 5 * 1000;
    setInterval(function(){
        // No esperamos la respuesta de la petición porque no nos importa
        fetch("<?php echo SERVERURL; ?>php/signin_out/refrescar.php")
          .then(response => {
            if (!response.ok) {
              throw new Error('Network response was not ok');
            }
            return response.json();
          })
          .then(data => {
            console.log(data);
          })
          .catch(error => {
            console.error('There was a problem with the fetch operation:', error);
          });
    }, milisegundos);
});*/

function getPorcentajeISV(){
    var url = '<?php echo SERVERURL; ?>php/productos/getIsv.php';
	var isv;
	$.ajax({
	    type:'POST',
		url:url,
		async: false,
		success:function(data){
		  var datos = eval(data);
          isv = datos[0];
		}
	});
	return isv;
}

function getISVEstadoProductos(productos_id){
    var url = '<?php echo SERVERURL; ?>php/productos/getIsvEstado.php';
	var isv_estado;
	$.ajax({
	    type:'POST',
		url:url,
		data:'productos_id='+productos_id,
		async: false,
		success:function(data){
		  var datos = eval(data);
          isv_estado = datos[0];
		}
	});
	return isv_estado;
}

$('#formulario_facturacion #notes').keyup(function() {
	    var max_chars = 250;
        var chars = $(this).val().length;
        var diff = max_chars - chars;

		$('#formulario_facturacion #charNum_notas').html(diff + ' Caracteres');

		if(diff == 0){
			return false;
		}
});

function caracteresAntecedentes(){
	var max_chars = 250;
	var chars = $('#formulario_facturacion #notes').val().length;
	var diff = max_chars - chars;

	$('#formulario_facturacion #charNum_notas').html(diff + ' Caracteres');

	if(diff == 0){
		return false;
	}
}

function getPrecioHospital(hospitales_id){
    var url = '<?php echo SERVERURL; ?>php/administrador_precios/getAdministradorPrecios.php';
	var precio_administrador;
	$.ajax({
	    type:'POST',
		url:url,
		data:'hospitales_id='+hospitales_id,
		async: false,
		success:function(data){
		  var datos = eval(data);
          precio_administrador = datos[0];
		}
	});
	return precio_administrador;
}

function showFactura(muestras_id){
	var url = '<?php echo SERVERURL; ?>php/muestras/editarFactura.php';

	$('#main_facturacion').hide();
	$('#facturacion').show();

	$('#formulario_facturacion')[0].reset();

	$.ajax({
	    type:'POST',
		url:url,
		data:'muestras_id='+muestras_id,
		success:function(data){
		    var datos = eval(data);
	        $('#formulario_facturacion #pro').val("Registro");			
			$('#formulario_facturacion #muestras_id').val(muestras_id);
			$('#formulario_facturacion #pacientes_id').val(datos[0]);
            $('#formulario_facturacion #cliente_nombre').val(datos[1]);
            $('#formulario_facturacion #fecha').val(datos[2]);
            $('#formulario_facturacion #colaborador_id').val(datos[3]);
			$('#formulario_facturacion #colaborador_nombre').val(datos[4]);
			$('#formulario_facturacion #servicio_id').val(datos[5]);
			$('#label_acciones_volver').html("ATA");
			$('#label_acciones_receta').html("Receta");

			$('#formulario_facturacion #fecha').attr("readonly", true);
			$('#formulario_facturacion #validar').attr("disabled", false);
			$('#formulario_facturacion #addRows').attr("disabled", false);
			$('#formulario_facturacion #removeRows').attr("disabled", false);
		    $('#formulario_facturacion #validar').show();
		    $('#formulario_facturacion #editar').hide();
		    $('#formulario_facturacion #eliminar').hide();
			limpiarTabla();

			$('#formulario_facturacion').attr({ 'data-form': 'save' });
			$('#formulario_facturacion').attr({ 'action': '<?php echo SERVERURL; ?>php/atencion_pacientes/addFactura.php' });
		}
	});
}

function getHospitalClinicaConsulta(muestras_id){
    var url = '<?php echo SERVERURL; ?>php/muestras/getHospitalClinicaCodigo.php';
	var hospitales_id;
	$.ajax({
	    type:'POST',
		url:url,
		data:'muestras_id='+muestras_id,
		async: false,
		success:function(data){
			var valores = eval(data);
			hospitales_id = valores[0];
		}
	});
	return hospitales_id;
}

function getPrecioHospitalConsulta(hospitales_id){
    var url = '<?php echo SERVERURL; ?>php/muestras/getPrecioHospital.php';
	var precio;
	$.ajax({
	    type:'POST',
		url:url,
		data:'hospitales_id='+hospitales_id,
		async: false,
		success:function(data){
			var valores = eval(data);
			precio = valores[0];
		}
	});
	return precio;
}

//INICIO FORMULARIO PACIENTES
function cleanPacientes(){
	$("#formulario_pacientes #correo").css("border-color", "none");
}

function getDepartamento(){
    var url = '<?php echo SERVERURL; ?>php/pacientes/getDepartamento.php';

				$.ajax({
				type: "POST",
				url: url,
				async: true,
				success: function(data){
						$('#formulario_pacientes #departamento_id').html("");
						$('#formulario_pacientes #departamento_id').html(data);
						$('#formulario_pacientes #departamento_id').selectpicker('refresh');
				}
     });
}

$(document).ready(function() {
	$('#formulario_pacientes #departamento_id').on('change', function(){
      getMunicipio();
	  return false;
    });
});

function getMunicipio(){
	var url = '<?php echo SERVERURL; ?>php/pacientes/getMunicipio.php';

	var departamento_id = $('#formulario_pacientes #departamento_id').val();

	$.ajax({
	   type:'POST',
	   url:url,
	   data:'departamento_id='+departamento_id,
	   success:function(data){
		  $('#formulario_pacientes #municipio_id').html("");
		  $('#formulario_pacientes #municipio_id').html(data);
			$('#formulario_pacientes #municipio_id').selectpicker('refresh');
	  }
  });
}

function getMunicipioEditar(departamento_id, municipio_id){
	var url = '<?php echo SERVERURL; ?>php/pacientes/getMunicipio.php';

	$.ajax({
	   type:'POST',
	   url:url,
	   data:'departamento_id='+departamento_id,
	   success:function(data){
	      $('#formulario_pacientes #municipio_id').html("");
		    $('#formulario_pacientes #municipio_id').html(data);
				$('#formulario_pacientes #municipio_id').selectpicker('refresh');

		    $('#formulario_pacientes #municipio_id').val(municipio_id);
				$('#formulario_pacientes #municipio_id').selectpicker('refresh');
	  }
	});
	return false;
}

function getReligion(){
    var url = '<?php echo SERVERURL; ?>php/pacientes/getReligion.php';

	$.ajax({
        type: "POST",
        url: url,
	    async: true,
        success: function(data){
		    $('#formulario_pacientes #religion').html("");
		  	$('#formulario_pacientes #religion').html(data);
		}
     });
}

function getProfesion(){
    var url = '<?php echo SERVERURL; ?>php/pacientes/getProfesion.php';

	$.ajax({
        type: "POST",
        url: url,
	    async: true,
        success: function(data){
		    $('#formulario_pacientes #profesion').html("");
			$('#formulario_pacientes #profesion').html(data);
		}
     });
}

function getSexo(){
    var url = '<?php echo SERVERURL; ?>php/pacientes/getSexo.php';

	$.ajax({
				type: "POST",
				url: url,
				async: true,
				success: function(data){
						$('#formulario_pacientes #sexo').html("");
						$('#formulario_pacientes #sexo').html(data);
						$('#formulario_pacientes #sexo').selectpicker('refresh');

						$('#formulario_agregar_expediente_manual #sexo_manual').html("");
						$('#formulario_agregar_expediente_manual #sexo_manual').html(data);
						$('#formulario_agregar_expediente_manual #sexo_manual').selectpicker('refresh');
				}
     });
}

function getTipoPacienteEstado(){
    var url = '<?php echo SERVERURL; ?>php/pacientes/getTipoPaciente.php';

	$.ajax({
				type: "POST",
				url: url,
				async: true,
				success: function(data){
					$('#formulario_pacientes #paciente_tipo').html("");
					$('#formulario_pacientes #paciente_tipo').html(data);
					$('#formulario_pacientes #paciente_tipo').selectpicker('refresh');

					$('#form_main #tipo_paciente_id').html("");
					$('#form_main #tipo_paciente_id').html(data);
					$('#form_main #tipo_paciente_id').selectpicker('refresh');
				}
     });
}

function getStatus(){
    var url = '<?php echo SERVERURL; ?>php/pacientes/getStatus.php';

	$.ajax({
        type: "POST",
        url: url,
	    async: true,
        success: function(data){
		    $('#form_main #estado').html("");
			  $('#form_main #estado').html(data);
				$('#form_main #estado').selectpicker('refresh');
		}
     });
}

$('#formulario_pacientes #buscar_religion_pacientes').on('click', function(e){
	listar_religion_buscar();
	 $('#modal_busqueda_religion').modal({
		show:true,
		keyboard: false,
		backdrop:'static'
	});
});

$('#formulario_pacientes #buscar_profesion_pacientes').on('click', function(e){
	listar_profesion_buscar();
	 $('#modal_busqueda_profesion').modal({
		show:true,
		keyboard: false,
		backdrop:'static'
	});
});

$('#formulario_pacientes #buscar_departamento_pacientes').on('click', function(e){
	listar_departamentos_buscar();
	$('#modal_busqueda_departamentos').modal({
		show:true,
		keyboard: false,
		backdrop:'static'
	});
});

$('#formulario_pacientes #buscar_municipio_pacientes').on('click', function(e){
	if($('#formulario_pacientes #departamento').val() == "" || $('#formulario_pacientes #departamento').val() == null){
		swal({
			title: "Error",
			text: "Lo sentimos el departamento no debe estar vacío, antes de seleccionar esta opción por favor seleccione un departamento, por favor corregir",
			type: "error",
			confirmButtonClass: 'btn-danger'
		});
	}else{
		listar_municipios_buscar();
		 $('#modal_busqueda_municipios').modal({
			show:true,
			keyboard: false,
			backdrop:'static'
		});
	}
});

var listar_religion_buscar = function(){
	var table_religion_buscar = $("#dataTableReligion").DataTable({
		"destroy":true,
		"ajax":{
			"method":"POST",
			"url":"<?php echo SERVERURL; ?>php/pacientes/getReligionTable.php"
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
	table_religion_buscar.search('').draw();
	$('#buscar').focus();

	view_religion_busqueda_dataTable("#dataTableReligion tbody", table_religion_buscar);
}

var view_religion_busqueda_dataTable = function(tbody, table){
	$(tbody).off("click", "button.view");
	$(tbody).on("click", "button.view", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();
		$('#formulario_pacientes #religion').val(data.religion_id);
		$('#modal_busqueda_religion').modal('hide');
	});
}

var listar_profesion_buscar = function(){
	var table_profeision_buscar = $("#dataTableProfesiones").DataTable({
		"destroy":true,
		"ajax":{
			"method":"POST",
			"url":"<?php echo SERVERURL; ?>php/pacientes/getProfesionTable.php"
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
	table_profeision_buscar.search('').draw();
	$('#buscar').focus();

	view_profesion_busqueda_dataTable("#dataTableProfesiones tbody", table_profeision_buscar);
}

var view_profesion_busqueda_dataTable = function(tbody, table){
	$(tbody).off("click", "button.view");
	$(tbody).on("click", "button.view", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();
		$('#formulario_pacientes #profesion').val(data.profesion_id);
		$('#modal_busqueda_profesion').modal('hide');
	});
}

var listar_departamentos_buscar = function(){
	var table_departamentos_buscar = $("#dataTableDepartamentos").DataTable({
		"destroy":true,
		"ajax":{
			"method":"POST",
			"url":"<?php echo SERVERURL; ?>php/pacientes/getDepartamentosTabla.php"
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
	table_departamentos_buscar.search('').draw();
	$('#buscar').focus();

	view_departamentos_busqueda_dataTable("#dataTableDepartamentos tbody", table_departamentos_buscar);
}

var view_departamentos_busqueda_dataTable = function(tbody, table){
	$(tbody).off("click", "button.view");
	$(tbody).on("click", "button.view", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();
		$('#formulario_pacientes #departamento').val(data.departamento_id);
		getMunicipio();
		$('#modal_busqueda_departamentos').modal('hide');
	});
}

var listar_municipios_buscar = function(){
	var departamento = $('#formulario_pacientes #departamento').val();
	var table_municipios_buscar = $("#dataTableMunicipios").DataTable({
		"destroy":true,
		"ajax":{
			"method":"POST",
			"url":"<?php echo SERVERURL; ?>php/pacientes/getMunicipiosTabla.php",
			"data":{ 'departamento' : departamento },
		},
		"columns":[
			{"defaultContent":"<button class='view btn btn-primary'><span class='fas fa-copy'></span></button>"},
			{"data":"municipio"},
			{"data":"departamento"}
		],
		"pageLength" : 5,
        "lengthMenu": lengthMenu,
		"stateSave": true,
		"bDestroy": true,
		"language": idioma_español,
	});
	table_municipios_buscar.search('').draw();
	$('#buscar').focus();

	view_municipios_busqueda_dataTable("#dataTableMunicipios tbody", table_municipios_buscar);
}

var view_municipios_busqueda_dataTable = function(tbody, table){
	$(tbody).off("click", "button.view");
	$(tbody).on("click", "button.view", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();
		$('#formulario_pacientes #municipio').val(data.municipio_id);
		$('#modal_busqueda_municipios').modal('hide');
	});
}

$('#form_main #limpiar').on('click', function(e){
    e.preventDefault();
	$('#form_main #bs_regis').val("");
	$('#form_main #bs_regis').focus();
	getSexo();
	pagination(1);
	getStatus();
	getTipoPacienteEstado();
	getDepartamento();
	getReligion();
	getProfesion();
	listar_departamentos_buscar();
	listar_profesion_buscar();
	listar_religion_buscar();
});

$(document).ready(function(){
	getSexo();
	pagination(1);
	getStatus();
	getTipoPacienteEstado();
	getDepartamento();
	getReligion();
	getProfesion();
});
//FIN FORMULARIO PACIENTES

//INICIO FORMULARIO COLABORADORES
function puesto(){
	var url = '<?php echo SERVERURL; ?>php/selects/puestos.php';

	$.ajax({
		type:'POST',
		url:url,
		success: function(data){
			$('#formulario_colaboradores #puesto').html("");
			$('#formulario_colaboradores #puesto').html(data);
			$('#formulario_colaboradores #puesto').selectpicker('refresh');
		}
	});
	return false;
}

function empresa(){
	var url = '<?php echo SERVERURL; ?>php/selects/empresa.php';

	$.ajax({
		type:'POST',
		url:url,
		success: function(data){
			$('#formulario_colaboradores #empresa').html("");
			$('#formulario_colaboradores #empresa').html(data);
			$('#formulario_colaboradores #empresa').selectpicker('refresh');
		}
	});
	return false;
}

function getEstatus(){
    var url = '<?php echo SERVERURL; ?>php/users/getStatus.php';

	$.ajax({
        type: "POST",
        url: url,
	    async: true,
        success: function(data){
		    $('#main_form #status').html("");
			$('#main_form #status').html(data);
			$('#main_form #status').selectpicker('refresh');

		    $('#formulario_colaboradores #estatus').html("");
			$('#formulario_colaboradores #estatus').html(data);
			$('#formulario_colaboradores #estatus').selectpicker('refresh');
		}
     });
}
//FIN FORMULARIO COLABORADORES

$(document).ready(function(){
	getClientes();
	getEmpresas();
	getTotalMuestras()
	getTotalAtenciones();
	getPendientesAtencion();
	getTotalPendienteMuestras();
	getPendientesFacturas();
	getTotalProductos();

	setInterval('getClientes()',2000);
	//setInterval('getEmpresas()',2000);
	setInterval('getTotalMuestras()',2000);
	setInterval('getTotalAtenciones()',2000);
	setInterval('getPendientesAtencion()',2000);
	setInterval('getTotalPendienteMuestras()',2000);
	setInterval('getPendientesFacturas()',2000);
	setInterval('getTotalProductos()',2000);

	listar_secuencia_fiscales_dashboard();

	$(window).scrollTop(0);
});

//DATOS MAIN
function getClientes(){
    var url = '<?php echo SERVERURL; ?>php/main/getClientes.php';
	$.ajax({
	    type:'POST',
		url:url,
		success: function(data){
           	$('#main_clientes').html(data);
		}
	});
}

function getEmpresas(){
    var url = '<?php echo SERVERURL; ?>php/main/getEmpresas.php';
	$.ajax({
	    type:'POST',
		url:url,
		success: function(data){
           	$('#main_empresas').html(data);
		}
	});
}

function getTotalMuestras(){
    var url = '<?php echo SERVERURL; ?>php/main/getTotalMuestras.php';
	$.ajax({
	    type:'POST',
		url:url,
		success: function(data){
           	$('#main_muestras').html(data);
		}
	});
}

function getTotalAtenciones(){
    var url = '<?php echo SERVERURL; ?>php/main/getTotalAtenciones.php';
	$.ajax({
	    type:'POST',
		url:url,
		success: function(data){
           	$('#main_atenciones').html(data);
		}
	});
}

function getPendientesAtencion(){
    var url = '<?php echo SERVERURL; ?>php/main/pendienteAtenciones.php';
	$.ajax({
	    type:'POST',
		url:url,
		success: function(data){
           	$('#main_prendiente_atenciones').html(data);
		}
	});
}

function getTotalPendienteMuestras(){
    var url = '<?php echo SERVERURL; ?>php/main/getTotalPendienteMuestras.php';
	$.ajax({
	    type:'POST',
		url:url,
		success: function(data){
           	$('#main_pendiente_muestras').html(data);
		}
	});
}

function getPendientesFacturas(){
    var url = '<?php echo SERVERURL; ?>php/main/facturasPendientes.php';
	$.ajax({
	    type:'POST',
		url:url,
		success: function(data){
           	$('#main_facturas_pendientes').html(data);
		}
	});
}

function getTotalProductos(){
    var url = '<?php echo SERVERURL; ?>php/main/totalProductos.php';
	$.ajax({
	    type:'POST',
		url:url,
		success: function(data){
           	$('#main_productos').html(data);
		}
	});
}

function pagination(partida){

}

function convertDate(inputFormat) {
  function pad(s) { return (s < 10) ? '0' + s : s; }
  var d = new Date(inputFormat);
  return [d.getFullYear(), pad(d.getMonth()+1), pad(d.getDate())].join('-');
}

function today(){
    var hoy = new Date();
    return convertDate(hoy);
}

function getMonth(){
	const hoy = new Date()
	return hoy.toLocaleString('default', { month: 'long' });
}

function convertDateFormat(string) {
  if(string == null || string == ""){
    var hoy = new Date();
    string = convertDate(hoy);
  }
}

var listar_secuencia_fiscales_dashboard = function(){
	var table_secuencia_fiscales_dashboard  = $("#dataTableSecuenciaDashboard").DataTable({
		"destroy":true,
		"ajax":{
			"method":"POST",
			"url":"<?php echo SERVERURL; ?>php/main/llenarDataTableDocumentosFiscalesDashboard.php"
		},
		"columns":[
			{"data":"empresa"},
			{"data":"documento"},
			{"data":"inicio"},
			{"data":"fin"},
			{"data":"siguiente"},
			{"data":"fecha"}
		],
        "lengthMenu": lengthMenu,
		"stateSave": true,
		"bDestroy": true,
		"language": idioma_español,//esta se encuenta en el archivo main.js
		"dom": dom,
		"columnDefs": [
		  { width: "40.66%", targets: 0 },
		  { width: "12.66%", targets: 1 },
		  { width: "12.66%", targets: 2 },
		  { width: "12.66%", targets: 3 },
		  { width: "8.66%", targets: 4 },
		  { width: "12.66%", targets: 5 }
		],
		"buttons":[
			{
				text:      '<i class="fas fa-sync-alt fa-lg"></i> Actualizar',
				titleAttr: 'Actualizar Documentos Fiscales',
				className: 'table_actualizar btn btn-secondary ocultar',
				action: 	function(){
					listar_secuencia_fiscales_dashboard();
				}
			},
			{
				extend:    'excelHtml5',
				text:      '<i class="fas fa-file-excel fa-lg"></i> Excel',
				titleAttr: 'Excel',
				orientation: 'landscape',
				pageSize: 'LETTER',
				title: 'Reporte Documentos Fiscales',
				messageBottom: 'Fecha de Reporte: ' + convertDateFormat(today()),
				className: 'table_reportes btn btn-success ocultar',
				exportOptions: {
						columns: [0,1,2,3,4,5]
				},
			},
			{
				extend:    'pdf',
				text:      '<i class="fas fa-file-pdf fa-lg"></i> PDF',
				titleAttr: 'PDF',
				orientation: 'landscape',
				pageSize: 'LETTER',
				title: 'Reporte Documentos Fiscales',
				messageBottom: 'Fecha de Reporte: ' + convertDateFormat(today()),
				className: 'table_reportes btn btn-danger ocultar',
				exportOptions: {
						columns: [0,1,2,3,4,5]
				},
				customize: function ( doc ) {
					doc.content.splice( 1, 0, {
						margin: [ 0, 0, 0, 12 ],
						alignment: 'left',
						image: imagen,//esta se encuenta en el archivo main.js
						width:100,
                        height:45
					} );
				}
			}
		],
	});
	table_secuencia_fiscales_dashboard.search('').draw();
	$('#buscar').focus();
}

//INICIO IMPRIMIR FACTURACION
function printBill(facturas_id){
	var url = '<?php echo SERVERURL; ?>php/facturacion/generaFactura.php?facturas_id='+facturas_id;
    window.open(url);
}

function printBillGroup(facturas_id){
	var url = '<?php echo SERVERURL; ?>php/facturacion/generaFacturaGrupal.php?facturas_id='+facturas_id;
    window.open(url);
}
//FIN IMPRIMIR FACTURACION

function getNumeroFactura(facturas_id){
	var url = '<?php echo SERVERURL; ?>php/cobrarClientes/getNoFactura.php';
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

function getNombreClienteFactura(factura_id){
	var url = '<?php echo SERVERURL; ?>php/cobrarClientes/getNombreClienteFactura.php';
    var cliente = '';

    $.ajax({
       type:'POST',
       url:url,
       async: false,
       data:'factura_id='+factura_id,
       success:function(data){
            var datos = eval(data);
            cliente = datos[0];
      }
    });

	return cliente;
}

function getImporteFacturas(factura_id){
	var url = '<?php echo SERVERURL; ?>php/cobrarClientes/getImporteFacturas.php';
    var importe = '';

    $.ajax({
       type:'POST',
       url:url,
       async: false,
       data:'factura_id='+factura_id,
       success:function(data){
            var datos = eval(data);
            importe = datos[0];
      }
    });

	return importe;
}

//INICIO MODAL PAGOS GRUPAL
function pagoGrupal(facturas_grupal_id,tipoPago ){
	var url = '<?php echo SERVERURL; ?>php/facturacion/editarPagoGrupal.php';
	var saldo = 0;

	$.ajax({
		type:'POST',
		url:url,
		data:'facturas_grupal_id='+facturas_grupal_id,
		success: function(valores){
			var datos = eval(valores);
			$('#formEfectivoBillGrupal .border-right a:eq(0) a').tab('show');
			$("#customer-name-bill-grupal").html("<b>Cliente:</b> " + datos[0]);

			saldo = datos[2];
            if (tipoPago == 2) {
				saldo = datos[3];
                $('#bill-pay').html("L. " + parseFloat(saldo));
                $('#tab5').hide();
                $("#formEfectivoBillGrupal #tipo_factura").val(tipoPago);

                $('#formTarjetaBillGrupal #monto_efectivo_tarjeta').show();
                $('#formTransferenciaBillGrupal #importe_transferencia').show()
                $('#formChequeBillGrupal #importe_cheque').show()
                $("#formEfectivoBillGrupal #grupo_cambio_efectivo").hide();
            }

		    $("#customer_bill_pay_grupal-grupal").val(saldo);
			$('#bill-pay-grupal').html("L. " + parseFloat(saldo).toFixed(2));

			//EFECTIVO
			$('#formEfectivoBillGrupal')[0].reset();
			$('#formEfectivoBillGrupal #monto_efectivo').val(saldo);
			$('#formEfectivoBillGrupal #factura_id_efectivo').val(facturas_grupal_id);
			$('#formEfectivoBillGrupal #tipo_factura').val(tipoPago);
			$('#formEfectivoBillGrupal #pago_efectivo_grupal').attr('disabled', true);

			//TARJETA
			$('#formTarjetaBillGrupal')[0].reset();
			$('#formTarjetaBillGrupal #monto_efectivo').val(saldo);
			$('#formTarjetaBillGrupal #tipo_factura').val(tipoPago);
			$('#formTarjetaBillGrupal #factura_id_tarjeta').val(facturas_grupal_id);

			//MIXTO
			$('#formMixtoBillGrupal')[0].reset();
			$('#formMixtoBillGrupal #monto_efectivo_mixto').val(saldo);
			$('#formMixtoBillGrupal #factura_id_mixto').val(facturas_grupal_id);
			$('#formMixtoBillGrupal #pago_efectivo_mixto_grupal').attr('disabled', true);

			//TRANSFERENCIA
			$('#formTransferenciaBillGrupal')[0].reset();
			$('#formTransferenciaBillGrupal #monto_efectivo').val(saldo);
            $('#formTransferenciaBillGrupal #tipo_factura_transferencia').val(tipoPago);			
			$('#formTransferenciaBillGrupal #factura_id_transferencia').val(facturas_grupal_id);

			//CHEQUES
			$('#formChequeBillGrupal')[0].reset();
			$('#formChequeBillGrupal #monto_efectivo').val(saldo);
            $('#formChequeBillGrupal #pago_efectivo').attr('disabled', true);
			$('#formChequeBillGrupal #factura_id_cheque').val(facturas_grupal_id);

			$('#modal_grupo_pagos').modal({
				show:true,
				keyboard: false,
				backdrop:'static'
			});

			return false;
		}
	});
}

$(document).ready(function(){
	$("#tabGrupal1").on("click", function(){
		$("#modal_grupo_pagos").on('shown.bs.modal', function(){
           $(this).find('#formEfectivoBillGrupal #efectivo_bill').focus();
		});
	});

	$("#tabGrupal2").on("click", function(){
		$("#modal_grupo_pagos").on('shown.bs.modal', function(){
           $(this).find('#formTarjetaBillGrupal #cr_bill').focus();
		});
	});

	$("#tabGrupal3").on("click", function(){
		$("#modal_grupo_pagos").on('shown.bs.modal', function(){
           $(this).find('#formTarjetaBillGrupal #bk_nm').focus();
		});
	});

	$("#tabGrupal4").on("click", function(){
		$("#modal_grupo_pagos").on('shown.bs.modal', function(){
           $(this).find('#formChequeBillGrupal #check_num').focus();
		});
	});

	$("#tabGrupal5").on("click", function(){
		$("#modal_pagos").on('shown.bs.modal', function(){
           $(this).find('#formMixtoBillGrupal #efectivo_bill_mixto').focus();
		});
	});
});

$(document).ready(function(){
	$('#formTarjetaBillGrupal #cr_bill').inputmask("9999");
});

$(document).ready(function(){
	$('#formTarjetaBillGrupal #exp').inputmask("99/99");
});

$(document).ready(function(){
	$('#formTarjetaBillGrupal #cvcpwd').inputmask("999999");
});

// MIXTO
$(document).ready(function(){
	$('#formMixtoBill #cr_bill_mixto').inputmask("9999");
});

$(document).ready(function(){
	$('#formMixtoBill #exp_mixto').inputmask("99/99");
});

$(document).ready(function(){
	$('#formMixtoBill #cvcpwd_mixto').inputmask("999999");
});

$(document).ready(function(){
	$("#formEfectivoBillGrupal #efectivo_bill").on("keyup", function(){
        var efectivo = parseFloat($("#formEfectivoBillGrupal #efectivo_bill").val()).toFixed(2);
        var monto = parseFloat($("#formEfectivoBillGrupal #monto_efectivo").val()).toFixed(2);
        var credito = $("#formEfectivoBillGrupal #tipo_factura").val();
        var pagos_multiples = $('#pagos_multiples_switch').val();

        if (credito == 2) {
            $("#formEfectivoBillGrupal #cambio_efectivo").val(0)
            $("#formEfectivoBillGrupal #grupo_cambio_efectivo").hide();
        }

        var total = efectivo - monto;

        if (Math.floor(efectivo * 100) >= Math.floor(monto * 100) || credito == 2 || pagos_multiples == 1) {
            $('#formEfectivoBillGrupal #cambio_efectivo').val(parseFloat(total).toFixed(2));
            $('#formEfectivoBillGrupal #pago_efectivo_grupal').attr('disabled', false);
        } else {
            $('#formEfectivoBillGrupal #cambio_efectivo').val(parseFloat(0).toFixed(2));
            $('#formEfectivoBillGrupal #pago_efectivo_grupal').attr('disabled', true);
        }
        
       // Deshabilitar el botón si efectivo es mayor que monto
	   if (parseFloat(efectivo) > parseFloat(monto)) {
            $('#formEfectivoBillGrupal #pago_efectivo_grupal').attr('disabled', true);
       }
	});

	//MIXTO
	$("#formMixtoBillGrupal #efectivo_bill_mixto").on("keyup", function(){
		var efectivo = parseFloat($("#formMixtoBillGrupal #efectivo_bill_mixto").val()).toFixed(2);
		var monto = parseFloat($("#formMixtoBillGrupal #monto_efectivo_mixto").val()).toFixed(2);

		var total = efectivo - monto;

		if(Math.floor(efectivo*100) >= Math.floor(monto*100)){
			$('#formMixtoBillGrupal #pago_efectivo_mixto_grupal').attr('disabled', true);
			$('#formMixtoBillGrupal #monto_tarjeta').val(parseFloat(0).toFixed(2));
			$('#formMixtoBillGrupal #monto_tarjeta').attr('disabled', true);
		}else{
			var tarjeta = monto - efectivo;
			$('#formMixtoBillGrupal #monto_tarjeta').val(parseFloat(tarjeta).toFixed(2))
			$('#formMixtoBillGrupal #cambio_efectivo_mixto').val(parseFloat(0).toFixed(2));
			$('#formMixtoBillGrupal #pago_efectivo_mixto_grupal').attr('disabled', false);
		}
	});
});

$(document).ready(function() {
    $("#formEfectivoBillGrupal #efectivo_bill").on("keyup", function() {
        var efectivo = parseFloat($("#formEfectivoBillGrupal #efectivo_bill").val()).toFixed(2);
        var monto = parseFloat($("#formEfectivoBillGrupal #monto_efectivo").val()).toFixed(2);
        var credito = $("#formEfectivoBillGrupal #tipo_factura").val();
        var pagos_multiples = $('#pagos_multiples_switch').val();

        if (credito == 2) {
            $("#formEfectivoBillGrupal #cambio_efectivo").val(0)
            $("#formEfectivoBillGrupal #grupo_cambio_efectivo").hide();
        }

        var total = efectivo - monto;

        if (Math.floor(efectivo * 100) >= Math.floor(monto * 100) || credito == 2 || pagos_multiples == 1) {
            $('#formEfectivoBillGrupal #cambio_efectivo').val(parseFloat(total).toFixed(2));
            $('#formEfectivoBillGrupal #pago_efectivo').attr('disabled', false);
        } else {
            $('#formEfectivoBillGrupal #cambio_efectivo').val(parseFloat(0).toFixed(2));
            $('#formEfectivoBillGrupal #pago_efectivo').attr('disabled', true);
        }
        
       // Deshabilitar el botón si efectivo es mayor que monto
	   if (parseFloat(efectivo) > parseFloat(monto)) {
            $('#formEfectivoBillGrupal #pago_efectivo').attr('disabled', true);
        }
    });
});
//FIN PAGO GRUPAL

//INICIO MODAL PAGOS
function pago(facturas_id,tipoPago){
	var url = '<?php echo SERVERURL; ?>php/facturacion/editarPago.php';
	var saldo = 0;
    $('#pagos_multiples_switch').attr('checked', false);

	$.ajax({
		type:'POST',
		url:url,
		data:'facturas_id='+facturas_id,
		success: function(valores){
            var datos = eval(valores);
            $('#formEfectivoBill .border-right a:eq(0) a').tab('show');
            $("#customer-name-bill").html("<b>Cliente:</b> " + datos[0]);

			saldo = datos[2];
            if (tipoPago == 2) {
				saldo = datos[3];
                $('#bill-pay').html("L. " + parseFloat(saldo));
                $('#tab5').hide();
                $("#formEfectivoBill #tipo_factura").val(tipoPago);

                $('#formTarjetaBill #monto_efectivo_tarjeta').show();
                $('#formTransferenciaBill #importe_transferencia').show()
                $('#formChequeBill #importe_cheque').show()
                $("#formEfectivoBill #grupo_cambio_efectivo").hide();
            }

            $("#customer_bill_pay").val(saldo);
            $('#bill-pay').html("L. " + parseFloat(saldo));

            //EFECTIVO
            $('#formEfectivoBill')[0].reset();
            $('#formEfectivoBill #monto_efectivo').val(parseFloat(saldo));

            $('#formEfectivoBill #factura_id_efectivo').val(facturas_id);
            $('#formEfectivoBill #tipo_factura').val(tipoPago);
            $('#formEfectivoBill #pago_efectivo').attr('disabled', true);

            //TARJETA
            $('#formTarjetaBill')[0].reset();
            $('#formTarjetaBill #monto_efectivo').val(parseFloat(saldo));
            $('#formTarjetaBill #importe_tarjeta').val(parseFloat(saldo));
            $('#formTarjetaBill #factura_id_tarjeta').val(facturas_id);
            $('#formTarjetaBill #tipo_factura').val(tipoPago);
            $('#formTarjetaBill #pago_efectivo').attr('disabled', true);

            //TRANSFERENCIA
            $('#formTransferenciaBill')[0].reset();
            $('#formTransferenciaBill #monto_efectivo').val(parseFloat(saldo));
            $('#formTransferenciaBill #factura_id_transferencia').val(facturas_id);
            $('#formTransferenciaBill #tipo_factura_transferencia').val(tipoPago);
            $('#formTransferenciaBill #pago_efectivo').attr('disabled', true);

            //CHEQUES
            $('#formChequeBill')[0].reset();
            $('#formChequeBill #monto_efectivo').val(parseFloat(saldo));
            $('#formChequeBill #factura_id_cheque').val(facturas_id);
            $('#formChequeBill #pago_efectivo').attr('disabled', true);
            $('#formChequeBill #tipo_factura_cheque').val(tipoPago);

            $('#modal_pagos').modal({
                show: true,
                keyboard: false,
                backdrop: 'static'
            });

            return false;
		}
	});
}

$(document).ready(function() {
    $("#tab1").on("click", function() {
        $("#modal_pagos").on('shown.bs.modal', function() {
            $(this).find('#formTarjetaBill #efectivo_bill').focus();
        });
    });

    $("#tab2").on("click", function() {
        $("#modal_pagos").on('shown.bs.modal', function() {
            $(this).find('#formTarjetaBill #cr_bill').focus();
        });
    });

    $("#tab3").on("click", function() {
        $("#modal_pagos").on('shown.bs.modal', function() {
            $(this).find('#formTarjetaBill #bk_nm').focus();
        });
    });

    $("#tab4").on("click", function() {
        $("#modal_pagos").on('shown.bs.modal', function() {
            $(this).find('#formChequeBill #bk_nm_chk').focus();
        });
    });
});

$(document).ready(function() {
    $('#formTarjetaBill #cr_bill').inputmask("9999");
});

$(document).ready(function() {
    $('#formTarjetaBill #exp').inputmask("99/99");
});

$(document).ready(function() {
    $('#formTarjetaBill #cvcpwd').inputmask("999999");
});

$(document).ready(function() {
    $("#formEfectivoBill #efectivo_bill").on("keyup", function() {
        var efectivo = parseFloat($("#formEfectivoBill #efectivo_bill").val()).toFixed(2);
        var monto = parseFloat($("#formEfectivoBill #monto_efectivo").val()).toFixed(2);
        var credito = $("#formEfectivoBill #tipo_factura").val();
        var pagos_multiples = $('#pagos_multiples_switch').val();

        if (credito == 2) {
            $("#formEfectivoBill #cambio_efectivo").val(0)
            $("#formEfectivoBill #grupo_cambio_efectivo").hide();
        }

        var total = efectivo - monto;

        if (Math.floor(efectivo * 100) >= Math.floor(monto * 100) || credito == 2 || pagos_multiples == 1) {
            $('#formEfectivoBill #cambio_efectivo').val(parseFloat(total).toFixed(2));
            $('#formEfectivoBill #pago_efectivo').attr('disabled', false);
        } else {
            $('#formEfectivoBill #cambio_efectivo').val(parseFloat(0).toFixed(2));
            $('#formEfectivoBill #pago_efectivo').attr('disabled', true);
        }
        
       // Deshabilitar el botón si efectivo es mayor que monto
	   if (parseFloat(efectivo) > parseFloat(monto)) {
            $('#formEfectivoBill #pago_efectivo').attr('disabled', true);
        }
    });
});

//FIN MODAL PAGOS

//INICIO FUNCION PARA OBTENER LOS BANCOS DISPONIBLES
function getBanco(){
    var url = '<?php echo SERVERURL; ?>php/facturacion/getBanco.php';

	$.ajax({
        type: "POST",
        url: url,
	    async: true,
        success: function(data){
		    $('#formTransferenciaBill #bk_nm').html("");
			$('#formTransferenciaBill #bk_nm').html(data);
			$('#formTransferenciaBill #bk_nm').selectpicker('refresh');

		    $('#formChequeBill #bk_nm_chk').html("");
			$('#formChequeBill #bk_nm_chk').html(data);
			$('#formChequeBill #bk_nm_chk').selectpicker('refresh');

		    $('#formTransferenciaBillGrupal #bk_nm').html("");
			$('#formTransferenciaBillGrupal #bk_nm').html(data);
			$('#formTransferenciaBillGrupal #bk_nm').selectpicker('refresh');

		    $('#formChequeBillGrupal #bk_nm_chk').html("");
			$('#formChequeBillGrupal #bk_nm_chk').html(data);
			$('#formChequeBillGrupal #bk_nm_chk').selectpicker('refresh');
        }
     });
}
//FIN FUNCION PARA OBTENER LOS BANCOS DISPONIBLES

//INICIO CONTROLES MODAL PAGO
$(".menu-toggle1").on("click", function(e){
	e.preventDefault();
	$(".menu-toggle1").hide();
	$(".menu-toggle2").show();
});

$(".menu-toggle2").on("click", function(e){
	e.preventDefault();
	$(".menu-toggle2").hide();
	$(".menu-toggle1").show();
});

//Menu Toggle Script
$("#menu-toggle1").click(function(e) {
	e.preventDefault();
	$("#wrapper").toggleClass("toggled");
});

$("#menu-toggle2").click(function(e) {
	e.preventDefault();
	$("#wrapper").toggleClass("toggled");
});

$(document).ready(function(){
	$(".menu-toggle2").hide();
	$("#tab1").addClass("active1");
	$("#sidebar-wrapper").toggleClass("toggled");

	//Menu Toggle Script
	$("#menu-toggle").click(function(e) {
		e.preventDefault();
		$("#wrapper").toggleClass("toggled");
	});

	// For highlighting activated tabs
	$("#tab1").click(function () {
		$(".tabs").removeClass("active1");
		$(".tabs").addClass("bg-light");
		$("#tab1").addClass("active1");
		$("#tab1").removeClass("bg-light");
	});

	$("#tab2").click(function () {
		$(".tabs").removeClass("active1");
		$(".tabs").addClass("bg-light");
		$("#tab2").addClass("active1");
		$("#tab2").removeClass("bg-light");
	});

	$("#tab3").click(function () {
		$(".tabs").removeClass("active1");
		$(".tabs").addClass("bg-light");
		$("#tab3").addClass("active1");
		$("#tab3").removeClass("bg-light");
	});

	$("#tab4").click(function () {
		$(".tabs").removeClass("active1");
		$(".tabs").addClass("bg-light");
		$("#tab4").addClass("active1");
		$("#tab4").removeClass("bg-light");
	});

	$("#tab5").click(function () {
		$(".tabs").removeClass("active1");
		$(".tabs").addClass("bg-light");
		$("#tab5").addClass("active1");
		$("#tab5").removeClass("bg-light");
	});
})
//FIN CONTROLES MODAL PAGO

//INICIO CONTROLES MODAL PAGO GRUPAL
$(".menu-toggleGrupal1").on("click", function(e){
	e.preventDefault();
	$(".menu-toggleGrupal1").hide();
	$(".menu-toggleGrupal2").show();
});

$(".menu-toggleGrupal2").on("click", function(e){
	e.preventDefault();
	$(".menu-toggleGrupal2").hide();
	$(".menu-toggleGrupal1").show();
});

//Menu Toggle Script
$("#menu-toggleGrupal1").click(function(e) {
	e.preventDefault();
	$("#wrapper").toggleClass("toggled");
});

$("#menu-toggleGrupal2").click(function(e) {
	e.preventDefault();
	$("#wrapper").toggleClass("toggled");
});

$(document).ready(function(){
	$(".menu-toggleGrupal2").hide();
	$("#tabGrupal1").addClass("active1");
	$("#sidebar-wrapper").toggleClass("toggled");

	//Menu Toggle Script
	$("#menu-toggle").click(function(e) {
		e.preventDefault();
		$("#wrapper").toggleClass("toggled");
	});

	// For highlighting activated tabs
	$("#tabGrupal1").click(function () {
		$(".tabs").removeClass("active1");
		$(".tabs").addClass("bg-light");
		$("#tabGrupal1").addClass("active1");
		$("#tabGrupal1").removeClass("bg-light");
	});

	$("#tabGrupal2").click(function () {
		$(".tabs").removeClass("active1");
		$(".tabs").addClass("bg-light");
		$("#tabGrupal2").addClass("active1");
		$("#tabGrupal2").removeClass("bg-light");
	});

	$("#tabGrupal3").click(function () {
		$(".tabs").removeClass("active1");
		$(".tabs").addClass("bg-light");
		$("#tabGrupal3").addClass("active1");
		$("#tabGrupal3").removeClass("bg-light");
	});

	$("#tabGrupal4").click(function () {
		$(".tabs").removeClass("active1");
		$(".tabs").addClass("bg-light");
		$("#tabGrupal4").addClass("active1");
		$("#tabGrupal4").removeClass("bg-light");
	});

	$("#tabGrupal5").click(function () {
		$(".tabs").removeClass("active1");
		$(".tabs").addClass("bg-light");
		$("#tabGrupal5").addClass("active1");
		$("#tabGrupal5").removeClass("bg-light");
	});
})
//FIN CONTROLES MODAL PAGO GRUPAL

function volver() {
	$('#main_facturacion').show();
	$('#label_acciones_factura').html("");
	$('#facturacion').hide();
	$('#grupo_facturacion').hide();
	$('#acciones_atras').addClass("breadcrumb-item active");
	$('#acciones_factura').removeClass("active");
	$('.footer').show();
	$('.footer1').hide();
}

function sendMail(facturas_id) {
	var url = '<?php echo SERVERURL; ?>php/facturacion/correo_facturas.php';
	var bill = '';

	$.ajax({
		type: 'POST',
		url: url,
		async: false,
		data: 'facturas_id=' + facturas_id,
		success: function (data) {
			bill = data;
			if (bill == 1) {
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

function sendMailGroup(facturas_id) {
	var url = '<?php echo SERVERURL; ?>php/facturacion/correo_facturasGrupal.php';
	var bill = '';

	$.ajax({
		type: 'POST',
		url: url,
		async: false,
		data: 'facturas_id=' + facturas_id,
		success: function (data) {
			bill = data;
			if (bill == 1) {
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
</script>