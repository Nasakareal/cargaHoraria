<?php
// Asegúrate de que program_id esté definido y sea válido
$program_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$program_id) {
    echo "ID de programa inválido.";
    exit;
}

include('../../app/config.php');
include('../../admin/layout/parte1.php');

// Obtener el nombre del programa desde la base de datos
$sql_programs = "SELECT program_name FROM programs WHERE estado = '1' AND program_id = :program_id";
$query_programs = $pdo->prepare($sql_programs);
$query_programs->bindParam(':program_id', $program_id, PDO::PARAM_INT);
$query_programs->execute();
$datos_programs = $query_programs->fetch(PDO::FETCH_ASSOC);

$nombre_program = $datos_programs['program_name'] ?? '';
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Editar el programa: <?= $nombre_program; ?></h1>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title">Datos registrados</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?= APP_URL; ?>/app/controllers/programas/update.php" method="post">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="">Nombre del programa</label>
                                            <input type="hidden" name="program_id" value="<?= $program_id; ?>">
                                            <input type="text" class="form-control" name="program_name" value="<?= $nombre_program; ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">Guardar</button>
                                            <a href="<?= APP_URL; ?>/admin/programas" class="btn btn-secondary">Cancelar</a>
                                        </div>
                                    </div>
                                </div>
                            </form>
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
