<script>
/*INICIO DE FUNCIONES PARA ESTABLECER EL FOCUS PARA LAS VENTANAS MODALES*/
$(document).ready(function(){
    $("#modalHospitales").on('shown.bs.modal', function(){
        $(this).find('#formularioHospitales #hospitales').focus();
    });
});
/*FIN DE FUNCIONES PARA ESTABLECER EL FOCUS PARA LAS VENTANAS MODALES*/

$(document).ready(function() {
	listar_hospitales_consulta();
});	

function agregarHospitales(){
	if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
		$('#formularioHospitales').attr({ 'data-form': 'save' });
		$('#formularioHospitales').attr({ 'action': '<?php echo SERVERURL; ?>php/hospitales/agregarHospitales.php' });			
		$('#reg_hospitales').show();
		$('#edi_hospitales').hide();
		$('#delete_hospitales').hide();		
		$('#formularioHospitales')[0].reset();	
		$('#formularioHospitales #pro').val('Registro');
		
		//HABILITAR OBJETOS
		 $('#formularioHospitales #hospitales').attr("readonly", false);				
				
		 $('#modalHospitales').modal({
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
		});edi
	} 	
}

var listar_hospitales_consulta = function(){
	var table_hospitales_consulta  = $("#dataTableHospitalesConsulta").DataTable({
		"destroy":true,	
		"ajax":{
			"method":"POST",
			"url":"<?php echo SERVERURL; ?>php/hospitales/getHospitalesTabla.php"
		},		
		"columns":[
			{"data":"nombre"},		
			{"defaultContent":"<button class='editar btn btn-warning'><span class='fas fa-edit'></span></button>"},
			{"defaultContent":"<button class='delete btn btn-danger'><span class='fa fa-trash'></span></button>"}
		],		
        "lengthMenu": lengthMenu10,
		"stateSave": true,
		"bDestroy": true,		
		"language": idioma_español,//esta se encuenta en el archivo main.js
		"dom": dom,			
		"buttons":[		
			{
				text:      '<i class="fas fa-sync-alt fa-lg"></i> Actualizar',
				titleAttr: 'Actualizar Hospitales',
				className: 'btn btn-info',
				action: 	function(){
					listar_hospitales_consulta();
				}
			},		
			{
				text:      '<i class="fas fa-hospital fa-lg"></i> Crear',
				titleAttr: 'Agregar Hospitales',
				className: 'btn btn-primary',
				action: 	function(){
					agregarHospitales();
				}
			},				
			{
				extend:    'excelHtml5',
				text:      '<i class="fas fa-file-excel fa-lg"></i> Excel',
				titleAttr: 'Excel',
				title: 'Reporte Hospitales',
				className: 'btn btn-success'				
			},
			{
				extend:    'pdf',
				orientation: 'landscape',
				text:      '<i class="fas fa-file-pdf fa-lg"></i> PDF',
				titleAttr: 'PDF',
				title: 'Reporte Hospitales',
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
	table_hospitales_consulta.search('').draw();
	$('#buscar').focus();
	
	edit_hospitales_consulta_dataTable("#dataTableHospitalesConsulta tbody", table_hospitales_consulta);
	delete_hospitales_consulta_dataTable("#dataTableHospitalesConsulta tbody", table_hospitales_consulta);
}

var edit_hospitales_consulta_dataTable = function(tbody, table){
	$(tbody).off("click", "button.editar");
	$(tbody).on("click", "button.editar", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();
		var url = '<?php echo SERVERURL; ?>php/hospitales/editarHospitales.php';	
		$('#formularioHospitales')[0].reset();
		$('#formularioHospitales #hospitales_id').val(data.hospitales_id);
			
		$.ajax({
			type:'POST',
			url:url,
			data:$('#formularioHospitales').serialize(),
			success: function(registro){
				var valores = eval(registro);
				$('#formularioHospitales').attr({ 'data-form': 'update' }); 
				$('#formularioHospitales').attr({ 'action': '<?php echo SERVERURL; ?>php/hospitales/modificarHospitales.php' }); 
				$('#reg_hospitales').hide();
				$('#edi_hospitales').show();
				$('#delete_hospitales').hide();
				$('#formularioHospitales #hospitales').val(valores[0]);
				
				$('#formularioHospitales #pro').val("Editar");
				$('#modalHospitales').modal({
					show:true,
					keyboard: false,
					backdrop:'static'
				});
			}
		});			
	});
}

var delete_hospitales_consulta_dataTable = function(tbody, table){
	$(tbody).off("click", "button.delete");
	$(tbody).on("click", "button.delete", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();
		var url = '<?php echo SERVERURL; ?>php/hospitales/editarHospitales.php';	
		$('#formularioHospitales')[0].reset();
		$('#formularioHospitales #hospitales_id').val(data.hospitales_id);
			
		$.ajax({
			type:'POST',
			url:url,
			data:$('#formularioHospitales').serialize(),
			success: function(registro){
				var valores = eval(registro);
				$('#formularioHospitales').attr({ 'data-form': 'update' }); 
				$('#formularioHospitales').attr({ 'action': '<?php echo SERVERURL; ?>php/hospitales/eliminarHospitales.php' }); 
				$('#reg_hospitales').hide();
				$('#edi_hospitales').hide();
				$('#delete_hospitales').show();
				$('#formularioHospitales #hospitales').val(valores[0]);

				//DESHABILITAR OBJETOS
				$('#formularioHospitales #hospitales').attr("readonly", true);			
				
				$('#formularioHospitales #pro').val("Eliminar");
				$('#modalHospitales').modal({
					show:true,
					keyboard: false,
					backdrop:'static'
				});
			}
		});			
	});
}
</script>