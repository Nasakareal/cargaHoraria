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
                <h1>Editar el laboratorio: <?= $lab_name; ?></h1>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title">Datos registrados</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?= APP_URL; ?>/app/controllers/laboratorios/update.php" method="post">
                                <div class="row">
                                    <!-- Campo oculto para el ID del laboratorio -->
                                    <input type="hidden" name="lab_id" value="<?= $lab_id; ?>">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="lab_name">Nombre del laboratorio</label>
                                            <input type="text" class="form-control" id="lab_name" name="lab_name" value="<?= $lab_name; ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="description">Descripción</label>
                                            <textarea class="form-control" id="description" name="description" rows="4"><?= $description; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">Guardar</button>
                                            <a href="<?= APP_URL; ?>/admin/laboratorios" class="btn btn-secondary">Cancelar</a>
                                        </div>
                                    </div>
                                </div>
                            </form>
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
