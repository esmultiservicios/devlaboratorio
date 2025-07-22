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
	//LLAMADA A LAS FUNCIONES
	funciones();

	//INICIO ABRIR VENTANA MODAL PARA EL REGISTRO DE LAS FACTURAS
	$('#form_main_facturacion_reportes #factura').on('click',function(e){
		e.preventDefault();
		if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3 || getUsuarioSistema() == 4){
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
				closeOnEsc: false, // Desactiva el cierre con la tecla Esc
				closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera
			});
	        return false;
          }
	});
	//FIN ABRIR VENTANA MODAL PARA EL REGISTRO DE LAS FACTURAS

	//INICIO PARA EL REGISTRO DE COBROS A PROFESIONALES
	$('#formCobros #generar').on('click', function(e){
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
				closeOnEsc: false, // Desactiva el cierre con la tecla Esc
				closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera
			});
			return false;
		 }
	});
	//FIN PARA EL REGISTRO DE COBROS A PROFESIONALES

    //INICIO PAGINATION (PARA LAS BUSQUEDAS SEGUN SELECCIONES)
  $('#form_main_facturacion_reportes #estado').on('change',function(){
    listar_reporte_facturacion();
  });

  $('#form_main_facturacion_reportes #pacientesIDGrupo').on('change',function(){
    listar_reporte_facturacion();
  });

  $('#form_main_facturacion_reportes #fecha_b').on('change',function(){
    listar_reporte_facturacion();
  });

  $('#form_main_facturacion_reportes #fecha_f').on('change',function(){
    listar_reporte_facturacion();
  });
	//FIN PAGINATION (PARA LAS BUSQUEDAS SEGUN SELECCIONES)
});
//FIN CONTROLES DE ACCION
/****************************************************************************************************************************************************************/

$('#form_eliminar #Si').on('click', function(e){ // add event submit We don't want this to act as a link so cancel the link action
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
			closeOnEsc: false, // Desactiva el cierre con la tecla Esc
			closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera
		});
		return false;
	}
}else{
	swal({
		title: "Acceso Denegado",
		text: "No tiene permisos para ejecutar esta acción",
		icon: "error",
		dangerMode: true,
		closeOnEsc: false, // Desactiva el cierre con la tecla Esc
		closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera
	});
}
});

//INICIO AGRUPAR FUNCIONES DE PACIENTES
function funciones(){
	listar_reporte_facturacion();
	getEstado();
	getTipoPacienteGrupo();
	getPacienteGrupo(1);
}
//FIN AGRUPAR FUNCIONES DE PACIENTES

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
					closeOnEsc: false, // Desactiva el cierre con la tecla Esc
					closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera					
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
					closeOnEsc: false, // Desactiva el cierre con la tecla Esc
					closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera
				});
				return false;
			}else if(registro == 3){
				swal({
					title: "Error",
					text: "Error, este registro ya existe",
					icon: "error",
					dangerMode: true,
					closeOnEsc: false, // Desactiva el cierre con la tecla Esc
					closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera
				});
				return false;
			}else{
				swal({
					title: "Error",
					text: "Error al procesar su solicitud, por favor intentelo de nuevo mas tarde",
					icon: "error",
					dangerMode: true,
					closeOnEsc: false, // Desactiva el cierre con la tecla Esc
					closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera
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

	$.ajax({
		type:'POST',
		url:url,
		data:'facturas_id='+facturas_id,
		success:function(data){
		   $('#mensaje_show').modal({
				show:true,
				keyboard: false,
				backdrop:'static'
		   });
		   $('#mensaje_mensaje_show').html(data);
		   $('#bad').hide();
		   $('#okay').show();
		}
	});
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
			closeOnEsc: false, // Desactiva el cierre con la tecla Esc
			closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera			
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
			closeOnEsc: false, // Desactiva el cierre con la tecla Esc
			closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera
		});
		return false;
	}
}

function rollback(facturas_id,comentario){
	var fecha = getFechaFactura(facturas_id);
    var hoy = new Date();
    fecha_actual = convertDate(hoy);

	var url = '<?php echo SERVERURL; ?>php/reporte_facturacion/rollback.php';

	if ( fecha <= fecha_actual){
	   $.ajax({
		  type:'POST',
		  url:url,
		  data:'facturas_id='+facturas_id+'&comentario='+comentario,
		  success: function(registro){
			  if(registro == 1){
			    listar_reporte_facturacion();
				swal({
					title: "Success",
					text: "Factura cancelada correctamente",
					icon: "success",
					closeOnEsc: false, // Desactiva el cierre con la tecla Esc
					closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera					
				});
			    return false;
			  }else if(registro == 2){
				swal({
					title: "Error",
					text: "Error al cancelar la factura",
					icon: "error",
					dangerMode: true,
					closeOnEsc: false, // Desactiva el cierre con la tecla Esc
					closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera
				});
			    return false;
			  }else{
				swal({
					title: "Error",
					text: "Error al ejecutar esta acción",
					icon: "error",
					dangerMode: true,
					closeOnEsc: false, // Desactiva el cierre con la tecla Esc
					closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera
				});
			  }
		  }
	   });
	   return false;
	}else{
		swal({
			title: "Error",
			text: "No se puede ejecutar esta acción fuera de esta fecha",
			icon: "error",
			dangerMode: true,
			closeOnEsc: false, // Desactiva el cierre con la tecla Esc
			closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera
		});
	}
}

function consultarNombre(pacientes_id){
    var url = '<?php echo SERVERURL; ?>php/pacientes/getNombre.php';
	var resp;

	$.ajax({
	    type:'POST',
		url:url,
		data:'pacientes_id='+pacientes_id,
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
		data:'facturas_id='+facturas_id,
		async: false,
		success:function(data){
          resp = data;
		}
	});
	return resp;
}
//INICIO ROLLBACK

//INICIO GET FECHA FACTURA
function getFechaFactura(facturas_id){
    var url = '<?php echo SERVERURL; ?>php/facturacion/getFechaFactura.php';
	var fecha;
	$.ajax({
	    type:'POST',
		url:url,
		data:'facturas_id='+facturas_id,
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
  function pad(s) { return (s < 10) ? '0' + s : s; }
  var d = new Date(inputFormat);
  return [d.getFullYear(), pad(d.getMonth()+1), pad(d.getDate())].join('-');
}
/******************************************************************************************************************************************************************************/
function getEstado(){
    var url = '<?php echo SERVERURL; ?>php/reporte_facturacion/getEstado.php';

	$.ajax({
        type: "POST",
        url: url,
	    async: true,
        success: function(data){
		    $('#form_main_facturacion_reportes #estado').html("");
			$('#form_main_facturacion_reportes #estado').html(data);
      $('#form_main_facturacion_reportes #estado').selectpicker('refresh');
        }
     });
}

function getTipoPacienteGrupo(){
    var url = '<?php echo SERVERURL; ?>php/facturacion/getTipoPaciente.php';

	$.ajax({
        type: "POST",
        url: url,
        success: function(data){
		    $('#form_main_facturacion_reportes #tipo_paciente_grupo').html("");
			  $('#form_main_facturacion_reportes #tipo_paciente_grupo').html(data);
			  $('#form_main_facturacion_reportes #tipo_paciente_grupo').selectpicker('refresh');
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
					$('#form_main_facturacion_reportes #pacientesIDGrupo').html("");
					$('#form_main_facturacion_reportes #pacientesIDGrupo').html(data);
					$('#form_main_facturacion_reportes #pacientesIDGrupo').selectpicker('refresh');
				}
     });
}

$('#form_main_facturacion_reportes #tipo_paciente_grupo').on('change',function(){
	getPacienteGrupo($('#form_main_facturacion_reportes #tipo_paciente_grupo').val());
});

var listar_reporte_facturacion = function(){
	var fechai = $('#form_main_facturacion_reportes #fecha_b').val();
	var fechaf = $('#form_main_facturacion_reportes #fecha_f').val();
	var pacientesIDGrupo = $('#form_main_facturacion_reportes #pacientesIDGrupo').val() || '';
	var estado = $('#form_main_facturacion_reportes #estado').val() || 1;
	
	var table_reporte_facturacion  = $("#dataTableReporteFacturacionMain").DataTable({
		"destroy":true,	
		"ajax":{
			"method":"POST",
			"url": "<?php echo SERVERURL; ?>php/reporte_facturacion/llenarDataTableReporteFacturas.php",
            "data": function(d) {
                d.fechai = fechai;
                d.fechaf = fechaf;
                d.pacientesIDGrupo = pacientesIDGrupo;			
				d.estado = estado;
            }		
		},		
		"columns":[
			{
				"data": "fecha",
				"render": function(data, type, row) {
					return '<a href="#" class="showInvoiceDetail">' + data + '</a>';
				}
			},									
			{
				"data": "tipo_documento",
				"render": function(data, type, row) {
					var color = data === 'Contado' ? '#119FB8' : '#FF781E'; // Naranja para "Contado" y morado para "Crédito"
					return '<span class="tipo-documento" style="border: 2px solid ' + color + '; border-radius: 12px; padding: 5px 10px; color: ' + color + ';">' + data + '</span>';
				}
			},
			{"data": "muestra"},
			{"data": "factura"},
			{"data": "paciente"},
			{"data": "identidad"},										
			{"data": "profesional"},
			{"data": "precio"},
			{"data": "isv_neto"},	
			{"data": "descuento"},
			{
				"data": "total",
				"render": function(data, type, row) {
					var estadoPago = row.estado_pago;  // Obtenemos el estado_pago desde la fila
					var color = 'black';  // Color por defecto
					var tooltip = 'Estado desconocido'; // Mensaje por defecto

					// Cambiar el color según el estado_pago
					if (estadoPago === 'Pago Pendiente') {
						color = '#CC2936';  // Rojo si está pendiente
						tooltip = 'Pago Pendiente.';
					} else if (estadoPago === 'Pagada') {
						color = '#A5BF13';  // Verde si está pagada
						tooltip = 'Pago Realizado.';
					}

					// Retornar el total con el color correspondiente
					return '<span data-toggle="tooltip" data-placement="top" title="' + tooltip + '" style="border: 2px solid ' + color + '; border-radius: 12px; padding: 5px 10px; color: ' + color + '; font-weight: bold; cursor: help;">' + data + '</span>';
				}
			},
			{"data": "servicio"},						
            {
                "data": "tipo_factura_agrupada",
                "render": function(data, type, row) {
                    var color = data === 'Grupal' ? 'green' : '#006992'; // Cambiar a los colores deseados
                    return '<span class="tipo-factura" style="border: 2px solid ' + color + '; border-radius: 12px; padding: 5px 10px; color: ' + color + ';">' + data + '</span>';
                }
            },
			{
				"data": null,
				"defaultContent": 
					'<div class="btn-group">' +
						'<button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' +
							'<i class="fas fa-cog"></i>' +
						'</button>' +
						'<div class="dropdown-menu">' +
							'<a class="dropdown-item printBill" href="#"><i class="fas fa-print fa-lg"></i> Imprimir</a>' +
							'<a class="dropdown-item closeBill" href="#"><i class="fas fa-calculator fa-lg"></i> Cierre</a>' +
							'<a class="dropdown-item deleteBill" href="#"><i class="fas fa-download fa-lg"></i> Anular</a>' +
						'</div>' +
					'</div>'
			}
		],	
		"footerCallback": function(row, data, start, end, display) {
            var api = this.api();

            // Limpiar el contenido del footer
            $('#footer-importe').html('');
            $('#footer-isv').html('');
            $('#footer-descuento').html('');
            $('#footer-neto').html('');
            $('#tipo_pago').html('');
            $('#total_pago').html('');

            // Función para calcular la suma de una columna específica
            var sumaColumna = function(index) {
                return api.column(index, { page: 'current' })
                    .data()
                    .reduce(function(a, b) {
                        return (parseFloat(a) || 0) + (parseFloat(b) || 0);
                    }, 0);
            };

            // Calcular totales para las columnas específicas
            var totalImporte = sumaColumna(7);
            var totalISV = sumaColumna(8);
            var totalDescuento = sumaColumna(9);
            var totalNeto = sumaColumna(10);

            var formatter = new Intl.NumberFormat('es-HN', {
                style: 'currency',
                currency: 'HNL',
                minimumFractionDigits: 2,
            });

            // Mostrar totales de las columnas
            $('#footer-importe').html(formatter.format(totalImporte));
            $('#footer-isv').html(formatter.format(totalISV));
            $('#footer-descuento').html(formatter.format(totalDescuento));
            $('#footer-neto').html(formatter.format(totalNeto));
        },	
		"order": [],	
        "lengthMenu": lengthMenu20,
		"stateSave": true,
		"bDestroy": true,		
		"language": idioma_español,//esta se encuenta en el archivo main.js
		"dom": dom,			
		"buttons":[		
			{
				text:      '<i class="fas fa-sync-alt fa-lg"></i> Actualizar',
				titleAttr: 'Actualizar Facturas',
				className: 'btn btn-info',
				action: 	function(){
					listar_reporte_facturacion();
				}
			},		
			{
				text:      '<i class="fas fa-calculator fa-lg"></i> Cierre',
				titleAttr: 'Cierre de Caja',
				className: 'btn btn-primary',
				action: 	function(){
					cierreBill();
				}
			},		
			{
				text:      '<i class="fa-solid fa-file-pdf fa-lg"></i> Reporte PDF',
				titleAttr: 'Reporte de Facturación PDF',
				className: 'btn btn-danger',
				action: 	function(){
					reporteFacturacion();
				}
			},
			{
				text:      '<i class="fa-solid fa-file-excel fa-lg"></i> Reporte Excel',
				titleAttr: 'Reporte de Facturación Excel',
				className: 'btn btn-success',
				action: 	function(){
					reporteFacturacionExcel();
				}
			}
		]		
	});	 

	// Inicializar tooltips después de cada redibujado de la tabla
	$('#dataTableReporteFacturacionMain').on('draw.dt', function() {
		$('[data-toggle="tooltip"]').tooltip();
	});

	table_reporte_facturacion.search('').draw();
	$('#buscar').focus();
	
	show_invoice_detail_dataTable("#dataTableReporteFacturacionMain tbody", table_reporte_facturacion);
	print_bill_dataTable("#dataTableReporteFacturacionMain tbody", table_reporte_facturacion);
	close_bill_dataTable("#dataTableReporteFacturacionMain tbody", table_reporte_facturacion);
	delete_bill_dataTable("#dataTableReporteFacturacionMain tbody", table_reporte_facturacion);	
}

var show_invoice_detail_dataTable = function(tbody, table){
	$(tbody).off("click", "a.showInvoiceDetail");
	$(tbody).on("click", "a.showInvoiceDetail", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();
		
		swal({
			title: "Información",
			text: "Esta opción se encuentra en desarrollo",
			icon: "warning",
			dangerMode: true,
			closeOnEsc: false, // Desactiva el cierre con la tecla Esc
			closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera			
		});		
		//invoicesDetails(data.pacientes_id)
	});
}

var print_bill_dataTable = function(tbody, table){
	$(tbody).off("click", "a.printBill");
	$(tbody).on("click", "a.printBill", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();
		
		if(data.tipo_factura_agrupada === "Individual") {
			printBill(data.facturas_id);

			return;
		}	
		
		printBillGroup(data.numero);
	});
}

var close_bill_dataTable = function(tbody, table){
	$(tbody).off("click", "a.closeBill");
	$(tbody).on("click", "a.closeBill", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();
		
		swal({
			title: "Información",
			text: "Esta opción se encuentra en desarrollo",
			icon: "warning",
			dangerMode: true,
			closeOnEsc: false, // Desactiva el cierre con la tecla Esc
			closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera			
		});
	});
}

var delete_bill_dataTable = function(tbody, table){
	$(tbody).off("click", "a.deleteBill");
	$(tbody).on("click", "a.deleteBill", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();
		
		modal_rollback(data.facturas_id, data.pacientes_id)
	});
}

function reporteFacturacion() {
    var fechai = $('#form_main_facturacion_reportes #fecha_b').val();
    var fechaf = $('#form_main_facturacion_reportes #fecha_f').val();  
    var clientes = $('#form_main_facturacion_reportes #clientes').val();
    var profesional = $('#form_main_facturacion_reportes #profesional').val();
    var estado = $('#form_main_facturacion_reportes #estado').val() || 1;

    // Añadir los parámetros al formulario
    var params = {
        "estado": estado,
        "type": "Reporte_facturas_laboratorio_cami",
        "fechai": fechai,
        "fechaf": fechaf,
        "clientes": clientes,
        "profesional": profesional,
        "db": "<?php echo DB; ?>"
    };

    viewReport(params);
}

function reporteFacturacionExcel() {
    var fechai = $('#form_main_facturacion_reportes #fecha_b').val();
    var fechaf = $('#form_main_facturacion_reportes #fecha_f').val();  
    var clientes = $('#form_main_facturacion_reportes #clientes').val();
    var profesional = $('#form_main_facturacion_reportes #profesional').val();
    var estado = $('#form_main_facturacion_reportes #estado').val() || 1;

    // Añadir los parámetros al formulario
    var params = {
        "estado": estado,
        "type": "Reporte_facturas_laboratorio_cami",
        "fechai": fechai,
        "fechaf": fechaf,
        "clientes": clientes,
        "profesional": profesional,
		"tipo": "Excel",
        "db": "<?php echo DB; ?>"
    };

    viewReport(params);
}
</script>
