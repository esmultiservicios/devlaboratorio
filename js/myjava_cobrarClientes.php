<script>
/*INICIO DE FUNCIONES PARA ESTABLECER EL FOCUS PARA LAS VENTANAS MODALES*/
$(document).ready(function(){
    $("#modal_almacen").on('shown.bs.modal', function(){
        $(this).find('#formulario_almacen #almacen').focus();
    });
});
/*FIN DE FUNCIONES PARA ESTABLECER EL FOCUS PARA LAS VENTANAS MODALES*/

$('#form_main_cobrar_clientes #buscar').on('click',function(e){
		e.preventDefault();
		listar_cuentas_por_cobrar_clientes();		
	});

$(document).ready(function(){
	getClientesCXC();
	listar_cuentas_por_cobrar_clientes();
	getBanco();
});	

function getClientesCXC(){
    var url = '<?php echo SERVERURL; ?>php/cobrarClientes/getClientesCXC.php';

	$.ajax({
        type: "POST",
        url: url,
	    async: true,
        success: function(data){
		    $('#form_main_cobrar_clientes #cobrar_clientes').html("");
			$('#form_main_cobrar_clientes #cobrar_clientes').html(data);
			$('#form_main_cobrar_clientes #cobrar_clientes').selectpicker('refresh');
		}
     });
}

var listar_cuentas_por_cobrar_clientes = function(){
	var estado = "";

	if($("#form_main_cobrar_clientes #cobrar_clientes_estado").val() == "" || $("#form_main_cobrar_clientes #cobrar_clientes_estado").val() == null){
		estado = 1;
	}else{
		estado = $("#form_main_cobrar_clientes #cobrar_clientes_estado").val();
	}

	var clientes_id = $("#form_main_cobrar_clientes #cobrar_clientes").val();
	var fechai = $("#form_main_cobrar_clientes #fechai").val();
	var fechaf = $("#form_main_cobrar_clientes #fechaf").val();

	var table_cuentas_por_cobrar_clientes = $("#dataTableCuentasPorCobrarClientes").DataTable({
		"destroy":true,
		"ajax":{
			"method":"POST",
			"url":"<?php echo SERVERURL; ?>php/cobrarClientes/llenarDataTableCobrarClientes.php",
			"data":{
				"estado":estado,
				"clientes_id":clientes_id,
				"fechai":fechai,
				"fechaf":fechaf
			}
		},
		"columns":[
			{"data":"fecha"},
			{"data":"cliente"},
			{"data":"numero"},
			{
                data: 'credito',
                render: function (data, type) {
                    var number = $.fn.dataTable.render
                        .number(',', '.', 2, 'L ')
                        .display(data);

                    if (type === 'display') {
                        let color = 'green';
                        if (data < 0) {
                            color = 'red';
                        }

                        return '<span style="color:' + color + '">' + number + '</span>';
                    }

                    return number;
                },
            },
			{data: "abono",
				render: function (data, type) {
                    var number = $.fn.dataTable.render
                        .number(',', '.', 2, 'L ')
                        .display(data);

                    if (type === 'display') {
                        let color = 'green';
                        if (data < 0) {
                            color = 'red';
                        }

                        return '<span style="color:' + color + '">' + number + '</span>';
                    }

                    return number;
                },
			},
			{data:"saldo",
				render: function (data, type) {
                    var number = $.fn.dataTable.render
                        .number(',', '.', 2, 'L ')
                        .display(data);

                    if (type === 'display') {
                        let color = 'green';
                        if (data < 0) {
                            color = 'red';
                        }

                        return '<span style="color:' + color + '">' + number + '</span>';
                    }

                    return number;
                },
			},
			{"data":"vendedor"},
			{"defaultContent":"<button class='table_abono btn btn-dark'><span class='fas fa-cash-register fa-lg'></span></button>"},
			{"defaultContent":"<button class='table_reportes abono_factura btn btn-dark ocultar'><span class='fa fa-money-bill-wave fa-solid'></span></button>"},
			{"defaultContent":"<button class='table_reportes print_factura btn btn-dark ocultar'><span class='fas fa-file-download fa-lg'></span></button>"}
		],
		"pageLength": 10,
        "lengthMenu": lengthMenu,
		"stateSave": true,
		"bDestroy": true,
		"language": idioma_español,
		"dom": dom,
		"columnDefs": [
		  { width: "10%", targets: 0 },
		  { width: "16%", targets: 1 },
		  { width: "16%", targets: 2 },
		  { width: "12%", targets: 3, className: "text-center"},
		  { width: "12%", targets: 4, className: "text-center" },
		  { width: "12%", targets: 5, className: "text-center" },
		  { width: "16%", targets: 6 },
		  { width: "2%", targets: 7 },
		  { width: "2%", targets: 8 },
		  { width: "2%", targets: 9 }
		],
		"fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
        	$('td', nRow).addClass(aData['color']);
			for (let index = 0; index < aData.length; index++) {
				console.log(aData[i]["credito"]);
			}
			$(row).find('td:eq(2)').css('color', 'red');
			$('#credito-cxc').html('L. '+ aData['total_credito'])
			$('#abono-cxc').html('L. '+ aData['total_abono'])
			$('#total-footer-cxc').html('L. '+ aData['total_pendiente'])

		},
		"buttons":[
			{
				text:      '<i class="fas fa-sync-alt fa-lg"></i> Actualizar',
				titleAttr: 'Actualizar Cuentas por Cobrar Clientes',
				className: 'table_actualizar btn btn-secondary ocultar',
				action: 	function(){
					listar_cuentas_por_cobrar_clientes();
				}
			},
			{
				extend:    'excelHtml5',
				text:      '<i class="fas fa-file-excel fa-lg"></i> Excel',
				titleAttr: 'Excel',
				title: 'Reporte Cuents por Cobrar Clientes',
				exportOptions: {
						columns: [2,3,4,5,6]
				},
				className: 'table_reportes btn btn-success ocultar'
			},
			{
				extend:    'pdf',
				text:      '<i class="fas fa-file-pdf fa-lg"></i> PDF',
				titleAttr: 'PDF',
				title: 'Reporte Cuentas por Cobrar Clientes',
				messageTop: 'Fecha desde: ' + convertDateFormat(fechai) + ' Fecha hasta: ' + convertDateFormat(fechaf),
				messageBottom: 'Fecha de Reporte: ' + convertDateFormat(today()),
				className: 'table_reportes btn btn-danger ocultar',
				exportOptions: {
						columns: [2,3,4,5,6]
				},
				customize: function ( doc ) {
					doc.content.splice( 1, 0, {
						margin: [ 0, 0, 0, 12 ],
						alignment: 'left',
						image: imagen,
						width:100,
                        height:45
					} );
				}
			}
		],
		"drawCallback": function( settings ) {
        	//getPermisosTipoUsuarioAccesosTable(getPrivilegioTipoUsuario());
    	}
	});
	table_cuentas_por_cobrar_clientes.search('').draw();
	$('#buscar').focus();

	registrar_abono_cxc_clientes_dataTable("#dataTableCuentasPorCobrarClientes tbody", table_cuentas_por_cobrar_clientes);
	ver_abono_cxc_clientes_dataTable("#dataTableCuentasPorCobrarClientes tbody", table_cuentas_por_cobrar_clientes);
	view_reporte_facturas_dataTable("#dataTableCuentasPorCobrarClientes tbody", table_cuentas_por_cobrar_clientes);
}

var registrar_abono_cxc_clientes_dataTable = function(tbody, table){
	$(tbody).off("click", "button.table_abono");
	$(tbody).on("click", "button.table_abono", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();
		if(data.estado == 2 || data.saldo <= 0){//no tiene acceso a la accion si la factura ya fue cancelada
				swal({
					title: 'Error',
					text: 'No puede realizar esta accion a las facturas canceladas!',
					type: 'error',
					confirmButtonClass: 'btn-danger'
				});
		}else{
			$("#GrupoPagosMultiplesFacturas").hide();
			if(data.tipo == 'Cliente'){
				pago(data.facturas_id,2);
			}else{
				pagoGrupal(data.facturas_id,2);
			}							
		}
	});
}

var ver_abono_cxc_clientes_dataTable = function(tbody, table){
	$(tbody).off("click", "button.abono_factura");
	$(tbody).on("click", "button.abono_factura", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();
		$('#ver_abono_cxc').modal('show');
		$("#formulario_ver_abono_cxc #abono_facturas_id").val(data.facturas_id);
		$("#formulario_ver_abono_cxc #abono_tipo").val(data.tipo);
		listar_AbonosCXC();
	});
}

var view_reporte_facturas_dataTable = function(tbody, table){
	$(tbody).off("click", "button.print_factura");
	$(tbody).on("click", "button.print_factura", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();
		if(data.tipo == 'Cliente'){
			printBill(data.facturas_id);
		}else{
			printBillGroup(data.facturas_id);
		}		
	});
}

var listar_AbonosCXC = function(){
	var factura_id = $("#formulario_ver_abono_cxc #abono_facturas_id").val();
	var tipo = $("#formulario_ver_abono_cxc #abono_tipo").val();

	var table_cuentas_por_cobrar_clientes = $("#table-modal-abonos").DataTable({
		"destroy":true,
		"ajax":{
			"method":"POST",
			"url":"<?php echo SERVERURL; ?>php/cobrarClientes/getAbonosCXC.php",
			"data":{
				"factura_id": factura_id,
				"tipo": tipo
			}
		},
		"columns":[
			{"data":"fecha"},
			{"data":"tipo_pago"},
			{"data":"descripcion"},
			{"data":"abono"},
			{"data":"usuario"},
		],
		"pageLength": 10,
        "lengthMenu": lengthMenu,
		"stateSave": true,
		"bDestroy": true,
		"language": idioma_español,
		"dom": dom,
		"columnDefs": [
		  { width: "10%", targets: 0 },
		  { width: "15%", targets: 1 },
		  { width: "35%", targets: 2 },
		  { width: "15%", targets: 3 },
		  { width: "50%", targets: 4 }
		],
		"fnRowCallback": function( nRow, res, iDisplayIndex, iDisplayIndexFull ) {
			$('#ver_abono_cxcTitle').html('Factura: '+ res['no_factura'] + ' Cliente: '+ res['cliente'] + ' Total Factura: L. ' + res['importe'])
			$('#total-footer-modal-cxc').html('L. '+ res['total'])
		},
		"buttons":[
			{
				text:      '<i class="fas fa-sync-alt fa-lg"></i> Actualizar',
				titleAttr: 'Actualizar Abonos',
				className: 'table_actualizar btn btn-secondary ocultar',
				action: 	function(){
					listar_AbonosCXC();
				}
			},
			{
				extend:    'excelHtml5',
				text:      '<i class="fas fa-file-excel fa-lg"></i> Excel',
				titleAttr: 'Excel',
				title: 'Reporte de Abonos Cuentas por Cobrar Clientes',
				messageTop: 'Factura: ' + getNumeroFactura(factura_id) + ' ' + getNombreClienteFactura(factura_id) + ' Total Factura: L. ' + getImporteFacturas(factura_id),
				messageBottom: 'Fecha de Reporte: ' + convertDateFormat(today()),
				className: 'table_reportes btn btn-success ocultar'
			},
			{
				extend:    'pdf',
				text:      '<i class="fas fa-file-pdf fa-lg"></i> PDF',
				titleAttr: 'PDF',
				title: 'Reporte de Abonos Cuentas por Cobrar Clientes',
				messageTop: 'Factura: ' + getNumeroFactura(factura_id) + ' ' + getNombreClienteFactura(factura_id) + ' Total Factura: L. ' + getImporteFacturas(factura_id),
				messageBottom: 'Fecha de Reporte: ' + convertDateFormat(today()),
				className: 'table_reportes btn btn-danger ocultar',
				customize: function ( doc ) {
					doc.content.splice( 1, 0, {
						margin: [ 0, 0, 0, 12 ],
						alignment: 'left',
						image: imagen,
						width:100,
                        height:45
					} );
				}
			}
		],
		"drawCallback": function( settings ) {
        	//getPermisosTipoUsuarioAccesosTable(getPrivilegioTipoUsuario());
    	}
	});
	table_cuentas_por_cobrar_clientes.search('').draw();
	$('#buscar').focus();
}
</script>