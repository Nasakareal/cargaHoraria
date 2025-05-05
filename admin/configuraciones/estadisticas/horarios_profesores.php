<?php
require_once('../../../app/config.php');
require '../../../vendor/autoload.php';

ini_set('memory_limit', '2G');
set_time_limit(0);

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

function obtenerHorariosPorProfesor($pdo, $teacher_id)
{
    $sql = "SELECT 
                t.teacher_id,
                t.teacher_name,
                sa.schedule_day AS day, 
                sa.start_time AS start_time, 
                sa.end_time AS end_time, 
                sa.tipo_espacio,
                s.subject_name, 
                sh.shift_name,
                r.classroom_name AS room_name,
                l.lab_name AS lab_name,
                RIGHT(r.building, 1) AS building_last_char,
                g.group_name
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
            WHERE 
                t.teacher_id = :teacher_id
            ORDER BY 
                sa.schedule_day, sa.start_time";

    $query = $pdo->prepare($sql);
    $query->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
    $query->execute();
    $horarios = $query->fetchAll(PDO::FETCH_ASSOC);

    return $horarios;
}

function obtenerTodosLosProfesoresConHorarios($pdo)
{
    $sql = "SELECT DISTINCT 
                t.teacher_id,
                t.teacher_name
            FROM 
                schedule_assignments sa
            JOIN 
                teachers t ON sa.teacher_id = t.teacher_id
            ORDER BY 
                t.teacher_name";

    $query = $pdo->prepare($sql);
    $query->execute();
    $profesores = $query->fetchAll(PDO::FETCH_ASSOC);

    return $profesores;
}

function logError($message) {
    file_put_contents(__DIR__ . '/error_log.txt', $message . "\n", FILE_APPEND);
}

$temp_dir = sys_get_temp_dir() . '/horarios_temp_' . uniqid();
if (!mkdir($temp_dir, 0777, true)) {
    exit("No se pudo crear el directorio temporal para almacenar los archivos Excel.");
}

$profesores = obtenerTodosLosProfesoresConHorarios($pdo);

if (empty($profesores)) {
    exit("No se encontraron profesores con horarios asignados.");
}

$template_path = __DIR__ . '/plantilla.xlsx';
if (!file_exists($template_path)) {
    exit("La plantilla 'plantilla.xlsx' no existe en el directorio " . __DIR__);
}

$zip = new ZipArchive();
$zip_file = __DIR__ . '/Horarios_Por_Profesor.zip';

if (file_exists($zip_file)) {
    unlink($zip_file);
}

if ($zip->open($zip_file, ZipArchive::CREATE) !== TRUE) {
    exit("No se pudo crear el archivo ZIP.");
}

$diaColumna = [
    'Lunes' => 'B',
    'Martes' => 'C',
    'Miércoles' => 'D',
    'Jueves' => 'E',
    'Viernes' => 'F',
    'Sábado' => 'G'
];

$filaInicial = [
    '07:00' => 6,
    '08:00' => 7,
    '09:00' => 8,
    '10:00' => 9,
    '11:00' => 10,
    '12:00' => 11,
    '13:00' => 13,
    '14:00' => 14,
    '15:00' => 15,
    '16:00' => 16,
    '17:00' => 17,
    '18:00' => 18,
    '19:00' => 19
];

foreach ($profesores as $profesor) {
    $teacher_id = $profesor['teacher_id'];
    $teacher_name = $profesor['teacher_name'];

    $horarios = obtenerHorariosPorProfesor($pdo, $teacher_id);

    if (empty($horarios)) {
        logError("El profesor '$teacher_name' (ID: $teacher_id) no tiene horarios asignados.");
        continue;
    }

    try {
        $spreadsheet = IOFactory::load($template_path);
    } catch (Exception $e) {
        logError("Error al cargar la plantilla para el profesor '$teacher_name' (ID: $teacher_id): " . $e->getMessage());
        continue;
    }

    $sheet = $spreadsheet->getActiveSheet();

    $totalHoras = 0;
    foreach ($horarios as $horario) {
        $start_time = new DateTime($horario['start_time']);
        $end_time = new DateTime($horario['end_time']);
        $interval = $start_time->diff($end_time);
        $horas = $interval->h + ($interval->i / 60);
        $totalHoras += $horas;
    }

    $sheet->setCellValue('C3', $teacher_name);
    $sheet->getStyle('C3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle('C3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

    $sheet->setCellValue('G4', $totalHoras);
    $sheet->getStyle('G4')->getNumberFormat()->setFormatCode('0.00');
    $sheet->getStyle('G4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle('G4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

    foreach ($horarios as $horario) {
        $dia = ucfirst(strtolower($horario['day']));
        $hora_raw = $horario['start_time'];
        $hora = date('H:i', strtotime($hora_raw));

        if (!isset($diaColumna[$dia])) {
            logError("Día no mapeado: '$dia' para el profesor '$teacher_name' (ID: $teacher_id)");
            continue;
        }

        if (!isset($filaInicial[$hora])) {
            logError("Hora no mapeada: '$hora' para el profesor '$teacher_name' (ID: $teacher_id)");
            continue;
        }

        $columna = $diaColumna[$dia];
        $fila = $filaInicial[$hora];

        $contenido = $horario['subject_name'] . "\n" . $horario['group_name'] . "\n";
        if (!empty($horario['room_name'])) {
            $contenido .= 'Aula: ' . $horario['room_name'] . ' (' . $horario['building_last_char'] . ')';
        } elseif (!empty($horario['lab_name'])) {
            $contenido .= 'Lab: ' . $horario['lab_name'];
        }

        $sheet->setCellValue($columna . $fila, $contenido);
        $sheet->getStyle($columna . $fila)->getAlignment()->setWrapText(true);
        $sheet->getStyle($columna . $fila)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
    }

    $safe_teacher_name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $teacher_name);

    $excel_filename = "Horario_" . $safe_teacher_name . ".xlsx";
    $excel_path = $temp_dir . '/' . $excel_filename;

    try {
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($excel_path);
    } catch (Exception $e) {
        logError("Error al guardar el archivo Excel para el profesor '$teacher_name' (ID: $teacher_id): " . $e->getMessage());
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        continue;
    }

    if (!$zip->addFile($excel_path, $excel_filename)) {
        logError("Error al agregar el archivo Excel al ZIP para el profesor '$teacher_name' (ID: $teacher_id).");
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
header('Content-Disposition: attachment; filename="' . basename($zip_file) . '"');
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
