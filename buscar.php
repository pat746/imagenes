<?php
require 'config/database.php';

$nombre = isset($_GET['nombre']) ? $_GET['nombre'] : '';
$fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '';
$fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '';

$query = "SELECT * FROM imagenes WHERE 1=1";
$params = [];

// Filtrar por nombre si se ingresa
if (!empty($nombre)) {
    $query .= " AND nombre LIKE :nombre";
    $params['nombre'] = "%$nombre%";
}

// Filtrar por fecha si se ingresa
if (!empty($fecha_desde)) {
    $query .= " AND fecha_subida >= :fecha_desde";
    $params['fecha_desde'] = $fecha_desde . " 00:00:00";
}

if (!empty($fecha_hasta)) {
    $query .= " AND fecha_subida <= :fecha_hasta";
    $params['fecha_hasta'] = $fecha_hasta . " 23:59:59";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($resultados) {
    echo "<h2>Resultados de la búsqueda:</h2>";
    foreach ($resultados as $fila) {
        echo "<p><a href='{$fila['url']}' target='_blank'>{$fila['nombre']}</a></p>";
        echo "<img src='{$fila['url']}' width='200'><br>";
        echo "<p>Fecha subida: {$fila['fecha_subida']}</p><hr>";
    }
} else {
    echo "No se encontraron imágenes.";
}
?>
