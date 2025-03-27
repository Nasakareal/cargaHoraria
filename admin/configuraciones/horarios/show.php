<?php
include('../../../app/config.php');
include('../../../app/helpers/verificar_admin.php');
include('../../../admin/layout/parte1.php');

if (!function_exists('obtenerGrupos')) {
    function obtenerGrupos($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM `groups` ORDER BY group_name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (!function_exists('obtenerProfesores')) {
    function obtenerProfesores($pdo) {
        $stmt = $pdo->prepare("SELECT teacher_id, teacher_name FROM teachers ORDER BY teacher_name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$grupos = obtenerGrupos($pdo);
$profesores = obtenerProfesores($pdo);
$group_id   = filter_input(INPUT_GET, 'group_id', FILTER_VALIDATE_INT);
$teacher_id = filter_input(INPUT_GET, 'teacher_id', FILTER_VALIDATE_INT);
$registros = [];
$nombre_filtro = '';
$horas = [];
$dias = [];
$tabla_horarios = [];

if ($group_id) {
    // Filtramos por grupo
    $stmt = $pdo->prepare("
        SELECT sh.*, s.subject_name, t.teacher_name 
        FROM schedule_history sh
        LEFT JOIN subjects s ON sh.subject_id = s.subject_id
        LEFT JOIN teachers t ON sh.teacher_id = t.teacher_id
        WHERE sh.group_id = :group_id
        ORDER BY FIELD(sh.schedule_day, 'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'), sh.start_time
    ");
    $stmt->execute(['group_id' => $group_id]);
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener nombre del grupo para mostrar
    foreach ($grupos as $g) {
        if ($g['group_id'] == $group_id) {
            $nombre_filtro = $g['group_name'];
            break;
        }
    }
}

elseif ($teacher_id) {
    $stmt = $pdo->prepare("
        SELECT sh.*, s.subject_name, t.teacher_name 
        FROM schedule_history sh
        LEFT JOIN subjects s ON sh.subject_id = s.subject_id
        LEFT JOIN teachers t ON sh.teacher_id = t.teacher_id
        WHERE sh.teacher_id = :teacher_id
        ORDER BY FIELD(sh.schedule_day, 'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'), sh.start_time
    ");
    $stmt->execute(['teacher_id' => $teacher_id]);
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener nombre del profesor para mostrar
    foreach ($profesores as $p) {
        if ($p['teacher_id'] == $teacher_id) {
            $nombre_filtro = $p['teacher_name'];
            break;
        }
    }
}

if (!empty($registros)) {
    // Sacar las horas y días disponibles
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
    // Ordenar horas
    sort($horas);

    // Ordenar días
    $orden_dias = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    $dias = array_values(array_intersect($orden_dias, $dias));

    // Crear matriz vacía para la tabla
    foreach ($horas as $hr) {
        foreach ($dias as $d) {
            $tabla_horarios[$hr][$d] = '';
        }
    }

    // Rellenar la matriz
    foreach ($registros as $registro) {
        $hora = $registro['start_time'];
        $dia  = $registro['schedule_day'];
        $contenido = "Materia: " . $registro['subject_name'] . "<br>" .
                     "Profesor: " . $registro['teacher_name'];

        // Si ya hubiera algo (caso de 2 materias en misma hora/día)
        if (!empty($tabla_horarios[$hora][$dia])) {
            $tabla_horarios[$hora][$dia] .= "<hr>" . $contenido;
        } else {
            $tabla_horarios[$hora][$dia] = $contenido;
        }
    }
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <br>
    <div class="content">
        <div class="container">

            <!-- Form con dos selects: Grupo y Profesor -->
            <div class="card card-outline card-info mb-4">
                <div class="card-header">
                    <h3 class="card-title">Filtrar Horarios Archivados</h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="row">
                            <!-- Seleccionar un Grupo -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="group_id">Seleccione un grupo:</label>
                                    <select id="group_id" name="group_id" class="form-control">
                                        <option value="">-- Ninguno --</option>
                                        <?php foreach ($grupos as $g): ?>
                                            <option value="<?= $g['group_id']; ?>"
                                                <?php if ($group_id == $g['group_id']) echo 'selected'; ?>>
                                                <?= htmlspecialchars($g['group_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <!-- Seleccionar un Profesor -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="teacher_id">Seleccione un profesor:</label>
                                    <select id="teacher_id" name="teacher_id" class="form-control">
                                        <option value="">-- Ninguno --</option>
                                        <?php foreach ($profesores as $p): ?>
                                            <option value="<?= $p['teacher_id']; ?>"
                                                <?php if ($teacher_id == $p['teacher_id']) echo 'selected'; ?>>
                                                <?= htmlspecialchars($p['teacher_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!-- Botón para Filtrar -->
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Ver Horarios
                        </button>
                    </form>
                </div>
            </div>

            <!-- Mostrar resultados si hay un filtro (grupo o profesor) y registros -->
            <?php if ($group_id || $teacher_id): ?>
                <div class="row mb-2">
                    <div class="col">
                        <?php if (!empty($registros)): ?>
                            <?php if ($group_id): ?>
                                <h2>Horarios Archivados del Grupo: 
                                    <?= htmlspecialchars($nombre_filtro); ?>
                                </h2>
                            <?php else: ?>
                                <h2>Horarios Archivados del Profesor: 
                                    <?= htmlspecialchars($nombre_filtro); ?>
                                </h2>
                            <?php endif; ?>
                        <?php else: ?>
                            <h2>No hay horarios para el filtro seleccionado.</h2>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="row mb-2">
                    <div class="col">
                        <p>Seleccione <strong>un grupo</strong> o <strong>un profesor</strong> para ver horarios archivados.</p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Tabla de Horarios -->
            <?php if (!empty($tabla_horarios)): ?>
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
                                        <?php foreach ($horas as $hr): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($hr); ?></td>
                                                <?php foreach ($dias as $d): ?>
                                                    <td><?= $tabla_horarios[$hr][$d] ?? ''; ?></td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div><!-- /.card-body -->
                        </div><!-- /.card -->
                    </div><!-- /.col -->
                </div><!-- /.row -->
            <?php endif; ?>

        </div><!-- /.container-fluid -->
    </div><!-- /.content -->
</div><!-- /.content-wrapper -->

<?php
include('../../../admin/layout/parte2.php');
include('../../../layout/mensajes.php');
?>

<!-- Inicialización de DataTables y lógica para limpiar la otra selección -->
<script>
$(function () {
    // DataTables
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

    $('#group_id').on('change', function() {
        if ($(this).val() !== '') {
            $('#teacher_id').val('');
        }
    });

    $('#teacher_id').on('change', function() {
        if ($(this).val() !== '') {
            $('#group_id').val('');
        }
    });
});
</script>
