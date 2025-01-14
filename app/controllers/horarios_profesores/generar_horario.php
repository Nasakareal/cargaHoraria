<?php
require '../../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/* Ruta de la plantilla */
$templatePath = __DIR__ . '/../../../templates/plantilla_horario.xlsx';

/* Verificar si la plantilla existe */
if (!file_exists($templatePath)) {
    die('Error: La plantilla no existe.');
}

/* Verificar que se enviaron los datos */
if (!isset($_POST['horarios']) || empty($_POST['horarios'])) {
    die('Error: No se enviaron datos para generar el archivo.');
}

/* Verificar que se envió el teacher_id */
if (!isset($_POST['teacher_id']) || !filter_var($_POST['teacher_id'], FILTER_VALIDATE_INT)) {
    die('Error: ID de profesor inválido.');
}

/* Obtener los datos enviados desde el cliente */
$horarios = $_POST['horarios'];
$teacher_id = intval($_POST['teacher_id']);

/* Conectar a la base de datos */
include('../../app/config.php'); // Asegúrate de que esta ruta es correcta y contiene la conexión $pdo

/* Obtener teacher_name y hours desde la tabla teachers */
$sql_teacher = "SELECT teacher_name, hours FROM teachers WHERE teacher_id = :teacher_id LIMIT 1";
$stmt_teacher = $pdo->prepare($sql_teacher);
$stmt_teacher->execute([':teacher_id' => $teacher_id]);
$teacher = $stmt_teacher->fetch(PDO::FETCH_ASSOC);

if (!$teacher) {
    die('Error: Profesor no encontrado.');
}

$teacher_name = $teacher['teacher_name'];
$hours = $teacher['hours'];

/* Cargar la plantilla */
$spreadsheet = IOFactory::load($templatePath);
$sheet = $spreadsheet->getActiveSheet();

/* Insertar el nombre del docente en la celda D3 */
$sheet->setCellValue('D3', $teacher_name);
$sheet->getStyle('D3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
$sheet->getStyle('D3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

/* Insertar el total de horas en la celda G4 */
$sheet->setCellValue('G4', $hours);
$sheet->getStyle('G4')->getNumberFormat()->setFormatCode('0'); // Formato de número entero
$sheet->getStyle('G4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
$sheet->getStyle('G4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

/* Mapear los datos a la plantilla */
foreach ($horarios as $fila) {
    $hora = $fila[0];
    $dias = array_slice($fila, 1);

    /* Determinar la fila correspondiente en la plantilla */
    $horaFila = [
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

    if (!isset($horaFila[$hora])) {
        continue;
    }

    $filaPlantilla = $horaFila[$hora];
    $columnaInicial = 'B';

    /* Rellenar los días en la plantilla */
    foreach ($dias as $index => $contenido) {
        $columna = chr(ord($columnaInicial) + $index);
        $sheet->setCellValue($columna . $filaPlantilla, strip_tags($contenido));
        // Opcional: Aplicar estilos si es necesario
        $sheet->getStyle($columna . $filaPlantilla)->getAlignment()->setWrapText(true);
        $sheet->getStyle($columna . $filaPlantilla)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
    }
}

/* Configurar encabezados para descargar el archivo como Excel */
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Horario_Personalizado.xlsx"');

/* Generar y enviar el archivo Excel */
$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save('php://output');
exit;
?>
