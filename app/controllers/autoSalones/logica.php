<?php
include_once('../../../app/config.php');

try {
    /* Limpiar las asignaciones previas de salones */
    $sql_limpiar = "UPDATE `groups` SET classroom_assigned = NULL";
    $stmt_limpiar = $pdo->prepare($sql_limpiar);
    $stmt_limpiar->execute();

    /* Obtener grupos con volumen > 0, excluyendo los turnos 'MIXTO' y 'ZINAPÉCUARO' */
    $sql_grupos = "
        SELECT g.group_id, g.group_name, g.volume AS capacidad_grupo, s.shift_name AS turn, g.area
        FROM `groups` g
        JOIN shifts s ON g.turn_id = s.shift_id
        WHERE g.volume > 0 AND s.shift_name NOT IN ('MIXTO', 'ZINAPÉCUARO')
        ORDER BY g.volume DESC";
    $query_grupos = $pdo->prepare($sql_grupos);
    $query_grupos->execute();
    $grupos = $query_grupos->fetchAll(PDO::FETCH_ASSOC);

    /* Obtener salones disponibles ordenados por capacidad ascendente */
    $sql_salones = "
        SELECT c.classroom_id, c.classroom_name, c.building, c.capacity
        FROM classrooms c
        WHERE c.estado = 'ACTIVO'
        ORDER BY c.capacity ASC";
    $query_salones = $pdo->prepare($sql_salones);
    $query_salones->execute();
    $salones_disponibles = $query_salones->fetchAll(PDO::FETCH_ASSOC);

    /* Obtener el mapeo de building_programs */
    $sql_building_programs = "SELECT building_name, area FROM building_programs";
    $query_building_programs = $pdo->prepare($sql_building_programs);
    $query_building_programs->execute();
    $building_programs_data = $query_building_programs->fetchAll(PDO::FETCH_ASSOC);

    /* Crear un mapeo de edificios a áreas */
    $building_programs = [];
    foreach ($building_programs_data as $bp) {
        $building_name = $bp['building_name'];
        $area = $bp['area'];
        if (!isset($building_programs[$building_name])) {
            $building_programs[$building_name] = [];
        }
        $building_programs[$building_name][] = $area;
    }

    $grupos_con_salones = [];

    /* Asignación de salones */
    foreach ($grupos as $grupo) {
        $capacidad_grupo = $grupo['capacidad_grupo'];
        $turno_grupo = $grupo['turn'];
        $area_grupo = $grupo['area'];
        $salon_asignado = null;

        /* Intentar asignar un salón que cumpla con la capacidad del grupo y el área según building_programs */
        foreach ($salones_disponibles as $salon) {
            $building = $salon['building'];
            $classroom_capacity = $salon['capacity'];

            /* Verificar si el building del salón es válido para el area del grupo */
            if (isset($building_programs[$building]) && in_array($area_grupo, $building_programs[$building])) {
                /* Verificar capacidad */
                if ($classroom_capacity >= $capacidad_grupo) {
                    $salon_identificador = $salon['classroom_name'] . ' (' . substr($building, -1) . ')';

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
            $classroom_id_query = "
                SELECT classroom_id
                FROM classrooms
                WHERE CONCAT(classroom_name, ' (', SUBSTRING(building, -1), ')') = :salon_asignado
                LIMIT 1";
            $stmt_classroom_id = $pdo->prepare($classroom_id_query);
            $stmt_classroom_id->execute([':salon_asignado' => $grupo_con_salon['salon_asignado']]);
            $classroom_id_result = $stmt_classroom_id->fetch(PDO::FETCH_ASSOC);

            if ($classroom_id_result) {
                $sql_update = "
                    UPDATE `groups`
                    SET classroom_assigned = :classroom_id
                    WHERE group_id = :group_id";
                $stmt = $pdo->prepare($sql_update);
                $stmt->execute([
                    ':classroom_id' => $classroom_id_result['classroom_id'],
                    ':group_id' => $grupo_con_salon['group_id']
                ]);
            }
        }
    }

    session_start();
    $_SESSION['mensaje'] = "Asignación de salones completada exitosamente.";
    $_SESSION['icono'] = "success";
    session_write_close();

    header('Location: ' . APP_URL . "/admin/autoSalones/index.php");
    exit();

} catch (Exception $e) {

    session_start();
    $_SESSION['mensaje'] = "No se pudo completar la asignación de salones. Contacte al área de IT.";
    $_SESSION['icono'] = "error";
    session_write_close();

    header('Location: ' . APP_URL . "/admin/autoSalones/index.php");
    exit();
}
?>
