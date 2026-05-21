<?php declare(strict_types=1);
/**
 * Framework de Pruebas Automatizadas
 * Sistema de Gestión de Bienes Nacionales - MCP
 */

namespace App\Tests;

use App\Core\Database;
use App\Core\Session;
use App\Core\Logger;

class TestRunner
{
    private array $tests = [];
    private array $results = [];
    private int $passed = 0;
    private int $failed = 0;
    private float $startTime;

    public function __construct()
    {
        $this->startTime = microtime(true);
    }

    /**
     * Registrar una prueba
     */
    public function test(string $name, callable $test): void
    {
        $this->tests[$name] = $test;
    }

    /**
     * Ejecutar todas las pruebas
     */
    public function run(): array
    {
        echo "🧪 Ejecutando pruebas del Sistema SBN_MCP...\n\n";

        foreach ($this->tests as $name => $test) {
            $this->runSingleTest($name, $test);
        }

        $this->printSummary();
        return $this->results;
    }

    /**
     * Ejecutar una prueba individual
     */
    private function runSingleTest(string $name, callable $test): void
    {
        $startTime = microtime(true);
        
        try {
            $test();
            $duration = microtime(true) - $startTime;
            $this->results[$name] = [
                'status' => 'PASSED',
                'duration' => $duration,
                'message' => null
            ];
            $this->passed++;
            echo "✅ {$name} (" . round($duration * 1000, 2) . "ms)\n";
        } catch (\Throwable $e) {
            $duration = microtime(true) - $startTime;
            $this->results[$name] = [
                'status' => 'FAILED',
                'duration' => $duration,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
            $this->failed++;
            echo "❌ {$name} - {$e->getMessage()}\n";
            echo "   📍 {$e->getFile()}:{$e->getLine()}\n";
        }
    }

    /**
     * Imprimir resumen de pruebas
     */
    private function printSummary(): void
    {
        $totalTime = microtime(true) - $this->startTime;
        $total = $this->passed + $this->failed;
        
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "📊 RESUMEN DE PRUEBAS\n";
        echo str_repeat("=", 50) . "\n";
        echo "Total: {$total}\n";
        echo "✅ Pasaron: {$this->passed}\n";
        echo "❌ Fallaron: {$this->failed}\n";
        echo "⏱️  Tiempo total: " . round($totalTime * 1000, 2) . "ms\n";
        
        if ($this->failed > 0) {
            echo "\n🚨 ALGUNAS PRUEBAS FALLARON\n";
            exit(1);
        } else {
            echo "\n🎉 TODAS LAS PRUEBAS PASARON\n";
        }
    }
}

/**
 * Clase de utilidades para pruebas
 */
class TestUtils
{
    /**
     * Afirmar que una condición es verdadera
     */
    public static function assertTrue(bool $condition, string $message = 'Assertion failed'): void
    {
        if (!$condition) {
            throw new \Exception($message);
        }
    }

    /**
     * Afirmar que dos valores son iguales
     */
    public static function assertEquals($expected, $actual, string $message = 'Values are not equal'): void
    {
        if ($expected !== $actual) {
            throw new \Exception("{$message}. Expected: " . json_encode($expected) . ", Actual: " . json_encode($actual));
        }
    }

    /**
     * Afirmar que un valor no es nulo
     */
    public static function assertNotNull($value, string $message = 'Value is null'): void
    {
        if ($value === null) {
            throw new \Exception($message);
        }
    }

    /**
     * Afirmar que un array contiene una clave
     */
    public static function assertArrayHasKey(string $key, array $array, string $message = 'Array does not have key'): void
    {
        if (!array_key_exists($key, $array)) {
            throw new \Exception("{$message}: {$key}");
        }
    }

    /**
     * Afirmar que una excepción es lanzada
     */
    public static function assertThrows(callable $callback, string $expectedExceptionClass = \Exception::class): void
    {
        try {
            $callback();
            throw new \Exception("Expected exception {$expectedExceptionClass} was not thrown");
        } catch (\Throwable $e) {
            if (!($e instanceof $expectedExceptionClass)) {
                throw new \Exception("Expected {$expectedExceptionClass}, got " . get_class($e));
            }
        }
    }

    /**
     * Crear datos de prueba para un bien
     */
    public static function createTestBien(): array
    {
        return [
            'nombre' => 'Equipo de Prueba ' . uniqid(),
            'descripcion' => 'Descripción de prueba',
            'marca' => 'Marca Test',
            'modelo' => 'Modelo Test',
            'serial' => 'TEST' . uniqid(),
            'id_tipo' => 1,
            'id_area' => 1,
            'id_estado' => 1,
            'valor_inicial' => 1000.00,
            'cantidad' => 1,
            'es_sn' => 0,
            'nro_bien_ministerio' => '1234567ABC',
            'cin_edificio' => 'Principal',
            'cin_piso' => '1',
            'cin_departamento' => 'Test',
            'cin_oficina' => 'Oficina Test',
            'cin_posicion' => '001'
        ];
    }

    /**
     * Crear usuario de prueba
     */
    public static function createTestUser(): array
    {
        $unique = uniqid();
        return [
            'cedula' => 'V' . rand(10000000, 99999999),
            'primer_nombre' => 'Test',
            'primer_apellido' => 'User' . $unique,
            'email' => "test{$unique}@test.com",
            'telefono' => '04121234567',
            'cargo' => 'Tester',
            'id_rol' => 4, // registrador
            'password_hash' => password_hash('Test123!', PASSWORD_BCRYPT, ['cost' => 12])
        ];
    }

    /**
     * Limpiar datos de prueba
     */
    public static function cleanupTestData(): void
    {
        // Limpiar bienes de prueba
        Database::query("DELETE FROM bienes WHERE nombre LIKE 'Equipo de Prueba%'");
        
        // Limpiar usuarios de prueba
        Database::query("DELETE FROM usuarios WHERE email LIKE '%@test.com'");
        
        // Limpiar movimientos de prueba
        Database::query("DELETE FROM movimientos WHERE motivo LIKE '%prueba%'");
    }

    /**
     * Simular login de usuario
     */
    public static function loginAs(array $user): void
    {
        Session::set('user_id', $user['id_usuario']);
        Session::set('username', $user['username'] ?? '');
        Session::set('nombre', $user['nombre_completo'] ?? 'Test User');
        Session::set('rol', $user['nombre_rol'] ?? 'registrador');
        Session::set('id_rol', $user['id_rol'] ?? 4);
        Session::set('primer_login', false);
    }

    /**
     * Simular logout
     */
    public static function logout(): void
    {
        Session::destroy();
    }
}