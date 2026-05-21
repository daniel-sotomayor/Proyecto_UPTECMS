<?php
/**
 * Clase Base de Controladores
 *
 * Proporciona renderizado de vistas, respuestas JSON, gestión de CSRF,
 * headers de seguridad y helpers de input para todos los controladores.
 *
 * @package App\Core
 */

namespace App\Core;

use App\Core\HttpResponseException;
use App\Core\HttpRedirectException;

abstract class Controller
{
    /** @var array Datos compartidos con la vista. */
    protected array $data = [];

    /** @var string Título de la página actual. */
    protected string $title = '';

    /** @var array Cache del body PUT/DELETE (evita leer php://input múltiples veces). */
    private array $putData = [];

    /** @var bool Indica si ya se parseó php://input. */
    private bool $putDataParsed = false;

    public function __construct()
    {
        $this->setSecurityHeaders();
    }

    /* ── Headers de seguridad ─────────────────────────────────────── */

    /**
     * Establece headers HTTP de seguridad en cada respuesta.
     * Cumple OWASP Secure Headers Project.
     */
    protected function setSecurityHeaders(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob:; font-src 'self'");
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    }

    /* ── Renderizado ──────────────────────────────────────────────── */

    /**
     * Renderiza una vista sin layout (para páginas standalone como login).
     *
     * @param string $view  Ruta relativa a app/views/ sin extensión.
     * @param array  $data  Variables adicionales para la vista.
     */
    protected function render(string $view, array $data = []): void
    {
        $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        $title    = $this->title;
        extract(array_merge($this->data, ['base_url' => $base_url, 'title' => $title], $data), EXTR_SKIP);

        $viewPath = APP_PATH . "/views/{$view}.php";
        if (!file_exists($viewPath)) {
            $this->notFound();
            return;
        }
        require $viewPath;
    }

    /**
     * Renderiza una vista con el layout completo (header + sidebar + footer).
     *
     * @param string $view  Ruta relativa a app/views/ sin extensión.
     * @param array  $data  Variables adicionales para la vista.
     */
    protected function renderWithLayout(string $view, array $data = []): void
    {
        $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        $title    = $this->title;
        $is_app   = true;
        extract(array_merge($this->data, ['base_url' => $base_url, 'title' => $title, 'is_app' => $is_app], $data), EXTR_SKIP);

        require APP_PATH . '/views/layout/header.php';

        $viewPath = APP_PATH . "/views/{$view}.php";
        if (file_exists($viewPath)) {
            require $viewPath;
        }

        require APP_PATH . '/views/layout/footer.php';
    }

    /* ── Respuestas ───────────────────────────────────────────────── */

    /**
     * Envía una respuesta JSON y termina la ejecución.
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        if (headers_sent()) {
            error_log('Headers already sent, cannot send JSON response');
            exit;
        }
        
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Redirige a una URL relativa al base_url de la aplicación.
     */
    protected function redirect(string $url): void
    {
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
        $safeUrl = '/' . ltrim(str_replace(["\r", "\n"], '', $url), '/');
        header('Location: ' . $base . $safeUrl, true, 302);
        throw new HttpRedirectException();
    }

    /* ── Errores HTTP ─────────────────────────────────────────────── */

    /** Muestra la página 403 y termina la ejecución. */
    protected function accessDenied(): void
    {
        http_response_code(403);
        $this->render('errors/403');
        throw new HttpResponseException(403);
    }

    /** Muestra la página 404 y termina la ejecución. */
    protected function notFound(): void
    {
        http_response_code(404);
        $viewPath = APP_PATH . '/views/errors/404.php';
        if (file_exists($viewPath)) {
            $this->render('errors/404');
        } else {
            echo '<h1>404 — Página no encontrada</h1>';
        }
        throw new HttpResponseException(404);
    }

    /* ── Input helpers ────────────────────────────────────────────── */

    /**
     * Obtiene todos los inputs del request actual.
     * Para PUT/DELETE lee php://input una sola vez (cacheado).
     *
     * @return array
     */
    protected function getInputs(): array
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method === 'PUT' || $method === 'DELETE') {
            return $this->parsePutData();
        }
        return array_merge($_GET, $_POST);
    }

    /**
     * Obtiene un input específico como string.
     *
     * @param string $key     Nombre del campo.
     * @param string $default Valor por defecto.
     * @return string
     */
    protected function getInput(string $key, string $default = ''): string
    {
        $inputs = $this->getInputs();
        $value = $inputs[$key] ?? $default;
        return is_array($value) ? $default : (string) $value;
    }

    /**
     * Obtiene un input como entero.
     *
     * @param string $key     Nombre del campo.
     * @param int    $default Valor por defecto.
     * @return int
     */
    protected function getIntInput(string $key, int $default = 0): int
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? null;
        return $value !== null ? (int) $value : $default;
    }

    /**
     * Input sanitizado: trim + htmlspecialchars (anti-XSS para output).
     */
    protected function getSanitizedInput(string $key, string $default = '', bool $htmlEscape = true): string
    {
        $value = $this->getInput($key, $default);
        $value = trim($value);
        return $htmlEscape ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $value;
    }

    /**
     * Input validado para DB con filter_var rules.
     * Rules: 'int', 'float', 'email', 'date:Y-m-d', 'bool'
     */
    protected function getValidatedInput(string $key, string $rule, $default = null): mixed
    {
        $value = $this->getInput($key, (string) $default);
        return match ($rule) {
            'int' => filter_var($value, FILTER_VALIDATE_INT) ?: $default,
            'float' => filter_var($value, FILTER_VALIDATE_FLOAT) ?: $default,
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) ?: $default,
            'date' => \DateTime::createFromFormat('Y-m-d', $value) ? $value : $default,
            'bool' => in_array(strtolower($value), ['1', 'true', 'on', 'yes']),
            default => $value,
        };
    }

    /**
     * Todos inputs sanitizados (para forms completos).
     */
    protected function getInputsSanitized(): array
    {
        $inputs = $this->getInputs();
        $sanitized = [];
        foreach ($inputs as $k => $v) {
            $sanitized[$k] = is_string($v) ? trim($v) : $v;
        }
        return $sanitized;
    }

    /** Lee y cachea php://input una única vez para PUT/DELETE. */
    private function parsePutData(): array
    {
        if (!$this->putDataParsed) {
            parse_str(file_get_contents('php://input'), $this->putData);
            $this->putDataParsed = true;
        }
        return $this->putData;
    }

    /* ── CSRF ─────────────────────────────────────────────────────── */

    /**
     * Genera y almacena un nuevo token CSRF en sesión.
     * Se rota en cada generación (token-per-request).
     *
     * @return string Token hexadecimal de 64 caracteres.
     */
    protected function generateCSRFToken(): string
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }

    /**
     * Verifica CSRF de forma flexible (Body o Header).
     * Esencial para peticiones fetch/AJAX consistentes.
     */
    protected function verifyCSRF(): bool
    {
        // Obtener token del request
        $token = $this->getInput('csrf_token') 
                 ?: ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        
        if (empty($token)) {
            return false;
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        return $this->verifyCSRFToken($token);
    }

    /**
     * Verifica el token CSRF usando comparación de tiempo constante.
     * NO rota el token por defecto para evitar problemas con formularios.
     *
     * @param string $token Token recibido del cliente.
     * @param bool $rotate Si debe rotar el token después de verificar (default: false)
     * @return bool
     */
    protected function verifyCSRFToken(string $token, bool $rotate = false): bool
    {
        $valid = isset($_SESSION['csrf_token']) &&
                 hash_equals($_SESSION['csrf_token'], $token);
        if ($valid && $rotate) {
            $this->generateCSRFToken();
        }
        return $valid;
    }

    /* ── Utilidades ───────────────────────────────────────────────── */

    /**
     * Sanitiza input para prevenir XSS y ataques de inyección
     */
    protected function sanitizeInput(string $input): string
    {
        $input = trim($input);
        $input = strip_tags($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        return $input;
    }

    /**
     * Verifica si la petición es AJAX (XMLHttpRequest).
     *
     * @return bool
     */
    protected function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Valida que un parámetro de ruta sea un entero positivo.
     * Llama a notFound() si no lo es.
     *
     * @param mixed $id Valor del parámetro de ruta.
     * @return int
     */
    protected function validateRouteId(mixed $id): int
    {
        $intId = (int) $id;
        if ($intId <= 0) {
            $this->notFound();
        }
        return $intId;
    }
}
