<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/programas/listado_de_programas.php');
include('../../app/controllers/grupos/listado_de_grupos.php');

$sentencia = $pdo->query('SELECT * FROM programs');
$programs = $sentencia->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Listado de Programas Educativos</h1>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Programas registrados</h3>
                            <br>
                            <div class="card-tools d-flex">
                                <a href="create.php" class="btn btn-primary me-2">
                                    <i class="bi bi-plus-square"></i> Agregar nuevo programa
                                </a>

                                <!-- Agregar programas desde archivo -->
                                <form action="<?= APP_URL; ?>/app/controllers/programas/upload.php" method="post" enctype="multipart/form-data" class="d-flex align-items-center">
                                    <div class="form-group me-2">
                                        <label for="file" class="d-none">Selecciona un archivo CSV:</label>
                                        <input type="file" name="file" accept=".csv, .xlsx" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Cargar Programas</button>
                                </form>
                            </div>
                        </div>

                        <div class="card-body">
                            <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th><center>Numero</center></th>
                                        <th><center>Nombre del programa</center></th>
                                        <th><center>Acciones</center></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $contador_programs = 0;
                                foreach ($programs as $program) {
                                    $program_id = $program['program_id'];
                                    $contador_programs++; ?>
                                    <tr>
                                        <td style="text-align: center"><?= $contador_programs; ?></td>
                                        <td><?= $program['program_name']; ?></td>
                                        
                                        <td style="text-align: center">
                                            <div class="btn-group" role="group" aria-label="Basic example">
                                                <a href="show.php?id=<?= $program_id; ?>" type="button" class="btn btn-info btn-sm"><i class="bi bi-eye"></i></a>
                                                <a href="edit.php?id=<?= $program_id; ?>" type="button" class="btn btn-success btn-sm"><i class="bi bi-pencil"></i></a>
                                                <form action="<?= APP_URL; ?>/app/controllers/programas/delete.php" onclick="preguntar<?= $program_id; ?>(event)" method="post" id="miFormulario<?= $program_id; ?>">
                                                    <input type="text" name="program_id" value="<?= $program_id; ?>" hidden>
                                                    <button type="submit" class="btn btn-danger btn-sm" style="border-radius: 0px 5px 5px 0px"><i class="bi bi-trash"></i></button>
                                                </form>

                                                <script>
                                                    function preguntar<?= $program_id; ?>(event){
                                                        event.preventDefault();
                                                        Swal.fire({
                                                            title: 'Eliminar Programa',
                                                            text: '�Desea eliminar este Programa?',
                                                            icon: 'question',
                                                            showDenyButton: true,
                                                            confirmButtonText: 'Eliminar',
                                                            confirmButtonColor: '#a5161d',
                                                            denyButtonColor: '#007bff',
                                                            denyButtonText: 'Cancelar',
                                                        }).then((result) => {
                                                            if (result.isConfirmed) { 
                                                                var form = $('#miFormulario<?= $program_id; ?>');
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
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Programas",
                "infoEmpty": "Mostrando 0 a 0 de 0 Programas",
                "infoFiltered": "(Filtrado de _Max_ total Programas)",
                "lengthMenu": "Mostrar _MENU_ Programas",
                "loadingRecord": "Cargando...",
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
