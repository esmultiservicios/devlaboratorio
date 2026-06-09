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

function rollback(facturas_id, comentario) {
  try {
    var fecha = getFechaFactura(facturas_id); // tu función
    var hoy = new Date();
    var fecha_actual = convertDate(hoy);      // tu función

    var url = '<?php echo SERVERURL; ?>php/reporte_facturacion/rollback.php';

    // Validación básica previa (igual que tenías)
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
        // Se espera {status, code, title, message, data?}
        if (resp && resp.status === true) {
          // Éxito
          listar_reporte_facturacion(); // refrescar tabla
          showNotify("success", "Success", resp.message || "Registro anulado correctamente");
        } else {
          // Error controlado desde PHP
          var title = (resp && resp.title) ? resp.title : "Error";
          var msg   = (resp && resp.message) ? resp.message : "Error al ejecutar esta acción";
          // Si quieres distinguir por resp.code:
          // switch(resp.code) { case 'NOT_FOUND': ...; break; }
          showNotify("error", title, msg);
        }
      },
      error: function (xhr, status, err) {
        // Error no controlado (red, parse, 500 sin JSON, etc.)
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
				min-width: 180px;
			}

			.rf-dropdown-menu .dropdown-item {
				padding: 10px 14px !important;
				font-weight: 600 !important;
				color: #333;
			}

			.rf-dropdown-menu .dropdown-item:hover {
				background: #f2f7ff !important;
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

					return '<a href="#" class="showInvoiceDetail rf-link-fecha">' +
						'<i class="fas fa-calendar-alt"></i>' +
						'<span>' + rfEscapeHtml(data) + '</span>' +
					'</a>';
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
	delete_bill_dataTable("#dataTableReporteFacturacionMain tbody", table_reporte_facturacion_global);

	return false;
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
		"documento_id": 1,
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
		"documento_id": 1,
        "db": "<?php echo DB; ?>"
    };

    viewReport(params);
}
</script>
