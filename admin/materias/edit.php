<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/materias/datos_de_materias.php');
include('../../app/controllers/programas/listado_de_programas.php');
include('../../app/controllers/cuatrimestres/listado_de_cuatrimestres.php');

$subject_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$subject_id) {
    echo "ID de materia inválido.";
    exit;
}

/* Obtener los datos de la materia a modificar */
$query = $pdo->prepare("SELECT * FROM subjects WHERE subject_id = :subject_id");
$query->bindParam(':subject_id', $subject_id);
$query->execute();
$materia = $query->fetch(PDO::FETCH_ASSOC);

if (!$materia) {
    echo "Materia no encontrada.";
    exit;
}

/* Extraer datos de la materia */
$subject_name = htmlspecialchars($materia['subject_name']);
$horas_consecutivas = $materia['hours_consecutive'];
$horas_semanales = $materia['weekly_hours'] ?? 0;
$is_specialization = $materia['is_specialization'];
$program_id = $materia['program_id'];
$term_id = $materia['term_id'];
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Modificar materia: <?= $subject_name; ?></h1>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title">Llene los datos</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?= APP_URL; ?>/app/controllers/materias/update.php" method="post">
                                <input type="hidden" name="subject_id" value="<?= $subject_id; ?>">

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">Nombre de la materia</label>
                                            <input type="text" name="nombres" value="<?= $subject_name; ?>" class="form-control" required>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">Horas Consecutivas</label>
                                            <input type="number" name="horas_consecutivas" value="<?= $horas_consecutivas; ?>" class="form-control" required>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">Horas Semanales</label>
                                            <input type="number" name="horas_semanales" value="<?= $horas_semanales; ?>" class="form-control" required>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">¿Es especialización?</label>
                                            <select name="is_specialization" class="form-control" required>
                                                <option value="0" <?= !$is_specialization ? 'selected' : ''; ?>>No</option>
                                                <option value="1" <?= $is_specialization ? 'selected' : ''; ?>>Sí</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">Programa</label>
                                            <select name="program_id" class="form-control" required>
                                                <?php foreach ($programs as $program): ?>
                                                    <option value="<?= $program['program_id']; ?>" <?= $program['program_id'] == $program_id ? 'selected' : ''; ?>>
                                                        <?= htmlspecialchars($program['programa']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">Cuatrimestre</label>
                                            <select name="term_id" class="form-control" required>
                                                <?php foreach ($terms as $term): ?>
                                                    <option value="<?= $term['term_id']; ?>" <?= $term['term_id'] == $term_id ? 'selected' : ''; ?>>
                                                        <?= htmlspecialchars($term['term_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary">Actualizar</button>
                                        <a href="<?= APP_URL; ?>/admin/materias" class="btn btn-secondary">Cancelar</a>
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
