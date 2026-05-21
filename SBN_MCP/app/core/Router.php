<?php
namespace App\Core;

use App\Core\HttpResponseException;
use App\Core\HttpRedirectException;

class Router
{
    protected array $routes = [];
    protected array $params = [];

    public function get(string $path, array $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    public function put(string $path, array $handler, array $middleware = []): void
    {
        $this->add('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, array $handler, array $middleware = []): void
    {
        $this->add('DELETE', $path, $handler, $middleware);
    }

    protected function add(string $method, string $path, array $handler, array $middleware): void
    {
        $isStatic = strpos($path, ':') === false;
        $pattern  = preg_replace('/\/:([a-zA-Z_]+)/', '/(?P<$1>[^/]+)', $path);
        $pattern  = '#^' . $pattern . '$#';
        $entry    = ['handler' => $handler, 'middleware' => $middleware];

        if ($isStatic) {
            $this->routes[$method] = [$pattern => $entry] + ($this->routes[$method] ?? []);
        } else {
            $this->routes[$method][$pattern] = $entry;
        }
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = $this->getUri();
        $match  = $this->match($method, $uri);

        if (!$match) {
            $this->notFound();
            return;
        }

        try {
            $this->runMiddleware($match['middleware']);
            $this->execute($match['handler']);
        } catch (HttpRedirectException | HttpResponseException $e) {
            return;
        }
    }

    protected function getUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        $uri = filter_var($uri, FILTER_SANITIZE_URL);
        $uri = str_replace(['../', '..\\'], '', $uri);

        $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
        if ($scriptDir !== '' && strpos($uri, $scriptDir) === 0) {
            $uri = substr($uri, strlen($scriptDir));
        }

        $uri = rtrim($uri, '/');

        return $uri === '' ? '/' : $uri;
    }

    protected function match(string $method, string $uri): ?array
    {
        if (!isset($this->routes[$method])) {
            return null;
        }

        foreach ($this->routes[$method] as $pattern => $route) {
            if (preg_match($pattern, $uri, $matches)) {
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $this->params[$key] = $value;
                    }
                }
                return $route;
            }
        }

        return null;
    }

    protected function runMiddleware(array $middleware): void
    {
        foreach ($middleware as $mw) {
            if ($mw === 'auth') {
                if (!Session::has('user_id')) {
                    $this->redirect('/login');
                }
                if (Session::get('primer_login') && $this->getUri() !== '/cambiar-clave') {
                    $this->redirect('/cambiar-clave');
                }
            }

            if (str_starts_with($mw, 'role:')) {
                $allowed  = explode(',', substr($mw, 5));
                $userRole = Session::get('rol');
                if (!in_array($userRole, $allowed, true)) {
                    http_response_code(403);
                    $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
                    $viewPath = APP_PATH . '/views/errors/403.php';
                    if (file_exists($viewPath)) {
                        require $viewPath;
                    } else {
                        echo '<h1>403 — Acceso denegado</h1>';
                    }
                    throw new HttpResponseException(403);
                }
            }
        }
    }

    protected function execute(array $handler): void
    {
        [$controllerName, $action] = $handler;

        // Validar namespace
        if (!str_starts_with($controllerName, 'App\\Controllers\\')) {
            $this->notFound();
            return;
        }

        // Validar acción: solo letras, números y guión bajo
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $action)) {
            $this->notFound();
            return;
        }

        // Extraer nombre de clase y validar formato seguro
        $parts     = explode('\\', $controllerName);
        $className = end($parts);

        if (!preg_match('/^[A-Za-z][A-Za-z0-9]*Controller$/', $className)) {
            $this->notFound();
            return;
        }

        $controllerFile = APP_PATH . '/controllers/' . $className . '.php';

        if (!file_exists($controllerFile)) {
            $this->notFound();
            return;
        }

        // Verificar que el archivo está dentro del directorio permitido
        $realFile = realpath($controllerFile);
        $realDir  = realpath(APP_PATH . '/controllers/');
        if ($realFile === false || $realDir === false || !str_starts_with($realFile, $realDir . DIRECTORY_SEPARATOR)) {
            $this->notFound();
            return;
        }

        require_once $controllerFile;

        if (!class_exists($controllerName)) {
            $this->notFound();
            return;
        }

        try {
            $controller = new $controllerName();
        } catch (\Throwable $e) {
            Logger::error('Error creating controller: ' . $e->getMessage());
            $this->notFound();
            return;
        }

        if (!method_exists($controller, $action)) {
            $this->notFound();
            return;
        }

        try {
            call_user_func_array([$controller, $action], array_values($this->params));
        } catch (HttpRedirectException | HttpResponseException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Logger::error('Controller error [' . $controllerName . '::' . $action . ']: ' . $e->getMessage());
            http_response_code(500);
            $debug = getenv('APP_DEBUG') === 'true';
            if ($debug) {
                echo '<h1>Error</h1><p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
                echo '<pre>' . htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8') . '</pre>';
            } else {
                echo '<h1>Error del Sistema</h1><p>Ha ocurrido un error interno.</p>';
            }
        }
    }

    public function redirect(string $url): void
    {
        $base    = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
        $safeUrl = '/' . ltrim(str_replace(["\r", "\n"], '', $url), '/');
        header('Location: ' . $base . $safeUrl, true, 302);
        throw new HttpRedirectException();
    }

    protected function notFound(): void
    {
        http_response_code(404);
        $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
        $viewPath = APP_PATH . '/views/errors/404.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            echo '<h1>404 — Página no encontrada</h1>';
        }
        throw new HttpResponseException(404);
    }
}
