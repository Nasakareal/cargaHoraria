<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/grupos/listado_de_grupos.php'); // Incluye los grupos
include('../../app/controllers/horarios_grupos/listado_de_horarios.php'); // Incluye los horarios

/* Lógica para asignar horarios según la disponibilidad */
function asignarHorarios($pdo, $group_id)
{
    $sql = "SELECT * FROM schedules WHERE group_id = :group_id ORDER BY start_time";
    $query = $pdo->prepare($sql);
    $query->execute(['group_id' => $group_id]);
    return $query->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Listado de Grupos y Asignación de Horarios</h1>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Grupos registrados</h3>
                            <div class="card-tools d-flex">
                                <a href="create.php" class="btn btn-primary me-2">
                                    <i class="bi bi-plus-square"></i> Agregar nuevo horario
                                </a>
                            </div>
                        </div>

                        <div class="card-body">
                            <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-center"><center>Número</center></th>
                                        <th class="text-center"><center>Nombre del Grupo</center></th>
                                        <th class="text-center"><center>Horarios Asignados</center></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $contador_grupos = 0;
                                foreach ($groups as $group) {
                                    $contador_grupos++;
                                    $horarios_asignados = asignarHorarios($pdo, $group['group_id']);
                                    $horarios_display = '';

                                    foreach ($horarios_asignados as $horario) {
                                        $horarios_display .= $horario['schedule_day'] . ': ' . $horario['start_time'] . ' - ' . $horario['end_time'] . '<br>';
                                    }
                                    ?>
                                    <tr>
                                        <td style="text-align: center"><?= $contador_grupos; ?></td>
                                        <td class="text-center"><?= $group['group_name']; ?></td>
                                        <td style="text-align: center"><?= $horarios_display ?: 'No hay horarios asignados'; ?></td>
                                    </tr>
                                    <?php
                                }
                                ?>
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
include('../../admin/layout/parte2.php');
include('../../layout/mensajes.php');
?>

<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 5,
            "language": {
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Grupos",
                "infoEmpty": "Mostrando 0 a 0 de 0 Grupos",
                "infoFiltered": "(Filtrado de _MAX_ total Grupos)",
                "lengthMenu": "Mostrar _MENU_ Grupos",
                "loadingRecord": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscador:",
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
