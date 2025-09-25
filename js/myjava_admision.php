<script>
// ===== Helpers =====
function safeRefresh($el){
  if ($el && $el.length && $.fn && $.fn.selectpicker) {
    $el.selectpicker('refresh');
  }
}

// ===== Cargas iniciales (devuelven Promesa) =====
function getEstadoMuestra(){
  var url = '<?php echo SERVERURL; ?>php/admision/getStatusMuestra.php';
  return $.ajax({ type:'POST', url:url }).done(function(data){
    var $s = $('#form_main_admision_muestras #estado');
    $s.html(data); safeRefresh($s);
  });
}

function getEstadoPaciente(){
  var url = '<?php echo SERVERURL; ?>php/admision/getStatusPaciente.php';
  return $.ajax({ type:'POST', url:url }).done(function(data){
    var $s = $('#form_main_admision #estado');
    $s.html(data); safeRefresh($s);
  });
}

function getTipoMuestra(){
  var url = '<?php echo SERVERURL; ?>php/admision/getTipoMuestra.php';
  return $.ajax({ type:'POST', url:url }).done(function(data){
    var $a = $('#formulario_admision #tipo_muestra');
    $a.html(data); safeRefresh($a);

    var $b = $('#form_main_admision_muestras #tipo_muestra');
    $b.html(data); safeRefresh($b);
  });
}

function getGenero(){
  var url = '<?php echo SERVERURL; ?>php/admision/getSexo.php';
  return $.ajax({ type:'POST', url:url }).done(function(data){
    var $a = $('#formulario_admision #genero');
    $a.html(data); safeRefresh($a);

    var $b = $('#formulario_admision_clientes_editar #genero');
    $b.html(data); safeRefresh($b);
  });
}

function getClientesAdmision(){
  var url = '<?php echo SERVERURL; ?>php/admision/getClientes.php';
  return $.ajax({ type:'POST', url:url }).done(function(data){
    var $s = $('#formulario_admision #cliente_admision');
    $s.html(data); safeRefresh($s);
  });
}

function getEmpresa(){
  var url = '<?php echo SERVERURL; ?>php/admision/getEmpresa.php';
  return $.ajax({ type:'POST', url:url }).done(function(data){
    var $s = $('#formulario_admision #empresa');
    $s.html(data); safeRefresh($s);
  });
}

function getTipo(){
  var url = '<?php echo SERVERURL; ?>php/admision/getTipoPaciente.php';
  return $.ajax({ type:'POST', url:url }).done(function(data){
    var $s = $('#formulario_admision #paciente_tipo');
    $s.html(data); safeRefresh($s);
  });
}

function getRemitente(){
  var url = '<?php echo SERVERURL; ?>php/admision/getRemitente.php';
  return $.ajax({ type:'POST', url:url }).done(function(data){
    var $s = $('#formulario_admision #remitente');
    $s.html(data); safeRefresh($s);
  });
}

function getHospitales(){
  var url = '<?php echo SERVERURL; ?>php/admision/getHospitales.php';
  return $.ajax({ type:'POST', url:url }).done(function(data){
    var $a = $('#formulario_admision #hospital');
    $a.html(data); safeRefresh($a);

    var $b = $('#formulario_admision_empresas #hospital_empresa');
    $b.html(data); safeRefresh($b);
  });
}

function getCategorias(){
  var url = '<?php echo SERVERURL; ?>php/admision/getCategoriaMuestra.php';
  return $.ajax({ type:'POST', url:url }).done(function(data){
    var $s = $('#formulario_admision #categoria');
    $s.html(data); safeRefresh($s);
  });
}

function getTipoPacienteSelect(){
  var url = '<?php echo SERVERURL; ?>php/admision/getTipoPaciente.php';
  return $.ajax({ type:'POST', url:url }).done(function(data){
    var $a = $('#form_main_admision #tipo');
    $a.html(data); safeRefresh($a);

    var $b = $('#form_main_admision_muestras #tipo');
    $b.html(data); safeRefresh($b);
  });
}

// ===== Orquestador: carga catálogos esenciales y luego pagina =====
function initPage(){
  var essentials = [
    getEstadoPaciente(),
    getTipoPacienteSelect()
  ];
  var extras = [
    getEstadoMuestra(),
    getGenero(),
    getTipoMuestra(),
    getEmpresa(),
    getRemitente(),
    getHospitales(),
    getCategorias(),
    getClientesAdmision(),
    getTipo(),
    getServicio()
  ];

  $.when.apply($, essentials).always(function(){
    setTimeout(function(){ pagination(1, true); }, 0);
  });

  $.when.apply($, extras);
}

// ===== Eventos de foco en modales =====
$(document).ready(function(){
  $("#modal_historico_muestras").on('shown.bs.modal', function(){
    $(this).find('#form_main_historico_muestras #bs_regis').focus();
  });
});
$(document).ready(function(){
  $("#modal_admision_clientes").on('shown.bs.modal', function(){
    $(this).find('#formulario_admision #name').focus();
  });
});
$(document).ready(function(){
  $("#modal_admision_clientes_editar").on('shown.bs.modal', function(){
    $(this).find('#formulario_admision_clientes_editar #name').focus();
  });
});
$(document).ready(function(){
  $("#modal_admision_empesas").on('shown.bs.modal', function(){
    $(this).find('#formulario_admision_empresas #empresa').focus();
  });
});

// ===== Listeners de filtros/búsquedas =====
$(document).ready(function(){
  $('#form_main_admision #bs_regis').on('keyup',function(){ pagination(1); });
  $('#form_main_admision #estado').on('change', function(){ pagination(1); });
  $('#form_main_admision #tipo').on('change',function(){ pagination(1); });

  $('#form_main_admision_muestras #estado').on('change',function(){ paginationMuestras(1); });
  $('#form_main_admision_muestras #cliente').on('change',function(){ paginationMuestras(1); });
  $('#form_main_admision_muestras #tipo_muestra').on('change',function(){ paginationMuestras(1); });

  $('#form_main_admision_muestras #buscar_registro').on('click',function(e){
    e.preventDefault();
    paginationMuestras(1);
  });

  $('#formulario_admision #fecha_nac').on('change',function(){
    CalcularEdadClientes();
  });
});

// ===== Utilidades =====
function CalcularEdadClientes(){
  var url = '<?php echo SERVERURL; ?>php/admision/calcularEdad.php';
  $.ajax({
    type:'POST',
    data:'fecha_nac='+$('#formulario_admision #fecha_nac').val(),
    url:url,
    success: function(data){
      $('#formulario_admision #edad').val(data);
    }
  });
  return false;
}

function getFechaActual(){
  var url = '<?php echo SERVERURL; ?>php/admision/getFechaActual.php';
  var fecha_actual;
  $.ajax({ type:'POST', url:url, async:false, success:function(data){ fecha_actual = data; } });
  return fecha_actual;
}

function getPacienteTipo(pacientes_id){
  var url = '<?php echo SERVERURL; ?>php/admision/getPacienteTipo.php';
  var tipo_paciente;
  $.ajax({ type:'POST', url:url, data:'pacientes_id='+pacientes_id, async:false, success:function(data){ tipo_paciente = data; } });
  return tipo_paciente;
}

// ===== Dependencia de selects =====
$('#form_main_admision_muestras #tipo').on('change', function(){
  var url = '<?php echo SERVERURL; ?>php/admision/getEmpresaCliente.php';
  $.ajax({
    type:'POST',
    url:url,
    data:'tipo='+$('#form_main_admision_muestras #tipo').val(),
    async: false,
    success:function(data){
      var $c = $('#form_main_admision_muestras #cliente');
      $c.html(data); safeRefresh($c);
    }
  });
});

$('#formulario_admision #cliente_admision').on('change', function(){
  var url = '<?php echo SERVERURL; ?>php/admision/consultarClientes.php';
  $.ajax({
    type:'POST',
    url:url,
    data:'pacientes_id='+$('#formulario_admision #cliente_admision').val(),
    async: false,
    success:function(data){
      var valores = eval(data);
      $('#formulario_admision #name').val(valores[0]);
      $('#formulario_admision #lastname').val(valores[1]);
      $('#formulario_admision #rtn').val(valores[2]);
      $('#formulario_admision #edad').val(valores[3]);
      $('#formulario_admision #telefono1').val(valores[4]);
      $('#formulario_admision #genero').val(valores[5]); safeRefresh($('#formulario_admision #genero'));
      $('#formulario_admision #direccion').val(valores[6]);
      $('#formulario_admision #correo').val(valores[7]);
    }
  });
});

$('#formulario_admision #tipo_muestra').on('change', function(){
  var url = '<?php echo SERVERURL; ?>php/admision/getProductos.php';
  $.ajax({
    type:'POST',
    url:url,
    data:'tipo_muestra_id='+$('#formulario_admision #tipo_muestra').val(),
    async: false,
    success:function(data){
      var $p = $('#formulario_admision #producto');
      $p.html(data); safeRefresh($p);
    }
  });
});

// ===== Paginación (con JSON.parse y retry en 1a carga) =====
function pagination(partida, firstLoad){
  var url  = '<?php echo SERVERURL; ?>php/admision/paginar.php';
  var tipo = $('#form_main_admision #tipo').val() || 1;
  var dato = $('#form_main_admision #bs_regis').val() || '';
  var estado = $('#form_main_admision #estado').val() || 1;

  $.ajax({
    type:'POST',
    url:url,
    data:'partida='+partida+'&tipo='+tipo+'&dato='+dato+'&estado='+estado,
    success:function(data){
      try{
        var array = (typeof data === 'string') ? JSON.parse(data) : data;
        $('#agrega-registros').html(array[0]);
        $('#pagination').html(array[1]);
      }catch(e){
        console.error('JSON parse error en pagination:', e, data);
      }
    },
    error:function(){
      if (firstLoad) {
        setTimeout(function(){ pagination(partida, false); }, 150);
      }
    }
  });
  return false;
}

function paginationMuestras(partida){
  var url = '<?php echo SERVERURL; ?>php/admision/paginarMuestras.php';

  // NO pongas 2 como fallback. Si no hay selección, no filtres por estado.
  var estado = $('#form_main_admision_muestras #estado').val(); // puede venir "", null o "0/1/2"
  var cliente = $('#form_main_admision_muestras #cliente').val() || '';
  var tipo_muestra = $('#form_main_admision_muestras #tipo_muestra').val() || '';
  var fecha_i = $('#form_main_admision_muestras #fecha_i').val() || '';
  var fecha_f = $('#form_main_admision_muestras #fecha_f').val() || '';
  var dato = $('#form_main_admision_muestras #bs_regis').val() || '';

  $.ajax({
    type:'POST',
    url:url,
    data:'partida='+partida+'&estado='+encodeURIComponent(estado ?? '')+'&cliente='+cliente+'&tipo_muestra='+tipo_muestra+'&fecha_i='+fecha_i+'&fecha_f='+fecha_f+'&dato='+encodeURIComponent(dato),
    beforeSend: function(){ if (typeof showLoading === 'function') showLoading("Por favor espere..."); },
    success:function(data){
      try{
        var array = (typeof data === 'string') ? JSON.parse(data) : data;
        $('#agrega-registros_muestras').html(array[0]);
        $('#pagination_muestras').html(array[1]);
      }catch(e){
        console.error('JSON parse error en paginationMuestras:', e, data);
      }finally{
        if (typeof hideLoading === 'function') hideLoading();
      }
    },
    error : function(){
      if (typeof hideLoading === 'function') hideLoading();
      if (typeof swal === 'function'){
        swal({ title:'Error', text:'No se enviaron los datos, favor corregir', icon:'error',
          dangerMode:true, closeOnEsc:false, closeOnClickOutside:false });
      }
    }
  });
  return false;
}

// ===== Botones que abren modales/listas =====
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
  getClientesAdmision();
  getGenero();
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
  safeRefresh($('#formulario_admision #producto'));

  getEmpresa();
  getRemitente();
  getHospitales();
  getTipoMuestra();
  getCategorias();
});

// ===== Consultas auxiliares =====
function consultarExpediente(pacientes_id){
  var url = '<?php echo SERVERURL; ?>php/pacientes/getExpedienteInformacion.php';
  var resp;
  $.ajax({ type:'POST', url:url, data:'pacientes_id='+pacientes_id, async:false, success:function(data){ resp = data; } });
  return resp;
}

function consultarNumeroMuestra(muestras_id){
  var url = '<?php echo SERVERURL; ?>php/admision/getNumeroMuestra.php';
  var resp;
  $.ajax({ type:'POST', url:url, data:'muestras_id='+muestras_id, async:false, success:function(data){ resp = data; } });
  return resp;
}

function consultarNombre(pacientes_id){
  var url = '<?php echo SERVERURL; ?>php/pacientes/getNombre.php';
  var resp;
  $.ajax({ type:'POST', url:url, data:'pacientes_id='+pacientes_id, async:false, success:function(data){ resp = data; } });
  return resp;
}

// ===== Anular/Eliminar muestras y pacientes =====
function anularRegistroMuestra(muestras_id, pacientes_id, comentario){
  var url = '<?php echo SERVERURL; ?>php/admision/anularMuestras.php';
  $.ajax({
    type:'POST',
    url:url,
    data:'muestras_id='+muestras_id+'&pacientes_id='+pacientes_id+'&comentario='+comentario,
    success: function(registro){
      if(registro == 1){
        showNotify("success", "Success", "Registro anulado correctamente");
        paginationMuestras(1);
        return false;
      }else if(registro == 2){
        showNotify("error", "Error", "Lo sentimos ya existe una factura para esta muestra, por favor anule la factrua antes de proceder.");
        return false;
      }else if(registro == 3){
        showNotify("error", "Error", "No se puede anular este registro");
        return false;
      }else{
        showNotify("error", "Error", "Error al completar el registro");
        return false;
      }
    }
  });
  return false;
}

function eliminarRegistroMuestra(muestras_id, pacientes_id, comentario){
  var url = '<?php echo SERVERURL; ?>php/admision/eliminarMuestras.php';
  $.ajax({
    type:'POST',
    url:url,
    data:'muestras_id='+muestras_id+'&pacientes_id='+pacientes_id+'&comentario='+comentario,
    success: function(registro){
      if(registro == 1){
        showNotify("success", "Success", "Registro eliminado correctamente");
        paginationMuestras(1);
        return false;
      }else if(registro == 2){
        showNotify("error", "Error", "No se puede eliminar este registro");
        return false;
      }else if(registro == 3){
        showNotify("error", "Error", "No se puede eliminar este registro, cuenta con información almacenada");
        return false;
      }else{
        showNotify("error", "Error", "Error al completar el registro");
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
        showNotify("success", "Success", "Registro eliminado correctamente");
        pagination(1);
        return false;
      }else if(registro == 2){
        showNotify("error", "Error", "No se puede eliminar este registro");
        return false;
      }else if(registro == 3){
        showNotify("error", "Error", "No se puede eliminar este registro, cuenta con información almacenada");
        return false;
      }else{
        showNotify("error", "Error", "Error al completar el registro");
        return false;
      }
    }
  });
  return false;
}

// ===== Habilitar / Inhabilitar =====
function DisableRegister(pacientes_id){
  var nombre_usuario = consultarNombre(pacientes_id);
  var expediente_usuario = consultarExpediente(pacientes_id);
  var estado = $('#form_main_admision #estado').val();
  var estado_label = '';
  var dato;

  if (estado == "1") {
    estado_label = "Inhabilitar";
  } else {
    estado_label = "Habilitar";
  }

  if(expediente_usuario == 0){
    dato = nombre_usuario;
  }else{
    dato = nombre_usuario + " (Expediente: " + expediente_usuario + ")";
  }

  swal({
    title: "¿Estas seguro?",
    text: "¿Desea " + estado_label + " este cliente: " + dato + "?",
    content: {
      element: "input",
      attributes: { placeholder: "Comentario", type: "text" },
    },
    icon: "warning",
    buttons: {
      cancel: "Cancelar",
      confirm: { text: "¡Sí, " + estado_label + " el cliente!", closeModal: false },
    },
    dangerMode: true,
    closeOnEsc: false,
    closeOnClickOutside: false
  }).then((value) => {
    if (value === null || value.trim() === "") { return false; }
    deshabilitarPaciente(pacientes_id, value, estado);
  });
}

function deshabilitarPaciente(pacientes_id, comentario, estado) {
  var url = '<?php echo SERVERURL; ?>php/admision/DeshabilitarPaciente.php';
  estado = (estado === null || estado === '') ? 1 : estado;

  $.ajax({
    type: 'POST',
    url: url,
    data: { pacientes_id: pacientes_id, comentario: comentario, estado: estado },
    dataType: "json",
    success: function(response) {
      if (response.status === "success") {
        showNotify("success", "Success", response.message);
        pagination(1);
      } else {
        showNotify("error", "Error", response.message);
      }
    },
    error: function() {
      showNotify("error", "Error", "Error en la comunicación con el servidor");
    }
  });
}

// ===== Modales de eliminar/anular =====
function modal_eliminar(pacientes_id){
  if (consultarExpediente(pacientes_id) != 0 && (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3)){
    var nombre_usuario = consultarNombre(pacientes_id);
    var expediente_usuario = consultarExpediente(pacientes_id);
    var dato = (expediente_usuario == 0) ? nombre_usuario : (nombre_usuario + " (Expediente: " + expediente_usuario + ")");

    swal({
      title: "¿Estas seguro?",
      text: "¿Desea eliminar este cliente: " + dato + "?",
      content: { element: "input", attributes: { placeholder: "Comentario", type: "text" } },
      icon: "warning",
      buttons: { cancel: "Cancelar", confirm: { text: "¡Sí, eliminar el cliente!", closeModal: false } },
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
  }else if (consultarExpediente(pacientes_id) == 0 && (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3)){
    var nombre_usuario = consultarNombre(pacientes_id);
    var expediente_usuario = consultarExpediente(pacientes_id);
    var dato = (expediente_usuario == 0) ? nombre_usuario : (nombre_usuario + " (Expediente: " + expediente_usuario + ")");

    swal({
      title: "¿Estas seguro?",
      text: "¿Desea eliminar este cliente: " + dato + "?",
      content: { element: "input", attributes: { placeholder: "Comentario", type: "text" } },
      icon: "warning",
      buttons: { cancel: "Cancelar", confirm: { text: "¡Sí, eliminar el cliente!", closeModal: false } },
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
  if (consultarExpediente(pacientes_id) != 0 && (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3)){
    var nombre_usuario = consultarNombre(pacientes_id);
    var numero_muestra = consultarNumeroMuestra(muestras_id);
    var dato = nombre_usuario + " (Muestra: " + numero_muestra + ")";

    swal({
      title: "¿Estas seguro?",
      text: "¿Desea eliminar esta muestra para el cliente: " + dato + "?",
      content: { element: "input", attributes: { placeholder: "Comentario", type: "text" } },
      icon: "warning",
      buttons: { cancel: "Cancelar", confirm: { text: "¡Sí, anular la muestra!", closeModal: false } },
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
  }else if (consultarExpediente(pacientes_id) == 0 && (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3)){
    var nombre_usuario = consultarNombre(pacientes_id);
    var expediente_usuario = consultarExpediente(pacientes_id);
    var dato = (expediente_usuario == 0) ? nombre_usuario : (nombre_usuario + " (Expediente: " + expediente_usuario + ")");

    swal({
      title: "¿Estas seguro?",
      text: "¿Desea eliminar esta muestra para el cliente: " + dato + "?",
      content: { element: "input", attributes: { placeholder: "Comentario", type: "text" } },
      icon: "warning",
      buttons: { cancel: "Cancelar", confirm: { text: "¡Sí, anular la muestra!", closeModal: false } },
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
    showNotify("error", "Acceso Denegado", "No tiene permisos para ejecutar esta acción");
    return false;
  }
}

function modalAnularMuestras(pacientes_id, muestras_id){
  if (consultarExpediente(pacientes_id) != 0 && (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3)){
    var nombre_usuario = consultarNombre(pacientes_id);
    var numero_muestra = consultarNumeroMuestra(muestras_id);
    var dato = nombre_usuario + " (Muestra: " + numero_muestra + ")";

    swal({
      title: "¿Estas seguro?",
      text: "¿Desea anular esta muestra para el cliente: " + dato + "?",
      content: { element: "input", attributes: { placeholder: "Comentario", type: "text" } },
      icon: "warning",
      buttons: { cancel: "Cancelar", confirm: { text: "¡Sí, anular la muestra!", closeModal: false } },
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
  }else if (consultarExpediente(pacientes_id) == 0 && (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3)){
    var nombre_usuario = consultarNombre(pacientes_id);
    var expediente_usuario = consultarExpediente(pacientes_id);
    var dato = (expediente_usuario == 0) ? nombre_usuario : (nombre_usuario + " (Expediente: " + expediente_usuario + ")");

    swal({
      title: "¿Estas seguro?",
      text: "¿Desea anular esta muestra para el cliente: " + dato + "?",
      content: { element: "input", attributes: { placeholder: "Comentario", type: "text" } },
      icon: "warning",
      buttons: { cancel: "Cancelar", confirm: { text: "¡Sí, anular la muestra!", closeModal: false } },
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

// ===== Editar registros =====
function editarRegistro(pacientes_id){
  if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
    var url = '<?php echo SERVERURL; ?>php/admision/consultarClientes.php';

    $.ajax({
      type:'POST',
      url:url,
      data:'pacientes_id='+pacientes_id,
      success: function(valores){
        var datos = eval(valores);

        if($('#form_main_admision #tipo').val() == 1 || $('#form_main_admision #tipo').val() == ""){
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
          safeRefresh($('#formulario_admision_clientes_editar #genero'));
          $('#formulario_admision_clientes_editar #direccion').val(datos[6]);
          $('#formulario_admision_clientes_editar #correo').val(datos[7]);

          $('#formulario_admision_clientes_editar').attr({ 'data-form': 'update' });
          $('#formulario_admision_clientes_editar').attr({ 'action': '<?php echo SERVERURL; ?>php/admision/modificarRegistro.php' });

          //Habilitar
          $('#formulario_admision_clientes_editar #name').attr('readonly', false);
          $('#formulario_admision_clientes_editar #lastname').attr('readonly', false);
          $('#formulario_admision_clientes_editar #rtn').attr('readonly', false);
          $('#formulario_admision_clientes_editar #fecha_nac').attr('disabled', false);
          $('#formulario_admision_clientes_editar #edad').attr('readonly', false);
          $('#formulario_admision_clientes_editar #telefono1').attr('readonly', false);
          $('#formulario_admision_clientes_editar #genero').attr('disabled', false);
          $('#formulario_admision_clientes_editar #direccion').attr('readonly', false);
          $('#formulario_admision_clientes_editar #correo').attr('readonly', false);

          $('#modal_admision_clientes_editar').modal({ show:true, keyboard: false, backdrop:'static' });
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

          $('#formulario_admision_empresas').attr({ 'data-form': 'update' });
          $('#formulario_admision_empresas').attr({ 'action': '<?php echo SERVERURL; ?>php/admision/modificarRegistroEmpresas.php' });

          $('#formulario_admision_empresas #name').attr('readonly', false);
          $('#formulario_admision_empresas #rtn').attr('readonly', false);
          $('#formulario_admision_empresas #telefono1').attr('readonly', false);
          $('#formulario_admision_empresas #direccion').attr('readonly', false);
          $('#formulario_admision_empresas #correo').attr('readonly', false);

          $('#modal_admision_empesas').modal({ show:true, keyboard: false, backdrop:'static' });
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
  $('#formulario_admision_clientes_editar').attr({ 'data-form': 'update' });
  $('#formulario_admision_clientes_editar').attr({ 'action': '<?php echo SERVERURL; ?>php/admision/agregarRegistro.php' });
  $('#formulario_admision_clientes_editar')[0].reset();
  $('#formulario_admision_clientes_editar #pro_admision').val("Registro");
  $('#reg_admision').show();
  $('#edi_admision').hide();
  $('#delete_admision').hide();

  $('#formulario_admision_clientes_editar #paciente_tipo').val(1);
  safeRefresh($('#formulario_admision_clientes_editar #paciente_tipo'));

  //OBTENER EL CODIGO DE CLINICAS
  $('#formulario_admision #hospital').val(getHospitalCodigo());
  safeRefresh($('#formulario_admision #hospital'));
}

function getHospitalCodigo(){
  var url = '<?php echo SERVERURL; ?>php/pacientes/getHospitalCodigo.php';
  var resp;
  $.ajax({ type:'POST', url:url, async: false, success:function(data){ resp = data; } });
  return resp;
}

function getRemitenteCodigo(){
  var url = '<?php echo SERVERURL; ?>php/pacientes/getRemitenteCodigo.php';
  var resp;
  $.ajax({ type:'POST', url:url, async: false, success:function(data){ resp = data; } });
  return resp;
}

// ===== Historial de muestras =====
function showModalhistoriaMuestrasEmpresas(pacientes_id){
  if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
    $('#form_main_historico_muestras #pacientes_id_muestras').val(pacientes_id);
    $('#modal_historico_muestras').modal({ show:true, keyboard: false, backdrop:'static' });

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

$('#form_main_historico_muestras #bs_regis').on('keyup',function(){
  if(getPacienteTipo($('#form_main_historico_muestras #pacientes_id_muestras').val()) == 1){
    historiaMuestrasPacientes(1);
  }else{
    historiaMuestrasEmpresas(1);
  }
});

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
      var array = (typeof data === 'string') ? JSON.parse(data) : data;
      $('#detalles-historico-muestras').html(array[0]);
      $('#pagination-historico-muestras').html(array[1]);
    }
  });
  return false;
}

function historiaMuestrasPacientes(partida){
  var url = '<?php echo SERVERURL; ?>php/admision/paginar_historico_muestras_pacientes.php';
  var pacientes_id = $('#form_main_historico_muestras #pacientes_id_muestras').val();
  var dato = $('#form_main_historico_muestras #bs_regis').val();

  $.ajax({
    type:'POST',
    url:url,
    async: true,
    data:'partida='+partida+'&pacientes_id='+pacientes_id+'&dato='+dato,
    success:function(data){
      var array = (typeof data === 'string') ? JSON.parse(data) : data;
      $('#detalles-historico-muestras').html(array[0]);
      $('#pagination-historico-muestras').html(array[1]);
    }
  });
  return false;
}

// ===== Modal clientes / empresas =====
function modalClientes(){
  $('#formulario_admision').attr({ 'data-form': 'save' });
  $('#formulario_admision').attr({ 'action': '<?php echo SERVERURL; ?>php/admision/agregarRegistro.php' });
  $('#formulario_admision')[0].reset();
  $('#formulario_admision #pro_admision').val("Registro");
  $('#reg_admision').show();

  $('#formulario_admision #paciente_tipo').val(1);
  safeRefresh($('#formulario_admision #paciente_tipo'));

  $('#formulario_admision #hospital').val(getHospitalCodigo());
  safeRefresh($('#formulario_admision #hospital'));

  $('#formulario_admision #remitente').val(getRemitenteCodigo());
  safeRefresh($('#formulario_admision #remitente'));

  //Habilitar
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
  getClientesAdmision();
  getEmpresa();

  $('#modal_admision_clientes').modal({ show:true, keyboard: false, backdrop:'static' });
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
  paginationMuestras(1);
});

function modaEmpresa(){
  $('#formulario_admision_empresas').attr({ 'data-form': 'save' });
  $('#formulario_admision_empresas').attr({ 'action': '<?php echo SERVERURL; ?>php/admision/agregarRegistroEmpresas.php' });
  $('#formulario_admision_empresas')[0].reset();
  $('#formulario_admision_empresas #pro_admision').val("Registro");
  $('#reg_admisionemp').show();
  $('#edi_admisionemp').hide();
  $('#delete_admisionemp').hide();

  $('#formulario_admision_empresas #paciente_tipo').val(1);
  safeRefresh($('#formulario_admision_empresas #paciente_tipo'));

  //Habilitar
  $('#formulario_admision_empresas #name').attr('readonly', false);
  $('#formulario_admision_empresas #lastname').attr('readonly', false);
  $('#formulario_admision_empresas #rtn').attr('readonly', false);
  $('#formulario_admision_empresas #fecha_nac').attr('disabled', false);
  $('#formulario_admision_empresas #edad').attr('readonly', false);
  $('#formulario_admision_empresas #telefono1').attr('readonly', false);
  $('#formulario_admision_empresas #genero').attr('disabled', false);
  $('#formulario_admision_empresas #direccion').attr('readonly', false);
  $('#formulario_admision_empresas #correo').attr('readonly', false);

  $('#modal_admision_empesas').modal({ show:true, keyboard: false, backdrop:'static' });
}

// ===== Facturación =====
function convertDate(inputFormat) {
  function pad(s) { return (s < 10) ? '0' + s : s; }
  var d = new Date(inputFormat);
  return [d.getFullYear(), pad(d.getMonth()+1), pad(d.getDate())].join('-');
}

function formFactura(){
  $('#formulario_facturacion')[0].reset();
  $('#main_facturacion').hide();
  $('#facturacion').show();
  $('#label_acciones_volver').html("Volver");
  $('#acciones_atras').removeClass("active");
  $('#acciones_factura').addClass("active");
  $('#label_acciones_factura').html("Factura");
  $('#formulario_facturacion #fact_eval').val(0);// viene de muestra
  $('#formulario_facturacion #fecha').attr('disabled', false);
  $('#formulario_facturacion').attr({ 'data-form': 'save' });
  $('#formulario_facturacion').attr({ 'action': '<?php echo SERVERURL; ?>php/facturacion/addPreFactura.php' });
  limpiarTabla();
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
}

$('#formulario_facturacion #validar').on('click', function(e){
  $('#formulario_facturacion').attr({ 'data-form': 'save' });
  $('#formulario_facturacion').attr({ 'action': '<?php echo SERVERURL; ?>php/facturacion/addPreFactura.php' });
  $("#formulario_facturacion").submit();
});

$('#formulario_facturacion #cobrar').on('click', function(e){
  $('#formulario_facturacion').attr({ 'data-form': 'save' });
  $('#formulario_facturacion').attr({ 'action': '<?php echo SERVERURL; ?>php/facturacion/addFactura.php' });
  $("#formulario_facturacion").submit();
});

$('#acciones_atras').on('click', function(e){
  e.preventDefault();
  volver()
});

$('#registrar_productos').on('click', function(e){
  e.preventDefault();
  agregarProductos()
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
}

function getFacturaEmision(muestras_id){
  var url = '<?php echo SERVERURL; ?>php/muestras/getFacturaEmision.php';
  var disponible;
  $.ajax({ type:'POST', url:url, data:'muestras_id='+muestras_id, async: false, success:function(data){ disponible = data; } });
  return disponible;
}

function getEstadoFactura(muestras_id){
  var url = '<?php echo SERVERURL; ?>php/muestras/getEstadoFactura.php';
  var disponible;
  $.ajax({ type:'POST', url:url, data:'muestras_id='+muestras_id, async: false, success:function(data){ disponible = data; } });
  return disponible;
}

function modalCreateBill(muestras_id, producto, nombre_producto, precio_venta, isv){
  if($('#form_main_admision_muestras #estado').val() == 0){
    if(getEstadoFactura(muestras_id) === ""){
      createBill(muestras_id, producto, nombre_producto, precio_venta, isv);
    }else{
      showNotify("error", "Error", "Lo sentimos esta factura ya ha sido emitida, por favor diríjase al módulo de facturación y realice le cobro de esta.");
    }
  }else if($('#form_main_admision_muestras #estado').val() == 1){
    if(getEstadoFactura(muestras_id) == ""){
      createBill(muestras_id, producto, nombre_producto, precio_venta, isv);
    }else{
      showNotify("error", "Error", "Lo sentimos esta factura ya ha sido emitida, por favor diríjase al reporte de facturación para buscarla, puede usar el numero de muestra como referencia.");
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
      var url = '<?php echo SERVERURL; ?>php/muestras/editarFacturasMuestras.php';
      $.ajax({
        type:'POST',
        url:url,
        data:'muestras_id='+muestras_id+'&producto='+producto,
        success: function(valores){
          var datos = eval(valores);
          $('#formulario_facturacion #fact_eval').val(0);
          $('#formulario_facturacion #muestras_id').val(muestras_id);
          $('#formulario_facturacion #pacientes_id').val(datos[0]);
          $('#formulario_facturacion #cliente_nombre').val(datos[1]);
          $('#formulario_facturacion #fecha').val(getFechaActual());
          $('#formulario_facturacion #colaborador_id').val(datos[3]);
          $('#formulario_facturacion #colaborador_nombre').val(datos[4]);
          $('#formulario_facturacion #servicio_id').val(datos[5]); 
          $('#formulario_facturacion #servicio_id').selectpicker('refresh');
          //safeRefresh($('#formulario_facturacion #servicio_id'));
          $('#formulario_facturacion #material_enviado_muestra').val(datos[6]);
          $('#formulario_facturacion #paciente_muestra_codigo').val(datos[7]);
          $('#formulario_facturacion #paciente_muestra').val(datos[8]);
          $('#formulario_facturacion #muestras_numero').val(datos[9]);

          $('#formulario_facturacion #fecha').attr("readonly", true);
          $('#formulario_facturacion #validar').attr("disabled", false);
          $('#formulario_facturacion #addRows').attr("disabled", false);
          $('#formulario_facturacion #removeRows').attr("disabled", false);

          $('#cobrar').hide();

          if(muestra === "Muestra"){
            $('.counter-container').hide();
          }

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

          limpiarTabla();

          $('#formulario_facturacion #invoiceItem #productoID_0').val(producto);
          $('#formulario_facturacion #invoiceItem #productName_0').val(nombre_producto);
          $('#formulario_facturacion #invoiceItem #quantity_0').val(1);
          $('#formulario_facturacion #invoiceItem #discount_0').val(0);
          $('#formulario_facturacion #invoiceItem #price_0').val(precio_venta);
          $('#formulario_facturacion #invoiceItem #total_0').val(precio_venta);

          if(muestra === "Muestra"){
            $('#facturas-counter').hide();
          }

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
      showNotify("error", "Error", "Lo sentimos esta factura ya ha sido generada, por favor diríjase al módulo de facturación y realice le cobro de esta");
    }
  }else{
    showNotify("error", "Acceso Denegado", "No tiene permisos para ejecutar esta acción");
  }
}

// ===== Modal Pagos =====
function pago(facturas_id){
  var url = '<?php echo SERVERURL; ?>php/facturacion/editarPago.php';

  $.ajax({
    type:'POST',
    url:url,
    data:'facturas_id='+facturas_id,
    success: function(valores){
      var datos = eval(valores);
      $('#formEfectivoBill .border-right a:eq(0) a').tab('show');
      $("#customer-name-bill").html("<b>Cliente:</b> " + datos[0]);
      $("#customer_bill_pay").val(datos[2]);
      $('#bill-pay').html("L. " + parseFloat(datos[2]).toFixed(2));

      // EFECTIVO
      $('#formEfectivoBill')[0].reset();
      $('#formEfectivoBill #monto_efectivo').val(datos[2]);
      $('#formEfectivoBill #factura_id_efectivo').val(facturas_id);
      $('#formEfectivoBill #pago_efectivo').attr('disabled', true);

      // TARJETA
      $('#formTarjetaBill')[0].reset();
      $('#formTarjetaBill #monto_efectivo').val(datos[2]);
      $('#formTarjetaBill #factura_id_tarjeta').val(facturas_id);

      // TRANSFERENCIA
      $('#formTransferenciaBill')[0].reset();
      $('#formTransferenciaBill #monto_efectivo').val(datos[2]);
      $('#formTransferenciaBill #factura_id_transferencia').val(facturas_id);

      // MIXTO
      $('#formMixtoBill')[0].reset();
      $('#formMixtoBill #monto_efectivo_mixto').val(datos[2]);
      $('#formMixtoBill #factura_id_mixto').val(facturas_id);
      $('#formMixtoBill #pago_efectivo_mixto').attr('disabled', true);

      // CHEQUES
      $('#formChequeBill')[0].reset();
      $('#formChequeBill #monto_efectivo').val(datos[2]);
      $('#formChequeBill #factura_id_cheque').val(facturas_id);

      $('#modal_pagos').modal({ show:true, keyboard: false, backdrop:'static' });
      return false;
    }
  });
}

$(document).ready(function(){
  $("#tab1").on("click", function(){
    $("#modal_pagos").on('shown.bs.modal', function(){
      $(this).find('#formTarjetaBill #efectivo_bill').focus();
    });
  });

  $("#tab2").on("click", function(){
    $("#modal_pagos").on('shown.bs.modal', function(){
      $(this).find('#formTarjetaBill #cr_bill').focus();
    });
  });

  $("#tab3").on("click", function(){
    $("#modal_pagos").on('shown.bs.modal', function(){
      $(this).find('#formTarjetaBill #bk_nm').focus();
    });
  });

  $("#tab4").on("click", function(){
    $("#modal_pagos").on('shown.bs.modal', function(){
      $(this).find('#formChequeBill #bk_nm_chk').focus();
    });
  });

  $("#tab5").on("click", function(){
    $("#modal_pagos").on('shown.bs.modal', function(){
      $(this).find('#formMixtoBill #efectivo_bill_mixto').focus();
    });
  });
});

// Inputmasks
$(document).ready(function(){
  $('#formMixtoPurchaseBill #cr_bill_mixtoPurchase').inputmask("9999");
});
$(document).ready(function(){
  $('#formMixtoPurchaseBill #exp_mixtoPurchase').inputmask("99/99");
});
$(document).ready(function(){
  $('#formMixtoPurchaseBill #cvcpwd_mixtoPurchase').inputmask("999999");
});
$(document).ready(function(){
  $('#formTarjetaBill #cr_bill').inputmask("9999");
});
$(document).ready(function(){
  $('#formTarjetaBill #exp').inputmask("99/99");
});
$(document).ready(function(){
  $('#formTarjetaBill #cvcpwd').inputmask("999999");
});

// Cálculos de efectivo y mixto
$(document).ready(function(){
  $("#formEfectivoBill #efectivo_bill").on("keyup", function(){
    var efectivo = parseFloat($("#formEfectivoBill #efectivo_bill").val() || 0).toFixed(2);
    var monto = parseFloat($("#formEfectivoBill #monto_efectivo").val() || 0).toFixed(2);
    var total = efectivo - monto;

    if(Math.floor(efectivo*100) >= Math.floor(monto*100)){
      $('#formEfectivoBill #cambio_efectivo').val(parseFloat(total).toFixed(2));
      $('#formEfectivoBill #pago_efectivo').attr('disabled', false);
    }else{
      $('#formEfectivoBill #cambio_efectivo').val(parseFloat(0).toFixed(2));
      $('#formEfectivoBill #pago_efectivo').attr('disabled', true);
    }
  });

  $("#formMixtoBill #efectivo_bill_mixto").on("keyup", function(){
    var efectivo = parseFloat($("#formMixtoBill #efectivo_bill_mixto").val() || 0).toFixed(2);
    var monto = parseFloat($("#formMixtoBill #monto_efectivo_mixto").val() || 0).toFixed(2);
    var total = efectivo - monto;

    if(Math.floor(efectivo*100) >= Math.floor(monto*100)){
      $('#formMixtoBill #pago_efectivo_mixto').attr('disabled', true);
      $('#formMixtoBill #monto_tarjeta').val(parseFloat(0).toFixed(2));
      $('#formMixtoBill #monto_tarjeta').attr('disabled', true);
    }else{
      var tarjeta = monto - efectivo;
      $('#formMixtoBill #monto_tarjeta').val(parseFloat(tarjeta).toFixed(2))
      $('#formMixtoBill #cambio_efectivo_mixto').val(parseFloat(0).toFixed(2));
      $('#formMixtoBill #pago_efectivo_mixto').attr('disabled', false);
    }
  });
});

// Imprimir
function printBill(facturas_id){
  var url = '<?php echo SERVERURL; ?>php/facturacion/generaFactura.php?facturas_id='+facturas_id;
  window.open(url);
}
function printBillGroup(facturas_id){
  var url = '<?php echo SERVERURL; ?>php/facturacion/generaFacturaGrupal.php?facturas_id='+facturas_id;
  window.open(url);
}

// ===== Lanzar init cuando todo (assets/plugins) terminó de cargar =====
$(window).on('load', initPage);
</script>
