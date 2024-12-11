<?php
include_once('../../app/config.php');
include_once('../../admin/layout/parte1.php');
include('../../app/controllers/profesores/listado_de_profesores.php');
?>

<div class="content-wrapper">
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Listado de Profesores</h1>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Profesores registrados</h3>
                        </div>
                        <div class="card-body">
                            <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-center">Número</th>
                                        <th class="text-center">Nombre del Profesor</th>
                                        <th class="text-center">Clasificación</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $contador_teachers = 0;
                                foreach ($teachers as $teacher) {
                                    $contador_teachers++;
                                    ?>
                                    <tr>
                                        <td style="text-align: center"><?= $contador_teachers; ?></td>
                                        <td class="text-center"><?= htmlspecialchars($teacher['profesor']); ?></td>
                                        <td><center><?= $teacher['clasificacion'] ?? 'No asignado'; ?></center></td>
                                        <td class="text-center">
                                            <a href="show.php?id=<?= $teacher['teacher_id']; ?>" class="btn btn-info">Ver detalles</a>
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
        </div>
    </div>
</div>

<?php
include_once('../../admin/layout/parte2.php');
include_once('../../layout/mensajes.php');
?>

<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 5,
            "language": {
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Profesores",
                "infoEmpty": "Mostrando 0 a 0 de 0 Profesores",
                "infoFiltered": "(Filtrado de _MAX_ total Profesores)",
                "lengthMenu": "Mostrar _MENU_ Profesores",
                "loadingRecords": "Cargando...",
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
            },
            {
                extend: 'colvis',
                text: 'Visor de columnas',
                collectionLayout: 'fixed three-column'
            }],
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    });
</script>
