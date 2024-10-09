<?php

function asignarHorarios($pdo, $group_id)
{
    $sql = "SELECT * FROM schedules WHERE group_id = :group_id ORDER BY start_time";
    $query = $pdo->prepare($sql);
    $query->execute(['group_id' => $group_id]);
    $horarios = $query->fetchAll(PDO::FETCH_ASSOC);

    // Lógica para validar que no se asignen más de 2 horas seguidas
    $valid_horarios = [];
    foreach ($horarios as $horario) {
        $hora_inicio = new DateTime($horario['start_time']);
        $hora_fin = new DateTime($horario['end_time']);
        
        // Verificar si el horario ya está asignado
        $conflicto = false;
        foreach ($valid_horarios as $valido) {
            $valido_inicio = new DateTime($valido['start_time']);
            $valido_fin = new DateTime($valido['end_time']);
            
            // Si hay un conflicto de horarios, marcar como conflicto
            if ($hora_inicio < $valido_fin && $hora_fin > $valido_inicio) {
                $conflicto = true;
                break;
            }
        }
        
        // Solo agregar si no hay conflicto y no excede las 2 horas
        if (!$conflicto && $hora_fin->diff($hora_inicio)->h <= 2) {
            $valid_horarios[] = $horario;
        }
    }
    
    return $valid_horarios;
}
