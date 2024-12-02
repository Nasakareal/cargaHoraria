<?php
header('Content-Type: text/html; charset=utf-8');

include('../../app/config.php');
include('../../app/helpers/verificar_admin.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/reportes/listado_de_reportes.php');

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Listado de Notificaciones</h1>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Notificaciones registradas</h3>
                        </div>
                        <div class="card-body">
                            <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-center">ID</th>
                                        <th class="text-center">Mensaje</th>
                                        <th class="text-center">Usuario</th>
                                        <th class="text-center">Fecha</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                if (!empty($reports)) {
                                    foreach ($reports as $report) { ?>
                                        <tr>
                                            <td class="text-center"><?= $report['report_id']; ?></td>
                                            <td><?= $report['report_message']; ?></td>
                                            <td class="text-center"><?= $report['user_name']; ?></td>
                                            <td class="text-center"><?= date('d-m-Y H:i:s', strtotime($report['created_at'])); ?></td>
                                            <td class="text-center">
                                                <?php if ($report['is_read']): ?>
                                                    <span class="badge badge-success">Leído</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">No leído</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group" aria-label="Acciones">
                                                    <!-- Botón para Ver -->
                                                    <a href="show.php?id=<?= $report['report_id']; ?>" class="btn btn-info btn-sm" title="Ver">
                                                        <i class="bi bi-eye"></i>
                                                    </a>

                                                    <!-- Botón para Eliminar -->
                                                    <form action="../../app/controllers/reportes/delete.php" method="post" id="deleteForm<?= $report['report_id']; ?>" class="d-inline">
                                                        <input type="hidden" name="report_id" value="<?= $report['report_id']; ?>">
                                                        <button type="button" onclick="confirmDelete<?= $report['report_id']; ?>(event)" class="btn btn-danger btn-sm" title="Eliminar">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                                <!-- Script de confirmación de eliminación -->
                                                <script>
                                                    function confirmDelete<?= $report['report_id']; ?>(event) {
                                                        event.preventDefault();
                                                        Swal.fire({
                                                            title: 'Eliminar Notificación',
                                                            text: '¿Estás seguro de que deseas eliminar esta notificación?',
                                                            icon: 'warning',
                                                            showCancelButton: true,
                                                            confirmButtonColor: '#d33',
                                                            cancelButtonColor: '#3085d6',
                                                            confirmButtonText: 'Sí, eliminar',
                                                            cancelButtonText: 'Cancelar'
                                                        }).then((result) => {
                                                            if (result.isConfirmed) {
                                                                document.getElementById('deleteForm<?= $report['report_id']; ?>').submit();
                                                            }
                                                        });
                                                    }
                                                </script>
                                            </td>
                                        </tr>
                                    <?php }
                                } else {
                                    echo "<tr><td colspan='6' class='text-center'>No se encontraron notificaciones.</td></tr>";
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
            "pageLength": 10,
            "language": {
                "emptyTable": "No hay notificaciones",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ notificaciones",
                "infoEmpty": "Mostrando 0 a 0 de 0 notificaciones",
                "infoFiltered": "(Filtrado de _MAX_ total notificaciones)",
                "lengthMenu": "Mostrar _MENU_ notificaciones",
                "search": "Buscar:",
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
        });
    });
</script>
