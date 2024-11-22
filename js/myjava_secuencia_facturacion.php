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
			swal({
				title: "Acceso Denegado", 
				text: "No tiene permisos para ejecutar esta acción",
				type: "error", 
				confirmButtonClass: 'btn-danger'
			});		 
		}			
	});
	//FIN ABRIR VENTANA MODAL PARA EL REGISTRO DE DESCUENTOS
	
	//INICIO REGISTRAR REGISTRAR LOS DESCUENTOS 
	$('#reg').on('click', function(e){
		 if ($('#formularioSecuenciaFacturacion #empresa').val() != "" && $('#formularioSecuenciaFacturacion #estado').val() != ""){					 
			e.preventDefault();
			agregar();	
		 }else{
			swal({
				title: "Error", 
				text: "La empresa y el estado no pueden quedar vacíos, por favor corregir",
				type: "error", 
				confirmButtonClass: 'btn-danger'
			});				
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
			swal({
				title: "Error", 
				text: "Hay registros en blanco, por favor corregir",
				type: "error", 
				confirmButtonClass: 'btn-danger'
			});				   
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
			swal({
				title: "Error", 
				text: "El comentario no puede quedar vacío, por favor corregir",
				type: "error", 
				confirmButtonClass: 'btn-danger'
			});				   
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
function agregar(){
	var url = '<?php echo SERVERURL; ?>php/secuencia_facturacion/agregar.php';
	$.ajax({
		type:'POST',
		url:url,
		data:$('#formularioSecuenciaFacturacion').serialize(),
		success: function(registro){
			if(registro == 1){
				$('#formularioSecuenciaFacturacion')[0].reset();  				
				swal({
					title: "Success", 
					text: "Registro almacenado correctamente",
					type: "success", 
					timer: 3000, //timeOut for auto-close
				});	
				$('#secuenciaFacturacion').modal('hide');
				pagination(1);
				limpiarSeciencia();
				getEmpresa();
				getDocumento();
				getEstado();
				return false;				
			}else if(registro == 2){
				swal({
					title: "Error", 
					text: "Error, no se puede almacenar este registro",
					type: "error", 
					confirmButtonClass: 'btn-danger'
				});	
			   return false;				
			}else if(registro == 3){
				swal({
					title: "Error", 
					text: "Lo sentimos Solo se puede tener un administrador de secuencias activo para cada documento",
					type: "error", 
					confirmButtonClass: 'btn-danger'
				});
				limpiarPago();
				return false;				
			}else{
				swal({
					title: "Error", 
					text: "Error al procesar su solicitud, por favor intentelo de nuevo mas tarde",
					type: "error", 
					confirmButtonClass: 'btn-danger'
				});
				return false;	
			}
		}
	});
	return false;
}

function agregarRegistro(){
	var url = '<?php echo SERVERURL; ?>php/secuencia_facturacion/agregarRegistro.php';
		
	$.ajax({
		type:'POST',
		url:url,
		data:$('#formularioSecuenciaFacturacion').serialize(),
		success: function(registro){
			if(registro == 1){ 			   
				swal({
					title: "Success", 
					text: "Registro modificado correctamente",
					type: "success", 
					timer: 3000, //timeOut for auto-close
				});
				//$('#secuenciaFacturacion').modal('hide');
				pagination(1);
				return false;				
			}else if(registro == 2){
				swal({
					title: "Error", 
					text: "Error, no se puede modifciar este registro",
					type: "error", 
					confirmButtonClass: 'btn-danger'
				});	
				return false;				
			}else{
				swal({
					title: "Error", 
					text: "Error al procesar su solicitud, por favor intentelo de nuevo mas tarde",
					type: "error", 
					confirmButtonClass: 'btn-danger'
				});
			    return false;	
			}
		}
	});
	return false;
}

function eliminarRegistro(){
	var url = '<?php echo SERVERURL; ?>php/secuencia_facturacion/eliminar.php';
		
	$.ajax({
		type:'POST',
		url:url,
		data:$('#formularioSecuenciaFacturacion').serialize(),
		success: function(registro){
			if(registro == 1){
			   $('#formularioSecuenciaFacturacion')[0].reset();  			   
				swal({
					title: "Success", 
					text: "Registro eliminado correctamente",
					type: "success",
					timer: 3000,
				});	
				$('#secuenciaFacturacion').modal('hide');
				getEmpresa();
				getDocumento();
				getEstado();
				$('#formularioSecuenciaFacturacion #pro').val('Eliminar Registro');
				pagination(1);
				return false;				
			}else if(registro == 2){
				swal({
					title: "Error", 
					text: "Error, no se puede eliminar este registro",
					type: "error", 
					confirmButtonClass: 'btn-danger'
				});
				return false;				
			}else if(registro == 3){
				swal({
					title: "Error", 
					text: "Lo sentimos este registro cuenta con información almacenada, no se puede eliminar",
					type: "error", 
					confirmButtonClass: 'btn-danger'
				});
				return false;				
			}else{
				swal({
					title: "Error", 
					text: "Error al procesar su solicitud, por favor intentelo de nuevo mas tarde",
					type: "error", 
					confirmButtonClass: 'btn-danger'
				});
				return false;	
			}
		}
	});
	return false;
}
//FIN FUNCION QUE GUARDA LOS REGISTROS DE PACIENTES QUE NO ESTAN ALMACENADOS EN LA AGENDA

function editarRegistro(secuencia_facturacion_id){
	if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 4){	
		$('#formularioSecuenciaFacturacion')[0].reset();		
		var url = '<?php echo SERVERURL; ?>php/secuencia_facturacion/editar.php';

			$.ajax({
			type:'POST',
			url:url,
			data:'secuencia_facturacion_id='+secuencia_facturacion_id,
			success: function(valores){
				var array = eval(valores);
				$('#reg').hide();
				$('#edi').show();
				$('#delete').hide(); 			 
				$('#formularioSecuenciaFacturacion #pro').val('Edición');
                $('#formularioSecuenciaFacturacion #secuencia_facturacion_id').val(secuencia_facturacion_id);
				$('#formularioSecuenciaFacturacion #empresa').val(array[0]);
				$('#formularioSecuenciaFacturacion #empresa').selectpicker('refresh');				
                $('#formularioSecuenciaFacturacion #cai').val(array[1]);
                $('#formularioSecuenciaFacturacion #prefijo').val(array[2]);
				$('#formularioSecuenciaFacturacion #relleno').val(array[3]);	
                $('#formularioSecuenciaFacturacion #incremento').val(array[4]);
				$('#formularioSecuenciaFacturacion #siguiente').val(array[5]);	
                $('#formularioSecuenciaFacturacion #rango_inicial').val(array[6]);
                $('#formularioSecuenciaFacturacion #rango_final').val(array[7]);
				$('#formularioSecuenciaFacturacion #fecha_limite').val(array[8]);	
                $('#formularioSecuenciaFacturacion #estado').val(array[9]);
				$('#formularioSecuenciaFacturacion #estado').selectpicker('refresh');
                $('#formularioSecuenciaFacturacion #comentario').val(array[10]);			
				$('#formularioSecuenciaFacturacion #documento_id').val(array[11]);
				$('#formularioSecuenciaFacturacion #documento_id').selectpicker('refresh');
				$("#edi").attr('disabled', false);	
				
				//DESHABILITAR CONTROLES PARA SOLO LECTURA				
				$("#formularioSecuenciaFacturacion #empresa").attr('disabled', true);
				$("#formularioSecuenciaFacturacion #documento_id").attr('disabled', true);
				$("#formularioSecuenciaFacturacion #cai").attr('disabled', true);				
				$("#formularioSecuenciaFacturacion #prefijo").attr('readonly', true);
				$("#formularioSecuenciaFacturacion #relleno").attr('readonly', true);				
				$("#formularioSecuenciaFacturacion #incremento").attr('readonly', true);
				$("#formularioSecuenciaFacturacion #rango_inicial").attr('readonly', true);
				$("#formularioSecuenciaFacturacion #rango_final").attr('readonly', true);
				$("#formularioSecuenciaFacturacion #fecha_activacion").attr('readonly', true);
				$("#formularioSecuenciaFacturacion #fecha_limite").attr('readonly', true);
				$("#formularioSecuenciaFacturacion #comentario").attr('readonly', true);

				
				//HABILITAR CONTROLES
				$("#formularioSecuenciaFacturacion #siguiente").attr('readonly', false);
				$("#formularioSecuenciaFacturacion #estado").attr('disabled', false);
								
				$('#formularioSecuenciaFacturacion #group_comentario').hide();
								
				$('#secuenciaFacturacion').modal({
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

function modal_eliminar(secuencia_facturacion_id){
	if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 4){	
		$('#formularioSecuenciaFacturacion')[0].reset();		
		var url = '<?php echo SERVERURL; ?>php/secuencia_facturacion/editar.php';

			$.ajax({
			type:'POST',
			url:url,
			data:'secuencia_facturacion_id='+secuencia_facturacion_id,
			success: function(valores){
				var array = eval(valores);
				$('#delete').show();
				$('#formularioSecuenciaFacturacion #pro').val('Eliminar Registro');
				$("#formularioSecuenciaFacturacion #comentario").val("");
                $('#formularioSecuenciaFacturacion #secuencia_facturacion_id').val(secuencia_facturacion_id);
				$('#formularioSecuenciaFacturacion #empresa').val(array[0]);
				$('#formularioSecuenciaFacturacion #empresa').selectpicker('refresh');								
                $('#formularioSecuenciaFacturacion #cai').val(array[1]);
                $('#formularioSecuenciaFacturacion #prefijo').val(array[2]);
				$('#formularioSecuenciaFacturacion #relleno').val(array[3]);	
                $('#formularioSecuenciaFacturacion #incremento').val(array[4]);
				$('#formularioSecuenciaFacturacion #siguiente').val(array[5]);	
                $('#formularioSecuenciaFacturacion #rango_inicial').val(array[6]);
                $('#formularioSecuenciaFacturacion #rango_final').val(array[7]);
				$('#formularioSecuenciaFacturacion #fecha_limite').val(array[8]);	
                $('#formularioSecuenciaFacturacion #estado').val(array[9]);	
				$('#formularioSecuenciaFacturacion #estado').selectpicker('refresh');
                $('#formularioSecuenciaFacturacion #comentario').val(array[10]);
				$('#formularioSecuenciaFacturacion #comentario').val(array[10]);			
				$('#formularioSecuenciaFacturacion #documento_id').val(array[11]);					
				$("#edi").attr('disabled', false);	

                //DESHABILITAR CONTROLES PARA SOLO LECTURA
				$("#formularioSecuenciaFacturacion #empresa").attr('disabled', true);
				$("#formularioSecuenciaFacturacion #documento_id").attr('disabled', true);
				$("#formularioSecuenciaFacturacion #cai").attr('disabled', true);				
                $("#formularioSecuenciaFacturacion #estado").attr('disabled', true);				
				$("#formularioSecuenciaFacturacion #prefijo").attr('readonly', true);
				$("#formularioSecuenciaFacturacion #relleno").attr('readonly', true);
				$("#formularioSecuenciaFacturacion #siguiente").attr('readonly', true);
				$("#formularioSecuenciaFacturacion #incremento").attr('readonly', true);
				$("#formularioSecuenciaFacturacion #rango_inicial").attr('readonly', true);
				$("#formularioSecuenciaFacturacion #rango_final").attr('readonly', true);
				$("#formularioSecuenciaFacturacion #fecha_activacion").attr('readonly', true);
				$("#formularioSecuenciaFacturacion #fecha_limite").attr('readonly', true);
				$("#formularioSecuenciaFacturacion #comentario").attr('readonly', true);				
				
				$('#reg').hide();
				$('#edi').hide();
				$('#delete').show();
				$('#formularioSecuenciaFacturacion #group_comentario').show();				
				
				$('#secuenciaFacturacion').modal({
					show:true,
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
        }
     });		
}
//FIN FUNCION PARA OBTENER EL ESTADO
//FIN FUNCIONES
/***************************************************************************************************************************************************************************/

/***************************************************************************************************************************************************************************/
</script>