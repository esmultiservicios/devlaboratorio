<script>
/****************************************************************************************************************************************************************/
/* ADMISION - JS COMPLETO ORDENADO
   - Select2 optimizado
   - Sin async:false
   - Sin eval
   - Muestras por defecto estado = 0
   - Búsqueda de muestras cancela petición anterior
   - Botón buscar muestras rápido
   - Texto de búsqueda muestras con debounce
   - Corregido SweetAlert normal: NO usa showNotify("warning")
   - Corregido Facturar desde muestras: abre factura aunque no venga producto automático
   - Corregido Empresa en modal Registro Clientes: queda vacía, no selecciona la primera empresa
/****************************************************************************************************************************************************************/

/****************************************************************************************************************************************************************/
// VARIABLES GLOBALES
/****************************************************************************************************************************************************************/

var catalogosCargados = false;
var requestPaginationAdmision = null;
var requestPaginationMuestrasAdmision = null;
var timerBusquedaAdmision = null;
var timerBusquedaMuestrasAdmision = null;

/****************************************************************************************************************************************************************/
// HELPERS GENERALES
/****************************************************************************************************************************************************************/

function valorAjax(response){
  if ($.isArray(response)) {
    return response[0];
  }

  return response;
}

function parseJsonSeguro(data){
  if (typeof data === 'object') {
    return data;
  }

  try {
    return JSON.parse(data);
  } catch(e) {
    return null;
  }
}

function respuestaVaciaFactura(resp){
  resp = $.trim(String(valorAjax(resp) || ''));

  return (
    resp === '' ||
    resp === '0' ||
    resp.toLowerCase() === 'null' ||
    resp.toLowerCase() === 'undefined' ||
    resp.toLowerCase() === 'false'
  );
}

function normalizarValor(valor, defecto){
  if (valor === null || typeof valor === 'undefined' || valor === 'undefined') {
    return defecto;
  }

  return valor;
}

function safeSelectpickerRefresh($el){
  if ($el && $el.length && $.fn && $.fn.selectpicker) {
    $el.selectpicker('refresh');
  }
}

function safeSelect2Refresh($el){
  if ($el && $el.length && $el.hasClass('select2-hidden-accessible')) {
    $el.trigger('change.select2');
  }
}

function mostrarErrorSimple(titulo, mensaje){
  if (typeof showNotify === 'function') {
    showNotify("error", titulo, mensaje);
    return;
  }

  if (typeof swal === 'function') {
    swal({
      title: titulo,
      text: mensaje,
      icon: "error",
      dangerMode: true,
      closeOnEsc: false,
      closeOnClickOutside: false
    });
    return;
  }

  alert(titulo + "\n" + mensaje);
}

function mostrarInfoSimple(titulo, mensaje){
  if (typeof swal === 'function') {
    swal({
      title: titulo,
      text: mensaje,
      icon: "info",
      closeOnEsc: false,
      closeOnClickOutside: false
    });
    return;
  }

  alert(titulo + "\n" + mensaje);
}

/****************************************************************************************************************************************************************/
// HELPER SELECT2 GLOBAL
/****************************************************************************************************************************************************************/

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

function prepararSelectVacio($select, texto){
  if (!$select || !$select.length) return;

  texto = texto || 'Sin selección';

  if ($select.find('option[value=""]').length === 0) {
    $select.prepend('<option value="">' + texto + '</option>');
  }

  $select.val('');

  if ($select.hasClass('select2-hidden-accessible')) {
    $select.trigger('change.select2');
  } else {
    $select.trigger('change');
  }
}

function limpiarEmpresaAdmision(){
  var $empresa = $('#formulario_admision select[name="empresa"], #formulario_admision #empresa');

  if (!$empresa.length) return;

  prepararSelectVacio($empresa, 'Sin selección');

  aplicarSelect2($empresa, {
    width: '100%',
    placeholder: 'Sin selección',
    allowClear: true,
    minimumResultsForSearch: 0,
    dropdownParent: $('#modal_admision_clientes')
  });

  $empresa.val(null).trigger('change');
}

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

function setEstadoMuestrasPendientePorDefecto(){
  var $estado = $('#form_main_admision_muestras select[name="estado"]');

  if (!$estado.length) return;

  var valorActual = $estado.val();

  if (valorActual === null || valorActual === '' || typeof valorActual === 'undefined') {
    if ($estado.find('option[value="0"]').length > 0) {
      $estado.val('0');
    } else if ($estado.find('option[value=0]').length > 0) {
      $estado.val(0);
    }
  }

  if ($estado.hasClass('select2-hidden-accessible')) {
    $estado.trigger('change.select2');
  }
}

/****************************************************************************************************************************************************************/
// SI ALGÚN SELECT PRINCIPAL QUEDA NATIVO, LO MONTA AL CLIC
/****************************************************************************************************************************************************************/

$(document).off('mousedown.admisionSelect2Principal', '#form_main_admision select, #form_main_admision_muestras select');
$(document).on('mousedown.admisionSelect2Principal', '#form_main_admision select, #form_main_admision_muestras select', function(e){
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

$(document).off('select2:open.admisionFocusSelect2');
$(document).on('select2:open.admisionFocusSelect2', function(){
  setTimeout(function(){
    var search = document.querySelector('.select2-container--open .select2-search__field');
    if (search) search.focus();
  }, 50);
});

/****************************************************************************************************************************************************************/
// FUNCIONES DE CARGA DE CATÁLOGOS
/****************************************************************************************************************************************************************/

function getEstadoMuestra(){
  var url = SERVERURL + 'php/admision/getStatusMuestra.php';

  return $.ajax({
    type: 'POST',
    url: url
  }).done(function(data){
    var $s = $('#form_main_admision_muestras select[name="estado"]');

    $s.html(data);

    if ($s.find('option[value="0"]').length > 0) {
      $s.val('0');
    }

    aplicarSelect2($s, {
      width: '130px',
      placeholder: 'Estado',
      minimumResultsForSearch: 0
    });

    setEstadoMuestrasPendientePorDefecto();
  });
}

function getEstadoPaciente(){
  var url = SERVERURL + 'php/admision/getStatusPaciente.php';

  return $.ajax({
    type: 'POST',
    url: url
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
    type: 'POST',
    url: url
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
    type: 'POST',
    url: url
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
    type: 'POST',
    url: url
  }).done(function(data){
    var $s = $('#formulario_admision select[name="empresa"], #formulario_admision #empresa');

    $s.html(data);

    if ($s.find('option[value=""]').length === 0) {
      $s.prepend('<option value="">Sin selección</option>');
    }

    $s.val('');

    aplicarSelect2($s, {
      width: '100%',
      placeholder: 'Sin selección',
      allowClear: true,
      minimumResultsForSearch: 0,
      dropdownParent: $('#modal_admision_clientes')
    });

    $s.val(null).trigger('change');
  });
}

function getTipo(){
  var url = SERVERURL + 'php/admision/getTipoPaciente.php';

  return $.ajax({
    type: 'POST',
    url: url
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
    type: 'POST',
    url: url
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
    type: 'POST',
    url: url
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
    type: 'POST',
    url: url
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
    type: 'POST',
    url: url
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

  return $.ajax({
    type: 'POST',
    url: url,
    data: {
      tipo_muestra_id: tipo_muestra_id
    }
  }).done(function(data){
    $p.html(data);

    aplicarSelect2($p, {
      width: '100%',
      placeholder: 'Producto',
      minimumResultsForSearch: 0,
      dropdownParent: $('#modal_admision_clientes')
    });
  });
}

/****************************************************************************************************************************************************************/
// CARGAR TODOS LOS CATÁLOGOS UNA SOLA VEZ
/****************************************************************************************************************************************************************/

function cargarCatalogosIniciales(callback){
  if (catalogosCargados) {
    if (typeof callback === 'function') callback();
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

    if (typeof callback === 'function') callback();
  });
}

/****************************************************************************************************************************************************************/
// ORQUESTADOR
/****************************************************************************************************************************************************************/

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
    setEstadoMuestrasPendientePorDefecto();
  });

  cargarCatalogosIniciales(function(){
    aplicarSelect2VistaPrincipal();
    setEstadoMuestrasPendientePorDefecto();
  });
}

/****************************************************************************************************************************************************************/
// EVENTOS DE FOCO EN MODALES
/****************************************************************************************************************************************************************/

$(document).ready(function(){
  $("#modal_historico_muestras").off('shown.bs.modal.admision').on('shown.bs.modal.admision', function(){
    $(this).find('#form_main_historico_muestras #bs_regis').focus();
  });

  $("#modal_admision_clientes").off('shown.bs.modal.admision').on('shown.bs.modal.admision', function(){
    limpiarEmpresaAdmision();
    $(this).find('#formulario_admision #name').focus();
  });

  $("#modal_admision_clientes_editar").off('shown.bs.modal.admision').on('shown.bs.modal.admision', function(){
    $(this).find('#formulario_admision_clientes_editar #name').focus();
  });

  $("#modal_admision_empesas").off('shown.bs.modal.admision').on('shown.bs.modal.admision', function(){
    $(this).find('#formulario_admision_empresas #empresa').focus();
  });
});

/****************************************************************************************************************************************************************/
// LISTENERS VISTA CLIENTES
/****************************************************************************************************************************************************************/

$(document).off('keyup.admisionBuscarClientes', '#form_main_admision #bs_regis');
$(document).on('keyup.admisionBuscarClientes', '#form_main_admision #bs_regis', function(e){
  clearTimeout(timerBusquedaAdmision);

  if (e.key === 'Enter') {
    pagination(1);
    return;
  }

  timerBusquedaAdmision = setTimeout(function(){
    pagination(1);
  }, 350);
});

$(document).off('change.admisionEstadoCliente', '#form_main_admision select[name="estado"]');
$(document).on('change.admisionEstadoCliente', '#form_main_admision select[name="estado"]', function(){
  pagination(1);
});

$(document).off('change.admisionTipoCliente', '#form_main_admision select[name="tipo"]');
$(document).on('change.admisionTipoCliente', '#form_main_admision select[name="tipo"]', function(){
  pagination(1);
});

/****************************************************************************************************************************************************************/
// LISTENERS VISTA MUESTRAS
/****************************************************************************************************************************************************************/

$(document).off('change.admisionMuestrasEstado', '#form_main_admision_muestras select[name="estado"]');
$(document).on('change.admisionMuestrasEstado', '#form_main_admision_muestras select[name="estado"]', function(){
  paginationMuestras(1);
});

$(document).off('change.admisionMuestrasCliente', '#form_main_admision_muestras select[name="cliente"]');
$(document).on('change.admisionMuestrasCliente', '#form_main_admision_muestras select[name="cliente"]', function(){
  paginationMuestras(1);
});

$(document).off('change.admisionMuestrasTipoMuestra', '#form_main_admision_muestras select[name="tipo_muestra"]');
$(document).on('change.admisionMuestrasTipoMuestra', '#form_main_admision_muestras select[name="tipo_muestra"]', function(){
  paginationMuestras(1);
});

$(document).off('change.admisionMuestrasFechaI', '#form_main_admision_muestras #fecha_i');
$(document).on('change.admisionMuestrasFechaI', '#form_main_admision_muestras #fecha_i', function(){
  paginationMuestras(1);
});

$(document).off('change.admisionMuestrasFechaF', '#form_main_admision_muestras #fecha_f');
$(document).on('change.admisionMuestrasFechaF', '#form_main_admision_muestras #fecha_f', function(){
  paginationMuestras(1);
});

$(document).off('click.admisionMuestrasBuscar', '#form_main_admision_muestras #buscar_registro');
$(document).on('click.admisionMuestrasBuscar', '#form_main_admision_muestras #buscar_registro', function(e){
  e.preventDefault();
  paginationMuestras(1);
});

$(document).off('keyup.admisionMuestrasBuscarTexto', '#form_main_admision_muestras #bs_regis');
$(document).on('keyup.admisionMuestrasBuscarTexto', '#form_main_admision_muestras #bs_regis', function(e){
  clearTimeout(timerBusquedaMuestrasAdmision);

  if (e.key === 'Enter') {
    paginationMuestras(1);
    return;
  }

  timerBusquedaMuestrasAdmision = setTimeout(function(){
    paginationMuestras(1);
  }, 450);
});

/****************************************************************************************************************************************************************/
// DEPENDENCIA DE SELECTS MUESTRAS
/****************************************************************************************************************************************************************/

$(document).off('change.admisionMuestrasTipoCliente', '#form_main_admision_muestras select[name="tipo"]');
$(document).on('change.admisionMuestrasTipoCliente', '#form_main_admision_muestras select[name="tipo"]', function(){
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
    type: 'POST',
    url: url,
    data: {
      tipo: tipo
    }
  }).done(function(data){
    $c.html(data);

    aplicarSelect2($c, {
      width: '250px',
      placeholder: 'Cliente',
      minimumResultsForSearch: 0
    });

    paginationMuestras(1);
  });
});

/****************************************************************************************************************************************************************/
// PAGINACIÓN CLIENTES
/****************************************************************************************************************************************************************/

function pagination(partida, firstLoad){
  var url = SERVERURL + 'php/admision/paginar.php';
  var tipo = $('#form_main_admision select[name="tipo"]').val() || 1;
  var dato = $.trim($('#form_main_admision #bs_regis').val() || '');
  var estado = $('#form_main_admision select[name="estado"]').val() || 1;

  if (requestPaginationAdmision !== null) {
    requestPaginationAdmision.abort();
    requestPaginationAdmision = null;
  }

  requestPaginationAdmision = $.ajax({
    type: 'POST',
    url: url,
    data: {
      partida: partida,
      tipo: tipo,
      dato: dato,
      estado: estado
    },
    dataType: 'json',
    success: function(array){
      if (!Array.isArray(array)) {
        mostrarErrorSimple("Error", "La respuesta del servidor no es válida.");
        return;
      }

      $('#agrega-registros').html(array[0] || '');
      $('#pagination').html(array[1] || '');

      setTimeout(function(){
        aplicarSelect2VistaPrincipal();
      }, 50);
    },
    error: function(xhr, status){
      if (status === 'abort') return;

      if (firstLoad) {
        setTimeout(function(){
          pagination(partida, false);
        }, 150);
      }
    },
    complete: function(){
      requestPaginationAdmision = null;
    }
  });

  return false;
}

/****************************************************************************************************************************************************************/
// PAGINACIÓN MUESTRAS
/****************************************************************************************************************************************************************/

function paginationMuestras(partida){
  var url = SERVERURL + 'php/admision/paginarMuestras.php';

  var estado = $('#form_main_admision_muestras select[name="estado"]').val();

  if (estado === null || estado === '' || typeof estado === 'undefined') {
    estado = '0';
  }

  var cliente = $('#form_main_admision_muestras select[name="cliente"]').val() || '';
  var tipo_muestra = $('#form_main_admision_muestras select[name="tipo_muestra"]').val() || '';
  var fecha_i = $('#form_main_admision_muestras #fecha_i').val() || '';
  var fecha_f = $('#form_main_admision_muestras #fecha_f').val() || '';
  var dato = $.trim($('#form_main_admision_muestras #bs_regis').val() || '');

  if (requestPaginationMuestrasAdmision !== null) {
    requestPaginationMuestrasAdmision.abort();
    requestPaginationMuestrasAdmision = null;
  }

  requestPaginationMuestrasAdmision = $.ajax({
    type: 'POST',
    url: url,
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
      if (typeof showLoading === 'function') {
        showLoading("Por favor espere...");
      }
    },
    success: function(array){
      if (!Array.isArray(array)) {
        mostrarErrorSimple("Error", "La respuesta del servidor no es válida.");
        return;
      }

      $('#agrega-registros_muestras').html(array[0] || '');
      $('#pagination_muestras').html(array[1] || '');
    },
    error: function(xhr, status){
      if (status === 'abort') {
        return;
      }

      mostrarErrorSimple("Error", "No se enviaron los datos, favor corregir");
    },
    complete: function(){
      requestPaginationMuestrasAdmision = null;

      if (typeof hideLoading === 'function') {
        hideLoading();
      }
    }
  });

  return false;
}

/****************************************************************************************************************************************************************/
// UTILIDADES
/****************************************************************************************************************************************************************/

function CalcularEdadClientes(){
  var url = SERVERURL + 'php/admision/calcularEdad.php';

  $.ajax({
    type: 'POST',
    data: {
      fecha_nac: $('#formulario_admision #fecha_nac').val()
    },
    url: url
  }).done(function(data){
    $('#formulario_admision #edad').val(data);
  });

  return false;
}

function getFechaActual(){
  var url = SERVERURL + 'php/admision/getFechaActual.php';

  return $.ajax({
    type: 'POST',
    url: url
  });
}

function getPacienteTipo(pacientes_id){
  var url = SERVERURL + 'php/admision/getPacienteTipo.php';

  return $.ajax({
    type: 'POST',
    url: url,
    data: {
      pacientes_id: pacientes_id
    }
  });
}

function consultarExpediente(pacientes_id){
  var url = SERVERURL + 'php/pacientes/getExpedienteInformacion.php';

  return $.ajax({
    type: 'POST',
    url: url,
    data: {
      pacientes_id: pacientes_id
    }
  });
}

function consultarNumeroMuestra(muestras_id){
  var url = SERVERURL + 'php/admision/getNumeroMuestra.php';

  return $.ajax({
    type: 'POST',
    url: url,
    data: {
      muestras_id: muestras_id
    }
  });
}

function consultarNombre(pacientes_id){
  var url = SERVERURL + 'php/pacientes/getNombre.php';

  return $.ajax({
    type: 'POST',
    url: url,
    data: {
      pacientes_id: pacientes_id
    }
  });
}

function getHospitalCodigo(){
  var url = SERVERURL + 'php/pacientes/getHospitalCodigo.php';

  return $.ajax({
    type: 'POST',
    url: url
  });
}

function getRemitenteCodigo(){
  var url = SERVERURL + 'php/pacientes/getRemitenteCodigo.php';

  return $.ajax({
    type: 'POST',
    url: url
  });
}

function getFacturaEmision(muestras_id){
  var url = SERVERURL + 'php/muestras/getFacturaEmision.php';

  return $.ajax({
    type: 'POST',
    url: url,
    data: {
      muestras_id: muestras_id
    }
  });
}

function getEstadoFactura(muestras_id){
  var url = SERVERURL + 'php/muestras/getEstadoFactura.php';

  return $.ajax({
    type: 'POST',
    url: url,
    data: {
      muestras_id: muestras_id
    }
  });
}

/****************************************************************************************************************************************************************/
// EVENTOS DE FORMULARIO ADMISIÓN
/****************************************************************************************************************************************************************/

$(document).off('change.admisionFechaNacimiento', '#formulario_admision #fecha_nac');
$(document).on('change.admisionFechaNacimiento', '#formulario_admision #fecha_nac', function(){
  CalcularEdadClientes();
});

$(document).off('change.admisionTipoMuestraProducto', '#formulario_admision #tipo_muestra');
$(document).on('change.admisionTipoMuestraProducto', '#formulario_admision #tipo_muestra', function(){
  getProductos();
});

/****************************************************************************************************************************************************************/
// BOTONES PRINCIPALES
/****************************************************************************************************************************************************************/

$('#form_main_admision #registrar_cliente').off('click').on('click', function(e){
  e.preventDefault();
  modalClientes();
});

$('#form_main_admision #registrar_empresa').off('click').on('click', function(e){
  e.preventDefault();
  modaEmpresa();
});

$('#formulario_admision #add_empresa').off('click').on('click', function(e){
  e.preventDefault();
  modaEmpresa();
});

$('#form_main_admision #ver_muestras').off('click').on('click', function(e){
  e.preventDefault();

  $('#main_facturacion').hide();
  $('#facturacion').hide();
  $('#main_admision_muestras').show();

  setTimeout(function(){
    aplicarSelect2VistaPrincipal();
    setEstadoMuestrasPendientePorDefecto();
    paginationMuestras(1);
  }, 100);
});

$('#registrar_productos').off('click').on('click', function(e){
  e.preventDefault();

  if (typeof agregarProductos === 'function') {
    agregarProductos();
  }
});

/****************************************************************************************************************************************************************/
// BOTONES LIMPIAR FORMULARIOS
/****************************************************************************************************************************************************************/

$('#formulario_admision #nuevo_admision').off('click').on('click', function(e){
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

  limpiarEmpresaAdmision();

  $('#formulario_admision #name').focus();
});

$('#formulario_admision_empresas #nuevo_admision_empresa').off('click').on('click', function(e){
  e.preventDefault();

  $('#formulario_admision_empresas #empresa').val("");
  $('#formulario_admision_empresas #rtn').val(0);
  $('#formulario_admision_empresas #telefono1').val("");
  $('#formulario_admision_empresas #direccion').val("");
  $('#formulario_admision_empresas #correo').val("");
  $('#formulario_admision_empresas #empresa').focus();
});

$('#formulario_admision #nuevo_admision_muestra').off('click').on('click', function(e){
  e.preventDefault();

  $('#formulario_admision #sitio_muestra').val("");
  $('#formulario_admision #diagnostico_clinico').val("");
  $('#formulario_admision #material_enviado').val("");
  $('#formulario_admision #datos_clinicos').val("");
  $('#formulario_admision #producto').html("");

  limpiarEmpresaAdmision();

  aplicarSelect2($('#formulario_admision #producto'), {
    width: '100%',
    placeholder: 'Producto',
    minimumResultsForSearch: 0,
    dropdownParent: $('#modal_admision_clientes')
  });
});

/****************************************************************************************************************************************************************/
// ANULAR / ELIMINAR
/****************************************************************************************************************************************************************/

function anularRegistroMuestra(muestras_id, pacientes_id, comentario){
  var url = SERVERURL + 'php/admision/anularMuestras.php';

  $.ajax({
    type: 'POST',
    url: url,
    data: {
      muestras_id: muestras_id,
      pacientes_id: pacientes_id,
      comentario: comentario
    }
  }).done(function(registro){
    if (registro == 1) {
      showNotify("success", "Success", "Registro anulado correctamente");
      paginationMuestras(1);
    } else if (registro == 2) {
      mostrarErrorSimple("Error", "Lo sentimos ya existe una factura para esta muestra, por favor anule la factura antes de proceder.");
    } else if (registro == 3) {
      mostrarErrorSimple("Error", "No se puede anular este registro");
    } else {
      mostrarErrorSimple("Error", "Error al completar el registro");
    }
  });

  return false;
}

function eliminarRegistroMuestra(muestras_id, pacientes_id, comentario){
  var url = SERVERURL + 'php/admision/eliminarMuestras.php';

  $.ajax({
    type: 'POST',
    url: url,
    data: {
      muestras_id: muestras_id,
      pacientes_id: pacientes_id,
      comentario: comentario
    }
  }).done(function(registro){
    if (registro == 1) {
      showNotify("success", "Success", "Registro eliminado correctamente");
      paginationMuestras(1);
    } else if (registro == 2) {
      mostrarErrorSimple("Error", "No se puede eliminar este registro");
    } else if (registro == 3) {
      mostrarErrorSimple("Error", "No se puede eliminar este registro, cuenta con información almacenada");
    } else {
      mostrarErrorSimple("Error", "Error al completar el registro");
    }
  });

  return false;
}

function eliminarRegistro(pacientes_id, comentario){
  var url = SERVERURL + 'php/admision/eliminar.php';

  $.ajax({
    type: 'POST',
    url: url,
    data: {
      id: pacientes_id,
      comentario: comentario
    }
  }).done(function(registro){
    if (registro == 1) {
      showNotify("success", "Success", "Registro eliminado correctamente");
      pagination(1);
    } else if (registro == 2) {
      mostrarErrorSimple("Error", "No se puede eliminar este registro");
    } else if (registro == 3) {
      mostrarErrorSimple("Error", "No se puede eliminar este registro, cuenta con información almacenada");
    } else {
      mostrarErrorSimple("Error", "Error al completar el registro");
    }
  });

  return false;
}

/****************************************************************************************************************************************************************/
// HABILITAR / INHABILITAR
/****************************************************************************************************************************************************************/

function DisableRegister(pacientes_id){
  if (getUsuarioSistema() != 1 && getUsuarioSistema() != 2 && getUsuarioSistema() != 3) {
    mostrarErrorSimple("Acceso Denegado", "No tiene permisos para ejecutar esta acción");
    return false;
  }

  $.when(
    consultarNombre(pacientes_id),
    consultarExpediente(pacientes_id)
  ).done(function(nombreResp, expedienteResp){
    var nombre_usuario = valorAjax(nombreResp);
    var expediente_usuario = valorAjax(expedienteResp);

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
    }).then(function(value){
      if (value === null || $.trim(value) === "") return false;

      deshabilitarPaciente(pacientes_id, value, estado);
    });
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
    dataType: "json"
  }).done(function(response){
    if (response.status === "success") {
      showNotify("success", "Success", response.message);
      pagination(1);
    } else {
      mostrarErrorSimple("Error", response.message);
    }
  }).fail(function(){
    mostrarErrorSimple("Error", "Error en la comunicación con el servidor");
  });
}

/****************************************************************************************************************************************************************/
// MODALES ELIMINAR / ANULAR
/****************************************************************************************************************************************************************/

function modal_eliminar(pacientes_id){
  if (getUsuarioSistema() != 1 && getUsuarioSistema() != 2 && getUsuarioSistema() != 3) {
    mostrarErrorSimple("Acceso Denegado", "No tiene permisos para ejecutar esta acción");
    return false;
  }

  $.when(
    consultarNombre(pacientes_id),
    consultarExpediente(pacientes_id)
  ).done(function(nombreResp, expedienteResp){
    var nombre_usuario = valorAjax(nombreResp);
    var expediente_usuario = valorAjax(expedienteResp);
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
    }).then(function(value){
      if (value === null || $.trim(value) === "") {
        mostrarErrorSimple("Error", "¡Necesita escribir algo!");
        return false;
      }

      eliminarRegistro(pacientes_id, value);
    });
  });
}

function modal_eliminarMuestras(pacientes_id, muestras_id){
  if (getUsuarioSistema() != 1 && getUsuarioSistema() != 2 && getUsuarioSistema() != 3) {
    mostrarErrorSimple("Acceso Denegado", "No tiene permisos para ejecutar esta acción");
    return false;
  }

  $.when(
    consultarNombre(pacientes_id),
    consultarNumeroMuestra(muestras_id)
  ).done(function(nombreResp, numeroResp){
    var nombre_usuario = valorAjax(nombreResp);
    var numero_muestra = valorAjax(numeroResp);
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
    }).then(function(value){
      if (value === null || $.trim(value) === "") {
        mostrarErrorSimple("Error", "¡Necesita escribir algo!");
        return false;
      }

      eliminarRegistroMuestra(muestras_id, pacientes_id, value);
    });
  });
}

function modalAnularMuestras(pacientes_id, muestras_id){
  if (getUsuarioSistema() != 1 && getUsuarioSistema() != 2 && getUsuarioSistema() != 3) {
    mostrarErrorSimple("Acceso Denegado", "No tiene permisos para ejecutar esta acción");
    return false;
  }

  $.when(
    consultarNombre(pacientes_id),
    consultarNumeroMuestra(muestras_id)
  ).done(function(nombreResp, numeroResp){
    var nombre_usuario = valorAjax(nombreResp);
    var numero_muestra = valorAjax(numeroResp);
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
    }).then(function(value){
      if (value === null || $.trim(value) === "") {
        mostrarErrorSimple("Error", "¡Necesita escribir algo!");
        return false;
      }

      anularRegistroMuestra(muestras_id, pacientes_id, value);
    });
  });
}

/****************************************************************************************************************************************************************/
// EDITAR REGISTROS
/****************************************************************************************************************************************************************/

function editarRegistro(pacientes_id){
  if (getUsuarioSistema() != 1 && getUsuarioSistema() != 2 && getUsuarioSistema() != 3) {
    mostrarErrorSimple("Acceso Denegado", "No tiene permisos para ejecutar esta acción");
    return false;
  }

  var url = SERVERURL + 'php/admision/consultarClientes.php';

  $.ajax({
    type: 'POST',
    url: url,
    data: {
      pacientes_id: pacientes_id
    }
  }).done(function(valores){
    var datos = parseJsonSeguro(valores);

    if (!datos) {
      mostrarErrorSimple("Error", "No se pudo leer la información del cliente.");
      return false;
    }

    if ($('#form_main_admision select[name="tipo"]').val() == 1 || $('#form_main_admision select[name="tipo"]').val() == "") {
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
        'data-form': 'update',
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
    } else {
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
        'data-form': 'update',
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
  });
}

function modalEditar(pacientes_id){
  $('#formulario_admision_clientes_editar').attr({
    'data-form': 'update',
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

  getHospitalCodigo().done(function(data){
    $('#formulario_admision #hospital').val(data);

    aplicarSelect2($('#formulario_admision #hospital'), {
      width: '100%',
      placeholder: 'Hospital',
      minimumResultsForSearch: 0,
      dropdownParent: $('#modal_admision_clientes')
    });
  });
}

/****************************************************************************************************************************************************************/
// HISTORIAL DE MUESTRAS
/****************************************************************************************************************************************************************/

function showModalhistoriaMuestrasEmpresas(pacientes_id){
  if (getUsuarioSistema() != 1 && getUsuarioSistema() != 2 && getUsuarioSistema() != 3) {
    mostrarErrorSimple("Acceso Denegado", "No tiene permisos para ejecutar esta acción");
    return false;
  }

  $('#form_main_historico_muestras #pacientes_id_muestras').val(pacientes_id);

  $('#modal_historico_muestras').modal({
    show: true,
    keyboard: false,
    backdrop:'static'
  });

  getPacienteTipo(pacientes_id).done(function(data){
    var tipo = $.trim(valorAjax(data));

    if (tipo == 1) {
      historiaMuestrasPacientes(1);
    } else {
      historiaMuestrasEmpresas(1);
    }
  });
}

$('#form_main_historico_muestras #bs_regis').off('keyup').on('keyup', function(){
  var pacientes_id = $('#form_main_historico_muestras #pacientes_id_muestras').val();

  getPacienteTipo(pacientes_id).done(function(data){
    var tipo = $.trim(valorAjax(data));

    if (tipo == 1) {
      historiaMuestrasPacientes(1);
    } else {
      historiaMuestrasEmpresas(1);
    }
  });
});

function historiaMuestrasEmpresas(partida){
  var url = SERVERURL + 'php/admision/paginar_historico_muestras_empresas.php';
  var pacientes_id = $('#modal_historico_muestras #pacientes_id_muestras').val();
  var dato = $('#form_main_historico_muestras #bs_regis').val();

  $.ajax({
    type: 'POST',
    url: url,
    data: {
      partida: partida,
      pacientes_id: pacientes_id,
      dato: dato
    },
    dataType: 'json'
  }).done(function(array){
    $('#detalles-historico-muestras').html(array[0]);
    $('#pagination-historico-muestras').html(array[1]);
  });

  return false;
}

function historiaMuestrasPacientes(partida){
  var url = SERVERURL + 'php/admision/paginar_historico_muestras_pacientes.php';
  var pacientes_id = $('#form_main_historico_muestras #pacientes_id_muestras').val();
  var dato = $('#form_main_historico_muestras #bs_regis').val();

  $.ajax({
    type: 'POST',
    url: url,
    data: {
      partida: partida,
      pacientes_id: pacientes_id,
      dato: dato
    },
    dataType: 'json'
  }).done(function(array){
    $('#detalles-historico-muestras').html(array[0]);
    $('#pagination-historico-muestras').html(array[1]);
  });

  return false;
}

/****************************************************************************************************************************************************************/
// MODAL CLIENTES
/****************************************************************************************************************************************************************/

function modalClientes(){
  $('#formulario_admision').attr({
    'data-form': 'save',
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

  getHospitalCodigo().done(function(data){
    $('#formulario_admision #hospital').val(data);

    aplicarSelect2($('#formulario_admision #hospital'), {
      width: '100%',
      placeholder: 'Hospital',
      minimumResultsForSearch: 0,
      dropdownParent: $('#modal_admision_clientes')
    });
  });

  getRemitenteCodigo().done(function(data){
    $('#formulario_admision #remitente').val(data);

    aplicarSelect2($('#formulario_admision #remitente'), {
      width: '100%',
      placeholder: 'Remitente',
      minimumResultsForSearch: 0,
      dropdownParent: $('#modal_admision_clientes')
    });
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

  limpiarEmpresaAdmision();

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

  $('#formulario_admision #cliente_admision').off('change.admisionClienteSelect').on('change.admisionClienteSelect', function(){
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
      limpiarEmpresaAdmision();
    }
  });

  $('#modal_admision_clientes').modal({
    show: true,
    keyboard: false,
    backdrop:'static'
  });

  setTimeout(function(){
    limpiarEmpresaAdmision();
  }, 200);
}

/****************************************************************************************************************************************************************/
// FORMATOS SELECT2 CLIENTE
/****************************************************************************************************************************************************************/

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
    type: 'POST',
    url: url,
    data: {
      pacientes_id: pacientes_id
    }
  }).done(function(data){
    var valores = parseJsonSeguro(data);

    if (!valores) {
      mostrarErrorSimple("Error", "No se pudieron cargar los datos del cliente.");
      return;
    }

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

    limpiarEmpresaAdmision();
  });
}

/****************************************************************************************************************************************************************/
// MODAL EMPRESA
/****************************************************************************************************************************************************************/

function modaEmpresa(){
  $('#formulario_admision_empresas').attr({
    'data-form': 'save',
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

/****************************************************************************************************************************************************************/
// FACTURACIÓN DESDE ADMISIÓN
/****************************************************************************************************************************************************************/

function convertDate(inputFormat) {
  function pad(s) {
    return (s < 10) ? '0' + s : s;
  }

  var d = new Date(inputFormat);

  return [
    d.getFullYear(),
    pad(d.getMonth() + 1),
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
    'data-form': 'save',
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
    setEstadoMuestrasPendientePorDefecto();
    paginationMuestras(1);
  }, 100);
}

$('#formulario_facturacion #validar').off('click').on('click', function(e){
  e.preventDefault();

  $('#formulario_facturacion').attr({
    'data-form': 'save',
    'action': SERVERURL + 'php/facturacion/addPreFactura.php'
  });

  $("#formulario_facturacion").submit();
});

$('#formulario_facturacion #cobrar').off('click').on('click', function(e){
  e.preventDefault();

  $('#formulario_facturacion').attr({
    'data-form': 'save',
    'action': SERVERURL + 'php/facturacion/addFactura.php'
  });

  $("#formulario_facturacion").submit();
});

$('#acciones_atras').off('click').on('click', function(e){
  e.preventDefault();
  volver();
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

/****************************************************************************************************************************************************************/
// CREAR FACTURA DESDE MUESTRA - CORREGIDO DEFINITIVO
/****************************************************************************************************************************************************************/

function abrirPantallaFacturaDesdeMuestra(){
  $('#main_facturacion').hide();
  $('#main_admision_muestras').hide();
  $('#facturacion').show();

  $('#label_acciones_volver').html("Volver");
  $('#acciones_atras').removeClass("active");
  $('#acciones_factura').addClass("active");
  $('#label_acciones_factura').html("Factura");

  $('.footer').hide();
  $('.footer1').show();
}

function cargarTotalesFacturaDesdeMuestra(precio_venta, isv){
  precio_venta = parseFloat(precio_venta || 0);
  isv = parseInt(isv || 0);

  var porcentaje_isv = 0;
  var porcentaje_calculo = 0;

  if (isv == 1 && precio_venta > 0) {
    porcentaje_isv = parseFloat(getPorcentajeISV("Facturas") / 100);
    porcentaje_calculo = (precio_venta * porcentaje_isv).toFixed(2);

    $('#formulario_facturacion #invoiceItem #isv_0').val(isv);
    $('#formulario_facturacion #invoiceItem #valor_isv_0').val(porcentaje_calculo);
    $('#formulario_facturacion #taxAmount').val(porcentaje_calculo);
  } else {
    $('#formulario_facturacion #invoiceItem #isv_0').val(isv);
    $('#formulario_facturacion #invoiceItem #valor_isv_0').val(0);
    $('#formulario_facturacion #taxAmount').val(0);
  }

  var neto = (parseFloat(precio_venta) + parseFloat(porcentaje_calculo || 0)).toFixed(2);

  $('#formulario_facturacion #subTotal').val(precio_venta.toFixed(2));
  $('#formulario_facturacion #taxAmount').val(porcentaje_calculo);
  $('#formulario_facturacion #taxDescuento').val(0);
  $('#formulario_facturacion #totalAftertax').val(neto);

  $('#subTotalFooter').val(precio_venta.toFixed(2));
  $('#taxAmountFooter').val(porcentaje_calculo);
  $('#taxDescuentoFooter').val(0);
  $('#totalAftertaxFooter').val(neto);
}

function modalCreateBill(muestras_id, producto, nombre_producto, precio_venta, isv, muestra){
  muestras_id = normalizarValor(muestras_id, '');
  producto = normalizarValor(producto, '');
  nombre_producto = normalizarValor(nombre_producto, '');
  precio_venta = normalizarValor(precio_venta, 0);
  isv = normalizarValor(isv, 0);
  muestra = normalizarValor(muestra, 'Muestra');

  if (muestras_id === '') {
    mostrarErrorSimple("Error", "No se recibió el código de la muestra.");
    return false;
  }

  var estadoVista = $('#form_main_admision_muestras select[name="estado"]').val();

  if (estadoVista === null || estadoVista === '' || typeof estadoVista === 'undefined') {
    estadoVista = $('#form_main_admision_muestras #estado').val();
  }

  if (estadoVista === null || estadoVista === '' || typeof estadoVista === 'undefined') {
    estadoVista = '0';
  }

  if (estadoVista == 2) {
    mostrarErrorSimple("Error", "Lo sentimos no puede generar factura a una muestra anulada.");
    return false;
  }

  createBill(muestras_id, producto, nombre_producto, precio_venta, isv, muestra);

  return false;
}

function createBill(muestras_id, producto, nombre_producto, precio_venta, isv, muestra){
  muestras_id = normalizarValor(muestras_id, '');
  producto = normalizarValor(producto, '');
  nombre_producto = normalizarValor(nombre_producto, '');
  precio_venta = normalizarValor(precio_venta, 0);
  isv = normalizarValor(isv, 0);
  muestra = normalizarValor(muestra, 'Muestra');

  if (getUsuarioSistema() != 1 && getUsuarioSistema() != 2 && getUsuarioSistema() != 3) {
    mostrarErrorSimple("Acceso Denegado", "No tiene permisos para ejecutar esta acción");
    return false;
  }

  if (muestras_id === '') {
    mostrarErrorSimple("Error", "No se recibió el código de la muestra.");
    return false;
  }

  $('#formulario_facturacion')[0].reset();
  $("#formulario_facturacion #invoiceItem > tbody").empty();

  if (typeof limpiarTabla === 'function') {
    limpiarTabla();
  }

  var url = SERVERURL + 'php/muestras/editarFacturasMuestras.php';

  $.ajax({
    type: 'POST',
    url: url,
    data: {
      muestras_id: muestras_id,
      producto: producto
    },
    beforeSend: function(){
      if (typeof showLoading === 'function') {
        showLoading("Cargando muestra...");
      }
    }
  }).done(function(valores){
    var datos = parseJsonSeguro(valores);

    if (!datos || !$.isArray(datos)) {
      console.error("Respuesta inválida de editarFacturasMuestras.php:", valores);
      mostrarErrorSimple("Error", "No se pudo leer la información de la muestra.");
      return false;
    }

    $('#formulario_facturacion #fact_eval').val(0);
    $('#formulario_facturacion #muestras_id').val(muestras_id);
    $('#formulario_facturacion #pacientes_id').val(datos[0]);
    $('#formulario_facturacion #cliente_nombre').val(datos[1]);
    $('#formulario_facturacion #colaborador_id').val(datos[3]);
    $('#formulario_facturacion #colaborador_nombre').val(datos[4]);
    $('#formulario_facturacion #servicio_id').val(datos[5]);

    safeSelectpickerRefresh($('#formulario_facturacion #servicio_id'));
    safeSelect2Refresh($('#formulario_facturacion #servicio_id'));

    $('#formulario_facturacion #material_enviado_muestra').val(datos[6]);
    $('#formulario_facturacion #paciente_muestra_codigo').val(datos[7]);
    $('#formulario_facturacion #paciente_muestra').val(datos[8]);
    $('#formulario_facturacion #muestras_numero').val(datos[9]);

    getFechaActual().done(function(fechaActual){
      $('#formulario_facturacion #fecha').val($.trim(valorAjax(fechaActual)));
    }).fail(function(){
      $('#formulario_facturacion #fecha').val(convertDate(new Date()));
    });

    $('#formulario_facturacion #fecha').attr("readonly", true);
    $('#formulario_facturacion #fecha').attr('disabled', false);

    $('#formulario_facturacion #validar').attr("disabled", false);
    $('#formulario_facturacion #addRows').attr("disabled", false);
    $('#formulario_facturacion #removeRows').attr("disabled", false);

    $('#cobrar').hide();

    $('#formulario_facturacion #validar').show();
    $('#formulario_facturacion #editar').hide();
    $('#formulario_facturacion #eliminar').hide();

    getPacienteTipo(datos[0]).done(function(tipoPaciente){
      if ($.trim(valorAjax(tipoPaciente)) == 2) {
        $('#formulario_facturacion #grupo_paciente_factura').show();
      } else {
        $('#formulario_facturacion #grupo_paciente_factura').hide();
      }
    });

    if (producto !== '' && nombre_producto !== '' && parseFloat(precio_venta || 0) > 0) {
      $('#formulario_facturacion #invoiceItem #productoID_0').val(producto);
      $('#formulario_facturacion #invoiceItem #productName_0').val(nombre_producto);
      $('#formulario_facturacion #invoiceItem #quantity_0').val(1);
      $('#formulario_facturacion #invoiceItem #discount_0').val(0);
      $('#formulario_facturacion #invoiceItem #price_0').val(precio_venta);
      $('#formulario_facturacion #invoiceItem #total_0').val(precio_venta);

      cargarTotalesFacturaDesdeMuestra(precio_venta, isv);
    } else {
      $('#formulario_facturacion #subTotal').val(0);
      $('#formulario_facturacion #taxAmount').val(0);
      $('#formulario_facturacion #taxDescuento').val(0);
      $('#formulario_facturacion #totalAftertax').val(0);

      $('#subTotalFooter').val(0);
      $('#taxAmountFooter').val(0);
      $('#taxDescuentoFooter').val(0);
      $('#totalAftertaxFooter').val(0);

      console.warn("La muestra abrió en factura, pero no recibió producto automático. Agregue el producto manualmente.");
    }

    if (muestra === "Muestra") {
      $('.counter-container').hide();
      $('#facturas-counter').hide();
    }

    abrirPantallaFacturaDesdeMuestra();

    return false;
  }).fail(function(xhr){
    console.error("Error editarFacturasMuestras.php:", xhr.responseText);
    mostrarErrorSimple("Error", "No se pudo consultar la información de la muestra.");
  }).always(function(){
    if (typeof hideLoading === 'function') {
      hideLoading();
    }
  });

  return false;
}

/****************************************************************************************************************************************************************/
// PAGO
/****************************************************************************************************************************************************************/

function pago(facturas_id){
  var url = SERVERURL + 'php/facturacion/editarPago.php';

  $.ajax({
    type: 'POST',
    url: url,
    data: {
      facturas_id: facturas_id
    }
  }).done(function(valores){
    var datos = parseJsonSeguro(valores);

    if (!datos) {
      mostrarErrorSimple("Error", "No se pudo leer la información del pago.");
      return false;
    }

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
  });
}

/****************************************************************************************************************************************************************/
// FOCUS MODAL PAGOS
/****************************************************************************************************************************************************************/

$(document).off('click.admisionPagoTab1', '#tab1');
$(document).on('click.admisionPagoTab1', '#tab1', function(){
  $("#modal_pagos").off('shown.bs.modal.admisionPagoTab1').on('shown.bs.modal.admisionPagoTab1', function(){
    $(this).find('#formEfectivoBill #efectivo_bill').focus();
  });
});

$(document).off('click.admisionPagoTab2', '#tab2');
$(document).on('click.admisionPagoTab2', '#tab2', function(){
  $("#modal_pagos").off('shown.bs.modal.admisionPagoTab2').on('shown.bs.modal.admisionPagoTab2', function(){
    $(this).find('#formTarjetaBill #cr_bill').focus();
  });
});

$(document).off('click.admisionPagoTab3', '#tab3');
$(document).on('click.admisionPagoTab3', '#tab3', function(){
  $("#modal_pagos").off('shown.bs.modal.admisionPagoTab3').on('shown.bs.modal.admisionPagoTab3', function(){
    $(this).find('#formTransferenciaBill #bk_nm').focus();
  });
});

$(document).off('click.admisionPagoTab4', '#tab4');
$(document).on('click.admisionPagoTab4', '#tab4', function(){
  $("#modal_pagos").off('shown.bs.modal.admisionPagoTab4').on('shown.bs.modal.admisionPagoTab4', function(){
    $(this).find('#formChequeBill #bk_nm_chk').focus();
  });
});

$(document).off('click.admisionPagoTab5', '#tab5');
$(document).on('click.admisionPagoTab5', '#tab5', function(){
  $("#modal_pagos").off('shown.bs.modal.admisionPagoTab5').on('shown.bs.modal.admisionPagoTab5', function(){
    $(this).find('#formMixtoBill #efectivo_bill_mixto').focus();
  });
});

/****************************************************************************************************************************************************************/
// INPUTMASKS
/****************************************************************************************************************************************************************/

$(document).ready(function(){
  if ($.fn.inputmask) {
    $('#formMixtoPurchaseBill #cr_bill_mixtoPurchase').inputmask("9999");
    $('#formMixtoPurchaseBill #exp_mixtoPurchase').inputmask("99/99");
    $('#formMixtoPurchaseBill #cvcpwd_mixtoPurchase').inputmask("999999");

    $('#formTarjetaBill #cr_bill').inputmask("9999");
    $('#formTarjetaBill #exp').inputmask("99/99");
    $('#formTarjetaBill #cvcpwd').inputmask("999999");
  }
});

/****************************************************************************************************************************************************************/
// CÁLCULOS EFECTIVO Y MIXTO
/****************************************************************************************************************************************************************/

$(document).off('keyup.admisionPagoEfectivo', '#formEfectivoBill #efectivo_bill');
$(document).on('keyup.admisionPagoEfectivo', '#formEfectivoBill #efectivo_bill', function(){
  var efectivo = parseFloat($("#formEfectivoBill #efectivo_bill").val() || 0).toFixed(2);
  var monto = parseFloat($("#formEfectivoBill #monto_efectivo").val() || 0).toFixed(2);
  var total = efectivo - monto;

  if(Math.floor(efectivo * 100) >= Math.floor(monto * 100)){
    $('#formEfectivoBill #cambio_efectivo').val(parseFloat(total).toFixed(2));
    $('#formEfectivoBill #pago_efectivo').attr('disabled', false);
  }else{
    $('#formEfectivoBill #cambio_efectivo').val(parseFloat(0).toFixed(2));
    $('#formEfectivoBill #pago_efectivo').attr('disabled', true);
  }
});

$(document).off('keyup.admisionPagoMixto', '#formMixtoBill #efectivo_bill_mixto');
$(document).on('keyup.admisionPagoMixto', '#formMixtoBill #efectivo_bill_mixto', function(){
  var efectivo = parseFloat($("#formMixtoBill #efectivo_bill_mixto").val() || 0).toFixed(2);
  var monto = parseFloat($("#formMixtoBill #monto_efectivo_mixto").val() || 0).toFixed(2);

  if(Math.floor(efectivo * 100) >= Math.floor(monto * 100)){
    $('#formMixtoBill #pago_efectivo_mixto').attr('disabled', true);
    $('#formMixtoBill #monto_tarjeta').val(parseFloat(0).toFixed(2));
    $('#formMixtoBill #monto_tarjeta').attr('disabled', true);
  }else{
    var tarjeta = monto - efectivo;

    $('#formMixtoBill #monto_tarjeta').val(parseFloat(tarjeta).toFixed(2));
    $('#formMixtoBill #cambio_efectivo_mixto').val(parseFloat(0).toFixed(2));
    $('#formMixtoBill #pago_efectivo_mixto').attr('disabled', false);
    $('#formMixtoBill #monto_tarjeta').attr('disabled', false);
  }
});

/****************************************************************************************************************************************************************/
// IMPRESIÓN
/****************************************************************************************************************************************************************/

function printBill(facturas_id){
  var url = SERVERURL + 'php/facturacion/generaFactura.php?facturas_id=' + facturas_id;
  window.open(url);
}

function printBillGroup(facturas_id){
  var url = SERVERURL + 'php/facturacion/generaFacturaGrupal.php?facturas_id=' + facturas_id;
  window.open(url);
}

/****************************************************************************************************************************************************************/
// ARRANQUE FINAL
/****************************************************************************************************************************************************************/

$(window).off('load.admisionInit').on('load.admisionInit', function(){
  initPage();

  setTimeout(function(){
    aplicarSelect2VistaPrincipal();
    setEstadoMuestrasPendientePorDefecto();
  }, 300);

  setTimeout(function(){
    aplicarSelect2VistaPrincipal();
    setEstadoMuestrasPendientePorDefecto();
  }, 800);

  setTimeout(function(){
    aplicarSelect2VistaPrincipal();
    setEstadoMuestrasPendientePorDefecto();
  }, 1500);

  setTimeout(function(){
    aplicarSelect2VistaPrincipal();
    setEstadoMuestrasPendientePorDefecto();
  }, 2500);
});
</script>