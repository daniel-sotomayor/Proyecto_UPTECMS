<?php declare(strict_types=1);
/**
 * Sistema de Logging Mejorado
 * Sistema de Gestión de Bienes Nacionales - MCP
 */

namespace App\Core;

class Logger
{
    private static string $logDir = '';
    private static array $config = [];

    /**
     * Inicializar el logger con configuración
     */
    public static function init(array $config = []): void
    {
        self::$config = array_merge([
            'max_file_size' => 10 * 1024 * 1024, // 10MB
            'max_files' => 30, // 30 días
            'log_level' => 'INFO',
        ], $config);
    }

    /**
     * Obtener directorio de logs — ruta fija, sin interpolación de usuario
     */
    private static function dir(): string
    {
        if (!self::$logDir) {
            // Ruta calculada en tiempo de compilación, no desde input externo
            self::$logDir = defined('APP_PATH')
                ? dirname(APP_PATH) . '/logs'
                : dirname(__DIR__, 2) . '/logs';
        }
        if (!is_dir(self::$logDir)) {
            if (!mkdir(self::$logDir, 0755, true)) {
                throw new \RuntimeException('No se pudo crear el directorio de logs');
            }
        }
        return self::$logDir;
    }

    /**
     * Escribir entrada de log
     */
    private static function write(string $level, string $message, array $context = []): void
    {
        try {
            // Verificar nivel de log
            if (!self::shouldLog($level)) {
                return;
            }

            $entry = [
                'timestamp' => date('Y-m-d\TH:i:s.v\Z'),
                'level'     => $level,
                'message'   => $message,
                'context'   => $context,
                'ip'        => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent'=> $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'request_id'=> self::getRequestId(),
            ];

            // Añadir información de usuario si está disponible
            if (class_exists('\\App\\Core\\Session') && Session::has('user_id')) {
                $entry['user_id'] = Session::get('user_id');
                $entry['username'] = Session::get('username');
            }

            $logLine = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
            
            $file = self::getLogFile($level);
            
            // Rotar archivo si es necesario
            self::rotateIfNeeded($file);
            
            if (file_put_contents($file, $logLine, FILE_APPEND | LOCK_EX) === false) {
                error_log("Failed to write to log file: {$file}");
            }
        } catch (\Throwable $e) {
            error_log("Logger error: " . $e->getMessage());
        }
    }

    /**
     * Verificar si se debe registrar el nivel de log
     */
    private static function shouldLog(string $level): bool
    {
        $levels = ['DEBUG' => 0, 'INFO' => 1, 'WARNING' => 2, 'ERROR' => 3, 'CRITICAL' => 4];
        $currentLevel = self::$config['log_level'] ?? 'INFO';
        
        return ($levels[$level] ?? 1) >= ($levels[$currentLevel] ?? 1);
    }

    /**
     * Obtener archivo de log apropiado
     */
    private static function getLogFile(string $level): string
    {
        $date = date('Y-m-d');
        $levelLower = strtolower($level);
        
        // Logs de error en archivo separado
        if (in_array($level, ['ERROR', 'CRITICAL'])) {
            return self::dir() . "/error-{$date}.log";
        }
        
        return self::dir() . "/app-{$date}.log";
    }

    /**
     * Rotar archivo si excede el tamaño máximo
     */
    private static function rotateIfNeeded(string $file): void
    {
        if (!file_exists($file)) {
            return;
        }
        
        $maxSize = self::$config['max_file_size'] ?? (10 * 1024 * 1024);
        
        if (filesize($file) > $maxSize) {
            $rotated = $file . '.' . time();
            rename($file, $rotated);
            
            // Comprimir archivo rotado
            if (function_exists('gzencode')) {
                $content = file_get_contents($rotated);
                file_put_contents($rotated . '.gz', gzencode($content));
                unlink($rotated);
            }
        }
        
        // Limpiar archivos antiguos
        self::cleanOldLogs();
    }

    /**
     * Limpiar logs antiguos
     */
    private static function cleanOldLogs(): void
    {
        $maxFiles = self::$config['max_files'] ?? 30;
        $logDir = self::dir();
        
        $files = glob($logDir . '/*.log*');
        if (count($files) <= $maxFiles) {
            return;
        }
        
        // Ordenar por fecha de modificación
        usort($files, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });
        
        // Eliminar archivos más antiguos
        $toDelete = array_slice($files, 0, count($files) - $maxFiles);
        foreach ($toDelete as $file) {
            unlink($file);
        }
    }

    /**
     * Obtener ID único de request
     */
    private static function getRequestId(): string
    {
        static $requestId = null;
        if ($requestId === null) {
            $requestId = substr(md5(uniqid('', true)), 0, 8);
        }
        return $requestId;
    }

    // Métodos públicos de logging
    public static function debug(string $msg, array $ctx = []): void    { self::write('DEBUG',    $msg, $ctx); }
    public static function info(string $msg, array $ctx = []): void     { self::write('INFO',     $msg, $ctx); }
    public static function warning(string $msg, array $ctx = []): void  { self::write('WARNING',  $msg, $ctx); }
    public static function error(string $msg, array $ctx = []): void    { self::write('ERROR',    $msg, $ctx); }
    public static function critical(string $msg, array $ctx = []): void { self::write('CRITICAL', $msg, $ctx); }

    /**
     * Log de actividad de usuario
     */
    public static function activity(string $action, array $data = []): void
    {
        self::info("User activity: {$action}", $data);
    }

    /**
     * Log de seguridad
     */
    public static function security(string $event, array $data = []): void
    {
        self::warning("Security event: {$event}", $data);
    }

    /**
     * Log de performance
     */
    public static function performance(string $operation, float $duration, array $data = []): void
    {
        $data['duration_ms'] = round($duration * 1000, 2);
        self::info("Performance: {$operation}", $data);
    }
}
