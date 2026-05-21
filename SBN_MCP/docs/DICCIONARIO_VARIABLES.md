# Diccionario de Variables
## Sistema de Gestión de Bienes Nacionales - SBN_MCP

---

## Tabla de Contenidos
1. [Variables PHP - Controladores](#variables-php-controladores)
2. [Variables PHP - Vistas](#variables-php-vistas)
3. [Variables JavaScript](#variables-javascript)
4. [Patrones de Validación](#patrones-de-validación)
5. [Constantes y Configuración](#constantes-y-configuración)

---

## Variables PHP - Controladores

### AuthController

| Variable | Tipo | Descripción |
|----------|------|-------------|
| `$identifier` | string | Cédula del usuario (limpiada, sin espacios) |
| `$password` | string | Contraseña en texto plano |
| `$user` | array | Datos del usuario desde BD |
| `$maxAttempts` | int | Máximo de intentos (5) |
| `$lockoutTime` | int | Tiempo de bloqueo en minutos (15) |

### AdminController

| Variable | Tipo | Descripción |
|----------|------|-------------|
| `$data` | array | Datos POST del formulario |
| `$errors` | array | Errores de validación |
| `$id` | int | ID del usuario a editar/eliminar |
| `$userData` | array | Datos procesados del usuario |
| `$exists` | array/bool | Verificación de existencia (cedula, email, username) |

### BienController

| Variable | Tipo | Descripción |
|----------|------|-------------|
| `$search` | string | Término de búsqueda (sanitizado) |
| `$estado` | int/false | Filtro por estado |
| `$area` | int/false | Filtro por área |
| `$tipo` | int/false | Filtro por tipo de bien |
| `$edificio` | string | Filtro por edificio |
| `$page` | int | Página actual (paginación) |
| `$perPage` | int | Registros por página (20) |
| `$offset` | int | Desplazamiento para SQL |
| `$where` | string | Cláusula WHERE construida |
| `$params` | array | Parámetros para prepared statements |
| `$bienData` | array | Datos del bien a insertar/actualizar |
| `$codigoSudebip` | string | Código generado BN-YYYY-NNNNNN |
| `$codigoInterno` | string | Código según Pub. 9 |

### MovimientoController

| Variable | Tipo | Descripción |
|----------|------|-------------|
| `$movimientoId` | int | ID del movimiento |
| `$estado` | string | Estado del movimiento |
| `$motivoRechazo` | string | Motivo si es rechazado |
| `$origen` | int | Área origen |
| `$destino` | int | Área destino |

### ReporteController

| Variable | Tipo | Descripción |
|----------|------|-------------|
| `$tipo` | string | Tipo de reporte (bm1, bm2, bm3, bm4) |
| `$fechaDesde` | string | Fecha inicio del período |
| `$fechaHasta` | string | Fecha fin del período |
| `$areaId` | int | Filtro por área |
| `$estadoId` | int | Filtro por estado |
| `$datos` | array | Resultados del query |

---

## Variables PHP - Vistas

### Variables Globales en Vistas

| Variable | Tipo | Origen | Descripción |
|----------|------|--------|-------------|
| `$base_url` | string | Controller | URL base de la aplicación |
| `$csrf_token` | string | Controller | Token CSRF para formularios |
| `$user` | array | Session | Datos del usuario logueado |
| `$userRole` | string | Session | Rol del usuario actual |

### auth/login.php

| Variable | Tipo | Descripción |
|----------|------|-------------|
| `$base_url` | string | URL base para enlaces y assets |
| `$csrf_token` | string | Token de seguridad CSRF |

### admin/create_user.php

| Variable | Tipo | Descripción |
|----------|------|-------------|
| `$roles` | array | Lista de roles disponibles |
| `$csrf_token` | string | Token CSRF |
| `$base_url` | string | URL base |

### bien/create.php

| Variable | Tipo | Descripción |
|----------|------|-------------|
| `$tipos` | array | Tipos de bien para select |
| `$estados` | array | Estados para select |
| `$areas` | array | Áreas para select |
| `$personal` | array | Usuarios para responsable |
| `$puedeAsignarNro` | bool | Si puede asignar Nro. de Bien |

### bien/index.php

| Variable | Tipo | Descripción |
|----------|------|-------------|
| `$bienes` | array | Listado de bienes paginados |
| `$estados` | array | Estados para filtro |
| `$areas` | array | Áreas para filtro |
| `$tipos` | array | Tipos para filtro |
| `$edificios` | array | Edificios únicos para filtro |
| `$total` | int | Total de registros |
| `$totalPages` | int | Total de páginas |
| `$page` | int | Página actual |
| `$search` | string | Búsqueda actual |
| `$estado` | int | Filtro estado activo |
| `$area` | int | Filtro área activo |
| `$tipo` | int | Filtro tipo activo |
| `$edificio` | string | Filtro edificio activo |

### reportes/*.php

| Variable | Tipo | Descripción |
|----------|------|-------------|
| `$datos` | array | Datos del reporte |
| `$totales` | array | Totales calculados |
| `$titulo` | string | Título del reporte |

---

## Variables JavaScript

### Variables Globales

| Variable | Tipo | Archivo | Descripción |
|----------|------|---------|-------------|
| `BASE` | string | Todas las vistas | URL base de la aplicación |
| `csrf_token` | string | Formularios | Token CSRF para AJAX |

### auth/login.php

| Variable | Tipo | Descripción |
|----------|------|-------------|
| `form` | HTMLElement | Formulario de login |
| `btn` | HTMLElement | Botón de submit |
| `btnText` | HTMLElement | Texto del botón |
| `btnSpinner` | HTMLElement | Spinner de carga |
| `msgDiv` | HTMLElement | Div de mensajes |
| `cedulaInput` | HTMLInputElement | Campo de cédula |

### admin/create_user.php

| Variable | Tipo | Descripción |
|----------|------|-------------|
| `form` | HTMLElement | Formulario de creación |
| `btn` | HTMLElement | Botón submit |
| `previewTimer` | number | Timer para debounce |
| `SOLO_LETRAS` | RegExp | Patrón: `/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/` |
| `CEDULA` | RegExp | Patrón: `/^\d{6,9}$/` |
| `RULES` | Object | Reglas de contraseña |

### bien/create.php

| Variable | Tipo | Descripción |
|----------|------|-------------|
| `BASE` | string | URL base |
| `serialInput` | HTMLInputElement | Campo serial |
| `marcaInput` | HTMLInputElement | Campo marca |
| `cedulaInput` | HTMLInputElement | Campo cédula |
| `nroBienInput` | HTMLInputElement | Campo Nro. Bien |
| `valorInput` | HTMLInputElement | Campo valor |

### bien/index.php (Listado)

| Variable | Tipo | Descripción |
|----------|------|-------------|
| `searchInput` | HTMLInputElement | Campo de búsqueda |
| `filtros` | Object | Estado de filtros activos |
| `currentPage` | int | Página actual |

---

## Patrones de Validación

### JavaScript - Validaciones Frontend

```javascript
// Cédula (solo números, 6-9 dígitos)
const CEDULA = /^\d{6,9}$/;

// Solo letras (con tildes y ñ)
const SOLO_LETRAS = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;

// Serial (letras, números, guiones, 3-50 caracteres)
const SERIAL = /^[a-zA-Z0-9\-]{3,50}$/;

// Marca (letras, espacios, puntos, 2-50 caracteres)
const MARCA = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s.]{2,50}$/;

// Número de Bien (6-7 dígitos + 3 letras A-F hex)
const NRO_BIEN = /^[0-9]{6,7}[A-F]{3}$/;

// Valor (hasta 10 dígitos enteros, 2 decimales)
const VALOR = /^\d{1,10}(\.\d{1,2})?$/;

// Email básico
const EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
```

### PHP - Validaciones Backend

```php
// Cédula en AuthController y AdminController
if (!preg_match('/^\d{6,9}$/', $identifier)) {
    $errors['cedula'] = 'Debe contener entre 6 y 9 números';
}

// Nombres (solo letras, espacios, tildes)
$soloLetrasPattern = '/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/';

// Número de Bien Ministerio
if (!preg_match('/^\d{6,7}[A-Fa-f0-9]{3}$/', $nroBien)) {
    $errors['nro_bien_ministerio'] = 'Formato inválido (Ej: 1234567ABC)';
}

// Email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Correo inválido';
}

// Valor numérico
$valorInicial = filter_var($data['valor_inicial'], FILTER_VALIDATE_FLOAT);
if ($valorInicial === false || $valorInicial < 0 || $valorInicial > 999999999999.99) {
    $errors['valor_inicial'] = 'Valor inválido';
}
```

---

## Constantes y Configuración

### Configuración de Base de Datos (.env)

| Constante | Valor Default | Descripción |
|-----------|---------------|-------------|
| DB_HOST | localhost | Servidor MySQL |
| DB_PORT | 3306 | Puerto MySQL |
| DB_NAME | hospital_bienes | Nombre de BD |
| DB_USER | root | Usuario MySQL |
| DB_PASS | '' | Contraseña MySQL |
| APP_ENV | development | Entorno (development/production) |
| APP_DEBUG | true | Modo debug |
| APP_KEY | '' | Clave de aplicación |
| SESSION_LIFETIME | 30 | Minutos de sesión |
| MAX_LOGIN_ATTEMPTS | 5 | Intentos máximos login |

### Configuración PHP (config/app.php)

| Constante | Valor | Descripción |
|-----------|-------|-------------|
| APP_NAME | 'SBN_MCP' | Nombre corto |
| APP_VERSION | '1.1.0' | Versión actual |
| DEFAULT_LOCALE | 'es_VE' | Locale español Venezuela |
| TIMEZONE | 'America/Caracas' | Zona horaria |
| CSRF_TOKEN_NAME | 'csrf_token' | Nombre del campo CSRF |
| SESSION_NAME | 'sbn_mcp_session' | Nombre de la cookie |

### Roles y Permisos

| Rol | ID | Permisos |
|-----|-----|----------|
| administrador | 1 | Todos los permisos |
| gerencia_bn | 2 | bienes, movimientos, actas, reportes, auditoria |
| controlador_inventario | 3 | bienes_editar, bienes_ver, movimientos_ver, reportes, auditoria |
| registrador | 4 | bienes_crear, bienes_ver, movimientos_ver |

### Estados de Bienes

| ID | Estado | es_baja | Color |
|----|--------|---------|-------|
| 1 | Operativo | false | #28a745 |
| 2 | Inoperativo | false | #ffc107 |
| 3 | En Resguardo | false | #17a2b8 |
| 4 | Chatarra | true | #dc3545 |
| 5 | Desincorporado | true | #6c757d |

### Estados de Movimientos

| Estado | Descripción |
|--------|-------------|
| pendiente | Esperando aprobación |
| aprobado | Aprobado y ejecutado |
| rechazado | Rechazado |
| cancelado | Cancelado |

### Tipos de Movimiento

| Tipo | Descripción |
|------|-------------|
| incorporacion | Entrada de nuevo bien |
| traslado | Cambio de ubicación |
| desincorporacion | Baja de bien |
| asignacion | Asignación a responsable |

---

## Funciones Helper Globales

### helpers.php

| Función | Parámetros | Retorno | Descripción |
|---------|-----------|---------|-------------|
| `esc()` | string | string | Escape HTML (htmlspecialchars) |
| `formatDate()` | string | string | Formatear fecha (d/m/Y) |
| `formatDateTime()` | string | string | Formatear fecha y hora |
| `formatCurrency()` | float | string | Formatear bolívares |
| `redirect()` | string | void | Redirección HTTP |
| `csrf()` | void | string | Generar token CSRF |
| `validateCsrf()` | string | bool | Validar token CSRF |
| `isAjax()` | void | bool | Detectar petición AJAX |
| `jsonResponse()` | array, int | void | Enviar respuesta JSON |

### SecurityHelper.php

| Función | Parámetros | Retorno | Descripción |
|---------|-----------|---------|-------------|
| `hashPassword()` | string | string | Hash bcrypt con cost 12 |
| `verifyPassword()` | string, string | bool | Verificar hash |
| `generateToken()` | int | string | Token aleatorio |
| `sanitizeInput()` | string | string | Limpiar input |
| `rateLimitCheck()` | string, int, int | bool | Verificar límite de intentos |

---

## Estructura de Datos JSON

### Permisos (roles.permisos)

```json
[
    "usuarios_crear",
    "usuarios_editar",
    "usuarios_eliminar",
    "bienes_crear",
    "bienes_editar",
    "bienes_eliminar",
    "movimientos_crear",
    "movimientos_aprobar",
    "actas_generar",
    "reportes_generar",
    "configuracion",
    "auditoria_ver"
]
```

### Respuesta API Estándar

```json
{
    "success": true,
    "message": "Operación exitosa",
    "data": { ... },
    "redirect": "/ruta"
}
```

### Respuesta de Error

```json
{
    "success": false,
    "error": "Mensaje de error general",
    "errors": {
        "campo1": "Error específico",
        "campo2": "Otro error"
    }
}
```

### Datos de Auditoría

```json
{
    "tabla_afectada": "bienes",
    "registro_id": 123,
    "accion": "UPDATE",
    "datos_anteriores": { "estado": "Operativo" },
    "datos_nuevos": { "estado": "En Resguardo" }
}
```

---

## Notas de Uso

### Convenciones de Nomenclatura

**PHP:**
- Variables: `$nombreVariable` (camelCase)
- Constantes: `CONSTANTE_NAME` (UPPER_SNAKE_CASE)
- Funciones: `nombreFuncion()` (camelCase)
- Clases: `NombreClase` (PascalCase)
- Archivos: `NombreClase.php` (PascalCase)

**JavaScript:**
- Variables: `nombreVariable` (camelCase)
- Constantes: `CONSTANTE` o `nombreConstante` (dependiendo del scope)
- Funciones: `nombreFuncion()` (camelCase)
- IDs de elementos: `nombre-id` (kebab-case)
- Clases CSS: `nombre-clase` (kebab-case)

**Base de Datos:**
- Tablas: `nombre_tabla` (snake_case, plural)
- Columnas: `nombre_columna` (snake_case)
- Claves foráneas: `tabla_id` (snake_case)
