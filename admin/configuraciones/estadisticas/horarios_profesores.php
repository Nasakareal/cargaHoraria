<?php
// Incluir la configuración de la base de datos
require_once('../../../app/config.php'); // Ajusta la ruta según sea necesario
require '../../../vendor/autoload.php'; // PhpSpreadsheet

// Aumentar el límite de memoria y tiempo de ejecución según sea necesario
ini_set('memory_limit', '2G'); // Ajusta según tus necesidades
set_time_limit(0); // Eliminar el límite de tiempo de ejecución

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// Función para obtener los horarios de un profesor específico
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

// Función para obtener todos los profesores que tienen horarios asignados
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

// Función para registrar errores en un archivo de log
function logError($message) {
    file_put_contents(__DIR__ . '/error_log.txt', $message . "\n", FILE_APPEND);
}

// Crear una carpeta temporal para almacenar los archivos Excel
$temp_dir = sys_get_temp_dir() . '/horarios_temp_' . uniqid();
if (!mkdir($temp_dir, 0777, true)) {
    exit("No se pudo crear el directorio temporal para almacenar los archivos Excel.");
}

// Obtener todos los profesores con horarios asignados
$profesores = obtenerTodosLosProfesoresConHorarios($pdo);

// Verificar si hay profesores
if (empty($profesores)) {
    exit("No se encontraron profesores con horarios asignados.");
}

// Ruta de la plantilla
$template_path = __DIR__ . '/plantilla.xlsx';
if (!file_exists($template_path)) {
    exit("La plantilla 'plantilla.xlsx' no existe en el directorio " . __DIR__);
}

// Crear un archivo ZIP
$zip = new ZipArchive();
$zip_file = 'Horarios_Por_Profesor.zip';

// Eliminar el ZIP existente si existe para evitar conflictos
if (file_exists($zip_file)) {
    unlink($zip_file);
}

if ($zip->open($zip_file, ZipArchive::CREATE) !== TRUE) {
    exit("No se pudo crear el archivo ZIP.");
}

// Mapeo de días a columnas (asumiendo que la plantilla usa estos nombres)
$diaColumna = [
    'Lunes' => 'B',
    'Martes' => 'C',
    'Miércoles' => 'D',
    'Jueves' => 'E',
    'Viernes' => 'F',
    'Sábado' => 'G'
];

// Mapeo de filas para cada horario (ajusta según tu plantilla)
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

// Iterar sobre cada profesor
foreach ($profesores as $profesor) {
    $teacher_id = $profesor['teacher_id'];
    $teacher_name = $profesor['teacher_name'];

    // Obtener los horarios del profesor
    $horarios = obtenerHorariosPorProfesor($pdo, $teacher_id);

    // Si el profesor no tiene horarios, saltar
    if (empty($horarios)) {
        logError("El profesor '$teacher_name' (ID: $teacher_id) no tiene horarios asignados.");
        continue;
    }

    try {
        // Cargar la plantilla
        $spreadsheet = IOFactory::load($template_path);
    } catch (Exception $e) {
        logError("Error al cargar la plantilla para el profesor '$teacher_name' (ID: $teacher_id): " . $e->getMessage());
        continue;
    }

    $sheet = $spreadsheet->getActiveSheet();

    foreach ($horarios as $horario) {
        // Normalizar día y hora
        $dia = ucfirst(strtolower($horario['day']));
        $hora_raw = $horario['start_time'];
        $hora = date('H:i', strtotime($hora_raw));

        if (!isset($diaColumna[$dia])) {
            // Día no mapeado, registrar y saltar
            logError("Día no mapeado: '$dia' para el profesor '$teacher_name' (ID: $teacher_id)");
            continue;
        }

        if (!isset($filaInicial[$hora])) {
            // Hora no mapeada, registrar y saltar
            logError("Hora no mapeada: '$hora' para el profesor '$teacher_name' (ID: $teacher_id)");
            continue;
        }

        $columna = $diaColumna[$dia];
        $fila = $filaInicial[$hora];

        // Crear el contenido a mostrar
        $contenido = $horario['subject_name'] . "\n" . $horario['group_name'] . "\n";
        if (!empty($horario['room_name'])) {
            $contenido .= 'Aula: ' . $horario['room_name'] . ' (' . $horario['building_last_char'] . ')';
        } elseif (!empty($horario['lab_name'])) {
            $contenido .= 'Lab: ' . $horario['lab_name'];
        }

        // Escribir el contenido en la celda correspondiente
        $sheet->setCellValue($columna . $fila, $contenido);
        // Configurar la alineación de la celda para mostrar el contenido con saltos de línea
        $sheet->getStyle($columna . $fila)->getAlignment()->setWrapText(true);
        $sheet->getStyle($columna . $fila)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
    }

    // Sanitizar el nombre del profesor para evitar caracteres inválidos en el nombre del archivo
    $safe_teacher_name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $teacher_name);

    // Ruta del archivo Excel temporal
    $excel_filename = "Horario_" . $safe_teacher_name . ".xlsx";
    $excel_path = $temp_dir . '/' . $excel_filename;

    try {
        // Guardar el archivo Excel temporalmente
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($excel_path);
    } catch (Exception $e) {
        logError("Error al guardar el archivo Excel para el profesor '$teacher_name' (ID: $teacher_id): " . $e->getMessage());
        // Limpiar objetos
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        continue;
    }

    // Agregar el archivo Excel al ZIP
    if (!$zip->addFile($excel_path, $excel_filename)) {
        logError("Error al agregar el archivo Excel al ZIP para el profesor '$teacher_name' (ID: $teacher_id).");
    }

    // Limpiar el objeto spreadsheet para liberar memoria
    $spreadsheet->disconnectWorksheets();
    unset($spreadsheet);
    unset($sheet);
    unset($writer);
}

// Cerrar el ZIP
$zip->close();

// Verificar si el ZIP se creó correctamente
if (!file_exists($zip_file)) {
    logError("El archivo ZIP '$zip_file' no se pudo crear.");
    exit("Error al crear el archivo ZIP.");
}

// Enviar el archivo ZIP al navegador para descargar
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zip_file . '"');
header('Content-Length: ' . filesize($zip_file));
readfile($zip_file);

// Eliminar el archivo ZIP y la carpeta temporal después de la descarga
unlink($zip_file);

// Eliminar todos los archivos Excel temporales y la carpeta
$files = glob($temp_dir . '/*.xlsx'); // Obtener todos los archivos .xlsx en la carpeta temporal
foreach ($files as $file) {
    unlink($file); // Eliminar cada archivo
}
rmdir($temp_dir); // Eliminar la carpeta temporal

exit;
?>
