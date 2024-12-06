<?php
include ('../app/config.php');
include ('../layout/parte1.php');
include('../app/controllers/profesores/listado_de_profesores.php');
include('../app/controllers/materias/listado_de_materias.php');
include('../app/controllers/programas/listado_de_programas.php');
include('../app/controllers/grupos/listado_de_grupos.php');
include('../app/controllers/cuatrimestres/listado_de_cuatrimestres.php');
?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <br>
    <div class="contentainer">
      <div class="container">
        <div class="row">
          <h1><?=APP_NAME;?></h1>
        </div>
        <br>
        <div class="row">

          
            <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
              <div class="inner">
                <?php
                $contador_teachers = 0;
                foreach ($teachers as $teacher) {
                    $contador_teachers++;
                }
                ?>
                <h3><?= $contador_teachers; ?></h3>
                <p>Profesores registrados</p>
              </div>
              <div class="icon">
                <i class="fas"><i class="bi bi-people-fill"></i></i>
              </div>
              <a href="<?= APP_URL; ?>/portal/profesores" class="small-box-footer">
                Más información <i class="fas fa-arrow-circle-right"></i>
              </a>
            </div>
          </div>

            <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
              <div class="inner">
                <?php
                $contador_subjects = 0;
                foreach ($subjects as $subject) {
                    $contador_subjects++;
                }
                ?>
                <h3><?= $contador_subjects; ?></h3>
                <p>Materias registradas</p>
              </div>
              <div class="icon">
                <i class="fas"><i class="bi bi-journal-bookmark-fill"></i></i>
              </div>
              <a href="<?= APP_URL; ?>/portal/materias" class="small-box-footer">
                Más información <i class="fas fa-arrow-circle-right"></i>
              </a>
            </div>
          </div>

            <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
              <div class="inner">
                <?php
                $contador_programs = 0;
                foreach ($programs as $program) {
                    $contador_programs++;
                }
                ?>
                <h3><?= $contador_programs; ?></h3>
                <p>Programas registrados</p>
              </div>
              <div class="icon">
                <i class="fas"><i class="bi bi-backpack2"></i></i>
              </div>
              <a href="<?= APP_URL; ?>/portal/programas" class="small-box-footer">
                Más información <i class="fas fa-arrow-circle-right"></i>
              </a>
            </div>
          </div>

            <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
              <div class="inner">
                <?php
                $contador_groups = 0;
                foreach ($groups as $group) {
                    $contador_groups++;
                }
                ?>
                <h3><?= $contador_groups; ?></h3>
                <p>Grupos registrados</p>
              </div>
              <div class="icon">
                <i class="fas"><i class="bi bi-boxes"></i></i>
              </div>
              <a href="<?= APP_URL; ?>/portal/grupos" class="small-box-footer">
                Más información <i class="fas fa-arrow-circle-right"></i>
              </a>
            </div>
          </div>

            <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
              <div class="inner">
                <?php
                $contador_terms = 0;
                foreach ($terms as $term) {
                    $contador_terms++;
                }
                ?>
                <h3><?= $contador_terms; ?></h3>
                <p>Cuatrimestres registrados</p>
              </div>
              <div class="icon">
                <i class="fas"><i class="bi bi-calendar3"></i></i>
              </div>
              <a href="<?= APP_URL; ?>/portal/cuatrimestres" class="small-box-footer">
                Más información <i class="fas fa-arrow-circle-right"></i>
              </a>
            </div>
          </div>

            

          

        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>

  <div>
    <p>Hello There</p>
  </div>
  <!-- /.content-wrapper -->

<?php 
  
include ('../layout/parte2.php');
include ('../layout/mensajes.php');
  
?>