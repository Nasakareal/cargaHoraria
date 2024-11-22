<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/grupos/listado_grupos_salones.php');
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Listado de Grupos y Asignación de Salones</h1>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Grupos registrados</h3>
                            <div class="card-tools d-flex">
                            <?php if (isset($_SESSION['sesion_rol']) && $_SESSION['sesion_rol'] == 1): ?>
    <!-- Formulario habilitado para administradores -->
    <form action="../../app/controllers/autoSalones/logica.php" method="POST" style="display:inline;">
        <button type="submit" name="auto-assign" class="btn btn-primary">
            <i class="bi bi-arrow-repeat"></i> Asignar Salones
        </button>
    </form>
<?php else: ?>
    <!-- Formulario deshabilitado para otros roles -->
    <form style="display:inline;">
        <button type="button" class="btn btn-primary disabled" aria-disabled="true" title="Solo disponible para administradores">
            <i class="bi bi-arrow-repeat"></i> Asignar Salones
        </button>
    </form>
<?php endif; ?>

                            </div>
                        </div>

                        <div class="card-body">
                            <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-center">Numero</th>
                                        <th class="text-center">Nombre del Grupo</th>
                                        <th class="text-center">Turno</th>
                                        <th class="text-center">Volumen</th>
                                        <th class="text-center">Salón Asignado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                if (!empty($grupos_con_salones)) {
                                    foreach ($grupos_con_salones as $index => $grupo) {
                                        $salon_asignado = $grupo['classroom_name']
                                            ? $grupo['classroom_name'] . ' (' . substr($grupo['building'], -1) . ')'
                                            : 'No disponible';
                                        ?>
                                        <tr>
                                            <td class="text-center"><?= $index + 1; ?></td>
                                            <td class="text-center"><?= $grupo['group_name']; ?></td>
                                            <td class="text-center"><?= $grupo['turn']; ?></td>
                                            <td class="text-center"><?= $grupo['capacidad_grupo']; ?></td>
                                            <td class="text-center"><?= $salon_asignado; ?></td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='text-center'>No hay información disponible</td></tr>";
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
