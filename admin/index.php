<?php
include('../app/config.php');
include('../app/helpers/verificar_admin.php');
include('../admin/layout/parte1.php');
include('../app/controllers/materias/obtener_materias.php');

?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <br>
    <div class="contentainer">
      <div class="container">
        <div class="row justify-content-center">
          <h1 class="text-center"><?= APP_NAME; ?></h1>
        </div>
        <br>
        <div class="row justify-content-center">
          <!-- Gráfico de Pastel -->
          <div class="col-md-6">
              <div class="card">
                  <div class="card-header">
                      <h3 class="card-title text-center">Materias Cubiertas</h3>
                  </div>
                  <div class="card-body">
                      <div class="d-flex justify-content-center">
                          <canvas id="materiasChart"></canvas>
                      </div>
                      <p class="mt-3 text-center">
                          <strong>Porcentaje de Materias Cubiertas:</strong> <?php echo $porcentaje_cubiertas; ?>%<br>
                          <strong>Porcentaje de Materias No Cubiertas:</strong> <?php echo $porcentaje_no_cubiertas; ?>%
                      </p>
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
include('../admin/layout/parte2.php');
include('../layout/mensajes.php');
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var ctx = document.getElementById('materiasChart').getContext('2d');
        var materiasChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Cubierta', 'No Cubierta'],
                datasets: [{
                    data: [<?php echo $materias_cubiertas; ?>, <?php echo $materias_no_cubiertas; ?>],
                    backgroundColor: ['#008080', '#A9A9A9'],
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                var label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.raw;
                                return label;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
