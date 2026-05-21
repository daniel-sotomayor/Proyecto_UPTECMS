<?php
/**
 * Configuración de la Aplicación
 * Sistema de Gestión de Bienes Nacionales
 * Maternidad Concepción Palacios
 */

return [
    // Configuración de la aplicación
    'app' => [
        'name' => 'Sistema de Gestión de Bienes Nacionales',
        'version' => '1.1.0',
        'env' => getenv('APP_ENV') ?: 'development',
        'debug' => filter_var(getenv('APP_DEBUG') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        'timezone' => getenv('APP_TIMEZONE') ?: 'America/Caracas',
        'key' => getenv('APP_KEY') ?: 'CHANGE_THIS_TO_SECURE_RANDOM_KEY_32_CHARS',
    ],

    // Configuración de seguridad
    'security' => [
        'csrf_protection' => true,
        'session_lifetime' => (int)(getenv('SESSION_LIFETIME') ?: 30), // minutos
        'max_login_attempts' => (int)(getenv('MAX_LOGIN_ATTEMPTS') ?: 5),
        'lockout_duration' => (int)(getenv('LOCKOUT_DURATION') ?: 900), // segundos
        'password_min_length' => 8,
        'hash_algorithm' => PASSWORD_BCRYPT,
        'hash_cost' => 12,
    ],

    // Configuración de archivos
    'files' => [
        'max_size' => (int)(getenv('MAX_FILE_SIZE') ?: 5242880), // 5MB
        'allowed_types' => explode(',', getenv('ALLOWED_FILE_TYPES') ?: 'jpg,jpeg,png,webp'),
        'upload_path' => dirname(__DIR__) . '/uploads',
    ],

    // Configuración de paginación
    'pagination' => [
        'per_page' => 20,
        'max_per_page' => 100,
    ],

    // Rutas de la aplicación
    'paths' => [
        'uploads' => dirname(__DIR__) . '/uploads',
        'reports' => dirname(__DIR__) . '/reports',
        'logs' => dirname(__DIR__) . '/logs',
        'backups' => dirname(__DIR__) . '/backups',
    ],

    // Configuración de rate limiting
    'rate_limit' => [
        'requests' => (int)(getenv('RATE_LIMIT_REQUESTS') ?: 60),
        'window' => (int)(getenv('RATE_LIMIT_WINDOW') ?: 3600), // 1 hora
    ],
];
