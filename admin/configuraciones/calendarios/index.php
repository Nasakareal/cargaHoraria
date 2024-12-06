<?php
include('../../../app/config.php');
include('../../../admin/layout/parte1.php');
include('../../../app/helpers/verificar_admin.php');
include('../../../app/controllers/calendario_escolar/listado_de_calendarios.php');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">
            <div class="row">
                <h1>Listado de Calendarios Escolares</h1>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Calendarios Registrados</h3>
                            <div class="card-tools">
                                <a href="create.php" class="btn btn-primary"><i class="bi bi-calendar-plus"> Crear nuevo calendario</i></a>
                            </div>
                        </div>

                        <div class="card-body">
                            <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th><center>Número</center></th>
                                        <th><center>Nombre del Calendario</center></th>
                                        <th><center>Fecha de Inicio</center></th>
                                        <th><center>Fecha de Fin</center></th>
                                        <th><center>Estado</center></th>
                                        <th><center>Acciones</center></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $contador_calendarios = 0;
                                foreach ($calendarios as $calendario) {
                                    $id_calendario = $calendario['id'];
                                    $contador_calendarios++; ?>
                                    <tr>
                                        <td style="text-align: center"><?= $contador_calendarios; ?></center></td>
                                        <td><center><?= htmlspecialchars($calendario['nombre_cuatrimestre']); ?></center></td>
                                        <td><center><?= htmlspecialchars($calendario['fecha_inicio']); ?></center></td>
                                        <td><center><?= htmlspecialchars($calendario['fecha_fin']); ?></center></td>
                                        <td><center><?= htmlspecialchars($calendario['estado']); ?></center></td>
                                        
                                        <td style="text-align: center">
                                        <div class="btn-group" role="group" aria-label="Basic example">
                                            <a href="show.php?id=<?= $id_calendario; ?>" type="button" class="btn btn-info btn-sm"> <i class="bi bi-eye"></i></a>
                                            <a href="edit.php?id=<?= $id_calendario; ?>" type="button" class="btn btn-success btn-sm"><i class="bi bi-pencil"></i></a>
                                            <form action="<?= APP_URL; ?>/app/controllers/calendario_escolar/delete.php" onclick="preguntar<?= $id_calendario; ?>(event)" method="post" id="miFormulario<?= $id_calendario; ?>">
                                              <input type="hidden" name="id" value="<?= $id_calendario; ?>">
                                              <button type="submit" class="btn btn-danger btn-sm" style="border-radius: 0px 5px 5px 0px"><i class="bi bi-trash"></i></button>
                                            </form>

                                            <script>
                                              function preguntar<?= $id_calendario; ?>(event) {
                                                event.preventDefault();
                                                Swal.fire({
                                                title: 'Eliminar Calendario',
                                                text: '¿Desea eliminar este calendario?',
                                                icon: 'question',
                                                showDenyButton: true,
                                                confirmButtonText: 'Eliminar',
                                                confirmButtonColor: '#a5161d',
                                                denyButtonColor: '#007bff',
                                                denyButtonText: 'Cancelar',
                                              }).then((result) => {
                                                if (result.isConfirmed) { 
                                                  var form=$('#miFormulario<?= $id_calendario; ?>');
                                                  form.submit();
                                                }
                                              });
                                              return false;
                                              }
                                            </script>
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
        </div>
    </div>
</div>

<?php
include('../../../admin/layout/parte2.php');
include('../../../layout/mensajes.php');
?>

<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 5,
            "language": {
              "emptyTable": "No hay información",
              "info": "Mostrando _START_ a _END_ de _TOTAL_ Calendarios",
              "infoEmpty": "Mostrando 0 a 0 de 0 Calendarios",
              "infoFiltered": "(Filtrado de _MAX_ total Calendarios)",
              "infoPostFix": "",
              "thousands": ",",
              "lengthMenu": "Mostrar _MENU_ Calendarios",
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
              buttons: [ {
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
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    });
</script>
