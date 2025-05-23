<?php
include('../app/config.php');
include('../admin/layout/parte1.php');
include('../app/controllers/materias/obtener_materias.php');
include('../app/controllers/materias/programas_no_asignados.php');

$grupos_all                    = $grupos_all ?? [];
$grupos_con_materias_faltantes = $grupos_con_materias_faltantes ?? [];
$materias_cubiertas    = array_sum(array_column($grupos_all, 'materias_asignadas'));
$materias_no_cubiertas = array_sum(array_column($grupos_all, 'materias_no_cubiertas'));

$total_materias        = $materias_cubiertas + $materias_no_cubiertas;
$porcentaje_cubiertas  = $total_materias
    ? round(($materias_cubiertas / $total_materias) * 100, 2)
    : 0;
$porcentaje_no_cubiertas = 100 - $porcentaje_cubiertas;
?>

<div class="content-wrapper"><br>
  <div class="container">
    <div class="row justify-content-center">
      <h1 class="text-center"><?= APP_NAME; ?></h1>
    </div><br>

    <div class="row justify-content-center">
      <!-- Gráfico -->
      <div class="col-md-5">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title text-center">Materias Cubiertas</h3>
          </div>
          <div class="card-body">
            <canvas id="materiasChart"></canvas>
            <p class="mt-3 text-center">
              <strong>% Cubiertas:</strong> <?= $porcentaje_cubiertas; ?>%<br>
              <strong>% No Cubiertas:</strong> <?= $porcentaje_no_cubiertas; ?>%
            </p>
          </div>
        </div>
      </div>

      <!-- Tabla de faltantes -->
      <div class="col-md-7">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title text-center">Grupos con Materias Sin Profesor</h3>
          </div>
          <div class="card-body">
            <p class="text-center">
              <strong>Total de Materias Faltantes:</strong> <?= $materias_no_cubiertas; ?>
            </p>

            <?php if (!empty($grupos_con_materias_faltantes)): ?>
              <table id="listadoMaterias" class="table table-striped table-bordered">
                <thead>
                  <tr>
                    <th>Grupo</th>
                    <th>Materias Sin Profesor</th>
                    <th># Faltantes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($grupos_con_materias_faltantes as $g): ?>
                    <tr>
                      <td>
                        <?= htmlspecialchars($g['grupo'] ?? '–', ENT_QUOTES, 'UTF-8') ?>
                      </td>
                      <td>
                        <?= htmlspecialchars($g['materias_faltantes'] ?? '–', ENT_QUOTES, 'UTF-8') ?>
                      </td>
                      <td>
                        <?= (int) $g['materias_no_cubiertas'] ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php else: ?>
              <p class="text-center">
                Todos los grupos tienen sus materias asignadas a profesores.
              </p>
            <?php endif; ?>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
include('../admin/layout/parte2.php');
include('../layout/mensajes.php');
?>

<!-- Bibliotecas JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Biblioteca de Confeti -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
<!-- jQuery y DataTables (asegúrate de que jQuery esté incluido antes de DataTables) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">

<!-- Reproductor de audio para la celebración -->
<audio id="audioCelebracion" src="../public/grunt.mp3"></audio>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var ctx = document.getElementById('materiasChart').getContext('2d');
        var porcentajeCubiertas = <?php echo $porcentaje_cubiertas; ?>;
        var porcentajeNoCubiertas = <?php echo $porcentaje_no_cubiertas; ?>;
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
                },
                animation: {
                    onComplete: function () {
                        if (porcentajeCubiertas === 100) {
                            lanzarConfeti();
                        }
                    }
                }
            }
        });
    });

    function lanzarConfeti() {
        const duracion = 15000;
        const animacionBase = {
            startVelocity: 30,
            spread: 360,
            ticks: 60,
            zIndex: 1000
        };

        const audio = document.getElementById('audioCelebracion');
        audio.play();

        const intervalo = setInterval(() => {
            const randomX = Math.random();
            const randomY = Math.random();
            const randomAngle = Math.random() * 360;

            confetti({
                ...animacionBase,
                origin: { x: randomX, y: randomY },
                angle: randomAngle
            });
        }, 250);

        setTimeout(() => {
            clearInterval(intervalo);
            confetti.reset();
            audio.pause();
            audio.currentTime = 0;
        }, duracion);
    }
</script>


<script>
    $(document).ready(function () {
        $('#listadoMaterias').DataTable({
            "pageLength": 5,
            "language": {
                "emptyTable": "No hay grupos con materias sin profesor",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ grupos",
                "infoEmpty": "Mostrando 0 a 0 de 0 grupos",
                "infoFiltered": "(Filtrado de _MAX_ grupos en total)",
                "lengthMenu": "Mostrar _MENU_ grupos",
                "search": "Buscar:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false
        });
    });
</script>
