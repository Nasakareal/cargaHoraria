<?php

header('Content-Type: text/html; charset=utf-8');

include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/alumnos/listado_de_alumnos.php');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Listado de Alumnos</h1>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Alumnos registrados</h3>
                            <br>
                            <div class="card-tools d-flex">
                                <a href="create.php" class="btn btn-primary me-2">
                                    <i class="bi bi-plus-square"></i> Agregar nuevo Alumno
                                </a>

                                <!-- Agregar grupos desde archivo -->
                                <form action="<?= APP_URL; ?>/app/controllers/alumnos/upload.php" method="post" enctype="multipart/form-data" class="d-flex align-items-center">
                                    <div class="form-group me-2">
                                        <label for="file" class="d-none">Selecciona un archivo CSV:</label>
                                        <input type="file" name="file" accept=".csv, .xlsx" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Cargar Alumnos</button>
                                </form>
                            </div>
                        </div>

                        <div class="card-body">
                            <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th><center>Numero</center></th>
                                        <th><center>Nombres del Alumno</center></th>
                                        <th><center>Grupo del Alumno</center></th>
                                        <th><center>Cuatrimestre del Alumno</center></th>
                                        <th><center>Programa del Alumno</center></th>  <!-- Agregar esta columna -->
                                        <th><center>Acciones</center></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $contador_students = 0;
                                foreach ($students as $student) {
                                    $contador_students++; ?>
                                    <tr>
                                        <td style="text-align: center"><?= $contador_students; ?></td>
                                        <td><center><?= $student['alumno']; ?></center></td>
                                        <td><center><?= $student['grupo']; ?></center></td>
                                        <td><center><?= $student['cuatrimestre']; ?></center></td>
                                        <td><center><?= $student['programa']; ?></center></td>  <!-- Mostrar el programa -->
                                        <td style="text-align: center">
                                            <div class="btn-group" role="group" aria-label="Basic example">
                                                <a href="show.php?id=<?= $student['student_id']; ?>" type="button" class="btn btn-info btn-sm"><i class="bi bi-eye"></i></a>
                                                <a href="edit.php?id=<?= $student['student_id']; ?>" type="button" class="btn btn-success btn-sm"><i class="bi bi-pencil"></i></a>
                                                <form action="<?= APP_URL; ?>/app/controllers/alumnos/delete.php" onclick="preguntar<?= $student['student_id']; ?>(event)" method="post" id="miFormulario<?= $student['student_id']; ?>">
                                                    <input type="text" name="student_id" value="<?= $student['student_id']; ?>" hidden>
                                                    <button type="submit" class="btn btn-danger btn-sm" style="border-radius: 0px 5px 5px 0px"><i class="bi bi-trash"></i></button>
                                                </form>

                                                <script>
                                                    function preguntar<?= $student['student_id']; ?>(event){
                                                        event.preventDefault();
                                                        Swal.fire({
                                                            title: 'Eliminar Alumno',
                                                            text: '�Desea eliminar este Alumno?',
                                                            icon: 'question',
                                                            showDenyButton: true,
                                                            confirmButtonText: 'Eliminar',
                                                            confirmButtonColor: '#a5161d',
                                                            denyButtonColor: '#007bff',
                                                            denyButtonText: 'Cancelar',
                                                        }).then((result) => {
                                                            if (result.isConfirmed) { 
                                                                var form = $('#miFormulario<?= $student['student_id']; ?>');
                                                                form.submit();
                                                            }
                                                        });
                                                        return false;
                                                    }
                                                </script>
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
                "emptyTable": "No hay informaci�n",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Alumnos",
                "infoEmpty": "Mostrando 0 a 0 de 0 Alumnos",
                "infoFiltered": "(Filtrado de _MAX_ total Alumnos)",
                "lengthMenu": "Mostrar _MENU_ Alumnos",
                "loadingRecord": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscador:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "�ltimo",
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
