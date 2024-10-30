<?php
include_once('../../../app/config.php');

/* Asegúrate de que no haya salida antes de `session_start()` para evitar el error de encabezados */
ob_start();
session_start();

try {
    /* Obtenemos los grupos con volumen mayor a 0 y sus turnos */
    $sql_grupos = "
        SELECT g.group_id, g.group_name, g.volume AS capacidad_grupo, s.shift_name AS turn 
        FROM `groups` g
        JOIN shifts s ON g.turn_id = s.shift_id
        WHERE g.volume > 0
        ORDER BY g.volume DESC"; /* Prioridad de mayor a menor volumen */

    $query_grupos = $pdo->prepare($sql_grupos);
    $query_grupos->execute();
    $grupos = $query_grupos->fetchAll(PDO::FETCH_ASSOC);

    /* Obtenemos los salones disponibles ordenados por capacidad ascendente */
    $sql_salones = "SELECT * FROM classrooms WHERE estado = 'ACTIVO' ORDER BY capacity ASC";
    $query_salones = $pdo->prepare($sql_salones);
    $query_salones->execute();
    $salones_disponibles = $query_salones->fetchAll(PDO::FETCH_ASSOC);

    $grupos_con_salones = [];

    /* Lógica de asignación de salones */
    foreach ($grupos as $grupo) {
        $capacidad_grupo = $grupo['capacidad_grupo'];
        $turno_grupo = isset($grupo['turn']) ? $grupo['turn'] : null;
        $salon_asignado = null;

        /* Saltar asignación para turnos 'MIXTO' y 'ZINAPÉCUARO' */
        if ($turno_grupo === 'MIXTO' || $turno_grupo === 'ZINAPÉCUARO') {
            $grupos_con_salones[] = [
                'group_id' => $grupo['group_id'],
                'salon_asignado' => 'No disponible'
            ];
            continue;
        }

        /* Asignación de salones */
        foreach ($salones_disponibles as $salon) {
            if ($salon['capacity'] >= $capacidad_grupo) {
                $salon_ocupado = false;

                /* Verificar si el salón ya está asignado a otro grupo del mismo turno */
                foreach ($grupos_con_salones as $grupo_asignado) {
                    $salon_identificador = $salon['classroom_name'] . ' (' . substr($salon['building'], -1) . ')';
                    if (
                        isset($grupo_asignado['salon_asignado']) &&
                        $grupo_asignado['salon_asignado'] === $salon_identificador &&
                        $grupo_asignado['turn'] === $turno_grupo
                    ) {
                        $salon_ocupado = true;
                        break;
                    }
                }

                /* Asignar el salón si no está ocupado */
                if (!$salon_ocupado) {
                    $salon_asignado = $salon['classroom_name'] . ' (' . substr($salon['building'], -1) . ')';
                    break;
                }
            }
        }

        /* Agregar el grupo con la asignación de salón */
        $grupos_con_salones[] = [
            'group_id' => $grupo['group_id'],
            'salon_asignado' => $salon_asignado ?: 'No disponible',
            'turn' => $turno_grupo
        ];
    }

    /* Guardar las asignaciones en la base de datos */
    foreach ($grupos_con_salones as $grupo_con_salon) {
        if ($grupo_con_salon['salon_asignado'] !== 'No disponible') {
            $sql_update = "UPDATE `groups` SET classroom_assigned = :salon_asignado WHERE group_id = :group_id";
            $stmt = $pdo->prepare($sql_update);
            $stmt->execute([
                ':salon_asignado' => $grupo_con_salon['salon_asignado'],
                ':group_id' => $grupo_con_salon['group_id']
            ]);
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
header('Location: ' . APP_URL . "/admin/autoSalones/index.php");
exit();
