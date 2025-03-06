<?php
require_once 'vendor/autoload.php';
require_once './config/database.php'; // Conexión a la base de datos

use Google\Cloud\Storage\StorageClient;

putenv('GOOGLE_APPLICATION_CREDENTIALS=config/credencial.json');

// Verificar conexión a la base de datos
if (!isset($pdo)) {
    die("❌ Error: No se pudo conectar a la base de datos.");
}

// Leer datos JSON
$input = json_decode(file_get_contents("php://input"), true);

if (isset($input["imagen"]) && isset($input["nombre_imagen"])) {
    $imagenBase64 = $input["imagen"];
    $nombreImagen = preg_replace("/[^a-zA-Z0-9_-]/", "_", $input["nombre_imagen"]); // Sanitizar nombre

    // Validar que es Base64 y extraer la extensión
    if (preg_match('/^data:image\/(\w+);base64,/', $imagenBase64, $tipo)) {
        $tipo = strtolower($tipo[1]); // Obtener extensión
        if (!in_array($tipo, ['jpg', 'jpeg', 'png'])) {
            die("❌ Error: Solo se permiten archivos JPG, JPEG o PNG.");
        }

        // Agregar la extensión al nombre del archivo
        $nombreArchivo = $nombreImagen . "." . $tipo;

        // Decodificar Base64
        $imagenBase64 = substr($imagenBase64, strpos($imagenBase64, ',') + 1);
        $imagenBinaria = base64_decode($imagenBase64);

        if ($imagenBinaria === false) {
            die("❌ Error: No se pudo procesar la imagen.");
        }

        // Crear archivo temporal
        $rutaTemporal = sys_get_temp_dir() . "/" . $nombreArchivo;
        file_put_contents($rutaTemporal, $imagenBinaria);
    } else {
        die("❌ Error: Formato de imagen inválido.");
    }

    try {
        // Subir imagen a Google Cloud Storage
        $bucketName = "ejemplo_bucket_imagenes1";
        $storage = new StorageClient();
        $bucket = $storage->bucket($bucketName);
        $objeto = $bucket->upload(fopen($rutaTemporal, 'r'), [
            'name' => "imagenes/" . $nombreArchivo
        ]);

        // Hacer público el archivo
        $objeto->update([], ['predefinedAcl' => 'PUBLICREAD']);

        // URL pública de la imagen
        $url = "https://storage.googleapis.com/$bucketName/imagenes/$nombreArchivo";

        // Guardar en la base de datos
        $stmt = $pdo->prepare("INSERT INTO imagenes (nombre, url, fecha_subida) VALUES (:nombre, :url, NOW())");
        $stmt->execute(['nombre' => $nombreArchivo, 'url' => $url]);

        echo "✅ Imagen subida con éxito";

        // Eliminar el archivo temporal
        unlink($rutaTemporal);
    } catch (Exception $e) {
        echo "❌ Error al subir la imagen: " . $e->getMessage();
    }
} else {
    echo "❌ Error: Se requiere una imagen y un nombre.";
}
