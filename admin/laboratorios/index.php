<?php

header('Content-Type: text/html; charset=utf-8');

include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/laboratorios/listado_de_laboratorios.php');

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Listado de Laboratorios</h1>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Laboratorios registrados</h3>
                            <br>
                            <div class="card-tools d-flex">
                            <?php if (isset($_SESSION['sesion_rol']) && $_SESSION['sesion_rol'] == 1): ?>
                                <a href="create.php" class="btn btn-primary me-2">
                                    <i class="bi bi-plus-square"></i> Agregar nuevo Laboratorio
                                </a>
                            <?php else: ?>
                                <a href="#" class="btn btn-primary me-2 disabled" aria-disabled="true" title="Solo disponible para administradores">
                                    <i class="bi bi-plus-square"></i> Agregar nuevo Laboratorio
                                </a>
                            <?php endif; ?>


                            </div>
                        </div>

                        <div class="card-body">
                            <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-center">Número</th>
                                        <th class="text-center">Nombre del Laboratorio</th>
                                        <th class="text-center">Descripción</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                if (!empty($labs)) {
                                    $contador_labs = 0;
                                    foreach ($labs as $lab) {
                                        $lab_id = $lab['lab_id'];
                                        $contador_labs++; ?>
                                        <tr>
                                            <td style="text-align: center"><?= $contador_labs; ?></td>
                                            <td class="text-center"><?= $lab['lab_name']; ?></td>
                                            <td class="text-center"><?= $lab['description']; ?></td>
                                            <td style="text-align: center">
                                                <div class="btn-group" role="group" aria-label="Basic example">
                                                    <a href="show.php?id=<?= $lab_id; ?>" type="button" class="btn btn-info btn-sm"><i class="bi bi-eye"></i></a>
                                                    <a href="edit.php?id=<?= $lab_id; ?>" type="button" class="btn btn-success btn-sm"><i class="bi bi-pencil"></i></a>
                                                    <form action="<?= APP_URL; ?>/app/controllers/laboratorios/delete.php" onclick="preguntar<?= $lab_id; ?>(event)" method="post" id="miFormulario<?= $lab_id; ?>">
                                                        <input type="text" name="lab_id" value="<?= $lab_id; ?>" hidden>
                                                        <button type="submit" class="btn btn-danger btn-sm" style="border-radius: 0px 5px 5px 0px"><i class="bi bi-trash"></i></button>
                                                    </form>

                                                    <script>
                                                        function preguntar<?= $lab_id; ?>(event) {
                                                            event.preventDefault();
                                                            Swal.fire({
                                                                title: 'Eliminar Laboratorio',
                                                                text: '¿Desea eliminar este Laboratorio?',
                                                                icon: 'question',
                                                                showDenyButton: true,
                                                                confirmButtonText: 'Eliminar',
                                                                confirmButtonColor: '#a5161d',
                                                                denyButtonColor: '#007bff',
                                                                denyButtonText: 'Cancelar',
                                                            }).then((result) => {
                                                                if (result.isConfirmed) { 
                                                                    var form = $('#miFormulario<?= $lab_id; ?>');
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
                                    echo "<tr><td colspan='5' style='text-align:center'>No se encontraron laboratorios.</td></tr>";
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
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Laboratorios",
                "infoEmpty": "Mostrando 0 a 0 de 0 Laboratorios",
                "infoFiltered": "(Filtrado de _MAX_ total Laboratorios)",
                "lengthMenu": "Mostrar _MENU_ Laboratorios",
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
