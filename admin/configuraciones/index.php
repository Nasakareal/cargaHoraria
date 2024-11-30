<?php
include('../../app/config.php');
include('../../app/helpers/verificar_admin.php');
include('../../admin/layout/parte1.php');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <br>
  <div class="content">
    <div class="container">
      <div class="row">
        <h1>Configuraciones del sistema</h1>
      </div>
      <div class="row">
          
          <div class="col-md-4 col-sm-6 col-12">
              <div class="info-box">
                  <span class="info-box-icon bg-info"><i class="bi bi-building-exclamation"></i></span>
                  <div class="info-box-content">
                      <span class="info-box-text"><b>Datos de la Institución</b></span>
                      <a href="institucion" class="btn btn-primary btn-sm">Configurar</a>
                  </div>
              </div>
          </div>

          <!-- Configuración para añadir roles -->
          <div class="col-md-4 col-sm-6 col-12">
              <div class="info-box">
                  <span class="info-box-icon bg-navy"><i class="bi bi-bookmarks"></i></span>
                  <div class="info-box-content">
                      <span class="info-box-text"><b>Roles</b></span>
                      <a href="<?= APP_URL; ?>/admin/roles" class="btn btn-primary btn-sm">Acceder</a>
                  </div>
              </div>
          </div>

          <!-- Configuración para añadir usuarios -->
          <div class="col-md-4 col-sm-6 col-12">
              <div class="info-box">
                  <span class="info-box-icon bg-orange"><i class="bi bi-people-fill"></i></span>
                  <div class="info-box-content">
                      <span class="info-box-text"><b>Usuarios</b></span>
                      <a href="<?= APP_URL; ?>/admin/usuarios" class="btn btn-primary btn-sm">Acceder</a>
                  </div>
              </div>
          </div>

          <!-- Configuración para Vaciar Tablas -->
          <div class="col-md-4 col-sm-6 col-12">
              <div class="info-box">
                  <span class="info-box-icon bg-danger"><i class="bi bi-trash-fill"></i></span>
                  <div class="info-box-content">
                      <span class="info-box-text"><b>Vaciar Base de datos</b></span>
                      <a href="<?= APP_URL; ?>/admin/Vaciados" class="btn btn-primary btn-sm">Acceder</a>
                  </div>
              </div>
          </div>

          <!-- Configuración para Desactivar Usuarios -->
          <div class="col-md-4 col-sm-6 col-12">
              <div class="info-box">
                  <span class="info-box-icon bg-warning"><i class="bi bi-person-x"></i></span>
                  <div class="info-box-content">
                      <span class="info-box-text"><b>Desactivar Usuarios</b></span>
                      <a href="<?= APP_URL; ?>/app/controllers/configuraciones/desactivar_usuarios.php" class="btn btn-primary btn-sm">Ejecutar</a>
                  </div>
              </div>
          </div>

          <!-- Configuración para Activar Usuarios -->
          <div class="col-md-4 col-sm-6 col-12">
              <div class="info-box">
                  <span class="info-box-icon bg-orange"><i class="bi bi-person-fill-check"></i></span>
                  <div class="info-box-content">
                      <span class="info-box-text"><b>Activar Usuarios</b></span>
                      <a href="<?= APP_URL; ?>/app/controllers/configuraciones/activar_usuarios.php" class="btn btn-primary btn-sm">Ejecutar</a>
                  </div>
              </div>
          </div>

            <!-- Interfaz para Eliminar Materias -->
            <div class="col-md-4 col-sm-6 col-12">
                <div class="info-box">
                    <span class="info-box-icon bg-danger"><i class="bi bi-trash-fill"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text"><b>Eliminar Materias</b></span>
                        <a href="<?= APP_URL; ?>/admin/configuraciones/eliminar_materias_profesor" class="btn btn-primary btn-sm">Acceder</a>
                    </div>
                </div>
            </div>

            <!-- Interfaz para Estadísticas -->
            <div class="col-md-4 col-sm-6 col-12">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="bi bi-bar-chart-fill"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text"><b>Estadísticas</b></span>
                        <a href="<?= APP_URL; ?>/admin/configuraciones/estadisticas" class="btn btn-primary btn-sm">Acceder</a>
                    </div>
                </div>
            </div>

            <!-- Interfaz para Calendario Escolar -->
            <div class="col-md-4 col-sm-6 col-12">
                <div class="info-box">
                    <span class="info-box-icon bg-primary"><i class="bi bi-calendar2-week"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text"><b>Calendario Escolar</b></span>
                        <a href="<?= APP_URL; ?>/admin/configuraciones/calendarios" class="btn btn-primary btn-sm">Acceder</a>
                    </div>
                </div>
            </div

            <!-- Interfaz para Calendario Escolar -->
            <div class="col-md-4 col-sm-6 col-12">
                <div class="info-box">
                    <span class="info-box-icon bg-olive"><i class="bi bi-map"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text"><b>Mapa Escolar</b></span>
                        <a href="<?= APP_URL; ?>/admin/configuraciones/mapa" class="btn btn-primary btn-sm">Acceder</a>
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
