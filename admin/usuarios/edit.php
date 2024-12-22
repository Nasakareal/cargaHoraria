<?php

$id_usuario = $_GET['id'];

include ('../../app/config.php');
include ('../../admin/layout/parte1.php');
include ('../../app/controllers/usuarios/datos_del_usuario.php');
include ('../../app/controllers/roles/listado_de_roles.php');

$sql_areas = "SELECT DISTINCT area FROM programs";
$query_areas = $pdo->prepare($sql_areas);
$query_areas->execute();
$areas = $query_areas->fetchAll(PDO::FETCH_ASSOC);
$user_areas = !empty($area) ? explode(',', $area) : [];
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
      <div class="container">
        <div class="row">
          <h1>Modificar usuario: <?=$nombres;?></h1>
        </div>
        <div class="row">
        
            <div class="col-md-12">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">Llene los datos</h3>
                    </div>
                    <div class="card-body">
                    <form action="<?= APP_URL;?>/app/controllers/usuarios/update.php" method="post">
                    <div class="row">
                      <?php if ($rol_id == 1): ?>
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="">Nombre del rol</label>
                          <input type="hidden" name="id_usuario" value="<?=$id_usuario;?>">
                          <div class="form-inline">
                          <select name="rol_id" class="form-control">
                          <?php 
                          foreach ($roles as $role){ 
                            $nombre_rol_tabla = $role['nombre_rol']?>
                            <option value="<?=$role['id_rol'];?>" <?php if($nombre_rol==$nombre_rol_tabla){ ?> selected="selected" <?php } ?> >
                                <?=$role['nombre_rol'];?>
                            </option>
                            <?php
                          }
                          ?>
                          </select>
                          <a href="<?=APP_URL;?>/admin/roles/create.php" style="margin-left: 5px" class="btn btn-primary"><i class="bi bi-file-plus"></i></a>
                          </div>
                        </div>
                      </div>
                      <?php endif; ?>
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="">Nombres del usuario</label>
                          <input type="text" name="nombres" value="<?=$nombres;?>" class="form-control" required>
                        </div>
                      </div>
                      
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="">Email</label>
                          <input type="email" name="email" value="<?=$email;?>" class="form-control" required>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="">Password</label>
                          <input type="password" name="password" class="form-control">
                          <small class="text-muted">Deje en blanco si no desea cambiar la contraseña</small>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="">Repetir Password</label>
                          <input type="password" name="password_repet" class="form-control">
                        </div>
                      </div>
                      <?php if ($rol_id == 1): ?>
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="">Áreas</label>
                          <div class="form-control" style="height:auto; padding:10px;">
                            <?php foreach ($areas as $area_item): ?>
                                <div class="form-check">
                                  <input class="form-check-input" type="checkbox" name="areas[]" value="<?= htmlspecialchars($area_item['area']); ?>" id="area_<?= htmlspecialchars($area_item['area']); ?>" 
                                  <?php if (in_array($area_item['area'], $user_areas)) echo 'checked'; ?>>
                                  <label class="form-check-label" for="area_<?= htmlspecialchars($area_item['area']); ?>">
                                    <?= htmlspecialchars($area_item['area']); ?>
                                  </label>
                                </div>
                            <?php endforeach; ?>
                          </div>
                        </div>
                      </div>
                      <?php endif; ?>
                    </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="">Foto de perfil</label>
                                <div>
                                    <a href="<?= APP_URL; ?>/admin/usuarios/cambiar_foto.php?id=<?= $id_usuario; ?>" class="btn btn-success">
                                        Cambiar foto de perfil
                                    </a>
                                </div>
                            </div>
                        </div>


                    <hr>
                    <div class="row">
                      <div class="col-md-12">
                        <div class="form-group">
                          <button type="submit" class="btn btn-primary">Actualizar</button>
                          <a href="<?=APP_URL;?>/admin/usuarios" class="btn btn-secondary">Cancelar</a>
                        </div>
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
include ('../../admin/layout/parte2.php');
include ('../../layout/mensajes.php');
?>
