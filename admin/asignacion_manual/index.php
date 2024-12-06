<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/grupos/listado_de_grupos.php');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Calendario de Horarios</h1>
                </div>
                <div class="col-sm-6"></div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container">
            <div class="row">
                <!-- Lista de materias -->
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Materias Disponibles</h3>
                        </div>
                        <div class="card-body">
                            <div id="external-events">
                                <p class="text-muted">Arrastra las materias al calendario para programarlas.</p>
                                <div class="external-event bg-success" data-event='{"title":"Matemáticas"}'>Matemáticas</div>
                                <div class="external-event bg-warning" data-event='{"title":"Física"}'>Física</div>
                                <div class="external-event bg-info" data-event='{"title":"Química"}'>Química</div>
                                <div class="external-event bg-danger" data-event='{"title":"Biología"}'>Biología</div>
                                <div class="external-event bg-primary" data-event='{"title":"Historia"}'>Historia</div>
                                <p>
                                    <input type="checkbox" id="drop-remove">
                                    <label for="drop-remove">Eliminar al arrastrar</label>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendario -->
                <div class="col-md-9">
                    <div class="card card-primary">
                        <div class="card-body p-0">
                            <div id="calendar"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include('../../admin/layout/parte2.php');
include('../../layout/mensajes.php');
?>



<!-- FullCalendar Styles and Scripts -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-ui-dist/jquery-ui.min.js"></script>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Calendario de Horarios</h1>
                </div>
                <div class="col-sm-6"></div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container">
            <div class="row">
                <!-- Lista de materias -->
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Materias Disponibles</h3>
                        </div>
                        <div class="card-body">
                            <div id="external-events">
                                <p class="text-muted">Arrastra las materias al calendario para programarlas.</p>
                                <div class="external-event bg-success">Matemáticas</div>
                                <div class="external-event bg-warning">Física</div>
                                <div class="external-event bg-info">Química</div>
                                <div class="external-event bg-danger">Biología</div>
                                <div class="external-event bg-primary">Historia</div>
                                <p>
                                    <input type="checkbox" id="drop-remove">
                                    <label for="drop-remove">Eliminar al arrastrar</label>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendario -->
                <div class="col-md-9">
                    <div class="card card-primary">
                        <div class="card-body p-0">
                            <div id="calendar"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        /* Inicializar eventos arrastrables */
        function ini_events(ele) {
            ele.each(function () {
                var eventObject = {
                    title: $.trim($(this).text())
                };

                $(this).data('eventObject', eventObject);

                $(this).draggable({
                    zIndex: 1070,
                    revert: true,
                    revertDuration: 0
                });
            });
        }

        ini_events($('#external-events div.external-event'));

        var containerEl = document.getElementById('external-events');
        var checkbox = document.getElementById('drop-remove');
        var calendarEl = document.getElementById('calendar');

        /* Inicializar Draggable */
        new FullCalendar.Draggable(containerEl, {
            itemSelector: '.external-event',
            eventData: function (eventEl) {
                return {
                    title: eventEl.innerText.trim(),
                    backgroundColor: window.getComputedStyle(eventEl, null).getPropertyValue('background-color'),
                    borderColor: window.getComputedStyle(eventEl, null).getPropertyValue('background-color'),
                    textColor: window.getComputedStyle(eventEl, null).getPropertyValue('color')
                };
            }
        });

        /* Inicializar el Calendario */
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            locale: 'es',
            editable: true,
            droppable: true,
            headerToolbar: {
                left: '',
                center: 'title',
                right: ''
            },
            allDaySlot: false,
            slotMinTime: '07:00:00',
            slotMaxTime: '20:00:00',
            slotDuration: '01:00',
            hiddenDays: [0],
            drop: function (info) {
                if (checkbox.checked) {
                    info.draggedEl.parentNode.removeChild(info.draggedEl);
                }
            },
            eventReceive: function (info) {
                // Mostrar mensaje con SweetAlert
                Swal.fire({
                    title: 'Evento añadido',
                    text: `El evento "${info.event.title}" fue añadido al calendario.`,
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                });

                console.log('Evento recibido:', info.event);
            }
        });

        calendar.render();
    });
</script>


<style>
    .external-event {
        cursor: pointer;
        margin-bottom: 10px;
        padding: 5px;
        color: #fff;
        text-align: center;
        border-radius: 3px;
    }
</style>
