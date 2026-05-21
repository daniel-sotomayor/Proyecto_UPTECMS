<?php declare(strict_types=1);
/**
 * Backup Helper Seguro - Sistema de respaldo automático de base de datos
 *
 * @package App\Helpers
 */

namespace App\Helpers;

use App\Core\Database;
use App\Core\Logger;

class BackupHelper
{
    private static string $backupPath;
    private static int $retentionDays;
    private static bool $enabled;
    private static array $allowedDatabases = [];

    /**
     * Inicializa configuración desde variables de entorno
     */
    private static function init(): void
    {
        self::$enabled = ($_ENV['BACKUP_ENABLED'] ?? 'false') === 'true';
        self::$retentionDays = (int)($_ENV['BACKUP_RETENTION_DAYS'] ?? 30);
        
        // Validar y sanitizar ruta de backup
        $backupPath = $_ENV['BACKUP_PATH'] ?? dirname(__DIR__, 2) . '/backups';
        self::$backupPath = self::sanitizePath($backupPath);
        
        // Lista de bases de datos permitidas
        self::$allowedDatabases = explode(',', $_ENV['ALLOWED_DATABASES'] ?? 'hospital_bienes');

        // Crear directorio si no existe
        if (!is_dir(self::$backupPath)) {
            if (!mkdir(self::$backupPath, 0755, true)) {
                throw new \Exception('No se pudo crear el directorio de backups');
            }
            
            // Crear .htaccess de seguridad
            $htaccess = self::$backupPath . '/.htaccess';
            file_put_contents($htaccess, "Deny from all\n");
        }
    }

    /**
     * Sanitizar ruta para prevenir path traversal
     */
    private static function sanitizePath(string $path): string
    {
        // Resolver ruta absoluta
        $realPath = realpath($path);
        if ($realPath === false) {
            // Si no existe, usar la ruta padre que sí existe
            $parentDir = dirname($path);
            $realParent = realpath($parentDir);
            if ($realParent === false) {
                throw new \Exception('Directorio de backup inválido');
            }
            return $realParent . '/' . basename($path);
        }
        
        // Verificar que está dentro del proyecto
        $projectRoot = realpath(dirname(__DIR__, 2));
        if (!str_starts_with($realPath, $projectRoot)) {
            throw new \Exception('Directorio de backup fuera del proyecto');
        }
        
        return $realPath;
    }

    /**
     * Validar nombre de base de datos
     */
    private static function validateDatabase(string $database): bool
    {
        // Solo caracteres alfanuméricos y guiones bajos
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $database)) {
            return false;
        }
        
        // Verificar lista de bases permitidas
        return in_array($database, self::$allowedDatabases, true);
    }

    /**
     * Crea un backup completo de la base de datos
     */
    public static function createBackup(): array
    {
        try {
            self::init();

            if (!self::$enabled) {
                return ['success' => false, 'error' => 'Backup deshabilitado en configuración'];
            }

            $dbConfig = require dirname(__DIR__, 2) . '/config/database.php';
            
            // Validar base de datos
            if (!self::validateDatabase($dbConfig['database'])) {
                throw new \Exception('Base de datos no permitida para backup');
            }
            
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "backup_{$dbConfig['database']}_{$timestamp}.sql";
            
            // Generar nombre de archivo seguro
            $safeFilename = preg_replace('/[^a-zA-Z0-9_.-]/', '', $filename);
            $filepath = self::$backupPath . '/' . $safeFilename;
            $compressedPath = $filepath . '.gz';

            // Verificar que el archivo está en el directorio correcto
            if (!self::isFileInDirectory($filepath, self::$backupPath)) {
                throw new \Exception('Ruta de archivo inválida');
            }

            // Crear backup usando PHP puro (más seguro)
            $result = self::backupWithPHP($dbConfig, $filepath);

            if (!$result) {
                throw new \Exception('Error al crear el backup');
            }

            // Comprimir
            if (!self::compressFile($filepath, $compressedPath)) {
                throw new \Exception('Error al comprimir el backup');
            }
            
            unlink($filepath); // Eliminar archivo original

            // Limpiar backups antiguos
            self::cleanupOldBackups();

            // Log del backup
            self::logBackup(basename($compressedPath), filesize($compressedPath));

            return [
                'success' => true,
                'filename' => basename($compressedPath),
                'path' => $compressedPath,
                'size' => self::formatBytes(filesize($compressedPath)),
                'timestamp' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            Logger::error('Error en backup', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Ejecuta backup usando PHP puro (más seguro que mysqldump)
     */
    private static function backupWithPHP(array $config, string $outputFile): bool
    {
        try {
            $tables = Database::fetchAll("SHOW TABLES");
            $output = "-- Backup SBN MCP\n";
            $output .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n";
            $output .= "-- Base de datos: " . htmlspecialchars($config['database']) . "\n\n";
            $output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                $tableName = array_values($table)[0];
                
                // Validar nombre de tabla
                if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
                    continue; // Saltar tablas con nombres sospechosos
                }

                // Estructura
                $create = Database::fetch("SHOW CREATE TABLE `" . $tableName . "`");
                $output .= "-- Estructura tabla: " . $tableName . "\n";
                $output .= "DROP TABLE IF EXISTS `" . $tableName . "`;\n";
                $output .= $create['Create Table'] . ";\n\n";

                // Datos
                $rows = Database::fetchAll("SELECT * FROM `" . $tableName . "`");
                if (!empty($rows)) {
                    $output .= "-- Datos tabla: " . $tableName . "\n";
                    $columns = array_keys($rows[0]);
                    $columnList = implode('`, `', array_map('htmlspecialchars', $columns));

                    foreach ($rows as $row) {
                        $values = array_map(function ($value) {
                            if ($value === null) {
                                return 'NULL';
                            }
                            return "'" . str_replace(["'", "\\"], ["\\'", "\\\\"], $value) . "'";
                        }, array_values($row));

                        $output .= "INSERT INTO `" . $tableName . "` (`" . $columnList . "`) VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $output .= "\n";
                }
            }

            $output .= "SET FOREIGN_KEY_CHECKS=1;\n";

            return file_put_contents($outputFile, $output, LOCK_EX) !== false;
        } catch (\Exception $e) {
            Logger::error('Error en backup PHP', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Comprime un archivo usando gzip
     */
    private static function compressFile(string $source, string $dest): bool
    {
        if (!file_exists($source)) {
            return false;
        }
        
        $fp = fopen($source, 'rb');
        if (!$fp) return false;

        $zp = gzopen($dest, 'wb9');
        if (!$zp) {
            fclose($fp);
            return false;
        }

        while (!feof($fp)) {
            $chunk = fread($fp, 4096);
            if ($chunk === false) break;
            gzwrite($zp, $chunk);
        }

        gzclose($zp);
        fclose($fp);

        return file_exists($dest) && filesize($dest) > 0;
    }

    /**
     * Elimina backups más antiguos que el período de retención
     */
    private static function cleanupOldBackups(): void
    {
        try {
            $pattern = self::$backupPath . '/backup_*.sql.gz';
            $files = glob($pattern);
            
            if (!$files) return;
            
            $now = time();
            $maxAge = self::$retentionDays * 86400;

            foreach ($files as $file) {
                if (!self::isFileInDirectory($file, self::$backupPath)) {
                    continue; // Saltar archivos fuera del directorio
                }
                
                if (is_file($file)) {
                    $age = $now - filemtime($file);
                    if ($age > $maxAge) {
                        unlink($file);
                        Logger::info('Backup antiguo eliminado', ['file' => basename($file)]);
                    }
                }
            }
        } catch (\Exception $e) {
            Logger::error('Error limpiando backups antiguos', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Obtiene lista de backups disponibles
     */
    public static function listBackups(): array
    {
        try {
            self::init();

            $pattern = self::$backupPath . '/backup_*.sql.gz';
            $files = glob($pattern);
            $backups = [];

            if (!$files) return $backups;

            foreach ($files as $file) {
                if (!self::isFileInDirectory($file, self::$backupPath)) {
                    continue; // Saltar archivos fuera del directorio
                }
                
                if (is_file($file)) {
                    $backups[] = [
                        'filename' => basename($file),
                        'size' => self::formatBytes(filesize($file)),
                        'created' => date('Y-m-d H:i:s', filemtime($file)),
                        'path' => $file
                    ];
                }
            }

            // Ordenar por fecha descendente
            usort($backups, fn($a, $b) => strtotime($b['created']) - strtotime($a['created']));

            return $backups;
        } catch (\Exception $e) {
            Logger::error('Error listando backups', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Verificar que un archivo está dentro de un directorio permitido
     */
    private static function isFileInDirectory(string $filePath, string $allowedDirectory): bool
    {
        $realFilePath = realpath($filePath);
        $realAllowedDir = realpath($allowedDirectory);
        
        if ($realFilePath === false || $realAllowedDir === false) {
            return false;
        }
        
        return str_starts_with($realFilePath, $realAllowedDir . DIRECTORY_SEPARATOR);
    }

    /**
     * Registra backup en logs
     */
    private static function logBackup(string $filename, int $size): void
    {
        Logger::info('Backup creado', [
            'filename' => $filename,
            'size' => $size,
            'size_formatted' => self::formatBytes($size)
        ]);
    }

    /**
     * Formatea bytes a unidades legibles
     */
    private static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;

        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }

        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }
}
