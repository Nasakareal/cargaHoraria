<?php
include_once('../../../app/config.php');
include('../../../admin/layout/parte1.php');
?>

<div class="content-wrapper">
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Añadir Nuevo Calendario Escolar</h1>
            </div>
            <div class="row">
                <!-- Enviar datos al archivo create.php -->
                <form action="../../../app/controllers/calendario_escolar/create.php" method="POST">
                    <div class="form-group">
                        <label>Nombre del Calendario</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Fecha de Inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Fecha de Fin</label>
                        <input type="date" name="fecha_fin" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="estado" class="form-control">
                            <option value="ACTIVO">ACTIVO</option>
                            <option value="INACTIVO">INACTIVO</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include('../../../admin/layout/parte2.php');
include('../../../layout/mensajes.php');
?>
