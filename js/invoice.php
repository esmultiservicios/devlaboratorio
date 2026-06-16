<script>
//INVOICES

/****************************************************************************************************************************************************************/
/* FUNCIONES GENERALES DE FACTURA */
/****************************************************************************************************************************************************************/

function notifyFactura(tipo, titulo, mensaje) {
	if (typeof showNotify === 'function') {
		showNotify(tipo, titulo, mensaje);
		return;
	}

	if (typeof swal === 'function') {
		var icono = 'info';

		if (tipo === 'success') {
			icono = 'success';
		} else if (tipo === 'error' || tipo === 'danger') {
			icono = 'error';
		} else if (tipo === 'warning') {
			icono = 'warning';
		}

		swal({
			title: titulo,
			text: mensaje,
			icon: icono,
			dangerMode: false,
			closeOnEsc: false,
			closeOnClickOutside: false
		});

		return;
	}

	alert(titulo + "\n\n" + mensaje);
}

function convertirNumeroFactura(valor) {
	if (valor === undefined || valor === null) {
		return 0;
	}

	valor = valor.toString().trim();

	if (valor === '') {
		return 0;
	}

	valor = valor.replace(/,/g, '');

	var numero = parseFloat(valor);

	if (isNaN(numero)) {
		return 0;
	}

	return numero;
}

function redondearFactura(valor) {
	return parseFloat(convertirNumeroFactura(valor)).toFixed(2);
}

function crearFilaFactura(count) {
	var htmlRows = '';

	htmlRows += '<tr>';
	htmlRows += '<td><input class="itemRow" type="checkbox"></td>';

	htmlRows += '<td>';
	htmlRows += '<input type="hidden" name="isv[]" id="isv_' + count + '" class="form-control" placeholder="Producto ISV" autocomplete="off">';
	htmlRows += '<input type="hidden" name="valor_isv[]" id="valor_isv_' + count + '" class="form-control" placeholder="Valor ISV" autocomplete="off">';
	htmlRows += '<input type="hidden" name="facturas_detalle_id[]" id="facturas_detalle_id_' + count + '" class="form-control" placeholder="Código Detalle" autocomplete="off">';
	htmlRows += '<input type="hidden" name="productoID[]" id="productoID_' + count + '" class="form-control" placeholder="Código Producto" autocomplete="off">';
	htmlRows += '<div class="input-group">';
	htmlRows += '<input type="text" name="productName[]" id="productName_' + count + '" class="form-control producto" placeholder="Producto o Servicio" autocomplete="off">';
	htmlRows += '<div id="suggestions_producto_' + count + '" class="suggestions"></div>';
	htmlRows += '<div class="input-group-append grupo_buscar_productos" id="grupo_buscar_productos">';
	htmlRows += '<a data-toggle="modal" href="#" class="btn btn-outline-success buscar_productos" id="buscar_productos">';
	htmlRows += '<div class="sb-nav-link-icon"></div><i class="buscar_producto fas fa-search-plus fa-lg"></i>';
	htmlRows += '</a>';
	htmlRows += '</div>';
	htmlRows += '</div>';
	htmlRows += '</td>';

	htmlRows += '<td><input type="number" name="quantity[]" id="quantity_' + count + '" placeholder="Cantidad" class="buscar_cantidad form-control" autocomplete="off"></td>';
	htmlRows += '<td><input type="number" name="price[]" id="price_' + count + '" placeholder="Precio" readonly class="form-control price" autocomplete="off"></td>';

	htmlRows += '<td>';
	htmlRows += '<div class="input-group mb-3">';
	htmlRows += '<input type="number" name="discount[]" id="discount_' + count + '" class="form-control" step="0.01" placeholder="Descuento" readonly autocomplete="off">';
	htmlRows += '<div id="suggestions_descuento_' + count + '" class="suggestions"></div>';
	htmlRows += '<div class="input-group-append grupo_aplicar_descuento" id="grupo_aplicar_descuento">';
	htmlRows += '<a data-toggle="modal" href="#" class="btn btn-outline-success">';
	htmlRows += '<div class="sb-nav-link-icon"></div><i class="aplicar_descuento fas fa-plus fa-lg"></i>';
	htmlRows += '</a>';
	htmlRows += '</div>';
	htmlRows += '</div>';
	htmlRows += '</td>';

	htmlRows += '<td><input type="number" name="total[]" id="total_' + count + '" placeholder="Total" class="form-control total" readonly autocomplete="off"></td>';
	htmlRows += '</tr>';

	return htmlRows;
}

function reindexarFilasFactura() {
	$('#formulario_facturacion #invoiceItem tbody tr').each(function(index) {
		$(this).find('.itemRow').prop('checked', false);

		$(this).find("input[name='isv[]']").attr('id', 'isv_' + index);
		$(this).find("input[name='valor_isv[]']").attr('id', 'valor_isv_' + index);
		$(this).find("input[name='facturas_detalle_id[]']").attr('id', 'facturas_detalle_id_' + index);
		$(this).find("input[name='productoID[]']").attr('id', 'productoID_' + index);
		$(this).find("input[name='productName[]']").attr('id', 'productName_' + index);
		$(this).find("input[name='quantity[]']").attr('id', 'quantity_' + index);
		$(this).find("input[name='price[]']").attr('id', 'price_' + index);
		$(this).find("input[name='discount[]']").attr('id', 'discount_' + index);
		$(this).find("input[name='total[]']").attr('id', 'total_' + index);

		$(this).find('.suggestions').first().attr('id', 'suggestions_producto_' + index);
		$(this).find('.suggestions').last().attr('id', 'suggestions_descuento_' + index);
	});

	$('#checkAll').prop('checked', false);
}

function llenarTablaFactura(count) {
	$('#invoiceItem').append(crearFilaFactura(count));
	$("#formulario_facturacion .tableFixHead").scrollTop($(document).height());
	$("#formulario_facturacion #invoiceItem #productoID_" + count).focus();
}

function limpiarTabla() {
	$("#formulario_facturacion #invoiceItem > tbody").empty();
	$('#invoiceItem').append(crearFilaFactura(0));

	$('#subTotal').val('0.00');
	$('#subTotalFooter').val('0.00');
	$('#taxAmount').val('0.00');
	$('#taxAmountFooter').val('0.00');
	$('#taxDescuento').val('0.00');
	$('#taxDescuentoFooter').val('0.00');
	$('#totalAftertax').val('0.00');
	$('#totalAftertaxFooter').val('0.00');
	$('#amountDue').val('0.00');

	$("#formulario_facturacion .tableFixHead").scrollTop($(document).height());
	$("#formulario_facturacion #invoiceItem #productoID_0").focus();
}

function addRow() {
	var count = $("#formulario_facturacion #invoiceItem tbody tr").length;
	$('#invoiceItem').append(crearFilaFactura(count));
	$("#formulario_facturacion #invoiceItem #productoID_" + count).focus();
}

function deleteFacturasDetalles(facturas_detalle_id) {
	if (facturas_detalle_id === undefined || facturas_detalle_id === null || facturas_detalle_id === '') {
		return false;
	}

	var url = '<?php echo SERVERURL; ?>php/facturacion/deleteFacturasDetalles.php';

	$.ajax({
		type: 'POST',
		url: url,
		async: false,
		data: 'facturas_detalle_id=' + encodeURIComponent(facturas_detalle_id),
		success: function(data) {

		},
		error: function() {
			notifyFactura('error', 'Error', 'No se pudo eliminar el detalle de la factura.');
		}
	});
}

/****************************************************************************************************************************************************************/
/* VALIDACIÓN ANTES DE ENVIAR AL PHP */
/****************************************************************************************************************************************************************/

function obtenerTotalesFacturaDesdeDetalle() {
	var totalAmount = 0;
	var totalDiscount = 0;
	var totalISV = 0;
	var totalLineas = 0;

	$('#formulario_facturacion #invoiceItem tbody tr').each(function() {
		var productoID = $(this).find("input[name='productoID[]']").val();
		var productName = $(this).find("input[name='productName[]']").val();
		var quantity = $(this).find("input[name='quantity[]']").val();
		var price = $(this).find("input[name='price[]']").val();
		var discount = $(this).find("input[name='discount[]']").val();
		var valorISV = $(this).find("input[name='valor_isv[]']").val();

		productoID = productoID === undefined || productoID === null ? '' : productoID.toString().trim();
		productName = productName === undefined || productName === null ? '' : productName.toString().trim();
		quantity = quantity === undefined || quantity === null ? '' : quantity.toString().trim();
		price = price === undefined || price === null ? '' : price.toString().trim();

		if (productoID === '' && productName === '' && quantity === '' && price === '') {
			return true;
		}

		var cantidadNumero = convertirNumeroFactura(quantity);
		var precioNumero = convertirNumeroFactura(price);
		var descuentoNumero = convertirNumeroFactura(discount);
		var isvNumero = convertirNumeroFactura(valorISV);

		totalAmount += cantidadNumero * precioNumero;
		totalDiscount += descuentoNumero;
		totalISV += isvNumero;
		totalLineas++;
	});

	return {
		subtotal: parseFloat(totalAmount.toFixed(2)),
		descuento: parseFloat(totalDiscount.toFixed(2)),
		isv: parseFloat(totalISV.toFixed(2)),
		total: parseFloat(((totalAmount + totalISV) - totalDiscount).toFixed(2)),
		lineas: totalLineas
	};
}

function validarFacturaAntesDeEnviar() {
	var errores = [];

	var pacientes_id = $('#formulario_facturacion #pacientes_id').val();
	var cliente_nombre = $('#formulario_facturacion #cliente_nombre').val();
	var colaborador_id = $('#formulario_facturacion #colaborador_id').val();
	var colaborador_nombre = $('#formulario_facturacion #colaborador_nombre').val();
	var servicio_id = $('#formulario_facturacion #servicio_id').val();

	pacientes_id = pacientes_id === undefined || pacientes_id === null ? '' : pacientes_id.toString().trim();
	cliente_nombre = cliente_nombre === undefined || cliente_nombre === null ? '' : cliente_nombre.toString().trim();
	colaborador_id = colaborador_id === undefined || colaborador_id === null ? '' : colaborador_id.toString().trim();
	colaborador_nombre = colaborador_nombre === undefined || colaborador_nombre === null ? '' : colaborador_nombre.toString().trim();
	servicio_id = servicio_id === undefined || servicio_id === null ? '' : servicio_id.toString().trim();

	if (pacientes_id === '') {
		errores.push('Debe seleccionar la empresa/paciente.');
	}

	if (cliente_nombre === '') {
		errores.push('El nombre de la empresa/paciente no puede quedar vacío.');
	}

	if (colaborador_id === '') {
		errores.push('Debe seleccionar el profesional.');
	}

	if (colaborador_nombre === '') {
		errores.push('El nombre del profesional no puede quedar vacío.');
	}

	if (servicio_id === '') {
		errores.push('Debe seleccionar el servicio.');
	}

	var filas = $('#formulario_facturacion #invoiceItem tbody tr');

	if (filas.length <= 0) {
		errores.push('Debe agregar al menos un producto o servicio.');
	}

	var lineasValidas = 0;

	filas.each(function(index) {
		var linea = index + 1;

		var productoID = $(this).find("input[name='productoID[]']").val();
		var productName = $(this).find("input[name='productName[]']").val();
		var quantity = $(this).find("input[name='quantity[]']").val();
		var price = $(this).find("input[name='price[]']").val();
		var discount = $(this).find("input[name='discount[]']").val();

		productoID = productoID === undefined || productoID === null ? '' : productoID.toString().trim();
		productName = productName === undefined || productName === null ? '' : productName.toString().trim();
		quantity = quantity === undefined || quantity === null ? '' : quantity.toString().trim();
		price = price === undefined || price === null ? '' : price.toString().trim();
		discount = discount === undefined || discount === null || discount.toString().trim() === '' ? '0' : discount.toString().trim();

		var filaVacia = productoID === '' && productName === '' && quantity === '' && price === '';

		if (filaVacia) {
			return true;
		}

		if (productoID === '') {
			errores.push('La línea ' + linea + ' tiene producto, pero no tiene código interno. Quite esa línea y vuelva a seleccionar el producto.');
			return true;
		}

		if (productName === '') {
			errores.push('La línea ' + linea + ' no tiene nombre de producto.');
			return true;
		}

		if (quantity === '') {
			errores.push('El producto "' + productName + '" no tiene cantidad.');
			return true;
		}

		if (price === '') {
			errores.push('El producto "' + productName + '" no tiene precio.');
			return true;
		}

		var cantidadNumero = convertirNumeroFactura(quantity);
		var precioNumero = convertirNumeroFactura(price);
		var descuentoNumero = convertirNumeroFactura(discount);

		if (cantidadNumero <= 0) {
			errores.push('La cantidad del producto "' + productName + '" debe ser mayor a cero.');
			return true;
		}

		if (precioNumero < 0) {
			errores.push('El precio del producto "' + productName + '" no puede ser negativo.');
			return true;
		}

		if (descuentoNumero < 0) {
			errores.push('El descuento del producto "' + productName + '" no puede ser negativo.');
			return true;
		}

		if (descuentoNumero > (cantidadNumero * precioNumero)) {
			errores.push('El descuento del producto "' + productName + '" no puede ser mayor al subtotal.');
			return true;
		}

		lineasValidas++;
	});

	if (lineasValidas <= 0) {
		errores.push('Debe agregar al menos un producto válido al detalle de la factura.');
	}

	calculateTotal();

	var totales = obtenerTotalesFacturaDesdeDetalle();

	/*
		IMPORTANTE:
		Se permite total 0.00 cuando el descuento es del 100%.
		Solo se bloquea si el total queda negativo.
	*/
	if (totales.total < 0) {
		errores.push('El total de la factura no puede ser negativo.');
	}

	var totalPantalla = convertirNumeroFactura($('#formulario_facturacion #totalAftertax').val());
	var diferencia = Math.abs(totalPantalla - totales.total);

	if (diferencia > 0.01) {
		errores.push(
			'El total de la factura no cuadra. Total en pantalla: L. ' +
			totalPantalla.toFixed(2) +
			' | Total calculado desde el detalle: L. ' +
			totales.total.toFixed(2)
		);
	}

	if (errores.length > 0) {
		notifyFactura(
			'error',
			'No se puede emitir la factura',
			errores.join('\n')
		);

		return false;
	}

	return true;
}

/*
	IMPORTANTE:
	Este listener trabaja antes que el submit de .FormularioAjax del main.js.
	Si algo está mal, detiene el submit y notifica al usuario.
	No se toca validarFormularioAjaxEspecial para evitar recursión.
*/
document.addEventListener('submit', function(e) {
	var formulario = e.target;

	if (!formulario || formulario.id !== 'formulario_facturacion') {
		return true;
	}

	if (!validarFacturaAntesDeEnviar()) {
		e.preventDefault();
		e.stopPropagation();
		e.stopImmediatePropagation();
		return false;
	}

	return true;
}, true);

/****************************************************************************************************************************************************************/
/* FACTURACION */
/****************************************************************************************************************************************************************/

$(document).ready(function() {
	$('#formulario_facturacion #label_facturas_activo').html("Contado");

	$('#formulario_facturacion .switch').off('change.facturaTipo').on('change.facturaTipo', function() {
		if ($('input[name=facturas_activo]').is(':checked')) {
			$('#formulario_facturacion #label_facturas_activo').html("Contado");
			return true;
		} else {
			$('#formulario_facturacion #label_facturas_activo').html("Crédito");
			return false;
		}
	});

	$('#formGrupoFacturacion #label_facturas_grupal_activo').html("Contado");

	$('#formGrupoFacturacion .switch').off('change.facturaGrupoTipo').on('change.facturaGrupoTipo', function() {
		if ($('input[name=facturas_grupal_activo]').is(':checked')) {
			$('#formGrupoFacturacion #label_facturas_grupal_activo').html("Contado");
			return true;
		} else {
			$('#formGrupoFacturacion #label_facturas_grupal_activo').html("Crédito");
			return false;
		}
	});
});

$(document).ready(function() {
	$(document).off('click.facturaCheckAll', '#checkAll').on('click.facturaCheckAll', '#checkAll', function() {
		$(".itemRow").prop("checked", this.checked);
	});

	$(document).off('click.facturaItemRow', '.itemRow').on('click.facturaItemRow', '.itemRow', function() {
		if ($('.itemRow:checked').length == $('.itemRow').length) {
			$('#checkAll').prop('checked', true);
		} else {
			$('#checkAll').prop('checked', false);
		}
	});

	$(document).off('click.facturaAddRows', '#addRows').on('click.facturaAddRows', '#addRows', function(e) {
		e.preventDefault();

		if ($("#formulario_facturacion #pacientes_id").val() !== "" && $("#formulario_facturacion #cliente_nombre").val() !== "") {
			addRow();
		} else {
			notifyFactura(
				'error',
				'Error',
				'Lo sentimos, no puede agregar más filas. Debe seleccionar una empresa/paciente antes de continuar.'
			);
		}
	});

	$(document).off('click.facturaRemoveRows', '#removeRows').on('click.facturaRemoveRows', '#removeRows', function(e) {
		e.preventDefault();

		if ($('.itemRow').is(':checked')) {
			$(".itemRow:checked").each(function() {
				var facturas_detalle_id = $(this).closest("tr").find("input[name='facturas_detalle_id[]']").val();

				if (facturas_detalle_id !== undefined && facturas_detalle_id !== null && facturas_detalle_id !== '') {
					deleteFacturasDetalles(facturas_detalle_id);
				}

				$(this).closest('tr').remove();
			});

			if ($("#formulario_facturacion #invoiceItem tbody tr").length <= 0) {
				limpiarTabla();
			} else {
				reindexarFilasFactura();
			}

			$('#checkAll').prop('checked', false);
			calculateTotal();
		} else {
			notifyFactura(
				'error',
				'Error',
				'Lo sentimos, debe seleccionar una fila antes de intentar eliminarla.'
			);
		}
	});

	$(document).off('blur.facturaQuantity keyup.facturaQuantity', "[id^=quantity_]").on('blur.facturaQuantity keyup.facturaQuantity', "[id^=quantity_]", function() {
		calculateTotal();
	});

	$(document).off('blur.facturaPrice keyup.facturaPrice', "[id^=price_]").on('blur.facturaPrice keyup.facturaPrice', "[id^=price_]", function() {
		calculateTotal();
	});

	$(document).off('blur.facturaDiscount keyup.facturaDiscount', "[id^=discount_]").on('blur.facturaDiscount keyup.facturaDiscount', "[id^=discount_]", function() {
		calculateTotal();
	});

	$(document).off('blur.facturaTaxRate', "#taxRate").on('blur.facturaTaxRate', "#taxRate", function() {
		calculateTotal();
	});

	$(document).off('blur.facturaAmountPaid', "#amountPaid").on('blur.facturaAmountPaid', "#amountPaid", function() {
		var amountPaid = convertirNumeroFactura($(this).val());
		var totalAftertax = convertirNumeroFactura($('#totalAftertax').val());

		if (amountPaid > 0 && totalAftertax > 0) {
			$('#amountDue').val(parseFloat(totalAftertax - amountPaid).toFixed(2));
		} else {
			$('#amountDue').val(parseFloat(totalAftertax).toFixed(2));
		}
	});

	$(document).off('click.facturaDeleteInvoice', '.deleteInvoice').on('click.facturaDeleteInvoice', '.deleteInvoice', function() {
		var id = $(this).attr("id");

		if (confirm("Are you sure you want to remove this?")) {
			$.ajax({
				url: "action.php",
				method: "POST",
				dataType: "json",
				data: {
					id: id,
					action: 'delete_invoice'
				},
				success: function(response) {
					if (response.status == 1) {
						$('#' + id).closest("tr").remove();
						reindexarFilasFactura();
						calculateTotal();
					}
				}
			});
		} else {
			return false;
		}
	});
});

/****************************************************************************************************************************************************************/
/* CALCULO DE TOTALES */
/****************************************************************************************************************************************************************/

function calculateTotal() {
	var totalAmount = 0;
	var totalDiscount = 0;
	var totalISV = 0;

	$("#formulario_facturacion #invoiceItem tbody tr").each(function() {
		var productoID = $(this).find("input[name='productoID[]']").val();
		var productName = $(this).find("input[name='productName[]']").val();
		var quantity = $(this).find("input[name='quantity[]']").val();
		var price = $(this).find("input[name='price[]']").val();
		var discount = $(this).find("input[name='discount[]']").val();
		var isv_calculo = $(this).find("input[name='valor_isv[]']").val();
		var totalInput = $(this).find("input[name='total[]']");

		productoID = productoID === undefined || productoID === null ? '' : productoID.toString().trim();
		productName = productName === undefined || productName === null ? '' : productName.toString().trim();

		var quantityNumber = convertirNumeroFactura(quantity);
		var priceNumber = convertirNumeroFactura(price);
		var discountNumber = convertirNumeroFactura(discount);
		var isvNumber = convertirNumeroFactura(isv_calculo);

		if (productoID === '' && productName === '' && quantityNumber === 0 && priceNumber === 0) {
			totalInput.val('0.00');
			return true;
		}

		var subtotalLinea = priceNumber * quantityNumber;
		var totalLinea = subtotalLinea - discountNumber;

		if (totalLinea < 0) {
			totalLinea = 0;
		}

		totalInput.val(parseFloat(totalLinea).toFixed(2));

		totalAmount += subtotalLinea;
		totalDiscount += discountNumber;
		totalISV += isvNumber;
	});

	$('#subTotal').val(parseFloat(totalAmount).toFixed(2));
	$('#subTotalFooter').val(parseFloat(totalAmount).toFixed(2));

	$('#taxAmount').val(parseFloat(totalISV).toFixed(2));
	$('#taxAmountFooter').val(parseFloat(totalISV).toFixed(2));

	$('#taxDescuento').val(parseFloat(totalDiscount).toFixed(2));
	$('#taxDescuentoFooter').val(parseFloat(totalDiscount).toFixed(2));

	var totalFactura = (parseFloat(totalAmount) + parseFloat(totalISV)) - parseFloat(totalDiscount);

	if (totalFactura < 0) {
		totalFactura = 0;
	}

	$('#totalAftertax').val(parseFloat(totalFactura).toFixed(2));
	$('#totalAftertaxFooter').val(parseFloat(totalFactura).toFixed(2));

	var amountPaid = convertirNumeroFactura($('#amountPaid').val());

	if (amountPaid > 0) {
		$('#amountDue').val(parseFloat(totalFactura - amountPaid).toFixed(2));
	} else {
		$('#amountDue').val(parseFloat(totalFactura).toFixed(2));
	}
}

function cleanFooterValueBill() {
	$('#subTotalFooter').val("");
	$('#taxAmountFooter').val("");
	$('#totalAftertaxFooter').val("");
}

/****************************************************************************************************************************************************************/
/* DESCUENTO PRODUCTO EN FACTURACION */
/****************************************************************************************************************************************************************/

$(document).ready(function() {
	$("#formulario_facturacion #invoiceItem").off('click.facturaDescuento', '.aplicar_descuento').on('click.facturaDescuento', '.aplicar_descuento', function(e) {
		e.preventDefault();

		$('#formDescuentoFacturacion')[0].reset();

		var row_index = $(this).closest("tr").index();
		var col_index = $(this).closest("td").index();

		var pacientes_id = $('#formulario_facturacion #pacientes_id').val();
		var productoID = $("#formulario_facturacion #invoiceItem #productoID_" + row_index).val();

		if (pacientes_id !== "" && productoID !== "") {
			$('#formDescuentoFacturacion #row_index').val(row_index);
			$('#formDescuentoFacturacion #col_index').val(col_index);

			var producto = $("#formulario_facturacion #invoiceItem #productName_" + row_index).val();
			var precio = $("#formulario_facturacion #invoiceItem #price_" + row_index).val();

			$('#formDescuentoFacturacion #descuento_productos_id').val(productoID);
			$('#formDescuentoFacturacion #producto_descuento_fact').val(producto);
			$('#formDescuentoFacturacion #precio_descuento_fact').val(precio);
			$('#formDescuentoFacturacion #pro_descuento_fact').val("Aplicar Descuento");

			$('#modalDescuentoFacturacion').modal({
				show: true,
				keyboard: false,
				backdrop: 'static'
			});
		} else {
			notifyFactura(
				'error',
				'Error',
				'Debe seleccionar un paciente y seleccionar un producto antes de continuar.'
			);
		}
	});
});

$(document).ready(function() {
	$("#formDescuentoFacturacion #porcentaje_descuento_fact").off("keyup.facturaPorcentajeDescuento").on("keyup.facturaPorcentajeDescuento", function() {
		var precio = convertirNumeroFactura($('#formDescuentoFacturacion #precio_descuento_fact').val());
		var porcentaje = convertirNumeroFactura($('#formDescuentoFacturacion #porcentaje_descuento_fact').val());

		if (porcentaje > 0) {
			$('#formDescuentoFacturacion #descuento_fact').val(parseFloat(precio * (porcentaje / 100)).toFixed(2));
		} else {
			$('#formDescuentoFacturacion #descuento_fact').val('0.00');
		}
	});

	$("#formDescuentoFacturacion #descuento_fact").off("keyup.facturaMontoDescuento").on("keyup.facturaMontoDescuento", function() {
		var precio = convertirNumeroFactura($('#formDescuentoFacturacion #precio_descuento_fact').val());
		var descuento_fact = convertirNumeroFactura($('#formDescuentoFacturacion #descuento_fact').val());

		if (descuento_fact > 0 && precio > 0) {
			$('#formDescuentoFacturacion #porcentaje_descuento_fact').val(parseFloat((descuento_fact / precio) * 100).toFixed(2));
		} else {
			$('#formDescuentoFacturacion #porcentaje_descuento_fact').val('0.00');
		}
	});
});

$("#reg_DescuentoFacturacion").off("click.facturaRegistrarDescuento").on("click.facturaRegistrarDescuento", function(e) {
	e.preventDefault();

	var row_index = $('#formDescuentoFacturacion #row_index').val();

	var descuento = convertirNumeroFactura($('#formDescuentoFacturacion #descuento_fact').val());
	var precio = convertirNumeroFactura($("#formulario_facturacion #invoiceItem #price_" + row_index).val());
	var cantidad = convertirNumeroFactura($("#formulario_facturacion #invoiceItem #quantity_" + row_index).val());
	var impuesto_venta = $("#formulario_facturacion #invoiceItem #isv_" + row_index).val();

	var subtotalLinea = precio * cantidad;
	var totalLinea = subtotalLinea - descuento;

	if (descuento < 0) {
		notifyFactura(
			'warning',
			'Advertencia',
			'El descuento no puede ser negativo.'
		);
		return false;
	}

	if (totalLinea < 0) {
		notifyFactura(
			'warning',
			'Advertencia',
			'El valor del descuento es mayor al precio total del artículo, por favor corregir.'
		);
		return false;
	}

	$("#formulario_facturacion #invoiceItem #discount_" + row_index).val(parseFloat(descuento).toFixed(2));

	if (impuesto_venta == 1) {
		var porcentaje_isv = convertirNumeroFactura(getPorcentajeISV("Facturas")) / 100;
		var porcentaje_calculo = parseFloat(totalLinea * porcentaje_isv).toFixed(2);
		$('#formulario_facturacion #invoiceItem #valor_isv_' + row_index).val(porcentaje_calculo);
	} else {
		$('#formulario_facturacion #invoiceItem #valor_isv_' + row_index).val('0.00');
	}

	$('#modalDescuentoFacturacion').modal('hide');
	calculateTotal();
});

/****************************************************************************************************************************************************************/
/* FIN DESCUENTO PRODUCTO EN FACTURACION */
/****************************************************************************************************************************************************************/
</script>