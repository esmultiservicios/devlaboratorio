<script>
/*INICIO DE FUNCIONES PARA ESTABLECER EL FOCUS PARA LAS VENTANAS MODALES*/
$(document).ready(function(){
    $("#modal_adendum").on('shown.bs.modal', function(){
        $(this).find('#formularioAdendum #descripcion_adendum').focus();
    });
});
/*FIN DE FUNCIONES PARA ESTABLECER EL FOCUS PARA LAS VENTANAS MODALES*/

$(document).ready(function() {
   getServicio();
   getProfesionales();
   pagination(1);
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
    var url = '<?php echo SERVERURL; ?>php/reportes_atenciones_medicas/getServicio.php';		
		
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
	var colaborador = '';
	var desde = $('#form_main #fecha_i').val();
	var hasta = $('#form_main #fecha_f').val();
	var dato = $('#form_main #bs_regis').val();
	var url = '<?php echo SERVERURL; ?>php/reportes_atenciones_medicas/paginar.php';	
	
	if($('#form_main #colaborador').val() == "" || $('#form_main #colaborador').val() == null){
		colaborador = "";
	}else{
		colaborador = $('#form_main #colaborador').val();
	}

	$.ajax({
		type:'POST',
		url:url,
		data:'partida='+partida+'&desde='+desde+'&hasta='+hasta+'&colaborador='+colaborador+'&dato='+dato,	
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
	 
    url = '<?php echo SERVERURL; ?>php/reportes_atenciones_medicas/reporte.php?desde='+desde+'&hasta='+hasta+'&colaborador='+colaborador;
	
	window.open(url);
}

function reporteEXCELDiario(){		
	var colaborador = '';
	var desde = $('#form_main #fecha_i').val();
	var hasta = $('#form_main #fecha_f').val();
	var url = '';
	
	if($('#form_main #colaborador').val() == "" || $('#form_main #colaborador').val() == null){
		colaborador = "";
	}else{
		colaborador = $('#form_main #colaborador').val();
	}

	var url = '<?php echo SERVERURL; ?>php/reportes_atenciones_medicas/reporteDiarioAtenciones.php?desde='+desde+'&hasta='+hasta+'&colaborador='+colaborador;
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

function consultarNombre(id){	
    var url = '<?php echo SERVERURL; ?>php/reporte_hospitalizacion/getNombre.php';
	var resp;
		
	$.ajax({
	    type:'POST',
		url:url,
		data:'id='+id,
		async: false,
		success:function(data){	
          resp = data;			  		  		  			  
		}
	});
	return resp;		
}

$('#form_main #reporte_excel').on('click', function(e){
 if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 5){
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
 if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 5){
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


function addAdendum(atencion_id, muestras_id){
	if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2){
		$('#formularioAdendum #pro').val("Registro");
		$('#formularioAdendum #atencion_id').val(atencion_id);
		$('#formularioAdendum #muestras_id').val(muestras_id);	
		
		$('#formularioAdendum #paciente_bioxia_adendum').val(getPaciente(atencion_id));
		$('#formularioAdendum #numero_bioxia_adendum').val(getNumeroBioxia(muestras_id));
		$('#formularioAdendum #descripcion_adendum').val(consultarAdendum(atencion_id));
			 
		$('#formularioAdendum').attr({ 'data-form': 'save' }); 
		$('#formularioAdendum').attr({ 'action': '<?php echo SERVERURL; ?>php/reportes_atenciones_medicas/agregarAdendum.php' });
				 
		$('#modal_adendum').modal({
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
	}
}

$('#formularioAdendum #descripcion_adendum').keyup(function() {
	    var max_chars = 10000;
        var chars = $(this).val().length;
        var diff = max_chars - chars;
		
		$('#formularioAdendum #charNum_adendum').html(diff + ' Caracteres'); 
		
		if(diff == 0){
			return false;
		}
});

function caracteresAntecedentes(){
	var max_chars = 10000;
	var chars = $('#formularioAdendum #descripcion_adendum').val().length;
	var diff = max_chars - chars;
	
	$('#formularioAdendum #charNum_adendum').html(diff + ' Caracteres'); 
	
	if(diff == 0){
		return false;
	}
}

function getNumeroBioxia(muestras_id){
    var url = '<?php echo SERVERURL; ?>php/reportes_atenciones_medicas/getNumeroBioxia.php';
	var numero;
		
	$.ajax({
	    type:'POST',
		url:url,
		data:'muestras_id='+muestras_id,
		async: false,
		success:function(data){
		  var array = eval(data);
          numero = array[0];			  		  		  			  
		}
	});
	return numero;	
}

function getPaciente(atencion_id){
    var url = '<?php echo SERVERURL; ?>php/reportes_atenciones_medicas/getPaciente.php';
	var paciente;
		
	$.ajax({
	    type:'POST',
		url:url,
		data:'atencion_id='+atencion_id,
		async: false,
		success:function(data){
		  var array = eval(data);
          paciente = array[0];			  		  		  			  
		}
	});
	return paciente;	
}

//INICIO IMPRIMIR FACTURACION
function printReport(atencion_id){
	var url = '<?php echo SERVERURL; ?>php/atencion_pacientes/generarReporteLaboratorio.php?atencion_id='+atencion_id;
    window.open(url);
}
//FIN IMPRIMIR FACTURACION

function consultarAdendum(atencion_id){
    var url = '<?php echo SERVERURL; ?>php/reportes_atenciones_medicas/getPaciente.php';
	var descripcion;
		
	$.ajax({
	    type:'POST',
		url:url,
		data:'atencion_id='+atencion_id,
		async: false,
		success:function(data){
		  var array = eval(data);
          descripcion = array[0];			  		  		  			  
		}
	});
	return descripcion;		
}

//INICIO ENVIAR REPORTE DE LABORATORIO POR CORREO ELECTRONICO
function mailAtencion(atencion_id){
	swal({
	  title: "¿Estas seguro?",
	  text: "¿Desea enviar este reporte de laboratorio con número de muestra: # " + getNumeroMuestra(atencion_id) + "?",
	  type: "info",
	  showCancelButton: true,
	  confirmButtonClass: "btn-primary",
	  confirmButtonText: "¡Sí, enviar el reporte!",
	  cancelButtonText: "Cancelar",
	  closeOnConfirm: false
	},
	function(){
		sendMailAtencion(atencion_id);
	});				
}

function sendMailAtencion(atencion_id){
	var url = '<?php echo SERVERURL; ?>php/atencion_pacientes/correo_reporteAtencion.php';
	var bill = '';
	
	$.ajax({
	   type:'POST',
	   url:url,
	   async: false,
	   data:'atencion_id='+atencion_id,	 
	   success:function(data){
	      bill = data;
	      if(bill == 1){
				swal({
					title: "Success", 
					text: "El reporte de laboratorio ha sido enviada por correo satisfactoriamente",
					type: "success", 
				});	
		  }
	  }
	});
	return bill;	
}

function getNumeroMuestra(atencion_id){
	var url = '<?php echo SERVERURL; ?>php/atencion_pacientes/getNoMuestra.php';
	var noFactura = '';
	
	$.ajax({
	   type:'POST',
	   url:url,
	   async: false,
	   data:'atencion_id='+atencion_id,	   
	   success:function(data){
			var datos = eval(data);	   
			noFactura = datos[0];
	  }
	});
	return noFactura;	
}
//FIN ENVIAR FACTURA POR CORREO ELECTRONICO
</script>