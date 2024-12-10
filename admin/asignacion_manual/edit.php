<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/horarios_grupos/grupos_disponibles.php');
include('../../app/controllers/horarios_grupos/obtener_horario_grupo.php');
include('../../app/controllers/horarios_grupos/procesar_horario_grupo.php');
include('../../app/controllers/horarios_grupos/listado_asignaciones.php');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

<!-- Selector de Grupos -->
<div class="container">
    <form method="GET" action="">
        <div class="form-group">
            <label for="groupSelector">Seleccione un grupo:</label>
            <select id="groupSelector" name="id" class="form-control" onchange="this.form.submit()">
                <option value="">-- Seleccionar grupo --</option>
                <?php foreach ($grupos as $grupo): ?>
                    <option value="<?= $grupo['group_id']; ?>" <?= isset($_GET['id']) && $_GET['id'] == $grupo['group_id'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($grupo['group_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>
<?php 
// Verificar si se seleccionó un grupo
$materias = [];
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $group_id = $_GET['id'];

    // Obtener las materias del grupo seleccionado
    $queryMaterias = $pdo->prepare("SELECT m.subject_name FROM subjects m 
                                    INNER JOIN group_subjects gs ON m.subject_id = gs.subject_id
                                    WHERE gs.group_id = :group_id");
    $queryMaterias->bindParam(':group_id', $group_id, PDO::PARAM_INT);
    $queryMaterias->execute();
    $materias = $queryMaterias->fetchAll(PDO::FETCH_ASSOC);
}

?>
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
                <?php if (!empty($materias)): ?>
                    <p class="text-muted">Arrastra las materias al calendario para programarlas.</p>
                    <?php foreach ($materias as $materia): ?>
                        <div class="external-event bg-success" data-event='{"title":"<?= htmlspecialchars($materia['subject_name']); ?>"}'>
                            <?= htmlspecialchars($materia['subject_name']); ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">Seleccione un grupo para ver las materias disponibles.</p>
                <?php endif; ?>
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
                // Mostrar una alerta con botones "Guardar" y "Cancelar"
                Swal.fire({
                    title: '¿Deseas guardar la asignación?',
                    text: `El evento "${info.event.title}" fue añadido al calendario. ¿Quieres guardarlo?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Guardar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Aquí es donde colocamos el AJAX para guardar la asignación
                        $.ajax({
                            url: '../../app/controllers/asignacion_manual/update.php', // Ruta del servidor
                            type: 'POST',
                            data: {
                                title: info.event.title,
                                start_time: info.event.start.toISOString().slice(11, 19),
                                end_time: info.event.end.toISOString().slice(11, 19),
                                schedule_day: info.event.start.toLocaleString('es', { weekday: 'long' }),
                                group_id: <?= $_GET['id']; ?>  // Obtener el grupo seleccionado
                            },
                            success: function(response) {
                                var data = JSON.parse(response);
                                if (data.status === 'success') {
                                    Swal.fire({
                                        title: 'Asignación guardada',
                                        text: `La asignación "${info.event.title}" ha sido guardada correctamente.`,
                                        icon: 'success',
                                        confirmButtonText: 'Aceptar'
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Error',
                                        text: 'Hubo un problema al guardar la asignación.',
                                        icon: 'error',
                                        confirmButtonText: 'Aceptar'
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Hubo un problema al intentar guardar la asignación.',
                                    icon: 'error',
                                    confirmButtonText: 'Aceptar'
                                });
                            }
                        });
                    } else {
                        // Si el usuario cancela, eliminar el evento del calendario
                        info.event.remove();
                        Swal.fire({
                            title: 'Asignación cancelada',
                            text: `El evento "${info.event.title}" ha sido removido del calendario.`,
                            icon: 'info',
                            confirmButtonText: 'Aceptar'
                        });
                    }
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

