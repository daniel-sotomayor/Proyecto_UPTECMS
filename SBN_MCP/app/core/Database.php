<?php declare(strict_types=1);
/**
 * =============================================================================
 * CLASE: DATABASE (CAPA DE ACCESO A DATOS)
 * =============================================================================
 * 
 * Gestiona la conexión y consultas a la base de datos MySQL usando PDO.
 * Implementa el patrón Singleton para mantener una única conexión.
 * 
 * Características:
 * - Conexión persistente con UTF-8 (utf8mb4)
 * - Prepared statements para todas las consultas (protección SQL Injection)
 * - Manejo de errores con excepciones
 * - Métodos estáticos para facilitar uso
 * 
 * Métodos principales:
 * - fetch(): Una sola fila
 * - fetchAll(): Múltiples filas
 * - execute(): INSERT/UPDATE/DELETE
 * - lastInsertId(): ID generado
 * 
 * @package App\Core
 * @author  MCP Development Team
 * @version 1.0.0
 * =============================================================================
 */

namespace App\Core;

use PDO;
use PDOStatement;
use PDOException;
use Exception;

class Database
{
    private static ?PDO $connection = null;

    public static function connect(array $config): PDO
    {
        if (self::$connection !== null) {
            return self::$connection;
        }

        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $config['host'],
                $config['port'] ?? 3306,
                $config['database']
            );

            self::$connection = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ]);

            return self::$connection;
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Error de conexión a la base de datos. Verifique la configuración.");
        }
    }

    public static function getInstance(): PDO
    {
        if (self::$connection === null) {
            $config = require CONFIG_PATH . '/database.php';
            self::connect($config);
        }
        return self::$connection;
    }

    public static function query(string $sql, array $params = []): PDOStatement
    {
        try {
            $stmt = self::getInstance()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query error: " . $e->getMessage() . " SQL: " . $sql);
            throw new Exception("Error en la consulta a la base de datos");
        }
    }

    public static function fetch(string $sql, array $params = []): ?array
    {
        $result = self::query($sql, $params)->fetch();
        return $result ?: null;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    public static function fetchValue(string $sql, array $params = [])
    {
        $result = self::query($sql, $params)->fetchColumn();
        return $result !== false ? $result : null;
    }

    public static function lastInsertId(): int
    {
        return (int) self::getInstance()->lastInsertId();
    }

    public static function beginTransaction(): void { self::getInstance()->beginTransaction(); }
    public static function commit(): void           { self::getInstance()->commit(); }
    public static function rollBack(): void         { self::getInstance()->rollBack(); }
}
