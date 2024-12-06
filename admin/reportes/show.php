<?php

$report_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$report_id) {
    header('Location: ' . APP_URL . '/app/reports/listado_notificaciones.php');
    exit;
}

include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/reportes/datos_de_notificacion.php');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Detalle de la Notificación</h1> 
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title">Detalles de la Notificación</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Remitente -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for=""><strong>De:</strong></label>
                                        <p><?= htmlspecialchars($report['user_name']); ?></p>
                                    </div>
                                </div>
                                
                                <!-- Fecha de creación -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for=""><strong>Fecha:</strong></label>
                                        <p><?= date('d-m-Y H:i:s', strtotime($report['created_at'])); ?></p>
                                    </div>
                                </div>

                                <!-- Asunto -->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for=""><strong>Asunto:</strong></label>
                                        <p>Notificación del Sistema</p>
                                    </div>
                                </div>

                                <!-- Contenido del mensaje -->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for=""><strong>Mensaje:</strong></label>
                                        <div class="p-3 border rounded" style="background-color: #f9f9f9; border: 1px solid #ddd;">
                                            <?= nl2br(htmlspecialchars($report['report_message'])); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <div class="row">
                                <div class="col-md-12 text-left">
                                    <a href="<?= APP_URL; ?>/admin/reportes/index.php" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Volver
                                    </a>
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
?>
