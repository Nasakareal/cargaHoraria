<?php
require('../../app/config.php');

try {
    $pdo = new PDO('mysql:host=localhost;dbname=cargaHoraria', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    function restaurarAsignacionesLaboratorio($pdo)
    {
        try {
            $sql_restore = "INSERT INTO schedule_assignments (subject_id, group_id, teacher_id, classroom_id, schedule_day, start_time, end_time, estado, fyh_creacion, tipo_espacio)
                            SELECT 
                                subject_id, 
                                group_id, 
                                NULL AS teacher_id, 
                                NULL  AS classroom_id, 
                                schedule_day, 
                                start_time, 
                                end_time, 
                                NULL, 
                                NOW(), 
                                'Laboratorio'
                            FROM manual_schedule_assignments
                            WHERE tipo_espacio = 'Laboratorio'";

            $rows_affected = $pdo->exec($sql_restore);

            echo "Filas insertadas: $rows_affected\n";
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }

    restaurarAsignacionesLaboratorio($pdo);
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
