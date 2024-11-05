<?php
include_once('../../../app/config.php');


ob_start();
session_start();

try {
    /* Limpiar las asignaciones previas de salones */
    $sql_limpiar = "UPDATE `groups` SET classroom_assigned = NULL";
    $stmt_limpiar = $pdo->prepare($sql_limpiar);
    $stmt_limpiar->execute();

    /* Obtener grupos con volumen > 0, excluyendo los turnos 'MIXTO' y 'ZINAPÉCUARO' */
    $sql_grupos = "
        SELECT g.group_id, g.group_name, g.volume AS capacidad_grupo, s.shift_name AS turn 
        FROM `groups` g
        JOIN shifts s ON g.turn_id = s.shift_id
        WHERE g.volume > 0 AND s.shift_name NOT IN ('MIXTO', 'ZINAPÉCUARO')
        ORDER BY g.volume DESC";
    $query_grupos = $pdo->prepare($sql_grupos);
    $query_grupos->execute();
    $grupos = $query_grupos->fetchAll(PDO::FETCH_ASSOC);

    /* Obtener salones disponibles ordenados por capacidad ascendente */
    $sql_salones = "SELECT classroom_id, classroom_name, building, capacity FROM classrooms WHERE estado = 'ACTIVO' ORDER BY capacity ASC";
    $query_salones = $pdo->prepare($sql_salones);
    $query_salones->execute();
    $salones_disponibles = $query_salones->fetchAll(PDO::FETCH_ASSOC);

    $grupos_con_salones = [];

    /* Asignación de salones */
    foreach ($grupos as $grupo) {
        $capacidad_grupo = $grupo['capacidad_grupo'];
        $turno_grupo = $grupo['turn'];
        $salon_asignado = null;

        /* Intentar asignar un salón que cumpla con la capacidad del grupo */
        foreach ($salones_disponibles as $salon) {
            if ($salon['capacity'] >= $capacidad_grupo) {
                // Concatenar el nombre del salón y el edificio como "classroom_name (último_dígito_del_edificio)"
                $salon_identificador = $salon['classroom_name'] . ' (' . substr($salon['building'], -1) . ')';
                
                /* Verificar si el salón ya está ocupado por otro grupo del mismo turno */
                $salon_ocupado = false;
                foreach ($grupos_con_salones as $grupo_asignado) {
                    if (
                        $grupo_asignado['salon_asignado'] === $salon_identificador &&
                        $grupo_asignado['turn'] === $turno_grupo
                    ) {
                        $salon_ocupado = true;
                        break;
                    }
                }

                /* Asignar el salón si no está ocupado */
                if (!$salon_ocupado) {
                    $salon_asignado = $salon_identificador;
                    $grupos_con_salones[] = [
                        'group_id' => $grupo['group_id'],
                        'salon_asignado' => $salon_asignado,
                        'turn' => $turno_grupo
                    ];
                    break;
                }
            }
        }

        /* Agregar el grupo con asignación o 'No disponible' si no encontró salón */
        if (!$salon_asignado) {
            $grupos_con_salones[] = [
                'group_id' => $grupo['group_id'],
                'salon_asignado' => 'No disponible',
                'turn' => $turno_grupo
            ];
        }
    }

    /* Guardar las asignaciones en la base de datos */
    foreach ($grupos_con_salones as $grupo_con_salon) {
        if ($grupo_con_salon['salon_asignado'] !== 'No disponible') {
            // Obtener el ID del salón correspondiente para actualizar la relación correctamente
            $classroom_id_query = "SELECT classroom_id FROM classrooms WHERE CONCAT(classroom_name, ' (', SUBSTRING(building, -1), ')') = :salon_asignado LIMIT 1";
            $stmt_classroom_id = $pdo->prepare($classroom_id_query);
            $stmt_classroom_id->execute([':salon_asignado' => $grupo_con_salon['salon_asignado']]);
            $classroom_id_result = $stmt_classroom_id->fetch(PDO::FETCH_ASSOC);

            if ($classroom_id_result) {
                $sql_update = "UPDATE `groups` SET classroom_assigned = :classroom_id WHERE group_id = :group_id";
                $stmt = $pdo->prepare($sql_update);
                $stmt->execute([
                    ':classroom_id' => $classroom_id_result['classroom_id'],
                    ':group_id' => $grupo_con_salon['group_id']
                ]);
            }
        }
    }

    /* Mensaje de éxito en la sesión */
    $_SESSION['mensaje'] = "Asignación de salones completada exitosamente.";
    $_SESSION['icono'] = "success";

} catch (Exception $e) {
    /* Mensaje de error en caso de falla */
    $_SESSION['mensaje'] = "No se pudo completar la asignación de salones. Contacte al área de IT.";
    $_SESSION['icono'] = "error";
}

/* Enviar todos los encabezados */
ob_end_clean();
header('Location: ' . APP_URL . "/portal/autoSalones/index.php");
exit();
