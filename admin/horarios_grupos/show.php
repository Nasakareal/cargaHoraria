<?php
/* Filtrar y validar el group_id */
$group_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$group_id) {
    echo "ID de grupo inválido.";
    exit;
}

include('../../app/config.php');
include('../../admin/layout/parte1.php');

/* Función para obtener el horario específico del grupo */
function obtenerHorarioGrupo($group_id, $pdo)
{
    $sql_horarios = "SELECT 
                        sa.schedule_day AS day, 
                        sa.start_time AS start, 
                        sa.end_time AS end, 
                        s.subject_name, 
                        s.lab_hours,          /* Añadimos lab_hours para saber si es laboratorio */
                        sh.shift_name,
                        r.classroom_name AS room_name, /* Nombre del salón */
                        t.teacher_name         /* Nombre del profesor asignado */
                     FROM 
                        schedule_assignments sa
                     JOIN 
                        subjects s ON sa.subject_id = s.subject_id
                     JOIN 
                        `groups` g ON sa.group_id = g.group_id
                     JOIN 
                        shifts sh ON g.turn_id = sh.shift_id
                     LEFT JOIN 
                        classrooms r ON sa.classroom_id = r.classroom_id
                     LEFT JOIN 
                        teachers t ON sa.teacher_id = t.teacher_id
                     WHERE 
                        sa.group_id = :group_id
                     ORDER BY sa.schedule_day, sa.start_time";

    $query_horarios = $pdo->prepare($sql_horarios);
    $query_horarios->execute([':group_id' => $group_id]);
    return $query_horarios->fetchAll(PDO::FETCH_ASSOC);
}

/* Obtener el horario procesado del grupo específico */
$horarios = obtenerHorarioGrupo($group_id, $pdo);

if (empty($horarios)) {
    echo "<p class='text-center text-muted'>No se encontraron horarios asignados para este grupo.</p>";
    echo "<div class='text-center'><a href='../../admin/horarios_grupos' class='btn btn-secondary'>Volver</a></div>";
    include('../../layout/parte2.php');
    exit;
}

/* Obtener el turno del grupo para el encabezado */
$turno = $horarios[0]['shift_name'] ?? 'Turno no especificado';

/* Definir las horas y días según el turno */
$horas = [];
$dias = [];

switch ($turno) {
    case 'MATUTINO':
        $horas = ['07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00'];
        $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
        break;
    case 'VESPERTINO':
        $horas = ['12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00'];
        $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
        break;
    case 'MIXTO':
        $horas = ['07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00'];
        $dias = ['Viernes', 'Sábado'];
        break;
}

/* Inicializar la tabla de horarios vacía para el formato de tabla */
$tabla_horarios = [];
foreach ($horas as $hora) {
    foreach ($dias as $dia) {
        $tabla_horarios[$hora][$dia] = '';
    }
}

/* Llenar la matriz de horarios en formato de tabla */
foreach ($horarios as $horario) {
    $start_time = strtotime($horario['start']);
    $end_time = strtotime($horario['end']);
    $dia = $horario['day'];
    $materia = $horario['subject_name'];
    $salon = $horario['room_name'] ?? 'Sin salón';
    $profesor = $horario['teacher_name'] ?? 'Sin profesor';

    /* Determinar si es Aula o Laboratorio */
    $tipo_espacio = ($horario['lab_hours'] > 0) ? 'Laboratorio' : 'Aula';

    /* Iterar en bloques de una hora entre el inicio y el fin de la materia */
    for ($current_time = $start_time; $current_time < $end_time; $current_time = strtotime("+1 hour", $current_time)) {
        $hora = date("H:i", $current_time);

        /* Verificar si el bloque horario y el día están en el horario definido */
        if (in_array($hora, $horas) && in_array($dia, $dias)) {
            /* Asignar la materia a la celda correspondiente solo si está vacía */
            $tabla_horarios[$hora][$dia] .= htmlspecialchars($materia) . " - " . htmlspecialchars($tipo_espacio) . " - " . htmlspecialchars($salon) . " - " . htmlspecialchars($profesor) . "<br>";
        }
    }
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Horarios Asignados al Grupo (Turno: <?= htmlspecialchars($turno); ?>)</h1> 
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title">Detalles del Horario</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <table id="example1" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Hora/Día</th>
                                                <?php foreach ($dias as $dia): ?>
                                                    <th><?= htmlspecialchars($dia); ?></th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($horas as $hora): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($hora); ?></td>
                                                    <?php foreach ($dias as $dia): ?>
                                                        <td><?= $tabla_horarios[$hora][$dia] ?? ''; ?></td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <a href="<?= APP_URL; ?>/admin/horarios_grupos" class="btn btn-secondary">Volver</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php
include('../../admin/layout/parte2.php');
include('../../layout/mensajes.php');
?>

<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 10,
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false,
            "dom": 'Bfrtip',
            buttons: [
                {
                    extend: 'collection',
                    text: 'Opciones',
                    buttons: [
                        'copy',
                        'csv',
                        {
                            text: 'PDF',
                            action: function () {
                                // Obtener los datos visibles de la tabla
                                let horarios = [];
                                $("#example1 tbody tr").each(function () {
                                    let fila = [];
                                    $(this).find('td').each(function () {
                                        fila.push($(this).html().trim());
                                    });
                                    horarios.push(fila);
                                });

                                // Enviar datos mediante POST al archivo PHP
                                $.ajax({
                                    url: '../../app/controllers/horarios_grupos/generar_pdf.php',
                                    method: 'POST',
                                    data: { horarios: horarios },
                                    xhrFields: {
                                        responseType: 'blob'
                                    },
                                    success: function (response) {
                                        // Descargar el archivo generado como PDF
                                        let blob = new Blob([response], { type: 'application/pdf' });
                                        let link = document.createElement('a');
                                        link.href = window.URL.createObjectURL(blob);
                                        link.download = "Horario_Personalizado.pdf";
                                        link.click();
                                    },
                                    error: function () {
                                        alert('Error al generar el PDF.');
                                    }
                                });
                            }
                        },
                        {
                            text: 'Imprimir',
                            action: function () {
                                // Obtener los datos visibles de la tabla
                                let horarios = [];
                                $("#example1 tbody tr").each(function () {
                                    let fila = [];
                                    $(this).find('td').each(function () {
                                        fila.push($(this).html().trim());
                                    });
                                    horarios.push(fila);
                                });

                                // Enviar los datos mediante POST y abrir la vista imprimible
                                $.post('../../app/controllers/horarios_grupos/imprimir_horario.php', { horarios: horarios }, function (data) {
                                    let w = window.open('');
                                    w.document.write(data);
                                    w.document.close();
                                });
                            }
                        },
                        {
                            text: 'Excel',
                            action: function () {
                                // Obtener los datos visibles de la tabla
                                let horarios = [];
                                $("#example1 tbody tr").each(function () {
                                    let fila = [];
                                    $(this).find('td').each(function () {
                                        fila.push($(this).html().trim());
                                    });
                                    horarios.push(fila);
                                });

                                // Enviar datos mediante POST al archivo PHP
                                $.ajax({
                                    url: '../../app/controllers/horarios_grupos/generar_horario.php',
                                    method: 'POST',
                                    data: { horarios: horarios },
                                    xhrFields: {
                                        responseType: 'blob'
                                    },
                                    success: function (response) {
                                        // Descargar el archivo generado como Excel
                                        let blob = new Blob([response], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                                        let link = document.createElement('a');
                                        link.href = window.URL.createObjectURL(blob);
                                        link.download = "Horario_Personalizado.xlsx";
                                        link.click();
                                    },
                                    error: function () {
                                        alert('Error al generar el archivo Excel.');
                                    }
                                });
                            }
                        }
                    ]
                },
                {
                    extend: 'colvis',
                    text: 'Visor de columnas'
                }
            ]
        });
    });
</script>


