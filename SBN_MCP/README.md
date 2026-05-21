# Sistema de Gestion de Bienes Nacionales

**Maternidad Concepcion Palacios** — UPTEC-MS

Sistema web para el control, registro y trazabilidad del inventario de Bienes Nacionales de la Maternidad Concepcion Palacios. Cumple con la Ley Organica de Bienes Publicos (LOBIP), Gaceta Oficial 43.077 y las normativas de codificacion del Ministerio de Salud.

---

## Stack tecnologico

- **Backend:** PHP 7.4+ (framework MVC propio)
- **Base de datos:** MySQL 8.0 / MariaDB 10.4+
- **Frontend:** HTML5, CSS3, JavaScript ES6+ (sin frameworks)
- **Servidor:** Apache 2.4 (XAMPP/LAMP)

---

## Requisitos previos

| Componente | Version minima |
|------------|---------------|
| XAMPP      | 8.0+          |
| PHP        | 7.4           |
| MySQL      | 8.0           |
| Apache     | 2.4           |
| Composer   | 2.0+          |
| Navegador  | Chrome 90+, Firefox 88+, Edge 90+ |

**Extensiones PHP requeridas:** `pdo_mysql`, `mbstring`, `json`, `openssl`, `gd`

---

## Instalacion rapida

### Windows (XAMPP)

#### 1. Instalar dependencias de PHP

```bash
cd C:\xampp\htdocs\SBN_MCP
composer update
```

> **Nota importante:** Si `composer install` falla con errores del lock file, ejecutar `composer update` para regenerar las dependencias.

#### 2. Copiar el proyecto

Colocar la carpeta del proyecto en el directorio de XAMPP:

```
C:\xampp\htdocs\SBN_MCP\
```

#### 3. Iniciar servicios

Abrir **XAMPP Control Panel** e iniciar:
- Apache
- MySQL

#### 4. Importar la base de datos

1. Abrir `http://localhost/phpmyadmin`
2. Ir a la pestaña **Importar**
3. Seleccionar el archivo `sql/hospital_bienes_DEFINITIVO.sql`
4. Hacer clic en **Continuar**

#### 5. Configurar permisos de carpetas (Windows)

Asegurar que las siguientes carpetas tengan permisos de escritura:
- `uploads/` - Para imágenes de bienes
- `logs/` - Para logs de errores

En Windows, estas carpetas ya deberían funcionar por defecto.

#### 6. Acceder al sistema

```
URL:      http://localhost/SBN_MCP/public/
Usuario:  admin
Clave:    Admin_bn
```

---

### Linux (XAMPP/LAMP)

#### 1. Instalar XAMPP en Linux

```bash
# Descargar XAMPP desde https://www.apachefriends.org/
sudo chmod +x xampp-linux-x64-8.x.x-installer.run
sudo ./xampp-linux-x64-8.x.x-installer.run

# Iniciar XAMPP
sudo /opt/lampp/lampp start
```

#### 2. Ubicar el proyecto

```bash
# Copiar proyecto al directorio htdocs de XAMPP
sudo cp -r SBN_MCP /opt/lampp/htdocs/

# O usar enlace simbólico
sudo ln -s /ruta/a/tu/proyecto/SBN_MCP /opt/lampp/htdocs/SBN_MCP
```

#### 3. Configurar permisos (IMPORTANTE)

```bash
cd /opt/lampp/htdocs/SBN_MCP

# Permisos para carpetas de escritura
sudo chmod -R 777 uploads/
sudo chmod -R 777 logs/
sudo chmod -R 777 vendor/

# Propietario (opcional, para evitar problemas con Composer)
sudo chown -R $USER:$USER /opt/lampp/htdocs/SBN_MCP
```

#### 4. Instalar Composer (si no está instalado)

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer
```

#### 5. Instalar dependencias

```bash
cd /opt/lampp/htdocs/SBN_MCP
composer update
```

#### 6. Configurar Apache (si es necesario)

Si encuentras errores 404 con URLs amigables, verificar que `mod_rewrite` esté habilitado:

```bash
sudo /opt/lampp/xampp reloadapache
```

Verificar que el archivo `public/.htaccess` exista y tenga este contenido:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

#### 7. Importar base de datos

1. Abrir `http://localhost/phpmyadmin`
2. Importar `sql/hospital_bienes_DEFINITIVO.sql`

#### 8. Acceder al sistema

```
URL:      http://localhost/SBN_MCP/public/
Usuario:  admin
Clave:    Admin_bn
```

---

### Configuracion de variables de entorno

El archivo `.env` ya viene preconfigurado para XAMPP:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=hospital_bienes
DB_USER=root
DB_PASS=
APP_ENV=development
APP_DEBUG=true
APP_TIMEZONE=America/Caracas
```

**Para Linux con contraseña de MySQL:**
```env
DB_PASS=tu_contraseña_mysql
```

> El sistema solicitara cambiar la clave en el primer inicio de sesion.

---

## Solucion de problemas comunes

### Error: "The lock file is not up to date"

**Solución:**
```bash
composer update
```

### Error 404 - Pagina no encontrada (Linux)

1. Verificar que `mod_rewrite` esté habilitado en Apache:
   ```bash
   sudo /opt/lampp/xampp reloadapache
   ```

2. Verificar archivo `.htaccess` en `public/.htaccess`

3. En algunas distribuciones Linux, editar `httpd.conf`:
   ```bash
   sudo nano /opt/lampp/etc/httpd.conf
   ```
   Buscar y cambiar:
   ```apache
   AllowOverride None
   ```
   a:
   ```apache
   AllowOverride All
   ```

### Error de conexion a base de datos (Linux)

Verificar que MySQL esté corriendo:
```bash
sudo /opt/lampp/xampp startmysql
```

Verificar permisos de usuario MySQL:
```bash
sudo /opt/lampp/bin/mysql -u root
```

### Problemas de permisos en uploads/ (Linux)

```bash
sudo chmod -R 777 /opt/lampp/htdocs/SBN_MCP/uploads/
sudo chown -R daemon:daemon /opt/lampp/htdocs/SBN_MCP/uploads/
```

### Error 500 - Error interno del servidor

1. Verificar logs en `logs/` o `/opt/lampp/logs/error_log`
2. Asegurar que PHP tenga las extensiones requeridas:
   ```bash
   /opt/lampp/bin/php -m | grep -E "pdo|mbstring|json|openssl|gd"
   ```

---

## Estructura del proyecto

```
SBN_MCP/
├── app/
│   ├── controllers/              # Controladores (logica de negocio)
│   │   ├── AuthController.php          Login, logout, cambio de clave
│   │   ├── AdminController.php         CRUD de usuarios (solo admin)
│   │   ├── BienController.php          CRUD de bienes nacionales
│   │   ├── MovimientoController.php    Movimientos y actas
│   │   ├── DashboardController.php     Metricas y estadisticas
│   │   ├── ReporteController.php       Reportes BM-1 a BM-4
│   │   ├── AuditoriaController.php     Log de auditoria
│   │   └── PublicController.php        Landing page publica
│   ├── core/                     # Framework MVC propio
│   │   ├── App.php                     Registro de rutas
│   │   ├── Router.php                  Enrutamiento HTTP + middleware
│   │   ├── Controller.php              Clase base de controladores
│   │   ├── Database.php                Conexion PDO singleton
│   │   └── Session.php                 Gestion segura de sesiones
│   ├── helpers/
│   │   ├── helpers.php                 Funciones globales
│   │   └── SecurityHelper.php          Utilidades de seguridad
│   └── views/
│       ├── auth/                       Login y cambio de clave
│       ├── bien/                       CRUD de bienes
│       ├── admin/                      Gestion de usuarios
│       ├── dashboard/                  Dashboard principal
│       ├── movimiento/                 Movimientos y actas
│       ├── reportes/                   Reportes BM-1 a BM-4
│       ├── auditoria/                  Log de auditoria
│       ├── errors/                     Paginas de error 404/403
│       ├── layout/                     Header y footer HTML
│       ├── partials/                   Sidebar reutilizable
│       └── public/                     Landing page
├── config/
│   ├── app.php                         Configuracion general
│   └── database.php                    Configuracion de BD
├── public/                             Document root de Apache
│   ├── index.php                       Front Controller (punto de entrada)
│   ├── .htaccess                       Reescritura Apache + seguridad
│   ├── css/
│   │   ├── main.css                    Estilos landing page
│   │   ├── auth.css                    Estilos login/cambio clave
│   │   └── app.css                     Estilos sistema interno
│   └── img/                            Logo, favicon
├── sql/
│   └── hospital_bienes_DEFINITIVO.sql  Script completo de instalacion
├── docs/
│   ├── DOCUMENTACION.md                Documentacion tecnica completa
│   └── CHECKLIST_PENDIENTES.md         Tareas pendientes de implementacion
├── logs/                               Logs de errores PHP
├── uploads/                            Imagenes de bienes
├── vendor/                             Autoloader Composer
├── .env                                Variables de entorno
├── .gitignore                          Archivos ignorados por Git
├── composer.json                       Configuracion Composer
└── README.md                           Este archivo
```

---

## Modulos del sistema

| Modulo | Estado | Descripcion |
|--------|--------|-------------|
| Autenticacion | Listo | Login, logout, cambio de clave, rate limiting |
| Inventario de Bienes | Listo | CRUD completo con filtros, codificacion automatica, C.I.N |
| Gestion de Usuarios | Listo | CRUD completo con username autogenerado |
| Dashboard | Listo | 8 metricas, distribucion por estado/edificio |
| Auditoria | Listo | Log paginado con filtros |
| Movimientos | Parcial | Listado funcional; faltan vistas de detalle y creacion |
| Reportes | Parcial | Solo listado de reportes; falta implementacion BM-1 a BM-4 |
| Paginas publicas | Parcial | Solo landing page; faltan nosotros, servicios, contacto |
| Mantenimientos | Pendiente | Tabla existe en BD, sin implementacion |

---

## Roles y permisos

| Rol | Acceso |
|-----|--------|
| **administrador** | Acceso total: usuarios, bienes, movimientos, reportes, auditoria, configuracion |
| **gerencia_bn** | Bienes, movimientos, reportes, auditoria (sin gestion de usuarios) |
| **controlador_inventario** | Edicion de bienes, reportes, auditoria (sin crear usuarios ni movimientos) |
| **registrador** | Crear y ver bienes, ver movimientos (sin funciones administrativas) |

---

## Codificacion de bienes

### Codigo interno (Publicacion 9)

Formato: `TIPO-EDIF-PISO-SEQ`

Ejemplo: `06-1-PRI-1-0001`
- `06-1` — Equipos Quirurgicos y Hospitalarios
- `PRI` — Edificio Principal
- `1` — Piso 1
- `0001` — Secuencial

### Codigo SUDEBIP (automatico)

Formato: `BN-YYYY-NNNNNN`

Ejemplo: `BN-2026-000001`

### C.I.N — Codigo de Ubicacion Institucional

```
Edificio / Piso / Departamento / Oficina / Posicion
```

Ejemplo: `Principal / 1 / Quirofano / Sala A / 003`

---

## Endpoints de API

El sistema usa peticiones `fetch()` para operaciones AJAX. Los endpoints principales retornan JSON:

| Metodo | Ruta | Descripcion |
|--------|------|-------------|
| POST | `/login` | Iniciar sesion |
| POST | `/logout` | Cerrar sesion |
| POST | `/cambiar-clave` | Cambiar contrasena |
| POST | `/bienes` | Crear bien |
| PUT | `/bienes/:id` | Actualizar bien |
| DELETE | `/bienes/:id` | Desincorporar bien |
| POST | `/movimientos` | Crear acta |
| POST | `/movimientos/:id/aprobar` | Aprobar movimiento |
| POST | `/movimientos/:id/rechazar` | Rechazar movimiento |
| POST | `/usuarios` | Crear usuario |
| PUT | `/usuarios/:id` | Actualizar usuario |
| DELETE | `/usuarios/:id` | Desactivar usuario |
| GET | `/usuarios/preview-username` | Preview username (AJAX) |

Todas las peticiones POST/PUT/DELETE requieren token CSRF en el campo `csrf_token` o header `X-CSRF-Token`.

---

## Seguridad

- **CSRF:** Token unico por peticion con rotacion automatica
- **XSS:** Todas las salidas HTML escapadas con `htmlspecialchars()`
- **SQL Injection:** Consultas preparadas PDO en el 100% de operaciones
- **Sesiones:** Cookies HttpOnly, SameSite=Strict, regeneracion de ID cada 30 min
- **Contrasenas:** bcrypt con cost=12
- **Rate limiting:** Bloqueo tras 5 intentos fallidos de login (15 min)
- **Headers:** X-Frame-Options, X-Content-Type-Options, CSP, Permissions-Policy
- **Variables de entorno:** Credenciales externalizadas en `.env`
- **Auditoria:** Log completo con IP, usuario, tabla, accion y timestamp

---

## Base de datos

### Tablas principales

| Tabla | Registros iniciales | Descripcion |
|-------|--------------------:|-------------|
| `roles` | 4 | administrador, gerencia_bn, controlador_inventario, registrador |
| `usuarios` | 1 | Usuario admin por defecto |
| `areas` | 51 | Areas fisicas de la MCP |
| `estados` | 5 | Operativo, Inoperativo, En Resguardo, Chatarra, Desincorporado |
| `tipos_bien` | 14 | Clasificaciones Publicacion 9 |
| `bienes` | 0 | Inventario principal |
| `movimientos` | 0 | Incorporacion, traslado, desincorporacion |
| `mantenimientos` | 0 | Mantenimientos preventivos y correctivos |
| `auditoria` | 0 | Log de operaciones |
| `configuracion` | 7 | Parametros del sistema |

---

## Solucion de problemas

### "404 - Pagina no encontrada"
1. Verificar que Apache este corriendo en XAMPP
2. Verificar que `mod_rewrite` este habilitado en `httpd.conf`
3. Verificar que `AllowOverride All` este activo para `htdocs`
4. Acceder a: `http://localhost/SBN_MCP/public/`

### "Error de conexion a la base de datos"
1. Verificar que MySQL este corriendo en XAMPP
2. Verificar el archivo `.env` (DB_HOST, DB_NAME, DB_USER, DB_PASS)
3. Verificar que la BD `hospital_bienes` exista en phpMyAdmin

### "Acceso denegado"
El rol del usuario no tiene permiso para esa ruta. Verificar roles en `app/core/App.php`.

### "Token de seguridad invalido"
El token CSRF expiro. Recargar la pagina e intentar de nuevo.

### "Error: Column not found"
La BD no esta actualizada. Reimportar `sql/hospital_bienes_DEFINITIVO.sql`.

---

## Documentacion adicional

- [Documentacion tecnica completa](docs/DOCUMENTACION.md)
- [Checklist de tareas pendientes](docs/CHECKLIST_PENDIENTES.md)

---

## Equipo

**UPTEC-MS** — Proyecto Socio-Tecnologico II
Maternidad Concepcion Palacios
Caracas, Venezuela

**Version:** 1.1.0 | Abril 2026

---

## Licencia

Este software es de uso interno de la Maternidad Concepcion Palacios. Todos los derechos reservados.
