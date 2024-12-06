<?php

$lab_id = $_GET['id'] ?? null;

if (!$lab_id) {
    echo "ID del laboratorio no especificado.";
    exit;
}

include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/laboratorios/datos_del_laboratorio.php');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Laboratorio: <?= htmlspecialchars($lab_name); ?></h1>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title">Datos registrados</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="">Nombre del laboratorio</label>
                                        <p><?= htmlspecialchars($lab_name); ?></p>
                                    </div>
                                    <div class="form-group">
                                        <label for="">Descripción</label>
                                        <p><?= htmlspecialchars($description); ?></p>
                                    </div>
                                    <div class="form-group">
                                        <label for="">Fecha de creación</label>
                                        <p><?= htmlspecialchars($fyh_creacion); ?></p>
                                    </div>
                                    <div class="form-group">
                                        <label for="">Última actualización</label>
                                        <p><?= htmlspecialchars($fyh_actualizacion); ?></p>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <a href="<?= APP_URL; ?>/admin/laboratorios" class="btn btn-secondary">Volver</a>
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