<script>
/*INICIO DE FUNCIONES PARA ESTABLECER EL FOCUS PARA LAS VENTANAS MODALES*/
$(document).ready(function(){
    $("#modal_plantillas").on('shown.bs.modal', function(){
        $(this).find('#formularioPlantillas #plantilla_asunto').focus();
    });
});

$(document).ready(function(){
    $("#modal_busqueda_atenciones_plantillas").on('shown.bs.modal', function(){
        $(this).find('#formulario_busqueda_atenciones_plantillas #plantilla_asunto').focus();
    });
});
/*FIN DE FUNCIONES PARA ESTABLECER EL FOCUS PARA LAS VENTANAS MODALES*/

$(document).ready(function() {
	funciones()
	listar_plantillas_buscar();	
});	

function funciones(){
	getAtenciones();
}	

function agregarPlantillas(){
	if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2){
		funciones();
		$('#formularioPlantillas').attr({ 'data-form': 'save' });
		$('#formularioPlantillas').attr({ 'action': '<?php echo SERVERURL; ?>php/plantillas/agregarPlantillas.php' });			
		$('#reg_plantilla').show();
		$('#edi_plantilla').hide();
		$('#delete_plantilla').hide();		
		$('#formularioPlantillas')[0].reset();	
		$('#formularioPlantillas #pro').val('Registro');
		
		//HABILITAR OBJETOS
		$('#formularioPlantillas #plantilla_atencion').attr('disabled', false);
		$('#formularioPlantillas #plantilla_asunto').attr('readonly', false);
		$('#formularioPlantillas #plantilla_descripcion').attr('readonly', false);		
				
		 $('#modal_plantillas').modal({
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

var listar_plantillas_buscar = function(){
	var table_plantillas  = $("#dataTablePlantillas").DataTable({
		"destroy":true,	
		"ajax":{
			"method":"POST",
			"url":"<?php echo SERVERURL; ?>php/plantillas/getPlantillasTabla.php"
		},		
		"columns":[
			{"data":"atencion"},
			{"data":"asunto"},	
			{"data":"descripcion"},				
			{"defaultContent":"<button class='editar btn btn-warning'><span class='fas fa-edit'></span></button>"},
			{"defaultContent":"<button class='delete btn btn-danger'><span class='fa fa-trash'></span></button>"}
		],		
        "lengthMenu": lengthMenu,
		"stateSave": true,
		"bDestroy": true,		
		"language": idioma_español,//esta se encuenta en el archivo main.js
		"dom": dom,			
		"buttons":[		
			{
				text:      '<i class="fas fa-sync-alt fa-lg"></i> Actualizar',
				titleAttr: 'Actualizar Plantillas',
				className: 'btn btn-info',
				action: 	function(){
					listar_plantillas_buscar();
				}
			},		
			{
				text:      '<i class="fas fa-comment-medical fa-lg"></i> Crear',
				titleAttr: 'Agregar Plantillas',
				className: 'btn btn-primary',
				action: 	function(){
					agregarPlantillas();
				}
			},				
			{
				extend:    'excelHtml5',
				text:      '<i class="fas fa-file-excel fa-lg"></i> Excel',
				titleAttr: 'Excel',
				title: 'Reporte Plantillas',
				className: 'btn btn-success'				
			},
			{
				extend:    'pdf',
				orientation: 'landscape',
				text:      '<i class="fas fa-file-pdf fa-lg"></i> PDF',
				titleAttr: 'PDF',
				title: 'Reporte Plantillas',
				className: 'btn btn-danger',
				customize: function ( doc ) {
					doc.content.splice( 1, 0, {
						margin: [ 0, 0, 0, 12 ],
						alignment: 'left',
						image: imagen,//esta se encuenta en el archivo main.js
						width:170,
                        height:45
					} );
				}				
			}
		]		
	});	 
	table_plantillas.search('').draw();
	$('#buscar').focus();
	
	edit_plantillas_dataTable("#dataTablePlantillas tbody", table_plantillas);
	delete_plantillas_dataTable("#dataTablePlantillas tbody", table_plantillas);
}

var edit_plantillas_dataTable = function(tbody, table){
	$(tbody).off("click", "button.editar");
	$(tbody).on("click", "button.editar", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();
		var url = '<?php echo SERVERURL; ?>php/plantillas/editarPlantillas.php';	
		$('#formularioPlantillas')[0].reset();
		$('#formularioPlantillas #plantillas_id').val(data.plantillas_id);
			
		$.ajax({
			type:'POST',
			url:url,
			data:$('#formularioPlantillas').serialize(),
			success: function(registro){
				var valores = eval(registro);
				$('#formularioPlantillas').attr({ 'data-form': 'update' }); 
				$('#formularioPlantillas').attr({ 'action': '<?php echo SERVERURL; ?>php/plantillas/modificarPlantillas.php' }); 
				$('#reg_plantilla').hide();
				$('#edi_plantilla').show();
				$('#delete_plantilla').hide();	
				$('#formularioPlantillas #plantilla_atencion').val(valores[0]);
				$('#formularioPlantillas #plantilla_asunto').val(valores[2]);
				$('#formularioPlantillas #plantilla_descripcion').val(valores[3]);
				caracteresDescripcionPlantillas();
				
				//HABILITAR OBJETOS
				$('#formularioPlantillas #plantilla_descripcion').attr('readonly', false);
				
				//DESHABILITAR OBJETOS
				$('#formularioPlantillas #plantilla_asunto').attr('readonly', true);				
				$('#formularioPlantillas #plantilla_atencion').attr('disabled', true);
				
				$('#formularioPlantillas #pro').val("Editar");
				$('#modal_plantillas').modal({
					show:true,
					keyboard: false,
					backdrop:'static'
				});			
			}
		});			
	});
}

var delete_plantillas_dataTable = function(tbody, table){
	$(tbody).off("click", "button.delete");
	$(tbody).on("click", "button.delete", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();
		var url = '<?php echo SERVERURL; ?>php/plantillas/editarPlantillas.php';	
		$('#formularioPlantillas')[0].reset();
		$('#formularioPlantillas #plantillas_id').val(data.plantillas_id);
			
		$.ajax({
			type:'POST',
			url:url,
			data:$('#formularioPlantillas').serialize(),
			success: function(registro){
				var valores = eval(registro);
				$('#formularioPlantillas').attr({ 'data-form': 'update' }); 
				$('#formularioPlantillas').attr({ 'action': '<?php echo SERVERURL; ?>php/plantillas/eliminarPlantillas.php' }); 
				$('#reg_plantilla').hide();
				$('#edi_plantilla').hide();
				$('#delete_plantilla').show();
				$('#formularioPlantillas #plantilla_atencion').val(valores[0]);
				$('#formularioPlantillas #plantilla_asunto').val(valores[2]);
				$('#formularioPlantillas #plantilla_descripcion').val(valores[3]);				
				caracteresDescripcionPlantillas();
				
				//DESHABILITAR OBJETOS
				$('#formularioPlantillas #plantilla_atencion').attr('disabled', true);
				$('#formularioPlantillas #plantilla_asunto').attr('readonly', true);
				$('#formularioPlantillas #plantilla_descripcion').attr('readonly', true);			
				
				$('#formularioPlantillas #pro').val("Eliminar");
				$('#modal_plantillas').modal({
					show:true,
					keyboard: false,
					backdrop:'static'
				});	
			}
		});			
	});
}

$('#formularioPlantillas #plantilla_descripcion').keyup(function() {
	    var max_chars = 3200;
        var chars = $(this).val().length;
        var diff = max_chars - chars;
		
		$('#formularioPlantillas #charNum_plantilla_descripcion').html(diff + ' Caracteres'); 
		
		if(diff == 0){
			return false;
		}
});

function caracteresDescripcionPlantillas(){
	var max_chars = 3200;
	var chars = $('#formularioPlantillas #plantilla_descripcion').val().length;
	var diff = max_chars - chars;
	
	$('#formularioPlantillas #charNum_plantilla_descripcion').html(diff + ' Caracteres'); 
	
	if(diff == 0){
		return false;
	}
}

function getAtenciones(){
    var url = '<?php echo SERVERURL; ?>php/plantillas/getAtenciones.php';		
		
	$.ajax({
        type: "POST",
        url: url,
	    async: true,
        success: function(data){	
		    $('#formularioPlantillas #plantilla_atencion').html("");
			$('#formularioPlantillas #plantilla_atencion').html(data);
			$('#formularioPlantillas #plantilla_atencion').selectpicker('refresh');			
		}			
     });		
}

$('#formularioPlantillas #buscar_plantilla_atenciones').on('click', function(e){
	listar_plantillas_atenciones_buscar(); 
	$('#modal_busqueda_atenciones_plantillas').modal({
		show:true,
		keyboard: false,
		backdrop:'static'
	});			
});

var listar_plantillas_atenciones_buscar = function(){
	var table_plantillas_atenciones_buscar = $("#dataTableAtencionesPlantillas").DataTable({		
		"destroy":true,	
		"ajax":{
			"method":"POST",
			"url":"<?php echo SERVERURL; ?>php/plantillas/getPlantillasAtencionesTabla.php"
		},
		"columns":[
			{"defaultContent":"<button class='view btn btn-primary'><span class='fas fa-copy'></span></button>"},
			{"data":"nombre"}		
		],
		"pageLength" : 5,
        "lengthMenu": lengthMenu,
		"stateSave": true,
		"bDestroy": true,
		"language": idioma_español,	
	});	 
	table_plantillas_atenciones_buscar.search('').draw();
	$('#buscar').focus();
	
	view_plantillas_atenciones_busqueda_dataTable("#dataTableAtencionesPlantillas tbody", table_plantillas_atenciones_buscar);
}

var view_plantillas_atenciones_busqueda_dataTable = function(tbody, table){
	$(tbody).off("click", "button.view");		
	$(tbody).on("click", "button.view", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();		  
		$('#formularioPlantillas #plantilla_atencion').val(data.atenciones_id);
		$('#modal_busqueda_atenciones_plantillas').modal('hide');
	});
}
</script>