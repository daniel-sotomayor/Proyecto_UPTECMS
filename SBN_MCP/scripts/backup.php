#!/usr/bin/env php
<?php declare(strict_types=1);
/**
 * Script de Backup Automático - Cron Job
 *
 * Uso:
 *   php scripts/backup.php [daily|weekly]
 *
 * Configuración en crontab:
 *   0 2 * * * /usr/bin/php /path/to/sbn_mcp/scripts/backup.php daily
 */

// Verificar que se ejecuta desde CLI
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo 'Acceso denegado';
    return;
}

// Ruta fija al autoload — sin interpolación de usuario
$autoload = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoload)) {
    fwrite(STDERR, "Error: vendor/autoload.php no encontrado\n");
    exit(1);
}
require $autoload;

use App\Helpers\BackupHelper;
use App\Core\Logger;

// Cargar variables de entorno
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);
        if (!empty($key) && getenv($key) === false) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }
}

// Validar argumento — solo valores permitidos
$allowedTypes = ['daily', 'weekly'];
$type = isset($argv[1]) && in_array($argv[1], $allowedTypes, true) ? $argv[1] : 'daily';

echo '[' . date('Y-m-d H:i:s') . "] Iniciando backup {$type}...\n";

try {
    $result = BackupHelper::createBackup();

    if ($result['success']) {
        echo '[' . date('Y-m-d H:i:s') . "] Backup exitoso: {$result['filename']} ({$result['size']})\n";
        Logger::info('Backup completado', ['type' => $type, 'file' => $result['filename']]);
        exit(0);
    } else {
        fwrite(STDERR, '[' . date('Y-m-d H:i:s') . "] Error en backup: {$result['error']}\n");
        Logger::error('Backup fallido', ['type' => $type, 'error' => $result['error']]);
        exit(1);
    }
} catch (\Throwable $e) {
    fwrite(STDERR, '[' . date('Y-m-d H:i:s') . '] Excepción: ' . $e->getMessage() . "\n");
    Logger::critical('Excepción en backup', ['error' => $e->getMessage()]);
    exit(1);
}
