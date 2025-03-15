<?php
include('../../../app/config.php');
include('../../../app/helpers/verificar_admin.php');
include('../../../admin/layout/parte1.php');

// Función para obtener los grupos, en caso de que no exista
if (!function_exists('obtenerGrupos')) {
    function obtenerGrupos($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM `groups` ORDER BY group_name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Obtener el grupo seleccionado desde GET (filtrado como entero)
$group_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Obtener la lista de grupos
$grupos = obtenerGrupos($pdo);

if ($group_id) {
    // Consultar los horarios archivados para el grupo seleccionado, usando JOIN para obtener nombres de materia y profesor
    $stmt = $pdo->prepare("SELECT sh.*, s.subject_name, t.teacher_name 
                           FROM schedule_history sh
                           LEFT JOIN subjects s ON sh.subject_id = s.subject_id
                           LEFT JOIN teachers t ON sh.teacher_id = t.teacher_id
                           WHERE sh.group_id = :group_id 
                           ORDER BY FIELD(sh.schedule_day, 'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'), sh.start_time");
    $stmt->execute(['group_id' => $group_id]);
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($registros) {
        // Extraer las horas (start_time) y los días (schedule_day) presentes en los registros
        $horas = [];
        $dias = [];
        foreach ($registros as $registro) {
            $hora = $registro['start_time'];
            $dia  = $registro['schedule_day'];
            if (!in_array($hora, $horas)) {
                $horas[] = $hora;
            }
            if (!in_array($dia, $dias)) {
                $dias[] = $dia;
            }
        }
        sort($horas); // Ordena las horas ascendentemente
        $orden_dias = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
        $dias = array_values(array_intersect($orden_dias, $dias));

        // Inicializar la matriz para la cuadrícula de horarios
        $tabla_horarios = [];
        foreach ($horas as $hora) {
            foreach ($dias as $dia) {
                $tabla_horarios[$hora][$dia] = '';
            }
        }

        // Llenar la matriz con los registros, mostrando solo el nombre de la materia y el nombre del profesor
        foreach ($registros as $registro) {
            $hora = $registro['start_time'];
            $dia  = $registro['schedule_day'];
            $contenido = "Materia: " . $registro['subject_name'] . "<br>" .
                         "Profesor: " . $registro['teacher_name'];
            if (!empty($tabla_horarios[$hora][$dia])) {
                $tabla_horarios[$hora][$dia] .= "<hr>" . $contenido;
            } else {
                $tabla_horarios[$hora][$dia] = $contenido;
            }
        }

        // Obtener el nombre del grupo para mostrarlo en el título
        $nombre_grupo = '';
        foreach ($grupos as $grupo) {
            if ($grupo['group_id'] == $group_id) {
                $nombre_grupo = $grupo['group_name'];
                break;
            }
        }
    } else {
        $tabla_horarios = [];
        $horas = [];
        $dias = [];
        $nombre_grupo = '';
    }
} else {
    $tabla_horarios = [];
    $horas = [];
    $dias = [];
    $nombre_grupo = '';
}
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
                                    <option value="<?= $grupo['group_id']; ?>" <?= ($group_id == $grupo['group_id']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($grupo['group_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($group_id): ?>
            <div class="row">
                <h1>Horarios Archivados del Grupo <?= htmlspecialchars($nombre_grupo); ?></h1>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title">Detalles del Horario Archivado</h3>
                            <div class="form-group d-flex justify-content-end">
                                <a href="<?= APP_URL; ?>/admin/configuraciones/horarios" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Volver
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
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
                        </div><!-- /.card-body -->
                    </div><!-- /.card -->
                </div><!-- /.col -->
            </div><!-- /.row -->
            <?php else: ?>
            <div class="row">
                <div class="col-md-12">
                    <p>Seleccione un grupo para ver su horario archivado.</p>
                </div>
            </div>
            <?php endif; ?>

        </div><!-- /.container-fluid -->
    </div><!-- /.content -->
</div><!-- /.content-wrapper -->

<?php
include('../../../admin/layout/parte2.php');
include('../../../layout/mensajes.php');
?>

<!-- Inicialización de DataTables -->
<script>
$(function () {
    $("#example1").DataTable({
        "pageLength": 15,
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "language": {
            "emptyTable": "No hay información",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty": "Mostrando 0 a 0 de 0 registros",
            "infoFiltered": "(Filtrado de _MAX_ registros)",
            "lengthMenu": "Mostrar _MENU_ registros",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscador:",
            "zeroRecords": "Sin resultados encontrados",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        }
    });
});
</script>
