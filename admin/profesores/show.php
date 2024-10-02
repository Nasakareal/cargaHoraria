<?php

/* Filtra y valida el teacher_id */

$teacher_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$teacher_id) {
    echo "ID de profesor inválido.";
    exit;
}

<<<<<<< HEAD
include ('../../app/config.php');
include ('../../admin/layout/parte1.php');
include ('../../app/controllers/profesores/datos_del_profesor.php');
=======
include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/profesores/datos_del_profesor.php');
>>>>>>> 09dfda8 (descagada)

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
<<<<<<< HEAD
                <h1>Profesor: <?=$nombres;?></h1> 
=======
                <h1>Profesor: <?= $nombres; ?></h1> 
>>>>>>> 09dfda8 (descagada)
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
<<<<<<< HEAD
                                        <p><?=$nombres;?></p>
=======
                                        <p><?= $nombres; ?></p>
>>>>>>> 09dfda8 (descagada)
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
<<<<<<< HEAD
                                        <label for="">Materia</label>
                                        <p><?=$materias;?></p> 
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Horas Semanales</label>
                                        <p><?=$horas_semanales;?></p> 
=======
                                        <label for="">Materias</label>
                                        <p>
                                            <?php
                                            if (!empty($materias)) {
                                                foreach ($materias as $materia) {
                                                    echo $materia['subject_name'] . " (" . $materia['weekly_hours'] . " horas semanales)<br>";
                                                }
                                            } else {
                                                echo "No tiene materias asignadas.";
                                            }
                                            ?>
                                        </p>
>>>>>>> 09dfda8 (descagada)
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
<<<<<<< HEAD
                                        <a href="<?=APP_URL;?>/admin/profesores" class="btn btn-secondary">Volver</a>
=======
                                        <a href="<?= APP_URL; ?>/admin/profesores" class="btn btn-secondary">Volver</a>
>>>>>>> 09dfda8 (descagada)
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

<<<<<<< HEAD
<?php 
include ('../../admin/layout/parte2.php');
include ('../../layout/mensajes.php');
=======
<?php
include('../../admin/layout/parte2.php');
include('../../layout/mensajes.php');
>>>>>>> 09dfda8 (descagada)
?>
