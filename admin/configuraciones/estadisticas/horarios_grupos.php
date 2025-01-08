<?php
require_once('../../../app/config.php');
require '../../../vendor/autoload.php';

ini_set('memory_limit', '2G');
set_time_limit(0);

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

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

function logError($message) {
    file_put_contents(__DIR__ . '/error_log.txt', $message . "\n", FILE_APPEND);
}

$temp_dir = sys_get_temp_dir() . '/horarios_temp_' . uniqid();
if (!mkdir($temp_dir, 0777, true)) {
    exit("No se pudo crear el directorio temporal para almacenar los archivos Excel.");
}

$horarios_por_grupo = obtenerHorariosPorGrupo($pdo);

if (empty($horarios_por_grupo)) {
    exit("No se encontraron horarios.");
}

$template_path = __DIR__ . '/plantilla.xlsx';
if (!file_exists($template_path)) {
    exit("La plantilla 'plantilla.xlsx' no existe en el directorio " . __DIR__);
}

$zip = new ZipArchive();
$zip_file = 'Horarios_Por_Grupo.zip';

if (file_exists($zip_file)) {
    unlink($zip_file);
}

if ($zip->open($zip_file, ZipArchive::CREATE) !== TRUE) {
    exit("No se pudo crear el archivo ZIP.");
}

foreach ($horarios_por_grupo as $group_name => $horarios) {
    if (empty($horarios)) {
        logError("El grupo '$group_name' no tiene horarios.");
        continue;
    }

    try {
        $spreadsheet = IOFactory::load($template_path);
    } catch (Exception $e) {
        logError("Error al cargar la plantilla para el grupo '$group_name': " . $e->getMessage());
        continue;
    }

    $sheet = $spreadsheet->getActiveSheet();

    $diaColumna = [
        'Lunes' => 'B',
        'Martes' => 'C',
        'Miércoles' => 'D',
        'Jueves' => 'E',
        'Viernes' => 'F',
        'Sábado' => 'G'
    ];

    $filaInicial = [
        '07:00' => 7,
        '08:00' => 10,
        '09:00' => 13,
        '10:00' => 16,
        '11:00' => 19,
        '12:00' => 22,
        '13:00' => 25,
        '14:00' => 28,
        '15:00' => 31,
        '16:00' => 34,
        '17:00' => 37,
        '18:00' => 40,
        '19:00' => 43
    ];

    foreach ($horarios as $horario) {
        $dia = ucfirst(strtolower($horario['day']));
        $hora_raw = $horario['start_time'];
        $hora = date('H:i', strtotime($hora_raw));

        if (!isset($diaColumna[$dia])) {
            logError("Día no mapeado: '$dia' para grupo: '$group_name'");
            continue;
        }

        if (!isset($filaInicial[$hora])) {
            logError("Hora no mapeada: '$hora' para grupo: '$group_name'");
            continue;
        }

        $columna = $diaColumna[$dia];
        $fila = $filaInicial[$hora];

        $contenido = $horario['subject_name'] . "\n" . $horario['teacher_name'] . "\n";
        if (!empty($horario['room_name'])) {
            $contenido .= 'Aula: ' . $horario['room_name'] . ' (' . $horario['building_last_char'] . ')';
        } elseif (!empty($horario['lab_name'])) {
            $contenido .= 'Lab: ' . $horario['lab_name'];
        }

        $sheet->setCellValue($columna . $fila, $contenido);
        $sheet->getStyle($columna . $fila)->getAlignment()->setWrapText(true);
        $sheet->getStyle($columna . $fila)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
    }

    $safe_group_name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $group_name);

    $excel_filename = "Horario_" . $safe_group_name . ".xlsx";
    $excel_path = $temp_dir . '/' . $excel_filename;

    try {
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($excel_path);
    } catch (Exception $e) {
        logError("Error al guardar el archivo Excel para el grupo '$group_name': " . $e->getMessage());
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        continue;
    }

    if (!$zip->addFile($excel_path, $excel_filename)) {
        logError("Error al agregar el archivo Excel al ZIP para el grupo '$group_name'.");
    }

    $spreadsheet->disconnectWorksheets();
    unset($spreadsheet);
    unset($sheet);
    unset($writer);
}

$zip->close();

if (!file_exists($zip_file)) {
    logError("El archivo ZIP '$zip_file' no se pudo crear.");
    exit("Error al crear el archivo ZIP.");
}

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zip_file . '"');
header('Content-Length: ' . filesize($zip_file));
readfile($zip_file);

unlink($zip_file);

$files = glob($temp_dir . '/*.xlsx');
foreach ($files as $file) {
    unlink($file);
}
rmdir($temp_dir);

exit;
?>
