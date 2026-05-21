# Guía de Instalación con XAMPP

## Sistema de Gestión de Bienes Nacionales
### Maternidad Concepción Palacios

---

## Requisitos Previos

1. **XAMPP** instalado (versión 8.0+ recomendada)
   - Descargar desde: https://www.apachefriends.org/
   - Incluir: Apache, MySQL, PHP

2. **Composer** (opcional, para dependencias)
   - Descargar desde: https://getcomposer.org/

---

## Instalación Rápida (Automática)

### Paso 1: Copiar proyecto a XAMPP

Copie la carpeta del proyecto a `C:\xampp\htdocs\`:

```
C:\xampp\htdocs\SBN_MCP\
```

### Paso 2: Ejecutar script de configuración

1. Abra **CMD** o **PowerShell** como administrador
2. Navegue a la carpeta del proyecto:
   ```cmd
   cd C:\xampp\htdocs\SBN_MCP
   ```
3. Ejecute el script de configuración:
   ```cmd
   .\setup_xampp.bat
   ```
   
   **Nota:** En PowerShell, use `.\setup_xampp.bat` en lugar de `setup_xampp.bat`

El script automáticamente:
- Verifica la instalación de XAMPP
- Crea la base de datos `hospital_bienes`
- Importa el esquema MySQL
- Crea los directorios necesarios
- Instala dependencias con Composer (si está disponible)

### Paso 3: Iniciar servicios

1. Abra **XAMPP Control Panel**
2. Inicie **Apache** y **MySQL**
3. Abra su navegador en: http://localhost/SBN_MCP/public/
   
   **Nota:** Asegúrese de incluir la barra diagonal al final de la URL

### Paso 4: Acceder al sistema

**Credenciales de administrador:**
- Cédula: `V-12345678`
- Contraseña: `Admin123!`

**IMPORTANTE:** Cambie la contraseña después del primer login.

---

## Instalación Manual

Si el script automático no funciona, siga estos pasos:

### 1. Configurar base de datos

1. Abra **phpMyAdmin** (http://localhost/phpmyadmin)
2. Cree una nueva base de datos llamada `hospital_bienes`
3. Seleccione la base de datos
4. Vaya a la pestaña **Importar**
5. Seleccione el archivo `sql/schema_mysql.sql`
6. Haga clic en **Continuar**

### 2. Verificar configuración PHP

Edite `C:\xampp\php\php.ini` y asegúrese de que estas extensiones estén habilitadas:

```ini
extension=pdo_mysql
extension=mbstring
extension=json
extension=openssl
```

### 3. Configurar archivo .env

El archivo `.env` ya está configurado para XAMPP con valores por defecto:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=hospital_bienes
DB_USER=root
DB_PASS=
```

### 4. Instalar dependencias (opcional)

Si tiene Composer instalado:

```cmd
cd C:\xampp\htdocs\SBN_MCP
composer install --no-dev --optimize-autoloader
```

### 5. Crear directorios

Cree los siguientes directorios si no existen:
- `uploads/`
- `logs/`
- `reports/`

---

## Estructura del Proyecto

```
SBN_MCP/
├── app/
│   ├── controllers/    # Controladores de la aplicación
│   ├── core/          # Clases base (App, Router, Database, etc.)
│   ├── helpers/       # Funciones auxiliares
│   └── views/         # Vistas (templates PHP)
├── config/
│   ├── app.php        # Configuración de la aplicación
│   └── database.php   # Configuración de base de datos
├── public/
│   ├── index.php      # Punto de entrada
│   ├── css/           # Estilos CSS
│   └── .htaccess      # Configuración Apache
├── sql/
│   ├── schema.sql     # Esquema PostgreSQL (original)
│   └── schema_mysql.sql # Esquema MySQL (para XAMPP)
├── uploads/           # Archivos subidos
├── logs/              # Archivos de log
├── reports/           # Reportes generados
├── .env               # Variables de entorno
├── composer.json      # Dependencias PHP
└── setup_xampp.bat    # Script de configuración
```

---

## Solución de Problemas

### Error: "No se pudo conectar a MySQL"

**Solución:**
1. Verifique que MySQL esté ejecutándose en XAMPP Control Panel
2. Verifique que el puerto 3306 no esté en uso
3. Reinicie MySQL desde XAMPP Control Panel

### Error: "Base de datos no encontrada"

**Solución:**
1. Ejecute el script `setup_xampp.bat` nuevamente
2. O importe manualmente `sql/schema_mysql.sql` desde phpMyAdmin

### Error: "Extensión PDO no encontrada"

**Solución:**
1. Edite `C:\xampp\php\php.ini`
2. Busque y descomente: `extension=pdo_mysql`
3. Reinicie Apache desde XAMPP Control Panel

### Error: "404 Not Found"

**Solución:**
1. Verifique que el proyecto esté en `C:\xampp\htdocs\SBN_MCP`
2. Verifique que Apache esté ejecutándose
3. Acceda a: http://localhost/SBN_MCP/public

### Error: "Acceso denegado"

**Solución:**
1. Verifique los permisos de la carpeta del proyecto
2. Asegúrese de que la carpeta `logs/` sea escribible

### Error: "Composer no encontrado"

**Solución:**
1. Instale Composer desde https://getcomposer.org/
2. O descargue las dependencias manualmente
3. El sistema funcionará sin Composer (con funcionalidad limitada)

---

## Configuración de Apache (Opcional)

Para usar un VirtualHost personalizado:

### 1. Editar archivo hosts

Abra `C:\Windows\System32\drivers\etc\hosts` como administrador y agregue:

```
127.0.0.1 hospital-bienes.local
```

### 2. Configurar VirtualHost

Edite `C:\xampp\apache\conf\extra\httpd-vhosts.conf` y agregue:

```apache
<VirtualHost *:80>
    ServerName hospital-bienes.local
    DocumentRoot "C:/xampp/htdocs/SBN_MCP/public"
    
    <Directory "C:/xampp/htdocs/SBN_MCP/public">
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 3. Reiniciar Apache

Reinicie Apache desde XAMPP Control Panel.

### 4. Acceder

Abra: http://hospital-bienes.local

---

## Credenciales por Defecto

| Usuario | Cédula | Contraseña | Rol |
|---------|--------|------------|-----|
| Administrador | V-12345678 | Admin123! | Administrador del sistema |

---

## Funcionalidades del Sistema

- **Gestión de Bienes:** Registro, edición, eliminación de bienes nacionales
- **Control de Movimientos:** Traslados, incorporaciones, desincorporaciones
- **Mantenimientos:** Registro de mantenimientos preventivos y correctivos
- **Reportes:** Generación de reportes y estadísticas
- **Auditoría:** Log de todas las operaciones del sistema
- **Usuarios y Roles:** Control de acceso por roles y permisos

---

## Soporte

Para problemas técnicos:
1. Revise los logs en `logs/`
2. Consulte la documentación en `docs/`
3. Verifique la configuración en `config/`

---

## Notas Importantes

1. **Seguridad:** Cambie la contraseña del administrador después del primer login
2. **Backups:** Realice backups periódicos de la base de datos
3. **Producción:** Para entorno de producción, desactive `APP_DEBUG` en `.env`
4. **HTTPS:** Configure SSL/TLS para entorno de producción

---

*Versión: 1.0.0*
*Última actualización: 2026-04-01*
