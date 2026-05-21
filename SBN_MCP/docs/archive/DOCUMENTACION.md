# Sistema de Gestión de Bienes Nacionales
## Maternidad Concepción Palacios — Documentación Técnica v1.1.0

---

## Índice
1. [Descripción General](#1-descripción-general)
2. [Requisitos del Sistema](#2-requisitos-del-sistema)
3. [Instalación desde Cero](#3-instalación-desde-cero)
4. [Arquitectura del Proyecto](#4-arquitectura-del-proyecto)
5. [Base de Datos](#5-base-de-datos)
6. [Módulos del Sistema — Estado de Implementación](#6-módulos-del-sistema)
7. [Roles y Permisos](#7-roles-y-permisos)
8. [Codificación de Bienes](#8-codificación-de-bienes)
9. [C.I.N — Código de Ubicación](#9-cin--código-de-ubicación)
10. [API Endpoints — Contratos JSON](#10-api-endpoints)
11. [Seguridad](#11-seguridad)
12. [Estado de Implementación — Inventario Completo](#12-estado-de-implementación)
13. [Funcionalidades Pendientes](#13-funcionalidades-pendientes)
14. [Solución de Problemas](#14-solución-de-problemas)

---

## 1. Descripción General

Sistema web para el control, registro y trazabilidad del inventario de Bienes Nacionales de la Maternidad Concepción Palacios. Desarrollado en PHP nativo con arquitectura MVC propia, MySQL y JavaScript puro, sin frameworks externos.

**Acuerdos de reunión (03/03/2026 y 23/03/2026):**
- Doble codificación obligatoria: Publicación 9 + Ministerio de Salud
- Estados: Operativo, Inoperativo, En Resguardo, Chatarra, Desincorporado
- Frecuencia de inventario: Semestral
- Auditoría obligatoria de todas las operaciones
- C.I.N se omite en el reporte oficial impreso

**Stack tecnológico:**
- PHP 7.4+ (framework MVC propio, sin dependencias externas)
- MySQL 8.0+ / MariaDB 10.4+
- HTML5, CSS3, JavaScript ES6+ (sin librerías externas)
- XAMPP (entorno local de desarrollo)

---

## 2. Requisitos del Sistema

| Componente | Versión mínima |
|------------|---------------|
| PHP        | 7.4           |
| MySQL      | 8.0           |
| Apache     | 2.4           |
| XAMPP      | 8.0+          |

**Extensiones PHP requeridas:** `pdo_mysql`, `mbstring`, `json`, `openssl`

**Navegadores soportados:** Chrome 90+, Firefox 88+, Edge 90+, Safari 14+

---

## 3. Instalación desde Cero

### Paso 1 — Copiar el proyecto
```
C:\xampp\htdocs\SBN_MCP\
```

### Paso 2 — Iniciar servicios XAMPP
1. Abrir XAMPP Control Panel
2. Iniciar **Apache** y **MySQL**
3. Verificar que ambos muestren estado verde

### Paso 3 — Importar la base de datos
1. Abrir `http://localhost/phpmyadmin`
2. Ir a la pestaña **Importar**
3. Seleccionar el archivo `sql/hospital_bienes_DEFINITIVO.sql`
4. Clic en **Continuar**
5. Verificar que aparezca: "Instalación completada exitosamente"

### Paso 4 — Verificar configuración
El archivo `.env` debe contener:
```
DB_HOST=localhost
DB_PORT=3306
DB_NAME=hospital_bienes
DB_USER=root
DB_PASS=
APP_ENV=development
APP_DEBUG=true
```

El archivo `config/database.php` lee automáticamente de `.env`:
```php
'host'     => getenv('DB_HOST') ?: 'localhost',
'port'     => getenv('DB_PORT') ?: '3306',
'database' => getenv('DB_NAME') ?: 'hospital_bienes',
'username' => getenv('DB_USER') ?: 'root',
'password' => getenv('DB_PASS') ?: '',
```

### Paso 5 — Acceder al sistema
- **URL del sistema:** `http://localhost/SBN_MCP/public/`
- **URL de login:** `http://localhost/SBN_MCP/public/login`
- **Usuario:** `admin`
- **Clave:** `Admin_bn`

> **IMPORTANTE:** El sistema pedirá cambiar la clave en el primer login.

### Paso 6 — Verificar funcionamiento
1. Acceder al login → debe mostrar el formulario
2. Ingresar credenciales → debe redirigir al cambio de clave
3. Establecer nueva contraseña → debe acceder al dashboard
4. Verificar que las métricas del dashboard carguen correctamente

---

## 4. Arquitectura del Proyecto

### Estructura de directorios

```
SBN_MCP/
├── app/
│   ├── controllers/              # Controladores (lógica de negocio)
│   │   ├── AuthController.php          — Login, logout, cambio de clave
│   │   ├── AdminController.php         — CRUD de usuarios (solo admin)
│   │   ├── BienController.php          — CRUD de bienes nacionales
│   │   ├── MovimientoController.php    — Movimientos y actas
│   │   ├── DashboardController.php     — Métricas y estadísticas
│   │   ├── ReporteController.php       — Reportes BM-1 a BM-4
│   │   ├── AuditoriaController.php     — Log de auditoría
│   │   └── PublicController.php        — Landing page pública
│   ├── core/                     # Framework MVC propio
│   │   ├── App.php                     — Registro de rutas, punto de entrada
│   │   ├── Router.php                  — Enrutamiento HTTP + middleware
│   │   ├── Controller.php              — Clase base con helpers
│   │   ├── Database.php                — Conexión PDO singleton
│   │   └── Session.php                 — Gestión segura de sesiones
│   ├── helpers/
│   │   ├── helpers.php                 — Funciones globales (esc, formatDate, etc.)
│   │   └── SecurityHelper.php          — Hash, CSRF, validaciones
│   └── views/
│       ├── auth/                       — Login y cambio de clave
│       ├── bien/                       — CRUD de bienes
│       ├── admin/                      — Gestión de usuarios
│       ├── dashboard/                  — Dashboard principal
│       ├── movimiento/                 — Movimientos y actas
│       ├── reportes/                   — Reportes BM-1 a BM-4
│       ├── auditoria/                  — Log de auditoría
│       ├── layout/                     — Header y footer HTML
│       ├── partials/                   — Sidebar reutilizable
│       └── public/                     — Landing page
├── config/
│   ├── app.php                         — Configuración general
│   └── database.php                    — Configuración de BD (lee de .env)
├── public/                             # Directorio público (DOCUMENT_ROOT)
│   ├── index.php                       — Front Controller
│   ├── .htaccess                       — Reescritura Apache + headers seguridad
│   ├── css/
│   │   ├── main.css                    — Estilos landing page
│   │   ├── auth.css                    — Estilos login/cambio clave
│   │   └── app.css                     — Estilos sistema interno
│   └── img/                            — Imágenes (logo, favicon)
├── sql/
│   └── hospital_bienes_DEFINITIVO.sql  — Script completo de instalación
├── docs/
│   └── DOCUMENTACION.md                — Este archivo
├── logs/                               — Logs de errores PHP
├── uploads/                            — Imágenes de bienes
├── vendor/                             — Autoloader Composer (PSR-4)
├── .env                                — Variables de entorno
└── composer.json                       — Configuración Composer
```

### Patrón MVC
El sistema usa un framework MVC propio sin dependencias externas:

| Capa | Responsabilidad | Archivos |
|------|-----------------|----------|
| **Model** | Acceso a datos mediante PDO | `Database.php` con consultas SQL preparadas |
| **View** | Renderizado HTML con variables del controlador | `app/views/**/*.php` |
| **Controller** | Procesamiento de requests, lógica de negocio | `app/controllers/*.php` |
| **Router** | Enrutamiento HTTP y middleware | `App.php` (rutas), `Router.php` (dispatch) |

### Flujo de una petición

```
Navegador
    ↓
public/index.php (Front Controller)
    ↓
App::__construct()
    ├── Database::connect()  → Conexión PDO singleton
    └── Session::start()     → Sesión segura con cookies HttpOnly
    ↓
App::registerRoutes() → 36 rutas definidas
    ↓
Router::dispatch()
    ├── getUri() → Limpia URL (elimina query string, prefijo)
    ├── match()  → Busca ruta por regex
    ├── runMiddleware()
    │   ├── 'auth' → Verifica $_SESSION['user_id']
    │   ├── 'role:...' → Verifica $_SESSION['rol']
    │   └── Redirige a /cambiar-clave si primer_login
    └── execute() → Instancia controller y llama método
        ↓
Controller::action()
    ├── getInputs() / getInput() → Lee $_POST, $_GET o php://input
    ├── verifyCSRFToken() → Valida token CSRF
    ├── Database::query() → Ejecuta SQL preparado
    └── renderWithLayout() / json() → Genera respuesta
        ↓
Respuesta HTML o JSON al navegador
```

### Clases del core

**`App.php`** — Registro centralizado de 36 rutas con middleware. Cada ruta define método HTTP, path, controller y middleware (`auth`, `role:...`).

**`Router.php`** — Convierte paths a regex, ejecuta middleware secuencialmente, instancia controllers y llama métodos con parámetros de ruta.

**`Controller.php`** — Clase abstracta base. Proporciona: `render()`, `renderWithLayout()`, `redirect()`, `json()`, `getInput()`, `verifyCSRFToken()`, `generateCSRFToken()`, `validateRouteId()`, `accessDenied()`, `notFound()`, headers de seguridad automáticos.

**`Database.php`** — Singleton PDO con métodos: `query()`, `fetch()`, `fetchAll()`, `fetchValue()`, `lastInsertId()`, `beginTransaction()`, `commit()`, `rollBack()`.

**`Session.php`** — Wrapper de `$_SESSION` con cookies seguras (HttpOnly, SameSite=Strict), regeneración de ID cada 30 min, verificación de integridad (IP + User-Agent), mensajes flash.

---

## 5. Base de Datos

### Diagrama entidad-relación (conceptual)

```
roles ──1:N── usuarios
areas ──1:N── areas (auto-referencia: area_padre_id)
areas ──1:N── bienes
estados ──1:N── bienes
tipos_bien ──1:N── bienes
usuarios ──1:N── bienes (como responsable)
bienes ──1:N── movimientos
bienes ──1:N── mantenimientos
usuarios ──1:N── movimientos (como solicitante)
usuarios ──1:N── movimientos (como aprobador)
usuarios ──1:N── auditoria
```

### Tablas principales

| Tabla | Registros iniciales | Descripción |
|-------|-------------------|-------------|
| `roles` | 4 | administrador, gerencia_bn, controlador_inventario, registrador |
| `usuarios` | 1 (admin) | Usuarios del sistema con username autogenerado |
| `areas` | 51 | Áreas físicas de la MCP (Principal y Anexo, pisos -1 a 9) |
| `estados` | 5 | Operativo, Inoperativo, En Resguardo, Chatarra, Desincorporado |
| `tipos_bien` | 14 | Clasificaciones oficiales Publicación 9 (01 al 13) |
| `bienes` | 0 | Inventario principal con doble codificación y C.I.N |
| `movimientos` | 0 | Trazabilidad: incorporación, traslado, desincorporación |
| `mantenimientos` | 0 | Mantenimientos preventivos y correctivos |
| `auditoria` | 0 | Log de todas las operaciones del sistema |
| `configuracion` | 7 | Parámetros configurables del sistema |

### Columnas clave de `bienes`

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `codigo_sudebip` | VARCHAR(50) UNIQUE | Código interno: `BN-YYYY-NNNNNN` |
| `codigo_interno` | VARCHAR(50) | Publicación 9: `TIPO-EDIF-PISO-SEQ` |
| `codigo_ministerio` | VARCHAR(50) | Código asignado por el Ministerio |
| `nro_bien_ministerio` | VARCHAR(30) | Número de bien o `S/N` |
| `es_sn` | BOOLEAN | TRUE si no tiene número ministerio |
| `serial` | VARCHAR(100) | Número de serie del fabricante |
| `nombre` | VARCHAR(255) | Nombre descriptivo del bien |
| `descripcion` | TEXT | Descripción detallada |
| `marca` | VARCHAR(100) | Marca del fabricante |
| `modelo` | VARCHAR(100) | Modelo específico |
| `cantidad` | INT | Número de unidades (default 1) |
| `valor_inicial` | DECIMAL(15,2) | Valor en Bolívares |
| `valor_residual` | DECIMAL(15,2) | Valor al final de vida útil |
| `vida_util_anos` | INT | Vida útil en años (default 10) |
| `id_estado` | INT FK | Estado actual del bien |
| `id_tipo` | INT FK | Clasificación Publicación 9 |
| `id_area` | INT FK | Área física donde se ubica |
| `responsable_id` | INT FK | Usuario responsable |
| `cin_edificio` | VARCHAR(50) | C.I.N: Edificio |
| `cin_piso` | VARCHAR(10) | C.I.N: Piso |
| `cin_departamento` | VARCHAR(100) | C.I.N: Departamento |
| `cin_oficina` | VARCHAR(100) | C.I.N: Oficina |
| `cin_posicion` | VARCHAR(20) | C.I.N: Posición |

### Índices de la base de datos

| Tabla | Índice | Columnas |
|-------|--------|----------|
| usuarios | `idx_usuarios_cedula` | cedula |
| usuarios | `idx_usuarios_username` | username |
| usuarios | `idx_usuarios_email` | email |
| usuarios | `idx_usuarios_rol` | id_rol |
| areas | `idx_areas_edificio` | edificio |
| bienes | `idx_bienes_codigo_sudebip` | codigo_sudebip |
| bienes | `idx_bienes_codigo_interno` | codigo_interno |
| bienes | `idx_bienes_nro_ministerio` | nro_bien_ministerio |
| bienes | `idx_bienes_estado` | id_estado |
| bienes | `idx_bienes_area` | id_area |
| bienes | `idx_bienes_tipo` | id_tipo |
| bienes | `idx_bienes_nombre` | nombre |
| movimientos | `idx_movimientos_bien` | bien_id |
| movimientos | `idx_movimientos_tipo` | tipo_movimiento |
| movimientos | `idx_movimientos_estado` | estado |
| movimientos | `idx_movimientos_fecha` | fecha_solicitud |
| mantenimientos | `idx_mantenimientos_bien` | bien_id |
| mantenimientos | `idx_mantenimientos_fecha` | fecha_ejecutada |
| auditoria | `idx_auditoria_tabla` | tabla_afectada |
| auditoria | `idx_auditoria_usuario` | usuario_id |
| auditoria | `idx_auditoria_fecha` | fecha_operacion |

---

## 6. Módulos del Sistema — Estado de Implementación

### 6.1 Autenticación ✅ COMPLETO

| Funcionalidad | Backend | Frontend | Estado |
|---------------|---------|----------|--------|
| Login con cédula o username | ✅ | ✅ | **FUNCIONANDO** |
| Logout | ✅ | ✅ | **FUNCIONANDO** |
| Cambio de contraseña (primer login) | ✅ | ✅ | **FUNCIONANDO** |
| Rate limiting (5 intentos, 15 min bloqueo) | ✅ | ✅ | **FUNCIONANDO** |
| Validación de fortaleza de contraseña | ✅ | ✅ | **FUNCIONANDO** |
| Verificación de usuario activo/bloqueado | ✅ | ✅ | **FUNCIONANDO** |
| Regeneración de ID de sesión | ✅ | — | **FUNCIONANDO** |
| Auditoría de login/logout | ✅ | — | **FUNCIONANDO** |

**Detalles de implementación:**
- `AuthController::showLogin()` — Renderiza formulario de login
- `AuthController::login()` — Valida CSRF, verifica credenciales, crea sesión
- `AuthController::logout()` — Loggea auditoría y destruye sesión
- `AuthController::showCambiarClave()` — Renderiza formulario de cambio
- `AuthController::cambiarClave()` — Valida fortaleza, actualiza hash

### 6.2 Inventario de Bienes ✅ COMPLETO

| Funcionalidad | Backend | Frontend | Estado |
|---------------|---------|----------|--------|
| Listado con filtros (clasificación, estado, edificio, área) | ✅ | ✅ | **FUNCIONANDO** |
| Búsqueda por nombre, código, serial, marca | ✅ | ✅ | **FUNCIONANDO** |
| Registro de nuevo bien | ✅ | ✅ | **FUNCIONANDO** |
| Generación automática de código SUDEBIP | ✅ | — | **FUNCIONANDO** |
| Generación automática de código interno (Pub. 9) | ✅ | ✅ | **FUNCIONANDO** |
| Preview C.I.N en tiempo real | — | ✅ | **FUNCIONANDO** |
| Detalle de bien con historial de movimientos | ✅ | ✅ | **FUNCIONANDO** |
| Edición de bien (todos los campos) | ✅ | ✅ | **FUNCIONANDO** |
| Desincorporación (cambio de estado) | ✅ | ✅ | **FUNCIONANDO** |
| Asignación de Nro. Bien (solo gerencia/admin) | ✅ | ✅ | **FUNCIONANDO** |
| Registro automático de movimiento de incorporación | ✅ | — | **FUNCIONANDO** |
| Auditoría de todas las operaciones | ✅ | — | **FUNCIONANDO** |
| Validación de campos requeridos | ✅ | ✅ | **FUNCIONANDO** |
| Protección CSRF en formularios | ✅ | ✅ | **FUNCIONANDO** |

**Detalles de implementación:**
- `BienController::index()` — Listado con 5 filtros, JOIN con 4 tablas
- `BienController::create()` — Formulario completo con preview de C.I.N
- `BienController::store()` — INSERT con generación de códigos automáticos + movimiento de incorporación
- `BienController::show()` — Detalle con historial completo de movimientos
- `BienController::edit()` — Formulario de edición con datos precargados
- `BienController::update()` — UPDATE masivo de campos
- `BienController::destroy()` — Soft delete (cambio a estado Desincorporado)

### 6.3 Gestión de Usuarios ✅ COMPLETO

| Funcionalidad | Backend | Frontend | Estado |
|---------------|---------|----------|--------|
| Listado de usuarios con estado y rol | ✅ | ✅ | **FUNCIONANDO** |
| Crear usuario con clave temporal | ✅ | ✅ | **FUNCIONANDO** |
| Username autogenerado con desambiguación | ✅ | ✅ | **FUNCIONANDO** |
| Preview de username en tiempo real (AJAX) | ✅ | ✅ | **FUNCIONANDO** |
| Validación de cédula única | ✅ | ✅ | **FUNCIONANDO** |
| Validación de email único | ✅ | ✅ | **FUNCIONANDO** |
| Editar usuario (rol, cargo, email, estado) | ✅ | ✅ | **FUNCIONANDO** |
| Reset de clave temporal | ✅ | ✅ | **FUNCIONANDO** |
| Desactivar usuario (soft delete) | ✅ | ✅ | **FUNCIONANDO** |
| Protección contra auto-eliminación | ✅ | ✅ | **FUNCIONANDO** |
| Validación de fortaleza de contraseña | ✅ | ✅ | **FUNCIONANDO** |
| Auditoría de operaciones CRUD | ✅ | — | **FUNCIONANDO** |

**Detalles de implementación:**
- `AdminController::usuarios()` — Lista con JOIN a roles
- `AdminController::createUser()` — Formulario con preview AJAX
- `AdminController::storeUser()` — INSERT con generación de username
- `AdminController::editUser()` — Formulario de edición
- `AdminController::updateUser()` — UPDATE con posible reset de clave
- `AdminController::deleteUser()` — Soft delete con CSRF
- `AdminController::previewUsername()` — Endpoint AJAX para preview

### 6.4 Dashboard ✅ COMPLETO

| Funcionalidad | Backend | Frontend | Estado |
|---------------|---------|----------|--------|
| Métricas por estado (6 indicadores) | ✅ | ✅ | **FUNCIONANDO** |
| Valor total del inventario | ✅ | ✅ | **FUNCIONANDO** |
| Movimientos del mes actual | ✅ | ✅ | **FUNCIONANDO** |
| Últimos 8 bienes registrados | ✅ | ✅ | **FUNCIONANDO** |
| Distribución por estado (barras progreso) | ✅ | ✅ | **FUNCIONANDO** |
| Últimos 6 movimientos | ✅ | ✅ | **FUNCIONANDO** |
| Distribución por edificio | ✅ | ✅ | **FUNCIONANDO** |

**Detalles de implementación:**
- `DashboardController::index()` — 5 queries optimizadas (métricas consolidadas en 1 query)
- Vista con SVG icons, metric cards, data tables, progress bars

### 6.5 Movimientos y Actas ⚠️ PARCIALMENTE IMPLEMENTADO

| Funcionalidad | Backend | Frontend | Estado |
|---------------|---------|----------|--------|
| Listado de movimientos con filtros | ✅ | ✅ | **FUNCIONANDO** |
| Detalle de movimiento | ✅ | ❌ Vista faltante | **ROTO** |
| Formulario de nueva acta | ✅ | ❌ Vista faltante | **ROTO** |
| Guardar acta (incorporación, traslado, desincorporación) | ✅ | — | **API lista** |
| Aprobar movimiento | ✅ | — | **API lista** |
| Rechazar movimiento | ✅ | — | **API lista** |
| Actualización automática de bien en traslado | ✅ | — | **FUNCIONANDO** |
| Cambio de estado en desincorporación | ✅ | — | **FUNCIONANDO** |
| Transacciones de BD (BEGIN/COMMIT/ROLLBACK) | ✅ | — | **FUNCIONANDO** |
| Auditoría de operaciones | ✅ | — | **FUNCIONANDO** |

**Lo que falta:**
- `app/views/movimiento/show.php` — Vista de detalle del movimiento/acta
- `app/views/movimiento/create.php` — Formulario de creación de nueva acta

**Detalles de implementación:**
- `MovimientoController::index()` — Listado con filtros por tipo, estado, búsqueda
- `MovimientoController::show()` — Controller completo, **vista no existe**
- `MovimientoController::create()` — Controller completo (carga bienes y áreas), **vista no existe**
- `MovimientoController::store()` — INSERT con transacción, actualización de bien
- `MovimientoController::approve()` — UPDATE de estado
- `MovimientoController::reject()` — UPDATE de estado

### 6.6 Reportes ❌ MAYORMENTE NO IMPLEMENTADO

| Funcionalidad | Backend | Frontend | Estado |
|---------------|---------|----------|--------|
| Página de listado de reportes | ✅ | ✅ | **FUNCIONANDO** |
| Reporte BM-1 (Inventario activo) | ⚠️ Query lista | ❌ Vista faltante | **ROTO** |
| Reporte BM-2 (Desincorporados) | ❌ Stub | ❌ Vista faltante | **NO IMPLEMENTADO** |
| Reporte BM-3 (Movimientos período) | ❌ Stub | ❌ Vista faltante | **NO IMPLEMENTADO** |
| Reporte BM-4 (Resumen ejecutivo) | ❌ Stub | ❌ Vista faltante | **NO IMPLEMENTADO** |
| Exportación a PDF | ❌ | ❌ | **NO IMPLEMENTADO** |
| Exportación a Excel/CSV | ❌ | ❌ | **NO IMPLEMENTADO** |

**Lo que falta:**
- `app/views/reportes/bm1.php` — Vista del reporte BM-1
- `app/views/reportes/bm2.php` — Vista del reporte BM-2
- `app/views/reportes/bm3.php` — Vista del reporte BM-3
- `app/views/reportes/bm4.php` — Vista del reporte BM-4
- Lógica de consulta SQL en `ReporteController::bm2()`, `bm3()`, `bm4()`
- Sistema de exportación (PDF, Excel, CSV)

**Detalles:**
- `ReporteController::bm1()` — Tiene query SQL completa (bienes activos con JOIN a 4 tablas) pero la vista no existe
- `ReporteController::bm2()` — Solo establece título, sin query, sin datos
- `ReporteController::bm3()` — Solo establece título, sin query, sin datos
- `ReporteController::bm4()` — Solo establece título, sin query, sin datos

### 6.7 Auditoría ✅ COMPLETO

| Funcionalidad | Backend | Frontend | Estado |
|---------------|---------|----------|--------|
| Listado paginado (50 por página) | ✅ | ✅ | **FUNCIONANDO** |
| Filtro por tabla afectada | ✅ | ✅ | **FUNCIONANDO** |
| Filtro por tipo de acción | ✅ | ✅ | **FUNCIONANDO** |
| Búsqueda por texto | ✅ | ✅ | **FUNCIONANDO** |
| Detalles expandibles (antes/después) | ✅ | ✅ | **FUNCIONANDO** |
| Badges de color por tipo de acción | ✅ | ✅ | **FUNCIONANDO** |
| Paginación con preservación de filtros | ✅ | ✅ | **FUNCIONANDO** |

**Detalles:**
- `AuditoriaController::index()` — Query con paginación, filtros dinámicos, COUNT separado

### 6.8 Páginas Públicas ⚠️ PARCIALMENTE IMPLEMENTADO

| Funcionalidad | Backend | Frontend | Estado |
|---------------|---------|----------|--------|
| Landing page (/) | ✅ | ✅ | **FUNCIONANDO** |
| Página Nosotros (/nosotros) | ✅ | ❌ Vista faltante | **ROTO** |
| Página Servicios (/servicios) | ✅ | ❌ Vista faltante | **ROTO** |
| Página Contacto (/contacto) | ✅ | ❌ Vista faltante | **ROTO** |
| Formulario de contacto (envío SMTP) | ❌ | ❌ | **NO IMPLEMENTADO** |

**Lo que falta:**
- `app/views/public/nosotros.php`
- `app/views/public/servicios.php`
- `app/views/public/contacto.php`
- Implementación de envío de correo SMTP en `PublicController::handleContactForm()`

### 6.9 Mantenimientos ❌ NO IMPLEMENTADO

| Funcionalidad | Estado |
|---------------|--------|
| Tabla `mantenimientos` en BD | ✅ Existe |
| Controlador de mantenimientos | ❌ No existe |
| Vistas de mantenimientos | ❌ No existen |
| Rutas de mantenimientos | ❌ No definidas |
| CRUD de mantenimientos | ❌ No implementado |
| Alertas de mantenimiento próximo | ❌ No implementado |

### 6.10 Vistas de Error ❌ NO IMPLEMENTADO

| Vista | Estado |
|-------|--------|
| `app/views/errors/404.php` | ❌ No existe (se usa HTML fallback) |
| `app/views/errors/403.php` | ❌ No existe (puede causar error PHP) |

### 6.11 Assets Faltantes

| Asset | Estado |
|-------|--------|
| `public/img/logo-mcp.svg` | ❌ No existe |
| `public/img/favicon.ico` | ❌ No existe |
| `public/css/main.css` | ✅ Existe |
| `public/css/auth.css` | ✅ Existe |
| `public/css/app.css` | ✅ Existe |

---

## 7. Roles y Permisos

### Definición de roles

| Rol | Nombre técnico | Descripción |
|-----|---------------|-------------|
| **Administrador** | `administrador` | Acceso total al sistema incluyendo gestión de usuarios |
| **Gerente BN** | `gerencia_bn` | Gestión de bienes, movimientos, reportes y auditoría |
| **Controlador Inventario** | `controlador_inventario` | Edición de bienes, reportes y auditoría |
| **Registrador** | `registrador` | Creación y visualización de bienes, visualización de movimientos |

### Matriz de permisos por ruta

| Ruta | administrador | gerencia_bn | controlador_inventario | registrador |
|------|:---:|:---:|:---:|:---:|
| `/dashboard` | ✅ | ✅ | ✅ | ✅ |
| `/bienes` (listar) | ✅ | ✅ | ✅ | ✅ |
| `/bienes/:id` (ver) | ✅ | ✅ | ✅ | ✅ |
| `/bienes/nuevo` (crear) | ✅ | ✅ | ❌ | ✅ |
| `/bienes/:id/editar` | ✅ | ✅ | ✅ | ❌ |
| `/bienes/:id` (actualizar) | ✅ | ✅ | ✅ | ❌ |
| `/bienes/:id` (desincorporar) | ✅ | ✅ | ❌ | ❌ |
| `/movimientos` (listar) | ✅ | ✅ | ✅ | ✅ |
| `/movimientos/:id` (ver) | ✅ | ✅ | ✅ | ✅ |
| `/movimientos/nuevo` (crear) | ✅ | ✅ | ❌ | ❌ |
| `/movimientos/:id/aprobar` | ✅ | ✅ | ❌ | ❌ |
| `/movimientos/:id/rechazar` | ✅ | ✅ | ❌ | ❌ |
| `/reportes` | ✅ | ✅ | ✅ | ❌ |
| `/reportes/bm1-bm4` | ✅ | ✅ | ✅ | ❌ |
| `/usuarios` (CRUD completo) | ✅ | ❌ | ❌ | ❌ |
| `/auditoria` | ✅ | ✅ | ✅ | ❌ |

### Permisos en base de datos (tabla `roles`)

```json
// administrador
["usuarios_crear","usuarios_editar","usuarios_eliminar","usuarios_ver",
 "bienes_crear","bienes_editar","bienes_eliminar","bienes_ver",
 "movimientos_crear","movimientos_ver","movimientos_aprobar",
 "actas_generar","reportes_generar","configuracion","auditoria_ver"]

// gerencia_bn
["bienes_crear","bienes_editar","bienes_ver",
 "movimientos_crear","movimientos_ver","movimientos_aprobar",
 "actas_generar","reportes_generar","auditoria_ver"]

// controlador_inventario
["bienes_editar","bienes_ver","movimientos_ver",
 "reportes_generar","auditoria_ver"]

// registrador
["bienes_crear","bienes_ver","movimientos_ver"]
```

> **Nota:** Los permisos JSON en la tabla `roles` no se verifican en runtime. El sistema usa exclusivamente el middleware `role:...` en las rutas para autorización.

---

## 8. Codificación de Bienes

### Código SUDEBIP (generado automáticamente)
Formato: `BN-YYYY-NNNNNN`

Ejemplo: `BN-2026-000001`
- `BN` = Bienes Nacionales
- `2026` = Año de registro
- `000001` = Secuencial autoincremental por año

### Código Interno (Publicación 9, generado automáticamente)
Formato: `TIPO-EDIF-PISO-SEQ`

Ejemplo: `06-1-PRI-1-0001`
- `06-1` = Equipos Quirúrgicos y Hospitalarios
- `PRI` = Edificio Principal (3 primeras letras)
- `1` = Piso
- `0001` = Secuencial por prefijo

### Número de Bien (Ministerio)
- Ingresado manualmente por gerencia_bn o administrador
- Formato: 6-7 dígitos + código hexadecimal
- Si no tiene número: se marca `S/N`

### Clasificaciones disponibles

| Código | Nombre | Categoría | Vida útil |
|--------|--------|-----------|-----------|
| 01 | Equipos de Oficina | Oficina | 10 años |
| 02 | Del Alojamiento | Alojamiento | 10 años |
| 03 | Material de Construcción y Taller | Construcción | 15 años |
| 03-1 | Reguladores de Voltaje | Construcción | 5 años |
| 04 | Vehículos | Vehículos | 15 años |
| 05 | Telecomunicaciones | Telecomunicaciones | 8 años |
| 06 | Bienes Hospitalarios | Hospitalario | 10 años |
| 06-1 | Equipos Quirúrgicos y Hospitalarios | Hospitalario | 10 años |
| 06-2 | Equipo Odontológico | Hospitalario | 10 años |
| 07 | Equipos Científicos, Enseñanza y Religiosos | Científico | 10 años |
| 07-2 | Equipos de Enseñanza | Científico | 10 años |
| 07-3 | Capilla | Religioso | 20 años |
| 08 | Cultural y Artístico | Cultural | 20 años |
| 13 | Equipos de Procesamiento de Datos | Tecnología | 5 años |

---

## 9. C.I.N — Código de Ubicación Institucional

Define la ubicación exacta del bien dentro de la institución:

```
Edificio / Piso / Departamento / Oficina / Posición
```

Ejemplo: `Principal / 1 / Quirófano / Sala A / 003`

**Componentes:**
- **Edificio:** Principal o Anexo
- **Piso:** -1 (Sótano), 0 (Planta Baja), 1-9
- **Departamento:** Nombre del área (ej: Quirófano, Sala de Parto)
- **Oficina:** Sub-área específica (opcional)
- **Posición:** Número de ubicación dentro de la oficina (opcional)

> **Nota crítica:** Al generar el reporte oficial para entrega al Ministerio, la columna C.I.N debe ser **omitida** según acuerdo de reunión.

---

## 10. API Endpoints — Contratos JSON

Los endpoints que retornan JSON son utilizados por el frontend mediante `fetch()`.

### Autenticación

#### POST /login
**Request:**
```
Content-Type: application/x-www-form-urlencoded

csrf_token=abc123&cedula=V-12345678&password=Admin123!
```

**Response 200 (éxito):**
```json
{"success": true, "redirect": "/dashboard"}
```

**Response 200 (primer login):**
```json
{"success": true, "redirect": "/cambiar-clave"}
```

**Response 401 (credenciales incorrectas):**
```json
{"error": "Credenciales incorrectas"}
```

**Response 429 (rate limit):**
```json
{"error": "Demasiados intentos fallidos. Espere 15 minutos."}
```

#### POST /cambiar-clave
**Request:**
```
csrf_token=abc123&nueva_clave=NuevaPass1!&confirmar_clave=NuevaPass1!
```

**Response 200:**
```json
{"success": true, "message": "Contraseña actualizada", "redirect": "/dashboard"}
```

**Response 400 (validación):**
```json
{"errors": {"nueva_clave": "Mínimo 8 caracteres"}}
```

### Bienes

#### POST /bienes
**Request:** Formulario multipart con campos del bien.

**Response 200:**
```json
{"success": true, "message": "Bien registrado correctamente", "redirect": "/bienes"}
```

**Response 400 (validación):**
```json
{"errors": {"nombre": "El nombre es requerido", "id_tipo": "La clasificación es requerida"}}
```

**Response 500 (error interno):**
```json
{"error": "Error interno al guardar el registro."}
```

#### PUT /bienes/:id
**Request:** URL-encoded con campos del bien.

**Response 200:**
```json
{"success": true, "redirect": "/bienes/123"}
```

#### DELETE /bienes/:id
**Response 200:**
```json
{"success": true}
```

### Movimientos

#### POST /movimientos
**Request:**
```
csrf_token=abc123&tipo_movimiento=traslado&bien_id=1&area_origen_id=5&area_destino_id=10&motivo=Traslado por reorganización
```

**Response 200:**
```json
{"success": true, "message": "Acta registrada correctamente", "redirect": "/movimientos/45"}
```

#### POST /movimientos/:id/aprobar
**Response 200:**
```json
{"success": true}
```

#### POST /movimientos/:id/rechazar
**Response 200:**
```json
{"success": true}
```

### Usuarios

#### POST /usuarios
**Request:**
```
csrf_token=abc123&primer_nombre=Juan&primer_apellido=Perez&cedula=V-12345678&email=juan@test.com&id_rol=4&clave_temporal=Temp123!
```

**Response 200:**
```json
{"success": true, "username": "jperez", "message": "Usuario creado. Username: jperez", "redirect": "/usuarios"}
```

#### PUT /usuarios/:id
**Request:**
```
csrf_token=abc123&id_rol=2&cargo=Analista&email=nuevo@test.com&activo=1
```

**Response 200:**
```json
{"success": true, "redirect": "/usuarios"}
```

#### DELETE /usuarios/:id
**Request Header:**
```
X-CSRF-Token: abc123
```

**Response 200:**
```json
{"success": true}
```

#### GET /usuarios/preview-username?primer_nombre=Juan&primer_apellido=Perez
**Response 200:**
```json
{"username": "jperez"}
```

---

## 11. Seguridad

### Medidas implementadas

| Categoría | Implementación | Detalle |
|-----------|---------------|---------|
| **CSRF** | Token por petición con rotación | `generateCSRFToken()` en cada form, `verifyCSRFToken()` rota después de validar |
| **XSS** | Escape en todas las salidas | `htmlspecialchars()` en vistas, `esc()` helper |
| **SQL Injection** | PDO prepared statements | 100% de queries usan parámetros nombrados |
| **Sesiones** | Cookies seguras | HttpOnly, SameSite=Strict, regeneración cada 30 min |
| **Contraseñas** | bcrypt cost=12 | `password_hash()` / `password_verify()` |
| **Rate limiting** | Por sesión | 5 intentos fallidos → bloqueo 15 minutos |
| **Headers HTTP** | Seguridad en cada respuesta | X-Frame-Options, X-Content-Type-Options, CSP, Permissions-Policy |
| **Input validation** | Server-side + client-side | Validación en controller antes de BD, validación en JS para UX |
| **Error handling** | Mensajes genéricos | Errores internos loggeados, nunca expuestos al usuario |
| **Variables de entorno** | Externalizadas | Credenciales en `.env`, no en código fuente |
| **Auditoría** | Log completo | IP, user-agent, usuario, tabla, acción, timestamp |

### Configuración de cookies de sesión
```php
session_set_cookie_params([
    'lifetime' => 0,        // Cookie de sesión (se borra al cerrar navegador)
    'path'     => '/',
    'domain'   => '',
    'secure'   => $isSecure, // TRUE en HTTPS
    'httponly'  => true,     // No accesible desde JavaScript
    'samesite'  => 'Strict', // Solo envía en requests del mismo sitio
]);
```

### Headers de seguridad (Apache .htaccess)
```apache
Header always set X-Frame-Options "DENY"
Header always set X-Content-Type-Options "nosniff"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
```

### Headers de seguridad (PHP Controller)
```php
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\'; ...');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
```

---

## 12. Estado de Implementación — Inventario Completo

### Resumen por módulo

| Módulo | Rutas | Backend | Frontend | Estado |
|--------|:---:|:---:|:---:|:---:|
| Autenticación | 5 | 5/5 | 2/2 | ✅ **100%** |
| Bienes (CRUD) | 7 | 7/7 | 4/4 | ✅ **100%** |
| Usuarios (Admin) | 7 | 7/7 | 3/3 | ✅ **100%** |
| Dashboard | 1 | 1/1 | 1/1 | ✅ **100%** |
| Auditoría | 1 | 1/1 | 1/1 | ✅ **100%** |
| Movimientos | 6 | 6/6 | 1/4 | ⚠️ **42%** |
| Reportes | 5 | 1/5 | 1/5 | ❌ **17%** |
| Páginas Públicas | 4 | 4/4 | 1/4 | ⚠️ **33%** |
| Mantenimientos | 0 | 0/0 | 0/0 | ❌ **0%** |
| Vistas de Error | — | — | 0/2 | ❌ **0%** |
| **TOTAL** | **36** | **32/36** | **13/25** | **72%** |

### Desglose detallado de rutas

#### ✅ Rutas funcionando correctamente (22 de 36)

| # | Ruta | Método | Controller | Vista |
|---|------|--------|------------|-------|
| 1 | `GET /` | GET | PublicController::index | public/index.php ✅ |
| 2 | `GET /login` | GET | AuthController::showLogin | auth/login.php ✅ |
| 3 | `POST /login` | POST | AuthController::login | JSON ✅ |
| 4 | `POST /logout` | POST | AuthController::logout | Redirect ✅ |
| 5 | `GET /cambiar-clave` | GET | AuthController::showCambiarClave | auth/cambiar_clave.php ✅ |
| 6 | `POST /cambiar-clave` | POST | AuthController::cambiarClave | JSON ✅ |
| 7 | `GET /dashboard` | GET | DashboardController::index | dashboard/index.php ✅ |
| 8 | `GET /bienes` | GET | BienController::index | bien/index.php ✅ |
| 9 | `GET /bienes/:id` | GET | BienController::show | bien/show.php ✅ |
| 10 | `GET /bienes/nuevo` | GET | BienController::create | bien/create.php ✅ |
| 11 | `POST /bienes` | POST | BienController::store | JSON ✅ |
| 12 | `GET /bienes/:id/editar` | GET | BienController::edit | bien/edit.php ✅ |
| 13 | `PUT /bienes/:id` | PUT | BienController::update | JSON ✅ |
| 14 | `DELETE /bienes/:id` | DELETE | BienController::destroy | JSON ✅ |
| 15 | `GET /movimientos` | GET | MovimientoController::index | movimiento/index.php ✅ |
| 16 | `POST /movimientos` | POST | MovimientoController::store | JSON ✅ |
| 17 | `POST /movimientos/:id/aprobar` | POST | MovimientoController::approve | JSON ✅ |
| 18 | `POST /movimientos/:id/rechazar` | POST | MovimientoController::reject | JSON ✅ |
| 19 | `GET /usuarios` | GET | AdminController::usuarios | admin/usuarios.php ✅ |
| 20 | `GET /usuarios/nuevo` | GET | AdminController::createUser | admin/create_user.php ✅ |
| 21 | `POST /usuarios` | POST | AdminController::storeUser | JSON ✅ |
| 22 | `GET /usuarios/preview-username` | GET | AdminController::previewUsername | JSON ✅ |
| 23 | `GET /usuarios/:id/editar` | GET | AdminController::editUser | admin/edit_user.php ✅ |
| 24 | `PUT /usuarios/:id` | PUT | AdminController::updateUser | JSON ✅ |
| 25 | `DELETE /usuarios/:id` | DELETE | AdminController::deleteUser | JSON ✅ |
| 26 | `GET /reportes` | GET | ReporteController::index | reportes/index.php ✅ |
| 27 | `GET /auditoria` | GET | AuditoriaController::index | auditoria/index.php ✅ |

#### ❌ Rutas con vista faltante (9 de 36)

| # | Ruta | Método | Controller | Vista necesaria |
|---|------|--------|------------|-----------------|
| 28 | `GET /nosotros` | GET | PublicController::nosotros | `public/nosotros.php` ❌ |
| 29 | `GET /servicios` | GET | PublicController::servicios | `public/servicios.php` ❌ |
| 30 | `GET /contacto` | GET | PublicController::contacto | `public/contacto.php` ❌ |
| 31 | `GET /movimientos/:id` | GET | MovimientoController::show | `movimiento/show.php` ❌ |
| 32 | `GET /movimientos/nuevo` | GET | MovimientoController::create | `movimiento/create.php` ❌ |
| 33 | `GET /reportes/bm1` | GET | ReporteController::bm1 | `reportes/bm1.php` ❌ |
| 34 | `GET /reportes/bm2` | GET | ReporteController::bm2 | `reportes/bm2.php` ❌ |
| 35 | `GET /reportes/bm3` | GET | ReporteController::bm3 | `reportes/bm3.php` ❌ |
| 36 | `GET /reportes/bm4` | GET | ReporteController::bm4 | `reportes/bm4.php` ❌ |

---

## 13. Funcionalidades Pendientes

### Prioridad ALTA — Bloquean funcionalidad básica

| # | Funcionalidad | Módulo | Detalle |
|---|--------------|--------|---------|
| 1 | Vista `movimiento/show.php` | Movimientos | Detalle de acta/movimiento. Controller ya pasa datos. |
| 2 | Vista `movimiento/create.php` | Movimientos | Formulario de nueva acta. Controller ya pasa bienes y áreas. |
| 3 | Vista `reportes/bm1.php` | Reportes | Inventario activo. Controller ya ejecuta query. |
| 4 | Vistas de error 404/403 | Core | `errors/404.php` y `errors/403.php` para páginas de error consistentes |

### Prioridad MEDIA — Reportes y páginas públicas

| # | Funcionalidad | Módulo | Detalle |
|---|--------------|--------|---------|
| 5 | Query SQL para `ReporteController::bm2()` | Reportes | Bienes desincorporados y chatarra |
| 6 | Query SQL para `ReporteController::bm3()` | Reportes | Movimientos del período con filtros de fecha |
| 7 | Query SQL para `ReporteController::bm4()` | Reportes | Resumen ejecutivo consolidado |
| 8 | Vista `reportes/bm2.php` | Reportes | Tabla de bienes desincorporados |
| 9 | Vista `reportes/bm3.php` | Reportes | Tabla de movimientos por período |
| 10 | Vista `reportes/bm4.php` | Reportes | Dashboard ejecutivo con métricas |
| 11 | Vista `public/nosotros.php` | Públicas | Página informativa sobre la institución |
| 12 | Vista `public/servicios.php` | Públicas | Página de servicios del sistema |
| 13 | Vista `public/contacto.php` | Públicas | Formulario de contacto |

### Prioridad BAJA — Mejoras funcionales

| # | Funcionalidad | Módulo | Detalle |
|---|--------------|--------|---------|
| 14 | Exportación PDF de reportes | Reportes | Generar PDF con librería (TCPDF/DOMPDF) |
| 15 | Exportación Excel/CSV | Reportes | Exportar datos a hoja de cálculo |
| 16 | Envío de correo SMTP | Contacto | Configuración de mail() o PHPMailer |
| 17 | Módulo de mantenimientos | Mantenimientos | CRUD completo, alertas de mantenimiento próximo |
| 18 | Paginación server-side en bienes | Bienes | Actualmente carga TODOS los registros |
| 19 | Imágenes de bienes (upload) | Bienes | Campo `imagen_path` existe pero no hay upload |
| 20 | Filtros de fecha en reportes BM-3 | Reportes | Selector de rango de fechas |
| 21 | Gráficos en dashboard | Dashboard | Charts.js o similar para visualización |
| 22 | Notificaciones automáticas | Sistema | Alertas de inventario bajo, productos sin movimiento |

### Assets pendientes

| # | Asset | Detalle |
|---|-------|---------|
| 23 | `public/img/logo-mcp.svg` | Logo institucional en formato SVG |
| 24 | `public/img/favicon.ico` | Favicon del sitio |

---

## 14. Solución de Problemas

### "404 - Página no encontrada"
1. Verificar que Apache esté corriendo en XAMPP
2. Verificar que `mod_rewrite` esté habilitado en `httpd.conf`
3. Verificar que `AllowOverride All` esté activo para `htdocs`
4. Acceder exactamente a: `http://localhost/SBN_MCP/public/`

### "Error de conexión a la base de datos"
1. Verificar que MySQL esté corriendo en XAMPP
2. Verificar `.env` (DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS)
3. Verificar que la BD `hospital_bienes` exista en phpMyAdmin

### "Acceso denegado"
El rol del usuario no tiene permiso para esa ruta. Verificar en `app/core/App.php` los roles permitidos para cada ruta.

### "Error: Column not found"
La BD no está actualizada. Reimportar `sql/hospital_bienes_DEFINITIVO.sql`.

### "Call to undefined method" (startBlock/endBlock)
Este error fue corregido en la versión 1.1.0. Actualizar los archivos de vista afectados.

### "Token de seguridad inválido"
El token CSRF expiró o fue rotado. Recargar la página e intentar de nuevo.

### Página en blanco al acceder a movimientos/show o movimientos/nuevo
Las vistas correspondientes aún no han sido creadas. Ver [Funcionalidades Pendientes](#13-funcionalidades-pendientes).

---

## Changelog

### v1.1.0 — 01/04/2026 — Auditoría de seguridad y correcciones

**Correcciones de seguridad (30 correcciones):**
- Eliminadas credenciales hardcodeadas de `config/database.php`
- Corregido session_start() duplicado (session fixation)
- Eliminado almacenamiento de contraseñas en texto plano
- Implementada rotación de tokens CSRF
- Corregido extract() sin EXTR_SKIP
- Agregados headers CSP y Permissions-Policy
- Eliminados datos sensibles de sesión (email, cédula)
- Corregida validación de parámetros :id en rutas
- Eliminada exposición de errores internos al usuario
- Agregado soporte para parseo de body en peticiones PUT/DELETE
- Corregidos métodos inexistentes startBlock/endBlock en vistas
- Corregida sincronización de permisos entre rutas y vistas
- Optimizadas 9 queries del dashboard a 2 queries
- Agregado método fetchValue() faltante en Database
- Corregido bug de parámetros SQL duplicados en AuditoriaController
- Agregada protección CSRF en eliminación de usuarios
- Deshabilitado display_errors en .htaccess

**Documentación:**
- Generada documentación técnica completa
- Inventario de funcionalidades implementadas y pendientes
- Contratos de API documentados

### v1.0.0 — 2026 — Versión inicial

---

*Desarrollado por: Equipo UPTEC-MS — Proyecto Socio-Tecnológico II*
*Versión: 1.1.0 | 01/04/2026*
