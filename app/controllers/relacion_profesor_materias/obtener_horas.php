<?php
include('../../../app/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = filter_input(INPUT_POST, 'teacher_id', FILTER_VALIDATE_INT);

    if (!$teacher_id) {
        echo "Error: ID de profesor inválido.";
        exit;
    }

    try {
        $sql = "SELECT hours FROM teachers WHERE teacher_id = :teacher_id";
        $query = $pdo->prepare($sql);
        $query->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);

        if ($result && isset($result['hours'])) {
            echo $result['hours']; // Enviar las horas encontradas
        } else {
            echo "Error: No se encontraron horas para este profesor."; // Depuración
        }
    } catch (Exception $e) {
        echo "Error al consultar la base de datos: " . $e->getMessage(); // Mensaje de error
    }
}
