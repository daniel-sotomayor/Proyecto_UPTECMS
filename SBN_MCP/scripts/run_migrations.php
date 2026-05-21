<?php
/**
 * Ejecuta migraciones SQL simples desde la carpeta /sql
 * Uso: php scripts/run_migrations.php
 */
declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');

// Cargar .env
$envFile = ROOT_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        if (!empty($key) && getenv($key) === false) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }
}

require ROOT_PATH . '/vendor/autoload.php';

$dir = ROOT_PATH . '/sql';
$files = glob($dir . '/*.sql');
if (!$files) {
    echo "No se encontraron archivos SQL en $dir\n";
    exit(0);
}

// Conexión usando mysqli para evitar dependencia de PDO en CLI
$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbPort = (int)(getenv('DB_PORT') ?: 3306);
$dbName = getenv('DB_NAME') ?: 'hospital_bienes';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';

$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
if ($mysqli->connect_errno) {
    fwrite(STDERR, "Error conectando a la base de datos: ({$mysqli->connect_errno}) {$mysqli->connect_error}\n");
    exit(1);
}

foreach ($files as $file) {
    echo "Aplicando: " . basename($file) . "\n";
    $sql = file_get_contents($file);
    // Separar por punto y coma en líneas nuevas — asume SQL simple
    $statements = array_filter(array_map('trim', preg_split('/;\s*\n/', $sql)));
    foreach ($statements as $stmt) {
        if ($stmt === '') continue;
        if (!$mysqli->query($stmt)) {
            fwrite(STDERR, "Error ejecutando statement: ({$mysqli->errno}) {$mysqli->error}\n");
        }
    }
}

$mysqli->close();
echo "Migraciones finalizadas.\n";
