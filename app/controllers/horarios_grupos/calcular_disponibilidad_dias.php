<?php
/* Función para calcular la disponibilidad de días */
function calcularDisponibilidadDias($pdo, $group_id, $dias_turno, $inicio_turno, $fin_turno)
{
    $disponibilidad_dias = [];
    foreach ($dias_turno as $dia) {
        $horas_disponibles = 0;
        $inicio_actual = $inicio_turno;
        while ($inicio_actual < $fin_turno) {
            $formatted_start_time = date('H:i:s', $inicio_actual);
            $formatted_end_time = date('H:i:s', strtotime('+1 hour', $inicio_actual));

            /* Verificar si el grupo ya tiene asignada una materia en este horario */
            $check_group_availability_sql = "SELECT COUNT(*) FROM schedule_assignments 
                WHERE 
                    group_id = :group_id 
                    AND schedule_day = :schedule_day 
                    AND (
                        (start_time < :end_time AND end_time > :start_time)
                    )";
            $check_group_availability_params = [
                ':group_id' => $group_id,
                ':schedule_day' => $dia,
                ':start_time' => $formatted_start_time,
                ':end_time' => $formatted_end_time
            ];

            $check_group_availability = $pdo->prepare($check_group_availability_sql);
            $check_group_availability->execute($check_group_availability_params);

            if ($check_group_availability->fetchColumn() == 0) {
                $horas_disponibles++;
            }

            $inicio_actual = strtotime('+1 hour', $inicio_actual);
        }
        $disponibilidad_dias[$dia] = $horas_disponibles;
    }

    /* Ordenar los días por horas disponibles de mayor a menor */
    arsort($disponibilidad_dias);
    return array_keys($disponibilidad_dias);
}
?>
