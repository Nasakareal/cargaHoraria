<?php
include('../../app/config.php');

$teacher_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$teacher_id) {
    echo "ID de profesor inválido.";
    exit;
}

include('../../admin/layout/parte1.php');
include('../../app/controllers/profesores/datos_del_profesor.php');
include('../../app/controllers/programas/listado_de_programas.php');

/* Inicializar variables para evitar errores de 'undefined variable' */
$clasificacion = isset($clasificacion) ? $clasificacion : '';
$specialization_program_id = isset($specialization_program_id) ? $specialization_program_id : '';
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Modificar profesor: <?= htmlspecialchars($nombres); ?></h1>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title">Llene los datos</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?= APP_URL; ?>/app/controllers/profesores/update.php" method="post">
                                <input type="hidden" name="teacher_id" value="<?= htmlspecialchars($teacher_id); ?>">

                                <!-- Datos del profesor -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">Nombres del profesor</label>
                                            <input type="text" name="nombres" value="<?= htmlspecialchars($nombres); ?>" class="form-control" required>
                                        </div>
                                    </div>

                                    <!-- Clasificación -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="clasificacion">Clasificación</label>
                                            <select name="clasificacion" id="clasificacion" class="form-control" required>
                                                <option value="PTC" <?= ($clasificacion == 'PTC') ? 'selected' : ''; ?>>PTC</option>
                                                <option value="PTA" <?= ($clasificacion == 'PTA') ? 'selected' : ''; ?>>PTA</option>
                                                <option value="TA" <?= ($clasificacion == 'TA') ? 'selected' : ''; ?>>TA</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Programa de Adscripción -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">Programa de Adscripción</label>
                                            <select name="programa_id" id="programa_id" class="form-control" required>
                                                <option value="">Seleccione un programa</option>
                                                <?php foreach ($programs as $program): ?>
                                                    <option value="<?= $program['program_id']; ?>" <?= ($specialization_program_id == $program['program_id']) ? 'selected' : ''; ?>>
                                                        <?= htmlspecialchars($program['program_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Botón de actualización -->
                                <div class="row" style="margin-top:20px;">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary">Actualizar</button>
                                        <a href="<?= APP_URL; ?>/admin/profesores" class="btn btn-secondary">Cancelar</a>
                                    </div>
                                </div>
                            </form>
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
