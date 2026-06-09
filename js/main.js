/*
############################################################################################################################################################################################################################################################################################################################################################################################################################################################################################################################################################################################################################################################
*/

// =======================================================
// FUNCIONES DE APOYO PARA FORMULARIO AJAX
// =======================================================

function notifyFormularioAjax(tipo, titulo, mensaje) {
  if (typeof showNotify === "function") {
    showNotify(tipo, titulo, mensaje);
    return;
  }

  if (typeof swal === "function") {
    var icono = "info";

    if (tipo === "success") {
      icono = "success";
    } else if (tipo === "error" || tipo === "danger") {
      icono = "error";
    } else if (tipo === "warning") {
      icono = "warning";
    }

    swal({
      title: titulo,
      text: mensaje,
      icon: icono,
      closeOnEsc: false,
      closeOnClickOutside: false,
    });

    return;
  }

  alert(titulo + "\n\n" + mensaje);
}

function convertirNumeroFormularioAjax(valor) {
  if (valor === undefined || valor === null) {
    return 0;
  }

  valor = valor.toString().trim();

  if (valor === "") {
    return 0;
  }

  valor = valor.replace(/,/g, "");

  var numero = parseFloat(valor);

  if (isNaN(numero)) {
    return 0;
  }

  return numero;
}

// =======================================================
// VALIDACIÓN ESPECIAL PARA FACTURA GRUPAL
// =======================================================

function validarFacturaGrupalAntesDeEnviar() {
  var errores = [];

  var clienteIDGrupo = $("#formGrupoFacturacion #clienteIDGrupo").val();
  var clienteNombreGrupo = $("#formGrupoFacturacion #clienteNombreGrupo").val();
  var colaboradorIDGrupo = $(
    "#formGrupoFacturacion #colaborador_idGrupo",
  ).val();
  var colaboradorNombreGrupo = $(
    "#formGrupoFacturacion #colaborador_nombreGrupo",
  ).val();
  var servicioIDGrupo = $("#formGrupoFacturacion #servicio_idGrupo").val();
  var tamano = convertirNumeroFormularioAjax(
    $("#formGrupoFacturacion #tamano").val(),
  );

  clienteIDGrupo =
    clienteIDGrupo === undefined || clienteIDGrupo === null
      ? ""
      : clienteIDGrupo.toString().trim();
  clienteNombreGrupo =
    clienteNombreGrupo === undefined || clienteNombreGrupo === null
      ? ""
      : clienteNombreGrupo.toString().trim();
  colaboradorIDGrupo =
    colaboradorIDGrupo === undefined || colaboradorIDGrupo === null
      ? ""
      : colaboradorIDGrupo.toString().trim();
  colaboradorNombreGrupo =
    colaboradorNombreGrupo === undefined || colaboradorNombreGrupo === null
      ? ""
      : colaboradorNombreGrupo.toString().trim();
  servicioIDGrupo =
    servicioIDGrupo === undefined || servicioIDGrupo === null
      ? ""
      : servicioIDGrupo.toString().trim();

  if (clienteIDGrupo === "") {
    errores.push("Debe seleccionar la empresa/cliente para la factura grupal.");
  }

  if (clienteNombreGrupo === "") {
    errores.push("El nombre de la empresa/cliente no puede quedar vacío.");
  }

  if (colaboradorIDGrupo === "") {
    errores.push("Debe seleccionar el profesional para la factura grupal.");
  }

  if (colaboradorNombreGrupo === "") {
    errores.push("El nombre del profesional no puede quedar vacío.");
  }

  if (servicioIDGrupo === "") {
    errores.push("Debe seleccionar el servicio para la factura grupal.");
  }

  if (tamano <= 0) {
    errores.push(
      "Debe seleccionar al menos una factura para generar la factura grupal.",
    );
  }

  var facturas = [];
  var facturasDuplicadas = {};
  var totalImporte = 0;
  var totalISV = 0;
  var totalDescuento = 0;
  var totalLineasValidas = 0;

  $("#formGrupoFacturacion #invoiceItemGrupo tbody tr").each(function (index) {
    var linea = index + 1;

    var billGrupoID = $(this).find("input[name='billGrupoID[]']").val();
    var pacienteIDBillGrupo = $(this)
      .find("input[name='pacienteIDBillGrupo[]']")
      .val();
    var billGrupoMuestraID = $(this)
      .find("input[name='billGrupoMuestraID[]']")
      .val();
    var quantyGrupoQuantity = $(this)
      .find("input[name='quantyGrupoQuantity[]']")
      .val();

    var importeBillGrupo = $(this)
      .find("input[name='importeBillGrupo[]']")
      .val();
    var discountBillGrupo = $(this)
      .find("input[name='discountBillGrupo[]']")
      .val();
    var totalBillGrupo = $(this).find("input[name='totalBillGrupo[]']").val();

    var billGrupoISV = $(this).find("input[name='billGrupoISV[]']").val();

    billGrupoID =
      billGrupoID === undefined || billGrupoID === null
        ? ""
        : billGrupoID.toString().trim();
    pacienteIDBillGrupo =
      pacienteIDBillGrupo === undefined || pacienteIDBillGrupo === null
        ? ""
        : pacienteIDBillGrupo.toString().trim();
    billGrupoMuestraID =
      billGrupoMuestraID === undefined || billGrupoMuestraID === null
        ? ""
        : billGrupoMuestraID.toString().trim();

    var filaVacia =
      billGrupoID === "" &&
      pacienteIDBillGrupo === "" &&
      convertirNumeroFormularioAjax(importeBillGrupo) === 0 &&
      convertirNumeroFormularioAjax(totalBillGrupo) === 0;

    if (filaVacia) {
      return true;
    }

    if (billGrupoID === "") {
      errores.push(
        "La línea " + linea + " no tiene el código interno de la factura.",
      );
      return true;
    }

    if (pacienteIDBillGrupo === "") {
      errores.push(
        "La línea " + linea + " no tiene el código interno del paciente.",
      );
      return true;
    }

    if (facturasDuplicadas[billGrupoID]) {
      errores.push(
        "La factura ID " +
          billGrupoID +
          " está duplicada en el detalle grupal.",
      );
      return true;
    }

    facturasDuplicadas[billGrupoID] = true;

    var cantidad = convertirNumeroFormularioAjax(quantyGrupoQuantity);
    var importe = convertirNumeroFormularioAjax(importeBillGrupo);
    var descuento = convertirNumeroFormularioAjax(discountBillGrupo);
    var isv = convertirNumeroFormularioAjax(billGrupoISV);
    var totalLinea = convertirNumeroFormularioAjax(totalBillGrupo);

    if (cantidad <= 0) {
      cantidad = 1;
    }

    if (importe <= 0) {
      errores.push("La factura ID " + billGrupoID + " tiene importe inválido.");
      return true;
    }

    if (descuento < 0) {
      errores.push(
        "La factura ID " + billGrupoID + " tiene descuento negativo.",
      );
      return true;
    }

    if (descuento > importe) {
      errores.push(
        "La factura ID " +
          billGrupoID +
          " tiene un descuento mayor al importe.",
      );
      return true;
    }

    var totalCalculadoLinea = importe + isv - descuento;

    if (totalCalculadoLinea <= 0) {
      errores.push("La factura ID " + billGrupoID + " tiene total inválido.");
      return true;
    }

    if (totalLinea > 0) {
      var diferenciaLinea = Math.abs(totalLinea - totalCalculadoLinea);

      if (diferenciaLinea > 0.01) {
        errores.push(
          "La factura ID " +
            billGrupoID +
            " no cuadra en pantalla. Total visible: L. " +
            totalLinea.toFixed(2) +
            " | Total calculado: L. " +
            totalCalculadoLinea.toFixed(2),
        );
        return true;
      }
    }

    totalImporte += importe;
    totalISV += isv;
    totalDescuento += descuento;
    totalLineasValidas++;

    facturas.push({
      facturas_id: billGrupoID,
      pacientes_id: pacienteIDBillGrupo,
      muestras_id: billGrupoMuestraID,
      cantidad: cantidad,
      importe: importe,
      isv: isv,
      descuento: descuento,
      total: totalCalculadoLinea,
    });
  });

  if (totalLineasValidas <= 0) {
    errores.push("Debe agregar al menos una factura válida al detalle grupal.");
  }

  if (tamano > 0 && totalLineasValidas !== tamano) {
    errores.push(
      "El tamaño del detalle grupal no coincide. Tamaño esperado: " +
        tamano +
        " | Líneas válidas: " +
        totalLineasValidas +
        ".",
    );
  }

  var totalCalculado = totalImporte + totalISV - totalDescuento;

  if (totalCalculado <= 0) {
    errores.push("El total de la factura grupal debe ser mayor a cero.");
  }

  var totalPantalla = convertirNumeroFormularioAjax(
    $("#formGrupoFacturacion #totalAftertaxBillGrupo").val(),
  );

  if (totalPantalla > 0) {
    var diferencia = Math.abs(totalPantalla - totalCalculado);

    if (diferencia > 0.01) {
      errores.push(
        "El total grupal no cuadra. Total en pantalla: L. " +
          totalPantalla.toFixed(2) +
          " | Total calculado: L. " +
          totalCalculado.toFixed(2),
      );
    }
  }

  if (errores.length > 0) {
    notifyFormularioAjax(
      "error",
      "No se puede generar la factura grupal",
      errores.join("\n"),
    );

    return false;
  }

  return true;
}

// =======================================================
// VALIDACIÓN ESPECIAL CENTRALIZADA
// =======================================================

function validarFormularioAjaxEspecial(form) {
  var formId = form.attr("id");

  if (formId === "formulario_facturacion") {
    if (typeof validarFacturaAntesDeEnviar === "function") {
      return validarFacturaAntesDeEnviar();
    }

    notifyFormularioAjax(
      "error",
      "Validación no disponible",
      "No se encontró la función validarFacturaAntesDeEnviar(). Revise que el JS de facturación esté cargado correctamente.",
    );

    return false;
  }

  if (formId === "formGrupoFacturacion") {
    return validarFacturaGrupalAntesDeEnviar();
  }

  return true;
}

// =======================================================
// FORMULARIO AJAX GLOBAL
// =======================================================

$(".FormularioAjax").submit(function (e) {
  e.preventDefault();

  var form = $(this);

  var tipo = form.attr("data-form");
  var action = form.attr("action");
  var method = form.attr("method");
  var respuesta = form.children(".RespuestaAjax");

  if (!method || method === "") {
    method = "POST";
  }

  if (!action || action === "") {
    notifyFormularioAjax(
      "error",
      "Error",
      "El formulario no tiene definida la ruta de procesamiento.",
    );

    return false;
  }

  if (!validarFormularioAjaxEspecial(form)) {
    form.find('button[type="submit"]').prop("disabled", false);
    return false;
  }

  form.find('button[type="submit"]').prop("disabled", true);

  var msjError = "<script></script>";
  var formdata = new FormData(this);

  var textoAlerta;
  var type;

  if (tipo == "save") {
    textoAlerta = "Los datos que enviarás quedarán almacenados en el sistema";
    type = "info";
  } else if (tipo == "delete") {
    textoAlerta = "Los datos serán eliminados completamente del sistema";
    type = "warning";
  } else if (tipo == "update") {
    textoAlerta = "Los datos del sistema serán actualizados";
    type = "info";
  } else {
    textoAlerta = "¿Quieres realizar la operación solicitada?";
    type = "warning";
  }

  swal({
    title: "¿Estás seguro?",
    text: textoAlerta,
    icon: type,
    buttons: {
      cancel: {
        text: "Cancelar",
        visible: true,
        closeModal: true,
      },
      confirm: {
        text: "Aceptar",
        closeModal: false,
      },
    },
    dangerMode: false,
    closeOnEsc: false,
    closeOnClickOutside: false,
  }).then(function (isConfirm) {
    if (isConfirm) {
      $.ajax({
        type: method,
        url: action,
        data: formdata,
        cache: false,
        contentType: false,
        processData: false,
        xhr: function () {
          var xhr = new window.XMLHttpRequest();

          xhr.upload.addEventListener(
            "progress",
            function (evt) {
              if (evt.lengthComputable) {
                var percentComplete = evt.loaded / evt.total;
                percentComplete = parseInt(percentComplete * 100);

                if (percentComplete < 100) {
                  respuesta.html(
                    '<p class="text-center">Procesado... (' +
                      percentComplete +
                      "%)</p>" +
                      '<div class="progress progress-striped active">' +
                      '<div class="progress-bar progress-bar-info" style="width: ' +
                      percentComplete +
                      '%;"></div>' +
                      "</div>",
                  );
                } else {
                  respuesta.html('<p class="text-center"></p>');
                }
              }
            },
            false,
          );

          return xhr;
        },
        success: function (data) {
          var datos;

          swal.close();

          try {
            if (typeof data === "object") {
              datos = data;
            } else {
              datos = JSON.parse(data);
            }
          } catch (errorJson) {
            try {
              datos = eval(data);
            } catch (errorEval) {
              console.error("Respuesta inválida del servidor:", data);
              console.error("Error JSON:", errorJson);
              console.error("Error eval:", errorEval);

              form.find('button[type="submit"]').prop("disabled", false);

              notifyFormularioAjax(
                "error",
                "Error",
                "El servidor devolvió una respuesta inválida. Revise si hay errores PHP, HTML o warnings antes del JSON.",
              );

              return false;
            }
          }

          if (!Array.isArray(datos)) {
            console.error("La respuesta no es un array:", datos);

            form.find('button[type="submit"]').prop("disabled", false);

            notifyFormularioAjax(
              "error",
              "Error",
              "La respuesta del servidor no tiene el formato esperado.",
            );

            return false;
          }

          var modalId = datos[7];

          if (modalId) {
            var modal = document.getElementById(modalId);

            if (modal) {
              $(modal).modal("hide");

              var bootstrapModal = $(modal).data("bs.modal");

              if (bootstrapModal) {
                bootstrapModal.hide();
              } else {
                $(modal).modal("hide");
              }
            }
          }

          if (datos[0] == "Error") {
            notifyFormularioAjax(datos[2], datos[0], datos[1]);
            form.find('button[type="submit"]').prop("disabled", false);
            return false;
          } else if (datos[0] == "Guardar") {
            notifyFormularioAjax(datos[2], datos[0], datos[1]);
          } else {
            notifyFormularioAjax(datos[2], datos[0], datos[1]);
          }

          if (datos[4] != "") {
            if ($("#" + datos[4]).length > 0) {
              $("#" + datos[4])[0].reset();
              $("#" + datos[4] + " #pro").val(datos[5]);
            }
          }

          if (typeof llenarTabla === "function") {
            llenarTabla(datos[6]);
          }

          if (datos[6] == "formEmpresas") {
            pagination(1);
            getEmpresa();
          }

          if (datos[6] == "formPacientesAdmisionEditar") {
            getGenero();
            pagination(1);
          }

          if (datos[6] == "formPacientesAdmision") {
            getGenero();
            getTipo();
            getTipoMuestra();
            getEmpresa();
            getRemitente();
            getHospitales();
            getCategorias();
            getServicio();
            getClientes();
            pagination(1);
            $("#formulario_admision #producto").html("");
            $("#formulario_admision #producto").selectpicker("refresh");
          }

          if (datos[6] == "AtencionMedica") {
            printReport(datos[8]);

            setTimeout(function () {
              sendMailAtencion(datos[8]);
            }, 5000);
          }

          if (datos[6] == "Adendum") {
            printReport(datos[8]);
          }

          if (datos[6] == "Facturacion") {
            pago(datos[8]);
          }

          if (datos[6] == "FacturacionCredito") {
            printBill(datos[8]);
          }

          if (datos[6] == "facturacionGrupal") {
            pagoGrupal(datos[8]);
          }

          if (datos[6] == "facturacionGrupalCredito") {
            printBillGroup(datos[9]);
          }

          if (datos[6] == "Pagos") {
            printBill(datos[8]);
            limpiarTabla();
            pagination(1);
            volver();

            setTimeout(function () {
              sendMail(datos[8]);
            }, 5000);

            $("#" + datos[7]).modal("hide");
          }

          if (datos[6] == "PagosGrupal") {
            printBillGroup(datos[9]);
            limpiarTabla();
            pagination(1);
            volver();
            listar_cuentas_por_cobrar_clientes();

            setTimeout(function () {
              sendMail(datos[8]);
            }, 5000);

            $("#" + datos[7]).modal("hide");
          }

          if (datos[6] == "PagosGrupalCredito") {
            printBillGroup(datos[9]);
            limpiarTabla();
            pagination(1);
            volver();
            listar_cuentas_por_cobrar_clientes();

            setTimeout(function () {
              sendMail(datos[8]);
            }, 5000);

            $("#" + datos[7]).modal("hide");
            listar_cuentas_por_cobrar_clientes();
          }

          if (datos[6] == "PagosCredito") {
            printBill(datos[8]);
            limpiarTabla();
            pagination(1);
            volver();
            $("#" + datos[7]).modal("hide");
            listar_cuentas_por_cobrar_clientes();
          }

          if (datos[6] == "PagosCXC") {
            printBill(datos[8]);
            limpiarTabla();
            pagination(1);
            volver();
            $("#" + datos[7]).modal("hide");
            listar_cuentas_por_cobrar_clientes();
          }

          if (datos[6] == "PagosCXCGrupal") {
            printBillGroup(datos[9]);
            limpiarTabla();
            pagination(1);
            volver();

            setTimeout(function () {
              sendMail(datos[8]);
            }, 5000);

            $("#" + datos[7]).modal("hide");
            listar_cuentas_por_cobrar_clientes();
          }

          if (datos[6] == "Muestras") {
            createBill(datos[8]);
          }

          if (datos[6] == "formPacientesAdmision") {
            createBill(
              datos[10],
              datos[11],
              datos[12],
              datos[13],
              datos[14],
              datos[15],
            );
          }

          if (datos[9] == "Eliminar") {
            $("#" + datos[7]).modal("hide");
          }

          if (datos[10] == "Guardar") {
            $("#" + datos[7]).modal("hide");
          }

          form.find('button[type="submit"]').prop("disabled", false);
        },
        error: function (xhr, status, error) {
          respuesta.html(msjError);
          swal.close();

          form.find('button[type="submit"]').prop("disabled", false);

          console.error("Error AJAX:", {
            status: status,
            error: error,
            responseText: xhr.responseText,
          });

          notifyFormularioAjax(
            "error",
            "Error de conexión",
            "No se pudo procesar la solicitud. Verifique su conexión o intente nuevamente.",
          );
        },
      });
    } else {
      form.find('button[type="submit"]').prop("disabled", false);
      swal.close();
    }
  });
});

const notyf = new Notyf({
  position: {
    x: "right",
    y: "top",
  },
  dismissible: true, // Permite cerrar manualmente
  closeOnClick: true, // Cierra al hacer clic
  types: [
    {
      type: "warning",
      background: "orange",
      duration: 5000, // 5 segundos
      icon: {
        className: "fas fa-exclamation-triangle fa-lg",
        tagName: "i",
        color: "white",
      },
      closeIcon: {
        className: "fas fa-times",
        color: "white",
        tagName: "span",
        position: "right",
        style: "margin-right: 15px;",
      },
    },
    {
      type: "error",
      background: "indianred",
      duration: 10000, // 10 segundos (más tiempo para errores)
      dismissible: true,
      icon: {
        className: "fas fa-times-circle fa-lg",
        tagName: "i",
        color: "white",
      },
      closeIcon: {
        className: "fas fa-times",
        color: "white",
        tagName: "span",
        position: "right",
      },
    },
    {
      type: "info",
      background: "#1e88e5",
      duration: 5000, // 5 segundos
      dismissible: true,
      icon: {
        className: "fas fa-info-circle fa-lg",
        tagName: "i",
        color: "white",
      },
      closeIcon: {
        className: "fas fa-times",
        color: "white",
        tagName: "span",
        position: "right",
      },
    },
    {
      type: "success",
      background: "#4caf50",
      duration: 5000, // 5 segundos
      dismissible: true,
      icon: {
        className: "fas fa-check-circle fa-lg",
        tagName: "i",
        color: "white",
      },
      closeIcon: {
        className: "fas fa-times",
        color: "white",
        tagName: "span",
        position: "right",
      },
    },
    {
      type: "loading",
      background: "#3498db", // Azul profesional
      duration: 5000, //5 segundos
      icon: {
        className: "fas fa-circle-notch fa-spin", // Icono giratorio
        tagName: "i",
        color: "white",
      },
      dismissible: false, // No se puede cerrar manualmente
      closeIcon: false, // Sin botón de cerrar
    },
  ],
});

// Variable para controlar la notificación de loading
let loadingNotification = null;

/**
 * Muestra un indicador de carga con Notyf
 * @param {string} [message='Procesando solicitud'] - Mensaje a mostrar
 */
function showLoading(message = "Procesando solicitud") {
  // Cierra cualquier notificación de carga previa
  if (loadingNotification) {
    loadingNotification.dismiss();
  }

  loadingNotification = notyf.open({
    type: "info",
    message: `<strong>Procesando</strong><br>${message}`, // Usar backticks (`) para template literal
    duration: 0, // Notificación persistente
    dismissible: false,
    icon: {
      className: "fas fa-spinner fa-spin fa-xl", // Icono con animación de giro
      tagName: "i",
      color: "white",
    },
    settings: {
      allowHtml: true,
    },
  });
}

function hideLoading() {
  if (loadingNotification) {
    notyf.dismiss(loadingNotification);
    loadingNotification = null;
  }
}

/**
 * Muestra una notificación estilizada al usuario.
 * @param {string} title - El título de la notificación (ej: 'Éxito', 'Error', 'Advertencia')
 * @param {string} message - El mensaje detallado a mostrar (ej: 'Los datos se guardaron correctamente')
 * @param {'success'|'error'|'warning'|'info'} type - Tipo de notificación (valores válidos: 'success', 'error', 'warning', 'info')
 * @example
 * // Muestra una notificación de éxito
 * showNotify('Éxito', 'Los datos se guardaron correctamente', 'success');
 * @example
 * // Muestra una notificación de error
 * showNotify('Error', 'No se pudo conectar al servidor', 'error');
 */
function showNotify(type, title, message) {
  const validTypes = ["success", "error", "warning", "info", "loading"];

  if (validTypes.includes(type)) {
    notyf.open({
      type: type,
      message: `<strong>${title}</strong><br>${message}`,
      settings: {
        ripple: true,
        allowHtml: true,
      },
    });
  } else {
    console.error("Tipo de notificación no válido");
  }

  if (typeof swal !== "undefined" && typeof swal.close === "function") {
    swal.close();
  }
}

//INICIO BUSCAR DATOS EN TABLA
$(document).ready(function () {
  $("#formBuscarColaboradores #colaborador_id").on("keyup", function () {
    var value = $(this).val().toLowerCase();
    $("#myTable tr").filter(function () {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
  });
});

/*##########################################################################################################################################################################################################################################################################################################################*/
//INICIO IDIOMA
var idioma_español = {
  processing: "Procesando...",
  lengthMenu: "Mostrar _MENU_ registros",
  zeroRecords: "No se encontraron resultados",
  emptyTable: "Ningún dato disponible en esta tabla",
  info: "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
  infoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros",
  infoFiltered: "(filtrado de un total de _MAX_ registros)",
  search: "Buscar:",
  infoThousands: ",",
  loadingRecords: "Cargando...",
  paginate: {
    first: "Primero",
    last: "Último",
    next: "Siguiente",
    previous: "Anterior",
  },
  aria: {
    sortAscending: ": Activar para ordenar la columna de manera ascendente",
    sortDescending: ": Activar para ordenar la columna de manera descendente",
  },
  buttons: {
    copy: "Copiar",
    colvis: "Visibilidad",
    collection: "Colección",
    colvisRestore: "Restaurar visibilidad",
    copyKeys:
      "Presione ctrl o u2318 + C para copiar los datos de la tabla al portapapeles del sistema. <br \/> <br \/> Para cancelar, haga clic en este mensaje o presione escape.",
    copySuccess: {
      1: "Copiada 1 fila al portapapeles",
      _: "Copiadas %d fila al portapapeles",
    },
    copyTitle: "Copiar al portapapeles",
    csv: "CSV",
    excel: "Excel",
    pageLength: {
      "-1": "Mostrar todas las filas",
      1: "Mostrar 1 fila",
      _: "Mostrar %d filas",
    },
    pdf: "PDF",
    print: "Imprimir",
  },
  autoFill: {
    cancel: "Cancelar",
    fill: "Rellene todas las celdas con <i>%d<\/i>",
    fillHorizontal: "Rellenar celdas horizontalmente",
    fillVertical: "Rellenar celdas verticalmentemente",
  },
  decimal: ",",
  searchBuilder: {
    add: "Añadir condición",
    button: {
      0: "Constructor de búsqueda",
      _: "Constructor de búsqueda (%d)",
    },
    clearAll: "Borrar todo",
    condition: "Condición",
    conditions: {
      date: {
        after: "Despues",
        before: "Antes",
        between: "Entre",
        empty: "Vacío",
        equals: "Igual a",
        not: "No",
        notBetween: "No entre",
        notEmpty: "No Vacio",
      },
      moment: {
        after: "Despues",
        before: "Antes",
        between: "Entre",
        empty: "Vacío",
        equals: "Igual a",
        not: "No",
        notBetween: "No entre",
        notEmpty: "No vacio",
      },
      number: {
        between: "Entre",
        empty: "Vacio",
        equals: "Igual a",
        gt: "Mayor a",
        gte: "Mayor o igual a",
        lt: "Menor que",
        lte: "Menor o igual que",
        not: "No",
        notBetween: "No entre",
        notEmpty: "No vacío",
      },
      string: {
        contains: "Contiene",
        empty: "Vacío",
        endsWith: "Termina en",
        equals: "Igual a",
        not: "No",
        notEmpty: "No Vacio",
        startsWith: "Empieza con",
      },
    },
    data: "Data",
    deleteTitle: "Eliminar regla de filtrado",
    leftTitle: "Criterios anulados",
    logicAnd: "Y",
    logicOr: "O",
    rightTitle: "Criterios de sangría",
    title: {
      0: "Constructor de búsqueda",
      _: "Constructor de búsqueda (%d)",
    },
    value: "Valor",
  },
  searchPanes: {
    clearMessage: "Borrar todo",
    collapse: {
      0: "Paneles de búsqueda",
      _: "Paneles de búsqueda (%d)",
    },
    count: "{total}",
    countFiltered: "{shown} ({total})",
    emptyPanes: "Sin paneles de búsqueda",
    loadMessage: "Cargando paneles de búsqueda",
    title: "Filtros Activos - %d",
  },
  select: {
    1: "%d fila seleccionada",
    _: "%d filas seleccionadas",
    cells: {
      1: "1 celda seleccionada",
      _: "$d celdas seleccionadas",
    },
    columns: {
      1: "1 columna seleccionada",
      _: "%d columnas seleccionadas",
    },
  },
  thousands: ".",
};
//FIN IDIOMA

//INICIO CONVETIR IMAGEN BASE 64
function toDataURL(src, callback, outputFormat) {
  var img = new Image();
  img.crossOrigin = "Anonymous";
  img.onload = function () {
    var canvas = document.createElement("CANVAS");
    var ctx = canvas.getContext("2d");
    var dataURL;
    canvas.height = this.naturalHeight;
    canvas.width = this.naturalWidth;
    ctx.drawImage(this, 0, 0);
    dataURL = canvas.toDataURL(outputFormat);
    callback(dataURL);
  };
  img.src = src;
  if (img.complete || img.complete === undefined) {
    img.src =
      "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==";
    img.src = src;
  }
}
//FIN CONVERTIR IMAGEN BASE 64

var imagen;
toDataURL("../img/logo.png", function (dataUrl) {
  imagen = dataUrl;
});

//INICIO DATATABLE OPCIONES
var lengthMenu = [
  [5, 10, 20, 50, 100, -1],
  [5, 10, 20, 50, 100, "Todos"],
];
var lengthMenu10 = [
  [10, 20, 50, 100, -1],
  [10, 20, 50, 100, "Todos"],
];
var lengthMenu20 = [
  [20, 50, 100, -1],
  [20, 50, 100, "Todos"],
];

var dom =
  "<'row'<'col-sm-3'l><'col-sm-6 text-center'B><'col-sm-3'f>>" +
  "<'row'<'col-sm-12'tr>>" +
  "<'row'<'col-sm-5'i><'col-sm-7'p>>";
//FIN DATATABLE OPCIONES
/*##########################################################################################################################################################################################################################################################################################################################*/

/*##########################################################################################################################################################################################################################################################################################################################*/

//LLENADO DE TABLAS
$("#invoice-form #notes").keyup(function () {
  var max_chars = 255;
  var chars = $(this).val().length;
  var diff = max_chars - chars;

  $("#invoice-form #charNum_notas").html(diff + " Caracteres");

  if (diff == 0) {
    return false;
  }
});

function llenarTabla(dato) {
  if (dato == "formPacientes") {
    pagination(1);
  }

  if (dato == "formPacientesAdmision") {
    pagination(1);
  }

  if (dato == "formCita") {
    pagination(1);
  }

  if (dato == "Almacen") {
    listar_almacen();
  }

  if (dato == "CategoriaMuestras") {
    pagination(1);
  }

  if (dato == "Preclinica") {
    pagination(1);
  }

  if (dato == "Colaboradores") {
    pagination(1);
    puesto();
    getJornadaColaborador();
    servicio();
  }

  if (dato == "Puestos") {
    pagination_puestos(1);
    puesto();
    getJornadaColaborador();
    servicio();
  }

  if (dato == "Servicios") {
    pagination_servicio(1);
  }

  if (dato == "servicioColaboradores") {
    pagination_jornada_colaboradores(1);
    puesto();
    getJornadaColaborador();
    servicio();
  }

  if (dato == "ReporteEnfermeria") {
    pagination_preclinica(1);
  }

  if (dato == "LimiteMuestras") {
    listar_limiteMuestras();
  }

  if (dato == "Ubicacion") {
    listar_ubicacion();
  }

  if (dato == "Almacen") {
    listar_almacen();
  }

  if (dato == "Productos") {
    listar_productos();
  }

  if (dato == "Hospitales") {
    listar_hospitales_consulta();
  }

  if (dato == "Facturacion") {
    funciones();
    limpiarTabla();
    cleanFooterValueBill();
    $(".footer").show();
    $(".footer1").hide();
  }

  if (dato == "facturacionGrupal") {
    funciones();
    limpiarTablaFacturaGrupo();
    cleanFooterValueBill();
    $(".footer").show();
    $(".footer1").hide();
  }

  if (dato == "FacturaAtenciones") {
    getServicio();
    pagination(1);
    limpiarTabla();
    volver();
    cleanFooterValueBill();
  }

  if (dato == "Usuarios") {
    pagination(1);
  }

  if (dato == "configuracionVarios") {
    pagination(1);
  }

  if (dato == "formProfesionales") {
    paginationPorfesionales(1);
  }

  if (dato == "Plantillas") {
    listar_plantillas_buscar();
  }

  if (dato == "Medidas") {
    listar_medidas();
  }

  if (dato == "AtencionMedica") {
    pagination(1);
  }

  if (
    dato == "Muestras" ||
    dato == "MuestrasEliminar" ||
    dato == "MuestrasModificar"
  ) {
    pagination(1);
    $(".footer").hide();
    $(".footer1").show();
  }

  if (dato == "AdministradorSecuencias") {
    pagination(1);
    getEstado();
    getEmpresa();
    getTipoMuestra();
  }

  if (dato == "Adendum") {
    getServicio();
    getProfesionales();
    pagination(1);
  }

  if (dato == "configuracionVariosemails") {
    pagination(1);
  }

  if (dato == "Movimientos") {
    listar_movimientos();
    agregarMovimientos();
    getCategoriaProductosMovimientos();
    getCategoriaProductos();
    getCategoriaOperacion();
    getProductos(1);
  }

  if (dato == "AdministradorPrecios") {
    listar_administrador_precios();
    getHospitales();
    getPrecios();
  }

  if (dato == "PagosCredito") {
    listar_cuentas_por_cobrar_clientes();
    pagination(1);
  }

  if (dato == "formEmpresas") {
    pagination(1);
  }

  if (dato == "formulario_admision_clientes_editar") {
    pagination(1);
  }
}

$(function () {
  $('[data-toggle="tooltip"]').tooltip({
    trigger: "hover",
  });
});

/*
//INICIO MENU FORM PAGOS FACTURAS
$(document).ready(function () {
	$(".menu-toggle2").hide();

	//Menu Toggle Script
	$("#menu-toggle1").click(function (e) {
		e.preventDefault();
		$("#wrapper").toggleClass("toggled");
	});

	$("#menu-toggle2").click(function (e) {
		e.preventDefault();
		$("#wrapper").toggleClass("toggled");
	});

	// For highlighting activated tabs
	$("#tab1").click(function () {
		$(".tabs").removeClass("active1");
		$(".tabs").addClass("bg-light");
		$("#tab1").addClass("active1");
		$("#tab1").removeClass("bg-light");
	});

	$("#tab2").click(function () {
		$(".tabs").removeClass("active1");
		$(".tabs").addClass("bg-light");
		$("#tab2").addClass("active1");
		$("#tab2").removeClass("bg-light");
	});

	$("#tab3").click(function () {
		$(".tabs").removeClass("active1");
		$(".tabs").addClass("bg-light");
		$("#tab3").addClass("active1");
		$("#tab3").removeClass("bg-light");
	});

	$("#tab4").click(function () {
		$(".tabs").removeClass("active1");
		$(".tabs").addClass("bg-light");
		$("#tab4").addClass("active1");
		$("#tab4").removeClass("bg-light");
	});
})
//FIN MENU FORM PAGOS FACTURAS

$(".menu-toggle1").on("click", function (e) {
	e.preventDefault();
	$(".menu-toggle1").hide();
	$(".menu-toggle2").show();
	$("#sidebar-wrapper").hide();
});

$(".menu-toggle2").on("click", function (e) {
	e.preventDefault();
	$(".menu-toggle2").hide();
	$(".menu-toggle1").show();
	$("#sidebar-wrapper").show();
});
*/
//FIN MENU FACTURAS
