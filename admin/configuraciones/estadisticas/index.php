<?php
include('../../../app/config.php');
include('../../../app/helpers/verificar_admin.php');
include('../../../admin/layout/parte1.php');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <br>
  <div class="content">
    <div class="container">
      <div class="row">
        <h1>Estadísticas del Sistema</h1>
      </div>
      <div class="row">
          
          <!-- Estadística: Suficiencia de Carga Horaria -->
          <div class="col-md-4 col-sm-6 col-12">
              <div class="info-box">
                  <span class="info-box-icon bg-info"><i class="bi bi-pie-chart-fill"></i></span>
                  <div class="info-box-content">
                      <span class="info-box-text"><b>Suficiencia General de Carga Horaria</b></span>
                      <a href="suficiencia_general_carga_horaria.php" class="btn btn-primary btn-sm">Ver Estadística</a>
                  </div>
              </div>
          </div>

          <!-- Estadística: Ejemplo de Otras Estadísticas Futuras -->
          <div class="col-md-4 col-sm-6 col-12">
              <div class="info-box">
                  <span class="info-box-icon bg-success"><i class="bi bi-bar-chart"></i></span>
                  <div class="info-box-content">
                      <span class="info-box-text"><b>Estadística de Prueba</b></span>
                      <a href="#" class="btn btn-primary btn-sm">Próximamente</a>
                  </div>
              </div>
          </div>

          <!-- Otra estadística futura -->
          <div class="col-md-4 col-sm-6 col-12">
              <div class="info-box">
                  <span class="info-box-icon bg-warning"><i class="bi bi-graph-up"></i></span>
                  <div class="info-box-content">
                      <span class="info-box-text"><b>Estadística en Desarrollo</b></span>
                      <a href="#" class="btn btn-primary btn-sm">Próximamente</a>
                  </div>
              </div>
          </div>

      </div>
    </div>
  </div>
</div>

<?php
include('../../../admin/layout/parte2.php');
include('../../../layout/mensajes.php');
?>
