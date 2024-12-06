<?php

$id_usuario = $_GET['id'];

include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/usuarios/datos_del_usuario.php');
?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <br>
    <div class="content">
      <div class="container">
        <div class="row">
          <h1>Usuario: <?= $nombres; ?></h1>
        </div>
        <div class="row">
        
            <div class="col-md-12">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title">Datos del usuario</h3>
                    </div>
                    <div class="card-body">

                    <div class="row">
                      <!-- Sección para mostrar la foto de perfil -->
                      <div class="col-md-4 text-center">
                        <div class="form-group">
                          <label for="">Foto de perfil</label>
                          <div style="position: relative; display: inline-block;">
                            <img src="<?= htmlspecialchars($foto_perfil); ?>" alt="Foto de perfil" class="img-thumbnail" style="width: 150px; height: 150px;">
                            <a href="<?= APP_URL; ?>/admin/usuarios/cambiar_foto.php?id=<?= $id_usuario; ?>" 
                               style="position: absolute; top: 5px; right: 5px; background: rgba(0,0,0,0.5); border-radius: 50%; padding: 5px;">
                              <i class="bi bi-pencil text-white"></i>
                            </a>
                          </div>
                        </div>
                      </div>

                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="">Nombre del rol</label>
                          <p><?= $nombre_rol; ?></p>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="">Nombres del usuario</label>
                          <p><?= $nombres; ?></p>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                    
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="">Email</label>
                          <p><?= $email; ?></p>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="">Fecha y Hora de creación</label>
                          <p><?= $fyh_creacion; ?></p>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="">Estado</label>
                          <p><?php
                          if ($estado == '1') {
                              echo "ACTIVO";
                          } else {
                              echo "INACTIVO";
                          } ?></p>
                        </div>
                      </div>
                    </div>
                    <hr>
                    <div class="row">
                      <div class="col-md-12">
                        <div class="form-group">
                          <a href="<?= APP_URL; ?>/admin/usuarios" class="btn btn-secondary">Volver</a>
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
