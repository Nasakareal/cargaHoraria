<?php
include('../../app/config.php');
include('../../admin/layout/parte1.php');
include('../../app/controllers/horarios_grupos/grupos_disponibles.php');
include('../../app/controllers/horarios_grupos/obtener_horario_grupo.php');
include('../../app/controllers/horarios_grupos/procesar_horario_grupo.php');
include('../../app/controllers/horarios_grupos/listado_asignaciones.php');


$group_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);


$asignaciones = listarAsignaciones($pdo);


if ($group_id) {
    $resultado = procesarHorarioGrupo($group_id, $pdo);

    if (isset($resultado['error'])) {
        echo $resultado['error'];
        include('../../layout/parte2.php');
        exit;
    }

    $tabla_horarios = $resultado['tabla_horarios'];
    $turno = $resultado['turno'];
    $nombre_grupo = $resultado['nombre_grupo'];
    $horas = $resultado['horas'];
    $dias = $resultado['dias'];
} else {
    $tabla_horarios = [];
    $turno = null;
    $nombre_grupo = null;
    $horas = [];
    $dias = [];
}


$grupos = obtenerGrupos($pdo);
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">

        <!-- Filtro por Grupo -->
        <div class="row mb-4">
                <div class="col-md-6">
                    <form method="GET" action="">
                        <div class="form-group">
                            <label for="groupSelector">Seleccione un grupo:</label>
                            <select id="groupSelector" name="id" class="form-control" onchange="this.form.submit()">
                                <option value="">-- Seleccionar grupo --</option>
                                <?php foreach ($grupos as $grupo): ?>
                                    <option value="<?= $grupo['group_id']; ?>" <?= $group_id == $grupo['group_id'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($grupo['group_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">
                <!-- Actualizado para mostrar el nombre del grupo -->
                <h1>Horarios Asignados al Grupo <?= htmlspecialchars($nombre_grupo); ?> (Turno: <?= htmlspecialchars($turno); ?>)</h1> 
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title">Detalles del Horario</h3>

                            <div class="form-group d-flex justify-content-end">
                                <a href="<?= APP_URL; ?>/admin/horarios_grupos" class="btn btn-secondary">Volver</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <table id="example1" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Hora/Día</th>
                                                <?php foreach ($dias as $dia): ?>
                                                    <th><?= htmlspecialchars($dia); ?></th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($horas as $hora): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($hora); ?></td>
                                                    <?php foreach ($dias as $dia): ?>
                                                        <td><?= $tabla_horarios[$hora][$dia] ?? ''; ?></td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
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

<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 10,
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false,
            "dom": 'Bfrtip',
            buttons: [
                {
                    extend: 'collection',
                    text: 'Opciones',
                    buttons: [
                        'copy',
                        'csv',
                        {
                            text: 'PDF',
                            action: function () {
                                
                                let horarios = [];
                                $("#example1 tbody tr").each(function () {
                                    let fila = [];
                                    $(this).find('td').each(function () {
                                        fila.push($(this).html().trim());
                                    });
                                    horarios.push(fila);
                                });

                                
                                $.ajax({
                                    url: '../../app/controllers/horarios_grupos/generar_pdf.php',
                                    method: 'POST',
                                    data: { horarios: horarios },
                                    xhrFields: {
                                        responseType: 'blob'
                                    },
                                    success: function (response) {
                                        
                                        let blob = new Blob([response], { type: 'application/pdf' });
                                        let link = document.createElement('a');
                                        link.href = window.URL.createObjectURL(blob);
                                        link.download = "Horario_Personalizado.pdf";
                                        link.click();
                                    },
                                    error: function () {
                                        alert('Error al generar el PDF.');
                                    }
                                });
                            }
                        },
                        {
                            text: 'Imprimir',
                            action: function () {
                                
                                let horarios = [];
                                $("#example1 tbody tr").each(function () {
                                    let fila = [];
                                    $(this).find('td').each(function () {
                                        fila.push($(this).html().trim());
                                    });
                                    horarios.push(fila);
                                });

                                
                                $.post('../../app/controllers/horarios_grupos/imprimir_horario.php', { horarios: horarios }, function (data) {
                                    let w = window.open('');
                                    w.document.write(data);
                                    w.document.close();
                                });
                            }
                        },
                        {
                            text: 'Excel',
                            action: function () {
                                
                                let horarios = [];
                                $("#example1 tbody tr").each(function () {
                                    let fila = [];
                                    $(this).find('td').each(function () {
                                        fila.push($(this).html().trim());
                                    });
                                    horarios.push(fila);
                                });

                                
                                $.ajax({
                                    url: '../../app/controllers/horarios_grupos/generar_horario.php',
                                    method: 'POST',
                                    data: { horarios: horarios },
                                    xhrFields: {
                                        responseType: 'blob'
                                    },
                                    success: function (response) {
                                        
                                        let blob = new Blob([response], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                                        let link = document.createElement('a');
                                        link.href = window.URL.createObjectURL(blob);
                                        link.download = "Horario_Personalizado.xlsx";
                                        link.click();
                                    },
                                    error: function () {
                                        alert('Error al generar el archivo Excel.');
                                    }
                                });
                            }
                        }
                    ]
                },
                {
                    extend: 'colvis',
                    text: 'Visor de columnas'
                }
            ]
        });
    });
</script>
