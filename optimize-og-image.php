<?php
/**
 * Script para optimizar imágenes de Open Graph para Facebook
 *
 * IMPORTANTE:
 * - Este script requiere que PHP tenga la extensión GD habilitada
 * - Ejecuta: php optimize-og-image.php
 * - O accede vía navegador: https://tudominio.com/optimize-og-image.php
 *
 * ELIMINA ESTE ARCHIVO DESPUÉS DE USARLO POR SEGURIDAD
 */

// Configuración
$sourceImage = __DIR__ . '/public/images/default-survey-preview.jpg';
$outputImage = __DIR__ . '/public/images/default-survey-preview-1200x630.jpg';
$targetWidth = 1200;
$targetHeight = 630;

echo "🖼️ Optimizador de Imágenes Open Graph para Facebook\n";
echo "================================================\n\n";

// Verificar que la imagen original existe
if (!file_exists($sourceImage)) {
    die("❌ Error: No se encontró la imagen en: $sourceImage\n");
}

// Verificar que GD está instalado
if (!extension_loaded('gd')) {
    die("❌ Error: La extensión GD de PHP no está instalada.\n" .
        "   Instala con: apt-get install php-gd (Linux) o habilita en php.ini (Windows)\n");
}

// Obtener información de la imagen original
$imageInfo = getimagesize($sourceImage);
if ($imageInfo === false) {
    die("❌ Error: No se pudo leer la imagen.\n");
}

list($originalWidth, $originalHeight) = $imageInfo;
$mimeType = $imageInfo['mime'];

echo "📊 Imagen original:\n";
echo "   - Dimensiones: {$originalWidth}x{$originalHeight}\n";
echo "   - Tipo: $mimeType\n";
echo "   - Tamaño: " . round(filesize($sourceImage) / 1024) . " KB\n\n";

// Crear imagen desde el archivo original
$sourceImg = null;
switch ($mimeType) {
    case 'image/jpeg':
        $sourceImg = imagecreatefromjpeg($sourceImage);
        break;
    case 'image/png':
        $sourceImg = imagecreatefrompng($sourceImage);
        break;
    case 'image/gif':
        $sourceImg = imagecreatefromgif($sourceImage);
        break;
    default:
        die("❌ Error: Formato de imagen no soportado. Usa JPG, PNG o GIF.\n");
}

if (!$sourceImg) {
    die("❌ Error: No se pudo cargar la imagen.\n");
}

echo "🔄 Procesando imagen...\n";

// Crear nueva imagen con dimensiones correctas
$destImg = imagecreatetruecolor($targetWidth, $targetHeight);

// Preservar transparencia para PNG
if ($mimeType === 'image/png') {
    imagealphablending($destImg, false);
    imagesavealpha($destImg, true);
    $transparent = imagecolorallocatealpha($destImg, 255, 255, 255, 127);
    imagefilledrectangle($destImg, 0, 0, $targetWidth, $targetHeight, $transparent);
}

// Calcular dimensiones para mantener proporción
$sourceAspect = $originalWidth / $originalHeight;
$targetAspect = $targetWidth / $targetHeight;

if ($sourceAspect > $targetAspect) {
    // Imagen original es más ancha - ajustar por altura
    $newHeight = $targetHeight;
    $newWidth = intval($targetHeight * $sourceAspect);
    $offsetX = intval(($newWidth - $targetWidth) / 2);
    $offsetY = 0;
} else {
    // Imagen original es más alta - ajustar por ancho
    $newWidth = $targetWidth;
    $newHeight = intval($targetWidth / $sourceAspect);
    $offsetX = 0;
    $offsetY = intval(($newHeight - $targetHeight) / 2);
}

// Crear imagen temporal redimensionada
$tempImg = imagecreatetruecolor($newWidth, $newHeight);

// Preservar transparencia para la imagen temporal
if ($mimeType === 'image/png') {
    imagealphablending($tempImg, false);
    imagesavealpha($tempImg, true);
    $transparent = imagecolorallocatealpha($tempImg, 255, 255, 255, 127);
    imagefilledrectangle($tempImg, 0, 0, $newWidth, $newHeight, $transparent);
}

// Redimensionar con alta calidad
imagecopyresampled(
    $tempImg, $sourceImg,
    0, 0, 0, 0,
    $newWidth, $newHeight,
    $originalWidth, $originalHeight
);

// Recortar al tamaño exacto (centrado)
imagecopy(
    $destImg, $tempImg,
    0, 0,
    $offsetX, $offsetY,
    $targetWidth, $targetHeight
);

// Guardar imagen optimizada
$success = imagejpeg($destImg, $outputImage, 90); // Calidad 90%

// Liberar memoria
imagedestroy($sourceImg);
imagedestroy($tempImg);
imagedestroy($destImg);

if ($success) {
    echo "✅ Imagen optimizada creada exitosamente!\n\n";
    echo "📊 Imagen optimizada:\n";
    echo "   - Dimensiones: {$targetWidth}x{$targetHeight}\n";
    echo "   - Archivo: $outputImage\n";
    echo "   - Tamaño: " . round(filesize($outputImage) / 1024) . " KB\n\n";

    echo "🎯 Próximos pasos:\n";
    echo "   1. Reemplaza la imagen original con la optimizada:\n";
    echo "      mv $outputImage $sourceImage\n\n";
    echo "   2. Sube al servidor de producción\n\n";
    echo "   3. Limpia la caché de Facebook:\n";
    echo "      https://developers.facebook.com/tools/debug/\n\n";
    echo "   4. ELIMINA este archivo (optimize-og-image.php) del servidor\n\n";
} else {
    echo "❌ Error al guardar la imagen optimizada.\n";
}
?>
