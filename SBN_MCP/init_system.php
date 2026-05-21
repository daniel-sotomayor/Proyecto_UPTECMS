<?php declare(strict_types=1);
/**
 * Script de Inicialización y Verificación del Sistema
 * Sistema de Gestión de Bienes Nacionales - MCP
 */

// Configurar entorno
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');

// Cargar autoload
require_once ROOT_PATH . '/vendor/autoload.php';

// Cargar variables de entorno
$envFile = ROOT_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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

use App\Core\Database;
use App\Core\Logger;
use App\Core\Session;

echo "🚀 Inicializando Sistema SBN_MCP...\n\n";

class SystemInitializer
{
    private array $checks = [];
    private array $errors = [];
    private array $warnings = [];

    public function run(): bool
    {
        echo "📋 Ejecutando verificaciones del sistema...\n\n";

        $this->checkEnvironment();
        $this->checkDirectories();
        $this->checkDatabase();
        $this->checkSecurity();
        $this->checkConfiguration();
        $this->initializeSystem();

        $this->printResults();
        
        return empty($this->errors);
    }

    /**
     * Verificar entorno PHP
     */
    private function checkEnvironment(): void
    {
        echo "🔍 Verificando entorno PHP...\n";

        // Versión PHP
        $phpVersion = PHP_VERSION;
        if (version_compare($phpVersion, '7.4.0', '<')) {
            $this->errors[] = "PHP 7.4+ requerido. Versión actual: {$phpVersion}";
        } else {
            $this->checks[] = "✅ PHP {$phpVersion}";
        }

        // Extensiones requeridas
        $requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'openssl', 'gd', 'fileinfo'];
        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $this->errors[] = "Extensión PHP requerida: {$ext}";
            } else {
                $this->checks[] = "✅ Extensión {$ext}";
            }
        }

        // Configuración PHP
        if (!ini_get('allow_url_fopen')) {
            $this->warnings[] = "allow_url_fopen deshabilitado";
        }

        if (ini_get('display_errors')) {
            $this->warnings[] = "display_errors habilitado (desactivar en producción)";
        }
    }

    /**
     * Verificar directorios y permisos
     */
    private function checkDirectories(): void
    {
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
                    $this->checks[] = "✅ Directorio {$name} creado";
                } else {
                    $this->errors[] = "No se pudo crear directorio: {$path}";
                    continue;
                }
            }

            if (!is_writable($path)) {
                $this->errors[] = "Directorio sin permisos de escritura: {$path}";
            } else {
                $this->checks[] = "✅ Permisos {$name}";
            }
        }

        // Verificar .htaccess en uploads
        $htaccessPath = ROOT_PATH . '/uploads/.htaccess';
        if (!file_exists($htaccessPath)) {
            $htaccessContent = "RemoveHandler .php .phtml .php3 .php4 .php5 .phtml .pl .py .jsp .asp .sh .cgi\n";
            $htaccessContent .= "php_flag engine off\n";
            $htaccessContent .= "Options -ExecCGI\n";
            $htaccessContent .= "AddType text/plain .php .phtml .php3 .php4 .php5 .phtml .pl .py .jsp .asp .sh .cgi\n";
            
            if (file_put_contents($htaccessPath, $htaccessContent)) {
                $this->checks[] = "✅ .htaccess de seguridad creado en uploads";
            } else {
                $this->warnings[] = "No se pudo crear .htaccess en uploads";
            }
        }
    }

    /**
     * Verificar conexión y estructura de base de datos
     */
    private function checkDatabase(): void
    {
        echo "🗄️  Verificando base de datos...\n";

        try {
            // Cargar configuración
            $dbConfig = require CONFIG_PATH . '/database.php';
            $connection = Database::connect($dbConfig);
            $this->checks[] = "✅ Conexión a base de datos";

            // Verificar tablas principales
            $requiredTables = [
                'usuarios', 'roles', 'bienes', 'estados', 'tipos_bien', 
                'areas', 'movimientos', 'auditoria', 'configuracion'
            ];

            foreach ($requiredTables as $table) {
                $exists = Database::fetchValue("SHOW TABLES LIKE '{$table}'");
                if (!$exists) {
                    $this->errors[] = "Tabla faltante: {$table}";
                } else {
                    $this->checks[] = "✅ Tabla {$table}";
                }
            }

            // Verificar usuario admin
            $admin = Database::fetch(
                "SELECT u.*, r.nombre as rol FROM usuarios u 
                 JOIN roles r ON u.id_rol = r.id_rol 
                 WHERE u.username = 'admin'"
            );

            if (!$admin) {
                $this->errors[] = "Usuario admin no encontrado";
            } else {
                $this->checks[] = "✅ Usuario admin existe";
                if ($admin['rol'] !== 'administrador') {
                    $this->warnings[] = "Usuario admin no tiene rol de administrador";
                }
            }

            // Verificar datos básicos
            $rolesCount = Database::fetchValue("SELECT COUNT(*) FROM roles WHERE activo = TRUE");
            if ($rolesCount < 4) {
                $this->warnings[] = "Faltan roles del sistema";
            }

            $estadosCount = Database::fetchValue("SELECT COUNT(*) FROM estados");
            if ($estadosCount < 5) {
                $this->warnings[] = "Faltan estados de bienes";
            }

        } catch (\Exception $e) {
            $this->errors[] = "Error de base de datos: " . $e->getMessage();
        }
    }

    /**
     * Verificar configuración de seguridad
     */
    private function checkSecurity(): void
    {
        echo "🔒 Verificando configuración de seguridad...\n";

        // Verificar archivo .env
        $envPath = ROOT_PATH . '/.env';
        if (!file_exists($envPath)) {
            $this->errors[] = "Archivo .env no encontrado";
        } else {
            $this->checks[] = "✅ Archivo .env existe";
            
            $envContent = file_get_contents($envPath);
            if (strpos($envContent, 'CHANGE_THIS_TO_SECURE_RANDOM_KEY') !== false) {
                $this->warnings[] = "APP_KEY no ha sido cambiada (generar clave segura)";
            }
            
            if (strpos($envContent, 'APP_DEBUG=true') !== false) {
                $this->warnings[] = "DEBUG habilitado (desactivar en producción)";
            }
        }

        // Verificar .htaccess
        $htaccessPath = ROOT_PATH . '/public/.htaccess';
        if (!file_exists($htaccessPath)) {
            $this->errors[] = "Archivo .htaccess no encontrado en public/";
        } else {
            $this->checks[] = "✅ .htaccess configurado";
        }

        // Verificar permisos de archivos sensibles
        $sensitiveFiles = [
            ROOT_PATH . '/.env',
            ROOT_PATH . '/config/database.php',
        ];

        foreach ($sensitiveFiles as $file) {
            if (file_exists($file)) {
                $perms = fileperms($file) & 0777;
                if ($perms > 0644) {
                    $this->warnings[] = "Permisos muy amplios en: " . basename($file);
                }
            }
        }
    }

    /**
     * Verificar configuración general
     */
    private function checkConfiguration(): void
    {
        echo "⚙️  Verificando configuración...\n";

        try {
            $config = require CONFIG_PATH . '/app.php';
            $this->checks[] = "✅ Configuración cargada";

            // Verificar configuraciones críticas
            if (empty($config['app']['key']) || $config['app']['key'] === 'CHANGE_THIS_TO_SECURE_RANDOM_KEY_32_CHARS') {
                $this->warnings[] = "APP_KEY no configurada";
            }

            if ($config['app']['debug'] && $config['app']['env'] === 'production') {
                $this->warnings[] = "DEBUG habilitado en producción";
            }

            // Verificar zona horaria
            $timezone = $config['app']['timezone'] ?? 'UTC';
            if (!in_array($timezone, timezone_identifiers_list())) {
                $this->errors[] = "Zona horaria inválida: {$timezone}";
            } else {
                date_default_timezone_set($timezone);
                $this->checks[] = "✅ Zona horaria: {$timezone}";
            }

        } catch (\Exception $e) {
            $this->errors[] = "Error cargando configuración: " . $e->getMessage();
        }
    }

    /**
     * Inicializar componentes del sistema
     */
    private function initializeSystem(): void
    {
        echo "🔧 Inicializando componentes...\n";

        try {
            // Inicializar Logger
            Logger::init([
                'log_level' => 'INFO',
                'max_file_size' => 10 * 1024 * 1024,
                'max_files' => 30
            ]);
            $this->checks[] = "✅ Logger inicializado";

            // Inicializar Session
            Session::start();
            $this->checks[] = "✅ Sistema de sesiones";

            // Log de inicialización
            Logger::info('Sistema inicializado correctamente', [
                'version' => '1.1.0',
                'php_version' => PHP_VERSION,
                'timestamp' => date('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            $this->errors[] = "Error inicializando sistema: " . $e->getMessage();
        }
    }

    /**
     * Imprimir resultados
     */
    private function printResults(): void
    {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "📊 RESUMEN DE VERIFICACIÓN\n";
        echo str_repeat("=", 60) . "\n";

        if (!empty($this->checks)) {
            echo "✅ VERIFICACIONES EXITOSAS:\n";
            foreach ($this->checks as $check) {
                echo "   {$check}\n";
            }
            echo "\n";
        }

        if (!empty($this->warnings)) {
            echo "⚠️  ADVERTENCIAS:\n";
            foreach ($this->warnings as $warning) {
                echo "   ⚠️  {$warning}\n";
            }
            echo "\n";
        }

        if (!empty($this->errors)) {
            echo "❌ ERRORES CRÍTICOS:\n";
            foreach ($this->errors as $error) {
                echo "   ❌ {$error}\n";
            }
            echo "\n";
        }

        $totalChecks = count($this->checks);
        $totalWarnings = count($this->warnings);
        $totalErrors = count($this->errors);

        echo "📈 ESTADÍSTICAS:\n";
        echo "   ✅ Verificaciones exitosas: {$totalChecks}\n";
        echo "   ⚠️  Advertencias: {$totalWarnings}\n";
        echo "   ❌ Errores: {$totalErrors}\n\n";

        if ($totalErrors === 0) {
            echo "🎉 SISTEMA LISTO PARA USAR\n";
            echo "   Acceder en: http://localhost/SBN_MCP/public/\n";
            echo "   Usuario: admin\n";
            echo "   Clave: Admin_bn (cambiar en primer login)\n\n";
        } else {
            echo "🚨 SISTEMA NO ESTÁ LISTO\n";
            echo "   Corregir errores antes de continuar\n\n";
        }

        if ($totalWarnings > 0) {
            echo "💡 RECOMENDACIONES:\n";
            echo "   - Revisar advertencias antes de producción\n";
            echo "   - Generar APP_KEY segura: openssl rand -base64 32\n";
            echo "   - Desactivar DEBUG en producción\n";
            echo "   - Configurar HTTPS y certificados SSL\n\n";
        }
    }
}

// Ejecutar inicialización
$initializer = new SystemInitializer();
$success = $initializer->run();

exit($success ? 0 : 1);