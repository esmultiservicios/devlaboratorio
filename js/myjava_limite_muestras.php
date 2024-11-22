<script>
/*INICIO DE FUNCIONES PARA ESTABLECER EL FOCUS PARA LAS VENTANAS MODALES*/
$(document).ready(function(){
    $("#modalLimiteMuestras").on('shown.bs.modal', function(){
        $(this).find('#formularioLimiteMuestras #limite').focus();
    });
});
/*FIN DE FUNCIONES PARA ESTABLECER EL FOCUS PARA LAS VENTANAS MODALES*/

$(document).ready(function(){
	listar_limiteMuestras();	
});	

var listar_limiteMuestras = function(){
	var table_limiteMuestras  = $("#dataTableLimiteMuestras").DataTable({
		"destroy":true,	
		"ajax":{
			"method":"POST",
			"url":"<?php echo SERVERURL; ?>php/LimiteMuestras/getLimiteMuestrasTabla.php"
		},		
		"columns":[
			{"data":"limite"},		
			{"defaultContent":"<button class='editar btn btn-warning'><span class='fas fa-edit'></span></button>"},
		],		
        "lengthMenu": lengthMenu,
		"stateSave": true,
		"bDestroy": true,		
		"language": idioma_español,//esta se encuenta en el archivo main.js
		"dom": dom,			
		"buttons":[		
			{
				text:      '<i class="fas fa-sync-alt fa-lg"></i> Actualizar',
				titleAttr: 'Actualizar Limite Muestras',
				className: 'btn btn-info',
				action: 	function(){
					listar_almacen();
				}
			},			
			{
				extend:    'excelHtml5',
				text:      '<i class="fas fa-file-excel fa-lg"></i> Excel',
				titleAttr: 'Excel',
				title: 'Reporte Muestras',
				className: 'btn btn-success'				
			},
			{
				extend:    'pdf',
				orientation: 'landscape',
				text:      '<i class="fas fa-file-pdf fa-lg"></i> PDF',
				titleAttr: 'PDF',
				title: 'Reporte Muestras',
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
	table_limiteMuestras.search('').draw();
	$('#buscar').focus();
	
	edit_limiteMuestras_dataTable("#dataTableLimiteMuestras tbody", table_limiteMuestras);
}

var edit_limiteMuestras_dataTable = function(tbody, table){
	$(tbody).off("click", "button.editar");
	$(tbody).on("click", "button.editar", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();
		var url = '<?php echo SERVERURL; ?>php/LimiteMuestras/editarLimiteMuestras.php';	
		$('#formularioLimiteMuestras')[0].reset();
			
		$.ajax({
			type:'POST',
			url:url,
			data:$('#formularioLimiteMuestras').serialize(),
			success: function(registro){
				var valores = eval(registro);
				$('#formularioLimiteMuestras ').attr({ 'data-form': 'update' }); 
				$('#formularioLimiteMuestras ').attr({ 'action': '<?php echo SERVERURL; ?>php/LimiteMuestras/modificaLimiteMuestras.php' }); 
				$('#ediLimite').show();
				$('#formularioLimiteMuestras #limite').val(valores[0]);
				
				$('#formularioLimiteMuestras #pro').val("Editar");
				$('#modalLimiteMuestras').modal({
					show:true,
					keyboard: false,
					backdrop:'static'
				});
			}
		});			
	});
}
</script>