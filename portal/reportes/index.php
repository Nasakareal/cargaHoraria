<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Informar un Problema</h1>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Nuevo Reporte</h3>
                        </div>
                        <form action="<?= APP_URL; ?>/app/controllers/reportes/enviar_reporte.php" method="post">
                            <div class="card-body">
                                <div class="row">
                                    <!-- Usuario que reporta -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="user_name"><strong>Usuario:</strong></label>
                                            <p><?= htmlspecialchars($nombre_sesion_usuario); ?></p>
                                            <input type="hidden" name="user_id" value="<?= $_SESSION['sesion_id_usuario']; ?>">
                                        </div>
                                    </div>
                                    
                                    <!-- Email del usuario -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="user_email"><strong>Email:</strong></label>
                                            <p><?= htmlspecialchars($email_sesion); ?></p>
                                            <input type="hidden" name="user_email" value="<?= htmlspecialchars($email_sesion); ?>">
                                        </div>
                                    </div>

                                    <!-- Asunto del reporte -->
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="report_subject"><strong>Asunto:</strong></label>
                                            <input type="text" name="report_subject" id="report_subject" class="form-control" placeholder="Breve descripción del problema" required>
                                        </div>
                                    </div>

                                    <!-- Mensaje del reporte -->
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="report_message"><strong>Mensaje:</strong></label>
                                            <textarea name="report_message" id="report_message" rows="6" class="form-control" placeholder="Describe el problema detalladamente" required></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Enviar Reporte
                                </button>
                                <a href="<?= APP_URL; ?>" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Volver
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div><!-- /.container -->
    </div><!-- /.content -->
</div><!-- /.content-wrapper -->

<?php
include('../../admin/layout/parte2.php');
include('../../layout/mensajes.php');
?>
