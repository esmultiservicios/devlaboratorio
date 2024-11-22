<script>
$(document).ready(function() {
   getServicio();
   getProfesionales();
   pagination(1);
   getTipoPacienteGrupo();
   getPacienteGrupo(1);
});

$(document).ready(function() {
  $('#form_main #servicio').on('change', function(){
     pagination(1);
  });
});

$(document).ready(function() {
  $('#form_main #colaborador').on('change', function(){
     pagination(1);
  });
});

$(document).ready(function() {
  $('#form_main #fecha_i').on('change', function(){
     pagination(1);
  });
});

$(document).ready(function() {
  $('#form_main #fecha_f').on('change', function(){
     pagination(1);
  });
});

$(document).ready(function() {
  $('#form_main #bs_regis').on('keyup', function(){
     pagination(1);
  });
});

function getServicio(){
    var url = '<?php echo SERVERURL; ?>php/reportes_muestras/getServicio.php';

	$.ajax({
        type: "POST",
        url: url,
	    async: true,
        success: function(data){
		    $('#form_main #servicio').html("");
			$('#form_main #servicio').html(data);
			$('#form_main #servicio').selectpicker('refresh');
		}
     });
}

function getProfesionales(){
    var url = '<?php echo SERVERURL; ?>php/citas/getMedico.php';

	$.ajax({
        type: "POST",
        url: url,
        success: function(data){
		    $('#form_main #colaborador').html("");
			$('#form_main #colaborador').html(data);
			$('#form_main #colaborador').selectpicker('refresh');
		}
     });
}

function pagination(partida){
	var servicio = '';
	var colaborador = '';
	var desde = $('#form_main #fecha_i').val();
	var hasta = $('#form_main #fecha_f').val();
	var dato = $('#form_main #bs_regis').val();
	var url = '<?php echo SERVERURL; ?>php/reportes_muestras/paginar.php';

	if($('#form_main #servicio').val() == "" || $('#form_main #servicio').val() == null){
		servicio = "";
	}else{
		servicio = $('#form_main #servicio').val();
	}

	if($('#form_main #colaborador').val() == "" || $('#form_main #colaborador').val() == null){
		colaborador = "";
	}else{
		colaborador = $('#form_main #colaborador').val();
	}

	$.ajax({
		type:'POST',
		url:url,
		data:'partida='+partida+'&desde='+desde+'&hasta='+hasta+'&servicio='+servicio+'&colaborador='+colaborador+'&dato='+dato,
		success:function(data){
			var array = eval(data);
			$('#agrega-registros').html(array[0]);
			$('#pagination').html(array[1]);
		}
	});
	return false;
}

function reporteEXCEL(){
	var colaborador = '';
	var desde = $('#form_main #fecha_i').val();
	var hasta = $('#form_main #fecha_f').val();
	var url = '';

	if($('#form_main #colaborador').val() == "" || $('#form_main #colaborador').val() == null){
		colaborador = "";
	}else{
		colaborador = $('#form_main #colaborador').val();
	}

    url = '<?php echo SERVERURL; ?>php/reportes_muestras/reporte.php?desde='+desde+'&hasta='+hasta+'&servicio='+servicio+'&colaborador='+colaborador;

	window.open(url);
}

function limpiar(){
	$('#unidad').html("");
	$('#medico_general').html("");
    $('#agrega-registros').html("");
	$('#pagination').html("");
    getServicio();
	pagination_transito(1);
}

function convertDate(inputFormat) {
  function pad(s) { return (s < 10) ? '0' + s : s; }
  var d = new Date(inputFormat);
  return [d.getFullYear(), pad(d.getMonth()+1), pad(d.getDate())].join('-');
}

function getMes(fecha){
    var url = '<?php echo SERVERURL; ?>php/atas/getMes.php';
	var resp;

	$.ajax({
	    type:'POST',
		data:'fecha='+fecha,
		url:url,
		async: false,
		success:function(data){
          resp = data;
		}
	});
	return resp	;
}

$('#form_main #reporte_excel').on('click', function(e){
 if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
    e.preventDefault();
    reporteEXCEL();
 }else{
	swal({
		title: "Acceso Denegado",
		text: "No tiene permisos para ejecutar esta acción",
		type: "error",
		confirmButtonClass: 'btn-danger'
	});
 }
});

$('#form_main #reporte_diario').on('click', function(e){ // add event submit We don't want this to act as a link so cancel the link action
 if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
	 e.preventDefault();
	 reporteEXCELDiario();
 }else{
	swal({
		title: "Acceso Denegado",
		text: "No tiene permisos para ejecutar esta acción",
		type: "error",
		confirmButtonClass: 'btn-danger'
	});
 }
});

$('#form_main #limpiar').on('click', function(e){
    e.preventDefault();
    limpiar();
});

function printMuestra(muestras_id){
	var url = '<?php echo SERVERURL; ?>php/muestras/generaMuestra.php?muestras_id='+muestras_id;
    window.open(url);
}

function printReport(muestras_id){
	printMuestra(muestras_id);
}

function getTipoPacienteGrupo(){
    var url = '<?php echo SERVERURL; ?>php/facturacion/getTipoPaciente.php';

	$.ajax({
        type: "POST",
        url: url,
        success: function(data){
		    $('#form_main #tipo_paciente_grupo').html("");
			  $('#form_main #tipo_paciente_grupo').html(data);
			  $('#form_main #tipo_paciente_grupo').selectpicker('refresh');
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
					$('#form_main #pacientesIDGrupo').html("");
					$('#form_main #pacientesIDGrupo').html(data);
					$('#form_main #pacientesIDGrupo').selectpicker('refresh');
				}
     });
}

$('#form_main #tipo_paciente_grupo').on('change',function(){
	getPacienteGrupo($('#form_main #tipo_paciente_grupo').val());
});
</script>
