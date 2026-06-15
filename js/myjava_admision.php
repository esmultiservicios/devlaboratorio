<script>
// ===== CACHÉ PARA SELECTS =====
var catalogosCargados = false;

// ===== HELPER SELECT2 GLOBAL =====
function aplicarSelect2($select, opciones){
  if (!$select || !$select.length) return;

  if (!$.fn.select2) {
    setTimeout(function(){
      aplicarSelect2($select, opciones);
    }, 150);
    return;
  }

  $select.each(function(){
    var $s = $(this);

    if (!$s.length) return;

    var valorActual = $s.val();

    if ($s.data('select2')) {
      $s.select2('destroy');
    }

    $s.next('.select2').remove();

    var config = $.extend({
      width: $s.attr('style') && $s.attr('style').indexOf('width') >= 0 ? 'style' : '100%',
      placeholder: '',
      minimumResultsForSearch: 0,
      dropdownAutoWidth: false
    }, opciones || {});

    $s.select2(config);

    if (valorActual !== null && valorActual !== undefined && valorActual !== '') {
      $s.val(valorActual).trigger('change.select2');
    }
  });
}

// ===== SELECT2 PRINCIPALES - OPCIONES FIJAS =====
function aplicarSelect2VistaPrincipal(){
  aplicarSelect2($('#form_main_admision select[name="estado"]'), {
    width: '130px',
    placeholder: 'Estado',
    minimumResultsForSearch: 0
  });

  aplicarSelect2($('#form_main_admision select[name="tipo"]'), {
    width: '130px',
    placeholder: 'Tipo',
    minimumResultsForSearch: 0
  });

  aplicarSelect2($('#form_main_admision_muestras select[name="estado"]'), {
    width: '130px',
    placeholder: 'Estado',
    minimumResultsForSearch: 0
  });

  aplicarSelect2($('#form_main_admision_muestras select[name="tipo"]'), {
    width: '130px',
    placeholder: 'Tipo',
    minimumResultsForSearch: 0
  });

  aplicarSelect2($('#form_main_admision_muestras select[name="tipo_muestra"]'), {
    width: '180px',
    placeholder: 'Tipo Muestra',
    minimumResultsForSearch: 0
  });

  var $cliente = $('#form_main_admision_muestras select[name="cliente"]');
  if ($cliente.length && $cliente.find('option').length > 0) {
    aplicarSelect2($cliente, {
      width: '250px',
      placeholder: 'Cliente',
      minimumResultsForSearch: 0
    });
  }
}

// ===== SI ALGÚN SELECT PRINCIPAL QUEDA NATIVO, LO MONTA AL CLIC =====
$(document).on('mousedown', '#form_main_admision select, #form_main_admision_muestras select', function(e){
  var $s = $(this);

  if (!$.fn.select2) return;

  if (!$s.hasClass('select2-hidden-accessible')) {
    e.preventDefault();

    if ($s.attr('name') === 'estado') {
      aplicarSelect2($s, {
        width: '130px',
        placeholder: 'Estado',
        minimumResultsForSearch: 0
      });
    } else if ($s.attr('name') === 'tipo') {
      aplicarSelect2($s, {
        width: '130px',
        placeholder: 'Tipo',
        minimumResultsForSearch: 0
      });
    } else if ($s.attr('name') === 'tipo_muestra') {
      aplicarSelect2($s, {
        width: '180px',
        placeholder: 'Tipo Muestra',
        minimumResultsForSearch: 0
      });
    } else if ($s.attr('name') === 'cliente') {
      aplicarSelect2($s, {
        width: '250px',
        placeholder: 'Cliente',
        minimumResultsForSearch: 0
      });
    } else {
      aplicarSelect2($s, {
        width: '100%',
        minimumResultsForSearch: 0
      });
    }

    setTimeout(function(){
      $s.select2('open');
    }, 50);
  }
});

// ===== FORZAR FOCUS AL BUSCADOR DE SELECT2 =====
$(document).on('select2:open', function(){
  setTimeout(function(){
    var search = document.querySelector('.select2-container--open .select2-search__field');
    if (search) search.focus();
  }, 50);
});

// ===== FUNCIONES DE CARGA CON SELECT2 =====
function getEstadoMuestra(){
  var url = SERVERURL + 'php/admision/getStatusMuestra.php';

  return $.ajax({
    type:'POST',
    url:url
  }).done(function(data){
    var $s = $('#form_main_admision_muestras select[name="estado"]');

    $s.html(data);

    aplicarSelect2($s, {
      width: '130px',
      placeholder: 'Estado',
      minimumResultsForSearch: 0
    });
  });
}

function getEstadoPaciente(){
  var url = SERVERURL + 'php/admision/getStatusPaciente.php';

  return $.ajax({
    type:'POST',
    url:url
  }).done(function(data){
    var $s = $('#form_main_admision select[name="estado"]');

    $s.html(data);

    aplicarSelect2($s, {
      width: '130px',
      placeholder: 'Estado',
      minimumResultsForSearch: 0
    });
  });
}

function getTipoMuestra(){
  var url = SERVERURL + 'php/admision/getTipoMuestra.php';

  return $.ajax({
    type:'POST',
    url:url
  }).done(function(data){
    var $a = $('#formulario_admision select[name="tipo_muestra"], #formulario_admision #tipo_muestra');
    var $b = $('#form_main_admision_muestras select[name="tipo_muestra"]');

    $a.html(data);
    $b.html(data);

    aplicarSelect2($a, {
      width: '100%',
      placeholder: 'Tipo Muestra',
      minimumResultsForSearch: 0,
      dropdownParent: $('#modal_admision_clientes')
    });

    aplicarSelect2($b, {
      width: '180px',
      placeholder: 'Tipo Muestra',
      minimumResultsForSearch: 0
    });
  });
}

function getGenero(){
  var url = SERVERURL + 'php/admision/getSexo.php';

  return $.ajax({
    type:'POST',
    url:url
  }).done(function(data){
    var $a = $('#formulario_admision select[name="genero"], #formulario_admision #genero');
    var $b = $('#formulario_admision_clientes_editar select[name="genero"], #formulario_admision_clientes_editar #genero');

    $a.html(data);
    $b.html(data);

    aplicarSelect2($a, {
      width: '100%',
      placeholder: 'Género',
      minimumResultsForSearch: 0,
      dropdownParent: $('#modal_admision_clientes')
    });

    aplicarSelect2($b, {
      width: '100%',
      placeholder: 'Género',
      minimumResultsForSearch: 0,
      dropdownParent: $('#modal_admision_clientes_editar')
    });
  });
}

function getEmpresa(){
  var url = SERVERURL + 'php/admision/getEmpresa.php';

  return $.ajax({
    type:'POST',
    url:url
  }).done(function(data){
    var $s = $('#formulario_admision select[name="empresa"], #formulario_admision #empresa');

    $s.html(data);

    aplicarSelect2($s, {
      width: '100%',
      placeholder: 'Empresa',
      minimumResultsForSearch: 0,
      dropdownParent: $('#modal_admision_clientes')
    });
  });
}

function getTipo(){
  var url = SERVERURL + 'php/admision/getTipoPaciente.php';

  return $.ajax({
    type:'POST',
    url:url
  }).done(function(data){
    var $s = $('#formulario_admision select[name="paciente_tipo"], #formulario_admision #paciente_tipo');

    $s.html(data);

    aplicarSelect2($s, {
      width: '100%',
      placeholder: 'Tipo',
      minimumResultsForSearch: 0,
      dropdownParent: $('#modal_admision_clientes')
    });
  });
}

function getRemitente(){
  var url = SERVERURL + 'php/admision/getRemitente.php';

  return $.ajax({
    type:'POST',
    url:url
  }).done(function(data){
    var $s = $('#formulario_admision select[name="remitente"], #formulario_admision #remitente');

    $s.html(data);

    aplicarSelect2($s, {
      width: '100%',
      placeholder: 'Remitente',
      minimumResultsForSearch: 0,
      dropdownParent: $('#modal_admision_clientes')
    });
  });
}

function getHospitales(){
  var url = SERVERURL + 'php/admision/getHospitales.php';

  return $.ajax({
    type:'POST',
    url:url
  }).done(function(data){
    var $a = $('#formulario_admision select[name="hospital"], #formulario_admision #hospital');
    var $b = $('#formulario_admision_empresas select[name="hospital_empresa"], #formulario_admision_empresas #hospital_empresa');

    $a.html(data);
    $b.html(data);

    aplicarSelect2($a, {
      width: '100%',
      placeholder: 'Hospital/Clínica',
      minimumResultsForSearch: 0,
      dropdownParent: $('#modal_admision_clientes')
    });

    aplicarSelect2($b, {
      width: '100%',
      placeholder: 'Hospital/Clínica',
      minimumResultsForSearch: 0,
      dropdownParent: $('#modal_admision_empesas')
    });
  });
}

function getCategorias(){
  var url = SERVERURL + 'php/admision/getCategoriaMuestra.php';

  return $.ajax({
    type:'POST',
    url:url
  }).done(function(data){
    var $s = $('#formulario_admision select[name="categoria"], #formulario_admision #categoria');

    $s.html(data);

    aplicarSelect2($s, {
      width: '100%',
      placeholder: 'Categoría',
      minimumResultsForSearch: 0,
      dropdownParent: $('#modal_admision_clientes')
    });
  });
}

function getTipoPacienteSelect(){
  var url = SERVERURL + 'php/admision/getTipoPaciente.php';

  return $.ajax({
    type:'POST',
    url:url
  }).done(function(data){
    var $a = $('#form_main_admision select[name="tipo"]');
    var $b = $('#form_main_admision_muestras select[name="tipo"]');

    $a.html(data);
    $b.html(data);

    aplicarSelect2($a, {
      width: '130px',
      placeholder: 'Tipo',
      minimumResultsForSearch: 0
    });

    aplicarSelect2($b, {
      width: '130px',
      placeholder: 'Tipo',
      minimumResultsForSearch: 0
    });
  });
}

function getProductos(){
  var url = SERVERURL + 'php/admision/getProductos.php';
  var tipo_muestra_id = $('#formulario_admision #tipo_muestra').val();
  var $p = $('#formulario_admision select[name="producto"], #formulario_admision #producto');

  $p.html('<option value="">Cargando...</option>');

  aplicarSelect2($p, {
    width: '100%',
    placeholder: 'Producto',
    minimumResultsForSearch: 0,
    dropdownParent: $('#modal_admision_clientes')
  });

  $.ajax({
    type:'POST',
    url:url,
    data: { tipo_muestra_id: tipo_muestra_id },
    async: false,
    success:function(data){
      $p.html(data);

      aplicarSelect2($p, {
        width: '100%',
        placeholder: 'Producto',
        minimumResultsForSearch: 0,
        dropdownParent: $('#modal_admision_clientes')
      });
    }
  });
}

// ===== CARGAR TODOS LOS CATÁLOGOS UNA SOLA VEZ =====
function cargarCatalogosIniciales(callback){
  if(catalogosCargados){
    if(typeof callback === 'function') callback();
    return;
  }

  var cargas = [
    getGenero(),
    getTipoMuestra(),
    getEmpresa(),
    getRemitente(),
    getHospitales(),
    getCategorias(),
    getTipo()
  ];

  if (typeof getServicio === 'function') cargas.push(getServicio());

  $.when.apply($, cargas).always(function(){
    catalogosCargados = true;

    if(typeof callback === 'function') callback();
  });
}

// ===== ORQUESTADOR =====
function initPage(){
  var essentials = [
    getEstadoPaciente(),
    getTipoPacienteSelect()
  ];

  var extras = [
    getEstadoMuestra()
  ];

  $.when.apply($, essentials).always(function(){
    aplicarSelect2VistaPrincipal();
    pagination(1, true);
  });

  $.when.apply($, extras).always(function(){
    aplicarSelect2VistaPrincipal();
  });

  cargarCatalogosIniciales(function(){
    aplicarSelect2VistaPrincipal();
  });
}

// ===== EVENTOS DE FOCO EN MODALES =====
$(document).ready(function(){
  $("#modal_historico_muestras").on('shown.bs.modal', function(){
    $(this).find('#form_main_historico_muestras #bs_regis').focus();
  });

  $("#modal_admision_clientes").on('shown.bs.modal', function(){
    $(this).find('#formulario_admision #name').focus();
  });

  $("#modal_admision_clientes_editar").on('shown.bs.modal', function(){
    $(this).find('#formulario_admision_clientes_editar #name').focus();
  });

  $("#modal_admision_empesas").on('shown.bs.modal', function(){
    $(this).find('#formulario_admision_empresas #empresa').focus();
  });
});

// ===== LISTENERS =====
$(document).ready(function(){
  $('#form_main_admision #bs_regis').on('keyup', function(){
    pagination(1);
  });

  $(document).on('change', '#form_main_admision select[name="estado"]', function(){
    pagination(1);
  });

  $(document).on('change', '#form_main_admision select[name="tipo"]', function(){
    pagination(1);
  });

  $(document).on('change', '#form_main_admision_muestras select[name="estado"]', function(){
    paginationMuestras(1);
  });

  $(document).on('change', '#form_main_admision_muestras select[name="cliente"]', function(){
    paginationMuestras(1);
  });

  $(document).on('change', '#form_main_admision_muestras select[name="tipo_muestra"]', function(){
    paginationMuestras(1);
  });

  $('#form_main_admision_muestras #buscar_registro').on('click', function(e){
    e.preventDefault();
    paginationMuestras(1);
  });

  $('#formulario_admision #fecha_nac').on('change', function(){
    CalcularEdadClientes();
  });

  $('#formulario_admision #tipo_muestra').on('change', function(){
    getProductos();
  });
});

// ===== UTILIDADES =====
function CalcularEdadClientes(){
  var url = SERVERURL + 'php/admision/calcularEdad.php';

  $.ajax({
    type:'POST',
    data: {
      fecha_nac: $('#formulario_admision #fecha_nac').val()
    },
    url: url,
    success: function(data){
      $('#formulario_admision #edad').val(data);
    }
  });

  return false;
}

function getFechaActual(){
  var url = SERVERURL + 'php/admision/getFechaActual.php';
  var fecha_actual;

  $.ajax({
    type:'POST',
    url:url,
    async:false,
    success:function(data){
      fecha_actual = data;
    }
  });

  return fecha_actual;
}

function getPacienteTipo(pacientes_id){
  var url = SERVERURL + 'php/admision/getPacienteTipo.php';
  var tipo_paciente;

  $.ajax({
    type:'POST',
    url:url,
    data: {
      pacientes_id: pacientes_id
    },
    async:false,
    success:function(data){
      tipo_paciente = data;
    }
  });

  return tipo_paciente;
}

// ===== DEPENDENCIA DE SELECTS =====
$(document).on('change', '#form_main_admision_muestras select[name="tipo"]', function(){
  var url = SERVERURL + 'php/admision/getEmpresaCliente.php';
  var tipo = $(this).val();
  var $c = $('#form_main_admision_muestras select[name="cliente"]');

  $c.html('<option value="">Cargando...</option>');

  aplicarSelect2($c, {
    width: '250px',
    placeholder: 'Cliente',
    minimumResultsForSearch: 0
  });

  $.ajax({
    type:'POST',
    url:url,
    data: {
      tipo: tipo
    },
    async: false,
    success:function(data){
      $c.html(data);

      aplicarSelect2($c, {
        width: '250px',
        placeholder: 'Cliente',
        minimumResultsForSearch: 0
      });
    }
  });
});

// ===== PAGINACIÓN =====
function pagination(partida, firstLoad){
  var url = SERVERURL + 'php/admision/paginar.php';
  var tipo = $('#form_main_admision select[name="tipo"]').val() || 1;
  var dato = $('#form_main_admision #bs_regis').val() || '';
  var estado = $('#form_main_admision select[name="estado"]').val() || 1;

  $.ajax({
    type:'POST',
    url:url,
    data: {
      partida: partida,
      tipo: tipo,
      dato: dato,
      estado: estado
    },
    dataType: 'json',
    success: function(array){
      $('#agrega-registros').html(array[0]);
      $('#pagination').html(array[1]);

      setTimeout(function(){
        aplicarSelect2VistaPrincipal();
      }, 50);
    },
    error: function(){
      if (firstLoad) {
        setTimeout(function(){
          pagination(partida, false);
        }, 150);
      }
    }
  });

  return false;
}

function paginationMuestras(partida){
  var url = SERVERURL + 'php/admision/paginarMuestras.php';
  var estado = $('#form_main_admision_muestras select[name="estado"]').val();
  var cliente = $('#form_main_admision_muestras select[name="cliente"]').val() || '';
  var tipo_muestra = $('#form_main_admision_muestras select[name="tipo_muestra"]').val() || '';
  var fecha_i = $('#form_main_admision_muestras #fecha_i').val() || '';
  var fecha_f = $('#form_main_admision_muestras #fecha_f').val() || '';
  var dato = $('#form_main_admision_muestras #bs_regis').val() || '';

  $.ajax({
    type:'POST',
    url:url,
    data: {
      partida: partida,
      estado: estado,
      cliente: cliente,
      tipo_muestra: tipo_muestra,
      fecha_i: fecha_i,
      fecha_f: fecha_f,
      dato: dato
    },
    dataType: 'json',
    beforeSend: function(){
      if (typeof showLoading === 'function') showLoading("Por favor espere...");
    },
    success: function(array){
      $('#agrega-registros_muestras').html(array[0]);
      $('#pagination_muestras').html(array[1]);

      setTimeout(function(){
        aplicarSelect2VistaPrincipal();
      }, 50);
    },
    error: function(){
      if (typeof swal === 'function'){
        swal({
          title:'Error',
          text:'No se enviaron los datos, favor corregir',
          icon:'error',
          dangerMode:true
        });
      }
    },
    complete: function(){
      if (typeof hideLoading === 'function') hideLoading();
    }
  });

  return false;
}

// ===== BOTONES =====
$('#form_main_admision #registrar_cliente').on('click', function(e){
  e.preventDefault();
  modalClientes();
});

$('#formulario_admision #nuevo_admision').on('click', function(e){
  e.preventDefault();

  $('#formulario_admision #name').val("");
  $('#formulario_admision #lastname').val("");
  $('#formulario_admision #rtn').val(0);
  $('#formulario_admision #telefono1').val("");
  $('#formulario_admision #direccion').val("");
  $('#formulario_admision #correo').val("");

  if ($.fn.select2) {
    $('#formulario_admision #cliente_admision').val(null).trigger('change');
  } else {
    $('#formulario_admision #cliente_admision').val("");
  }

  $('#formulario_admision #name').focus();
});

$('#formulario_admision_empresas #nuevo_admision_empresa').on('click', function(e){
  e.preventDefault();

  $('#formulario_admision_empresas #empresa').val("");
  $('#formulario_admision_empresas #rtn').val(0);
  $('#formulario_admision_empresas #telefono1').val("");
  $('#formulario_admision_empresas #direccion').val("");
  $('#formulario_admision_empresas #correo').val("");
  $('#formulario_admision_empresas #empresa').focus();
});

$('#formulario_admision #nuevo_admision_muestra').on('click', function(e){
  e.preventDefault();

  $('#formulario_admision #sitio_muestra').val("");
  $('#formulario_admision #diagnostico_clinico').val("");
  $('#formulario_admision #material_enviado').val("");
  $('#formulario_admision #datos_clinicos').val("");
  $('#formulario_admision #producto').html("");

  aplicarSelect2($('#formulario_admision #producto'), {
    width: '100%',
    placeholder: 'Producto',
    minimumResultsForSearch: 0,
    dropdownParent: $('#modal_admision_clientes')
  });
});

// ===== CONSULTAS AUXILIARES =====
function consultarExpediente(pacientes_id){
  var url = SERVERURL + 'php/pacientes/getExpedienteInformacion.php';
  var resp;

  $.ajax({
    type:'POST',
    url:url,
    data: {
      pacientes_id: pacientes_id
    },
    async:false,
    success:function(data){
      resp = data;
    }
  });

  return resp;
}

function consultarNumeroMuestra(muestras_id){
  var url = SERVERURL + 'php/admision/getNumeroMuestra.php';
  var resp;

  $.ajax({
    type:'POST',
    url:url,
    data: {
      muestras_id: muestras_id
    },
    async:false,
    success:function(data){
      resp = data;
    }
  });

  return resp;
}

function consultarNombre(pacientes_id){
  var url = SERVERURL + 'php/pacientes/getNombre.php';
  var resp;

  $.ajax({
    type:'POST',
    url:url,
    data: {
      pacientes_id: pacientes_id
    },
    async:false,
    success:function(data){
      resp = data;
    }
  });

  return resp;
}

// ===== ANULAR / ELIMINAR =====
function anularRegistroMuestra(muestras_id, pacientes_id, comentario){
  var url = SERVERURL + 'php/admision/anularMuestras.php';

  $.ajax({
    type:'POST',
    url:url,
    data: {
      muestras_id: muestras_id,
      pacientes_id: pacientes_id,
      comentario: comentario
    },
    success: function(registro){
      if(registro == 1){
        showNotify("success", "Success", "Registro anulado correctamente");
        paginationMuestras(1);
      }else if(registro == 2){
        showNotify("error", "Error", "Lo sentimos ya existe una factura para esta muestra, por favor anule la factura antes de proceder.");
      }else if(registro == 3){
        showNotify("error", "Error", "No se puede anular este registro");
      }else{
        showNotify("error", "Error", "Error al completar el registro");
      }
    }
  });

  return false;
}

function eliminarRegistroMuestra(muestras_id, pacientes_id, comentario){
  var url = SERVERURL + 'php/admision/eliminarMuestras.php';

  $.ajax({
    type:'POST',
    url:url,
    data: {
      muestras_id: muestras_id,
      pacientes_id: pacientes_id,
      comentario: comentario
    },
    success: function(registro){
      if(registro == 1){
        showNotify("success", "Success", "Registro eliminado correctamente");
        paginationMuestras(1);
      }else if(registro == 2){
        showNotify("error", "Error", "No se puede eliminar este registro");
      }else if(registro == 3){
        showNotify("error", "Error", "No se puede eliminar este registro, cuenta con información almacenada");
      }else{
        showNotify("error", "Error", "Error al completar el registro");
      }
    }
  });

  return false;
}

function eliminarRegistro(pacientes_id, comentario){
  var url = SERVERURL + 'php/admision/eliminar.php';

  $.ajax({
    type:'POST',
    url:url,
    data: {
      id: pacientes_id,
      comentario: comentario
    },
    success: function(registro){
      if(registro == 1){
        showNotify("success", "Success", "Registro eliminado correctamente");
        pagination(1);
      }else if(registro == 2){
        showNotify("error", "Error", "No se puede eliminar este registro");
      }else if(registro == 3){
        showNotify("error", "Error", "No se puede eliminar este registro, cuenta con información almacenada");
      }else{
        showNotify("error", "Error", "Error al completar el registro");
      }
    }
  });

  return false;
}

// ===== HABILITAR / INHABILITAR =====
function DisableRegister(pacientes_id){
  var nombre_usuario = consultarNombre(pacientes_id);
  var expediente_usuario = consultarExpediente(pacientes_id);
  var estado = $('#form_main_admision select[name="estado"]').val();
  var estado_label = (estado == "1") ? "Inhabilitar" : "Habilitar";
  var dato = (expediente_usuario == 0) ? nombre_usuario : nombre_usuario + " (Expediente: " + expediente_usuario + ")";

  swal({
    title: "¿Estas seguro?",
    text: "¿Desea " + estado_label + " este cliente: " + dato + "?",
    content: {
      element: "input",
      attributes: {
        placeholder: "Comentario",
        type: "text"
      }
    },
    icon: "warning",
    buttons: {
      cancel: "Cancelar",
      confirm: {
        text: "¡Sí, " + estado_label + " el cliente!",
        closeModal: false
      }
    },
    dangerMode: true,
    closeOnEsc: false,
    closeOnClickOutside: false
  }).then((value) => {
    if (value === null || value.trim() === "") return false;

    deshabilitarPaciente(pacientes_id, value, estado);
  });
}

function deshabilitarPaciente(pacientes_id, comentario, estado){
  var url = SERVERURL + 'php/admision/DeshabilitarPaciente.php';

  estado = (estado === null || estado === '') ? 1 : estado;

  $.ajax({
    type: 'POST',
    url: url,
    data: {
      pacientes_id: pacientes_id,
      comentario: comentario,
      estado: estado
    },
    dataType: "json",
    success: function(response){
      if (response.status === "success") {
        showNotify("success", "Success", response.message);
        pagination(1);
      } else {
        showNotify("error", "Error", response.message);
      }
    },
    error: function(){
      showNotify("error", "Error", "Error en la comunicación con el servidor");
    }
  });
}

// ===== MODALES DE ELIMINAR / ANULAR =====
function modal_eliminar(pacientes_id){
  if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
    var nombre_usuario = consultarNombre(pacientes_id);
    var expediente_usuario = consultarExpediente(pacientes_id);
    var dato = (expediente_usuario == 0) ? nombre_usuario : (nombre_usuario + " (Expediente: " + expediente_usuario + ")");

    swal({
      title: "¿Estas seguro?",
      text: "¿Desea eliminar este cliente: " + dato + "?",
      content: {
        element: "input",
        attributes: {
          placeholder: "Comentario",
          type: "text"
        }
      },
      icon: "warning",
      buttons: {
        cancel: "Cancelar",
        confirm: {
          text: "¡Sí, eliminar el cliente!",
          closeModal: false
        }
      },
      dangerMode: true,
      closeOnEsc: false,
      closeOnClickOutside: false
    }).then((value) => {
      if (value === null || value.trim() === "") {
        showNotify("error", "Error", "¡Necesita escribir algo!");
        return false;
      }

      eliminarRegistro(pacientes_id, value);
    });
  }else{
    showNotify("error", "Acceso Denegado", "No tiene permisos para ejecutar esta acción");
    return false;
  }
}

function modal_eliminarMuestras(pacientes_id, muestras_id){
  if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
    var nombre_usuario = consultarNombre(pacientes_id);
    var numero_muestra = consultarNumeroMuestra(muestras_id);
    var dato = nombre_usuario + " (Muestra: " + numero_muestra + ")";

    swal({
      title: "¿Estas seguro?",
      text: "¿Desea eliminar esta muestra para el cliente: " + dato + "?",
      content: {
        element: "input",
        attributes: {
          placeholder: "Comentario",
          type: "text"
        }
      },
      icon: "warning",
      buttons: {
        cancel: "Cancelar",
        confirm: {
          text: "¡Sí, eliminar la muestra!",
          closeModal: false
        }
      },
      dangerMode: true,
      closeOnEsc: false,
      closeOnClickOutside: false
    }).then((value) => {
      if (value === null || value.trim() === "") {
        showNotify("error", "Error", "¡Necesita escribir algo!");
        return false;
      }

      eliminarRegistroMuestra(muestras_id, pacientes_id, value);
    });
  }else{
    showNotify("error", "Acceso Denegado", "No tiene permisos para ejecutar esta acción");
    return false;
  }
}

function modalAnularMuestras(pacientes_id, muestras_id){
  if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
    var nombre_usuario = consultarNombre(pacientes_id);
    var numero_muestra = consultarNumeroMuestra(muestras_id);
    var dato = nombre_usuario + " (Muestra: " + numero_muestra + ")";

    swal({
      title: "¿Estas seguro?",
      text: "¿Desea anular esta muestra para el cliente: " + dato + "?",
      content: {
        element: "input",
        attributes: {
          placeholder: "Comentario",
          type: "text"
        }
      },
      icon: "warning",
      buttons: {
        cancel: "Cancelar",
        confirm: {
          text: "¡Sí, anular la muestra!",
          closeModal: false
        }
      },
      dangerMode: true,
      closeOnEsc: false,
      closeOnClickOutside: false
    }).then((value) => {
      if (value === null || value.trim() === "") {
        showNotify("error", "Error", "¡Necesita escribir algo!");
        return false;
      }

      anularRegistroMuestra(muestras_id, pacientes_id, value);
    });
  }else{
    showNotify("error", "Acceso Denegado", "No tiene permisos para ejecutar esta acción");
    return false;
  }
}

// ===== EDITAR REGISTROS =====
function editarRegistro(pacientes_id){
  if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
    var url = SERVERURL + 'php/admision/consultarClientes.php';

    $.ajax({
      type:'POST',
      url:url,
      data: {
        pacientes_id: pacientes_id
      },
      success: function(valores){
        var datos = JSON.parse(valores);

        if($('#form_main_admision select[name="tipo"]').val() == 1 || $('#form_main_admision select[name="tipo"]').val() == ""){
          var nombre_completo = datos[0] + " " + datos[1];

          $('#formulario_admision_clientes_editar #edi_admision').show();
          $('#formulario_admision_clientes_editar #pacientes_id').val(pacientes_id);
          $('#formulario_admision_clientes_editar #name').val(nombre_completo.trim());
          $('#formulario_admision_clientes_editar #lastname').val(datos[1]);
          $('#formulario_admision_clientes_editar #rtn').val(datos[2]);
          $('#formulario_admision_clientes_editar #edad').val(datos[3]);
          $('#formulario_admision_clientes_editar #telefono1').val(datos[4]);
          $('#formulario_admision_clientes_editar #telefono2').val(datos[8]);
          $('#formulario_admision_clientes_editar #genero').val(datos[5]);

          aplicarSelect2($('#formulario_admision_clientes_editar #genero'), {
            width: '100%',
            placeholder: 'Género',
            minimumResultsForSearch: 0,
            dropdownParent: $('#modal_admision_clientes_editar')
          });

          $('#formulario_admision_clientes_editar #genero').val(datos[5]).trigger('change');

          $('#formulario_admision_clientes_editar #direccion').val(datos[6]);
          $('#formulario_admision_clientes_editar #correo').val(datos[7]);

          $('#formulario_admision_clientes_editar').attr({
            'data-form': 'update'
          });

          $('#formulario_admision_clientes_editar').attr({
            'action': SERVERURL + 'php/admision/modificarRegistro.php'
          });

          $('#formulario_admision_clientes_editar #name').attr('readonly', false);
          $('#formulario_admision_clientes_editar #lastname').attr('readonly', false);
          $('#formulario_admision_clientes_editar #rtn').attr('readonly', false);
          $('#formulario_admision_clientes_editar #fecha_nac').attr('disabled', false);
          $('#formulario_admision_clientes_editar #edad').attr('readonly', false);
          $('#formulario_admision_clientes_editar #telefono1').attr('readonly', false);
          $('#formulario_admision_clientes_editar #genero').attr('disabled', false);
          $('#formulario_admision_clientes_editar #direccion').attr('readonly', false);
          $('#formulario_admision_clientes_editar #correo').attr('readonly', false);

          $('#modal_admision_clientes_editar').modal({
            show: true,
            keyboard: false,
            backdrop:'static'
          });
        }else{
          $('#reg_admisionemp').hide();
          $('#edi_admisionemp').show();

          $('#formulario_admision_empresas #pacientes_id').val(pacientes_id);
          $('#formulario_admision_empresas #empresa').val(datos[0]);
          $('#formulario_admision_empresas #rtn').val(datos[2]);
          $('#formulario_admision_empresas #edad').val(datos[3]);
          $('#formulario_admision_empresas #telefono1').val(datos[4]);
          $('#formulario_admision_empresas #direccion').val(datos[6]);
          $('#formulario_admision_empresas #correo').val(datos[7]);

          $('#formulario_admision_empresas').attr({
            'data-form': 'update'
          });

          $('#formulario_admision_empresas').attr({
            'action': SERVERURL + 'php/admision/modificarRegistroEmpresas.php'
          });

          $('#formulario_admision_empresas #name').attr('readonly', false);
          $('#formulario_admision_empresas #rtn').attr('readonly', false);
          $('#formulario_admision_empresas #telefono1').attr('readonly', false);
          $('#formulario_admision_empresas #direccion').attr('readonly', false);
          $('#formulario_admision_empresas #correo').attr('readonly', false);

          $('#modal_admision_empesas').modal({
            show: true,
            keyboard: false,
            backdrop:'static'
          });
        }

        return false;
      }
    });
  }else{
    showNotify("error", "Acceso Denegado", "No tiene permisos para ejecutar esta acción");
    return false;
  }
}

function modalEditar(pacientes_id){
  $('#formulario_admision_clientes_editar').attr({
    'data-form': 'update'
  });

  $('#formulario_admision_clientes_editar').attr({
    'action': SERVERURL + 'php/admision/agregarRegistro.php'
  });

  $('#formulario_admision_clientes_editar')[0].reset();
  $('#formulario_admision_clientes_editar #pro_admision').val("Registro");

  $('#reg_admision').show();
  $('#edi_admision').hide();
  $('#delete_admision').hide();

  $('#formulario_admision_clientes_editar #paciente_tipo').val(1);

  aplicarSelect2($('#formulario_admision_clientes_editar #paciente_tipo'), {
    width: '100%',
    placeholder: 'Tipo',
    minimumResultsForSearch: 0,
    dropdownParent: $('#modal_admision_clientes_editar')
  });

  $('#formulario_admision #hospital').val(getHospitalCodigo());

  aplicarSelect2($('#formulario_admision #hospital'), {
    width: '100%',
    placeholder: 'Hospital',
    minimumResultsForSearch: 0,
    dropdownParent: $('#modal_admision_clientes')
  });
}

function getHospitalCodigo(){
  var url = SERVERURL + 'php/pacientes/getHospitalCodigo.php';
  var resp;

  $.ajax({
    type:'POST',
    url:url,
    async: false,
    success:function(data){
      resp = data;
    }
  });

  return resp;
}

function getRemitenteCodigo(){
  var url = SERVERURL + 'php/pacientes/getRemitenteCodigo.php';
  var resp;

  $.ajax({
    type:'POST',
    url:url,
    async: false,
    success:function(data){
      resp = data;
    }
  });

  return resp;
}

// ===== HISTORIAL DE MUESTRAS =====
function showModalhistoriaMuestrasEmpresas(pacientes_id){
  if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
    $('#form_main_historico_muestras #pacientes_id_muestras').val(pacientes_id);

    $('#modal_historico_muestras').modal({
      show: true,
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
    showNotify("error", "Acceso Denegado", "No tiene permisos para ejecutar esta acción");
  }
}

$('#form_main_historico_muestras #bs_regis').on('keyup', function(){
  if(getPacienteTipo($('#form_main_historico_muestras #pacientes_id_muestras').val()) == 1){
    historiaMuestrasPacientes(1);
  }else{
    historiaMuestrasEmpresas(1);
  }
});

function historiaMuestrasEmpresas(partida){
  var url = SERVERURL + 'php/admision/paginar_historico_muestras_empresas.php';
  var pacientes_id = $('#modal_historico_muestras #pacientes_id_muestras').val();
  var dato = $('#form_main_historico_muestras #bs_regis').val();

  $.ajax({
    type:'POST',
    url:url,
    data: {
      partida: partida,
      pacientes_id: pacientes_id,
      dato: dato
    },
    dataType: 'json',
    success: function(array){
      $('#detalles-historico-muestras').html(array[0]);
      $('#pagination-historico-muestras').html(array[1]);
    }
  });

  return false;
}

function historiaMuestrasPacientes(partida){
  var url = SERVERURL + 'php/admision/paginar_historico_muestras_pacientes.php';
  var pacientes_id = $('#modal_historico_muestras #pacientes_id_muestras').val();
  var dato = $('#form_main_historico_muestras #bs_regis').val();

  $.ajax({
    type:'POST',
    url:url,
    data: {
      partida: partida,
      pacientes_id: pacientes_id,
      dato: dato
    },
    dataType: 'json',
    success: function(array){
      $('#detalles-historico-muestras').html(array[0]);
      $('#pagination-historico-muestras').html(array[1]);
    }
  });

  return false;
}

// ===== MODAL CLIENTES =====
function modalClientes(){
  $('#formulario_admision').attr({
    'data-form': 'save'
  });

  $('#formulario_admision').attr({
    'action': SERVERURL + 'php/admision/agregarRegistro.php'
  });

  $('#formulario_admision')[0].reset();
  $('#formulario_admision #pro_admision').val("Registro");
  $('#reg_admision').show();

  $('#formulario_admision #paciente_tipo').val(1);

  aplicarSelect2($('#formulario_admision #paciente_tipo'), {
    width: '100%',
    placeholder: 'Tipo',
    minimumResultsForSearch: 0,
    dropdownParent: $('#modal_admision_clientes')
  });

  $('#formulario_admision #hospital').val(getHospitalCodigo());

  aplicarSelect2($('#formulario_admision #hospital'), {
    width: '100%',
    placeholder: 'Hospital',
    minimumResultsForSearch: 0,
    dropdownParent: $('#modal_admision_clientes')
  });

  $('#formulario_admision #remitente').val(getRemitenteCodigo());

  aplicarSelect2($('#formulario_admision #remitente'), {
    width: '100%',
    placeholder: 'Remitente',
    minimumResultsForSearch: 0,
    dropdownParent: $('#modal_admision_clientes')
  });

  $('#formulario_admision #name').attr('readonly', false);
  $('#formulario_admision #lastname').attr('readonly', false);
  $('#formulario_admision #rtn').attr('readonly', false);
  $('#formulario_admision #fecha_nac').attr('disabled', false);
  $('#formulario_admision #edad').attr('readonly', false);
  $('#formulario_admision #telefono1').attr('readonly', false);
  $('#formulario_admision #genero').attr('disabled', false);
  $('#formulario_admision #direccion').attr('readonly', false);
  $('#formulario_admision #correo').attr('readonly', false);
  $('#formulario_admision #hospital').attr('disabled', false);
  $('#formulario_admision #empresa').attr('disabled', false);
  $('#formulario_admision #referencia').attr('readonly', false);
  $('#formulario_admision #tipo_muestra').attr('disabled', false);
  $('#formulario_admision #remitente').attr('readonly', false);
  $('#formulario_admision #categoria').attr('disabled', false);
  $('#formulario_admision #sitio_muestra').attr('readonly', false);
  $('#formulario_admision #diagnostico_clinico').attr('readonly', false);
  $('#formulario_admision #material_enviado').attr('readonly', false);
  $('#formulario_admision #datos_clinicos').attr('readonly', false);
  $('#formulario_admision #mostrar_datos_clinicos').attr('disabled', false);

  var $clienteAdmision = $('#formulario_admision #cliente_admision');

  if ($.fn.select2) {
    if ($clienteAdmision.data('select2')) {
      $clienteAdmision.select2('destroy');
    }

    $clienteAdmision.next('.select2').remove();

    $clienteAdmision.select2({
      width: '100%',
      dropdownParent: $('#modal_admision_clientes'),
      placeholder: 'Escriba para buscar un cliente...',
      minimumInputLength: 0,
      minimumResultsForSearch: 0,
      ajax: {
        url: SERVERURL + 'php/admision/getClientes.php',
        dataType: 'json',
        delay: 300,
        data: function(params) {
          return {
            term: params.term || ''
          };
        },
        processResults: function(data) {
          return {
            results: data.results || []
          };
        },
        cache: false
      },
      templateResult: formatClienteResult,
      templateSelection: formatClienteSelection
    });
  }

  aplicarSelect2($('#formulario_admision #genero'), {
    width: '100%',
    placeholder: 'Género',
    minimumResultsForSearch: 0,
    dropdownParent: $('#modal_admision_clientes')
  });

  aplicarSelect2($('#formulario_admision #tipo_muestra'), {
    width: '100%',
    placeholder: 'Tipo Muestra',
    minimumResultsForSearch: 0,
    dropdownParent: $('#modal_admision_clientes')
  });

  aplicarSelect2($('#formulario_admision #empresa'), {
    width: '100%',
    placeholder: 'Empresa',
    minimumResultsForSearch: 0,
    dropdownParent: $('#modal_admision_clientes')
  });

  aplicarSelect2($('#formulario_admision #categoria'), {
    width: '100%',
    placeholder: 'Categoría',
    minimumResultsForSearch: 0,
    dropdownParent: $('#modal_admision_clientes')
  });

  aplicarSelect2($('#formulario_admision #producto'), {
    width: '100%',
    placeholder: 'Producto',
    minimumResultsForSearch: 0,
    dropdownParent: $('#modal_admision_clientes')
  });

  $('#formulario_admision #cliente_admision').off('change').on('change', function(e){
    var pacientes_id = $(this).val();

    if (pacientes_id) {
      cargarDatosClienteAdmision(pacientes_id);
    } else {
      $('#formulario_admision #name').val("");
      $('#formulario_admision #lastname').val("");
      $('#formulario_admision #rtn').val(0);
      $('#formulario_admision #edad').val("");
      $('#formulario_admision #telefono1').val("");
      $('#formulario_admision #direccion').val("");
      $('#formulario_admision #correo').val("");
    }
  });

  $('#modal_admision_clientes').modal({
    show: true,
    keyboard: false,
    backdrop:'static'
  });
}

// ===== FORMATOS SELECT2 CLIENTE =====
function formatClienteResult(cliente) {
  if (cliente.loading) return 'Cargando...';

  var $container = $('<div>');

  $container.append(
    $('<strong>').text(cliente.nombre || (cliente.text ? cliente.text.split(' - ')[0] : ''))
  );

  if (cliente.identidad) {
    $container.append(
      $('<small>')
        .css('display', 'block')
        .css('color', '#6c757d')
        .text('RTN: ' + cliente.identidad)
    );
  }

  return $container;
}

function formatClienteSelection(cliente) {
  if (cliente.nombre) return cliente.nombre;
  if (cliente.text) return cliente.text.split(' - ')[0];

  return cliente.id;
}

function cargarDatosClienteAdmision(pacientes_id){
  if (!pacientes_id) return;

  var url = SERVERURL + 'php/admision/consultarClientes.php';

  $.ajax({
    type:'POST',
    url:url,
    data: {
      pacientes_id: pacientes_id
    },
    async: false,
    success:function(data){
      var valores = JSON.parse(data);

      $('#formulario_admision #name').val(valores[0]);
      $('#formulario_admision #lastname').val(valores[1]);
      $('#formulario_admision #rtn').val(valores[2]);
      $('#formulario_admision #edad').val(valores[3]);
      $('#formulario_admision #telefono1').val(valores[4]);
      $('#formulario_admision #genero').val(valores[5]);

      aplicarSelect2($('#formulario_admision #genero'), {
        width: '100%',
        placeholder: 'Género',
        minimumResultsForSearch: 0,
        dropdownParent: $('#modal_admision_clientes')
      });

      $('#formulario_admision #genero').val(valores[5]).trigger('change');

      $('#formulario_admision #direccion').val(valores[6]);
      $('#formulario_admision #correo').val(valores[7]);
    }
  });
}

$('#form_main_admision #registrar_empresa').on('click', function(e){
  e.preventDefault();
  modaEmpresa();
});

$('#formulario_admision #add_empresa').on('click', function(e){
  e.preventDefault();
  modaEmpresa();
});

$('#form_main_admision #ver_muestras').on('click', function(e){
  e.preventDefault();

  $('#main_facturacion').hide();
  $('#facturacion').hide();
  $('#main_admision_muestras').show();

  setTimeout(function(){
    aplicarSelect2VistaPrincipal();
    paginationMuestras(1);
  }, 100);
});

function modaEmpresa(){
  $('#formulario_admision_empresas').attr({
    'data-form': 'save'
  });

  $('#formulario_admision_empresas').attr({
    'action': SERVERURL + 'php/admision/agregarRegistroEmpresas.php'
  });

  $('#formulario_admision_empresas')[0].reset();
  $('#formulario_admision_empresas #pro_admision').val("Registro");

  $('#reg_admisionemp').show();
  $('#edi_admisionemp').hide();
  $('#delete_admisionemp').hide();

  $('#formulario_admision_empresas #paciente_tipo').val(1);

  aplicarSelect2($('#formulario_admision_empresas #paciente_tipo'), {
    width: '100%',
    placeholder: 'Tipo',
    minimumResultsForSearch: 0,
    dropdownParent: $('#modal_admision_empesas')
  });

  $('#formulario_admision_empresas #name').attr('readonly', false);
  $('#formulario_admision_empresas #lastname').attr('readonly', false);
  $('#formulario_admision_empresas #rtn').attr('readonly', false);
  $('#formulario_admision_empresas #fecha_nac').attr('disabled', false);
  $('#formulario_admision_empresas #edad').attr('readonly', false);
  $('#formulario_admision_empresas #telefono1').attr('readonly', false);
  $('#formulario_admision_empresas #genero').attr('disabled', false);
  $('#formulario_admision_empresas #direccion').attr('readonly', false);
  $('#formulario_admision_empresas #correo').attr('readonly', false);

  $('#modal_admision_empesas').modal({
    show: true,
    keyboard: false,
    backdrop:'static'
  });
}

// ===== FACTURACIÓN DESDE ADMISIÓN =====
function convertDate(inputFormat) {
  function pad(s) {
    return (s < 10) ? '0' + s : s;
  }

  var d = new Date(inputFormat);

  return [
    d.getFullYear(),
    pad(d.getMonth()+1),
    pad(d.getDate())
  ].join('-');
}

function formFactura(){
  $('#formulario_facturacion')[0].reset();

  $('#main_facturacion').hide();
  $('#facturacion').show();

  $('#label_acciones_volver').html("Volver");
  $('#acciones_atras').removeClass("active");
  $('#acciones_factura').addClass("active");
  $('#label_acciones_factura').html("Factura");

  $('#formulario_facturacion #fact_eval').val(0);
  $('#formulario_facturacion #fecha').attr('disabled', false);

  $('#formulario_facturacion').attr({
    'data-form': 'save'
  });

  $('#formulario_facturacion').attr({
    'action': SERVERURL + 'php/facturacion/addPreFactura.php'
  });

  if (typeof limpiarTabla === 'function') limpiarTabla();

  $('.footer').show();
  $('.footer1').hide();
}

function ModalVerMas(){
  $('#main_facturacion').hide();
  $('#facturacion').hide();
  $('#main_admision_muestras').show();

  $('#form_main_admision_muestras')[0].reset();

  $('#label_acciones_volver').html("Volver");
  $('#acciones_atras').removeClass("active");
  $('#acciones_factura').addClass("active");
  $('#label_acciones_factura').html("Muestras");

  $('.footer').show();
  $('.footer1').hide();

  setTimeout(function(){
    aplicarSelect2VistaPrincipal();
  }, 100);
}

$('#formulario_facturacion #validar').on('click', function(e){
  $('#formulario_facturacion').attr({
    'data-form': 'save'
  });

  $('#formulario_facturacion').attr({
    'action': SERVERURL + 'php/facturacion/addPreFactura.php'
  });

  $("#formulario_facturacion").submit();
});

$('#formulario_facturacion #cobrar').on('click', function(e){
  $('#formulario_facturacion').attr({
    'data-form': 'save'
  });

  $('#formulario_facturacion').attr({
    'action': SERVERURL + 'php/facturacion/addFactura.php'
  });

  $("#formulario_facturacion").submit();
});

$('#acciones_atras').on('click', function(e){
  e.preventDefault();
  volver();
});

$('#registrar_productos').on('click', function(e){
  e.preventDefault();

  if (typeof agregarProductos === 'function') agregarProductos();
});

function volver(){
  $('#main_facturacion').show();
  $('#label_acciones_factura').html("");
  $('#facturacion').hide();
  $('#main_admision_muestras').hide();

  $('#acciones_atras').addClass("breadcrumb-item active");
  $('#acciones_factura').removeClass("active");

  $('.footer').show();
  $('.footer1').hide();

  $('#agrega-registros_muestras').html("");
  $('#pagination_muestras').html("");

  setTimeout(function(){
    aplicarSelect2VistaPrincipal();
  }, 100);
}

function getFacturaEmision(muestras_id){
  var url = SERVERURL + 'php/muestras/getFacturaEmision.php';
  var disponible;

  $.ajax({
    type:'POST',
    url:url,
    data: {
      muestras_id: muestras_id
    },
    async:false,
    success:function(data){
      disponible = data;
    }
  });

  return disponible;
}

function getEstadoFactura(muestras_id){
  var url = SERVERURL + 'php/muestras/getEstadoFactura.php';
  var disponible;

  $.ajax({
    type:'POST',
    url:url,
    data: {
      muestras_id: muestras_id
    },
    async:false,
    success:function(data){
      disponible = data;
    }
  });

  return disponible;
}

function modalCreateBill(muestras_id, producto, nombre_producto, precio_venta, isv, muestra){
  if($('#form_main_admision_muestras select[name="estado"]').val() == 0){
    if(getEstadoFactura(muestras_id) === ""){
      createBill(muestras_id, producto, nombre_producto, precio_venta, isv, muestra);
    }else{
      showNotify("error", "Error", "Lo sentimos esta factura ya ha sido emitida, por favor diríjase al módulo de facturación y realice el cobro de esta.");
    }
  }else if($('#form_main_admision_muestras select[name="estado"]').val() == 1){
    if(getEstadoFactura(muestras_id) == ""){
      createBill(muestras_id, producto, nombre_producto, precio_venta, isv, muestra);
    }else{
      showNotify("error", "Error", "Lo sentimos esta factura ya ha sido emitida, por favor diríjase al reporte de facturación para buscarla, puede usar el número de muestra como referencia.");
    }
  }else{
    showNotify("error", "Error", "Lo sentimos no puede generar factura a una muestra anulada.");
  }
}

function createBill(muestras_id, producto, nombre_producto, precio_venta, isv, muestra){
  if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
    if(getFacturaEmision(muestras_id) == ""){
      $('#formulario_facturacion')[0].reset();
      $("#formulario_facturacion #invoiceItem > tbody").empty();

      var url = SERVERURL + 'php/muestras/editarFacturasMuestras.php';

      $.ajax({
        type:'POST',
        url:url,
        data: {
          muestras_id: muestras_id,
          producto: producto
        },
        success: function(valores){
          var datos = JSON.parse(valores);

          $('#formulario_facturacion #fact_eval').val(0);
          $('#formulario_facturacion #muestras_id').val(muestras_id);
          $('#formulario_facturacion #pacientes_id').val(datos[0]);
          $('#formulario_facturacion #cliente_nombre').val(datos[1]);
          $('#formulario_facturacion #fecha').val(getFechaActual());
          $('#formulario_facturacion #colaborador_id').val(datos[3]);
          $('#formulario_facturacion #colaborador_nombre').val(datos[4]);
          $('#formulario_facturacion #servicio_id').val(datos[5]);
          $('#formulario_facturacion #material_enviado_muestra').val(datos[6]);
          $('#formulario_facturacion #paciente_muestra_codigo').val(datos[7]);
          $('#formulario_facturacion #paciente_muestra').val(datos[8]);
          $('#formulario_facturacion #muestras_numero').val(datos[9]);

          $('#formulario_facturacion #fecha').attr("readonly", true);
          $('#formulario_facturacion #validar').attr("disabled", false);
          $('#formulario_facturacion #addRows').attr("disabled", false);
          $('#formulario_facturacion #removeRows').attr("disabled", false);

          $('#cobrar').hide();

          if(muestra === "Muestra") $('.counter-container').hide();

          $('#formulario_facturacion #validar').show();
          $('#formulario_facturacion #editar').hide();
          $('#formulario_facturacion #eliminar').hide();

          if(getPacienteTipo(datos[0]) == 2){
            $('#formulario_facturacion #grupo_paciente_factura').show();
          }else{
            $('#formulario_facturacion #grupo_paciente_factura').hide();
          }

          $('#main_facturacion').hide();
          $('#facturacion').show();

          $('#label_acciones_volver').html("Volver");
          $('#acciones_atras').removeClass("active");
          $('#acciones_factura').addClass("active");
          $('#label_acciones_factura').html("Factura");

          $('#formulario_facturacion #fecha').attr('disabled', false);

          if (typeof limpiarTabla === 'function') limpiarTabla();

          $('#formulario_facturacion #invoiceItem #productoID_0').val(producto);
          $('#formulario_facturacion #invoiceItem #productName_0').val(nombre_producto);
          $('#formulario_facturacion #invoiceItem #quantity_0').val(1);
          $('#formulario_facturacion #invoiceItem #discount_0').val(0);
          $('#formulario_facturacion #invoiceItem #price_0').val(precio_venta);
          $('#formulario_facturacion #invoiceItem #total_0').val(precio_venta);

          if(muestra === "Muestra") $('#facturas-counter').hide();

          var porcentaje_isv = 0;
          var porcentaje_calculo = 0;

          if(isv == 1){
            porcentaje_isv = parseFloat(getPorcentajeISV("Facturas") / 100);
            porcentaje_calculo = (parseFloat(precio_venta) * porcentaje_isv).toFixed(2);

            $('#formulario_facturacion #invoiceItem #isv_0').val(isv);
            $('#formulario_facturacion #invoiceItem #valor_isv_0').val(porcentaje_calculo);
            $('#formulario_facturacion #taxAmount').val(porcentaje_calculo);
          }else{
            $('#formulario_facturacion #invoiceItem #isv_0').val(isv);
            $('#formulario_facturacion #invoiceItem #valor_isv_0').val(0);
            $('#formulario_facturacion #taxAmount').val(0);
          }

          var neto = (parseFloat(precio_venta) + parseFloat(porcentaje_calculo || 0)).toFixed(2);

          $('#formulario_facturacion #subTotal').val(precio_venta);
          $('#formulario_facturacion #taxAmount').val(porcentaje_calculo);
          $('#formulario_facturacion #taxDescuento').val(0);
          $('#formulario_facturacion #totalAftertax').val(neto);

          $('#subTotalFooter').val(precio_venta);
          $('#taxAmountFooter').val(porcentaje_calculo);
          $('#taxDescuentoFooter').val(0);
          $('#totalAftertaxFooter').val(neto);

          $('#main_facturacion').hide();
          $('#main_admision_muestras').hide();
          $('#label_acciones_factura').html("Factura");
          $('#facturacion').show();

          $('.footer').hide();
          $('.footer1').show();

          return false;
        }
      });
    }else{
      showNotify("error", "Error", "Lo sentimos esta factura ya ha sido generada, por favor diríjase al módulo de facturación y realice el cobro de esta");
    }
  }else{
    showNotify("error", "Acceso Denegado", "No tiene permisos para ejecutar esta acción");
  }
}

function pago(facturas_id){
  var url = SERVERURL + 'php/facturacion/editarPago.php';

  $.ajax({
    type:'POST',
    url:url,
    data: {
      facturas_id: facturas_id
    },
    success: function(valores){
      var datos = JSON.parse(valores);

      $('#formEfectivoBill .border-right a:eq(0) a').tab('show');

      $("#customer-name-bill").html("<b>Cliente:</b> " + datos[0]);
      $("#customer_bill_pay").val(datos[2]);
      $('#bill-pay').html("L. " + parseFloat(datos[2]).toFixed(2));

      $('#formEfectivoBill')[0].reset();
      $('#formEfectivoBill #monto_efectivo').val(datos[2]);
      $('#formEfectivoBill #factura_id_efectivo').val(facturas_id);
      $('#formEfectivoBill #pago_efectivo').attr('disabled', true);

      $('#formTarjetaBill')[0].reset();
      $('#formTarjetaBill #monto_efectivo').val(datos[2]);
      $('#formTarjetaBill #factura_id_tarjeta').val(facturas_id);

      $('#formTransferenciaBill')[0].reset();
      $('#formTransferenciaBill #monto_efectivo').val(datos[2]);
      $('#formTransferenciaBill #factura_id_transferencia').val(facturas_id);

      $('#formMixtoBill')[0].reset();
      $('#formMixtoBill #monto_efectivo_mixto').val(datos[2]);
      $('#formMixtoBill #factura_id_mixto').val(facturas_id);
      $('#formMixtoBill #pago_efectivo_mixto').attr('disabled', true);

      $('#formChequeBill')[0].reset();
      $('#formChequeBill #monto_efectivo').val(datos[2]);
      $('#formChequeBill #factura_id_cheque').val(facturas_id);

      $('#modal_pagos').modal({
        show: true,
        keyboard: false,
        backdrop:'static'
      });

      return false;
    }
  });
}

function printBill(facturas_id){
  var url = SERVERURL + 'php/facturacion/generaFactura.php?facturas_id=' + facturas_id;
  window.open(url);
}

function printBillGroup(facturas_id){
  var url = SERVERURL + 'php/facturacion/generaFacturaGrupal.php?facturas_id=' + facturas_id;
  window.open(url);
}

// ===== ARRANQUE FINAL =====
$(window).on('load', function(){
  initPage();

  setTimeout(function(){
    aplicarSelect2VistaPrincipal();
  }, 300);

  setTimeout(function(){
    aplicarSelect2VistaPrincipal();
  }, 800);

  setTimeout(function(){
    aplicarSelect2VistaPrincipal();
  }, 1500);

  setTimeout(function(){
    aplicarSelect2VistaPrincipal();
  }, 2500);
});
</script>