<?php
// Asegúrate de iniciar la sesión si es necesario
session_start();

// Simulamos los datos que se descargarán, puedes adaptar esto según tus necesidades
if (isset($_GET['group_id'])) {
    $group_id = intval($_GET['group_id']);
    // Aquí deberías hacer una consulta a la base de datos para obtener los datos específicos del grupo
    $data = [
        "Group ID" => $group_id,
        "Group Name" => "Nombre del Grupo " . $group_id,
        "Period" => "2023-1",
        "Volume" => 30,
        "Shift" => "Mañana"
    ];

    // Formato de descarga
    $filename = "group_$group_id.csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    // Abrimos la salida para escribir en el archivo
    $output = fopen('php://output', 'w');

    // Escribimos los encabezados
    fputcsv($output, array_keys($data));
    // Escribimos los datos
    fputcsv($output, $data);

    fclose($output);
    exit;
} else {
    echo "No se ha especificado un ID de grupo.";
}
