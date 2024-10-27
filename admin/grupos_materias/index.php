<?php
header('Content-Type: text/html; charset=utf-8');

include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/grupos/listado_de_grupos.php');
include('../../app/controllers/materias/listado_de_materias.php');

// Nueva función para obtener materias asignadas a un grupo
function obtenerMateriasAsignadas($pdo, $group_id)
{
    $sql = "SELECT s.subject_name 
            FROM group_subjects gs
            JOIN subjects s ON gs.subject_id = s.subject_id
            WHERE gs.group_id = :group_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':group_id' => $group_id]);
    $materias = $stmt->fetchAll(PDO::FETCH_COLUMN);
    return implode(', ', $materias); // Devolver las materias en un string separado por comas
}

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Asignación de Materias a Grupos</h1>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Grupos y Materias</h3>
                            <div class="card-tools d-flex">
                                <a href="create.php" class="btn btn-primary me-2">
                                    <i class="bi bi-plus-square"></i> Asignar Materia
                                </a>
                            </div>
                        </div>

                        <div class="card-body">
                            <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-center">N&uacute;mero</th>
                                        <th class="text-center">Nombre del Grupo</th>
                                        <th class="text-center">Materias Asignadas</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                if (isset($groups)) {
                                    $contador_groups = 0;
                                    foreach ($groups as $group) {
                                        $group_id = $group['group_id'];
                                        $contador_groups++;

                                        // Obtener las materias asignadas a cada grupo
                                        $materias_asignadas = obtenerMateriasAsignadas($pdo, $group_id);

                                        ?>
                                        <tr>
                                            <td style="text-align: center"><?= $contador_groups; ?></td>
                                            <td class="text-center"><?= htmlspecialchars($group['group_name']); ?></td>
                                            <td class="text-center"><?= htmlspecialchars($materias_asignadas); ?></td>
                                            <td style="text-align: center">
                                                <div class="btn-group" role="group" aria-label="Basic example">
                                                    <a href="show.php?id=<?= $group_id; ?>" type="button" class="btn btn-info btn-sm"><i class="bi bi-eye"></i></a>
                                                    <a href="edit.php?id=<?= $group_id; ?>" type="button" class="btn btn-success btn-sm"><i class="bi bi-pencil"></i></a>
                                                    <a href="delete.php?id=<?= $group_id; ?>" type="button" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='4' style='text-align:center'>No se encontraron grupos.</td></tr>";
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
