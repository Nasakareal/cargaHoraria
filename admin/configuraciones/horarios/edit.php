<?php
include('../../../app/config.php');
include('../../../admin/layout/parte1.php');

// Validar y obtener el assignment_id
$assignment_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$assignment_id) {
    echo "ID inválido.";
    exit;
}

// Obtener datos actuales del registro
$stmt = $pdo->prepare("SELECT * FROM schedule_history WHERE assignment_id = :assignment_id");
$stmt->bindParam(':assignment_id', $assignment_id);
$stmt->execute();
$registro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$registro) {
    echo "Registro no encontrado.";
    exit;
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Editar Registro del Historial</h1>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title">Editar Cuatrimestre</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?= APP_URL; ?>/app/controllers/configuraciones/horarios_anteriores/update_cuatrimestre.php" method="post">
                                <input type="hidden" name="assignment_id" value="<?= $assignment_id; ?>">

                                <div class="form-group">
                                    <label for="quarter_name_en">Nombre del Cuatrimestre (en inglés)</label>
                                    <input type="text" name="quarter_name_en" class="form-control" value="<?= htmlspecialchars($registro['quarter_name_en'] ?? ''); ?>" required>
                                </div>

                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                <a href="index.php" class="btn btn-secondary">Cancelar</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </div><!-- /.content -->
</div><!-- /.content-wrapper -->

<?php
include('../../../admin/layout/parte2.php');
include('../../../layout/mensajes.php');
?>
