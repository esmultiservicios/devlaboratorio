<script>
$(document).ready(function(){
	$('#form_main #nuevo-registro').on('click',function(){
		if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
			getGenero();
			getDepartamento();
			getMunicipio();
			$('#formulario_pacientes #reg').show();
			$('#formulario_pacientes #edi').hide();
			cleanPacientes();
			$('#formulario_pacientes #grupo_expediente').hide();
			$('#formulario_pacientes')[0].reset();
			$('#formulario_pacientes #pro').val('Registro');
			$("#formulario_pacientes #fecha").attr('readonly', false);
			$('#formulario_pacientes #rtn').attr('readonly',false);
			$('#formulario_pacientes #validate').removeClass('bien_email');
			$('#formulario_pacientes #validate').removeClass('error_email');
			$("#formulario_pacientes #correo").css("border-color", "none");
			$('#formulario_pacientes #validate').html('');
			$('#formulario_pacientes').attr({ 'data-form': 'save' });
			$('#formulario_pacientes').attr({ 'action': '<?php echo SERVERURL; ?>php/pacientes/agregarPacientes.php' });
			$('#modal_pacientes').modal({
				show:true,
				keyboard: false,
				backdrop:'static'
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
	});

	$('#form_main #profesion').on('click',function(){
		if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
			$('#formulario_profesiones #reg').show();
			$('#formulario_profesiones #edi').hide();
			$('#formulario_profesiones')[0].reset();
			$('#formulario_profesiones #proceso').val('Registro');
			paginationPorfesionales(1);
			$('#formulario_profesiones').attr({ 'data-form': 'save' });
			$('#formulario_profesiones').attr({ 'action': '<?php echo SERVERURL; ?>php/pacientes/agregar_profesional.php' });
			 $('#modal_profesiones').modal({
				show:true,
				keyboard: false,
				backdrop:'static'
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
	});

	$('#form_main #bs_regis').on('keyup',function(){
	  pagination(1);
	});

	$('#formulario_profesiones #profesionales_buscar').on('keyup',function(){
	  paginationPorfesionales(1);
	});

	$('#form_main #estado').on('change',function(){
	  pagination(1);
	});

	$('#form_main #tipo_paciente_id').on('change',function(){
	  pagination(1);
	});

	$('#formulario_agregar_expediente_manual #identidad_ususario_manual').on('keyup',function(){
		busquedaUsuarioManualIdentidad();
    });

	$('#formulario_agregar_expediente_manual #expediente_usuario_manual').on('keyup',function(){
		busquedaUsuarioManualExpediente();
    });
});

$('#form_main_historico_muestras #bs_regis').on('keyup',function(){
	if(getPacienteTipo($('#form_main_historico_muestras #pacientes_id_muestras').val()) == 1){
		historiaMuestrasPacientes(1);
	}else{
		historiaMuestrasEmpresas(1);
	}
});

function getGenero(){
  var url = '<?php echo SERVERURL; ?>php/admision/getSexo.php';

  $.ajax({
 	 type:'POST',
	 url:url,
		success: function(data){
			$('#formulario_pacientes #sexo').html("");
			$('#formulario_pacientes #sexo').html(data);
			$('#formulario_pacientes #sexo').selectpicker('refresh');
		}
   });
   return false;
}

/*INICIO DE FUNCIONES PARA ESTABLECER EL FOCUS PARA LAS VENTANAS MODALES*/
$(document).ready(function(){
    $("#modal_pacientes").on('shown.bs.modal', function(){
        $(this).find('#formulario_pacientes #name').focus();
    });
});

$(document).ready(function(){
    $("#modal_profesiones").on('shown.bs.modal', function(){
        $(this).find('#formulario_profesiones #profesionales_buscar').focus();
    });
});

$(document).ready(function(){
    $("#agregar_expediente_manual").on('shown.bs.modal', function(){
        $(this).find('#formulario_agregar_expediente_manual #identidad_ususario_manual').focus();
    });
});

$(document).ready(function(){
    $("#modal_busqueda_departamentos").on('shown.bs.modal', function(){
        $(this).find('#formulario_busqueda_departamentos #buscar').focus();
    });
});

$(document).ready(function(){
    $("#modal_busqueda_municipios").on('shown.bs.modal', function(){
        $(this).find('#formulario_busqueda_municipios #buscar').focus();
    });
});

$(document).ready(function(){
    $("#modal_busqueda_profesion").on('shown.bs.modal', function(){
        $(this).find('#formulario_busqueda_profesion #buscar').focus();
    });
});

$(document).ready(function(){
    $("#modal_busqueda_religion").on('shown.bs.modal', function(){
        $(this).find('#formulario_busqueda_religion #buscar').focus();
    });
});

$(document).ready(function(){
    $("#modal_historico_muestras").on('shown.bs.modal', function(){
        $(this).find('#form_main_historico_muestras #bs_regis').focus();
    });
});
/*FIN DE FUNCIONES PARA ESTABLECER EL FOCUS PARA LAS VENTANAS MODALES*/

$('#reg_manual').on('click', function(e){ // delete event clicked // We don't want this to act as a link so cancel the link action
   e.preventDefault();
   if ($('#formulario_agregar_expediente_manual #expediente_usuario_manual').val()!="" || $('#formulario_agregar_expediente_manual #identidad_ususario_manual').val() !=""){
	  registrarExpedienteManual();
   }else{
		swal({
			title: "Error",
			text: "Hay registros en blanco, por favor corregir",
			type: "error",
			confirmButtonClass: 'btn-danger'
		});
	   return false;
   }
});

$('#convertir_manual').on('click', function(e){ // add event submit We don't want this to act as a link so cancel the link action
	 e.preventDefault();
	 if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
	     convertirExpedientetoTemporal();
	 }else{
		  swal({
				title: 'Acceso Denegado',
				text: 'No tiene permisos para ejecutar esta acción',
				type: 'error',
				confirmButtonClass: 'btn-danger'
		  });
	}
});

$('#form_main #reporte').on('click', function(e){
    e.preventDefault();
    reporteEXCEL();
});

function reporteEXCEL(){
 if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
	var estado = "";
	var dato = $('#form_main #bs_regis').val();

	if ($('#estado').val() == ""){
		estado = 1;
	}else{
		estado = $('#estado').val();
	}

	var url = '<?php echo SERVERURL; ?>php/pacientes/reportePacientes.php?dato='+dato+'&estado='+estado;
    window.open(url);
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

function asignarExpedienteaRegistro(pacientes_id){
	var url = '<?php echo SERVERURL; ?>php/pacientes/agregar_expediente.php';

	$.ajax({
		type:'POST',
		url:url,
		data:'pacientes_id='+pacientes_id,
		success: function(registro){
			swal.close();
			showExpediente(pacientes_id);
			pagination(1);
			return false;
		}
	});
	return false;
}

function showExpediente(pacientes_id){
	var url = '<?php echo SERVERURL; ?>php/pacientes/getExpediente.php';

	$.ajax({
		type:'POST',
		url:url,
		data:'pacientes_id='+pacientes_id,
		success:function(data){
			if(data == 1){
				swal({
					title: "Error",
					text: "Por favor intentelo de nuevo más tarde",
					type: "error",
					confirmButtonClass: 'btn-danger'
				});
			}else{
  	           $('#mensaje_show').modal({
				show:true,
				keyboard: false,
				backdrop:'static'
     	       });
               $('#mensaje_mensaje_show').html(data);
	           $('#bad').hide();
	           $('#okay').show();
			}
		}
	});
}

function modal_eliminarProfesional(profesional_id){
	if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
		swal({
		  title: "¿Estas seguro?",
		  text: "¿Desea eliminar este registro?",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-warning",
		  confirmButtonText: "¡Sí, eliminar el registro!",
		  cancelButtonText: "Cancelar",
		  closeOnConfirm: false
		},
		function(){
			eliminarProfesional(profesional_id);
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

function modal_eliminar(pacientes_id){
  if (consultarExpediente(pacientes_id) != 0 && (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3)){
		var nombre_usuario = consultarNombre(pacientes_id);
		var expediente_usuario = consultarExpediente(pacientes_id);
		var dato;

		if(expediente_usuario == 0){
			dato = nombre_usuario;
		}else{
			dato = nombre_usuario + " (Expediente: " + expediente_usuario + ")";
		}

		swal({
			title: "¿Estas seguro?",
			text: "¿Desea eliminar este cliente: " + dato + "?",
			type: "input",
			showCancelButton: true,
			closeOnConfirm: false,
			inputPlaceholder: "Comentario",
			cancelButtonText: "Cancelar",
			confirmButtonText: "¡Sí, eliminar el cliente!",
			confirmButtonClass: "btn-warning"
		}, function (inputValue) {
			if (inputValue === false) return false;
			if (inputValue === "") {
			swal.showInputError("¡Necesita escribir algo!");
			return false
			}
			eliminarRegistro(pacientes_id, inputValue);
		});
  }else if (consultarExpediente(pacientes_id) == 0 && (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3)){
		var nombre_usuario = consultarNombre(pacientes_id);
		var expediente_usuario = consultarExpediente(pacientes_id);
		var dato;

		if(expediente_usuario == 0){
			dato = nombre_usuario;
		}else{
			dato = nombre_usuario + " (Expediente: " + expediente_usuario + ")";
		}

		swal({
			title: "¿Estas seguro?",
			text: "¿Desea eliminar este cliente: " + dato + "?",
			type: "input",
			showCancelButton: true,
			closeOnConfirm: false,
			inputPlaceholder: "Comentario",
			cancelButtonText: "Cancelar",
			confirmButtonText: "¡Sí, eliminar el cliente!",
			confirmButtonClass: "btn-warning"
		}, function (inputValue) {
			if (inputValue === false) return false;
			if (inputValue === "") {
			swal.showInputError("¡Necesita escribir algo!");
			return false
			}
			eliminarRegistro(pacientes_id, inputValue);
		});
  }else{
	  swal({
			title: 'Acceso Denegado',
			text: 'No tiene permisos para ejecutar esta acción',
			type: 'error',
			confirmButtonClass: 'btn-danger'
	  });
	 return false;
  }
}

function editarRegistro(pacientes_id){
	if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
		var url = '<?php echo SERVERURL; ?>php/pacientes/editar.php';
		   $.ajax({
			   type:'POST',
			   url:url,
			   data:'pacientes_id='+pacientes_id,
			   success: function(valores){
					var datos = eval(valores);
					$('#formulario_pacientes #reg').hide();
					$('#formulario_pacientes #edi').show();
					$('#formulario_pacientes #pro').val('Edición');
					$('#formulario_pacientes #grupo_expediente').hide();
					$('#formulario_pacientes #pacientes_id').val(pacientes_id);
					$('#formulario_pacientes #name').val(datos[0]);
					$('#formulario_pacientes #lastname').val(datos[1]);
					$('#formulario_pacientes #telefono1').val(datos[2]);
					$('#formulario_pacientes #telefono2').val(datos[3]);
					$('#formulario_pacientes #sexo').val(datos[4]);
					$('#formulario_pacientes #sexo').selectpicker('refresh');
					$('#formulario_pacientes #correo').val(datos[5]);
					$('#formulario_pacientes #edad_editar').val(datos[6]);
					$('#formulario_pacientes #expediente').val(datos[7]);
					$('#formulario_pacientes #departamento_id').val(datos[8]);
					$('#formulario_pacientes #departamento_id').selectpicker('refresh');
					getMunicipioEditar(datos[8], datos[9]);
					/*$('#formulario_pacientes #municipio_id').val(datos[9]);
					$('#formulario_pacientes #municipio_id').selectpicker('refresh');*/
					$('#formulario_pacientes #direccion').val(datos[10]);
					$('#formulario_pacientes #rtn').val(datos[11]);
					$('#formulario_pacientes #religion').val(datos[12]);
					$('#formulario_pacientes #profesion').val(datos[13]);
					$('#formulario_pacientes #edad').val(datos[14]);
					$('#formulario_pacientes #paciente_tipo').val(datos[15]);
					$('#formulario_pacientes #paciente_tipo').selectpicker('refresh');
					$('#formulario_pacientes #rtn').attr('readonly',true);
					$("#formulario_pacientes #fecha").attr('readonly', true);
					$("#formulario_pacientes #expediente").attr('readonly', true);
					$('#formulario_pacientes #validate').removeClass('bien_email');
					$('#formulario_pacientes #validate').removeClass('error_email');
					$("#formulario_pacientes #correo").css("border-color", "none");
					$('#formulario_pacientes #validate').html('');
					cleanPacientes();
					$('#formulario_pacientes').attr({ 'data-form': 'update' });
					$('#formulario_pacientes').attr({ 'action': '<?php echo SERVERURL; ?>php/pacientes/editarPacientes.php' });
					$('#modal_pacientes').modal({
						show:true,
						keyboard: false,
						backdrop:'static'
					});
			   return false;
			}
		});
	}else{
		swal({
			title: 'Acceso Denegado',
			text: 'No tiene permisos para ejecutar esta acción',
			type: 'error',
			confirmButtonClass: 'btn-danger'
		});
		return false;
	}
}

function eliminarProfesional(id){
	var url = '<?php echo SERVERURL; ?>php/pacientes/eliminar_profesional.php';
	$.ajax({
		type:'POST',
		url:url,
		data:'id='+id,
		success: function(registro){
			if(registro == 1){
				swal({
					title: "Success",
					text: "Registro eliminado correctamente",
					type: "success",
					timer: 3000, //timeOut for auto-clos
				});
				paginationPorfesionales(1);
				$('#modal_profesiones').modal('hide');
			   return false;
			}else if(registro == 2){
				swal({
					title: "Error",
					text: "No se puede eliminar este registro",
					type: "error",
					confirmButtonClass: 'btn-danger'
				});
	           return false;
			}else if(registro == 3){
				swal({
					title: "Error",
					text: "No se puede eliminar este registro, cuenta con información almacenada",
					type: "error",
					confirmButtonClass: 'btn-danger'
				});
	           return false;
			}else{
				swal({
					title: "Error",
					text: "Error al completar el registro",
					type: "error",
					confirmButtonClass: 'btn-danger'
				});
	           return false;
			}
  		}
	});
	return false;
}

function eliminarRegistro(pacientes_id, comentario){
	var url = '<?php echo SERVERURL; ?>php/admision/eliminar.php';
	$.ajax({
		type:'POST',
		url:url,
		data:'id='+pacientes_id+'&comentario='+comentario,
		success: function(registro){
			if(registro == 1){
				swal({
					title: "Success",
					text: "Registro eliminado correctamente",
					type: "success",
					timer: 3000, //timeOut for auto-clos
				});
				pagination(1);
			   return false;
			}else if(registro == 2){
				swal({
					title: "Error",
					text: "No se puede eliminar este registro",
					type: "error",
					confirmButtonClass: 'btn-danger'
				});
	           return false;
			}else if(registro == 3){
				swal({
					title: "Error",
					text: "No se puede eliminar este registro, cuenta con información almacenada",
					type: "error",
					confirmButtonClass: 'btn-danger'
				});
	           return false;
			}else{
				swal({
					title: "Error",
					text: "Error al completar el registro",
					type: "error",
					confirmButtonClass: 'btn-danger'
				});
	           return false;
			}
  		}
	});
	return false;
}

function convertirExpedientetoTemporal(){
    var url = '<?php echo SERVERURL; ?>php/pacientes/convertirExpedienteTemporal.php';
    var pacientes_id = $('#formulario_agregar_expediente_manual #pacientes_id').val();

	$.ajax({
        type: "POST",
        url: url,
	    data:'pacientes_id='+pacientes_id,
	    async: true,
        success: function(data){
            if(data == 1){
				swal({
					title: "Usuario convertido",
					text: "El usuario se ha convertido a temporal",
					type: "success",
					timer: 3000, //timeOut for auto-close
				});
				$('#agregar_expediente_manual').modal('hide');
			    $('#formulario_agregar_expediente_manual #expediente_manual').val('TEMP');
			    $('#formulario_agregar_expediente_manual #temporal').hide();
			    $('#convertir_manual').hide();
			    $('#reg_manual').show();
                pagination(1);
	            return false;
			}else{
				swal({
					title: "Error",
					text: "No se puede procesar su solicitud",
					type: "error",
					confirmButtonClass: "btn-danger"
				});
                return false;
			}
		}
     });
}

function registrarExpedienteManual(){
	var url = '<?php echo SERVERURL; ?>php/pacientes/agregarExpedienteManual.php';

	$.ajax({
		type:'POST',
		url:url,
		data:$('#formulario_agregar_expediente_manual').serialize(),
		success: function(registro){
		   if(registro==1){
			   $('#formulario_agregar_expediente_manual #pro_manual').val('Registro');
				swal({
					title: "Success",
					text: "Registro completado correctamente",
					type: "success",
					timer: 3000, //timeOut for auto-clos
				});
				$('#agregar_expediente_manual').modal('hide');
				pagination(1);
		   }else if(registro==2){
				swal({
					title: "Error",
					text: "No se pudo guardar el registro, por favor verifique la información",
					type: "error",
					confirmButtonClass: 'btn-danger'
				});
		   }else if(registro==3){
				swal({
					title: "Error",
					text: "Error al ejecutar esta acción",
					type: "error",
					confirmButtonClass: 'btn-danger'
				});
		   }else if(registro==4){
				swal({
					title: "Error",
					text: "Error en los datos",
					type: "error",
					confirmButtonClass: 'btn-danger'
				});
		   }else{
				swal({
					title: "Error",
					text: "Error al guardar el registro",
					type: "error",
					confirmButtonClass: 'btn-danger'
				});
		   }
		}
	   });
	  return false;
}

function busquedaUsuarioManualIdentidad(){
	var url = '<?php echo SERVERURL; ?>php/pacientes/consultarIdentidad.php';

	var identidad = $('#formulario_agregar_expediente_manual #identidad_ususario_manual').val();

   $.ajax({
	  type:'POST',
	  url:url,
	  data:'identidad='+identidad,
	  success:function(data){
		 if(data == 1){
			swal({
				title: "Error",
				text: "Este numero de Identidad ya existe, por favor corriga el numero e intente nuevamente",
				type: "error",
				confirmButtonClass: "btn-danger"
			});
			 $("#formulario_agregar_expediente_manual #reg").attr('disabled', true);
			 return false;
		 }else{
			 $("#formulario_agregar_expediente_manual #reg").attr('disabled', false);
		}
	}
   });
}

function busquedaUsuarioManualExpediente(){
	var url = '<?php echo SERVERURL; ?>php/pacientes/consultarExpediente.php';

	var expediente = $('#formulario_agregar_expediente_manual #expediente_usuario_manual').val();

   $.ajax({
	  type:'POST',
	  url:url,
	  data:'expediente='+expediente,
	  success:function(data){
		 if(data == 1){
			swal({
				title: "Error",
				text: "Este numero de Expediente ya existe, por favor corriga el numero e intente nuevamente",
				type: "error",
				confirmButtonClass: "btn-danger"
			});
			$("#formulario_agregar_expediente_manual #reg").attr('disabled', true);
			return false;
		 }else{
			$("#formulario_agregar_expediente_manual #reg").attr('disabled', false);
		}
	  }
   });
}

function consultarExpediente(pacientes_id){
    var url = '<?php echo SERVERURL; ?>php/pacientes/getExpedienteInformacion.php';
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

function getPacienteTipo(pacientes_id){
	var url = '<?php echo SERVERURL; ?>php/admision/getPacienteTipo.php';
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

function getTipoPaciente(pacientes_id){
    var url = '<?php echo SERVERURL; ?>php/muestras/getTipoPaciente.php';
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

function modal_muestras(pacientes_id){
   if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
		$('#form_main_historico_muestras #pacientes_id_muestras').val(pacientes_id);
		$('#modal_historico_muestras').modal({
			show:true,
			keyboard: false,
			backdrop:'static'
		});

		var tipo = getPacienteTipo(pacientes_id);

		if(tipo == 1){
			historiaMuestrasPacientes(1);
		}else{
			historiaMuestrasEmpresas(1);
		}
   }else{
		swal({
			title: "Acceso Denegado",
			text: "No tiene permisos para ejecutar esta acción",
			type: "error",
			confirmButtonClass: 'btn-danger'
		});
	}
}

function modal_agregar_expediente_manual(id, expediente){
   if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
	  $('#formulario_agregar_expediente_manual')[0].reset();
	  var url = '<?php echo SERVERURL; ?>php/pacientes/buscarUsuario.php';
		$.ajax({
		type:'POST',
		url:url,
		data:'id='+id,
		success: function(valores){
			var datos = eval(valores);
			if(expediente == 0){
				$("#formulario_agregar_expediente_manual #temporal").hide();
			}else{
				$("#formulario_agregar_expediente_manual #temporal").show();
			}
			$("#formulario_agregar_expediente_manual #pacientes_id").val(id);
			$("#formulario_agregar_expediente_manual #expediente").val(expediente);
			$("#formulario_agregar_expediente_manual #name_manual").val(datos[0]);
			$("#formulario_agregar_expediente_manual #identidad_manual").val(datos[1]);
			$('#formulario_agregar_expediente_manual #sexo_manual').val(datos[2]);
			$('#formulario_agregar_expediente_manual #sexo_manual').selectpicker('refresh');
			$("#formulario_agregar_expediente_manual #fecha_manual").val(datos[3]);
			$("#formulario_agregar_expediente_manual #edad_manual").val(datos[6]);
			$("#formulario_agregar_expediente_manual #expediente_manual").val(datos[5]);
			$("#formulario_agregar_expediente_manual #edad_manual").show();
			$('#formulario_agregar_expediente_manual #pro').val('Registrar');
			$("#formulario_agregar_expediente_manual #sexo_manual").attr("disabled", true);
			$("#formulario_agregar_expediente_manual #fecha_re_manual").attr("readonly", true);

			$("#reg_manual").show();
			$("#convertir_manual").hide();
			$('#agregar_expediente_manual').modal({
				show:true,
				keyboard: false,
				backdrop:'static'
			});
			return false;
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
 }

function modal_agregar_expediente(pacientes_id, expediente){
    var nombre_usuario = consultarNombre(pacientes_id);
    var expediente_usuario = consultarExpediente(pacientes_id);
    var dato;

    if(expediente_usuario == 0){
		dato = nombre_usuario;
	}else{
		dato = nombre_usuario + " (Expediente: " + expediente_usuario + ")";
	}

    if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
	     if (expediente == "" || expediente == 0){
				swal({
				  title: "¿Estas seguro?",
				  text: "¿Desea asignarle un número de expediente a este usuario:" + dato + "?",
				  type: "warning",
				  showCancelButton: true,
				  confirmButtonClass: "btn-warning",
				  confirmButtonText: "¡Sí, Asignar el expediente!",
				  cancelButtonText: "Cancelar",
				  closeOnConfirm: false
				},
				function(){
					asignarExpedienteaRegistro(pacientes_id);
				});
	     }else{
			swal({
				title: "Error",
				text: "Este usuario: " + dato + " ya tiene un expediente asignado",
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
		return false;
    }
}

function paginationPorfesionales(partida){
	var url = '<?php echo SERVERURL; ?>php/pacientes/paginarProfesionales.php';
	var profesional = $('#formulario_profesiones #profesionales_buscar').val();

	$.ajax({
		type:'POST',
		url:url,
		data:'partida='+partida+'&profesional='+profesional,
		success:function(data){
			var array = eval(data);
			$('#agrega_registros_profesionales').html(array[0]);
			$('#pagination_profesionales').html(array[1]);
		}
	});
	return false;
}

function pagination(partida){
	var url = '<?php echo SERVERURL; ?>php/pacientes/paginar.php';
	var estado = "";
	var paciente = "";
	var tipo_paciente = "";
	var dato = $('#form_main #bs_regis').val();

	if ($('#form_main #estado').val() == "" || $('#form_main #estado').val() == null){
		estado = 1;
	}else{
		estado = $('#form_main #estado').val();
	}

	if ($('#form_main #tipo').val() == "" || $('#form_main #tipo').val() == null){
		paciente = 1;
	}else{
		paciente = $('#form_main #tipo').val();
	}

	if ($('#form_main #tipo_paciente_id').val() == "" || $('#form_main #tipo_paciente_id').val() == null){
		tipo_paciente = 1;
	}else{
		tipo_paciente = $('#form_main #tipo_paciente_id').val();
	}

	$.ajax({
		type:'POST',
		url:url,
		data:'partida='+partida+'&estado='+estado+'&dato='+dato+'&paciente='+paciente+'&tipo_paciente='+tipo_paciente,
		success:function(data){
			var array = eval(data);
			$('#agrega-registros').html(array[0]);
			$('#pagination').html(array[1]);
		}
	});
	return false;
}

function historiaMuestrasPacientes(partida){
	var url = '<?php echo SERVERURL; ?>php/admision/paginar_historico_muestras_pacientes.php';
	var url = '<?php echo SERVERURL; ?>php/admision/paginar_historico_muestras_pacientes.php';
	var pacientes_id = $('#form_main_historico_muestras #pacientes_id_muestras').val();
	var dato = $('#form_main_historico_muestras #bs_regis').val();

	$.ajax({
		type:'POST',
		url:url,
		async: true,
		data:'partida='+partida+'&pacientes_id='+pacientes_id+'&dato='+dato,
		success:function(data){
			var array = eval(data);
			$('#detalles-historico-muestras').html(array[0]);
			$('#pagination-historico-muestras').html(array[1]);
		}
	});
	return false;
}


function historiaMuestrasEmpresas(partida){
	var url = '<?php echo SERVERURL; ?>php/admision/paginar_historico_muestras_empresas.php';
	var pacientes_id = $('#modal_historico_muestras #pacientes_id_muestras').val();
	var dato = $('#form_main_historico_muestras #bs_regis').val();

	$.ajax({
		type:'POST',
		url:url,
		async: true,
		data:'partida='+partida+'&pacientes_id='+pacientes_id+'&dato='+dato,
		success:function(data){
			var array = eval(data);
			$('#detalles-historico-muestras').html(array[0]);
			$('#pagination-historico-muestras').html(array[1]);
		}
	});
	return false;
}
/*INICIO AUTO COMPLETAR*/
/*INICIO SUGGESTION NOMBRE*/
$(document).ready(function() {
   $('#formulario_pacientes #name').on('keyup', function() {
	   if($('#formulario_pacientes #name').val() != ""){
		     var key = $(this).val();
             var dataString = 'key='+key;
		     var url = '<?php echo SERVERURL; ?>php/pacientes/autocompletarNombre.php';

	        $.ajax({
               type: "POST",
               url: url,
               data: dataString,
               success: function(data) {
                  //Escribimos las sugerencias que nos manda la consulta
                  $('#formulario_pacientes #suggestions_name').fadeIn(1000).html(data);
                  //Al hacer click en algua de las sugerencias
                  $('.suggest-element').on('click', function(){
                        //Obtenemos la id unica de la sugerencia pulsada
                        var id = $(this).attr('id');
                        //Editamos el valor del input con data de la sugerencia pulsada
                        $('#formulario_pacientes #name').val($('#'+id).attr('data'));
                        //Hacemos desaparecer el resto de sugerencias
                        $('#formulario_pacientes #suggestions_name').fadeOut(1000);
                        return false;
                 });
              }
           });
	   }else{
		   $('#formulario_pacientes#suggestions_name').fadeIn(1000).html("");
		   $('#formulario_pacientes #suggestions_name').fadeOut(1000);
	   }
     });
});

//OCULTAR EL SUGGESTION
$(document).ready(function() {
   $('#formulario_pacientes #name').on('blur', function() {
	   $('#formulario_pacientes #suggestions_name').fadeOut(1000);
   });
});

$(document).ready(function() {
   $('#formulario_pacientes #name').on('click', function() {
	   if($('#formulario_pacientes #name').val() != ""){
		     var key = $(this).val();
             var dataString = 'key='+key;
		     var url = '<?php echo SERVERURL; ?>php/pacientes/autocompletarNombre.php';

	        $.ajax({
               type: "POST",
               url: url,
               data: dataString,
               success: function(data) {
                  //Escribimos las sugerencias que nos manda la consulta
                  $('#formulario_pacientes #suggestions_name').fadeIn(1000).html(data);
                  //Al hacer click en algua de las sugerencias
                  $('.suggest-element').on('click', function(){
                        //Obtenemos la id unica de la sugerencia pulsada
                        var id = $(this).attr('id');
                        //Editamos el valor del input con data de la sugerencia pulsada
                        $('#formulario_pacientes #name').val($('#'+id).attr('data'));
                        //Hacemos desaparecer el resto de sugerencias
                        $('#formulario_pacientes #suggestions_name').fadeOut(1000);
                        return false;
                 });
              }
           });
	   }else{
		   $('#formulario_pacientes#suggestions_name').fadeIn(1000).html("");
		   $('#formulario_pacientes #suggestions_name').fadeOut(1000);
	   }
     });
});
/*FIN SUGGESTION NOMBRE*/

/*INICIO SUGGESTION APELLIDO*/
$(document).ready(function() {
   $('#formulario_pacientes #lastname').on('keyup', function() {
	   if($('#formulario_pacientes #lastname').val() != ""){
		     var key = $(this).val();
             var dataString = 'key='+key;
		     var url = '<?php echo SERVERURL; ?>php/pacientes/autocompletarNombre.php';

	        $.ajax({
               type: "POST",
               url: url,
               data: dataString,
               success: function(data) {
                  //Escribimos las sugerencias que nos manda la consulta
                  $('#formulario_pacientes #suggestions_apellido').fadeIn(1000).html(data);
                  //Al hacer click en algua de las sugerencias
                  $('.suggest-element').on('click', function(){
                        //Obtenemos la id unica de la sugerencia pulsada
                        var id = $(this).attr('id');
                        //Editamos el valor del input con data de la sugerencia pulsada
                        $('#formulario_pacientes #lastname').val($('#'+id).attr('data'));
                        //Hacemos desaparecer el resto de sugerencias
                        $('#formulario_pacientes #suggestions_apellido').fadeOut(1000);
                        return false;
                 });
              }
           });
	   }else{
		   $('#formulario_pacientes#suggestions_apellido').fadeIn(1000).html("");
		   $('#formulario_pacientes #suggestions_apellido').fadeOut(1000);
	   }
     });
});

//OCULTAR EL SUGGESTION
$(document).ready(function() {
   $('#formulario_pacientes #lastname').on('blur', function() {
	   $('#formulario_pacientes #suggestions_apellido').fadeOut(1000);
   });
});

$(document).ready(function() {
   $('#formulario_pacientes #lastname').on('cli', function() {
	   if($('#formulario_pacientes #lastname').val() != ""){
		     var key = $(this).val();
             var dataString = 'key='+key;
		     var url = '<?php echo SERVERURL; ?>php/pacientes/autocompletarNombre.php';

	        $.ajax({
               type: "POST",
               url: url,
               data: dataString,
               success: function(data) {
                  //Escribimos las sugerencias que nos manda la consulta
                  $('#formulario_pacientes #suggestions_apellido').fadeIn(1000).html(data);
                  //Al hacer click en algua de las sugerencias
                  $('.suggest-element').on('click', function(){
                        //Obtenemos la id unica de la sugerencia pulsada
                        var id = $(this).attr('id');
                        //Editamos el valor del input con data de la sugerencia pulsada
                        $('#formulario_pacientes #lastname').val($('#'+id).attr('data'));
                        //Hacemos desaparecer el resto de sugerencias
                        $('#formulario_pacientes #suggestions_apellido').fadeOut(1000);
                        return false;
                 });
              }
           });
	   }else{
		   $('#formulario_pacientes#suggestions_apellido').fadeIn(1000).html("");
		   $('#formulario_pacientes #suggestions_apellido').fadeOut(1000);
	   }
     });
});
/*FIN SUGGESTION APELLIDO*/
/*FIN AUTO COMPLETAR*/

function convertDate(inputFormat) {
  function pad(s) { return (s < 10) ? '0' + s : s; }
  var d = new Date(inputFormat);
return [d.getFullYear(), pad(d.getMonth()+1), pad(d.getDate())].join('-');
}

//SÍ
$(document).ready(function() {
	$('#formulario_agregar_expediente_manual #respuestasi').on('click', function(){
        $("#convertir_manual").show();
		$("#reg_manual").hide();
    });
});

//NO
$(document).ready(function() {
	$('#formulario_agregar_expediente_manual #respuestano').on('click', function(){
		$("#convertir_manual").hide();
		$("#reg_manual").show();
    });
});
</script>
