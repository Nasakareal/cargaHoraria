<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/edificios/listado_de_edificios.php'); 
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Listado de Edificios</h1>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Edificios registrados</h3>
                            <br>
                            <div class="card-tools d-flex">
                            <?php if (isset($_SESSION['sesion_rol']) && $_SESSION['sesion_rol'] == 1): ?>
                                <!-- Botón habilitado para administradores -->
                                <a href="create.php" class="btn btn-primary me-2">
                                    <i class="bi bi-plus-square"></i> Agregar nuevo edificio
                                </a>
                            <?php else: ?>
                                <!-- Botón deshabilitado para otros roles -->
                                <a href="#" class="btn btn-primary me-2 disabled" aria-disabled="true" title="Solo disponible para administradores">
                                    <i class="bi bi-plus-square"></i> Agregar nuevo edificio
                                </a>
                            <?php endif; ?>
                            </div>
                        </div>

                        <div class="card-body">
                            <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th><center>Número</center></th>
                                        <th><center>Nombre del Edificio</center></th>
                                        <th><center>Áreas</center></th>
                                        <th><center>Planta Alta</center></th>
                                        <th><center>Planta Baja</center></th>
                                        <th><center>Acciones</center></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $contador_edificios = 0;
                                foreach ($buildingPrograms as $building) {
                                    $building_id = $building['id'];
                                    $contador_edificios++; ?>
                                    <tr>
                                        <td style="text-align: center;"><?= $contador_edificios; ?></td>
                                        <td style="text-align: center;"><?= htmlspecialchars($building['building_name'] ?? 'Sin nombre'); ?></td>
                                        <td><center><?= htmlspecialchars($building['areas']); ?></center></td>
                                        <td style="text-align: center;"><?= $building['planta_alta'] ? 'Sí' : 'No'; ?></td>
                                        <td style="text-align: center;"><?= $building['planta_baja'] ? 'Sí' : 'No'; ?></td>
                                        <td style="text-align: center;">
                                            <div class="btn-group" role="group" aria-label="Basic example">
                                                <a href="show.php?id=<?= $building_id; ?>" class="btn btn-info btn-sm"><i class="bi bi-eye"></i></a>
                                                <a href="edit.php?id=<?= $building_id; ?>" class="btn btn-success btn-sm"><i class="bi bi-pencil"></i></a>
                                                <form action="<?= APP_URL; ?>/app/controllers/buildings/delete.php" method="post" onsubmit="return confirmarEliminar(event, <?= $building_id; ?>);">
                                                    <input type="hidden" name="building_id" value="<?= $building_id; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
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
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Materias",
                "infoEmpty": "Mostrando 0 a 0 de 0 Materias",
                "infoFiltered": "(Filtrado de _MAX_ total Materias)",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ Materias",
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