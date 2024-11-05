<?php
include('../../app/config.php');
include('../../layout/parte1.php');

/* Obtener el ID del profesor de la URL */
$teacher_id = filter_input(INPUT_GET, 'teacher_id', FILTER_VALIDATE_INT);

/* Verificar si el ID es válido */
if (!$teacher_id) {
    echo "ID de profesor inválido.";
    exit;
}

/* Consulta para obtener el nombre del profesor y verificar si tiene materias asignadas */
$sql_profesor = "SELECT teacher_name FROM teachers WHERE teacher_id = :teacher_id";
$stmt_profesor = $pdo->prepare($sql_profesor);
$stmt_profesor->execute([':teacher_id' => $teacher_id]);
$profesor = $stmt_profesor->fetch(PDO::FETCH_ASSOC);

if (!$profesor) {
    echo "Profesor no encontrado.";
    exit;
}

/* Consulta para obtener los horarios solo de las materias asignadas al profesor */
$sql_horarios = "SELECT sa.assignment_id, sa.schedule_day, sa.start_time, sa.end_time, s.subject_name
                 FROM schedule_assignments sa
                 JOIN subjects s ON sa.subject_id = s.subject_id
                 JOIN teacher_subjects ts ON ts.subject_id = s.subject_id
                 WHERE ts.teacher_id = :teacher_id AND sa.teacher_id IS NULL AND sa.estado = 'activo'
                 ORDER BY sa.schedule_day, sa.start_time";
$stmt_horarios = $pdo->prepare($sql_horarios);
$stmt_horarios->execute([':teacher_id' => $teacher_id]);
$horarios = $stmt_horarios->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-wrapper">
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Asignar Horario a <?= htmlspecialchars($profesor['teacher_name']); ?></h1>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Horarios Disponibles</h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($horarios)) : ?>
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Día</th>
                                            <th>Hora de Inicio</th>
                                            <th>Hora de Fin</th>
                                            <th>Materia</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($horarios as $horario) : ?>
                                        <tr>
                                            <td><?= htmlspecialchars($horario['schedule_day']); ?></td>
                                            <td><?= htmlspecialchars($horario['start_time']); ?></td>
                                            <td><?= htmlspecialchars($horario['end_time']); ?></td>
                                            <td><?= htmlspecialchars($horario['subject_name']); ?></td>
                                            <td>
                                                <!-- Botón para asignar el horario al profesor -->
                                                <a href="actualizar_horario.php?assignment_id=<?= $horario['assignment_id']; ?>&teacher_id=<?= $teacher_id; ?>" 
                                                   class="btn btn-primary btn-sm">
                                                    Asignar
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else : ?>
                                <p>No hay horarios disponibles para asignar.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include('../../layout/parte2.php');
include('../../layout/mensajes.php');
?>
