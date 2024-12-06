<?php

header('Content-Type: text/html; charset=utf-8');

include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/programas/listado_de_programas.php');
include('../../app/controllers/grupos/listado_de_grupos.php');
include('../../app/controllers/cuatrimestres/listado_de_cuatrimestres.php');
include('../../app/controllers/niveles/listado_de_niveles.php');

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Listado de Grupos</h1>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                    <div class="card-header">
    <h3 class="card-title">Grupos registrados</h3>
    <br>
    <div class="card-tools d-flex">
        <a href="create.php" class="btn btn-primary me-2">
            <i class="bi bi-plus-square"></i> Agregar nuevo Grupo
        </a>

                                <!-- Añadir grupos desde archivo -->
                                <form action="<?= APP_URL; ?>/app/controllers/grupos/upload.php" method="post" enctype="multipart/form-data" class="d-flex align-items-center">
                                    <div class="form-group me-2">
                                        <label for="file" class="d-none">Selecciona un archivo CSV:</label>
                                        <?php if (isset($_SESSION['sesion_rol']) && $_SESSION['sesion_rol'] == 1): ?>
                                            <input type="file" name="file" accept=".csv, .xlsx" required>
                                            <button type="submit" class="btn btn-primary">Cargar Grupos</button>
                                        <?php else: ?>
                                            <input type="file" name="file" accept=".csv, .xlsx" disabled>
                                            <button type="button" class="btn btn-primary disabled" aria-disabled="true" title="Solo disponible para administradores">Cargar Grupos</button>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>
                        </div>


                        <div class="card-body">
                            <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-center">N&uacute;mero</th>
                                        <th class="text-center">Nombre del Grupo</th>
                                        <th class="text-center">Nombre del Programa Educativo</th>
                                        <th class="text-center">Cuatrimestre</th>
                                        <th class="text-center">Volumen del grupo</th>
                                        <th class="text-center">Turno</th>
                                        <th class="text-center">Nivel Educativo</th>  
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                if (!empty($groups)) {
                                    $contador_groups = 0;
                                    foreach ($groups as $group) {
                                        $group_id = $group['group_id'];
                                        $contador_groups++; ?>
                                        <tr>
                                            <td style="text-align: center"><?= $contador_groups; ?></td>
                                            <td class="text-center"><?= $group['group_name']; ?></td>
                                            <td class="text-center"><?= $group['program_name']; ?></td>
                                            <td class="text-center"><?= $group['term_name']; ?></td>
                                            <td style="text-align: center"><?= $group['volume']; ?></td>
                                            <td class="text-center"><?= $group['shift_name']; ?></td>
                                            <td class="text-center"><?= $group['level_name']; ?></td>  
                                            <td style="text-align: center">
                                                <div class="btn-group" role="group" aria-label="Basic example">
                                                    <a href="show.php?id=<?= $group_id; ?>" type="button" class="btn btn-info btn-sm"><i class="bi bi-eye"></i></a>
                                                    <a href="edit.php?id=<?= $group_id; ?>" type="button" class="btn btn-success btn-sm"><i class="bi bi-pencil"></i></a>
                                                    <form action="<?= APP_URL; ?>/app/controllers/grupos/delete.php" onclick="preguntar<?= $group_id; ?>(event)" method="post" id="miFormulario<?= $group_id; ?>">
                                                        <input type="text" name="group_id" value="<?= $group_id; ?>" hidden>
                                                        <button type="submit" class="btn btn-danger btn-sm" style="border-radius: 0px 5px 5px 0px"><i class="bi bi-trash"></i></button>
                                                    </form>

                                                    <script>
                                                        function preguntar<?= $group_id; ?>(event){
                                                            event.preventDefault();
                                                            Swal.fire({
                                                                title: 'Eliminar Grupo',
                                                                text: '¿Desea eliminar este Grupo?',
                                                                icon: 'question',
                                                                showDenyButton: true,
                                                                confirmButtonText: 'Eliminar',
                                                                confirmButtonColor: '#a5161d',
                                                                denyButtonColor: '#007bff',
                                                                denyButtonText: 'Cancelar',
                                                            }).then((result) => {
                                                                if (result.isConfirmed) { 
                                                                    var form = $('#miFormulario<?= $group_id; ?>');
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
                                } else {
                                    echo "<tr><td colspan='9' style='text-align:center'>No se encontraron grupos.</td></tr>";
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
