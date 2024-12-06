<?php
include('../../../app/config.php');
include('../../../admin/layout/parte1.php');
include('../../../app/controllers/estadisticas/suficiencia_carga_horaria.php');

// Obtener calendarios disponibles
$query_calendarios = "SELECT id, nombre_cuatrimestre FROM calendario_escolar WHERE estado = 'ACTIVO'";
$stmt_calendarios = $pdo->prepare($query_calendarios);
$stmt_calendarios->execute();
$calendarios = $stmt_calendarios->fetchAll(PDO::FETCH_ASSOC);

// Obtener calendario seleccionado (por defecto, ninguno)
$calendar_id = filter_input(INPUT_GET, 'calendar_id', FILTER_VALIDATE_INT);

// Inicializar totales
$total_grupos = 0;
$total_horas_totales_asignatura = 0;
$total_asignaturas = 0;
$total_numero_horas_totales = 0;
$total_numero_hsm = 0;

// Calcular totales de las columnas
foreach ($estadisticas as $fila) {
    $total_grupos += $fila['grupos'] ?? 0;
    $total_horas_totales_asignatura += $fila['horas_totales_asignatura'] ?? 0;
    $total_asignaturas += $fila['asignaturas'] ?? 0;
    $total_numero_horas_totales += $fila['horas_totales_asignatura'] ?? 0;
    $total_numero_hsm += $fila['numero_hsm'] ?? 0;
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Estadísticas: Suficiencia General de Carga Horaria</h1>
            </div>
            <div class="row mb-3">
                <!-- Filtro de calendario -->
                <div class="col-md-4">
                    <form method="GET" action="">
                        <div class="form-group">
                            <label for="calendar_id">Seleccionar Calendario:</label>
                            <select name="calendar_id" id="calendar_id" class="form-control" onchange="this.form.submit()">
                                <option value="">-- Todos los calendarios --</option>
                                <?php foreach ($calendarios as $calendario): ?>
                                    <option value="<?= $calendario['id']; ?>" <?= $calendar_id == $calendario['id'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($calendario['nombre_cuatrimestre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Datos de Suficiencia de Carga Horaria</h3>
                        </div>
                        <div class="card-body">
                            <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th><center>Programas Educativos</center></th>
                                        <th><center>No. Grupos</center></th>
                                        <th><center>Horas Totales de Asignatura</center></th>
                                        <th><center>Asignaturas</center></th>
                                        <th><center>Horas Totales</center></th>
                                        <th><center>H/S/M</center></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($estadisticas as $fila): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($fila['programa'] ?? 'N/A'); ?></td>
                                        <td><center><?= htmlspecialchars($fila['grupos'] ?? '0'); ?></center></td>
                                        <td><center><?= htmlspecialchars($fila['horas_totales_asignatura'] ?? '0'); ?></center></td>
                                        <td><center><?= htmlspecialchars($fila['asignaturas'] ?? '0'); ?></center></td>
                                        <td><center><?= htmlspecialchars($fila['horas_totales_asignatura'] ?? '0'); ?></center></td>
                                        <td><center><?= htmlspecialchars($fila['numero_hsm'] ?? '0'); ?></center></td>
                                    </tr>
                                <?php endforeach; ?>

                                <!-- Fila de totales -->
                                <tr>
                                    <td><b>Total</b></td>
                                    <td><center><b><?= htmlspecialchars($total_grupos); ?></b></center></td>
                                    <td><center><b><?= htmlspecialchars($total_horas_totales_asignatura); ?></b></center></td>
                                    <td><center><b><?= htmlspecialchars($total_asignaturas); ?></b></center></td>
                                    <td><center><b><?= htmlspecialchars($total_horas_totales_asignatura); ?></b></center></td>
                                    <td><center><b><?= htmlspecialchars($total_numero_hsm); ?></b></center></td>
                                </tr>
                                </tbody>
                            </table>
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
include('../../../admin/layout/parte2.php');
include('../../../layout/mensajes.php');
?>

<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 10,
            "language": {
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Estadísticas",
                "infoEmpty": "Mostrando 0 a 0 de 0 Estadísticas",
                "infoFiltered": "(Filtrado de _MAX_ total Estadísticas)",
                "lengthMenu": "Mostrar _MENU_ Estadísticas",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscador:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Ultimo",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false,
            buttons: [{
                extend: 'collection',
                text: 'Opciones',
                orientation: 'landscape',
                buttons: [{
                    text: 'Copiar',
                    extend: 'copy',
                }, {
                    extend: 'pdf'
                }, {
                    extend: 'csv'
                }, {
                    extend: 'excel'
                }, {
                    text: 'Imprimir',
                    extend: 'print'
                }]
            },
            {
                extend: 'colvis',
                text: 'Visor de columnas',
                collectionLayout: 'fixed three-column'
            }],
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    });
</script>
