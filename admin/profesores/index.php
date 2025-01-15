<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/profesores/listado_de_profesores.php');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Listado de profesores</h1>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Profesores registrados</h3>
                            <br>
                            <div class="card-tools d-flex flex-column flex-md-row">
                                <!-- Botón para agregar nuevo profesor -->
                                <?php if (isset($_SESSION['sesion_rol']) && $_SESSION['sesion_rol'] == 1): ?>
                                <a href="create.php" class="btn btn-primary me-2 mb-2 mb-md-0">
                                    <i class="bi bi-plus-square"></i> Añadir nuevo profesor
                                </a>

                                <!-- Formulario para cargar profesores desde archivo (solo para administradores) -->
                                
                                <form action="<?= APP_URL; ?>/app/controllers/profesores/upload.php" method="post" enctype="multipart/form-data" class="d-flex align-items-center">
                                    <div class="form-group me-2">
                                        <label for="file" class="d-none">Selecciona un archivo CSV:</label>
                                        <input type="file" name="file" accept=".csv, .xlsx" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Cargar Profesores</button>
                                </form>
                                <?php else: ?>
                                <form class="d-flex align-items-center">
                                    <div class="form-group me-2">
                                        <label for="file" class="d-none">Selecciona un archivo CSV:</label>
                                        <input type="file" name="file" accept=".csv, .xlsx" disabled>
                                    </div>
                                    <button type="button" class="btn btn-primary disabled" aria-disabled="true" title="Solo disponible para administradores">Cargar Profesores</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card-body">
                            <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th><center>Número</center></th>
                                        <th><center>Nombres</center></th>
                                        <th><center>Clasificación</center></th>
                                        <th><center>Materias</center></th>
                                        <th><center>Horas Semanales</center></th>
                                        <th><center>Programas</center></th>
                                        <th><center>Acciones</center></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $contador_teachers = 0;
                                foreach ($teachers as $teacher) {
                                    $teacher_id = $teacher['teacher_id'];
                                    $contador_teachers++;

                                   
                                    $programas_texto = 'No asignado';
                                    if (!empty($teacher['programas'])) {
                                        $programas = explode(', ', $teacher['programas']);
                                        $programas_limitados = array_slice($programas, 0, 5);
                                        $programas_texto = implode(', ', $programas_limitados);
                                        if (count($programas) > 5) {
                                            $programas_texto .= ', ...';
                                        }
                                    }

                                    ?>
                                    <tr>
                                        <td style="text-align: center"><?= $contador_teachers; ?></td>
                                        <td><?= $teacher['profesor']; ?></td>
                                        <td><center><?= $teacher['clasificacion'] ?? 'No asignado'; ?></center></td>
                                        <td><center><?= $teacher['materias']; ?></center></td>
                                        <td><center><?= $teacher['horas_semanales']; ?></center></td>
                                        <td><center><?= $programas_texto; ?></center></td>
                                        
                                        <td style="text-align: center">
                                            <div class="btn-group" role="group" aria-label="Basic example">
                                                <a href="show.php?id=<?= $teacher_id; ?>" type="button" class="btn btn-info btn-sm"><i class="bi bi-eye"></i></a>
                                                <?php if (isset($_SESSION['sesion_rol']) && $_SESSION['sesion_rol'] == 1): ?>
                                                <a href="edit.php?id=<?= $teacher_id; ?>" type="button" class="btn btn-success btn-sm"><i class="bi bi-pencil"></i></a>
                                                <form action="<?= APP_URL; ?>/app/controllers/profesores/delete.php" onclick="preguntar<?= $teacher_id; ?>(event)" method="post" id="miFormulario<?= $teacher_id; ?>" style="display:inline;">
                                                    <input type="text" name="teacher_id" value="<?= $teacher_id; ?>" hidden>
                                                    <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                                                </form>
                                                <?php endif; ?>
                                                <a href="asignar_horario_profesor.php?teacher_id=<?= $teacher_id; ?>" class="btn btn-secondary btn-sm" style="background-color: #fd7e14;">
                                                    <i class="bi bi-journal-text"></i>
                                                </a>
                                                <a href="<?= APP_URL; ?>/admin/horarios_profesores/show.php?id=<?= $teacher['teacher_id']; ?>" class="btn btn-secondary btn-sm" style="background-color: #6f42c1;">
                                                    <i class="bi bi-clock"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                </tbody>
                            </table>
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

<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 5,
            "language": {
              "emptyTable": "No hay información",
              "info": "Mostrando _START_ a _END_ de _TOTAL_ Usuarios",
              "infoEmpty": "Mostrando 0 a 0 de 0 Usuarios",
              "infoFiltered": "(Filtrado de _MAX_ total Usuarios)",
              "infoPostFix": "",
              "thousands": ",",
              "lengthMenu": "Mostrar _MENU_ Usuarios",
              "loadingRecord": "Cargando...",
              "processing": "Procesando...",
              "search": "Buscador:",
              "zeroRecords": "Sin resultados encontrados",
              "paginate": {
                "first": "Primero",
                "last": "Ultimo",
                "next": "Siguiente",
                "previous": "Anterior"
              }
            },
            "responsive": true, "lengthChange": true, "autoWidth": false,
            buttons: [{
              extend: 'collection',
              text: 'Opciones',
              orientation: 'landscape',
              buttons: [{
                text: 'Copiar',
                extend: 'copy',
              }, {
                extend: 'pdf'
              }, {
                extend: 'csv'
              }, {
                extend: 'excel'
              }, {
                text: 'Imprimir',
                extend: 'print'
              }
              ]
            },
              {
                extend: 'colvis',
                text: 'Visor de columnas',
                collectionLayout: 'fixed three-column'
              }
            ],
        }) .buttons() .container() .appendTo('#example1_wrapper .col-md-6:eq(0)');
    });
</script>

<style>
    .dataTables_filter input {
        background-color: #ffd800;
        color: #333;
        border: 1px solid #555;
        border-radius: 4px;
        padding: 5px;
        font-weight: bold;
    }

    .dataTables_filter label {
        color: #333;
        font-weight: bold;
    }
</style>