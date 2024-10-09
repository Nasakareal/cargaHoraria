<?php

header('Content-Type: text/html; charset=utf-8');

include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/relacion_materia_cuatrimestre_programa/listado_de_relacion.php');

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Relación de Materias por Programa y Cuatrimestre</h1>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Materias asignadas a Programas y Cuatrimestres</h3>
                            <br>
                            <div class="card-tools d-flex">
                                <a href="create.php" class="btn btn-primary me-2">
                                    <i class="bi bi-plus-square"></i> Agregar nueva Relación
                                </a>
                            </div>
                        </div>

                        <div class="card-body">
                            <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-center">Número</th>
                                        <th class="text-center">Programa Educativo</th>
                                        <th class="text-center">Cuatrimestre</th>
                                        <th class="text-center">Materias</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                if (isset($relations) && count($relations) > 0) {
                                    $contador = 0;
                                    foreach ($relations as $relation) {
                                        $contador++; ?>
                                        <tr>
                                            <td style="text-align: center"><?= $contador; ?></td>
                                            <td class="text-center"><?= $relation['program_name']; ?></td>
                                            <td class="text-center"><?= $relation['term_name']; ?></td>
                                            <td class="text-center"><?= $relation['subjects']; ?></td>
                                            <td style="text-align: center">
                                                <div class="btn-group" role="group" aria-label="Basic example">
                                                    <a href="show.php?id=<?= $relation['program_term_subject_id']; ?>" type="button" class="btn btn-info btn-sm"><i class="bi bi-eye"></i></a>
                                                    <a href="edit.php?id=<?= $relation['program_term_subject_id']; ?>" type="button" class="btn btn-success btn-sm"><i class="bi bi-pencil"></i></a>
                                                    <form action="<?= APP_URL; ?>/app/controllers/group_subjects/delete.php" method="post" id="miFormulario<?= $relation['program_term_subject_id']; ?>">
                                                        <input type="hidden" name="program_term_subject_id" value="<?= $relation['program_term_subject_id']; ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='5' style='text-align:center'>No se encontraron relaciones.</td></tr>";
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
                "info": "Mostrando _START_ a _END_ de _TOTAL_ relaciones",
                "infoEmpty": "Mostrando 0 a 0 de 0 relaciones",
                "infoFiltered": "(Filtrado de _MAX_ total relaciones)",
                "lengthMenu": "Mostrar _MENU_ relaciones",
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
            buttons: [
                
            ],
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    });
</script>
