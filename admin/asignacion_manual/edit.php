<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/horarios_grupos/grupos_disponibles.php');
include('../../app/controllers/asignacion_manual/listado_de_laboratorios.php');
include('../../app/controllers/asignacion_manual/obtener_laboratorio.php');
include('../../app/controllers/asignacion_manual/obtener_aula.php');

$materias = [];
$group_id = isset($_GET['id']) ? $_GET['id'] : null;

if ($group_id) {
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

$lab_id  = isset($_GET['lab_id'])  && !empty($_GET['lab_id'])  ? $_GET['lab_id']  : null;
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
    $sql .= " AND a.classroom_id = :aula_id";
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

$colorPalette = [
    '#1f77b4', // azul
    '#2ca02c', // verde
    '#ff7f0e', // naranja
    '#ffc107', // amarillo
    '#9467bd', // morado
    '#8c564b', // café
    '#e377c2', // rosa
    '#7f7f7f', // gris
    '#bcbd22', // verde claro
    '#17becf'  // turquesa
];

$subjectColors = [];
foreach ($materias as $index => $materia) {
    $colorIndex = $index % count($colorPalette);
    $subjectColors[$materia['subject_id']] = $colorPalette[$colorIndex];
}

$events = [];
$daysOfWeek = [
    'lunes' => 1, 'martes' => 2, 'miércoles' => 3,
    'jueves' => 4, 'viernes' => 5,
    'sábado' => 6, 'sabado' => 6,
    'Sábado' => 6, 'Sabado' => 6
];

foreach ($asignaciones as $asignacion) {
    $dayLower = strtolower($asignacion['schedule_day']);
    if (!isset($daysOfWeek[$dayLower])) {
        continue;
    }

    $start_date = new DateTime();
    $start_date->setISODate((int)$start_date->format('o'), (int)$start_date->format('W'), $daysOfWeek[$dayLower]);
    $start_date->setTime(
        (int)substr($asignacion['start_time'], 0, 2),
        (int)substr($asignacion['start_time'], 3, 2)
    );

    $end_date = clone $start_date;
    $end_date->setTime(
        (int)substr($asignacion['end_time'], 0, 2),
        (int)substr($asignacion['end_time'], 3, 2)
    );

    $color = '#000000';

    $events[] = [
        'title' => htmlspecialchars($asignacion['subject_name'] . ' - Grupo ' . $asignacion['group_name']),
        'start' => $start_date->format('Y-m-d\TH:i:s'),
        'end'   => $end_date->format('Y-m-d\TH:i:s'),
        'color' => $color,
        'textColor' => '#fff',
        'editable' => false,
        'extendedProps' => [
            'assignment_id'   => $asignacion['assignment_id'],
            'group_id'        => $asignacion['group_id'],
            'subject_id'      => $asignacion['subject_id'],
            'assignment_type' => $assignment_type
        ]
    ];
}

function darkenColor($hexColor, $percent) {
    $hexColor = ltrim($hexColor, '#');
    $r = hexdec(substr($hexColor, 0, 2));
    $g = hexdec(substr($hexColor, 2, 2));
    $b = hexdec(substr($hexColor, 4, 2));

    $r = max(0, min(255, $r - ($r * $percent / 100)));
    $g = max(0, min(255, $g - ($g * $percent / 100)));
    $b = max(0, min(255, $b - ($b * $percent / 100)));

    return sprintf("#%02x%02x%02x", $r, $g, $b);
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
                        <option value="<?= htmlspecialchars($grupo['group_id']); ?>"
                            <?= ($group_id && $group_id == $grupo['group_id']) ? 'selected' : ''; ?>>
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
                        <option value="<?= htmlspecialchars($aula['classroom_assigned']); ?>"
                            <?= ($aula_id && $aula_id == $aula['classroom_assigned']) ? 'selected' : ''; ?>>
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
                        <option value="<?= htmlspecialchars($lab['lab_id']); ?>"
                            <?= ($lab_id && $lab_id == $lab['lab_id']) ? 'selected' : ''; ?>>
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
                                    <p class="text-muted">
                                        Arrastra las materias al calendario para programarlas.
                                    </p>
                                    <?php foreach ($materias as $materia): ?>
                                        <div 
                                          class="external-event" 
                                          style="background-color: <?= htmlspecialchars($subjectColors[$materia['subject_id']]); ?>;"
                                          data-event='{
                                            "title":"<?= htmlspecialchars($materia['subject_name']); ?>",
                                            "subject_id":"<?= htmlspecialchars($materia['subject_id']); ?>"
                                          }'>
                                            <?= htmlspecialchars($materia['subject_name']); ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted">
                                        Seleccione un grupo para ver las materias disponibles.
                                    </p>
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
    const userRolId = <?= $_SESSION['sesion_rol']; ?>;
    const events       = <?php echo $events_json; ?>;
    const materias     = <?php echo json_encode($materias); ?>;
    const groupId      = <?= json_encode($group_id); ?>;
    const lab_id       = <?= ($lab_id  !== null) ? intval($lab_id)  : 'null'; ?>;
    const aula_id      = <?= ($aula_id !== null) ? intval($aula_id) : 'null'; ?>;
    let assignmentType = 'Aula';
    if (lab_id !== null) {
        assignmentType = 'Laboratorio';
    }

    console.log("Eventos desde PHP:", events);
    console.log("Materias desde PHP:", materias);
    console.log("Grupo seleccionado:", groupId);
    console.log("Laboratorio seleccionado:", lab_id);
    console.log("Aula seleccionada:", aula_id);
    console.log("Tipo de Asignación:", assignmentType);

    const colorPalette = [
        '#1f77b4', '#2ca02c', '#ff7f0e', '#ffc107',
        '#9467bd', '#8c564b', '#e377c2', '#7f7f7f',
        '#bcbd22', '#17becf'
    ];

    const subjectColorMap = {};
    materias.forEach((materia, index) => {
        subjectColorMap[materia.subject_id] = colorPalette[index % colorPalette.length];
    });

    const eventsWithColors = events.map(ev => {
        if (parseInt(ev.extendedProps.group_id) === parseInt(groupId)) {
            // Sí pertenece al grupo actual
            const subjId = ev.extendedProps.subject_id;
            return {
                ...ev,
                color: subjectColorMap[subjId] || '#4F4F4F',
                textColor: '#fff'
            };
        } else {
            // Es de otro grupo
            return {
                ...ev,
                color: '#4F4F4F',
                textColor: '#fff'
            };
        }
    });

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
        var checkbox    = document.getElementById('drop-remove');
        var calendarEl  = document.getElementById('calendar');

        new FullCalendar.Draggable(containerEl, {
            itemSelector: '.external-event',
            eventData: function (eventEl) {
                return {
                    title: eventEl.innerText.trim(),
                    subject_id: $(eventEl).data('event').subject_id,
                    color: window.getComputedStyle(eventEl, null).getPropertyValue('background-color'),
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

            events: eventsWithColors,

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
                        const start_time   = info.event.start ? info.event.start.toISOString().slice(11, 19) : null;
                        const end_time     = info.event.end   ? info.event.end.toISOString().slice(11, 19)   : null;
                        const schedule_day = info.event.start ?
                            info.event.start.toLocaleString('es-ES', { weekday: 'long' }) : null;

                        $.ajax({
                            url: '../../app/controllers/asignacion_manual/update.php',
                            type: 'POST',
                            data: {
                                subject_id  : info.event.extendedProps.subject_id,
                                start_time  : start_time,
                                end_time    : end_time,
                                schedule_day: schedule_day,
                                group_id    : groupId,
                                lab_id      : lab_id,
                                aula_id     : aula_id,
                                tipo_espacio : assignmentType
                            },
                            success: function(response) {
                                try {
                                    var data = JSON.parse(response);
                                    if (data.status === 'success') {
                                        Swal.fire({
                                            title: 'Asignación guardada',
                                            text : `La asignación "${info.event.title}" ha sido guardada correctamente.`,
                                            icon : 'success',
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
                                    text : 'Hubo un problema al intentar guardar la asignación.',
                                    icon : 'error',
                                    confirmButtonText: 'Aceptar'
                                });
                            }
                        });
                    } else {
                        info.event.remove();
                        Swal.fire({
                            title: 'Asignación cancelada',
                            text : `El evento "${info.event.title}" ha sido removido del calendario.`,
                            icon : 'info',
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
                        const start_time   = info.event.start ? info.event.start.toISOString().slice(11, 19) : null;
                        const end_time     = info.event.end   ? info.event.end.toISOString().slice(11, 19)   : null;
                        const schedule_day = info.event.start ?
                            info.event.start.toLocaleString('es-ES', { weekday: 'long' }) : null;
                        const assignment_id = info.event.extendedProps.assignment_id;

                        $.ajax({
                            url: '../../app/controllers/asignacion_manual/update.php',
                            type: 'POST',
                            data: {
                                assignment_id: assignment_id,
                                subject_id   : info.event.extendedProps.subject_id,
                                start_time   : start_time,
                                end_time     : end_time,
                                schedule_day : schedule_day,
                                group_id     : groupId,
                                lab_id       : lab_id,
                                aula_id      : aula_id,
                                tipo_espacio : assignmentType
                            },
                            success: function(response) {
                                try {
                                    var data = JSON.parse(response);
                                    if (data.status === 'success') {
                                        Swal.fire({
                                            title: 'Asignación guardada',
                                            text : `La asignación "${info.event.title}" ha sido guardada correctamente.`,
                                            icon : 'success',
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
                                    text : 'Hubo un problema al intentar guardar la asignación.',
                                    icon : 'error',
                                    confirmButtonText: 'Aceptar'
                                });
                                info.revert();
                            }
                        });
                    } else {
                        info.revert();
                        Swal.fire({
                            title: 'Movimiento cancelado',
                            text : `El evento "${info.event.title}" ha sido revertido al lugar original.`,
                            icon : 'info',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                });
            },

            eventClick: function(info) {
                if (userRolId !== 1) {
                    // Opcional: Mostrar un aviso si no es admin
                    Swal.fire({
                        icon: 'info',
                        title: 'Acceso restringido',
                        text: 'Solo los administradores pueden eliminar asignaciones.',
                        confirmButtonText: 'Aceptar'
                    });
                    return; // no continúa si no es admin
                }

                Swal.fire({
                    title: '¿Deseas eliminar esta asignación?',
                    text : `El evento "${info.event.title}" será eliminado.`,
                    icon : 'warning',
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
                                group_id     : groupId
                            },
                            success: function(response) {
                                try {
                                    var data = JSON.parse(response);
                                    if (data.status === 'success') {
                                        info.event.remove();
                                        Swal.fire({
                                            title: 'Asignación eliminada',
                                            text : `La asignación "${info.event.title}" ha sido eliminada correctamente.`,
                                            icon : 'success',
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
                                    text : 'Hubo un problema al intentar eliminar la asignación.',
                                    icon : 'error',
                                    confirmButtonText: 'Aceptar'
                                });
                            }
                        });
                    }
                });
            },

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

    .fc-event {
        cursor: pointer;
        opacity: 1;
    }

    .fc-timegrid-event {
        font-size: 8px;
        color: white !important;
    }
</style>
