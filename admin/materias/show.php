<?php

/* Filtra y valida el subject_id */
$subject_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$subject_id) {
    echo "ID de materia inválido.";
    exit;
}

include ('../../app/config.php');
include ('../../admin/layout/parte1.php');

/* Realiza la consulta para obtener los datos de la materia */
$sql_materias = "SELECT 
                    s.subject_name, 
                    s.hours_consecutive, 
                    s.weekly_hours, 
                    s.is_specialization, 
                    p.program_name, 
                    t.term_name 
                 FROM 
                    subjects s 
                 LEFT JOIN 
                    programs p ON s.program_id = p.program_id 
                 LEFT JOIN 
                    terms t ON s.term_id = t.term_id 
                 WHERE 
                    s.subject_id = :subject_id";

$query_materias = $pdo->prepare($sql_materias);
$query_materias->execute([':subject_id' => $subject_id]);
$materia = $query_materias->fetch(PDO::FETCH_ASSOC);

if (!$materia) {
    echo "Materia no encontrada.";
    exit;
}

$subject_name = $materia['subject_name'];
$hours_consecutive = $materia['hours_consecutive'];
$weekly_hours = $materia['weekly_hours'];
$is_specialization = $materia['is_specialization'] ? 'Sí' : 'No';
$program_name = $materia['program_name'] ?? 'No asignado';
$term_name = $materia['term_name'] ?? 'No asignado';

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Materia: <?= htmlspecialchars($subject_name); ?></h1> 
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title">Datos de la Materia</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Nombre de la Materia</label>
                                        <p><?= htmlspecialchars($subject_name); ?></p> 
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Horas Consecutivas</label>
                                        <p><?= $hours_consecutive; ?></p> 
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Horas Semanales</label>
                                        <p><?= $weekly_hours; ?></p> 
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Es Especialización?</label>
                                        <p><?= $is_specialization; ?></p> 
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Programa Educativo</label>
                                        <p><?= htmlspecialchars($program_name); ?></p> 
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Cuatrimestre</label>
                                        <p><?= htmlspecialchars($term_name); ?></p> 
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <a href="<?= APP_URL; ?>/admin/materias" class="btn btn-secondary">Volver</a>
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
include ('../../layout/mensajes.php');
?>
