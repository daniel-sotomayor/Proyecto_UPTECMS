# Documentación del Código - Arquitectura y Estructura

## 1. Arquitectura General

```
SBN_MCP (Sistema de Bienes Nacionales - MCP)
│
├── Patrón: MVC (Modelo-Vista-Controlador)
├── Framework: PHP 8 + PDO (Sin dependencias externas)
├── Base de Datos: MySQL/MariaDB 10.4+
└── Frontend: HTML5 + CSS3 + Vanilla JavaScript
```

## 2. Estructura de Carpetas

```
app/
├── core/               # Núcleo del framework
│   ├── App.php        # Enrutador y bootstrap
│   ├── Router.php     # Motor de rutas
│   ├── Controller.php # Clase base de controladores
│   ├── Database.php   # Abstracción de BD (PDO)
│   ├── Session.php    # Gestión de sesiones
│   ├── Cache.php      # Sistema de caché en memoria
│   ├── Logger.php     # Registro de errores
│   └── Validator.php  # Validaciones
│
├── controllers/        # Controladores (lógica de negocio)
│   ├── AuthController.php           # Autenticación
│   ├── BienController.php           # Gestión de bienes
│   ├── MovimientoController.php     # Movimientos y traslados
│   ├── ReporteController.php        # Reportes
│   ├── AdminController.php          # Gestión de usuarios
│   ├── InventarioMuralController.php# Inventario por área
│   └── ...
│
├── models/            # Modelos (NO USADOS - BD directa)
│
├── views/             # Vistas (Plantillas PHP)
│   ├── bien/          # Vistas de bienes
│   ├── reportes/      # Vistas de reportes
│   ├── auth/          # Vistas de autenticación
│   ├── partials/      # Componentes reutilizables
│   └── layout/        # Layouts base
│
├── helpers/           # Funciones útiles
│   ├── helpers.php         # Helpers generales
│   ├── SecurityHelper.php  # Seguridad (hash, CSRF, validaciones)
│   ├── EmailHelper.php     # Envío de emails
│   ├── BackupHelper.php    # Respaldos
│   └── AuditTrait.php      # Auditoría (trait)
│
└── core/              # Clases principales
    └── HttpExceptions.php # Excepciones HTTP
```

## 3. Flujo de una Solicitud

```
1. Usuario accede a http://localhost/SBN_MCP/public/bienes/nuevo
2. public/index.php inicia la aplicación
3. App.php (bootstrap) registra rutas
4. Router.php encuentra la ruta coincidente
5. BienController->create() se ejecuta
6. Controller renderiza vista con datos
7. Vista se envía al navegador
```

## 4. Componentes Principales

### 4.1 Core\App (Bootstrap)
- Inicia la aplicación
- Registra todas las rutas
- Maneja excepciones
- Controla errores 404/500

**Rutas registradas:**
```php
// Autenticación
GET  /               → AuthController@login
POST /login          → AuthController@authenticate
POST /logout         → AuthController@logout

// Bienes
GET  /bienes         → BienController@index
GET  /bienes/nuevo   → BienController@create
POST /bienes         → BienController@store
GET  /bienes/:id     → BienController@show

// Inventario Mural (por área)
GET  /inventario-mural      → InventarioMuralController@index
GET  /inventario-mural/:id  → InventarioMuralController@area
```

### 4.2 Core\Database (Abstracción de BD)
Capa de abstracción para PDO. No usa ORM.

**Métodos principales:**
```php
Database::query($sql, $params)        // Ejecutar INSERT/UPDATE/DELETE
Database::fetch($sql, $params)        // Obtener 1 registro
Database::fetchAll($sql, $params)     // Obtener varios registros
Database::lastInsertId()              // ID del último INSERT
Database::beginTransaction()          // Transacción START
Database::commit()                    // Confirmar cambios
Database::rollBack()                  // Deshacer cambios
```

**Ejemplo de uso:**
```php
$bien = Database::fetch(
    "SELECT * FROM bienes WHERE id_bien = :id",
    ['id' => 123]
);

Database::query(
    "UPDATE bienes SET nombre = :nombre WHERE id_bien = :id",
    ['nombre' => 'Nuevo nombre', 'id' => 123]
);
```

### 4.3 Core\Controller (Clase Base)
Todos los controladores heredan de esta clase.

**Métodos disponibles:**
```php
$this->getInput($key, $default)       // Obtener parámetro POST/GET
$this->getInputs()                    // Obtener todos los parámetros
$this->sanitizeInput($value)          // Escapar HTML
$this->json($data, $status)           // Retornar JSON
$this->renderWithLayout($view, $data) // Renderizar vista con layout
$this->notFound()                     // Error 404
$this->redirect($url)                 // Redireccionar
$this->requireAuth()                  // Verificar autenticación
$this->requireRole($role)             // Verificar rol
```

### 4.4 Core\Session (Gestión de Sesiones)
Maneja sesiones de usuario.

**Métodos:**
```php
Session::set($key, $value)            // Guardar en sesión
Session::get($key, $default)          // Obtener de sesión
Session::has($key)                    // Verificar existencia
Session::forget($key)                 // Eliminar de sesión
Session::all()                        // Obtener todo
Session::destroy()                    // Cerrar sesión
```

### 4.5 SecurityHelper (Seguridad)
Funciones de seguridad: hashing, validaciones, CSRF.

**Métodos principales:**
```php
// Contraseñas
SecurityHelper::hashPassword($pwd)           // Hash bcrypt
SecurityHelper::verifyPassword($pwd, $hash)  // Verificar
SecurityHelper::isStrongPassword($pwd)       // Validar fortaleza

// CSRF
SecurityHelper::generateCSRFToken()   // Generar token
SecurityHelper::verifyCSRFToken($token) // Verificar token

// Validaciones
SecurityHelper::isValidEmail($email)       // Email válido
SecurityHelper::isValidCedula($cedula)     // Cédula válida
SecurityHelper::isValidDecimal($value)     // Decimal válido
SecurityHelper::sanitizeInput($value)      // Escapar HTML
```

## 5. Flujo de Registro de Bien

```
1. Usuario accede a /bienes/nuevo
   → BienController->create()
   → Obtiene tipos, áreas, estados, personal
   → Renderiza vista bien/create.php

2. Usuario completa formulario multietapa
   → Validaciones en tiempo real (AJAX)
   → Busca duplicados de nro_bien, serial, código_ministerio

3. Usuario haz clic "Guardar Bien"
   → form submit → BienController->store()
   → Validar CSRF
   → Validar datos (validateBienData)
   → Si hay errores → Retornar JSON con errores
   → Si OK → Insertar en BD
   → Generar código interno
   → Subir imágenes (bien, responsable)
   → Registrar en auditoría
   → Registrar movimiento de "incorporación"
   → Retornar JSON success

4. Frontend recibe success
   → Muestra modal de éxito
   → Redirige a /bienes
```

## 6. Modelo de Datos

### Tabla: bienes
```
id_bien              → INT, PK
codigo_sudebip       → VARCHAR, UNIQUE
codigo_interno       → VARCHAR (Pub. 9)
nro_bien_ministerio  → VARCHAR
serial               → VARCHAR
nombre               → VARCHAR
descripcion          → TEXT
marca, modelo, color → VARCHAR
id_estado            → INT, FK
id_tipo              → INT, FK
id_area              → INT, FK
responsable_id       → INT, FK
responsable_cedula   → VARCHAR (NEW)
responsable_foto_path → VARCHAR (NEW)
valor_inicial        → DECIMAL(15,2)
valor_residual       → DECIMAL(15,2)
cin_* (edificio, piso, depto, oficina, posicion)
imagen_path          → VARCHAR
created_at, updated_at → TIMESTAMP
```

### Tabla: movimientos
```
id_movimiento        → INT, PK
bien_id              → INT, FK
tipo_movimiento      → ENUM (incorporacion, traslado, desincorporacion, asignacion)
area_origen_id       → INT, FK
area_destino_id      → INT, FK
usuario_solicita_id  → INT, FK
usuario_aprueba_id   → INT, FK
estado               → ENUM (pendiente, aprobado, rechazado, cancelado)
fecha_solicitud      → TIMESTAMP
fecha_aprobacion     → TIMESTAMP
fecha_ejecucion      → TIMESTAMP
motivo, observaciones → TEXT
created_at, updated_at → TIMESTAMP
```

### Tabla: usuarios
```
id_usuario           → INT, PK
id_rol               → INT, FK
cedula               → VARCHAR, UNIQUE
username             → VARCHAR, UNIQUE
nombre_completo      → VARCHAR
email                → VARCHAR, UNIQUE
password_hash        → VARCHAR
cargo                → VARCHAR
activo               → TINYINT(1)
primer_login         → TINYINT(1)
created_at, updated_at → TIMESTAMP
```

## 7. Validaciones Implementadas

### En Formulario (Frontend - JavaScript)
```javascript
// Nro. de Bien: Solo alfanuméricos, 6-10 caracteres
pattern="[A-Za-z0-9]{6,10}"

// Validación en tiempo real AJAX
/bienes/validar-numero → Verifica duplicados
/bienes/validar-serial → Verifica duplicados
/bienes/validar-codigo-ministerio → Verifica duplicados
```

### En Backend (PHP)
```php
validateBienData($data) {
    // Validar CSRF
    // Validar campos requeridos
    // Validar tipos de dato
    // Validar formato (email, cédula, decimal)
    // Validar duplicados en BD
    // Retornar array de errores (si existen)
}
```

## 8. Autenticación y Autorización

### Autenticación (Login)
```php
AuthController->authenticate():
1. Recibir usuario/clave
2. Buscar usuario en BD
3. Comparar hash (bcrypt)
4. Si OK → Crear sesión
5. Si NO → Mostrar error
6. Redirigir a dashboard o solicitar cambio de clave
```

### Autorización (Permisos por Rol)
```php
// En controladores:
$this->requireAuth()  // Verificar login
$this->requireRole(['administrador', 'gerencia_bn']) // Verificar rol

// En vistas:
<?php if (in_array($rol, ['administrador', 'gerencia_bn'])): ?>
    <!-- Mostrar contenido solo para estos roles -->
<?php endif; ?>
```

## 9. Auditoría

### Registro de Acciones
```php
$this->logAudit('INSERT', 'bienes', $bienId)  // Crear bien
$this->logAudit('UPDATE', 'bienes', $bienId)  // Editar bien
$this->logAudit('DELETE', 'bienes', $bienId)  // Eliminar bien
```

### Tabla: audit_logs (generada automáticamente)
```
id_auditoria
usuario_id
accion (INSERT, UPDATE, DELETE)
tabla
registro_id
cambios_anterior → JSON
cambios_nuevo → JSON
fecha → TIMESTAMP
ip_address
user_agent
```

## 10. Manejo de Errores

### Excepciones Personalizadas
```php
// En app/core/HttpExceptions.php
throw new HttpResponseException($statusCode, $message)

// Ejemplo:
throw new HttpResponseException(404, 'Bien no encontrado')
throw new HttpResponseException(403, 'Acceso denegado')
throw new HttpResponseException(500, 'Error interno del servidor')
```

### Logging
```php
Logger::error($message, $context)     // Errores
Logger::warning($message, $context)   // Advertencias
Logger::info($message, $context)      // Información
Logger::debug($message, $context)     // Debug
```

## 11. Tips de Desarrollo

### Agregar una Nueva Ruta
```php
// En app/core/App.php, método registerRoutes():
$this->router->get('/mis-bienes', ['App\Controllers\BienController', 'myBiens'],
    ['roles' => ['administrador', 'registrador']]
);
```

### Agregar un Nuevo Controlador
```php
// Crear app/controllers/MiControlador.php
namespace App\Controllers;
use App\Core\Controller;

class MiControlador extends Controller {
    public function accion() {
        $this->requireAuth();
        $this->requireRole(['administrador']);
        
        $datos = Database::fetchAll("SELECT * FROM tabla");
        $this->renderWithLayout('mi_vista', ['datos' => $datos]);
    }
}
```

### Usar la Base de Datos
```php
// Insertar
Database::query(
    "INSERT INTO tabla (col1, col2) VALUES (:c1, :c2)",
    ['c1' => 'valor1', 'c2' => 'valor2']
);

// Actualizar
Database::query(
    "UPDATE tabla SET col1 = :c1 WHERE id = :id",
    ['c1' => 'nuevo', 'id' => 123]
);

// Eliminar
Database::query(
    "DELETE FROM tabla WHERE id = :id",
    ['id' => 123]
);

// Transacciones
Database::beginTransaction();
try {
    Database::query(...);
    Database::query(...);
    Database::commit();
} catch (\Exception $e) {
    Database::rollBack();
}
```

---

**Para soporte técnico, consulta el código fuente comentado y los ejemplos en `app/controllers/`.**
