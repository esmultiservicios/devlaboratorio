<script>
$('#botones_citas #tipo_muestra').on('change',function(){
	actualizarEventos();
});

$(document).ready(function() {
	getTipoMuestra();
	actualizarEventos();
	var hoy = new Date();
    fecha_actual = convertDate(hoy);
	$("#form-addevent #color").css("pointer-events","none");
	$("#ModalEdit #color").css("pointer-events","none");

	$('#calendar').fullCalendar({
	    header: {
			left: 'prev,next today',
			center: 'title',
			right: 'month,agendaWeek,agendaDay'
		},
		defaultView: 'agendaWeek',
		height: 792,
		width: 990,
		dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
		dayNamesShort: ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"],
		dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
        monthNames:
            ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio",
            "Agosto", "Septiembre", 	"Octubre", "Noviembre", "Diciembre"],
        monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
		defaultDate: fecha_actual,
		slotLabelInterval: '00:20:00',
        minTime: "07:00:00",
        maxTime: "23:59:59",
        slotDuration: "00:40:00",
		editable: true,
		eventLimit: true, // allow "more" link when too many events
		selectable: true,
		selectHelper: true,
		//eventDurationEditable: false,
		displayEventTime: true,
	    businessHours: {
          start: '08:00:00', // hora final
          end: '23:59:59', // hora inicial
          dow: [ 1, 2, 3, 4, 5, 6 ] // dias de semana, 0=Domingo
        },

		select: function(start, end) {
		  /*if(getFechaAusencias(moment(start).format('YYYY-MM-DD HH:mm:ss'), $('#botones_citas #medico_general').val()) == 2){
			if (getUsuarioSistema() == 1 || getUsuarioSistema() == 3 || getUsuarioSistema() == 4){
                 $("#ModalAdd_enviar").attr('disabled', false);
			     if ($('#medico_general').val()!="" && $('#servicio').val()!=""){
				     $('#form-addevent')[0].reset();
			         if (moment(start).format('YYYY-MM-DD HH:mm:ss') >= fecha_actual){
					    $('#ModalAdd #fecha_cita').val(moment(start).format('YYYY-MM-DD HH:mm:ss'));
			            $('#ModalAdd #fecha_cita_end').val(moment(end).format('YYYY-MM-DD HH:mm:ss'));
			            $('#ModalAdd #medico').val($('#botones_citas #medico_general').val());
						$('#ModalAdd #unidad').val($('#botones_citas #unidad').val());
					    $('#ModalAdd #serv').val($('#botones_citas #servicio').val());
						$('#form-addevent #profesional_citas').val(getProfesionalName($('#botones_citas #medico_general').val()));

		                $('#ModalAdd').modal({
							show:true,
							keyboard: false,
							backdrop:'static'
		                });
                        $('#mensaje_ModalAdd').removeClass('error');
					    $('#mensaje_ModalAdd').removeClass('bien');
					    $('#mensaje_ModalAdd').hide();
					    $('#mensaje_ModalAdd').html("");
			         }else{
						swal({
							title: "Error",
							text: "No se puede agregar una cita en esta fecha",
							type: "error",
							confirmButtonClass: 'btn-danger'
						});
			         }
			         }else{
						swal({
							title: "Error",
							text: "Debe seleccionar un médico y un servcicio antes de agendar una cita",
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
		  }else{
				swal({
					title: "Error",
					text: "El médico se encuentra ausente, no se le puede agendar una cita. " + getComentarioAusencia(moment(start).format('YYYY-MM-DD HH:mm:ss'))+ "",
					type: "error",
					confirmButtonClass: 'btn-danger'
				});
		  }	*/
		},
		eventRender: function(event, element) {
		  element.bind('dblclick', function() {
              /*if (getUsuarioSistema() == 1 || getUsuarioSistema() == 3 || getUsuarioSistema() == 4){
				 $("#ModalEdit_enviar").attr('disabled', false);
	             $("#ModalImprimir_enviar").attr('disabled', false);
		         $('#form-editevent')[0].reset();
		         var palabras = event.title.split("-");
                 var fecha = moment(event.start).format('YYYY-MM-DD HH:mm:ss').split(" ");
		         $('#ModalEdit #paciente').val(palabras[1]);
		         $('#ModalEdit #fecha_citaedit1').val(moment(event.start).format('YYYY-MM-DD HH:mm:ss'));
		         $('#ModalEdit #fecha_citaeditend').val(moment(event.end).format('YYYY-MM-DD HH:mm:ss'));
		         $('#ModalEdit #color').val(event.color);
                 getColaborador_id(event.id);
				 getComentario(event.id);
				 getComentario1(event.id);
				 getHora(event.id);
				 getFechaInicio(event.id);
				 getHoraInicio(event.id);
				 getExpediente(event.id);
		         $('#ModalEdit #id').val(event.id);
		         $('#ModalEdit').modal({
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
		      }*/
		    });
		},
		eventDrop: function(event, delta, revertFunc) { // si changement de position
		   /*if(getFechaAusencias(moment(event.start).format('YYYY-MM-DD HH:mm:ss')) == 2){
		       if (getUsuarioSistema() == 1 || getUsuarioSistema() == 3 || getUsuarioSistema() == 4){
		           if (moment(event.start).format('YYYY-MM-DD HH:mm:ss') >= fecha_actual){
			          edit(event);
		           }else{
						swal({
							title: "Error",
							text: "No se puede mover una cita en esta fecha",
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
		   }else{
				swal({
					title: "Error",
					text: "El médico se encuentra ausente, no se le puede agendar una cita. " + getComentarioAusencia(moment(event.start).format('YYYY-MM-DD HH:mm:ss')) + "",
					type: "error",
					confirmButtonClass: 'btn-danger'
				});
		   }*/
		},
		eventResize: function(event,dayDelta,minuteDelta,revertFunc) { // si changement de longueur
			/*if(getFechaAusencias(moment(start).format('YYYY-MM-DD HH:mm:ss')) == 2){
				if (getUsuarioSistema() == 1){
					edit(event);
				}else{
						swal({
							title: "Acceso Denegado",
							text: "No tiene permisos para ejecutar esta acción",
							type: "error",
							confirmButtonClass: 'btn-danger'
						});
				}
			}else{
				swal({
					title: "Error",
					text: "El médico se encuentra ausente, no se le puede agendar una cita",
					type: "error",
					confirmButtonClass: 'btn-danger'
				});
		    }*/
		}
		//, events: "<?php echo SERVERURL; ?>php/citas/getCalendar.php",
    });
});

/*INICIO DE FUNCIONES PARA ESTABLECER EL FOCUS PARA LAS VENTANAS MODALES*/
$(document).ready(function(){
    $("#ModalAdd").on('shown.bs.modal', function(){
        $(this).find('#form-addevent #expediente').focus();
    });
});

$(document).ready(function(){
    $("#buscarCita").on('shown.bs.modal', function(){
        $(this).find('#form-buscarcita #bs-regis').focus();
    });
});

$(document).ready(function(){
    $("#buscarHistorial").on('shown.bs.modal', function(){
        $(this).find('#form-buscarhistorial #bs-regis').focus();
    });
});

$(document).ready(function(){
    $("#buscarHistorialReprogramaciones").on('shown.bs.modal', function(){
        $(this).find('#form_buscarhistorial_reprogramaciones #bs-regis').focus();
    });
});

$(document).ready(function(){
    $("#buscarHistorialNo").on('shown.bs.modal', function(){
        $(this).find('#form-buscarhistorialno #bs-regis').focus();
    });
});

$(document).ready(function(){
    $("#modal_busqueda_colaboradores").on('shown.bs.modal', function(){
        $(this).find('#formulario_busqueda_coloboradores #buscar').focus();
    });
});
/*FIN DE FUNCIONES PARA ESTABLECER EL FOCUS PARA LAS VENTANAS MODALES*/

$('#ModalImprimir_enviar').on('click', function(e){ // delete event clicked // We don't want this to act as a link so cancel the link action
	 if ($('#fecha_citaedit').val() == "" || $('#fecha_citaeditend').val() == "" ){
		$('#form-editevent')[0].reset();
		swal({
			title: "Error",
			text: "No se pueden enviar los datos, los campos estan vacíos",
			type: "error",
			confirmButtonClass: 'btn-danger'
		});
		return false;
	 }else{
        e.preventDefault();
		reportePDF($('#form-editevent #id').val());
	 }
});

$(document).ready(function() {
	setInterval('actualizarEventos()',1000);
});

function actualizarEventos(){
	var tipo_muestra = "";

	if($('#botones_citas #tipo_muestra').val() != null || $('#botones_citas #tipo_muestra').val() != ""){
		tipo_muestra = $('#botones_citas #tipo_muestra').val()
	}

	var url = '<?php echo SERVERURL; ?>php/citas/getCalendar.php';

	$.ajax({
		type: "POST",
		url: url,
		async: true,
		data:'tipo_muestra='+tipo_muestra,
		success: function(events){
			$('#calendar').fullCalendar('removeEvents');
			$('#calendar').fullCalendar('addEventSource', events);
			$('#calendar').fullCalendar('rerenderEvents');
		}
	});
}

function convertDate(inputFormat) {
     function pad(s) { return (s < 10) ? '0' + s : s; }
     var d = new Date(inputFormat);
     return [d.getFullYear(), pad(d.getMonth()+1), pad(d.getDate())].join('-');
}

//BOOSTRAP SELECT
function reportePDF(agenda_id){
	window.open('<?php echo SERVERURL; ?>php/citas/tickets.php?agenda_id='+agenda_id);
}

function pagination(partida){

}

function getTipoMuestra(){
    var url = '<?php echo SERVERURL; ?>php/citas/getTipoMuestra.php';

	$.ajax({
        type: "POST",
        url: url,
	    async: true,
        success: function(data){
		    $('#botones_citas #tipo_muestra').html("");
			$('#botones_citas #tipo_muestra').html(data);
			$('#botones_citas #tipo_muestra').selectpicker('refresh');
        }
     });
}

$(document).ready(function() {
	actualizarEventos();
});
</script>
