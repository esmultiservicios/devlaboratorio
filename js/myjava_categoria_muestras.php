<script>
$(document).ready(pagination(1));getCategoriaMuestra();
 $(function(){
	  $('#for_main #nuevo_registro').on('click',function(e){
		e.preventDefault();
		if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
		  $('#formularioCategoriaMuestras')[0].reset();
     	  $('#formularioCategoriaMuestras #pro').val('Registro');
		  $('#ediCategoria').hide();
		  $('#regCategoria').show();				
		  $('#formularioCategoriaMuestras').attr({ 'data-form': 'save' }); 
		  $('#formularioCategoriaMuestras').attr({ 'action': '<?php echo SERVERURL; ?>php/categorias_muestras/agregarCategoriaMuestras.php' });			  
		  
		  //HABILITAR OBJETO
	      $('#formularioCategoriaMuestras #categoria_muestra').attr('disabled', false);

		  $('#modalCategoriaMuestras').modal({
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
           }
	   });
                      			   
	   $('#for_main #bs_regis').on('keyup',function(){
		  pagination(1);
   	      return false;
   });	
});

/*INICIO DE FUNCIONES PARA ESTABLECER EL FOCUS PARA LAS VENTANAS MODALES*/
$(document).ready(function(){
    $("#modalCategoriaMuestras").on('shown.bs.modal', function(){
        $(this).find('#formularioCategoriaMuestras #categoria_muestra').focus();
    });
});
/*FIN DE FUNCIONES PARA ESTABLECER EL FOCUS PARA LAS VENTANAS MODALES*/

function pagination(partida){
	var url = '<?php echo SERVERURL; ?>php/categorias_muestras/paginar.php';
	var dato = $('#for_main #bs_regis').val();
		
	$.ajax({
		type:'POST',
		url:url,
		data:'partida='+partida+'&dato='+dato,
		success:function(data){
			var array = eval(data);
			$('#agrega-registros').html(array[0]);
			$('#pagination').html(array[1]);
		}
	});
	return false;
}

function editarRegistro(categoria_id){
	$('#formularioCategoriaMuestras')[0].reset();		
	var url = '<?php echo SERVERURL; ?>php/categorias_muestras/editar.php';
		
	$.ajax({
		type:'POST',
		url:url,
		data:'categoria_id='+categoria_id,
		success: function(valores){
				var datos = eval(valores);
				$('#regCategoria').hide();
				$('#ediCategoria').show();
				$('#formularioCategoriaMuestras #pro').val('Edicion');
				$('#formularioCategoriaMuestras #categoria_id').val(id);
                $('#formularioCategoriaMuestras #categoria_muestra').val(datos[0]);				
				$('#formularioCategoriaMuestras #tiempo_categoria').val(datos[1]);				
				
				$('#formularioCategoriaMuestras').attr({ 'data-form': 'update' }); 
				$('#formularioCategoriaMuestras').attr({ 'action': '<?php echo SERVERURL; ?>php/categorias_muestras/modificarCategoriaMuestras.php' });
		  
				//DESHABILITAR OBJETO
				$('#formularioCategoriaMuestras #categoria_muestra').attr('disabled', true);

				$('#modalCategoriaMuestras').modal({
					show:true,
					keyboard: false,
					backdrop:'static'
				});
			return false;
		}
	});
	return false;	
}

function modal_eliminar(categoria_id){
	if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){	
		swal({
			title: "¿Esta seguro?",
			text: "¿Desea eliminar este registro:  " + consultarNombre(id) + "",
			icon: "warning",
			buttons: {
				cancel: {
					text: "Cancelar",
					visible: true
				},
				confirm: {
					text: "¡Sí, Eliminar el registro!",
				}
			},
			dangerMode: true,
			closeOnEsc: false, // Desactiva el cierre con la tecla Esc
			closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera
		}).then((willConfirm) => {
			if (willConfirm === true) {
				eliminarRegistro(categoria_id);
			}
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
	}	
}

function eliminarRegistro(categoria_id){
	var url = '<?php echo SERVERURL; ?>php/categorias_muestras/eliminar.php';
	
	$.ajax({
		type:'POST',
		url:url,
		data:'categoria_id='+categoria_id,
		success: function(registro){
			if (registro == 1){
				swal({
					title: "Success", 
					text: "Registro almacenado correctamente",
					icon: "success",
					timer: 3000, //timeOut for auto-close
					closeOnEsc: false, // Desactiva el cierre con la tecla Esc
					closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera					
				});			       				
			   pagination(1);
			   return false;
			}else if (registro == 2){
				swal({
					title: "Error", 
					text: "Error al intentar eliminar el registro, por favor intente de nuevo",
					icon: "error",
					dangerMode: true,
					closeOnEsc: false, // Desactiva el cierre con la tecla Esc
					closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera
				});
				return false;			
			}else if (registro == 3){
				swal({
					title: "Error", 
					text: "Error al intentar eliminar el registro, cuenta con información almacenada",
					icon: "error",
					dangerMode: true,
					closeOnEsc: false, // Desactiva el cierre con la tecla Esc
					closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera
				});
				return false;			
			}else{
				swal({
					title: "Error", 
					text: "Error procesar su solicitud",
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

$(document).ready(function() {
	$('#for_main #consulta').on('change', function(){
          pagination(1);
    });					
});


//BOTONES DE ACCION
$('#formulario_registros #reg').on('click', function(e){ // add event submit We don't want this to act as a link so cancel the link action
	 if ($('#formulario_registros #nombre_registro').val() != "" && $('#formulario_registros #consulta_registro').val() != ""){
		e.preventDefault();
		agregar();			   
		return false;
	 }else{
       $('#formulario_registros #pro').val('Registro');	
		swal({
			title: "Error", 
			text: "No se pueden enviar los datos, los campos estan vacíos",
			icon: "error",
			dangerMode: true,
			closeOnEsc: false, // Desactiva el cierre con la tecla Esc
			closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera
		});	
	   return false;	   
	 }  
});

$('#formulario_registros #edi').on('click', function(e){ // add event submit We don't want this to act as a link so cancel the link action
	 if ($('#formulario_registros #nombre_registro').val() != "" && $('#formulario_registros #consulta_registro').val() != ""){
		e.preventDefault();
		modificar();			   
		return false;
	 }else{
		$('#formulario_registros #pro').val('Edición');		
		swal({
			title: "Error", 
			text: "No se pueden enviar los datos, los campos estan vacíos",
			icon: "error",
			dangerMode: true,
			closeOnEsc: false, // Desactiva el cierre con la tecla Esc
			closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera
		});			
		return false;	   
	 }  
});

function getCategoriaMuestra(){
    var url = '<?php echo SERVERURL; ?>php/categorias_muestras/getCategoriaMuestra.php';		
		
	$.ajax({
        type: "POST",
        url: url,
	    async: true,
        success: function(data){	
		    $('#formularioCategoriaMuestras #categoria_muestra').html("");
			$('#formularioCategoriaMuestras #categoria_muestra').html(data);			
		}			
     });		
}

function consultarNombre(categoria_id){	
    var url = '<?php echo SERVERURL; ?>php/categorias_muestras/getNombre.php';
	var resp;
		
	$.ajax({
	    type:'POST',
		url:url,
		data:'categoria_id='+categoria_id,
		async: false,
		success:function(data){	
          resp = data;			  		  		  			  
		}
	});
	return resp;		
}
</script>