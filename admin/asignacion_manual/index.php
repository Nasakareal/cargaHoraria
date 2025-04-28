<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');
include_once('../../app/middleware.php');

if (!verificarPermiso($_SESSION['sesion_id_usuario'], 'lab_block_manage', $pdo)) {
    $_SESSION['mensaje'] = "No tienes permiso para hacer un bloqueo laboratorio/aula.";
    $_SESSION['icono'] = "error";
    ?>
    <script>
        history.back();
    </script>
    <?php
    exit;
}


$sql_groups = "SELECT group_id, group_name FROM `groups` WHERE estado = '1'";
$stmt_groups = $pdo->prepare($sql_groups);
$stmt_groups->execute();
$groups = $stmt_groups->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-wrapper">
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
                        </div>
                        <div class="card-body">
                            <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-center">Número</th>
                                        <th class="text-center">Nombre del Grupo</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $contador_grupos = 0;
                                foreach ($groups as $group) {
                                    $contador_grupos++;
                                    ?>
                                    <tr>
                                        <td style="text-align: center"><?= $contador_grupos; ?></td>
                                        <td class="text-center"><?= htmlspecialchars($group['group_name']); ?></td>
                                        <td class="text-center">
                                            <!-- Botón Editar que redirige a edit.php con el ID del grupo -->
                                            <a href="edit.php?id=<?= $group['group_id']; ?>" class="btn btn-success btn-sm">
                                            <i class="bi bi-pencil"></i> Editar
                                            </a>
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
include('../../admin/layout/parte2.php');
include('../../layout/mensajes.php');
?>

<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 10,
            "language": {
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Grupos",
                "infoEmpty": "Mostrando 0 a 0 de 0 Grupos",
                "infoFiltered": "(Filtrado de _MAX_ total Grupos)",
                "lengthMenu": "Mostrar _MENU_ Grupos",
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
                extend: 'collection',
                text: 'Opciones',
                orientation: 'landscape',
                buttons: [{
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

<style>
    .dataTables_filter input {
        background-color: #ffd800;
        color: #333;
        border: 1px solid #555;
        border-radius: 4px;
        padding: 5px;
        font-weight: bold;
    }

    .dataTables_filter label {
        color: #333;
        font-weight: bold;
    }
</style>
