<?php

/* Filtra y valida el teacher_id */
$teacher_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$teacher_id) {
    echo "ID de profesor inválido.";
    exit;
}

include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/profesores/datos_del_profesor.php');

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Profesor: <?= htmlspecialchars($nombres); ?></h1> 
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title">Datos del profesor</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Nombres del profesor</label>
                                        <p><?= htmlspecialchars($nombres); ?></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Materias</label>
                                        <p>
                                            <?php
                                            if (!empty($materias)) {
                                                echo "<ul>";
                                                foreach ($materias as $materia) {
                                                    echo "<li>" . htmlspecialchars($materia['subject_name'] ?? 'Materia no especificada') . " (" . htmlspecialchars($materia['weekly_hours'] ?? 0) . " horas semanales)</li>";
                                                }
                                                echo "</ul>";
                                            } else {
                                                echo "No tiene materias asignadas.";
                                            }
                                            ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Horas Semanales</label>
                                        <p><?= ($weekly_hours ?? 0); ?></p> 
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <a href="<?= APP_URL; ?>/admin/profesores" class="btn btn-secondary">Volver</a>
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
