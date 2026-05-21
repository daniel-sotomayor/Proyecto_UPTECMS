<?php declare(strict_types=1);
/**
 * Pruebas del Sistema SBN_MCP
 * Sistema de Gestión de Bienes Nacionales - MCP
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/core/TestRunner.php';

use App\Core\TestRunner;
use App\Tests\TestUtils;
use App\Core\Database;
use App\Core\Session;
use App\Core\Logger;
use App\Core\Validator;
use App\Controllers\AuthController;
use App\Controllers\BienController;

// Configurar entorno de pruebas
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');

// Cargar configuración
$config = require CONFIG_PATH . '/app.php';
date_default_timezone_set($config['app']['timezone']);

// Inicializar componentes
Logger::init(['log_level' => 'ERROR']); // Solo errores en pruebas
Session::start();

$runner = new TestRunner();

// ============================================================================
// PRUEBAS DE CONFIGURACIÓN Y CONEXIÓN
// ============================================================================

$runner->test('Configuración cargada correctamente', function() use ($config) {
    TestUtils::assertNotNull($config, 'Configuración no cargada');
    TestUtils::assertArrayHasKey('app', $config, 'Sección app faltante');
    TestUtils::assertArrayHasKey('security', $config, 'Sección security faltante');
    TestUtils::assertEquals('1.1.0', $config['app']['version'], 'Versión incorrecta');
});

$runner->test('Conexión a base de datos', function() {
    $dbConfig = require CONFIG_PATH . '/database.php';
    $connection = Database::connect($dbConfig);
    TestUtils::assertNotNull($connection, 'No se pudo conectar a la base de datos');
    
    // Verificar que las tablas principales existen
    $tables = ['usuarios', 'bienes', 'roles', 'estados', 'tipos_bien', 'areas'];
    foreach ($tables as $table) {
        $exists = Database::fetchValue("SHOW TABLES LIKE '{$table}'");
        TestUtils::assertNotNull($exists, "Tabla {$table} no existe");
    }
});

// ============================================================================
// PRUEBAS DEL SISTEMA DE VALIDACIÓN
// ============================================================================

$runner->test('Validador - Campos requeridos', function() {
    $validator = new Validator(['nombre' => '', 'email' => 'test@test.com']);
    $validator->rules([
        'nombre' => 'required',
        'email' => 'required|email'
    ]);
    
    $isValid = $validator->validate();
    TestUtils::assertTrue(!$isValid, 'Validación debería fallar');
    TestUtils::assertNotNull($validator->error('nombre'), 'Error de nombre requerido');
});

$runner->test('Validador - Email válido', function() {
    $validator = new Validator(['email' => 'invalid-email']);
    $validator->rules(['email' => 'email']);
    
    $isValid = $validator->validate();
    TestUtils::assertTrue(!$isValid, 'Email inválido debería fallar');
});

$runner->test('Validador - Contraseña segura', function() {
    $errors = Validator::validatePassword('123');
    TestUtils::assertTrue(count($errors) > 0, 'Contraseña débil debería tener errores');
    
    $errors = Validator::validatePassword('Test123!');
    TestUtils::assertTrue(count($errors) === 0, 'Contraseña fuerte no debería tener errores');
});

$runner->test('Validador - Cédula venezolana', function() {
    TestUtils::assertTrue(Validator::validateCedula('V12345678'), 'Cédula válida V');
    TestUtils::assertTrue(Validator::validateCedula('E87654321'), 'Cédula válida E');
    TestUtils::assertTrue(!Validator::validateCedula('X12345678'), 'Cédula inválida X');
    TestUtils::assertTrue(!Validator::validateCedula('V123'), 'Cédula muy corta');
});

// ============================================================================
// PRUEBAS DE AUTENTICACIÓN
// ============================================================================

$runner->test('Autenticación - Usuario admin existe', function() {
    $admin = Database::fetch(
        "SELECT u.*, r.nombre AS nombre_rol 
         FROM usuarios u 
         JOIN roles r ON u.id_rol = r.id_rol 
         WHERE u.username = 'admin' AND u.activo = TRUE"
    );
    
    TestUtils::assertNotNull($admin, 'Usuario admin no existe');
    TestUtils::assertEquals('administrador', $admin['nombre_rol'], 'Admin no tiene rol correcto');
});

$runner->test('Autenticación - Hash de contraseña', function() {
    $password = 'Test123!';
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    
    TestUtils::assertTrue(password_verify($password, $hash), 'Hash de contraseña no funciona');
    TestUtils::assertTrue(!password_verify('wrong', $hash), 'Hash acepta contraseña incorrecta');
});

// ============================================================================
// PRUEBAS DE BIENES
// ============================================================================

$runner->test('Bienes - Crear bien de prueba', function() {
    TestUtils::cleanupTestData(); // Limpiar antes
    
    $bienData = TestUtils::createTestBien();
    
    // Simular inserción
    $sql = "INSERT INTO bienes (nombre, descripcion, marca, modelo, serial, id_tipo, id_area, id_estado, valor_inicial, cantidad, es_sn, nro_bien_ministerio) 
            VALUES (:nombre, :descripcion, :marca, :modelo, :serial, :id_tipo, :id_area, :id_estado, :valor_inicial, :cantidad, :es_sn, :nro_bien_ministerio)";
    
    Database::beginTransaction();
    try {
        Database::query($sql, $bienData);
        $bienId = Database::lastInsertId();
        TestUtils::assertTrue($bienId > 0, 'Bien no se creó correctamente');
        
        // Verificar que se creó
        $bien = Database::fetch("SELECT * FROM bienes WHERE id_bien = ?", [$bienId]);
        TestUtils::assertNotNull($bien, 'Bien no se encontró después de crear');
        TestUtils::assertEquals($bienData['nombre'], $bien['nombre'], 'Nombre del bien no coincide');
        
        Database::commit();
    } catch (\Exception $e) {
        Database::rollBack();
        throw $e;
    }
});

$runner->test('Bienes - Código SUDEBIP único', function() {
    $year = date('Y');
    $pattern = "BN-{$year}-%";
    
    // Obtener el siguiente número
    $row = Database::fetch(
        "SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(codigo_sudebip, '-', -1) AS UNSIGNED)), 0) + 1 AS seq
         FROM bienes WHERE codigo_sudebip LIKE ?",
        [$pattern]
    );
    
    $nextSeq = $row['seq'] ?? 1;
    $codigo = sprintf('BN-%s-%06d', $year, $nextSeq);
    
    TestUtils::assertTrue(Validator::validateCodigoSudebip($codigo), 'Código SUDEBIP inválido');
});

// ============================================================================
// PRUEBAS DE SEGURIDAD
// ============================================================================

$runner->test('Seguridad - Sanitización de entrada', function() {
    $maliciousInput = "<script>alert('xss')</script>";
    $sanitized = htmlspecialchars($maliciousInput, ENT_QUOTES, 'UTF-8');
    
    TestUtils::assertTrue(strpos($sanitized, '<script>') === false, 'XSS no fue sanitizado');
});

$runner->test('Seguridad - SQL Injection prevención', function() {
    $maliciousInput = "'; DROP TABLE usuarios; --";
    
    // Esto debería ser seguro con consultas preparadas
    $result = Database::fetch("SELECT COUNT(*) as count FROM usuarios WHERE username = ?", [$maliciousInput]);
    TestUtils::assertEquals(0, $result['count'], 'SQL injection no fue prevenida');
    
    // Verificar que la tabla usuarios sigue existiendo
    $tableExists = Database::fetchValue("SHOW TABLES LIKE 'usuarios'");
    TestUtils::assertNotNull($tableExists, 'Tabla usuarios fue eliminada por SQL injection');
});

// ============================================================================
// PRUEBAS DE RENDIMIENTO
// ============================================================================

$runner->test('Rendimiento - Consulta de bienes', function() {
    $startTime = microtime(true);
    
    $bienes = Database::fetchAll(
        "SELECT b.*, e.nombre as estado_nombre 
         FROM bienes b 
         JOIN estados e ON b.id_estado = e.id_estado 
         LIMIT 100"
    );
    
    $duration = microtime(true) - $startTime;
    
    TestUtils::assertTrue($duration < 1.0, 'Consulta de bienes muy lenta: ' . $duration . 's');
    TestUtils::assertTrue(is_array($bienes), 'Resultado no es array');
});

$runner->test('Rendimiento - Paginación eficiente', function() {
    $startTime = microtime(true);
    
    $total = Database::fetchValue("SELECT COUNT(*) FROM bienes");
    $bienes = Database::fetchAll("SELECT * FROM bienes LIMIT 20 OFFSET 0");
    
    $duration = microtime(true) - $startTime;
    
    TestUtils::assertTrue($duration < 0.5, 'Paginación muy lenta: ' . $duration . 's');
    TestUtils::assertTrue(count($bienes) <= 20, 'Paginación no respeta límite');
});

// ============================================================================
// PRUEBAS DE INTEGRIDAD DE DATOS
// ============================================================================

$runner->test('Integridad - Roles y permisos', function() {
    $roles = Database::fetchAll("SELECT * FROM roles WHERE activo = TRUE");
    $expectedRoles = ['administrador', 'gerencia_bn', 'controlador_inventario', 'registrador'];
    
    TestUtils::assertTrue(count($roles) >= 4, 'Faltan roles del sistema');
    
    foreach ($expectedRoles as $expectedRole) {
        $found = false;
        foreach ($roles as $role) {
            if ($role['nombre'] === $expectedRole) {
                $found = true;
                break;
            }
        }
        TestUtils::assertTrue($found, "Rol {$expectedRole} no encontrado");
    }
});

$runner->test('Integridad - Estados de bienes', function() {
    $estados = Database::fetchAll("SELECT * FROM estados ORDER BY id_estado");
    $expectedStates = ['Operativo', 'Inoperativo', 'En Resguardo', 'Chatarra', 'Desincorporado'];
    
    TestUtils::assertTrue(count($estados) >= 5, 'Faltan estados del sistema');
    
    for ($i = 0; $i < min(5, count($estados)); $i++) {
        TestUtils::assertEquals($expectedStates[$i], $estados[$i]['nombre'], "Estado {$i} incorrecto");
    }
});

// ============================================================================
// PRUEBAS DE LOGGING
// ============================================================================

$runner->test('Logging - Escribir y leer logs', function() {
    $testMessage = 'Test log message ' . uniqid();
    Logger::info($testMessage, ['test' => true]);
    
    // Verificar que el log se escribió
    $logFile = dirname(__DIR__) . '/logs/app-' . date('Y-m-d') . '.log';
    if (file_exists($logFile)) {
        $logContent = file_get_contents($logFile);
        TestUtils::assertTrue(strpos($logContent, $testMessage) !== false, 'Log no se escribió correctamente');
    }
});

// ============================================================================
// LIMPIAR DATOS DE PRUEBA
// ============================================================================

$runner->test('Limpieza - Datos de prueba', function() {
    TestUtils::cleanupTestData();
    
    // Verificar limpieza
    $testBienes = Database::fetchValue("SELECT COUNT(*) FROM bienes WHERE nombre LIKE 'Equipo de Prueba%'");
    $testUsers = Database::fetchValue("SELECT COUNT(*) FROM usuarios WHERE email LIKE '%@test.com'");
    
    TestUtils::assertEquals(0, $testBienes, 'Bienes de prueba no fueron limpiados');
    TestUtils::assertEquals(0, $testUsers, 'Usuarios de prueba no fueron limpiados');
});

// Ejecutar todas las pruebas
$results = $runner->run();

// Generar reporte de pruebas
$reportFile = dirname(__DIR__) . '/logs/test-report-' . date('Y-m-d-H-i-s') . '.json';
file_put_contents($reportFile, json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "\n📄 Reporte guardado en: {$reportFile}\n";