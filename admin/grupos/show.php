<?php

$group_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$group_id) {
    header('Location: ' . APP_URL . '/admin/grupos');
    exit;
}

include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/grupos/datos_del_grupo.php');
include('../../app/controllers/horarios_grupos/grupos_disponibles.php');
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

    <!-- Selector de Grupos -->
    <div class="container">
        <form method="GET" action="">
            <div class="form-group">
                <label for="groupSelector">Seleccione un grupo:</label>
                <select id="groupSelector" name="id" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Seleccionar grupo --</option>
                    <?php foreach ($grupos as $grupo): ?>
                        <option value="<?= $grupo['group_id']; ?>" <?= isset($_GET['id']) && $_GET['id'] == $grupo['group_id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($grupo['group_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>

    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Datos del Grupo</h1>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title">Datos del Grupo</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Nombre del grupo</label>
                                        <p><?= htmlspecialchars($group_name); ?></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Programa educativo</label>
                                        <p><?= htmlspecialchars($program_name); ?></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Cuatrimestre</label>
                                        <p><?= htmlspecialchars($term_name); ?></p>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Volumen</label>
                                        <p><?= htmlspecialchars($volumen_grupo); ?></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Turno</label>
                                        <p><?= htmlspecialchars($turno); ?></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Nivel educativo</label>
                                        <p><?= htmlspecialchars($nivel_educativo); ?></p>
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <!-- Sección de materias -->
                            <div class="row">
                                <div class="col-md-12">
                                    <h3>Materias del Grupo</h3>
                                    <ul>
                                        <?php if (!empty($materias)): ?>
                                            <?php foreach (explode(', ', $materias) as $materia): ?>
                                                <?php
                                                // Separamos la información de la materia en "Materia" y sus horas
                                                $parts = explode('Lab:', $materia);
                                                $materiaPrincipal = trim($parts[0]); // Nombre y horas totales
                                                $labInfo = isset($parts[1]) ? 'Lab:' . trim($parts[1]) : ''; // Información del laboratorio
                                                ?>
                                                <li>
                                                    <?= htmlspecialchars($materiaPrincipal); ?>
                                                    <?= $labInfo ? ' - ' . htmlspecialchars($labInfo) : ''; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <li>No hay materias asignadas a este grupo.</li>
                                        <?php endif; ?>
                                    </ul>

                                </div>
                            </div>
                            <!-- Fin de sección de materias -->

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <a href="<?= APP_URL; ?>/admin/grupos" class="btn btn-secondary">Volver</a>
                                    </div>
                                </div>
                            </div>
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
