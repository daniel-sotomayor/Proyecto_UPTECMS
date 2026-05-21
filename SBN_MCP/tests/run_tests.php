<?php
/**
 * Tests Unitarios — Validaciones y Lógica de Negocio
 *
 * Ejecutar: php tests/run_tests.php
 *
 * Framework: PHPUnit-style assertions sin dependencias externas.
 * Compatible con PHP 7.4+ sin frameworks de testing.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');

require_once ROOT_PATH . '/vendor/autoload.php';

class TestRunner
{
    private int $passed = 0;
    private int $failed = 0;
    private array $failures = [];

    public function assert(bool $condition, string $message): void
    {
        if ($condition) {
            $this->passed++;
            echo "  ✓ {$message}\n";
        } else {
            $this->failed++;
            $this->failures[] = $message;
            echo "  ✗ {$message}\n";
        }
    }

    public function assertEquals(mixed $expected, mixed $actual, string $message): void
    {
        $this->assert($expected === $actual, "{$message} (expected: " . var_export($expected, true) . ", got: " . var_export($actual, true) . ")");
    }

    public function assertNotEmpty(mixed $value, string $message): void
    {
        $this->assert(!empty($value), $message);
    }

    public function assertEmpty(mixed $value, string $message): void
    {
        $this->assert(empty($value), $message);
    }

    public function assertCount(int $expected, array $array, string $message): void
    {
        $this->assertEquals($expected, count($array), $message);
    }

    public function summary(): void
    {
        echo "\n";
        echo str_repeat('=', 60) . "\n";
        echo "Resultados: {$this->passed} pasaron, {$this->failed} fallaron\n";
        if (!empty($this->failures)) {
            echo "\nFallos:\n";
            foreach ($this->failures as $i => $f) {
                echo "  " . ($i + 1) . ". {$f}\n";
            }
        }
        echo str_repeat('=', 60) . "\n";
        exit($this->failed > 0 ? 1 : 0);
    }
}

$test = new TestRunner();

echo "=== Tests: helpers.php ===\n\n";

// formatDate con fecha válida
$test->assertEquals('01/01/2026', formatDate('2026-01-01'), 'formatDate con fecha válida');

// formatDate con null
$test->assertEquals('—', formatDate(null), 'formatDate con null');

// formatDate con string vacío
$test->assertEquals('—', formatDate(''), 'formatDate con string vacío');

// formatDate con fecha inválida
$test->assertEquals('—', formatDate('abc'), 'formatDate con fecha inválida');

// formatDate con formato personalizado
$test->assertEquals('2026-01-15', formatDate('2026-01-15', 'Y-m-d'), 'formatDate con formato personalizado');

// formatCurrency
$test->assertEquals('Bs. 1.234,56', formatCurrency(1234.56), 'formatCurrency formatea correctamente');

// esc
$test->assertEquals('&lt;script&gt;', esc('<script>'), 'esc previene XSS');

echo "\n=== Tests: SecurityHelper ===\n\n";

use App\Helpers\SecurityHelper;

// Password hashing
$hash = SecurityHelper::hashPassword('Test1234!@#');
$test->assert(str_starts_with($hash, '$2y$'), 'Password hash usa bcrypt');
$test->assert(SecurityHelper::verifyPassword('Test1234!@#', $hash), 'verifyPassword acepta clave correcta');
$test->assert(!SecurityHelper::verifyPassword('WrongPass1!', $hash), 'verifyPassword rechaza clave incorrecta');

// Password strength validation (desde AuthController logic)
function validatePasswordStrength(string $password): array
{
    if (strlen($password) < 8)           return ['nueva_clave' => 'Mínimo 8 caracteres'];
    if (!preg_match('/[A-Z]/', $password)) return ['nueva_clave' => 'Debe incluir al menos una mayúscula'];
    if (!preg_match('/[a-z]/', $password)) return ['nueva_clave' => 'Debe incluir al menos una minúscula'];
    if (!preg_match('/[0-9]/', $password)) return ['nueva_clave' => 'Debe incluir al menos un número'];
    if (!preg_match('/[\W_]/', $password)) return ['nueva_clave' => 'Debe incluir al menos un carácter especial'];
    return [];
}

$test->assertEmpty(validatePasswordStrength('Str0ng!Pass'), 'Password fuerte pasa validación');
$test->assertNotEmpty(validatePasswordStrength('short'), 'Password corto falla');
$test->assertNotEmpty(validatePasswordStrength('nouppercase1!'), 'Password sin mayúscula falla');
$test->assertNotEmpty(validatePasswordStrength('NOLOWERCASE1!'), 'Password sin minúscula falla');
$test->assertNotEmpty(validatePasswordStrength('NoNumbers!!'), 'Password sin número falla');
$test->assertNotEmpty(validatePasswordStrength('NoSpecial1'), 'Password sin carácter especial falla');

// sanitizeInput
$test->assertEquals('&lt;script&gt;alert(1)&lt;/script&gt;', SecurityHelper::sanitizeInput('<script>alert(1)</script>'), 'sanitizeInput escapa HTML');

// validateCedula
$test->assert(SecurityHelper::validateCedula('V-12345678'), 'Cédula V válida');
$test->assert(SecurityHelper::validateCedula('E-12345678'), 'Cédula E válida');
$test->assert(SecurityHelper::validateCedula('12345678'), 'Cédula sin prefijo válida');
$test->assert(!SecurityHelper::validateCedula('abc'), 'Cédula inválida rechazada');

// validateEmail
$test->assert(SecurityHelper::validateEmail('test@example.com'), 'Email válido');
$test->assert(!SecurityHelper::validateEmail('invalid'), 'Email inválido rechazado');

// validateDecimal
$test->assert(SecurityHelper::validateDecimal('123.45'), 'Decimal válido');
$test->assert(!SecurityHelper::validateDecimal('123.456'), 'Decimal con 3 decimales rechazado');
$test->assert(!SecurityHelper::validateDecimal('abc'), 'No-numérico rechazado');

// validateDate
$test->assert(SecurityHelper::validateDate('2026-01-15'), 'Fecha válida');
$test->assert(!SecurityHelper::validateDate('2026-13-45'), 'Fecha inválida rechazada');

// CSRF Token
$token = SecurityHelper::generateCSRFToken();
$test->assert(strlen($token) === 64, 'CSRF token tiene 64 caracteres hex');
$test->assert(SecurityHelper::validateCSRFToken($token), 'CSRF token válido se acepta');
$test->assert(!SecurityHelper::validateCSRFToken('invalid'), 'CSRF token inválido se rechaza');

// generateToken
$token32 = SecurityHelper::generateToken(32);
$test->assert(strlen($token32) === 64, 'generateToken(32) retorna 64 chars hex');

echo "\n=== Tests: Cache ===\n\n";

use App\Core\Cache;

// Clean cache before tests
Cache::flush();

// Cache miss returns null
$test->assert(Cache::get('nonexistent') === null, 'Cache miss retorna null');

// Cache set and get
Cache::set('test_key', ['foo' => 'bar']);
$test->assertEquals(['foo' => 'bar'], Cache::get('test_key'), 'Cache set/get funciona');

// Cache remember
$result = Cache::remember('remember_test', fn() => ['computed' => true], 60);
$test->assertEquals(['computed' => true], $result, 'Cache remember ejecuta callback');
$cached = Cache::get('remember_test');
$test->assertEquals(['computed' => true], $cached, 'Cache remember almacena resultado');

// Cache forget
Cache::forget('test_key');
$test->assert(Cache::get('test_key') === null, 'Cache forget elimina clave');

// Cache flush
Cache::set('flush1', 'a');
Cache::set('flush2', 'b');
Cache::flush();
$test->assert(Cache::get('flush1') === null, 'Cache flush elimina todo');

echo "\n=== Tests: depreciación lineal ===\n\n";

// Valor inicial sin tiempo transcurrido
$dep1 = calcularDepreciacion(10000, 1000, 10, date('Y-m-d'));
$test->assert($dep1 <= 10000 && $dep1 >= 9900, 'Depreciación recién adquirido ≈ valor inicial');

// Valor residual como mínimo
$dep2 = calcularDepreciacion(10000, 1000, 10, '2000-01-01');
$test->assertEquals(1000.0, round($dep2, 0), 'Depreciación no baja del valor residual');

// Vida útil 0 retorna valor inicial
$dep3 = calcularDepreciacion(5000, 500, 0, '2020-01-01');
$test->assertEquals(5000.0, $dep3, 'Vida útil 0 retorna valor inicial');

echo "\n";
$test->summary();
