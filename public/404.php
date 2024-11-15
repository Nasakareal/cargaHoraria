<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <h1 class="display-1 text-danger">404</h1>
                    <h2>¡Página no encontrada!</h2>
                    <p>Lo sentimos, no pudimos encontrar la página que buscas.</p>
                    <a href="<?= APP_URL; ?>" class="btn btn-primary">Volver al inicio</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include('../../admin/layout/parte2.php');
?>
