<?php declare(strict_types=1);
/**
 * Clase de Caché Segura basada en archivos para datos estáticos.
 *
 * Almacena resultados de consultas frecuentes (estados, tipos, áreas)
 * en archivos JSON con TTL configurable y validación de seguridad.
 *
 * @package App\Core
 */

namespace App\Core;

class Cache
{
    private static string $cacheDir = '';
    private static array $allowedKeys = [];

    /**
     * Inicializar cache con claves permitidas
     */
    public static function init(array $allowedKeys = []): void
    {
        self::$allowedKeys = array_merge([
            'estados', 'tipos_bien', 'areas', 'roles', 'configuracion'
        ], $allowedKeys);
    }

    /**
     * Obtener directorio de cache seguro
     */
    private static function dir(): string
    {
        if (self::$cacheDir === '') {
            self::$cacheDir = defined('ROOT_PATH')
                ? ROOT_PATH . '/cache'
                : dirname(__DIR__, 2) . '/cache';
        }
        
        if (!is_dir(self::$cacheDir)) {
            if (!mkdir(self::$cacheDir, 0755, true)) {
                throw new \Exception('No se pudo crear el directorio de cache');
            }
            
            // Crear .htaccess de seguridad
            $htaccess = self::$cacheDir . '/.htaccess';
            file_put_contents($htaccess, "Deny from all\n");
        }
        
        return self::$cacheDir;
    }

    /**
     * Validar clave de cache
     */
    private static function validateKey(string $key): bool
    {
        // Solo permitir claves alfanuméricas y guiones bajos
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $key)) {
            return false;
        }
        
        // Verificar lista de claves permitidas si está configurada
        if (!empty(self::$allowedKeys) && !in_array($key, self::$allowedKeys, true)) {
            return false;
        }
        
        return true;
    }

    /**
     * Generar nombre de archivo seguro
     */
    private static function getFilePath(string $key): string
    {
        if (!self::validateKey($key)) {
            throw new \InvalidArgumentException('Clave de cache inválida: ' . $key);
        }
        
        // Usar hash seguro para el nombre del archivo
        $hash = hash('sha256', $key);
        return self::dir() . '/' . $hash . '.cache';
    }

    /**
     * Obtener datos del cache
     */
    public static function get(string $key, int $ttl = 3600): mixed
    {
        try {
            $file = self::getFilePath($key);
            
            if (!file_exists($file)) {
                return null;
            }
            
            // Verificar TTL
            if ((time() - filemtime($file)) >= $ttl) {
                unlink($file);
                return null;
            }
            
            $content = file_get_contents($file);
            if ($content === false) {
                return null;
            }
            
            // Usar JSON en lugar de serialize por seguridad
            $data = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                unlink($file); // Eliminar archivo corrupto
                return null;
            }
            
            return $data;
        } catch (\Exception $e) {
            Logger::error('Error reading cache', ['key' => $key, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Guardar datos en cache
     */
    public static function set(string $key, mixed $data): bool
    {
        try {
            $file = self::getFilePath($key);
            
            // Convertir a JSON
            $json = json_encode($data, JSON_UNESCAPED_UNICODE);
            if ($json === false) {
                throw new \Exception('No se pudo serializar los datos');
            }
            
            // Escribir de forma atómica
            $tempFile = $file . '.tmp';
            if (file_put_contents($tempFile, $json, LOCK_EX) === false) {
                throw new \Exception('No se pudo escribir el archivo temporal');
            }
            
            if (!rename($tempFile, $file)) {
                unlink($tempFile);
                throw new \Exception('No se pudo mover el archivo temporal');
            }
            
            return true;
        } catch (\Exception $e) {
            Logger::error('Error writing cache', ['key' => $key, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Eliminar entrada del cache
     */
    public static function forget(string $key): bool
    {
        try {
            $file = self::getFilePath($key);
            if (file_exists($file)) {
                return unlink($file);
            }
            return true;
        } catch (\Exception $e) {
            Logger::error('Error deleting cache', ['key' => $key, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Limpiar todo el cache
     */
    public static function flush(): bool
    {
        try {
            $dir = self::dir();
            $files = glob($dir . '/*.cache');
            
            if (!$files) {
                return true;
            }
            
            $success = true;
            foreach ($files as $file) {
                if (!unlink($file)) {
                    $success = false;
                }
            }
            
            return $success;
        } catch (\Exception $e) {
            Logger::error('Error flushing cache', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Recordar datos con callback
     */
    public static function remember(string $key, callable $callback, int $ttl = 3600): mixed
    {
        $cached = self::get($key, $ttl);
        if ($cached !== null) {
            return $cached;
        }

        $data = $callback();
        self::set($key, $data);
        return $data;
    }

    /**
     * Obtener estadísticas del cache
     */
    public static function stats(): array
    {
        try {
            $dir = self::dir();
            $files = glob($dir . '/*.cache');
            
            $stats = [
                'total_files' => count($files ?: []),
                'total_size' => 0,
                'oldest_file' => null,
                'newest_file' => null,
            ];
            
            if ($files) {
                $times = [];
                foreach ($files as $file) {
                    $stats['total_size'] += filesize($file);
                    $times[] = filemtime($file);
                }
                $stats['oldest_file'] = date('Y-m-d H:i:s', min($times));
                $stats['newest_file'] = date('Y-m-d H:i:s', max($times));
            }
            
            return $stats;
        } catch (\Exception $e) {
            Logger::error('Error getting cache stats', ['error' => $e->getMessage()]);
            return ['error' => 'No se pudieron obtener las estadísticas'];
        }
    }
}
