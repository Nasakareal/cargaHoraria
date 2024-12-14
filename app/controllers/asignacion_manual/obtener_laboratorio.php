<?php
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $group_id = $_GET['id'];

    $queryMaterias = $pdo->prepare("SELECT m.subject_id, m.subject_name FROM subjects m 
                                    INNER JOIN group_subjects gs ON m.subject_id = gs.subject_id
                                    WHERE gs.group_id = :group_id");
    $queryMaterias->bindParam(':group_id', $group_id, PDO::PARAM_INT);
    $queryMaterias->execute();
    $materias = $queryMaterias->fetchAll(PDO::FETCH_ASSOC);

    $lab_id = isset($_GET['lab_id']) ? $_GET['lab_id'] : null;

    $queryAsignaciones = "SELECT a.assignment_id, a.subject_id, m.subject_name, a.start_time, a.end_time, a.schedule_day, a.lab1_assigned, a.lab2_assigned
                          FROM manual_schedule_assignments a
                          INNER JOIN subjects m ON a.subject_id = m.subject_id
                          WHERE a.group_id = :group_id";

    if ($lab_id) {
        if ($lab_id == 1) {
            $queryAsignaciones .= " AND a.lab1_assigned = 1";
        } elseif ($lab_id == 2) {
            $queryAsignaciones .= " AND a.lab2_assigned = 1";
        }
    }

    $queryAsignaciones .= " ORDER BY a.start_time";

    $stmt = $pdo->prepare($queryAsignaciones);
    $stmt->bindParam(':group_id', $group_id, PDO::PARAM_INT);
    $stmt->execute();
    $asignaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $events = [];
    foreach ($asignaciones as $asignacion) {
        $daysOfWeek = ['lunes' => 1, 'martes' => 2, 'miércoles' => 3, 'jueves' => 4, 'viernes' => 5];
        $schedule_day_lower = strtolower($asignacion['schedule_day']);

        if (!isset($daysOfWeek[$schedule_day_lower])) {
            echo "Día inválido: " . $asignacion['schedule_day'] . "<br>";
            continue;
        }

        $dayOfWeek = $daysOfWeek[$schedule_day_lower];
        $start_date = new DateTime();
        $start_date->setISODate($start_date->format('Y'), $start_date->format('W'), $dayOfWeek);
        $start_date->setTime(substr($asignacion['start_time'], 0, 2), substr($asignacion['start_time'], 3, 2));
        $end_date = clone $start_date;
        $end_date->setTime(substr($asignacion['end_time'], 0, 2), substr($asignacion['end_time'], 3, 2));
        $start_datetime = $start_date->format('Y-m-d\TH:i:s');
        $end_datetime = $end_date->format('Y-m-d\TH:i:s');

        $events[] = [
            'title' => htmlspecialchars($asignacion['subject_name']),
            'start' => $start_datetime,
            'end' => $end_datetime,
            'subject_id' => $asignacion['subject_id'],
            'assignment_id' => $asignacion['assignment_id'],
            'backgroundColor' => '#FF5733',
            'borderColor' => '#FF5733',
            'textColor' => '#fff'
        ];
    }

    $events_json = json_encode($events);
}
?>
