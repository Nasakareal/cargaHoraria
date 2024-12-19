<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/horarios_grupos/grupos_disponibles.php');
include('../../app/controllers/asignacion_manual/listado_de_laboratorios.php');
include('../../app/controllers/asignacion_manual/obtener_laboratorio.php');
include('../../app/controllers/asignacion_manual/obtener_aula.php');

$materias = [];
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $group_id = $_GET['id'];
    $queryMaterias = $pdo->prepare("
        SELECT m.subject_id, m.subject_name 
        FROM subjects m 
        INNER JOIN group_subjects gs ON m.subject_id = gs.subject_id
        WHERE gs.group_id = :group_id
    ");
    $queryMaterias->bindParam(':group_id', $group_id, PDO::PARAM_INT);
    $queryMaterias->execute();
    $materias = $queryMaterias->fetchAll(PDO::FETCH_ASSOC);
}

$lab_id = isset($_GET['lab_id']) && !empty($_GET['lab_id']) ? $_GET['lab_id'] : null;
$aula_id = isset($_GET['aula_id']) && !empty($_GET['aula_id']) ? $_GET['aula_id'] : null;

$assignment_type = 'Aula';
if ($lab_id !== null) {
    $assignment_type = 'Laboratorio';
}

$sql = "
    SELECT 
        a.assignment_id, 
        a.subject_id, 
        m.subject_name, 
        a.start_time, 
        a.end_time, 
        a.schedule_day,
        a.group_id,
        g.group_name
    FROM manual_schedule_assignments a
    INNER JOIN subjects m ON a.subject_id = m.subject_id
    INNER JOIN `groups` g ON a.group_id = g.group_id
    WHERE a.schedule_day IS NOT NULL
";

$params = [];

if ($assignment_type === 'Aula') {
    $sql .= " AND a.group_id = :group_id AND a.classroom_id = :aula_id";
    $params[':group_id'] = $_GET['id'];
    $params[':aula_id'] = $aula_id;
} elseif ($assignment_type === 'Laboratorio') {
    $sql .= " AND (a.lab1_assigned = :lab_id OR a.lab2_assigned = :lab_id)";
    $params[':lab_id'] = $lab_id;
}

$queryAsignaciones = $pdo->prepare($sql);

foreach ($params as $key => $value) {
    $queryAsignaciones->bindValue($key, $value, PDO::PARAM_INT);
}

$queryAsignaciones->execute();
$asignaciones = $queryAsignaciones->fetchAll(PDO::FETCH_ASSOC);

$events = [];
$daysOfWeek = ['lunes' => 1, 'martes' => 2, 'miércoles' => 3, 'jueves' => 4, 'viernes' => 5, 'sábado' => 6];

foreach ($asignaciones as $asignacion) {
    $schedule_day_lower = strtolower($asignacion['schedule_day']);
    if (!isset($daysOfWeek[$schedule_day_lower])) {
        continue;
    }

    $start_date = new DateTime();
    $start_date->setISODate((int)$start_date->format('o'), (int)$start_date->format('W'), $daysOfWeek[$schedule_day_lower]);
    $start_date->setTime((int)substr($asignacion['start_time'], 0, 2), (int)substr($asignacion['start_time'], 3, 2));
    $end_date = clone $start_date;
    $end_date->setTime((int)substr($asignacion['end_time'], 0, 2), (int)substr($asignacion['end_time'], 3, 2));

    $events[] = [
        'title' => htmlspecialchars($asignacion['subject_name'] . ' - Grupo ' . $asignacion['group_name']),
        'start' => $start_date->format('Y-m-d\TH:i:s'),
        'end' => $end_date->format('Y-m-d\TH:i:s'),
        'backgroundColor' => '#8B0000',
        'borderColor' => '#660000',
        'textColor' => '#fff',
        'editable' => false,
        'extendedProps' => [
            'assignment_id' => $asignacion['assignment_id'],
            'group_id' => $asignacion['group_id'],
            'assignment_type' => $assignment_type
        ]
    ];
}

$events_json = json_encode($events);
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
                        <option value="<?= htmlspecialchars($grupo['group_id']); ?>" <?= isset($_GET['id']) && $_GET['id'] == $grupo['group_id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($grupo['group_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filtro de aulas -->
            <div class="form-group">
                <label for="aulaSelector">Seleccione un aula:</label>
                <select id="aulaSelector" name="aula_id" class="form-control" onchange="clearLabAndSubmit()">
                    <option value="">-- Seleccionar aula --</option>
                    <?php foreach ($aulas as $aula): ?>
                        <option value="<?= htmlspecialchars($aula['classroom_assigned']); ?>" <?= isset($_GET['aula_id']) && $_GET['aula_id'] == $aula['classroom_assigned'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($aula['aula_nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filtro de laboratorio -->
            <div class="form-group">
                <label for="labSelector">Seleccione un laboratorio:</label>
                <select id="labSelector" name="lab_id" class="form-control" onchange="clearAulaAndSubmit()">
                    <option value="">-- Seleccionar laboratorio --</option>
                    <?php foreach ($labs as $lab): ?>
                        <option value="<?= htmlspecialchars($lab['lab_id']); ?>" <?= isset($_GET['lab_id']) && $_GET['lab_id'] == $lab['lab_id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($lab['lab_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>

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
                                        <div class="external-event bg-success" data-event='{"title":"<?= htmlspecialchars($materia['subject_name']); ?>", "subject_id":"<?= htmlspecialchars($materia['subject_id']); ?>"}'>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const events = <?php echo $events_json; ?>;
    const materias = <?php echo json_encode($materias); ?>;
    const lab_id = <?= isset($_GET['lab_id']) && !empty($_GET['lab_id']) ? intval($_GET['lab_id']) : 'null'; ?>;
    const aula_id = <?= isset($_GET['aula_id']) && !empty($_GET['aula_id']) ? intval($_GET['aula_id']) : 'null'; ?>;

    let assignment_type = 'Aula';
    if (lab_id !== null) {
        assignment_type = 'Laboratorio';
    }

    console.log("Eventos desde PHP:", events);
    console.log("Materias desde PHP:", materias);
    console.log("Laboratorio seleccionado:", lab_id);
    console.log("Aula seleccionada:", aula_id);
    console.log("Tipo de Asignación:", assignment_type);

    $(function () {
        function ini_events(ele) {
            ele.each(function () {
                var eventObject = {
                    title: $.trim($(this).text()),
                    subject_id: $(this).data('event').subject_id
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

        new FullCalendar.Draggable(containerEl, {
            itemSelector: '.external-event',
            eventData: function (eventEl) {
                return {
                    title: eventEl.innerText.trim(),
                    subject_id: $(eventEl).data('event').subject_id,
                    backgroundColor: window.getComputedStyle(eventEl, null).getPropertyValue('background-color'),
                    borderColor: window.getComputedStyle(eventEl, null).getPropertyValue('background-color'),
                    textColor: window.getComputedStyle(eventEl, null).getPropertyValue('color')
                };
            }
        });

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            locale: 'es',
            timeZone: 'America/Mexico_City',
            editable: true,
            droppable: true,
            headerToolbar: {
                left: '',
                center: '',
                right: ''
            },
            allDaySlot: false,
            slotMinTime: '07:00:00',
            slotMaxTime: '20:00:00',
            slotDuration: '00:30',
            hiddenDays: [0],
            drop: function (info) {
                if (checkbox.checked) {
                    info.draggedEl.parentNode.removeChild(info.draggedEl);
                }
            },

            events: events,

            eventReceive: function (info) {
                Swal.fire({
                    title: '¿Deseas guardar la asignación?',
                    text: `El evento "${info.event.title}" fue añadido al calendario. ¿Quieres guardarlo?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Guardar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const start_time = info.event.start ? info.event.start.toISOString().slice(11, 19) : null;
                        const end_time = info.event.end ? info.event.end.toISOString().slice(11, 19) : null;
                        const schedule_day = info.event.start ? info.event.start.toLocaleString('es-ES', { weekday: 'long' }) : null;

                        $.ajax({
                            url: '../../app/controllers/asignacion_manual/update.php',
                            type: 'POST',
                            data: {
                                subject_id: info.event.extendedProps.subject_id,
                                start_time: start_time,
                                end_time: end_time,
                                schedule_day: schedule_day,
                                group_id: <?= json_encode($_GET['id'] ?? null); ?>,
                                lab_id: lab_id,
                                aula_id: aula_id,
                                tipo_espacio: assignment_type
                            },
                            success: function(response) {
                                try {
                                    var data = JSON.parse(response);
                                    if (data.status === 'success') {
                                        Swal.fire({
                                            title: 'Asignación guardada',
                                            text: `La asignación "${info.event.title}" ha sido guardada correctamente.`,
                                            icon: 'success',
                                            confirmButtonText: 'Aceptar'
                                        });
                                        if (data.assignment_id) {
                                            info.event.setExtendedProp('assignment_id', data.assignment_id);
                                        }
                                    } else {
                                        Swal.fire({
                                            title: 'Error',
                                            text: data.message || 'Hubo un problema al guardar la asignación.',
                                            icon: 'error',
                                            confirmButtonText: 'Aceptar'
                                        });
                                        info.event.remove();
                                    }
                                } catch (e) {
                                    Swal.fire({
                                        title: 'Error',
                                        text: 'Respuesta inválida del servidor.',
                                        icon: 'error',
                                        confirmButtonText: 'Aceptar'
                                    });
                                    info.event.remove();
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
                        info.event.remove();
                        Swal.fire({
                            title: 'Asignación cancelada',
                            text: `El evento "${info.event.title}" ha sido removido del calendario.`,
                            icon: 'info',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                });
            },

            eventDrop: function (info) {
                Swal.fire({
                    title: '¿Deseas guardar la nueva asignación?',
                    text: `El evento "${info.event.title}" ha sido movido. ¿Quieres guardar la nueva asignación?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Guardar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const start_time = info.event.start ? info.event.start.toISOString().slice(11, 19) : null;
                        const end_time = info.event.end ? info.event.end.toISOString().slice(11, 19) : null;
                        const schedule_day = info.event.start ? info.event.start.toLocaleString('es-ES', { weekday: 'long' }) : null;
                        const assignment_id = info.event.extendedProps.assignment_id;

                        $.ajax({
                            url: '../../app/controllers/asignacion_manual/update.php',
                            type: 'POST',
                            data: {
                                assignment_id: assignment_id,
                                subject_id: info.event.extendedProps.subject_id,
                                start_time: start_time,
                                end_time: end_time,
                                schedule_day: schedule_day,
                                group_id: <?= json_encode($_GET['id'] ?? null); ?>,
                                lab_id: lab_id,
                                aula_id: aula_id,
                                tipo_espacio: assignment_type
                            },
                            success: function(response) {
                                try {
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
                                            text: data.message || 'Hubo un problema al guardar la asignación.',
                                            icon: 'error',
                                            confirmButtonText: 'Aceptar'
                                        });
                                        info.revert();
                                    }
                                } catch (e) {
                                    Swal.fire({
                                        title: 'Error',
                                        text: 'Respuesta inválida del servidor.',
                                        icon: 'error',
                                        confirmButtonText: 'Aceptar'
                                    });
                                    info.revert();
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Hubo un problema al intentar guardar la asignación.',
                                    icon: 'error',
                                    confirmButtonText: 'Aceptar'
                                });
                                info.revert();
                            }
                        });
                    } else {
                        info.revert();
                        Swal.fire({
                            title: 'Movimiento cancelado',
                            text: `El evento "${info.event.title}" ha sido revertido al lugar original.`,
                            icon: 'info',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                });
            },

            eventClick: function(info) {
                Swal.fire({
                    title: '¿Deseas eliminar esta asignación?',
                    text: `El evento "${info.event.title}" será eliminado.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const assignment_id = info.event.extendedProps.assignment_id;

                        $.ajax({
                            url: '../../app/controllers/asignacion_manual/delete.php',
                            type: 'POST',
                            data: {
                                assignment_id: assignment_id,
                                group_id: <?= json_encode($_GET['id'] ?? null); ?>
                            },
                            success: function(response) {
                                try {
                                    var data = JSON.parse(response);
                                    if (data.status === 'success') {
                                        info.event.remove();
                                        Swal.fire({
                                            title: 'Asignación eliminada',
                                            text: `La asignación "${info.event.title}" ha sido eliminada correctamente.`,
                                            icon: 'success',
                                            confirmButtonText: 'Aceptar'
                                        });
                                    } else {
                                        Swal.fire({
                                            title: 'Error',
                                            text: data.message || 'Hubo un problema al eliminar la asignación.',
                                            icon: 'error',
                                            confirmButtonText: 'Aceptar'
                                        });
                                    }
                                } catch (e) {
                                    Swal.fire({
                                        title: 'Error',
                                        text: 'Respuesta inválida del servidor.',
                                        icon: 'error',
                                        confirmButtonText: 'Aceptar'
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Hubo un problema al intentar eliminar la asignación.',
                                    icon: 'error',
                                    confirmButtonText: 'Aceptar'
                                });
                            }
                        });
                    }
                });
            },

            eventDidMount: function(info) {
                if(info.event.start && info.event.start < new Date()) {
                    info.el.style.backgroundColor = "#FF6F61";
                }
            }
        });

        calendar.render();
    });

    function clearLabAndSubmit() {
        document.getElementById('labSelector').value = '';
        document.forms[0].submit();
    }

    function clearAulaAndSubmit() {
        document.getElementById('aulaSelector').value = '';
        document.forms[0].submit();
    }

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

<style>
    .fc-event {
        cursor: not-allowed;
        opacity: 0.8;
    }

    .fc-timegrid-event {
        font-size: 10px;
        background-color: #8B0000 !important;
        border-color: #660000 !important;
        color: white !important;
    }
</style>
