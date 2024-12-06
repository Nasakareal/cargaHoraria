<?php
$id_usuario = $_GET['id'];

include('../../app/config.php');
include('../../admin/layout/parte1.php');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Elegir foto de perfil</h1>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title">Seleccione un avatar</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?= APP_URL; ?>/app/controllers/usuarios/actualizar_foto.php" method="post">
                                <input type="hidden" name="id_usuario" value="<?= $id_usuario; ?>">
                                <div class="row">
                                    <?php
                                    
                                    $rutaAvatares = APP_URL . '/public/dist/img/avatar/';
                                    $directorio = '../../public/dist/img/avatar/';
                                    $avatares = scandir($directorio);

                                    
                                    $avatares = array_filter($avatares, function ($archivo) {
                                        return in_array(pathinfo($archivo, PATHINFO_EXTENSION), ['jpg', 'png', 'jpeg']);
                                    });

                                    
                                    foreach ($avatares as $avatar) { ?>
                                        <div class="col-md-2 text-center">
                                            <label>
                                                <input type="radio" name="avatar" value="<?= $rutaAvatares . $avatar; ?>" required>
                                                <img src="<?= $rutaAvatares . $avatar; ?>" alt="Avatar" class="img-thumbnail" style="width: 100px; height: 100px;">
                                            </label>
                                        </div>
                                    <?php } ?>
                                </div>
                                <hr>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">Guardar</button>
                                    <a href="<?= APP_URL; ?>/admin/usuarios" class="btn btn-secondary">Cancelar</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include('../../admin/layout/parte2.php');
include('../../layout/mensajes.php');
?>
