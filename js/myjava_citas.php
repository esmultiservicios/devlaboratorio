<script>
console.clear();
console.log("myjava_citas.php CARGADO - FULLCALENDAR 3.10.2");

$(document).ready(function () {

    console.log("jQuery:", $.fn.jquery);
    console.log("Moment:", typeof moment);
    console.log("FullCalendar:", typeof $.fn.fullCalendar);

    if (typeof moment === 'undefined') {
        console.error("ERROR: Moment.js no está cargado.");
        return false;
    }

    if (typeof $.fn.fullCalendar !== 'function') {
        console.error("ERROR: FullCalendar no está cargado. Revisa que no exista bloqueo de CDN o jQuery duplicado.");
        return false;
    }

    $("#form-addevent #color").css("pointer-events", "none");
    $("#ModalEdit #color").css("pointer-events", "none");

    inicializarCalendarioCitas();
    getTipoMuestra();
    configurarEventosCitas();
    configurarFocusModalesCitas();
});

function inicializarCalendarioCitas() {
    if ($('#calendar').length === 0) {
        console.error("No existe el div #calendar en la vista.");
        return false;
    }

    $('#calendar').fullCalendar('destroy');

    $('#calendar').fullCalendar({
        locale: 'es',

        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },

        defaultView: 'agendaWeek',
        defaultDate: moment().format('YYYY-MM-DD'),

        height: 792,

        editable: true,
        eventLimit: true,
        selectable: true,
        selectHelper: true,
        displayEventTime: true,

        minTime: "07:00:00",
        maxTime: "23:59:59",
        slotDuration: "00:40:00",
        slotLabelInterval: "00:20:00",

        allDayText: 'Todo el día',

        buttonText: {
            today: 'Hoy',
            month: 'Mes',
            week: 'Semana',
            day: 'Día'
        },

        businessHours: {
            start: '08:00:00',
            end: '23:59:59',
            dow: [1, 2, 3, 4, 5, 6]
        },

        events: function (start, end, timezone, callback) {
            cargarEventosCalendario(callback);
        },

        select: function (start, end) {
            /*
            Aquí va tu lógica para agregar cita.
            */
        },

        eventRender: function (event, element) {
            element.off('dblclick').on('dblclick', function () {
                /*
                Aquí va tu lógica para editar cita.
                */
            });
        },

        eventDrop: function (event, delta, revertFunc) {
            /*
            Aquí va tu lógica para mover cita.
            */
        },

        eventResize: function (event, dayDelta, minuteDelta, revertFunc) {
            /*
            Aquí va tu lógica para cambiar duración de cita.
            */
        }
    });

    return true;
}

function cargarEventosCalendario(callback) {
    var tipo_muestra = $('#botones_citas #tipo_muestra').val();

    if (tipo_muestra === null || tipo_muestra === undefined) {
        tipo_muestra = '';
    }

    $.ajax({
        type: "POST",
        url: '<?php echo SERVERURL; ?>php/citas/getCalendar.php',
        dataType: "json",
        cache: false,
        data: {
            tipo_muestra: tipo_muestra
        },
        success: function (respuesta) {
            console.log("Respuesta getCalendar.php:", respuesta);

            if (respuesta === null || respuesta === undefined) {
                respuesta = [];
            }

            if (!Array.isArray(respuesta)) {
                console.error("getCalendar.php no devolvió un array JSON válido:", respuesta);
                respuesta = [];
            }

            callback(respuesta);
        },
        error: function (xhr, status, error) {
            console.error("Error AJAX en getCalendar.php");
            console.error("Status:", status);
            console.error("Error:", error);
            console.error("Respuesta servidor:", xhr.responseText);

            callback([]);
        }
    });
}

function configurarEventosCitas() {
    $('#botones_citas #tipo_muestra').off('change').on('change', function () {
        refrescarCalendarioCitas();
    });

    $('#botones_citas').off('submit').on('submit', function (e) {
        e.preventDefault();
        refrescarCalendarioCitas();
    });

    $('#ModalImprimir_enviar').off('click').on('click', function (e) {
        e.preventDefault();

        if ($('#fecha_citaedit').val() === "" || $('#fecha_citaeditend').val() === "") {
            if ($('#form-editevent').length > 0) {
                $('#form-editevent')[0].reset();
            }

            swal({
                title: "Error",
                text: "No se pueden enviar los datos, los campos están vacíos",
                icon: "error",
                dangerMode: true,
                closeOnEsc: false,
                closeOnClickOutside: false
            });

            return false;
        }

        reportePDF($('#form-editevent #id').val());
    });
}

function refrescarCalendarioCitas() {
    if ($('#calendar').length === 0) {
        return false;
    }

    if (typeof $.fn.fullCalendar !== 'function') {
        console.error("No se puede refrescar: FullCalendar no está disponible.");
        return false;
    }

    $('#calendar').fullCalendar('refetchEvents');

    return true;
}

function getTipoMuestra() {
    $.ajax({
        type: "POST",
        url: '<?php echo SERVERURL; ?>php/citas/getTipoMuestra.php',
        cache: false,
        success: function (data) {
            $('#botones_citas #tipo_muestra').html(data);

            if ($.fn.selectpicker) {
                $('#botones_citas #tipo_muestra').selectpicker('refresh');
            }

            refrescarCalendarioCitas();
        },
        error: function (xhr, status, error) {
            console.error("Error AJAX en getTipoMuestra.php");
            console.error("Status:", status);
            console.error("Error:", error);
            console.error("Respuesta servidor:", xhr.responseText);
        }
    });
}

function configurarFocusModalesCitas() {
    $("#ModalAdd").off('shown.bs.modal').on('shown.bs.modal', function () {
        $(this).find('#form-addevent #expediente').focus();
    });

    $("#buscarCita").off('shown.bs.modal').on('shown.bs.modal', function () {
        $(this).find('#form-buscarcita #bs-regis').focus();
    });

    $("#buscarHistorial").off('shown.bs.modal').on('shown.bs.modal', function () {
        $(this).find('#form-buscarhistorial #bs-regis').focus();
    });

    $("#buscarHistorialReprogramaciones").off('shown.bs.modal').on('shown.bs.modal', function () {
        $(this).find('#form_buscarhistorial_reprogramaciones #bs-regis').focus();
    });

    $("#buscarHistorialNo").off('shown.bs.modal').on('shown.bs.modal', function () {
        $(this).find('#form-buscarhistorialno #bs-regis').focus();
    });

    $("#modal_busqueda_colaboradores").off('shown.bs.modal').on('shown.bs.modal', function () {
        $(this).find('#formulario_busqueda_coloboradores #buscar').focus();
    });
}

function actualizarEventos() {
    return refrescarCalendarioCitas();
}

function convertDate(inputFormat) {
    function pad(s) {
        return s < 10 ? '0' + s : s;
    }

    var d = new Date(inputFormat);

    return [
        d.getFullYear(),
        pad(d.getMonth() + 1),
        pad(d.getDate())
    ].join('-');
}

function reportePDF(agenda_id) {
    window.open('<?php echo SERVERURL; ?>php/citas/tickets.php?agenda_id=' + agenda_id);
}

function pagination(partida) {

}
</script>