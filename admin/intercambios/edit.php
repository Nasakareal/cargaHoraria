<?php
// Ajusta la ruta y la lógica según tu estructura real
date_default_timezone_set('America/Mexico_City');

include('../../app/config.php');
include('../../app/helpers/verificar_admin.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/horarios_grupos/grupos_disponibles.php');

if (!isset($grupos) || !is_array($grupos)) {
    die('Error: No se pudieron obtener los grupos disponibles.');
}

$materias = [];
$group_id = isset($_GET['id']) ? intval($_GET['id']) : null;

// Si se especifica un grupo, obtenemos las materias
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

// Obtenemos asignaciones activas
$sql = "
    SELECT 
        a.assignment_id,
        a.subject_id,
        m.subject_name,
        a.start_time,
        a.end_time,
        a.schedule_day,
        a.group_id,
        g.group_name,
        a.classroom_id,
        a.lab_id,
        a.tipo_espacio,
        a.teacher_id
    FROM schedule_assignments a
    INNER JOIN subjects m ON a.subject_id = m.subject_id
    INNER JOIN `groups` g ON a.group_id = g.group_id
    WHERE a.schedule_day IS NOT NULL
      AND a.estado = 'activo'
      AND a.group_id = :group_id
";

$queryAsignaciones = $pdo->prepare($sql);

if ($group_id) {
    $queryAsignaciones->bindValue(':group_id', $group_id, PDO::PARAM_INT);
}

$queryAsignaciones->execute();
$asignaciones = $queryAsignaciones->fetchAll(PDO::FETCH_ASSOC);

// Paleta de colores para asignar a cada materia
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

// Asignamos un color a cada materia según índice
$subjectColors = [];
foreach ($materias as $index => $materia) {
    $colorIndex = $index % count($colorPalette);
    $subjectColors[$materia['subject_id']] = $colorPalette[$colorIndex];
}

// Creamos eventos para FullCalendar
$events = [];
$daysOfWeek = [
    'lunes'     => 1, 
    'martes'    => 2, 
    'miércoles' => 3,
    'jueves'    => 4, 
    'viernes'   => 5,
    'sábado'    => 6,
    // A veces escrito "sabado" sin tilde
    'sabado'    => 6
];

foreach ($asignaciones as $asignacion) {
    $dayLower = strtolower($asignacion['schedule_day']);
    if (!isset($daysOfWeek[$dayLower])) {
        continue;
    }

    // Creamos fechas ficticias basadas en la semana actual
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

    // Determinamos color según la materia
    $color = isset($subjectColors[$asignacion['subject_id']])
             ? $subjectColors[$asignacion['subject_id']]
             : '#000000';

    $events[] = [
        'id'    => $asignacion['assignment_id'],
        'title' => htmlspecialchars($asignacion['subject_name'] . ' - Grupo ' . $asignacion['group_name']),
        'start' => $start_date->format('Y-m-d\TH:i:s'),
        'end'   => $end_date->format('Y-m-d\TH:i:s'),
        'color' => $color,
        'textColor' => '#fff',
        'editable' => true,
        'extendedProps' => [
            'assignment_id' => $asignacion['assignment_id'],
            'group_id'      => $asignacion['group_id'],
            'subject_id'    => $asignacion['subject_id'],
            'tipo_espacio'  => $asignacion['tipo_espacio']
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
                        <option 
                            value="<?= htmlspecialchars($grupo['group_id']); ?>"
                            <?= ($group_id && $group_id == $grupo['group_id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($grupo['group_name']); ?>
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
                <!-- Calendario -->
                <div class="col-md-12">
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
    // Pasamos los eventos y materias desde PHP a JavaScript
    const events = <?php echo $events_json; ?>;
    const materias = <?php echo json_encode($materias); ?>;
    const groupId = <?= json_encode($group_id); ?>;

    console.log("Eventos desde PHP:", events);
    console.log("Materias desde PHP:", materias);
    console.log("Grupo seleccionado:", groupId);

    // Paleta de colores para reasignar si fuera necesario
    const colorPalette = [
        '#1f77b4', '#2ca02c', '#ff7f0e', '#ffc107',
        '#9467bd', '#8c564b', '#e377c2', '#7f7f7f',
        '#bcbd22', '#17becf'
    ];

    // Mapeo de ID de materia a color
    const subjectColorMap = {};
    materias.forEach((materia, index) => {
        subjectColorMap[materia.subject_id] = colorPalette[index % colorPalette.length];
    });

    // Aplicamos el color correspondiente a cada evento
    const eventsWithColors = events.map(ev => {
        const subjId = ev.extendedProps.subject_id;
        return {
            ...ev,
            color: subjectColorMap[subjId] || '#4F4F4F',
            textColor: '#fff'
        };
    });

    $(function () {
        var calendarEl = document.getElementById('calendar');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            locale: 'es',
            timeZone: 'America/Mexico_City',
            editable: true,
            droppable: true,
            headerToolbar: {
                left: '',    // Puedes agregar: 'prev,next today'
                center: '',  // 'title'
                right: ''    // 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            allDaySlot: false,
            slotMinTime: '07:00:00',
            slotMaxTime: '20:00:00',
            slotDuration: '00:30',
            hiddenDays: [0], // Oculta los domingos

            events: eventsWithColors,

            // Al hacer clic sobre un evento, mostramos alerta
            eventClick: function(info) {
                Swal.fire({
                    title: info.event.title,
                    html: `
                        <strong>Inicio:</strong> ${info.event.start.toISOString().slice(11, 19)}<br>
                        <strong>Fin:</strong> ${info.event.end.toISOString().slice(11, 19)}<br>
                        <strong>Tipo de Espacio:</strong> ${info.event.extendedProps.tipo_espacio}
                    `,
                    icon: 'info',
                    confirmButtonText: 'Cerrar'
                });
            },

            // Al arrastrar y soltar un evento
            eventDrop: function(info) {
                const newStart     = info.event.start;
                const newEnd       = info.event.end;
                const daysSpanish  = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
                const schedule_day = daysSpanish[newStart.getDay()];
                const start_time   = newStart.toISOString().slice(11, 19);
                const end_time     = newEnd.toISOString().slice(11, 19);

                Swal.fire({
                    title: '¿Estás seguro de mover esta materia?',
                    html: `Nueva Fecha: <strong>${schedule_day.charAt(0).toUpperCase() + schedule_day.slice(1)}</strong><br>
                           Nueva Hora: <strong>${start_time} - ${end_time}</strong>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, mover',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Llamamos a nuestro PHP
                        $.ajax({
                            url: '../../app/controllers/intercambios/update_assignment.php',
                            method: 'POST',
                            data: {
                                assignment_id: info.event.extendedProps.assignment_id,
                                schedule_day: schedule_day,
                                start_time: start_time,
                                end_time: end_time
                            },
                            success: function(response) {
                                // Intentamos parsear la respuesta
                                try {
                                    var data = (typeof response === 'object') ? response : JSON.parse(response);
                                    if (data.status === 'success') {
                                        Swal.fire(
                                            'Actualizado!',
                                            data.message || 'La materia ha sido movida correctamente.',
                                            'success'
                                        );
                                        // Refrescamos o actualizamos si deseas
                                        //calendar.refetchEvents(); // si deseas recargar del servidor
                                    } else {
                                        Swal.fire(
                                            'Error!',
                                            data.message || 'No se pudo actualizar la materia.',
                                            'error'
                                        );
                                        // Revertimos el movimiento en el calendario
                                        info.revert();
                                    }
                                } catch (e) {
                                    console.error("Error al parsear respuesta:", e);
                                    Swal.fire(
                                        'Error!',
                                        'No se pudo leer la respuesta del servidor.',
                                        'error'
                                    );
                                    // Revertir el arrastre
                                    info.revert();
                                }
                            },
                            error: function() {
                                Swal.fire(
                                    'Error!',
                                    'Ocurrió un error al comunicarse con el servidor.',
                                    'error'
                                );
                                info.revert();
                            }
                        });
                    } else {
                        // Si cancela, revertimos
                        info.revert();
                    }
                });
            },
        });

        calendar.render();
    });
</script>

<style>
    .fc-event {
        cursor: pointer;
        opacity: 1;
    }
    .fc-timegrid-event {
        font-size: 10px;
        color: white !important;
    }
</style>
