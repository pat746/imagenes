<?php
$host = "localhost";
$dbname = "evidencia";
$username = "root"; // Cambia si es necesario
$password = ""; // Si tienes contraseña, agrégala

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Error en la conexión: " . $e->getMessage());
}
?>
