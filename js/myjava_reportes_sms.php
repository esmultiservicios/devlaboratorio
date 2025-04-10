<script>
$(document).ready(function() {
	setInterval('pagination(1)',8000);
});

$(document).ready(function() {
   getServicio();
   getProfesional();
   pagination(1);
	 getTipoPacienteGrupo();
	 getPacienteGrupo(1);
});

$(document).ready(function() {
  $('#form_main #profesional').on('change', function(){
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

$(document).ready(function() {
  $('#form_main #usuario').on('change', function(){
     pagination(1);
  });
});

//BOTONES DE ACCIÓN PARA EJECUTAR EL REPORTE EN EXCEL
$('#form_main #reportes_exportar').on('click', function(e){
 if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
	e.preventDefault();
	if($('#form_main #servicio').val() != ""){
	   reporteEXCEL();
	}else{
		swal({
			title: "Error",
			text: "Error al exportar, debe seleccionar el servicio",
			icon: "error",
			dangerMode: true,
			closeOnEsc: false, // Desactiva el cierre con la tecla Esc
			closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera
		});
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

$('#form_main #reportes_exportar_diario').on('click', function(e){
 if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
	e.preventDefault();
	if($('#form_main #servicio').val() != ""){
	   reporteDiarioEXCEL();
	}else{
		swal({
			title: "Error",
			text: "Error al exportar, debe seleccionar el servicio",
			icon: "error",
			dangerMode: true,
			closeOnEsc: false, // Desactiva el cierre con la tecla Esc
			closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera
		});
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

$('#form_main #reportes_exportar_diario_colaboradores').on('click', function(e){
 if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
	e.preventDefault();
	if($('#form_main #servicio').val() != ""){
	   reporteDiarioColaboradorEXCEL();
	}else{
		swal({
			title: "Error",
			text: "Error al exportar, debe seleccionar el servicio",
			icon: "error",
			dangerMode: true,
			closeOnEsc: false, // Desactiva el cierre con la tecla Esc
			closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera
		});
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

//OBTENER EL USUARIO
function getUsuario(){
    var url = '<?php echo SERVERURL; ?>php/sms/getUsuario.php';

	$.ajax({
	    type:'POST',
		url:url,
		async: true,
		success:function(data){
		   $('#form_main #usuario').html("");
		   $('#form_main #usuario').html(data);
		   $('#form_main #usuario').selectpicker('refresh');
		}
	});
	return false;
}

//OBTENER EL SERVICIO
function getProfesional(){
    var url = '<?php echo SERVERURL; ?>php/citas/getMedico.php';

	$.ajax({
	    type:'POST',
		url:url,
		async: true,
		success:function(data){
		   $('#form_main #profesional').html("");
		   $('#form_main #profesional').html(data);
		   $('#form_main #profesional').selectpicker('refresh');
		}
	});
	return false;
}

//CAMBIAR VALORES DE LA UNDIAD AL SELECCIONAR EL SERVICIO
$(document).ready(function() {
	  $('#form_main #servicio').on('change', function(){
		var servicio = $('#form_main #servicio').val();
        var url = '<?php echo SERVERURL; ?>php/citas/getUnidad.php';

		$.ajax({
            type: "POST",
            url: url,
			data:'servicio='+servicio,
			async: true,
            success: function(data){
				$('#form_main #unidad').html("");
				$('#form_main #unidad').html(data);
				$('#form_main #unidad').selectpicker('refresh');
				$('#form_main #profesional').html("");
            }
         });

      });
});

//CAMBIAR VALORES DEL PROFESIONAL AL SELECCIONAR LA UNIDAD DE SERVICIO
$(document).ready(function() {
	  $('#form_main #unidad').on('change', function(){
		var servicio_id = $('#form_main #servicio').val();
		var puesto_id = $('#form_main #unidad').val();
        var url = '<?php echo SERVERURL; ?>php/citas/getMedico.php';

		$.ajax({
            type: "POST",
            url: url,
			async: true,
            data:'servicio='+servicio_id+'&puesto_id='+puesto_id,
            success: function(data){
				$('#form_main #profesional').html(data);
            }
         });

      });
});

function pagination(partida){
	var url = '<?php echo SERVERURL; ?>php/sms/paginar.php';
	var fechai = $('#form_main #fecha_i').val();
	var fechaf = $('#form_main #fecha_f').val();
	var servicio;
	var unidad;
	var profesional;
	var usuario;
	var dias;
	var dato = $('#form_main #bs_regis').val();

	if ($('#form_main #servicio').val() == "" || $('#form_main #servicio').val() == null){
	  servicio = 1;
	}else{
	  servicio = $('#form_main #servicio').val();
	}

	if ($('#form_main #unidad').val() == "" || $('#form_main #unidad').val() == null){
	  unidad = '';
	}else{
	  unidad = $('#form_main #unidad').val();
	}

	if ($('#form_main #profesional').val() == "" || $('#form_main #profesional').val() == null){
	  profesional = '';
	}else{
	  profesional = $('#form_main #profesional').val();
	}

	if ($('#form_main #usuario').val() == "" || $('#form_main #usuario').val() == null){
	  usuario = '';
	}else{
	  usuario = $('#form_main #usuario').val();
	}

	if ($('#form_main #dias').val() == "" || $('#form_main #dias').val() == null){
	  dias = '';
	}else{
	  dias = $('#form_main #dias').val();
	}

	$.ajax({
		type:'POST',
		url:url,
		data:'partida='+partida+'&fechai='+fechai+'&fechaf='+fechaf+'&dato='+dato+'&servicio='+servicio+'&unidad='+unidad+'&profesional='+profesional+'&usuario='+usuario+'&dias='+dias,
		success:function(data){
			var array = eval(data);
			$('#agrega-registros').html(array[0]);
			$('#pagination').html(array[1]);
		}
	});
	return false;
}

function reporteEXCEL(){
	var fechai = $('#form_main #fecha_i').val();
	var fechaf = $('#form_main #fecha_f').val();
	var servicio;
	var unidad;
	var profesional;
	var usuario;
	var dias;
	var dato = $('#form_main #bs_regis').val();

	if ($('#form_main #servicio').val() == "" || $('#form_main #servicio').val() == null){
	  servicio = 1;
	}else{
	  servicio = $('#form_main #servicio').val();
	}

	if ($('#form_main #unidad').val() == "" || $('#form_main #unidad').val() == null){
	  unidad = '';
	}else{
	  unidad = $('#form_main #unidad').val();
	}

	if ($('#form_main #profesional').val() == "" || $('#form_main #profesional').val() == null){
	  profesional = '';
	}else{
	  profesional = $('#form_main #profesional').val();
	}

	if ($('#form_main #usuario').val() == "" || $('#form_main #usuario').val() == null){
	  usuario = '';
	}else{
	  usuario = $('#form_main #usuario').val();
	}

	if ($('#form_main #dias').val() == "" || $('#form_main #dias').val() == null){
	  dias = '';
	}else{
	  dias = $('#form_main #dias').val();
	}
	var url = '<?php echo SERVERURL; ?>php/sms/reporteSMS.php?dato='+dato+'&profesional='+profesional+'&usuario='+usuario+'&dias='+dias+'&fechai='+fechai+'&fechaf='+fechaf;
    window.open(url);
}

function reporteDiarioEXCEL(){
	var fechai = $('#form_main #fecha_i').val();
	var fechaf = $('#form_main #fecha_f').val();
	var profesional;
	var usuario;
	var dias;
	var dato = $('#form_main #bs_regis').val();

	if ($('#form_main #profesional').val() == "" || $('#form_main #profesional').val() == null){
	  profesional = '';
	}else{
	  profesional = $('#form_main #profesional').val();
	}

	if ($('#form_main #usuario').val() == "" || $('#form_main #usuario').val() == null){
	  usuario = '';
	}else{
	  usuario = $('#form_main #usuario').val();
	}

	if ($('#form_main #dias').val() == "" || $('#form_main #dias').val() == null){
	  dias = '';
	}else{
	  dias = $('#form_main #dias').val();
	}

	var url = '<?php echo SERVERURL; ?>php/sms/reporteDiarioSMS.php?profesional='+profesional+'&usuario='+usuario+'&dias='+dias+'&fechai='+fechai+'&fechaf='+fechaf;
    window.open(url);
}

function reporteDiarioColaboradorEXCEL(){
	var fechai = $('#form_main #fecha_i').val();
	var fechaf = $('#form_main #fecha_f').val();
	var profesional;
	var usuario;
	var dato = $('#form_main #bs_regis').val();

	if ($('#form_main #profesional').val() == "" || $('#form_main #profesional').val() == null){
	  profesional = '';
	}else{
	  profesional = $('#form_main #profesional').val();
	}

	if ($('#form_main #usuario').val() == "" || $('#form_main #usuario').val() == null){
	  usuario = '';
	}else{
	  usuario = $('#form_main #usuario').val();
	}

	var url = '<?php echo SERVERURL; ?>php/sms/reporteDiarioSMSColaborador.php?profesional='+profesional+'&usuario='+usuario+'&fechai='+fechai+'&fechaf='+fechaf;
    window.open(url);
}

function limpiar(){
	$('#form_main #servicio').html("");
	$('#form_main #unidad').html("");
	$('#form_main #profesional').html("");
	$('#form_main #usuario').html("");
    $('#form_main #agrega-registros').html("");
	$('#form_main #pagination').html("");
   getSercicio();
   getUsuario();
   pagination(1);
}

$('#form_main #limpiar').on('click', function(e){
    e.preventDefault();
    limpiar();
});

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
