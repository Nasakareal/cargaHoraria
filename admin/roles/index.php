<?php
include ('../../app/config.php');
include('../../app/helpers/verificar_admin.php');
include ('../../admin/layout/parte1.php');
include ('../../app/controllers/roles/listado_de_roles.php');
?>

<script>
  $(function () {
    $("#example1").DataTable({
      "pageLength": 5,
      "responsive": true, "lengthChange": false, "autoWidth": false,
      "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    $('#example2').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": false,
      "ordering": true,
      "info": true,
      "autoWidth": false,
      "responsive": true,
    });
  });
</script>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
      <div class="container">
        <div class="row">
          <h1>Listado de roles</h1>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Roles registrados</h3>
                        <div class="card-tools">
                            <a href="create.php" class="btn btn-primary"><i class="bi bi-plus-square"> Crear nuevo rol</i></a>
                        </div>
                    </div>

                    <div class="card-body">
                    <table id="example1" class="table table-striped table-bordered table-hover table-sm">
                        <thead>
                            <tr>
                                <th><center>Número</center></th>
                                <th><center>Nombre del rol</center></th>
                                <th><center>Acciones</center></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            $contador_rol = 0;
                            foreach ($roles as $role){
                                $id_rol = $role['id_rol']; 
                                $contador_rol++; ?>
                            <tr>
                                <td style ="text-align: center"><?=$contador_rol;?></td>
                                <td><?=$role['nombre_rol'];?></td>
                                
                                <td style="text-align: center">
                                <div class="btn-group" role="group" aria-label="Basic example">
                                    <a href="show.php?id=<?=$id_rol;?>" type="button" class="btn btn-info btn-sm"><i class="bi bi-eye"></i></a>
                                    <a href="edit.php?id=<?=$id_rol;?>" type="button" class="btn btn-success btn-sm"><i class="bi bi-pencil"></i></a>
                                    <a href="permissions.php?id=<?=$id_rol;?>" type="button" class="btn btn-warning btn-sm"><i class="bi bi-card-checklist"></i></a>
                                    <form action="<?=APP_URL;?>/app/controllers/roles/delete.php" onclick="preguntar<?=$id_rol;?>(event)" method="post" id="miFormulario<?=$id_rol;?>">
                                      <input type="text" name="id_rol" value="<?=$id_rol;?>" hidden>
                                      <button type="submit" class="btn btn-danger btn-sm" style="border-radius: 0px 5px 5px 0px"><i class="bi bi-trash"></i></button>
                                    </form>
                                    <script>
                                      function preguntar<?=$id_rol;?>(event){
                                        event.preventDefault();
                                        Swal.fire({
                                          title: 'Eliminar registro',
                                          text: '¿Desea eliminar este registro?',
                                          icon: 'question',
                                          showDenyButton: true,
                                          confirmButtonText: 'Eliminar',
                                          confirmButtonColor: '#a5161d',
                                          denyButtonColor: '#007bff',
                                          denyButtonText: 'Cancelar',
                                        }).then((result) => {
                                          if (result.isConfirmed) { 
                                            var form=$('#miFormulario<?=$id_rol;?>');
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
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php 
include ('../../admin/layout/parte2.php');
include ('../../layout/mensajes.php');
?>

<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 5,
            "language": {
              "emptyTable": "No hay información",
              "info": "Mostrando _START_ a _END_ de _TOTAL_ Roles",
              "infoEmpty": "Mostrando 0 a 0 de 0 Roles",
              "infoFiltered": "(Filtrado de _Max_ total Roles)",
              "infoPostFix": "",
              "thousands": ",",
              "lengthMenu": "Mostrar _MENU_ Roles",
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
              }]
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
