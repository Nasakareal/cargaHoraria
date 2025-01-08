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

            <!-- Configuración para Activar/Desactivar Usuarios -->
            <div class="col-md-4 col-sm-6 col-12">
                <div class="info-box">
                    <span class="info-box-icon bg-orange">
                        <!-- Ícono dinámico -->
                        <i id="toggle-icon" class="bi bi-person-x"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text"><b id="toggle-text">Activar Usuarios</b></span>
                        <!-- Toggle Switch -->
                        <label class="switch">
                            <input type="checkbox" id="toggle-switch">
                            <span class="slider"></span>
                        </label>
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

            <!-- Interfaz para el Mapa Escolar -->
            <div class="col-md-4 col-sm-6 col-12">
                <div class="info-box">
                    <span class="info-box-icon bg-olive"><i class="bi bi-map"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text"><b>Mapa Escolar</b></span>
                        <a href="<?= APP_URL; ?>/admin/configuraciones/mapa" class="btn btn-primary btn-sm">Acceder</a>
                    </div>
                </div>
            </div>

            <!-- Interfaz para Registro de actividades -->
            <div class="col-md-4 col-sm-6 col-12">
                <div class="info-box">
                    <span class="info-box-icon bg-gray"><i class="bi bi-clock-history"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text"><b>Registro de actividades</b></span>
                        <a href="<?= APP_URL; ?>/admin/configuraciones/registro_de_actividades" class="btn btn-primary btn-sm">Acceder</a>
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

<style>
/* Estilo para el toggle switch */
.switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 25px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 25px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 19px;
    width: 19px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: #28a745;
}

input:checked + .slider:before {
    transform: translateX(24px);
}

/* Estilo para las etiquetas debajo del toggle */
.toggle-labels {
    margin-top: 10px;
    font-size: 12px;
    color: #555;
    display: flex;
    justify-content: space-between;
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const toggleSwitch = document.getElementById("toggle-switch");
    const icon = document.getElementById("toggle-icon");
    const text = document.getElementById("toggle-text");

    // Cargar estado guardado
    const estadoGuardado = localStorage.getItem("estadoUsuarios");
    if (estadoGuardado === "activar") {
        toggleSwitch.checked = true;
        icon.className = "bi bi-person-fill-check";
        text.textContent = "Desactivar Usuarios";
    } else {
        toggleSwitch.checked = false;
        icon.className = "bi bi-person-x";
        text.textContent = "Activar Usuarios";
    }

    // Guardar estado al cambiar
    toggleSwitch.addEventListener("change", function () {
        if (this.checked) {
            icon.className = "bi bi-person-fill-check";
            text.textContent = "Desactivar Usuarios";
            localStorage.setItem("estadoUsuarios", "activar");
            // Redirigir al script para activar usuarios
            window.location.href = "<?= APP_URL; ?>/app/controllers/configuraciones/activar_usuarios.php";
        } else {
            icon.className = "bi bi-person-x";
            text.textContent = "Activar Usuarios";
            localStorage.setItem("estadoUsuarios", "desactivar");
            // Redirigir al script para desactivar usuarios
            window.location.href = "<?= APP_URL; ?>/app/controllers/configuraciones/desactivar_usuarios.php";
        }
    });
});
</script>