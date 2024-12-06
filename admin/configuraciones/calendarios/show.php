<?php
// Obtener el ID del calendario desde la URL
$id_calendario = $_GET['id'] ?? null;

if (!$id_calendario) {
    echo "<script>
        alert('Calendario no especificado.');
        window.location.href = 'index.php';
    </script>";
    exit;
}

include('../../../app/config.php');
include('../../../admin/layout/parte1.php');
include('../../../app/helpers/verificar_admin.php');
include('../../../app/controllers/calendario_escolar/datos_del_calendario.php');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Detalles del Calendario</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Calendarios</a></li>
                        <li class="breadcrumb-item active">Detalles</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Información del Calendario</h3>
                            <div class="card-tools">
                                <a href="index.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left-circle"></i> Volver</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-4">Nombre del Cuatrimestre:</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($calendario['nombre_cuatrimestre']); ?></dd>

                                <dt class="col-sm-4">Fecha de Inicio:</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($calendario['fecha_inicio']); ?></dd>

                                <dt class="col-sm-4">Fecha de Fin:</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($calendario['fecha_fin']); ?></dd>
                            </dl>

                            <div class="col-md-12">
                                <div id="calendar"></div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>




    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            buttonText: {
                today: 'Hoy',
                month: 'Mes',
                week: 'Semana',
                day: 'Día'
            },
            events: [
                {
                    title: 'Inicio de Cuatrimestre',
                    start: '<?= $calendario['fecha_inicio']; ?>',
                    color: 'green'
                },
                {
                    title: 'Fin de Cuatrimestre',
                    start: '<?= $calendario['fecha_fin']; ?>',
                    color: 'red'
                }
            ],
            dayCellDidMount: function (info) {
                /* Colorear los domingos */
                var date = info.date;
                var day = date.getDay();
                if (day === 0) {
                    info.el.style.backgroundColor = '#c9c7c7';
                }

                /* Definir días inhábiles en México */
                var diasInhabiles = [
                    '2024-01-01', /* Año Nuevo */
                    '2024-02-05', /* Día de la Constitución */
                    '2024-03-18', /* Natalicio de Benito Juárez */
                    '2024-05-01', /* Día del Trabajo */
                    '2024-09-16', /* Día de la Independencia */
                    '2024-11-20', /* Revolución Mexicana */
                    '2024-12-25', /* Navidad */
                ];

                var formattedDate = date.toISOString().split('T')[0];
                if (diasInhabiles.includes(formattedDate)) {
                    info.el.style.backgroundColor = '#ffcccc';
                }
            }
        });

        calendar.render();
    });
</script>


<?php
include('../../../admin/layout/parte2.php');
?>
