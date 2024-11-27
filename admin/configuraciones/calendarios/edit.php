<?php
include('../../../app/config.php');

// Obtener el ID del calendario desde la URL
$id_calendario = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_calendario) {
    echo "<script>
        alert('ID de calendario inválido.');
        window.location.href = 'index.php';
    </script>";
    exit;
}

// Incluir dependencias necesarias
include('../../../admin/layout/parte1.php');
include('../../../app/helpers/verificar_admin.php');

// Consultar los datos del calendario
$query = $pdo->prepare("SELECT * FROM calendario_escolar WHERE id = :id");
$query->bindParam(':id', $id_calendario, PDO::PARAM_INT);
$query->execute();
$calendario = $query->fetch(PDO::FETCH_ASSOC);

if (!$calendario) {
    echo "<script>
        alert('Calendario no encontrado.');
        window.location.href = 'index.php';
    </script>";
    exit;
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Editar Calendario: <?= htmlspecialchars($calendario['nombre_cuatrimestre']); ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Calendarios</a></li>
                        <li class="breadcrumb-item active">Editar</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title">Actualizar Información del Calendario</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?= APP_URL; ?>/app/controllers/calendario_escolar/update.php" method="post">
                                <input type="hidden" name="id_calendario" value="<?= htmlspecialchars($id_calendario); ?>">

                                <!-- Nombre del cuatrimestre -->
                                <div class="form-group">
                                    <label for="nombre_cuatrimestre">Nombre del Cuatrimestre</label>
                                    <input type="text" name="nombre_cuatrimestre" id="nombre_cuatrimestre" class="form-control"
                                        value="<?= htmlspecialchars($calendario['nombre_cuatrimestre']); ?>" required>
                                </div>

                                <!-- Fecha de inicio -->
                                <div class="form-group">
                                    <label for="fecha_inicio">Fecha de Inicio</label>
                                    <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control"
                                        value="<?= htmlspecialchars($calendario['fecha_inicio']); ?>" required>
                                </div>

                                <!-- Fecha de fin -->
                                <div class="form-group">
                                    <label for="fecha_fin">Fecha de Fin</label>
                                    <input type="date" name="fecha_fin" id="fecha_fin" class="form-control"
                                        value="<?= htmlspecialchars($calendario['fecha_fin']); ?>" required>
                                </div>

                                <!-- Estado -->
                                <div class="form-group">
                                    <label for="estado">Estado</label>
                                    <select name="estado" id="estado" class="form-control" required>
                                        <option value="ACTIVO" <?= $calendario['estado'] === 'ACTIVO' ? 'selected' : ''; ?>>Activo</option>
                                        <option value="INACTIVO" <?= $calendario['estado'] === 'INACTIVO' ? 'selected' : ''; ?>>Inactivo</option>
                                    </select>
                                </div>

                                <!-- Botones de acción -->
                                <div class="form-group text-right">
                                    <button type="submit" class="btn btn-primary">Actualizar</button>
                                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
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
include('../../../admin/layout/parte2.php');
include('../../../layout/mensajes.php');
?>
