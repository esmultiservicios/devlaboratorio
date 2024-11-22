<script>
/*INICIO DE FUNCIONES PARA ESTABLECER EL FOCUS PARA LAS VENTANAS MODALES*/
$(document).ready(function(){
    $("#modalAdministradorPrecios").on('shown.bs.modal', function(){
        $(this).find('#formularioAdministradorPrecios #hospitales_id').focus();
    });
});

/*FIN DE FUNCIONES PARA ESTABLECER EL FOCUS PARA LAS VENTANAS MODALES*/
/****************************************************************************************************************************************************************/
//INICIO CONTROLES DE ACCION
$(document).ready(function(){
	getHospitales();
	getPrecios();
	listar_administrador_precios();
});
//FIN CONTROLES DE ACCION

function agregarPrecios(){
	if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
		$('#formularioAdministradorPrecios')[0].reset();
    
		//HABILITAR CONTROLES PARA SOLO LECTURA
		$("#formularioAdministradorPrecios #hospitales_id").attr('disabled', false);
		$("#formularioAdministradorPrecios #precio").attr('readonly', false);

		 $('#reg_precios').show();
		 $('#edi_precios').hide();
		 $('#delete_precios').hide();

		 $('#formularioAdministradorPrecios #pro').val("Registro");
		 $('#formularioAdministradorPrecios').attr({ 'data-form': 'save' });
		 $('#formularioAdministradorPrecios').attr({ 'action': '<?php echo SERVERURL; ?>php/administrador_precios/agregarAdministardorPrecios.php' });

		 $('#modalAdministradorPrecios').modal({
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
}

var listar_administrador_precios = function(){
	var table_administrador_precios  = $("#dataTableAdministadorPrecios").DataTable({
		"destroy":true,
		"ajax":{
			"method":"POST",
			"url":"<?php echo SERVERURL; ?>php/administrador_precios/getAdministradorPreciosTabla.php"
		},
		"columns":[
			{"data":"hospital"},
			{"data":"precio"},
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
				titleAttr: 'Actualizar Administrador de Precios',
				className: 'btn btn-info',
				action: 	function(){
					listar_administrador_precios();
				}
			},
			{
				text:      '<i class="fas fa-plus-circle fa-lg"></i> Crear',
				titleAttr: 'Agregar Administrador de Precios',
				className: 'btn btn-primary',
				action: 	function(){
					agregarPrecios();
				}
			},
			{
				extend:    'excelHtml5',
				text:      '<i class="fas fa-file-excel fa-lg"></i> Excel',
				titleAttr: 'Excel',
				title: 'Reporte Administrador de Precios',
				className: 'btn btn-success'
			},
			{
				extend:    'pdf',
				orientation: 'landscape',
				text:      '<i class="fas fa-file-pdf fa-lg"></i> PDF',
				titleAttr: 'PDF',
				title: 'Reporte Administrador de Precios',
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
	table_administrador_precios.search('').draw();
	$('#buscar').focus();

	edit_administrador_precios_dataTable("#dataTableAdministadorPrecios tbody", table_administrador_precios);
	delete_administrador_precios_dataTable("#dataTableAdministadorPrecios tbody", table_administrador_precios);
}

var edit_administrador_precios_dataTable = function(tbody, table){
	$(tbody).off("click", "button.editar");
	$(tbody).on("click", "button.editar", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();
		var url = '<?php echo SERVERURL; ?>php/administrador_precios/editarAministradorPrecios.php';
		$('#formularioAdministradorPrecios')[0].reset();
		$('#formularioAdministradorPrecios #hospitales_id_consulta').val(data.hospitales_id);
		$('#formularioAdministradorPrecios #administrador_precios_id').val(data.administrador_precios_id);

		$.ajax({
			type:'POST',
			url:url,
			data:$('#formularioAdministradorPrecios').serialize(),
			success: function(registro){
				var valores = eval(registro);
				$('#formularioAdministradorPrecios').attr({ 'data-form': 'update' });
				$('#formularioAdministradorPrecios').attr({ 'action': '<?php echo SERVERURL; ?>php/administrador_precios/modificarAdministardorPrecios.php' });
				$('#reg_precios').hide();
				$('#edi_precios').show();
				$('#delete_precios').hide();
				$('#formularioAdministradorPrecios #hospitales_id').val(valores[0]);
        $('#formularioAdministradorPrecios #hospitales_id').selectpicker('refresh');
				$('#formularioAdministradorPrecios #precio').val(valores[1]);
        $('#formularioAdministradorPrecios #precio').selectpicker('refresh');

				//HABILITAR OBJETOS
				$('#formularioAdministradorPrecios #precio').attr("readonly", false);

				//DESHABILITAR OBJETOS
				$('#formularioAdministradorPrecios #hospitales_id').attr("disabled", true);

				$('#formularioAdministradorPrecios #pro').val("Editar");
				$('#modalAdministradorPrecios').modal({
					show:true,
					keyboard: false,
					backdrop:'static'
				});
			}
		});
	});
}

var delete_administrador_precios_dataTable = function(tbody, table){
	$(tbody).off("click", "button.delete");
	$(tbody).on("click", "button.delete", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();
		var url = '<?php echo SERVERURL; ?>php/administrador_precios/editarAministradorPrecios.php';
		$('#formularioAdministradorPrecios')[0].reset();
		$('#formularioAdministradorPrecios #hospitales_id_consulta').val(data.hospitales_id);
		$('#formularioAdministradorPrecios #administrador_precios_id').val(data.administrador_precios_id);

		$.ajax({
			type:'POST',
			url:url,
			data:$('#formularioAdministradorPrecios').serialize(),
			success: function(registro){
				var valores = eval(registro);
				$('#formularioAdministradorPrecios').attr({ 'data-form': 'update' });
				$('#formularioAdministradorPrecios').attr({ 'action': '<?php echo SERVERURL; ?>php/administrador_precios/eliminarAdministardorPrecios.php' });
				$('#reg_precios').hide();
				$('#edi_precios').hide();
				$('#delete_precios').show();
				$('#formularioAdministradorPrecios #hospitales_id').val(valores[0]);
        $('#formularioAdministradorPrecios #hospitales_id').selectpicker('refresh');
				$('#formularioAdministradorPrecios #precio').val(valores[1]);
        $('#formularioAdministradorPrecios #precio').selectpicker('refresh');

				//DESHABILITAR OBJETOS
				$('#formularioAdministradorPrecios #hospitales_id').attr("disabled", true);
				$('#formularioAdministradorPrecios #precio').attr("readonly", true);

				$('#formularioAdministradorPrecios #pro').val("Eliminar");
				$('#modalAdministradorPrecios').modal({
					show:true,
					keyboard: false,
					backdrop:'static'
				});
			}
		});
	});
}

function getHospitales(){
    var url = '<?php echo SERVERURL; ?>php/administrador_precios/getHospitales.php';

	$.ajax({
        type: "POST",
        url: url,
	    async: true,
        success: function(data){
		    $('#formularioAdministradorPrecios #hospitales_id').html("");
			  $('#formularioAdministradorPrecios #hospitales_id').html(data);
        $('#formularioAdministradorPrecios #hospitales_id').selectpicker('refresh');
		}
     });
}

function getPrecios(){
    var url = '<?php echo SERVERURL; ?>php/administrador_precios/getPrecio.php';

    $.ajax({
      type: "POST",
      url: url,
      async: true,
      success: function(data){
        $('#formularioAdministradorPrecios #precio').html("");
        $('#formularioAdministradorPrecios #precio').html(data);
        $('#formularioAdministradorPrecios #precio').selectpicker('refresh');
      }
    });
}

</script>
