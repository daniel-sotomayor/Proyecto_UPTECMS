<?php declare(strict_types=1);
/**
 * Gestión de Sesiones Seguras
 * Sistema de Gestión de Bienes Nacionales
 * Cumplimiento OWASP
 * [Code Review: strict_types=1]
 */

namespace App\Core;

class Session
{
    /**
     * Iniciar sesión segura
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        // Detectar si la conexión es HTTPS
        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                    || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
                    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $isSecure,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);

        // Set secure session name
        session_name('SBN_MCP_SESSION');
        
        if (!session_start()) {
            throw new \Exception('Failed to start session');
        }

        // Regenerar ID periódicamente
        self::regenerate();

        // Verificar integridad de sesión
        if (!self::verify()) {
            self::destroy();
            return;
        }
    }

    /**
     * Regenerar ID de sesión
     */
    public static function regenerate(): void
    {
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        } elseif (time() - $_SESSION['created'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }

    /**
     * Verificar integridad de sesión.
     * Solo valida User-Agent para no bloquear usuarios detrás de proxies/NAT.
     */
    public static function verify(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }
        // Verificar User Agent (más estable que IP en redes corporativas/móviles)
        if (isset($_SESSION['user_agent']) &&
            $_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
            return false;
        }
        return true;
    }

    /**
     * Establecer valor en sesión
     */
    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Obtener valor de sesión
     */
    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Verificar si existe clave
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Eliminar valor de sesión
     */
    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Destruir sesión
     */
    public static function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];

            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                // Incluir SameSite explícitamente para cumplir CWE-1275
                setcookie(
                    session_name(),
                    '',
                    [
                        'expires'  => time() - 42000,
                        'path'     => $params['path'],
                        'domain'   => $params['domain'],
                        'secure'   => $params['secure'],
                        'httponly' => $params['httponly'],
                        'samesite' => 'Strict',
                    ]
                );
            }

            session_destroy();
        }
    }

    /**
     * Establece un mensaje flash.
     * Los mensajes flash solo persisten por un request.
     */
    public static function setFlash(string $key, $value): void
    {
        $_SESSION['flash'][$key] = $value;
    }

    /**
     * Obtiene y elimina un mensaje flash.
     * Una vez leído, el mensaje es eliminado de la sesión.
     */
    public static function getFlash(string $key): mixed
    {
        $value = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $value;
    }
}
