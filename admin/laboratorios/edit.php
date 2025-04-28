<?php

$lab_id = $_GET['id'] ?? null;

if (!$lab_id) {
    echo "ID del laboratorio no especificado.";
    exit;
}

include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/laboratorios/datos_del_laboratorio.php');

include_once('../../app/middleware.php');

if (!verificarPermiso($_SESSION['sesion_id_usuario'], 'lab_edit', $pdo)) {
    $_SESSION['mensaje'] = "No tienes permiso para editar un Laboratorio.";
    $_SESSION['icono'] = "error";
    ?>
    <script>
        history.back();
    </script>
    <?php
    exit;
}

$sql_programs = "SELECT 
                    DISTINCT p.area
                 FROM
                    programs p";

$query_programs = $pdo->prepare($sql_programs);
$query_programs->execute();
$programs = $query_programs->fetchAll(PDO::FETCH_ASSOC);

if (empty($programs)) {
    $programs = [];
}

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Editar el laboratorio: <?= $lab_name; ?></h1>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title">Datos registrados</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?= APP_URL; ?>/app/controllers/laboratorios/update.php" method="post">
                                <div class="row">
                                    <!-- Campo oculto para el ID del laboratorio -->
                                    <input type="hidden" name="lab_id" value="<?= $lab_id; ?>">

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="lab_name">Nombre del laboratorio</label>
                                            <input type="text" class="form-control" id="lab_name" name="lab_name" value="<?= $lab_name; ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="description">Descripción</label>
                                            <textarea class="form-control" id="description" name="description" rows="4"><?= $description; ?></textarea>
                                        </div>
                                    </div>

                                    <!-- Campo para editar el área (Checkboxes) -->
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="area">Área(s)</label>
                                            <div>
                                                <?php foreach ($programs as $program): ?>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="area_<?= $program['area']; ?>" name="areas[]" value="<?= $program['area']; ?>"
                                                            <?php if (in_array($program['area'], explode(',', $area)))
                                                                echo 'checked'; ?>>
                                                        <label class="form-check-label" for="area_<?= $program['area']; ?>">
                                                            <?= $program['area']; ?>
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">Guardar</button>
                                            <a href="<?= APP_URL; ?>/admin/laboratorios" class="btn btn-secondary">Cancelar</a>
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
