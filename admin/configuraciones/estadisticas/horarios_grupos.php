<?php
// Incluir la configuración de la base de datos
require_once('../../../app/config.php'); // Ajusta la ruta según sea necesario
require '../../../vendor/autoload.php'; // PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

// Función para obtener los horarios de todos los grupos
function obtenerHorariosPorGrupo($pdo)
{
    $sql = "SELECT 
                g.group_id,
                g.group_name,
                sa.schedule_day AS day, 
                sa.start_time AS start_time, 
                sa.end_time AS end_time, 
                sa.tipo_espacio,
                s.subject_name, 
                sh.shift_name,
                r.classroom_name AS room_name,
                l.lab_name AS lab_name,
                RIGHT(r.building, 1) AS building_last_char,
                t.teacher_name
            FROM 
                schedule_assignments sa
            JOIN 
                subjects s ON sa.subject_id = s.subject_id
            JOIN 
                `groups` g ON sa.group_id = g.group_id
            JOIN 
                shifts sh ON g.turn_id = sh.shift_id
            LEFT JOIN 
                classrooms r ON sa.classroom_id = r.classroom_id
            LEFT JOIN 
                labs l ON sa.lab_id = l.lab_id
            LEFT JOIN 
                teachers t ON sa.teacher_id = t.teacher_id
            ORDER BY g.group_name, sa.schedule_day, sa.start_time";

    $query = $pdo->prepare($sql);
    $query->execute();
    $horarios = $query->fetchAll(PDO::FETCH_ASSOC);

    // Agrupar los horarios por grupo
    $horarios_por_grupo = [];
    foreach ($horarios as $horario) {
        $group_name = $horario['group_name'];
        if (!isset($horarios_por_grupo[$group_name])) {
            $horarios_por_grupo[$group_name] = [];
        }
        $horarios_por_grupo[$group_name][] = $horario;
    }

    return $horarios_por_grupo;
}

// Obtener los horarios por grupo
$horarios_por_grupo = obtenerHorariosPorGrupo($pdo);

// Crear un archivo ZIP
$zip = new ZipArchive();
$zip_file = 'Horarios_Por_Grupo.zip';

if ($zip->open($zip_file, ZipArchive::CREATE) !== TRUE) {
    exit("No se pudo crear el archivo ZIP.");
}

foreach ($horarios_por_grupo as $group_name => $horarios) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Encabezados del Excel
    $sheet->setCellValue('A1', 'Grupo');
    $sheet->setCellValue('B1', 'Materia');
    $sheet->setCellValue('C1', 'Profesor');
    $sheet->setCellValue('D1', 'Día');
    $sheet->setCellValue('E1', 'Hora Inicio');
    $sheet->setCellValue('F1', 'Hora Fin');
    $sheet->setCellValue('G1', 'Salón');
    $sheet->setCellValue('H1', 'Edificio');

    // Agregar los datos al Excel
    $fila = 2;
    foreach ($horarios as $horario) {
        $sheet->setCellValue("A$fila", $horario['group_name']);
        $sheet->setCellValue("B$fila", $horario['subject_name']);
        $sheet->setCellValue("C$fila", $horario['teacher_name']);
        $sheet->setCellValue("D$fila", $horario['day']);
        $sheet->setCellValue("E$fila", $horario['start_time']);
        $sheet->setCellValue("F$fila", $horario['end_time']);
        $sheet->setCellValue("G$fila", $horario['room_name']);
        $sheet->setCellValue("H$fila", $horario['building_last_char']);
        $fila++;
    }

    // Guardar el archivo Excel temporalmente
    $filename = "Horario_" . str_replace(' ', '_', $group_name) . ".xlsx";
    $temp_file = tempnam(sys_get_temp_dir(), $filename);
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save($temp_file);

    // Agregar el archivo Excel al ZIP
    $zip->addFile($temp_file, $filename);
}

// Cerrar el ZIP
$zip->close();

// Enviar el archivo ZIP al navegador para descargar
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zip_file . '"');
header('Content-Length: ' . filesize($zip_file));
readfile($zip_file);

// Eliminar el archivo ZIP después de enviarlo
unlink($zip_file);
exit;
