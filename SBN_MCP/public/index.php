<?php
/**
 * Punto de Entrada - Front Controller
 * Sistema de Gestión de Bienes Nacionales
 * Maternidad Concepción Palacios
 */

// Definir constantes
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// Cargar variables de entorno desde .env (sin dependencias externas)
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

// Cargar autoload — ruta fija, sin interpolación de usuario
require ROOT_PATH . '/vendor/autoload.php';

// Cargar excepciones HTTP (dos clases en un archivo, fuera del autoloader PSR-4)
require_once APP_PATH . '/core/HttpExceptions.php';

// Cargar configuración
$config = require CONFIG_PATH . '/app.php';

// Establecer zona horaria
date_default_timezone_set($config['app']['timezone']);

// Configurar manejo de errores
if ($config['app']['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

// Inicializar aplicación
require_once APP_PATH . '/core/App.php';

use App\Core\App;
use App\Core\HttpRedirectException;
use App\Core\HttpResponseException;

define('BASE', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));

try {
    $app = new App();
    $app->run();
} catch (HttpRedirectException | HttpResponseException $e) {
    // Respuesta HTTP normal (redirect, 404, JSON, etc.) — no es un error
} catch (\Throwable $e) {
    // Sanitizar datos antes de loguear para prevenir log injection
    $safeMsg = preg_replace('/[\r\n\t]/', ' ', $e->getMessage());
    $safeIp  = preg_replace('/[^0-9a-fA-F.:,]/', '', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $safeUri = preg_replace('/[\r\n]/', '', $_SERVER['REQUEST_URI'] ?? 'unknown');

    $errorMessage = sprintf(
        '[%s] ERROR: %s | IP: %s | URI: %s',
        date('Y-m-d H:i:s'),
        $safeMsg,
        $safeIp,
        $safeUri
    );
    error_log($errorMessage);

    if ($config['app']['debug']) {
        http_response_code(500);
        echo '<h1>Error</h1>';
        echo '<p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8') . '</pre>';
    } else {
        http_response_code(500);
        echo '<h1>Error del Sistema</h1>';
        echo '<p>Ha ocurrido un error. Por favor, contacte al administrador.</p>';
    }
}
