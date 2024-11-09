<?php
include ('../../app/config.php');
include('../../app/helpers/verificar_admin.php');
include ('../../admin/layout/parte1.php');
include ('../../app/controllers/roles/datos_permisos.php');

/* Obtener el ID del rol desde el parámetro GET */
$id_rol = $_GET['id'];

/* Obtener los detalles del rol y sus permisos */
$rol = obtenerDatosRol($pdo, $id_rol);
$permisos = obtenerPermisos($pdo);
$permisos_asignados = obtenerPermisosAsignadosRol($pdo, $id_rol);
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
      <div class="container">
        <div class="row">
          <h1>Permisos para el rol: <?= $rol['nombre_rol']; ?></h1>
        </div>
        <div class="row">
        
            <div class="col-md-12">
                <div class="card card-outline card-warning">
                    <div class="card-header">
                        <h3 class="card-title">Gestionar permisos del rol</h3>
                    </div>
                    <div class="card-body">
                    <form action="<?= APP_URL;?>/app/controllers/roles/update_permisos.php" method="post">
                    <input type="hidden" name="id_rol" value="<?= $id_rol; ?>">
                    <table class="table table-striped table-bordered table-hover table-sm">
                        <thead>
                            <tr>
                                <th><center>Número</center></th>
                                <th><center>Descripción del Permiso</center></th>
                                <th><center>Asignar</center></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            $contador_permisos = 0;
                            foreach ($permisos as $permiso) {
                                $contador_permisos++;
                                $permiso_id = $permiso['id_permiso'];
                                $asignado = in_array($permiso_id, $permisos_asignados);
                        ?>
                            <tr>
                                <td style="text-align: center"><?= $contador_permisos; ?></td>
                                <td><?= $permiso['descripcion']; ?></td>
                                <td style="text-align: center">
                                    <input type="checkbox" name="permisos[]" value="<?= $permiso_id; ?>" <?= $asignado ? 'checked' : ''; ?>>
                                </td>
                            </tr>
                        <?php
                            }
                        ?>
                        </tbody>
                    </table>
                    <div class="form-group mt-3">
                        <button type="submit" class="btn btn-primary">Guardar Permisos del Rol</button>
                        <a href="<?= APP_URL; ?>/admin/roles" class="btn btn-secondary">Cancelar</a>
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
include ('../../admin/layout/parte2.php');
include ('../../layout/mensajes.php');
?>
