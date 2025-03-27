<?php
include('../../../app/config.php');
include('../../../app/helpers/verificar_admin.php');
include('../../../admin/layout/parte1.php');

try {
    // Se obtiene la fecha (solo la parte de la fecha) y el número total de registros archivados por día
    $stmt = $pdo->prepare("SELECT DATE(fecha_registro) AS fecha_registro, COUNT(*) AS total 
                           FROM schedule_history 
                           GROUP BY DATE(fecha_registro) 
                           ORDER BY fecha_registro DESC");
    $stmt->execute();
    $fechas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Historial de Horarios</h1>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Horarios Archivados por Fecha</h3>
                            <div class="card-tools">
                                <?php if (isset($_SESSION['sesion_rol']) && $_SESSION['sesion_rol'] == 1): ?>
                                    <a href="<?= APP_URL; ?>/app/controllers/configuraciones/horarios_anteriores/mover_horarios.php" class="btn btn-danger" onclick="return confirm('¿Seguro que deseas archivar los horarios actuales y limpiar la tabla?')">
                                        <i class="bi bi-archive"></i> Archivar Horarios Actuales
                                    </a>
                                <?php else: ?>
                                    <a href="#" class="btn btn-primary disabled" title="Solo disponible para administradores">
                                        <i class="bi bi-plus-square"></i> Registrar Horario Actual
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-center">Número</th>
                                        <th class="text-center">Cuatrimestre</th>
                                        <th class="text-center">Fecha de Registro</th>
                                        <th class="text-center">Total Registros</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $contador = 1;
                                    foreach ($fechas as $fila):

                                        // Obtener el cuatrimestre y assignment_id representativo por fecha
                                        $stmtDetalle = $pdo->prepare("SELECT assignment_id, quarter_name_en 
                                                                      FROM schedule_history 
                                                                      WHERE DATE(fecha_registro) = :fecha 
                                                                      LIMIT 1");
                                        $stmtDetalle->bindParam(':fecha', $fila['fecha_registro']);
                                        $stmtDetalle->execute();
                                        $detalle = $stmtDetalle->fetch(PDO::FETCH_ASSOC);

                                        $quarter_name = $detalle['quarter_name_en'] ?? '';
                                        $assignment_id = $detalle['assignment_id'] ?? null;
                                    ?>
                                    <tr>
                                        <td class="text-center"><?= $contador; ?></td>
                                        <td class="text-center"><?= htmlspecialchars($quarter_name); ?></td>
                                        <td class="text-center"><?= $fila['fecha_registro']; ?></td>
                                        <td class="text-center"><?= $fila['total']; ?></td>
                                        <td class="text-center">
                                            <a href="show.php?fecha=<?= $fila['fecha_registro']; ?>" class="btn btn-info btn-sm">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            <?php if ($assignment_id): ?>
                                            <a href="edit.php?id=<?= $assignment_id; ?>" class="btn btn-success btn-sm">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php
                                        $contador++;
                                    endforeach;
                                    ?>
                                </tbody>
                            </table>
                        </div><!-- /.card-body -->
                    </div><!-- /.card -->
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div><!-- /.content -->
</div><!-- /.content-wrapper -->

<?php
include('../../../admin/layout/parte2.php');
include('../../../layout/mensajes.php');
?>

<!-- DataTables y configuración de idioma -->
<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 5,
            "language": {
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                "infoFiltered": "(Filtrado de _MAX_ registros)",
                "lengthMenu": "Mostrar _MENU_ registros",
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
            "autoWidth": false
        });
    });
</script>
