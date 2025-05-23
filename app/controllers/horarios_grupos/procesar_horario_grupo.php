<?php
function procesarHorarioGrupo($group_id, $pdo)
{
    $horarios = obtenerHorarioGrupo($group_id, $pdo);

    if (empty($horarios)) {
        return [
            'error' => "<p class='text-center text-muted'>No se encontraron horarios asignados para este grupo.</p>
                        <div class='text-center'><a href='../../admin/horarios_grupos' class='btn btn-secondary'>Volver</a></div>",
        ];
    }

    $turno = $horarios[0]['shift_name'] ?? 'Turno no especificado';
    $nombre_grupo = $horarios[0]['group_name'] ?? 'Grupo no especificado';

    $horas = [];
    $dias = [];

    switch ($turno) {
        case 'MATUTINO':
            $horas = ['07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00'];
            $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            break;
        case 'VESPERTINO':
            $horas = ['11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00'];
            $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            break;
        case 'MIXTO':
        case 'ZINAPÉCUARO':
            $horas = ['07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00'];
            $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            break;
        case 'ENFERMERIA':
            $horas = ['07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00'];
            $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            break;
        case 'MATUTINO AVANZADO':
            $horas = ['07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00'];
            $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            break;
        case 'VESPERTINO AVANZADO':
            $horas = ['11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00'];
            $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            break;

        default:
            return [
                'error' => "<p class='text-center text-muted'>Turno no reconocido.</p>
                            <div class='text-center'><a href='../../admin/horarios_grupos' class='btn btn-secondary'>Volver</a></div>",
            ];
    }

    $tabla_horarios = [];
    foreach ($horas as $hora) {
        foreach ($dias as $dia) {
            $tabla_horarios[$hora][$dia] = '';
        }
    }

    foreach ($horarios as $horario) {
        $start_time = strtotime($horario['start']);
        $end_time = strtotime($horario['end']);
        $dia = $horario['day'];
        $materia = $horario['subject_name'];
        $profesor = $horario['teacher_name'] ?? 'Sin profesor';
        $lab_name = $horario['lab_name'] ?? null;
        $room_name = $horario['room_name'] ?? null;

        if (!empty($lab_name)) {
            $espacio = htmlspecialchars($lab_name);
        } elseif (!empty($room_name)) {
            $building_last_char = $horario['building_last_char'] ?? '';
            if ($building_last_char !== '') {
                $espacio = htmlspecialchars($building_last_char) . "-" . htmlspecialchars($room_name);
            } else {
                $espacio = htmlspecialchars($room_name);
            }
        } else {
            $espacio = 'Sin salón ni laboratorio';
        }

        $contenido = "{$materia} - {$espacio} - {$profesor}";

        for ($current_time = $start_time; $current_time < $end_time; $current_time = strtotime("+1 hour", $current_time)) {
            $hora = date("H:i", $current_time);

            if (in_array($hora, $horas) && in_array($dia, $dias)) {
                if (empty($tabla_horarios[$hora][$dia])) {
                    $tabla_horarios[$hora][$dia] = $contenido;
                } else {
                    $tabla_horarios[$hora][$dia] .= "<br>" . $contenido;
                }
            }
        }
    }

    return [
        'tabla_horarios' => $tabla_horarios,
        'turno' => $turno,
        'nombre_grupo' => $nombre_grupo,
        'horas' => $horas,
        'dias' => $dias,
    ];
}
?>
