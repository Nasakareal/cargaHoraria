<?php
/* Filtrar y validar el group_id */
$group_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$group_id) {
    echo "ID de grupo inválido.";
    exit;
}

include ('../../app/config.php');
include ('../../admin/layout/parte1.php');

/* Consulta para obtener los detalles del horario asignado al grupo y el turno */
$sql_horarios = "SELECT 
                    sa.schedule_day, 
                    sa.start_time, 
                    sa.end_time, 
                    s.subject_name, 
                    g.turn_id, 
                    sh.shift_name 
                 FROM 
                    schedule_assignments sa
                 JOIN 
                    subjects s ON sa.subject_id = s.subject_id
                 JOIN 
                    `groups` g ON sa.group_id = g.group_id
                 JOIN 
                    shifts sh ON g.turn_id = sh.shift_id
                 WHERE 
                    sa.group_id = :group_id
                 ORDER BY sa.schedule_day, sa.start_time";

$query_horarios = $pdo->prepare($sql_horarios);
$query_horarios->execute([':group_id' => $group_id]);
$horarios = $query_horarios->fetchAll(PDO::FETCH_ASSOC);

if (!$horarios) {
    echo "No se encontraron horarios asignados para este grupo.";
    exit;
}

/* Obtenemos el turno del grupo */
$turno = $horarios[0]['shift_name'];

/* Definir los horarios y días según el turno */
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

/* Inicializamos una matriz vacía para los horarios */
$tabla_horarios = [];

/* Rellenamos la tabla con horarios vacíos */
foreach ($horas as $hora) {
    foreach ($dias as $dia) {
        $tabla_horarios[$hora][$dia] = '';  /* Inicializamos las celdas vacías */
    }
}

/* Rellenar la tabla con los horarios asignados */
foreach ($horarios as $horario) {
    $start_time = date('H:i', strtotime($horario['start_time']));
    $end_time = date('H:i', strtotime($horario['end_time']));
    $dia = $horario['schedule_day'];
    $materia = $horario['subject_name'];

    /* Asignar la materia a las horas correspondientes en el día */
    foreach ($horas as $hora) {
        if ($hora >= $start_time && $hora < $end_time && in_array($dia, $dias)) {
            $tabla_horarios[$hora][$dia] = htmlspecialchars($materia);
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
                <h1>Horarios Asignados al Grupo (Turno: <?= $turno; ?>)</h1> 
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
                                    <table class="table table-bordered table-hover">
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
include ('../../admin/layout/parte2.php');
?>
