<script>
/*INICIO DE FUNCIONES PARA ESTABLECER EL FOCUS PARA LAS VENTANAS MODALES*/
$(document).ready(function(){
    $("#modalEliminarSecuenciaFacturacion").on('shown.bs.modal', function(){
        $(this).find('#formularioSecuenciaFacturacion #comentario').focus();
    });
});

$(document).ready(function(){
    $("#secuenciaFacturacion").on('shown.bs.modal', function(){
        $(this).find('#formularioSecuenciaFacturacion #cai').focus();
    });
});
/*FIN DE FUNCIONES PARA ESTABLECER EL FOCUS PARA LAS VENTANAS MODALES*/
/****************************************************************************************************************************************************************/
//INICIO CONTROLES DE ACCION
$(document).ready(function() {
	//LLAMADA A LAS FUNCIONES
	funciones();
	getDocumento();
	
	//INICIO ABRIR VENTANA MODAL PARA EL REGISTRO DE DESCUENTOS
	$('#form_main #nuevo_registro').on('click',function(e){
		e.preventDefault();
		funciones();
		getDocumento1();
		if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 4){	
		    $('#formularioSecuenciaFacturacion')[0].reset();
            limpiarSeciencia();	
            //HABILITAR CONTROLES PARA SOLO LECTURA
			$("#formularioSecuenciaFacturacion #cai").attr('disabled', false);
			$("#formularioSecuenciaFacturacion #empresa").attr('disabled', false);
			$("#formularioSecuenciaFacturacion #documento_id").attr('disabled', false);
			$("#formularioSecuenciaFacturacion #prefijo").attr('readonly', false);
			$("#formularioSecuenciaFacturacion #relleno").attr('readonly', false);
			$("#formularioSecuenciaFacturacion #incremento").attr('readonly', false);
			$("#formularioSecuenciaFacturacion #rango_inicial").attr('readonly', false);
			$("#formularioSecuenciaFacturacion #rango_final").attr('readonly', false);
			$("#formularioSecuenciaFacturacion #fecha_activacion").attr('readonly', false);			
			$("#formularioSecuenciaFacturacion #fecha_limite").attr('readonly', false);
			$("#formularioSecuenciaFacturacion #siguiente").attr('readonly', false);
			$("#formularioSecuenciaFacturacion #comentario").attr('readonly', false);
				
			 $('#reg').show();
			 $('#edi').hide(); 
			 $('#delete').hide(); 			 
			 $('#formularioSecuenciaFacturacion #group_comentario').hide();

			// Eliminar la opción cero del select
			$('#formularioSecuenciaFacturacion #documento_id option[value="0"]').remove();

			 $('#secuenciaFacturacion').modal({
				show:true,
				keyboard: false,
				backdrop:'static'
			});
			return false;
		}else{
			// Antes era swal de error; ahora notificación
			showNotify("error", "Acceso denegado", "No tiene permisos para ejecutar esta acción");
		}			
	});
	//FIN ABRIR VENTANA MODAL PARA EL REGISTRO DE DESCUENTOS
	
	//INICIO REGISTRAR REGISTRAR LOS DESCUENTOS 
	$('#reg').on('click', function(e){
		 if ($('#formularioSecuenciaFacturacion #empresa').val() != "" && $('#formularioSecuenciaFacturacion #estado').val() != ""){					 
			e.preventDefault();
			agregar();	
		 }else{
			showNotify("error", "Error", "La empresa y el estado no pueden quedar vacíos, por favor corregir");
			return false;
		 } 		 
	});
	//FIN REGISTRAR REGISTRAR LOS DESCUENTOS 
	
	//INICIO VENTANA EDITAR LOS DESCUENTOS 
	$('#edi').on('click', function(e){
		 if ($('#formularioSecuenciaFacturacion #empresa').val() != "" && $('#formulario_tarifas #estado').val() != "" ){					 
			e.preventDefault();
			agregarRegistro();	
		 }else{
			showNotify("error", "Error", "Hay registros en blanco, por favor corregir");
			return false;
		 } 		 
	});	
	//FIN VENTANA EDITAR LOS DESCUENTOS 
	
	//INICIO ELIMINAR LOS DESCUENTOS 
	$('#delete').on('click', function(e){
		 if ($('#formularioSecuenciaFacturacion #comentario').val() != "" ){					 
			e.preventDefault();
			eliminarRegistro();	
		 }else{
			showNotify("error", "Error", "El comentario no puede quedar vacío, por favor corregir");
			return false;
		 } 		 
	});	
	//FIN ELIMINAR LOS DESCUENTOS 
	
    //INICIO PAGINATION (PARA LAS BUSQUEDAS SEGUN SELECCIONES)
	$('#form_main #bs_regis').on('keyup',function(){
	  pagination(1);
	}); 

	$('#form_main #servicio').on('change',function(){
	  pagination(1);
	});
	
	$('#form_main #estado').on('change',function(){
	  pagination(1);
	});	
	
	$('#form_main #profesional').on('change',function(){
	  pagination(1);
	});	

	$('#form_main #documento').on('change',function(){
	  pagination(1);
	});	
	//FIN PAGINATION (PARA LAS BUSQUEDAS SEGUN SELECCIONES)
});
//FIN CONTROLES DE ACCION
/****************************************************************************************************************************************************************/


/***************************************************************************************************************************************************************************/
//INICIO FUNCIONES

//INICIO FUNCION QUE GUARDA LOS REGISTROS DE PACIENTES QUE NO ESTAN ALMACENADOS EN LA AGENDA
function agregar() {
  var url = '<?php echo SERVERURL; ?>php/secuencia_facturacion/agregar.php';

  $.ajax({
    type: 'POST',
    url: url,
    dataType: 'json',
    data: $('#formularioSecuenciaFacturacion').serialize(),
    success: function(resp) {
      if (resp && resp.status === true) {
        // ÉXITO
        try { $('#formularioSecuenciaFacturacion')[0].reset(); } catch(e) {}
        // Si tienes modal:
        try { $('#secuenciaFacturacion').modal('hide'); } catch(e) {}
        // Notificación
        showNotify("success", resp.title || "Success", resp.message || "Registro almacenado correctamente");
        // Refrescos auxiliares
        try { pagination(1); } catch(e) {}
        try { limpiarSeciencia(); } catch(e) {}   // respeta tu función existente
        try { getEmpresa(); } catch(e) {}
        try { getDocumento(); } catch(e) {}
        try { getEstado(); } catch(e) {}
      } else {
        // ERRORES CONTROLADOS
        var code  = (resp && resp.code)    ? resp.code    : "ERROR";
        var title = (resp && resp.title)   ? resp.title   : "Error";
        var msg   = (resp && resp.message) ? resp.message : "Error al procesar su solicitud.";

        // Escalas de severidad
        var type = "error";
        if (code === "OUT_OF_RANGE" || code === "SEQ_ACTIVE_EXISTS" || code === "DATE_INVALID" || code === "INVALID_INPUT") {
          type = "warning";
        }

        showNotify(type, title, msg);

        if (code === "SEQ_ACTIVE_EXISTS") {
          try { limpiarSeciencia && limpiarSeciencia(); } catch(e) {}
        }
      }
    },
    error: function(xhr, status, err) {
      var extra = (xhr && xhr.responseText) ? String(xhr.responseText).slice(0, 250) + "..." : (err || status || "");
      showNotify("error", "Error", "Error al procesar su solicitud. " + extra);
    }
  });

  return false;
}

function agregarRegistro() {
  var url = '<?php echo SERVERURL; ?>php/secuencia_facturacion/agregarRegistro.php';

  $.ajax({
    type: 'POST',
    url: url,
    dataType: 'json',
    data: $('#formularioSecuenciaFacturacion').serialize(),
    success: function(resp) {
      if (resp && resp.status === true) {
        // Éxito
        showNotify("success", resp.title || "Success", resp.message || "Registro modificado correctamente");
        pagination(1);
      } else {
        // Error controlado
        var code = resp && resp.code ? resp.code : "ERROR";
        var title = resp && resp.title ? resp.title : "Error";
        var msg = resp && resp.message ? resp.message : "Error al procesar su solicitud.";

        // Si el número está en uso, lo mostramos como warning
        var type = (code === "NUMBER_IN_USE") ? "warning" : "error";
        showNotify(type, title, msg);
      }
    },
    error: function(xhr, status, err) {
      var extra = (xhr && xhr.responseText) ? String(xhr.responseText).slice(0, 200) + "..." : (err || status || "");
      showNotify("error", "Error", "Error al procesar su solicitud. " + extra);
    }
  });

  return false;
}

function eliminarRegistro(){
	var url = '<?php echo SERVERURL; ?>php/secuencia_facturacion/eliminar.php';
		
	$.ajax({
		type:'POST',
		url:url,
		dataType: 'json', // esperamos JSON; fallback abajo si no lo es
		data:$('#formularioSecuenciaFacturacion').serialize(),
		success: function(resp){
			// Si viene JSON estándar
			if (resp && typeof resp === 'object' && resp.hasOwnProperty('status')) {
				if (resp.status === true) {
					try { $('#formularioSecuenciaFacturacion')[0].reset(); } catch(e){}
					try { $('#secuenciaFacturacion').modal('hide'); } catch(e){}
					showNotify("success", resp.title || "Success", resp.message || "Registro eliminado correctamente");
					try { getEmpresa(); } catch(e){}
					try { getDocumento(); } catch(e){}
					try { getEstado(); } catch(e){}
					$('#formularioSecuenciaFacturacion #pro').val('Eliminar Registro');
					try { pagination(1); } catch(e){}
					return false;
				} else {
					var code  = resp.code  || "ERROR";
					var title = resp.title || "Error";
					var msg   = resp.message || "Error, no se puede eliminar este registro";
					var type  = (code === "HAS_DEPENDENCIES") ? "warning" : "error";
					showNotify(type, title, msg);
					return false;
				}
			}

			// Fallback si el backend aún devuelve 1/2/3
			var registro = resp;
			if (registro == 1){
				try { $('#formularioSecuenciaFacturacion')[0].reset(); } catch(e){}
				try { $('#secuenciaFacturacion').modal('hide'); } catch(e){}
				showNotify("success", "Success", "Registro eliminado correctamente");
				try { getEmpresa(); } catch(e){}
				try { getDocumento(); } catch(e){}
				try { getEstado(); } catch(e){}
				$('#formularioSecuenciaFacturacion #pro').val('Eliminar Registro');
				try { pagination(1); } catch(e){}
				return false;
			}else if(registro == 2){
				showNotify("error", "Error", "Error, no se puede eliminar este registro");
				return false;				
			}else if(registro == 3){
				showNotify("error", "Error", "Lo sentimos este registro cuenta con información almacenada, no se puede eliminar");
				return false;				
			}else{
				showNotify("error", "Error", "Error al procesar su solicitud, por favor inténtelo de nuevo más tarde");
				return false;	
			}
		},
		error: function(xhr, status, err){
			var extra = (xhr && xhr.responseText) ? String(xhr.responseText).slice(0, 250) + "..." : (err || status || "");
			showNotify("error", "Error", "Error al procesar su solicitud. " + extra);
		}
	});
	return false;
}
//FIN FUNCION QUE GUARDA LOS REGISTROS DE PACIENTES QUE NO ESTAN ALMACENADOS EN LA AGENDA

function editarRegistro(secuencia_facturacion_id){
  if (!(getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 4)){
    showNotify("error", "Acceso denegado", "No tiene permisos para ejecutar esta acción");
    return false;
  }

  try { $('#formularioSecuenciaFacturacion')[0].reset(); } catch(e) {}

  var url = '<?php echo SERVERURL; ?>php/secuencia_facturacion/editar.php';

  $.ajax({
    type: 'POST',
    url: url,
    dataType: 'json',
    data: { 'secuencia_facturacion_id': secuencia_facturacion_id },
    success: function(resp){
      if (!resp || resp.status !== true || !resp.data){
        showNotify("error", "Error", "No se pudo cargar la secuencia para editar");
        return false;
      }

      var d = resp.data;

      // Botones/UI
      $('#reg').hide();
      $('#edi').show();
      $('#delete').hide();
      $('#formularioSecuenciaFacturacion #pro').val('Edición');
      $('#formularioSecuenciaFacturacion #secuencia_facturacion_id').val(secuencia_facturacion_id);

      // Empresa
      if ($('#formularioSecuenciaFacturacion #empresa').length){
        $('#formularioSecuenciaFacturacion #empresa').val(d.empresa_id).selectpicker('refresh');
      }

      // Tipo de documento
      if ($('#formularioSecuenciaFacturacion #documento_id').length){
        $('#formularioSecuenciaFacturacion #documento_id').val(d.documento_id).selectpicker('refresh');
      }

      // Campos texto/número
      $('#formularioSecuenciaFacturacion #cai').val(d.cai || '');
      $('#formularioSecuenciaFacturacion #prefijo').val(d.prefijo || '');
      $('#formularioSecuenciaFacturacion #relleno').val(d.relleno || '');
      $('#formularioSecuenciaFacturacion #incremento').val(d.incremento || '');
      $('#formularioSecuenciaFacturacion #siguiente').val(d.siguiente || '');
      $('#formularioSecuenciaFacturacion #rango_inicial').val(d.rango_inicial || '');
      $('#formularioSecuenciaFacturacion #rango_final').val(d.rango_final || '');

      if (d.fecha_activacion){ $('#formularioSecuenciaFacturacion #fecha_activacion').val(d.fecha_activacion); }
      if (d.fecha_limite){     $('#formularioSecuenciaFacturacion #fecha_limite').val(d.fecha_limite); }

      // Estado
      if ($('#formularioSecuenciaFacturacion #estado').length){
        $('#formularioSecuenciaFacturacion #estado').val(d.activo).selectpicker('refresh');
      }

      // Comentario
      $('#formularioSecuenciaFacturacion #comentario').val(d.comentario || '');

      // ===== Reglas de edición =====
      // Todo inhabilitado…
      $('#formularioSecuenciaFacturacion #cai').prop('disabled', true);
      $('#formularioSecuenciaFacturacion #prefijo').prop('readonly', true);
      $('#formularioSecuenciaFacturacion #relleno').prop('readonly', true);
      $('#formularioSecuenciaFacturacion #incremento').prop('readonly', true);
      $('#formularioSecuenciaFacturacion #rango_inicial').prop('readonly', true);
      $('#formularioSecuenciaFacturacion #rango_final').prop('readonly', true);
      $('#formularioSecuenciaFacturacion #fecha_activacion').prop('readonly', true);
      $('#formularioSecuenciaFacturacion #fecha_limite').prop('readonly', true);
      $('#formularioSecuenciaFacturacion #comentario').prop('readonly', true);

      // …excepto Siguiente y Estado
      $('#formularioSecuenciaFacturacion #siguiente').prop('readonly', false);
      if ($('#formularioSecuenciaFacturacion #estado').length){
        $('#formularioSecuenciaFacturacion #estado').prop('disabled', false).selectpicker('refresh');
      }

      // Deshabilitar selects que NO deben cambiarse
      if ($('#formularioSecuenciaFacturacion #empresa').length){
        $('#formularioSecuenciaFacturacion #empresa').prop('disabled', true).selectpicker('refresh');
      }
      if ($('#formularioSecuenciaFacturacion #documento_id').length){
        $('#formularioSecuenciaFacturacion #documento_id').prop('disabled', true).selectpicker('refresh');
      }

      $('#formularioSecuenciaFacturacion #group_comentario').hide();

      // Habilitar botón editar
      $("#edi").prop('disabled', false);

      // Abrir modal
      // Ponemos el focus cuando el modal *ya* está visible (evento shown.bs.modal)
      $('#secuenciaFacturacion')
        .off('shown.bs.modal.editarSecFocus') // evita duplicar handlers
        .on('shown.bs.modal.editarSecFocus', function () {
          var $s = $('#formularioSecuenciaFacturacion #siguiente');
          // Intento 1: focus directo
          $s.trigger('focus');
          // Intento 2 (fallback): select de contenido por si el focus no entró
          try { $s[0] && $s[0].select && $s[0].select(); } catch(e){}
        })
        .modal({
          show:true,
          keyboard: false,
          backdrop:'static'
        });

      // Fallback adicional por si el evento no dispara en algún navegador
      setTimeout(function(){
        var $s = $('#formularioSecuenciaFacturacion #siguiente');
        if ($s.length) { $s.trigger('focus'); }
      }, 150);

      return false;
    },
    error: function(){
      showNotify("error", "Error", "No se pudo cargar la secuencia para editar");
    }
  });

  return false;
}

function modal_eliminar(secuencia_facturacion_id){
  // ---- Permisos ----
  if (!(getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 4)){
    showNotify("error", "Acceso Denegado", "No tiene permisos para ejecutar esta acción");
    return false;
  }

  // ---- Traer datos para mostrar un resumen antes de eliminar ----
  var urlInfo = '<?php echo SERVERURL; ?>php/secuencia_facturacion/editar.php';

  $.ajax({
    type: 'POST',
    url: urlInfo,
    dataType: 'json',
    data: { 'secuencia_facturacion_id': secuencia_facturacion_id },
    success: function(resp){
      if (!resp || resp.status !== true || !resp.data){
        showNotify("error", "Error", "No se pudo cargar la secuencia para eliminar");
        return false;
      }

      var d = resp.data;

      // Helpers display: pad a la izquierda con ceros
      var pad = function(n, len){
        n = String(n == null ? "" : n);
        len = parseInt(len || 0, 10);
        try { return n.padStart(len, "0"); } catch(e){
          while (n.length < len) n = "0" + n;
          return n;
        }
      };

      var docTxt = (d.documento_id == 1)
        ? "Factura Electrónica"
        : (d.documento_id == 4 ? "Factura Proforma" : ("Documento #" + (d.documento_id || "")));

      var numDisplay   = (d.prefijo || "") + pad(d.siguiente, d.relleno);
      var rangoDisplay = (d.prefijo || "") + pad(d.rango_inicial, d.relleno) +
                         " &rarr; " + (d.prefijo || "") + pad(d.rango_final, d.relleno);

      // Armar HTML del swal (sin uso de jQuery dentro de las cadenas)
      var htmlResumen =
        '<div class="text-left" style="line-height:1.35;">' +
          '<div class="mb-2"><span class="badge badge-secondary">Documento</span> ' + docTxt + '</div>' +
          '<div><strong>Prefijo:</strong> ' + (d.prefijo ? String(d.prefijo) : '<em>(vacío)</em>') + '</div>' +
          '<div><strong>Relleno:</strong> ' + (d.relleno || 0) + '</div>' +
          '<div><strong>Número siguiente:</strong> <span class="badge badge-info">' + numDisplay + '</span></div>' +
          '<div><strong>Rango autorizado:</strong> ' + rangoDisplay + '</div>' +
          (d.fecha_activacion ? ('<div><strong>Fecha activación:</strong> ' + d.fecha_activacion + '</div>') : '') +
          (d.fecha_limite ? ('<div><strong>Fecha límite:</strong> ' + d.fecha_limite + '</div>') : '') +
          '<hr class="mt-2 mb-2"/>' +
          '<div class="text-danger"><i class="fas fa-exclamation-triangle"></i> Esta acción es permanente.</div>' +
        '</div>';

      // SweetAlert de confirmación
      swal({
        title: "¿Eliminar secuencia?",
        content: (function(){
          var wrapper = document.createElement('div');
          wrapper.innerHTML = htmlResumen;
          return wrapper;
        })(),
        icon: "warning",
        buttons: {
          cancel: {
            text: "Cancelar",
            visible: true,
            className: "btn btn-light",
            closeModal: true
          },
          confirm: {
            text: "Sí, eliminar",
            visible: true,
            className: "btn btn-danger",
            closeModal: false // se cierra manualmente al terminar
          }
        },
        dangerMode: true,
        closeOnClickOutside: false,
        closeOnEsc: false
      }).then(function(confirmed){
        if (!confirmed){
          showNotify("info", "Cancelado", "La eliminación fue cancelada.");
          return false;
        }

        // ---- Llamar a eliminar.php ----
        var urlDel = '<?php echo SERVERURL; ?>php/secuencia_facturacion/eliminar.php';
        $.ajax({
          type: 'POST',
          url: urlDel,
          dataType: 'json',
          data: { 'secuencia_facturacion_id': secuencia_facturacion_id },
          success: function(r){
            try { swal.stopLoading(); swal.close(); } catch(e) {}

            if (r && r.status === true){
              showNotify("success", r.title || "Eliminado", r.message || "Registro eliminado correctamente");
              try { pagination(1); } catch(e) {}
              try { getEmpresa(); } catch(e) {}
              try { getEstado(); } catch(e) {}
            } else {
              var code  = (r && r.code)    ? r.code    : "ERROR";
              var title = (r && r.title)   ? r.title   : "Error";
              var msg   = (r && r.message) ? r.message : "No se pudo eliminar el registro.";

              var type = "error";
              if (code === "HAS_DEPENDENCIES" || code === "INVALID_INPUT") type = "warning";
              if (code === "NO_SESSION") type = "info";

              showNotify(type, title, msg);
            }
            return false;
          },
          error: function(xhr, status, err){
            try { swal.stopLoading(); swal.close(); } catch(e) {}
            var extra = (xhr && xhr.responseText) ? String(xhr.responseText).slice(0, 250) + "..." : (err || status || "");
            showNotify("error", "Error", "Error al procesar su solicitud. " + extra);
          }
        });

        return false;
      });

    },
    error: function(xhr, status, err){
      var extra = (xhr && xhr.responseText) ? String(xhr.responseText).slice(0, 250) + "..." : (err || status || "");
      showNotify("error", "Error", "No se pudo cargar la secuencia para eliminar. " + extra);
    }
  });

  return false;
}

function limpiarSeciencia(){
   	$('#formularioSecuenciaFacturacion #pro').val("Registro");
}

//INICIO FUNCION PARA OBTENER LOS COLABORADORES	
function funciones(){
    pagination(1);
    getEstado();
    getEmpresa();
}

//INICIO PAGINACION DE REGISTROS
function pagination(partida){
	var url = '<?php echo SERVERURL; ?>php/secuencia_facturacion/paginar.php';
	var dato = '';
	var profesional = '';
	
	var empresa = $('#form_main #empresa').val() || 0;
	var estado = $('#form_main #estado').val() || 1;
	var dato = $('#form_main #bs_regis').val() || '';
	var documento = $('#form_main #documento').val() || '';

	$.ajax({
		type:'POST',
		url:url,
		async: true,
		data:'partida='+partida+'&dato='+dato+'&empresa='+empresa+'&estado='+estado+'&documento='+documento,
		success:function(data){
			var array = eval(data);
			$('#agrega-registros').html(array[0]);
			$('#pagination').html(array[1]);
		},
		error: function(xhr, status, err){
			var extra = (xhr && xhr.responseText) ? String(xhr.responseText).slice(0, 200) + "..." : (err || status || "");
			showNotify("error", "Error", "No se pudo cargar la paginación. " + extra);
		}
	});
	return false;
}
//FIN PAGINACION DE REGISTROS

//INICIO FUNCION PARA OBTENER LA EMPRESA
function getEmpresa(){
    var url = '<?php echo SERVERURL; ?>php/secuencia_facturacion/getEmpresa.php';		
		
	$.ajax({
        type: "POST",
        url: url,
	    async: true,
        success: function(data){
		    $('#form_main #empresa').html("");
			$('#form_main #empresa').html(data);
			$('#form_main #empresa').selectpicker('refresh');

		    $('#formularioSecuenciaFacturacion #empresa').html("");
			$('#formularioSecuenciaFacturacion #empresa').html(data);	
			$('#formularioSecuenciaFacturacion #empresa').selectpicker('refresh');			
        },
		error: function(xhr, status, err){
			showNotify("warning", "Aviso", "No se pudieron cargar las empresas.");
		}
     });		
}

function getDocumento1(){
    var url = '<?php echo SERVERURL; ?>php/secuencia_facturacion/getDocumento1.php';		
		
	$.ajax({
        type: "POST",
        url: url,
	    async: true,
        success: function(data){
		    $('#formularioSecuenciaFacturacion #documento_id').html("");
			$('#formularioSecuenciaFacturacion #documento_id').html(data);
			$('#formularioSecuenciaFacturacion #documento_id').selectpicker('refresh');		
        },
		error: function(){
			showNotify("warning", "Aviso", "No se pudieron cargar los documentos.");
		}
     });		
}

function getDocumento(){
    var url = '<?php echo SERVERURL; ?>php/secuencia_facturacion/getDocumento.php';		
		
	$.ajax({
        type: "POST",
        url: url,
	    async: true,
        success: function(data){
		    $('#form_main #documento').html("");
			$('#form_main #documento').html(data);
			$('#form_main #documento').selectpicker('refresh');

		    $('#formularioSecuenciaFacturacion #documento_id').html("");
			$('#formularioSecuenciaFacturacion #documento_id').html(data);	
			$('#formularioSecuenciaFacturacion #documento_id').selectpicker('refresh');			
        },
		error: function(){
			showNotify("warning", "Aviso", "No se pudieron cargar los documentos.");
		}
     });		
}
//FIN FUNCION PARA OBTENER LA EMPRESA	

//INICIO FUNCION PARA OBTENER EL ESTADO
function getEstado(){
    var url = '<?php echo SERVERURL; ?>php/secuencia_facturacion/getEstado.php';		
		
	$.ajax({
        type: "POST",
        url: url,
	    async: true,
        success: function(data){
		    $('#form_main #estado').html("");
			$('#form_main #estado').html(data);	
			$('#form_main #estado').selectpicker('refresh');	

		    $('#formularioSecuenciaFacturacion #estado').html("");
			$('#formularioSecuenciaFacturacion #estado').html(data);	
			$('#formularioSecuenciaFacturacion #estado').selectpicker('refresh');				
        },
		error: function(){
			showNotify("warning", "Aviso", "No se pudieron cargar los estados.");
		}
     });		
}
//FIN FUNCION PARA OBTENER EL ESTADO
//FIN FUNCIONES
/***************************************************************************************************************************************************************************/

/***************************************************************************************************************************************************************************/
</script>
