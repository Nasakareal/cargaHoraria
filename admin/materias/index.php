<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/materias/listado_de_materias.php');
include('../../app/controllers/cuatrimestres/listado_de_cuatrimestres.php');
include('../../app/controllers/programas/listado_de_programas.php');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Listado de Materias</h1>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Materias registradas</h3>
                            <br>
                            <div class="card-tools">
                                <a href="create.php" class="btn btn-primary"><i class="bi bi-plus-square"></i> Añadir nueva materia</a>
                                <form action="<?= APP_URL; ?>/app/controllers/materias/upload.php" method="post" enctype="multipart/form-data" class="d-inline">
                                    <div class="form-group d-inline">
                                        <label for="file" class="sr-only">Selecciona un archivo CSV:</label>
                                        <input type="file" name="file" accept=".csv, .xlsx" required class="form-control-file d-inline" style="display: inline-block; width: auto;">
                                    </div>
                                    <button type="submit" class="btn btn-primary d-inline">Cargar Materias</button>
                                </form>
                            </div>
                        </div>

                        <div class="card-body">
                            <?php if (isset($_SESSION['mensaje'])): ?>
                                <div class="alert alert-<?= $_SESSION['icono'] == 'success' ? 'success' : 'danger'; ?>">
                                    <i class="<?= $_SESSION['icono'] == 'success' ? 'bi bi-check-circle' : 'bi bi-exclamation-triangle'; ?>"></i> <?= $_SESSION['mensaje']; ?>
                                </div>
                                <?php unset($_SESSION['mensaje']); ?>
                            <?php endif; ?>

                            <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th><center>Número</center></th>
                                        <th><center>Materias</center></th>
                                        <th><center>Horas Consecutivas</center></th>
                                        <th><center>Horas Semanales</center></th>
                                        <th><center>Programa</center></th>
                                        <th><center>Cuatrimestre</center></th>
                                        <th><center>Laboratorios Asignados</center></th>
                                        <th><center>Horas Aula</center></th>
                                        <th><center>Horas Laboratorio</center></th>
                                        <th><center>Acciones</center></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $contador_subjects = 0;
                                    foreach ($subjects as $subject) {
                                        $contador_subjects++;

                                        /* Busca el nombre del programa */
                                        $program_name = 'No asignado';
                                        foreach ($programs as $program) {
                                            if ($program['program_id'] == $subject['program_id']) {
                                                $program_name = $program['programa'] ?? 'No disponible';
                                                break;
                                            }
                                        }

                                        /* Busca el nombre del cuatrimestre */
                                        $term_name = 'No asignado';
                                        foreach ($terms as $term) {
                                            if ($term['term_id'] == $subject['term_id']) {
                                                $term_name = $term['term_name'] ?? 'No disponible';
                                                break;
                                            }
                                        }
                                        ?>
                                    <tr>
                                        <td style="text-align: center"><?= $contador_subjects; ?></td>
                                        <td style="text-align: center"><?= htmlspecialchars($subject['subject_name']); ?></td>
                                        <td><center><?= htmlspecialchars($subject['hours_consecutive']); ?></center></td>
                                        <td><center><?= htmlspecialchars($subject['class_hours'] + $subject['lab_hours']); ?></center></td>
                                        <td><center><?= $program_name; ?></center></td>
                                        <td><center><?= $term_name; ?></center></td>
                                        <td><center><?= isset($subject['num_labs']) ? htmlspecialchars($subject['num_labs']) : 'No asignado'; ?></center></td>
                                        <td><center><?= htmlspecialchars($subject['class_hours']); ?></center></td>
                                        <td><center><?= htmlspecialchars($subject['lab_hours']); ?></center></td>
                                        <td style="text-align: center">
                                            <div class="btn-group" role="group">
                                                <a href="show.php?id=<?= $subject['subject_id']; ?>" class="btn btn-info btn-sm"><i class="bi bi-eye"></i></a>
                                                <a href="edit.php?id=<?= $subject['subject_id']; ?>" class="btn btn-success btn-sm"><i class="bi bi-pencil"></i></a>
                                                <form action="<?= APP_URL; ?>/app/controllers/materias/delete.php" onclick="preguntar<?= $subject['subject_id']; ?>(event)" method="post" id="miFormulario<?= $subject['subject_id']; ?>">
                                                    <input type="hidden" name="subject_id" value="<?= $subject['subject_id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                                                </form>
                                                <script>
                                                    function preguntar<?= $subject['subject_id']; ?>(event){
                                                        event.preventDefault();
                                                        Swal.fire({
                                                            title: 'Eliminar Materia',
                                                            text: '¿Desea eliminar esta Materia?',
                                                            icon: 'question',
                                                            showDenyButton: true,
                                                            confirmButtonText: 'Eliminar',
                                                            confirmButtonColor: '#a5161d',
                                                            denyButtonColor: '#007bff',
                                                            denyButtonText: 'Cancelar',
                                                        }).then((result) => {
                                                            if (result.isConfirmed) { 
                                                                var form = $('#miFormulario<?= $subject['subject_id']; ?>');
                                                                form.submit();
                                                            }
                                                        });
                                                        return false;
                                                    }
                                                </script>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php } ?>
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
