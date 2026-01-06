<?php
/**
 * Archivo temporal para ejecutar migraciones en producción
 * IMPORTANTE: ELIMINAR ESTE ARCHIVO DESPUÉS DE USARLO
 */

// Cambiar al directorio de Laravel
chdir(__DIR__);

// Ejecutar migraciones
echo "Ejecutando migraciones...\n";
$output = shell_exec('php artisan migrate --force 2>&1');
echo $output;
echo "\n\nMigraciones completadas.\n";
echo "IMPORTANTE: ELIMINA ESTE ARCHIVO DESPUÉS DE USARLO POR SEGURIDAD.\n";
