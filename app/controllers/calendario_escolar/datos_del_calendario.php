<?php

/* Verificar si `$id_calendario` está definido */
if (!isset($id_calendario) || !$id_calendario) {
    echo "<script>
        alert('Calendario no especificado.');
        window.location.href = 'index.php';
    </script>";
    exit;
}



try {
    /* Consultar los detalles del calendario */
    $query = $pdo->prepare("SELECT * FROM calendario_escolar WHERE id = :id");
    $query->bindParam(':id', $id_calendario, PDO::PARAM_INT);
    $query->execute();
    $calendario = $query->fetch(PDO::FETCH_ASSOC);

    /* Verificar si el calendario existe */
    if (!$calendario) {
        echo "<script>
            alert('Calendario no encontrado.');
            window.location.href = 'index.php';
        </script>";
        exit;
    }
} catch (Exception $e) {
    echo "<script>
        alert('Ocurrió un error al consultar el calendario: " . $e->getMessage() . "');
        window.location.href = 'index.php';
    </script>";
    exit;
}
