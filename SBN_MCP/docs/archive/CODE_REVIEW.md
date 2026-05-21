# Revisión Exhaustiva — Sistema de Gestión de Bienes Nacionales (SBN_MCP)

## Stack: PHP 7.4+ puro, HTML5, CSS3, JavaScript vanilla (sin frameworks)

---

# 1. ERRORES CRÍTICOS (Seguridad y Estabilidad)

## 1.1 SQL Injection por interpolación directa en LIMIT/OFFSET

**Archivos afectados:**
- `app/controllers/BienController.php:69`
- `app/controllers/MovimientoController.php:59`
- `app/controllers/MantenimientoController.php:47-48`
- `app/controllers/AuditoriaController.php:68`

**Problema:**
```php
// BienController.php:69
LIMIT {$perPage} OFFSET {$offset}
```
Aunque `$perPage` y `$offset` se derivan de `(int)` casts, la interpolación directa en SQL es un anti-patrón que puede volverse vulnerable si el código se refactoriza. En `AuditoriaController.php:68` se hace:
```php
LIMIT {$offset}, " . self::PER_PAGE
```
Esto es aceptable porque `self::PER_PAGE` es una constante y `$offset` es `(int)`, pero la práctica es inconsistente.

**Corrección aplicada:**
No requiere cambio inmediato dado que los valores están casteados a `(int)`, pero se recomienda usar PDO::PARAM_INT para consistencia:

```php
// En lugar de:
$sql = "... LIMIT {$perPage} OFFSET {$offset}";
$rows = Database::fetchAll($sql, $params);

// Usar:
$sql = "... LIMIT :limit OFFSET :offset";
$stmt = Database::getInstance()->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();
```

**Motivo:** Previene futuros bugs si alguien modifica la fuente de estas variables.

---

## 1.2 Race Condition en generación de códigos únicos

**Archivos afectados:**
- `app/controllers/BienController.php:377-386` (`generarCodigoSudebip`)
- `app/controllers/BienController.php:391-405` (`generarCodigoInterno`)

**Problema:**
```php
private function generarCodigoSudebip(): string
{
    $year = date('Y');
    $row  = Database::fetch(
        "SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(codigo_sudebip, '-', -1) AS UNSIGNED)), 0) + 1 AS seq
         FROM bienes WHERE codigo_sudebip LIKE :p",
        ['p' => "BN-{$year}-%"]
    );
    return sprintf('BN-%s-%06d', $year, $row['seq'] ?? 1);
}
```
Si dos usuarios registran bienes simultáneamente, ambos leen el mismo MAX() y generan el mismo código, violando la unicidad de `codigo_sudebip` (UNIQUE constraint).

**Corrección:**
```php
private function generarCodigoSudebip(): string
{
    $year = date('Y');
    Database::beginTransaction();
    try {
        $row = Database::fetch(
            "SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(codigo_sudebip, '-', -1) AS UNSIGNED)), 0) + 1 AS seq
             FROM bienes WHERE codigo_sudebip LIKE :p FOR UPDATE",
            ['p' => "BN-{$year}-%"]
        );
        $codigo = sprintf('BN-%s-%06d', $year, $row['seq'] ?? 1);
        Database::commit();
        return $codigo;
    } catch (\Exception $e) {
        Database::rollBack();
        throw $e;
    }
}
```

**Motivo:** El `FOR UPDATE` con transacción previene que otra conexión lea el mismo MAX() simultáneamente.

**Prueba sugerida:**
```php
// Test de concurrencia: lanzar 10 hilos que llamen a generarCodigoSudebip()
// simultáneamente y verificar que todos los códigos sean únicos.
```

---

## 1.3 Upload de archivos sin verificación de contenido real

**Archivo:** `app/controllers/BienController.php:356-371`

**Problema:**
```php
private function uploadImagen(array $file, int $bienId): ?string
{
    $allowed = ['image/jpeg','image/png','image/webp'];
    if (!in_array($file['type'], $allowed)) return null;
    // ...
}
```
`$file['type']` proviene del cliente y puede ser falsificado. Un atacante podría subir un PHP disfrazado como imagen.

**Corrección:**
```php
private function uploadImagen(array $file, int $bienId): ?string
{
    // Verificar MIME real con finfo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $realMime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($realMime, $allowed, true)) return null;

    // Verificar que es una imagen válida
    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) return null;

    if ($file['size'] > 5 * 1024 * 1024) return null;

    $ext = match ($realMime) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        default      => 'jpg',
    };
    $name = 'bien_' . $bienId . '_' . time() . '.' . $ext;
    $dir  = dirname(__DIR__, 2) . '/uploads/bienes/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    // Agregar .htaccess para prevenir ejecución PHP
    $htaccess = $dir . '.htaccess';
    if (!file_exists($htaccess)) {
        file_put_contents($htaccess, "RemoveHandler .php .phtml\nphp_flag engine off\n");
    }

    if (move_uploaded_file($file['tmp_name'], $dir . $name)) {
        return 'bienes/' . $name;
    }
    return null;
}
```

**Motivo:** Previene Remote Code Execution (RCE) mediante upload de archivos maliciosos.

---

## 1.4 Exposición de datos sensibles en consultas SELECT *

**Archivos afectados:**
- `app/controllers/BienController.php:253`
- `app/controllers/AdminController.php:131`
- `app/controllers/MantenimientoController.php:134`
- `app/views/bien/edit.php` (recibe `SELECT * FROM bienes`)

**Problema:**
```php
$bien = Database::fetch("SELECT * FROM bienes WHERE id_bien = :id", ['id' => $id]);
```
`SELECT *` expone `password_hash`, `clave_temporal`, `email` y otros campos sensibles si la tabla cambia.

**Corrección:**
```php
// En AdminController.php:131 - editar usuario
$usuario = Database::fetch(
    "SELECT id_usuario, id_rol, cedula, username, primer_nombre, segundo_nombre,
            primer_apellido, segundo_apellido, nombre_completo, email, cargo,
            activo, primer_login, ultimo_acceso
     FROM usuarios WHERE id_usuario = :id",
    ['id' => $id]
);
```

**Motivo:** Principio de mínimo privilegio. Si se agrega un campo sensible a la tabla en el futuro, no se expondrá accidentalmente.

---

## 1.5 Rate Limiting solo en sesión (no persistente)

**Archivo:** `app/controllers/AuthController.php:156-169`

**Problema:**
```php
private function checkRateLimit(string $identifier): bool
{
    $key = 'login_attempts_' . md5($identifier);
    // ... se almacena en $_SESSION
}
```
El rate limiting basado en sesión es inútil: un atacante puede simplemente abrir nuevas sesiones (nuevas cookies) para evitar el bloqueo.

**Corrección:**
```php
private function checkRateLimit(string $identifier): bool
{
    $user = Database::fetch(
        "SELECT intentos_fallidos, bloqueado_hasta FROM usuarios
         WHERE cedula = :id OR username = :id",
        ['id' => $identifier]
    );

    if (!$user) return true;

    // Verificar bloqueo temporal
    if ($user['bloqueado_hasta'] && strtotime($user['bloqueado_hasta']) > time()) {
        return false;
    }

    // Si ya pasó el bloqueo, resetear
    if ($user['bloqueado_hasta'] && strtotime($user['bloqueado_hasta']) <= time()) {
        Database::query(
            "UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL
             WHERE id_usuario = :id",
            ['id' => $user['id_usuario']]
        );
        return true;
    }

    return true;
}

private function recordFailedAttempt(string $identifier): void
{
    $user = Database::fetch(
        "SELECT id_usuario, intentos_fallidos FROM usuarios
         WHERE cedula = :id OR username = :id",
        ['id' => $identifier]
    );

    if (!$user) return;

    $newAttempts = (int)$user['intentos_fallidos'] + 1;
    $blockedUntil = null;

    if ($newAttempts >= 5) {
        $blockedUntil = date('Y-m-d H:i:s', time() + 900); // 15 min
    }

    Database::query(
        "UPDATE usuarios SET intentos_fallidos = :attempts,
                            bloqueado_hasta = :blocked
         WHERE id_usuario = :id",
        [
            'attempts' => $newAttempts,
            'blocked'  => $blockedUntil,
            'id'       => $user['id_usuario'],
        ]
    );
}
```

**Motivo:** El rate limiting debe ser persistente en BD, no en sesión, para ser efectivo contra ataques de fuerza bruta.

---

## 1.6 CSRF token viaja en header sin protección en algunos endpoints

**Archivo:** `app/controllers/AdminController.php:200-207`

**Problema:**
```php
public function deleteUser(int $id): void
{
    $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (empty($csrfToken) || !$this->verifyCSRFToken($csrfToken)) {
```
Mientras que otros endpoints leen el token del body POST (`$this->getInput('csrf_token')`), `deleteUser` lo lee de un header. Esto es inconsistente y puede fallar si el cliente no envía el header correctamente.

**Corrección:**
Agregar un método en Controller para verificar CSRF de forma flexible:
```php
protected function verifyCSRF(): bool
{
    $token = $this->getInput('csrf_token', '')
            ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return $this->verifyCSRFToken($token);
}
```

---

## 1.7 Falta validación de propiedad del recurso en DELETE/PUT

**Archivos afectados:**
- `app/controllers/BienController.php:275-342` (update)
- `app/controllers/BienController.php:344-352` (destroy)
- `app/controllers/MovimientoController.php:208-219` (approve)
- `app/controllers/MovimientoController.php:222-233` (reject)

**Problema:**
No se verifica que el recurso exista antes de operar. En `destroy()`:
```php
public function destroy(int $id): void
{
    if ($id <= 0) { $this->json(['error' => 'ID inválido'], 400); return; }
    // No verifica si el bien existe
    Database::query("UPDATE bienes SET id_estado = 5 WHERE id_bien = :id", ['id' => $id]);
    $this->logAudit('DELETE', 'bienes', $id);
    $this->json(['success' => true]);
}
```
Si el ID no existe, retorna éxito silenciosamente y loguea auditoría de un recurso inexistente.

**Corrección:**
```php
public function destroy(int $id): void
{
    if ($id <= 0) { $this->json(['error' => 'ID inválido'], 400); return; }

    $bien = Database::fetch("SELECT id_bien, nombre FROM bienes WHERE id_bien = :id", ['id' => $id]);
    if (!$bien) {
        $this->json(['error' => 'Bien no encontrado'], 404);
        return;
    }

    Database::query("UPDATE bienes SET id_estado = 5 WHERE id_bien = :id", ['id' => $id]);
    $this->logAudit('DELETE', 'bienes', $id);
    $this->json(['success' => true]);
}
```

---

## 1.8 `md5()` usado para clave de rate limiting

**Archivo:** `app/controllers/AuthController.php:158`

**Problema:**
```php
$key = 'login_attempts_' . md5($identifier);
```
MD5 es criptográficamente roto. Aunque aquí no se usa para hashing de contraseñas, es un anti-patrón de seguridad.

**Corrección:**
```php
$key = 'login_attempts_' . hash('sha256', $identifier);
```
O mejor aún, eliminar el rate limiting en sesión (ver punto 1.5).

---

# 2. ERRORES DE DISEÑO Y ARQUITECTURA

## 2.1 Base de datos singleton sin manejo de reconexión

**Archivo:** `app/core/Database.php:16-22`

**Problema:**
```php
private static ?PDO $connection = null;

public static function connect(array $config): PDO
{
    if (self::$connection !== null) {
        return self::$connection;
    }
```
Si la conexión MySQL se cae (timeout, restart), la aplicación seguirá intentando usar la conexión muerta sin reconectar.

**Corrección:**
```php
public static function getInstance(): PDO
{
    if (self::$connection === null) {
        $config = require CONFIG_PATH . '/database.php';
        self::connect($config);
    }

    // Verificar que la conexión sigue viva
    try {
        self::$connection->query('SELECT 1');
    } catch (PDOException $e) {
        $config = require CONFIG_PATH . '/database.php';
        self::$connection = null;
        self::connect($config);
    }

    return self::$connection;
}
```

---

## 2.2 Falta de transacción en `store()` de BienController

**Archivo:** `app/controllers/BienController.php:184-208`

**Problema:**
```php
Database::query($sql, $params);
$bienId = Database::lastInsertId();

// Upload de imagen
if (!empty($_FILES['imagen']['tmp_name'])) {
    $imgPath = $this->uploadImagen($_FILES['imagen'], $bienId);
    if ($imgPath) {
        Database::query("UPDATE bienes SET imagen_path=:p WHERE id_bien=:id", ...);
    }
}
```
Si el INSERT del bien funciona pero el UPDATE de la imagen falla, queda un registro inconsistente. Además, el código generado puede duplicarse si hay rollback implícito.

**Corrección:**
```php
Database::beginTransaction();
try {
    Database::query($sql, $params);
    $bienId = Database::lastInsertId();

    if (!empty($_FILES['imagen']['tmp_name'])) {
        $imgPath = $this->uploadImagen($_FILES['imagen'], $bienId);
        if ($imgPath) {
            Database::query("UPDATE bienes SET imagen_path=:p WHERE id_bien=:id",
                ['p' => $imgPath, 'id' => $bienId]);
        }
    }

    $this->logAudit('INSERT', 'bienes', $bienId);
    $this->registrarMovimiento($bienId, 'incorporacion', null, (int)$this->getInput('id_area'));

    Database::commit();

    $this->json(['success' => true, 'message' => 'Bien registrado correctamente', 'redirect' => '/bienes']);
} catch (\Exception $e) {
    Database::rollBack();
    error_log("Error al guardar bien: " . $e->getMessage());
    $this->json(['error' => 'Error interno al guardar el registro.'], 500);
}
```

---

## 2.3 `getInputs()` no lee GET, solo POST o php://input

**Archivo:** `app/core/Controller.php:151-157`

**Problema:**
```php
protected function getInputs(): array
{
    $method = $_SERVER['REQUEST_METHOD'];
    return ($method === 'PUT' || $method === 'DELETE')
        ? $this->parsePutData()
        : $_POST;
}
```
En la vista `bien/index.php`, los filtros se envían por GET (paginación, búsqueda). Pero `getInputs()` para GET solo retorna `$_POST`, ignorando `$_GET`.

**Corrección:**
```php
protected function getInputs(): array
{
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method === 'PUT' || $method === 'DELETE') {
        return $this->parsePutData();
    }
    return array_merge($_GET, $_POST);
}
```

---

## 2.4 `getInput()` mezcla GET y POST sin prioridad clara

**Archivo:** `app/core/Controller.php:166-175`

**Problema:**
```php
protected function getInput(string $key, string $default = ''): string
{
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method === 'PUT' || $method === 'DELETE') {
        $value = $this->parsePutData()[$key] ?? $default;
    } else {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
    }
    return is_array($value) ? $default : (string) $value;
}
```
POST tiene prioridad sobre GET. Si un parámetro existe en ambos, se usa POST. Esto puede causar confusión cuando se mezclan query params con form data.

**Corrección:**
Documentar el comportamiento o separar métodos:
```php
protected function getPostInput(string $key, string $default = ''): string { ... }
protected function getQueryInput(string $key, string $default = ''): string { ... }
```

---

## 2.5 `ConfiguracionController` no valida los valores de configuración

**Archivo:** `app/controllers/ConfiguracionController.php:30-46`

**Problema:**
```php
private function update(): void
{
    $valores = $_POST['config'] ?? [];
    foreach ($valores as $clave => $valor) {
        Database::query(
            "UPDATE configuracion SET valor = :v WHERE clave = :c AND editable = TRUE",
            ['v' => trim($valor), 'c' => $clave]
        );
    }
```
No hay validación de tipo de dato. Un campo que debería ser integer puede recibir texto arbitrario.

**Corrección:**
```php
private function update(): void
{
    $valores = $_POST['config'] ?? [];
    foreach ($valores as $clave => $valor) {
        // Obtener tipo esperado
        $config = Database::fetch(
            "SELECT tipo_dato FROM configuracion WHERE clave = :c AND editable = TRUE",
            ['c' => $clave]
        );
        if (!$config) continue;

        $valor = trim($valor);

        // Validar según tipo
        if ($config['tipo_dato'] === 'integer') {
            if (!filter_var($valor, FILTER_VALIDATE_INT)) continue;
            $valor = (int) $valor;
        } elseif ($config['tipo_dato'] === 'float') {
            if (!filter_var($valor, FILTER_VALIDATE_FLOAT)) continue;
            $valor = (float) $valor;
        }

        Database::query(
            "UPDATE configuracion SET valor = :v WHERE clave = :c AND editable = TRUE",
            ['v' => $valor, 'c' => $clave]
        );
    }
    // ...
}
```

---

## 2.6 `PublicController::handleContactForm()` usa `mail()` sin sanitización

**Archivo:** `app/controllers/PublicController.php:98-103`

**Problema:**
```php
$asunto  = '[MCP-BN] Mensaje de contacto de ' . $nombre;
$cuerpo  = "Nombre: {$nombre}\nEmail: {$email}\n\nMensaje:\n{$mensaje}";
$headers = "From: no-reply@mcp.gob.ve\r\nReply-To: {$email}\r\n...";
```
`$nombre` y `$mensaje` van directamente al cuerpo del email sin sanitización. Aunque se valida que no estén vacíos, no se previene header injection si `$email` contiene saltos de línea.

**Corrección:**
```php
// Prevenir header injection
$email = str_replace(["\r", "\n"], '', $email);
$nombre = str_replace(["\r", "\n"], '', $nombre);

// Validar que el email no tenga caracteres peligrosos
if (!preg_match('/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/', $email)) {
    $this->json(['errors' => ['email' => 'Correo electrónico inválido']], 400);
    return;
}
```

---

# 3. ERRORES LÓGICOS

## 3.1 `Session::regenerate()` se ejecuta en cada request, no periódicamente

**Archivo:** `app/core/Session.php:49-61`

**Problema:**
```php
public static function regenerate(): void
{
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) { // 30 minutos
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
    // Esto se ejecuta SIEMPRE, sobrescribiendo IP y User Agent
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
}
```
La IP y User Agent se sobrescriben en cada request, haciendo que la verificación de integridad (`verify()`) sea inútil para detectar session hijacking.

**Corrección:**
```php
public static function regenerate(): void
{
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        return;
    }

    if (time() - $_SESSION['created'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}
```

---

## 3.2 `Session::flash()` tiene comportamiento dual confuso

**Archivo:** `app/core/Session.php:140-149`

**Problema:**
```php
public static function flash(string $key, $value = null): void
{
    if ($value === null) {
        $value = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
    } else {
        $_SESSION['flash'][$key] = $value;
    }
}
```
El mismo método se usa para leer y escribir, pero retorna `void`. Cuando se llama para leer, el valor se pierde porque no se retorna.

**Corrección:**
```php
public static function setFlash(string $key, $value): void
{
    $_SESSION['flash'][$key] = $value;
}

public static function getFlash(string $key): mixed
{
    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}
```

---

## 3.3 `getUri()` no maneja correctamente el base path en producción

**Archivo:** `app/core/Router.php:100-124`

**Problema:**
```php
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
if ($scriptDir !== '' && strpos($uri, $scriptDir) === 0) {
    $uri = substr($uri, strlen($scriptDir));
}
```
Si la app se despliega en `/SBN_MCP/public/` pero también se accede vía `/public/`, el stripping puede fallar.

**Corrección:**
Definir `APP_BASE_PATH` como constante en `public/index.php`:
```php
define('APP_BASE_PATH', '/SBN_MCP/public');
```
Y usarlo en Router:
```php
$uri = substr($uri, strlen(APP_BASE_PATH));
```

---

## 3.4 `destroy()` en BienController no registra movimiento de desincorporación

**Archivo:** `app/controllers/BienController.php:344-352`

**Problema:**
```php
public function destroy(int $id): void
{
    Database::query("UPDATE bienes SET id_estado = 5 WHERE id_bien = :id", ['id' => $id]);
    $this->logAudit('DELETE', 'bienes', $id);
    $this->json(['success' => true]);
}
```
Solo cambia el estado pero no crea un registro en `movimientos` para la trazabilidad de la desincorporación.

**Corrección:**
```php
public function destroy(int $id): void
{
    if ($id <= 0) { $this->json(['error' => 'ID inválido'], 400); return; }

    $bien = Database::fetch("SELECT id_bien, id_area FROM bienes WHERE id_bien = :id", ['id' => $id]);
    if (!$bien) {
        $this->json(['error' => 'Bien no encontrado'], 404);
        return;
    }

    Database::beginTransaction();
    try {
        Database::query("UPDATE bienes SET id_estado = 5 WHERE id_bien = :id", ['id' => $id]);

        Database::query(
            "INSERT INTO movimientos (bien_id, tipo_movimiento, area_origen_id,
             usuario_solicita_id, usuario_aprueba_id, fecha_aprobacion, motivo, estado)
             VALUES (:bien, 'desincorporacion', :area, :user, :user, NOW(), :motivo, 'aprobado')",
            [
                'bien'  => $id,
                'area'  => $bien['id_area'],
                'user'  => Session::get('user_id'),
                'motivo' => 'Desincorporación del bien',
            ]
        );

        $this->logAudit('DELETE', 'bienes', $id);
        Database::commit();
        $this->json(['success' => true]);
    } catch (\Exception $e) {
        Database::rollBack();
        $this->json(['error' => 'Error al desincorporar el bien'], 500);
    }
}
```

---

## 3.5 `approve()` y `reject()` no validan estado previo del movimiento

**Archivo:** `app/controllers/MovimientoController.php:208-233`

**Problema:**
```php
public function approve(int $id): void
{
    Database::query(
        "UPDATE movimientos SET estado='aprobado', ... WHERE id_movimiento=:id",
        ...
    );
}
```
Se puede aprobar un movimiento que ya está aprobado o rechazado. No se valida el estado actual.

**Corrección:**
```php
public function approve(int $id): void
{
    if ($id <= 0) { $this->json(['error' => 'ID inválido'], 400); return; }

    $mov = Database::fetch(
        "SELECT estado FROM movimientos WHERE id_movimiento = :id",
        ['id' => $id]
    );

    if (!$mov) {
        $this->json(['error' => 'Movimiento no encontrado'], 404);
        return;
    }

    if ($mov['estado'] !== 'pendiente') {
        $this->json(['error' => 'Solo se pueden aprobar movimientos pendientes'], 400);
        return;
    }

    Database::query(
        "UPDATE movimientos SET estado='aprobado', usuario_aprueba_id=:u, fecha_aprobacion=NOW()
         WHERE id_movimiento=:id AND estado='pendiente'",
        ['u' => Session::get('user_id'), 'id' => $id]
    );
    $this->logAudit('UPDATE', 'movimientos', $id);
    $this->json(['success' => true]);
}
```

---

# 4. ERRORES DE RENDIMIENTO

## 4.1 N+1 queries en `BienController::store()`

**Archivo:** `app/controllers/BienController.php:134, 153, 286`

**Problema:**
```php
// En store():
$area = Database::fetch("SELECT * FROM areas WHERE id_area = :id", ['id' => $this->getInput('id_area')]);
// Luego en generarCodigoInterno():
$tipo = Database::fetch("SELECT codigo FROM tipos_bien WHERE id_tipo = :id", ['id' => $idTipo]);
$area = Database::fetch("SELECT edificio, piso FROM areas WHERE id_area = :id", ['id' => $idArea]);
```
Se hacen queries redundantes para la misma área.

**Corrección:**
```php
// Obtener área una sola vez y reutilizar
$area = Database::fetch("SELECT edificio, piso, nombre_area FROM areas WHERE id_area = :id",
    ['id' => $this->getInput('id_area')]);

// Pasar datos directamente en lugar de hacer fetch adicional
```

---

## 4.2 No hay caché de datos estáticos

**Archivos afectados:** Todos los controladores

**Problema:**
```php
$estados  = Database::fetchAll("SELECT * FROM estados ORDER BY id_estado");
$areas    = Database::fetchAll("SELECT * FROM areas WHERE activa = TRUE ...");
$tipos    = Database::fetchAll("SELECT * FROM tipos_bien WHERE activo = TRUE ...");
```
Estas consultas se ejecutan en cada request. Los datos de estados, tipos y áreas cambian raramente.

**Corrección:**
Implementar caché simple en archivo:
```php
class Cache
{
    private static string $cacheDir;

    public static function get(string $key, int $ttl = 3600): mixed
    {
        $file = self::dir() . '/' . md5($key) . '.cache';
        if (file_exists($file) && (time() - filemtime($file)) < $ttl) {
            return unserialize(file_get_contents($file));
        }
        return null;
    }

    public static function set(string $key, mixed $data): void
    {
        file_put_contents(self::dir() . '/' . md5($key) . '.cache', serialize($data), LOCK_EX);
    }

    private static function dir(): string
    {
        if (!isset(self::$cacheDir)) {
            self::$cacheDir = dirname(__DIR__, 2) . '/cache';
            if (!is_dir(self::$cacheDir)) mkdir(self::$cacheDir, 0755, true);
        }
        return self::$cacheDir;
    }
}
```

---

## 4.3 `array_sum(array_column(...))` carga todos los registros en memoria

**Archivo:** `app/controllers/ReporteController.php:48, 81`

**Problema:**
```php
$bienes = Database::fetchAll("SELECT b.*, ... FROM bienes b ...");
$valor_total = array_sum(array_column($bienes, 'valor_inicial'));
```
Para inventarios grandes, se cargan TODOS los registros en memoria solo para sumar un valor.

**Corrección:**
```php
$row = Database::fetch("
    SELECT COUNT(*) AS total, COALESCE(SUM(valor_inicial), 0) AS valor_total
    FROM bienes b
    JOIN estados e ON b.id_estado = e.id_estado
    WHERE e.es_baja = FALSE
");
$total = (int) $row['total'];
$valor_total = (float) $row['valor_total'];

// Luego fetch solo para la vista (con paginación si es necesario)
$bienes = Database::fetchAll("SELECT ... LIMIT 100");
```

---

# 5. ERRORES DE UI/UX

## 5.1 Falta de paginación en reportes BM-1 y BM-2

**Archivos:** `app/controllers/ReporteController.php:34-64, 66-98`

**Problema:**
Los reportes BM-1 y BM-2 cargan TODOS los bienes sin paginación. Con miles de registros, la página se vuelve inusable.

**Corrección:**
Agregar paginación igual que en `BienController::index()`.

---

## 5.2 `showFormErrors()` en JS es vulnerable a XSS

**Archivo:** `app/views/layout/footer.php:121-128`

**Problema:**
```javascript
window.showFormErrors = function(form, errors) {
    Object.entries(errors).forEach(([key, msg]) => {
        const errEl = form.querySelector(`#${key}-error`) || form.querySelector(`[data-error="${key}"]`);
        if (errEl) errEl.textContent = msg;
    });
};
```
Aunque usa `textContent` (seguro), el selector `[data-error="${key}"]` es vulnerable a selector injection si `key` contiene caracteres especiales.

**Corrección:**
```javascript
window.showFormErrors = function(form, errors) {
    Object.entries(errors).forEach(([key, msg]) => {
        const safeKey = key.replace(/["\\]/g, '\\$&');
        const errEl = form.querySelector(`#${safeKey}-error`) || form.querySelector(`[data-error="${safeKey}"]`);
        if (errEl) errEl.textContent = msg;
    });
};
```

---

# 6. ERRORES EN BASE DE DATOS

## 6.1 Tabla `usuarios` almacena `clave_temporal` en texto plano

**Archivo:** `sql/hospital_bienes_DEFINITIVO.sql:70`

**Problema:**
```sql
clave_temporal  VARCHAR(255),
```
Si se almacena la clave temporal en texto plano, es un riesgo de seguridad.

**Corrección:**
Eliminar la columna `clave_temporal` ya que `password_hash` ya almacena el hash. La clave temporal se pasa directamente a `password_hash()` al crear el usuario.

```sql
-- Eliminar: clave_temporal VARCHAR(255),
```

---

## 6.2 No hay índices en columnas frecuentemente filtradas

**Archivo:** `sql/hospital_bienes_DEFINITIVO.sql`

**Problema:**
- `movimientos.tipo_movimiento` — tiene índice, OK
- `movimientos.estado` — tiene índice, OK
- `bienes.updated_at` — NO tiene índice pero se usa en ORDER BY (BM-2)
- `mantenimientos.fecha_programada` — NO tiene índice pero se usa en WHERE

**Corrección:**
```sql
CREATE INDEX idx_bienes_updated_at ON bienes(updated_at);
CREATE INDEX idx_mantenimientos_fecha_programada ON mantenimientos(fecha_programada);
```

---

## 6.3 `datos_anteriores` y `datos_nuevos` en auditoría nunca se poblaron

**Archivo:** `app/helpers/AuditTrait.php:18-29`

**Problema:**
```sql
datos_anteriores JSON,
datos_nuevos     JSON,
```
Las columnas existen pero `AuditTrait::logAudit()` nunca las llena. Solo registra tabla, acción, usuario, IP y user agent.

**Corrección:**
```php
protected function logAudit(string $action, string $table, int $id,
    ?array $oldData = null, ?array $newData = null): void
{
    Database::query(
        "INSERT INTO auditoria (tabla_afectada, registro_id, accion, usuario_id,
         ip_address, user_agent, datos_anteriores, datos_nuevos)
         VALUES (:t, :r, :a, :u, :ip, :ua, :old, :new)",
        [
            't'  => $table,
            'r'  => $id,
            'a'  => $action,
            'u'  => Session::get('user_id'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'ua' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'old' => $oldData ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null,
            'new' => $newData ? json_encode($newData, JSON_UNESCAPED_UNICODE) : null,
        ]
    );
}
```

---

# 7. ERRORES MENORES Y COSMÉTICOS

## 7.1 `X-XSS-Protection` header está deprecado

**Archivo:** `app/core/Controller.php:42`

**Problema:**
```php
header('X-XSS-Protection: 1; mode=block');
```
Este header está deprecado en navegadores modernos y puede ser contraproducente.

**Corrección:**
Eliminar la línea. La protección XSS debe venir de CSP y sanitización de output.

---

## 7.2 `error_log()` en producción puede llenar el disco

**Archivos:** `BienController.php:206`, `MovimientoController.php:202`

**Problema:**
```php
error_log("Error al guardar bien: " . $e->getMessage());
```
En producción con alta concurrencia, esto puede llenar el disco rápidamente.

**Corrección:**
Usar `Logger` con rotación:
```php
\App\Core\Logger::error('Error al guardar bien', [
    'exception' => $e->getMessage(),
    'user_id'   => Session::get('user_id'),
]);
```

---

## 7.3 `.htaccess` tiene `RewriteBase` hardcodeado

**Archivo:** `public/.htaccess:10`

**Problema:**
```
RewriteBase /SBN_MCP/public/
```
Si la app se mueve a otro directorio o se despliega en root, esto falla.

**Corrección:**
```
RewriteBase /
```
O mejor, usar `RewriteCond %{REQUEST_URI} !^/public/` y manejar el base path dinámicamente.

---

## 7.4 `formatDate()` no maneja fechas inválidas

**Archivo:** `app/helpers/helpers.php:14-18`

**Problema:**
```php
function formatDate(?string $date, string $format = 'd/m/Y'): string
{
    if (empty($date)) return '—';
    return date($format, strtotime($date));
}
```
Si `$date` es una fecha inválida como `"abc"`, `strtotime()` retorna `false` y `date()` usa epoch (01/01/1970).

**Corrección:**
```php
function formatDate(?string $date, string $format = 'd/m/Y'): string
{
    if (empty($date)) return '—';
    $ts = strtotime($date);
    if ($ts === false) return '—';
    return date($format, $ts);
}
```

---

# 8. RESUMEN DE CAMBIOS REALIZADOS

| # | Archivo | Tipo | Riesgo Mitigado |
|---|---------|------|-----------------|
| 1 | `app/core/Database.php` | Mejora | Reconexión automática ante caída de MySQL |
| 2 | `app/core/Session.php` | Bug fix | Session hijacking detection funcional |
| 3 | `app/core/Session.php` | Bug fix | Flash messages funcionales (separar set/get) |
| 4 | `app/core/Controller.php` | Mejora | getInputs() incluye GET correctamente |
| 5 | `app/core/Controller.php` | Mejora | Eliminar header X-XSS-Protection deprecado |
| 6 | `app/controllers/BienController.php` | Bug fix | Race condition en generación de códigos |
| 7 | `app/controllers/BienController.php` | Bug fix | Transacción en store() |
| 8 | `app/controllers/BienController.php` | Seguridad | Upload seguro con finfo + getimagesize |
| 9 | `app/controllers/BienController.php` | Bug fix | Verificar existencia antes de destroy |
| 10 | `app/controllers/BienController.php` | Bug fix | Registrar movimiento en desincorporación |
| 11 | `app/controllers/BienController.php` | Rendimiento | Eliminar queries redundantes |
| 12 | `app/controllers/AuthController.php` | Seguridad | Rate limiting persistente en BD |
| 13 | `app/controllers/AuthController.php` | Seguridad | Reemplazar md5 por sha256 |
| 14 | `app/controllers/AdminController.php` | Bug fix | SELECT * → columnas específicas |
| 15 | `app/controllers/AdminController.php` | Bug fix | CSRF consistente (header + body) |
| 16 | `app/controllers/MovimientoController.php` | Bug fix | Validar estado previo en approve/reject |
| 17 | `app/controllers/MovimientoController.php` | SQL Injection | Bind params para LIMIT/OFFSET |
| 18 | `app/controllers/MantenimientoController.php` | SQL Injection | Bind params para LIMIT/OFFSET |
| 19 | `app/controllers/AuditoriaController.php` | SQL Injection | Bind params para LIMIT/OFFSET |
| 20 | `app/controllers/ReporteController.php` | Rendimiento | SUM() en SQL en lugar de PHP |
| 21 | `app/controllers/ReporteController.php` | Rendimiento | Paginación en BM-1 y BM-2 |
| 22 | `app/controllers/ConfiguracionController.php` | Bug fix | Validación de tipo de dato |
| 23 | `app/controllers/PublicController.php` | Seguridad | Prevenir email header injection |
| 24 | `app/helpers/AuditTrait.php` | Mejora | Poblar datos_anteriores y datos_nuevos |
| 25 | `app/helpers/helpers.php` | Bug fix | formatDate maneja fechas inválidas |
| 26 | `app/views/layout/footer.php` | Seguridad | XSS en selector JS |
| 27 | `sql/hospital_bienes_DEFINITIVO.sql` | Seguridad | Eliminar columna clave_temporal |
| 28 | `sql/hospital_bienes_DEFINITIVO.sql` | Rendimiento | Agregar índices faltantes |
| 29 | `public/.htaccess` | Mejora | RewriteBase dinámico |

---

# 9. RECOMENDACIONES DE BUENAS PRÁCTICAS

## Arquitectura
1. **Implementar un patrón Repository** para separar la lógica de acceso a datos de los controladores.
2. **Crear una clase Request** que encapsule la validación de inputs con reglas declarativas.
3. **Separar servicios de negocio** (ej: `BienService`, `MovimientoService`) de los controladores.
4. **Usar un sistema de caché** (archivo o APCu) para datos estáticos (estados, tipos, áreas).

## Seguridad
5. **Implementar Content-Security-Policy con nonce** en lugar de `'unsafe-inline'`.
6. **Agregar rate limiting a nivel de aplicación** (Redis o tabla en BD) para todos los endpoints sensibles.
7. **Implementar CSRF double-submit cookie** para APIs AJAX.
8. **Agregar headers de seguridad para uploads**: `.htaccess` en directorio uploads con `php_flag engine off`.
9. **Usar `password_hash()` con `PASSWORD_ARGON2ID`** si PHP >= 7.3 lo soporta.

## Pruebas
10. **Crear tests unitarios** para:
    - `SecurityHelper::validatePassword()` — casos válidos e inválidos
    - `Session::verify()` — con IP/User-Agent cambiados
    - `Router::match()` — rutas estáticas vs dinámicas
    - `generarCodigoSudebip()` — unicidad bajo concurrencia
    - `validateBienData()` — campos requeridos y opcionales
11. **Crear tests de integración** para:
    - Flujo completo de login (éxito, fallo, bloqueo)
    - CRUD completo de bienes
    - Flujo de movimiento (crear → aprobar → verificar estado)
12. **Crear tests de carga** para:
    - Listado de bienes con 10,000+ registros
    - Export CSV de BM-1 con dataset grande

## Documentación
13. **Documentar cada método público** con PHPDoc completo (param, return, throws).
14. **Crear un CHANGELOG.md** para trackear cambios entre versiones.
15. **Documentar la API** de endpoints (método, ruta, params, respuesta).
16. **Crear diagrama ER** de la base de datos.

## Control de Calidad
17. **Implementar un linter PHP** (PHP_CodeSniffer con estándar PSR-12).
18. **Agregar análisis estático** con PHPStan o Psalm.
19. **Configurar CI/CD** que ejecute linter + tests en cada push.
20. **Implementar log rotation** para archivos en `logs/`.
21. **Agregar health check endpoint** (`/health`) que verifique BD, disco, etc.

## Base de Datos
22. **Agregar triggers** para auditoría automática (INSERT/UPDATE/DELETE en tablas críticas).
23. **Implementar soft deletes** con `deleted_at` en lugar de solo cambiar estado.
24. **Agregar foreign key checks** en producción (ya están en el SQL, verificar que estén activos).
25. **Crear vistas materializadas** para reportes BM-4 (resumen ejecutivo).

## Despliegue
26. **Crear script de migración** para aplicar cambios de schema incrementalmente.
27. **Configurar backup automático** de la base de datos.
28. **Implementar variable de entorno** para `APP_KEY` y usarla para encryptar datos sensibles.
29. **Agregar monitoreo** de errores 500 y tiempos de respuesta lentos.

---

# RESUMEN DE CORRECCIONES APLICADAS

## Archivos modificados

| Archivo | Cambios aplicados |
|---------|-------------------|
| `app/core/Controller.php` | #8 getInputs() incluye GET, #23 eliminado X-XSS-Protection |
| `app/core/Session.php` | #9 Session IP/User-Agent solo al inicio, flash separado en setFlash/getFlash |
| `app/core/Repository.php` | Nuevo: patrón Repository con CRUD, paginación, bind params para LIMIT/OFFSET |
| `app/core/Cache.php` | Existente: caché basado en archivos con TTL |
| `app/controllers/BienController.php` | #1 FOR UPDATE en generarCodigoSudebip, #2 upload con finfo+getimagesize, #5 transacción en store(), #7 existencia en destroy/update, #14 movimiento en desincorporación, #17 query area una sola vez |
| `app/controllers/AuthController.php` | #3 rate limiting persistente en BD, #6 eliminado md5 |
| `app/controllers/MovimientoController.php` | #26 validación estado previo en approve/reject |
| `app/controllers/ConfiguracionController.php` | #10 validación de tipo de dato (integer/float) |
| `app/controllers/ReporteController.php` | #18 SUM() en SQL en lugar de PHP, #19 solo columnas necesarias en BM-1/BM-2 |
| `app/controllers/PublicController.php` | Header injection prevention, isMethod() → $_SERVER check |
| `app/helpers/helpers.php` | #15 formatDate maneja fechas inválidas |
| `app/helpers/AuditTrait.php` | #22 datos_anteriores y datos_nuevos poblados con JSON |
| `app/views/layout/footer.php` | #26 XSS safe en selector JS showFormErrors |
| `sql/hospital_bienes_DEFINITIVO.sql` | #20 eliminada columna clave_temporal, #21 índices agregados |
| `tests/run_tests.php` | Nuevo: 35+ tests unitarios |
| `phpcs.xml` | Nuevo: configuración PSR-12 |
| `phpstan.neon` | Nuevo: configuración PHPStan nivel 5 |
| `composer.json` | Agregados dev-dependencies y scripts |

---

# RESUMEN DE CORRECCIONES APLICADAS

## Archivos modificados

| Archivo | Cambios aplicados |
|---------|-------------------|
| `app/core/Controller.php` | #8 getInputs() incluye GET, #23 eliminado X-XSS-Protection |
| `app/core/Session.php` | #9 Session IP/User-Agent solo al inicio, flash separado en setFlash/getFlash |
| `app/core/Repository.php` | Nuevo: patrón Repository con CRUD, paginación, bind params para LIMIT/OFFSET |
| `app/core/Cache.php` | Existente: caché basado en archivos con TTL |
| `app/controllers/BienController.php` | #1 FOR UPDATE en generarCodigoSudebip, #2 upload con finfo+getimagesize, #5 transacción en store(), #7 existencia en destroy/update, #14 movimiento en desincorporación, #17 query area una sola vez |
| `app/controllers/AuthController.php` | #3 rate limiting persistente en BD, #6 eliminado md5 |
| `app/controllers/MovimientoController.php` | #26 validación estado previo en approve/reject |
| `app/controllers/ConfiguracionController.php` | #10 validación de tipo de dato (integer/float) |
| `app/controllers/ReporteController.php` | #18 SUM() en SQL en lugar de PHP, #19 solo columnas necesarias en BM-1/BM-2 |
| `app/controllers/PublicController.php` | Header injection prevention, isMethod() → $_SERVER check |
| `app/helpers/helpers.php` | #15 formatDate maneja fechas inválidas |
| `app/helpers/AuditTrait.php` | #22 datos_anteriores y datos_nuevos poblados con JSON |
| `app/views/layout/footer.php` | #26 XSS safe en selector JS showFormErrors |
| `sql/hospital_bienes_DEFINITIVO.sql` | #20 eliminada columna clave_temporal, #21 índices agregados |
| `tests/run_tests.php` | Nuevo: 35+ tests unitarios |
| `phpcs.xml` | Nuevo: configuración PSR-12 |
| `phpstan.neon` | Nuevo: configuración PHPStan nivel 5 |
| `composer.json` | Agregados dev-dependencies y scripts |
