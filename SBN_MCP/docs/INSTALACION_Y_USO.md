# Guía de Instalación y Uso — Sistema de Bienes Nacionales MCP

## 1. Requisitos
- XAMPP (PHP 8+, MySQL/MariaDB)
- Composer (opcional, si deseas actualizar dependencias)
- Navegador web moderno

## 2. Instalación
1. **Clona o copia el proyecto en tu carpeta de XAMPP:**
   - Ejemplo: `C:/xampp/htdocs/SBN_MCP`
2. **Crea la base de datos:**
   - Abre phpMyAdmin o consola MySQL.
   - Importa el archivo: `sql/hospital_bienes_FINAL.sql`
3. **Configura la conexión a la base de datos:**
   - Edita `config/database.php` y ajusta usuario, clave y nombre de la base de datos si es necesario.
4. **(Opcional) Instala dependencias PHP:**
   - Abre terminal en la carpeta del proyecto y ejecuta: `composer install`
5. **Configura permisos de carpetas:**
   - Asegúrate de que las carpetas `uploads/`, `backups/`, y `logs/` sean escribibles por PHP.

## 3. Primer acceso
- Usuario: `admin`
- Clave: `Admin_bn`
- El sistema pedirá cambiar la clave en el primer login.

## 4. Estructura de carpetas
- `app/` — Código fuente MVC
- `public/` — Archivos públicos (index.php, css, js)
- `sql/` — Scripts de base de datos
- `uploads/` — Imágenes y archivos subidos
- `backups/` — Copias de seguridad
- `logs/` — Logs del sistema
- `tests/` — Pruebas automáticas
- `docs/` — Documentación

## 5. Pruebas automáticas
- Ejecuta: `php tests/run_tests.php`
- Todos los tests deben pasar (ver consola para detalles)

## 6. Respaldo y restauración
- Usa los scripts en `scripts/backup.php` para generar copias de seguridad.

## 7. Soporte y contacto
- Para soporte técnico, contactar a la Gerencia de Bienes Nacionales MCP.

---
**¡Listo! El sistema está preparado para usarse en cualquier equipo siguiendo estos pasos.**
