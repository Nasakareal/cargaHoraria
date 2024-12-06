<?php
/* Validar teacher_id */
$teacher_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$teacher_id) {
    echo "ID de profesor inválido.";
    exit;
}

include('../../app/config.php');
include('../../admin/layout/parte1.php');

/* Actualizar la consulta SQL para manejar registros con classroom_id NULL */
$sql_horarios = "SELECT 
                    sa.schedule_day, 
                    sa.start_time, 
                    sa.end_time, 
                    s.subject_name, 
                    g.group_name, 
                    COALESCE(r.classroom_name, 'Sin aula') AS classroom_name
                 FROM 
                    schedule_assignments sa
                 JOIN 
                    subjects s ON sa.subject_id = s.subject_id
                 JOIN 
                    `groups` g ON sa.group_id = g.group_id
                 LEFT JOIN 
                    classrooms r ON sa.classroom_id = r.classroom_id
                 WHERE 
                    sa.teacher_id = :teacher_id
                 ORDER BY sa.schedule_day, sa.start_time";

$query_horarios = $pdo->prepare($sql_horarios);
$query_horarios->execute([':teacher_id' => $teacher_id]);
$horarios = $query_horarios->fetchAll(PDO::FETCH_ASSOC);

if (!$horarios) {
    echo "No se encontraron horarios asignados para este profesor.";
    exit;
}

/* Definir los horarios y días */
$horas = ['07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00'];
$dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

/* Inicializar una matriz vacía para los horarios */
$tabla_horarios = [];
foreach ($horas as $hora) {
    foreach ($dias as $dia) {
        $tabla_horarios[$hora][$dia] = '';  
    }
}

/* Llenar la tabla con los horarios asignados */
foreach ($horarios as $horario) {
    $start_time = date('H:i', strtotime($horario['start_time']));
    $end_time = date('H:i', strtotime($horario['end_time']));
    $dia = ucfirst(strtolower($horario['schedule_day'])); // Asegura coincidencia con los días definidos
    $materia = $horario['subject_name'];
    $grupo = $horario['group_name'];
    $salon = $horario['classroom_name'];

    $detalle_clase = htmlspecialchars("$materia (Grupo: $grupo, Salón: $salon)");

    // Llenar cada hora dentro del rango de inicio y fin
    foreach ($horas as $hora) {
        if ($hora >= $start_time && $hora < $end_time) {
            // Verificar que el día esté en los días definidos
            if (in_array($dia, $dias)) {
                // Concatenar si ya existe información
                if (!empty($tabla_horarios[$hora][$dia])) {
                    $tabla_horarios[$hora][$dia] .= "<br>" . $detalle_clase;
                } else {
                    $tabla_horarios[$hora][$dia] = $detalle_clase;
                }
            }
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
                <h1>Horarios Asignados al Profesor</h1> 
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
                                                    <th><?= $dia; ?></th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($horas as $hora): ?>
                                                <tr>
                                                    <td><?= $hora; ?></td>
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
                                        <a href="<?= APP_URL; ?>/admin/horarios_profesores" class="btn btn-secondary">Volver</a>
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
                                
                                let horarios = [];
                                $("#example1 tbody tr").each(function () {
                                    let fila = [];
                                    $(this).find('td').each(function () {
                                        fila.push($(this).html().trim());
                                    });
                                    horarios.push(fila);
                                });

                                
                                $.ajax({
                                    url: '../../app/controllers/horarios_grupos/generar_pdf.php',
                                    method: 'POST',
                                    data: { horarios: horarios },
                                    xhrFields: {
                                        responseType: 'blob'
                                    },
                                    success: function (response) {
                                        
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
                                
                                let horarios = [];
                                $("#example1 tbody tr").each(function () {
                                    let fila = [];
                                    $(this).find('td').each(function () {
                                        fila.push($(this).html().trim());
                                    });
                                    horarios.push(fila);
                                });

                                
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
                                
                                let horarios = [];
                                $("#example1 tbody tr").each(function () {
                                    let fila = [];
                                    $(this).find('td').each(function () {
                                        fila.push($(this).html().trim());
                                    });
                                    horarios.push(fila);
                                });

                                
                                $.ajax({
                                    url: '../../app/controllers/horarios_grupos/generar_horario.php',
                                    method: 'POST',
                                    data: { horarios: horarios },
                                    xhrFields: {
                                        responseType: 'blob'
                                    },
                                    success: function (response) {
                                        
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
