<?php declare(strict_types=1);
/**
 * Script de Verificación Básica del Sistema
 * Sistema de Gestión de Bienes Nacionales - MCP
 */

echo "🚀 Verificando Sistema SBN_MCP...\n\n";

// Configurar entorno
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');

$checks = [];
$errors = [];
$warnings = [];

// ============================================================================
// VERIFICAR ENTORNO PHP
// ============================================================================

echo "🔍 Verificando entorno PHP...\n";

// Versión PHP
$phpVersion = PHP_VERSION;
if (version_compare($phpVersion, '7.4.0', '<')) {
    $errors[] = "PHP 7.4+ requerido. Versión actual: {$phpVersion}";
} else {
    $checks[] = "✅ PHP {$phpVersion}";
}

// Extensiones requeridas
$requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'openssl', 'gd', 'fileinfo', 'zip'];
foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $errors[] = "Extensión PHP requerida: {$ext}";
    } else {
        $checks[] = "✅ Extensión {$ext}";
    }
}

// ============================================================================
// VERIFICAR DIRECTORIOS Y PERMISOS
// ============================================================================

echo "📁 Verificando directorios y permisos...\n";

$directories = [
    'logs' => ROOT_PATH . '/logs',
    'uploads' => ROOT_PATH . '/uploads',
    'uploads/bienes' => ROOT_PATH . '/uploads/bienes',
    'reports' => ROOT_PATH . '/reports',
    'backups' => ROOT_PATH . '/backups',
];

foreach ($directories as $name => $path) {
    if (!is_dir($path)) {
        if (mkdir($path, 0755, true)) {
            $checks[] = "✅ Directorio {$name} creado";
        } else {
            $errors[] = "No se pudo crear directorio: {$path}";
            continue;
        }
    }

    if (!is_writable($path)) {
        $errors[] = "Directorio sin permisos de escritura: {$path}";
    } else {
        $checks[] = "✅ Permisos {$name}";
    }
}

// Crear .htaccess de seguridad en uploads
$htaccessPath = ROOT_PATH . '/uploads/.htaccess';
if (!file_exists($htaccessPath)) {
    $htaccessContent = "RemoveHandler .php .phtml .php3 .php4 .php5 .phtml .pl .py .jsp .asp .sh .cgi\n";
    $htaccessContent .= "php_flag engine off\n";
    $htaccessContent .= "Options -ExecCGI\n";
    $htaccessContent .= "AddType text/plain .php .phtml .php3 .php4 .php5 .phtml .pl .py .jsp .asp .sh .cgi\n";
    
    if (file_put_contents($htaccessPath, $htaccessContent)) {
        $checks[] = "✅ .htaccess de seguridad creado en uploads";
    } else {
        $warnings[] = "No se pudo crear .htaccess en uploads";
    }
}

// ============================================================================
// VERIFICAR ARCHIVOS DE CONFIGURACIÓN
// ============================================================================

echo "⚙️  Verificando configuración...\n";

// Verificar archivo .env
$envPath = ROOT_PATH . '/.env';
if (!file_exists($envPath)) {
    $errors[] = "Archivo .env no encontrado";
} else {
    $checks[] = "✅ Archivo .env existe";
    
    $envContent = file_get_contents($envPath);
    if (strpos($envContent, 'CHANGE_THIS_TO_SECURE_RANDOM_KEY') !== false) {
        $warnings[] = "APP_KEY no ha sido cambiada (generar clave segura)";
    }
    
    if (strpos($envContent, 'APP_DEBUG=true') !== false) {
        $warnings[] = "DEBUG habilitado (desactivar en producción)";
    }
}

// Verificar .htaccess
$htaccessPath = ROOT_PATH . '/public/.htaccess';
if (!file_exists($htaccessPath)) {
    $errors[] = "Archivo .htaccess no encontrado en public/";
} else {
    $checks[] = "✅ .htaccess configurado";
}

// Verificar archivos de configuración
$configFiles = [
    'app.php' => CONFIG_PATH . '/app.php',
    'database.php' => CONFIG_PATH . '/database.php',
];

foreach ($configFiles as $name => $path) {
    if (!file_exists($path)) {
        $errors[] = "Archivo de configuración faltante: {$name}";
    } else {
        $checks[] = "✅ Configuración {$name}";
    }
}

// ============================================================================
// VERIFICAR BASE DE DATOS
// ============================================================================

echo "🗄️  Verificando base de datos...\n";

try {
    // Cargar variables de entorno
    if (file_exists($envPath)) {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue;
            if (strpos($line, '=') === false) continue;
            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);
            if (!empty($key) && getenv($key) === false) {
                putenv("{$key}={$value}");
                $_ENV[$key]    = $value;
                $_SERVER[$key] = $value;
            }
        }
    }

    // Intentar conexión a base de datos
    $host = getenv('DB_HOST') ?: 'localhost';
    $port = getenv('DB_PORT') ?: '3306';
    $database = getenv('DB_NAME') ?: 'hospital_bienes';
    $username = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASS') ?: '';

    $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $checks[] = "✅ Conexión a base de datos";

    // Verificar tablas principales
    $requiredTables = [
        'usuarios', 'roles', 'bienes', 'estados', 'tipos_bien', 
        'areas', 'movimientos', 'auditoria', 'configuracion'
    ];

    foreach ($requiredTables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        if (!$stmt->fetch()) {
            $errors[] = "Tabla faltante: {$table}";
        } else {
            $checks[] = "✅ Tabla {$table}";
        }
    }

    // Verificar usuario admin
    $stmt = $pdo->prepare(
        "SELECT u.username, r.nombre as rol FROM usuarios u 
         JOIN roles r ON u.id_rol = r.id_rol 
         WHERE u.username = 'admin'"
    );
    $stmt->execute();
    $admin = $stmt->fetch();

    if (!$admin) {
        $warnings[] = "Usuario admin no encontrado (crear manualmente)";
    } else {
        $checks[] = "✅ Usuario admin existe";
    }

} catch (PDOException $e) {
    $errors[] = "Error de base de datos: " . $e->getMessage();
}

// ============================================================================
// VERIFICAR COMPOSER
// ============================================================================

echo "📦 Verificando dependencias...\n";

if (!file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    $errors[] = "Dependencias no instaladas. Ejecutar: composer install";
} else {
    $checks[] = "✅ Dependencias instaladas";
}

// ============================================================================
// IMPRIMIR RESULTADOS
// ============================================================================

echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 RESUMEN DE VERIFICACIÓN\n";
echo str_repeat("=", 60) . "\n";

if (!empty($checks)) {
    echo "✅ VERIFICACIONES EXITOSAS:\n";
    foreach ($checks as $check) {
        echo "   {$check}\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "⚠️  ADVERTENCIAS:\n";
    foreach ($warnings as $warning) {
        echo "   ⚠️  {$warning}\n";
    }
    echo "\n";
}

if (!empty($errors)) {
    echo "❌ ERRORES CRÍTICOS:\n";
    foreach ($errors as $error) {
        echo "   ❌ {$error}\n";
    }
    echo "\n";
}

$totalChecks = count($checks);
$totalWarnings = count($warnings);
$totalErrors = count($errors);

echo "📈 ESTADÍSTICAS:\n";
echo "   ✅ Verificaciones exitosas: {$totalChecks}\n";
echo "   ⚠️  Advertencias: {$totalWarnings}\n";
echo "   ❌ Errores: {$totalErrors}\n\n";

if ($totalErrors === 0) {
    echo "🎉 SISTEMA LISTO PARA USAR\n";
    echo "   Acceder en: http://localhost/SBN_MCP/public/\n";
    echo "   Usuario: admin\n";
    echo "   Clave: Admin_bn (cambiar en primer login)\n\n";
    
    // Crear archivo de estado
    file_put_contents(ROOT_PATH . '/logs/system_status.json', json_encode([
        'status' => 'ready',
        'timestamp' => date('Y-m-d H:i:s'),
        'checks' => $totalChecks,
        'warnings' => $totalWarnings,
        'errors' => $totalErrors
    ], JSON_PRETTY_PRINT));
    
} else {
    echo "🚨 SISTEMA NO ESTÁ LISTO\n";
    echo "   Corregir errores antes de continuar\n\n";
}

if ($totalWarnings > 0) {
    echo "💡 RECOMENDACIONES:\n";
    echo "   - Revisar advertencias antes de producción\n";
    echo "   - Generar APP_KEY segura: openssl rand -base64 32\n";
    echo "   - Desactivar DEBUG en producción\n";
    echo "   - Configurar HTTPS y certificados SSL\n";
    echo "   - Ejecutar: composer install (si no se ha hecho)\n\n";
}

echo "📋 PRÓXIMOS PASOS:\n";
echo "   1. Corregir errores críticos si los hay\n";
echo "   2. Ejecutar: php tests/SystemTest.php (para pruebas completas)\n";
echo "   3. Acceder al sistema y cambiar contraseña admin\n";
echo "   4. Configurar usuarios y roles según necesidades\n\n";

exit($totalErrors > 0 ? 1 : 0);