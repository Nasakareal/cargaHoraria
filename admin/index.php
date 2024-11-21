<?php
include('../app/config.php');
include('../admin/layout/parte1.php');
include('../app/controllers/materias/obtener_materias.php');
include('../app/controllers/materias/programas_no_asignados.php');

// Validación y conteo
$grupos_materias_faltantes = isset($grupos_materias_faltantes) ? $grupos_materias_faltantes : [];
$total_grupos = count($grupos_materias_faltantes);

$materias_cubiertas = 0;
$materias_no_cubiertas = 0;

// Calcular totales para el gráfico
foreach ($grupos_materias_faltantes as $grupo) {
    $materias_cubiertas += $grupo['materias_asignadas'];
    $materias_no_cubiertas += $grupo['materias_no_cubiertas'];
}

$total_materias = $materias_cubiertas + $materias_no_cubiertas;
$porcentaje_cubiertas = $total_materias > 0 ? round(($materias_cubiertas / $total_materias) * 100, 2) : 0;
$porcentaje_no_cubiertas = 100 - $porcentaje_cubiertas;
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <br>
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

            <!-- Listado de Grupos con Materias No Asignadas -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title text-center">Grupos con Materias Sin Profesor</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-center"><strong>Total de Grupos Faltantes:</strong> <?php echo $total_grupos; ?></p>
                        <p class="text-center"><strong>Total de Materias Faltantes:</strong> <?php echo $materias_no_cubiertas; ?></p>

                        <?php if (!empty($grupos_materias_faltantes)): ?>
                            <ul class="list-group">
                                <?php foreach ($grupos_materias_faltantes as $grupo): ?>
                                    <li class="list-group-item">
                                        <strong><?php echo htmlspecialchars($grupo['grupo']); ?>:</strong><br>
                                        <strong>Materias Sin Profesor:</strong> <?php echo htmlspecialchars($grupo['materias_faltantes']); ?><br>
                                        <strong>Total Materias:</strong> <?php echo $grupo['total_materias']; ?><br>
                                        <strong>Materias Asignadas:</strong> <?php echo $grupo['materias_asignadas']; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-center">Todos los grupos tienen sus materias asignadas a profesores.</p>
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
