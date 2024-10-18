<?php

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
                <h1>Profesor: <?= $nombres; ?></h1> 
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title">Datos del profesor</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Nombres del profesor -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Nombres del profesor</label>
                                        <p><?= $nombres; ?></p>
                                    </div>
                                </div>

                                <!-- Local o Foráneo -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Local/Foráneo</label>
                                        <p><?= $es_local; ?></p>  <!-- Nueva columna -->
                                    </div>
                                </div>

                                <!-- Materia -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Materia</label>
                                        <p><?= $materias; ?></p> 
                                    </div>
                                </div>

                                <!-- Horas Semanales -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Horas Semanales</label>
                                        <p><?= $horas_semanales; ?></p> 
                                    </div>
                                </div>

                                <!-- Programa -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Programa</label>
                                        <p><?= $programa; ?></p>
                                    </div>
                                </div>

                                <!-- Cuatrimestre -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Cuatrimestre</label>
                                        <p><?= $cuatrimestre; ?></p>
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
