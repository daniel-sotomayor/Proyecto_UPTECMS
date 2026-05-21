# ⚡ Quick Start - Instalación Rápida (5 minutos)

## 🎯 Objetivo
Instalar y tener funcionando el Sistema de Bienes Nacionales en tu PC en menos de 5 minutos.

---

## ✅ Requisitos Previos

- [ ] XAMPP instalado (PHP 8+, MySQL)
- [ ] Acceso a phpMyAdmin (`http://localhost/phpmyadmin`)
- [ ] Carpeta del proyecto en: `C:\xampp\htdocs\SBN_MCP`

---

## 🚀 Instalación Paso a Paso

### 1️⃣ Copiar Archivos (30 segundos)
```
Copiar la carpeta completa del proyecto a:
C:\xampp\htdocs\SBN_MCP
```

### 2️⃣ Crear Base de Datos (1 minuto)

**Opción A: phpMyAdmin (Recomendado)**
```
1. Ir a: http://localhost/phpmyadmin
2. Click en "Importar" (arriba)
3. Seleccionar archivo: sql/hospital_bienes_FINAL.sql
4. Click en "Continuar"
5. ¡Listo!
```

**Opción B: Línea de Comandos**
```bash
cd C:\xampp\mysql\bin
mysql -u root -p < "C:\xampp\htdocs\SBN_MCP\sql\hospital_bienes_FINAL.sql"
# (Presionar Enter si no hay contraseña)
```

### 3️⃣ Verificar Configuración (30 segundos)

Abrir archivo: `C:\xampp\htdocs\SBN_MCP\config\database.php`

Verificar que tenga:
```php
'host' => 'localhost',
'db' => 'hospital_bienes',  // Debe coincidir
'user' => 'root',           // Tu usuario MySQL
'pass' => '',               // Tu contraseña MySQL
```

Si es diferente, actualizar y guardar.

### 4️⃣ Primer Acceso (1 minuto)

```
1. Abrir navegador
2. Ir a: http://localhost/SBN_MCP/public/
3. Login:
   Usuario: admin
   Contraseña: Admin_bn
4. Click en "Ingresar"
```

### 5️⃣ Cambiar Contraseña (1 minuto)

En el primer login, el sistema te pedirá cambiar la contraseña:
```
Nueva contraseña: (tu nueva contraseña)
Confirmar: (repite)
Click: Cambiar
```

---

## ✅ ¡Sistema Listo!

Si llegaste aquí sin errores, el sistema está funcionando.

Acceso rápido:
- **Admin:** http://localhost/SBN_MCP/public/
- **phpMyAdmin:** http://localhost/phpmyadmin
- **XAMPP Control:** Iniciar Apache y MySQL

---

## 🆘 Problemas Comunes

### ❌ "Error de conexión a base de datos"
```
Solución:
1. Verificar que MySQL esté iniciado en XAMPP Control
2. Verificar usuario/contraseña en config/database.php
3. Verificar que la BD "hospital_bienes" exista en phpMyAdmin
```

### ❌ "Página no encontrada (404)"
```
Solución:
1. Verificar que la carpeta esté en: C:\xampp\htdocs\SBN_MCP
2. Verificar URL: http://localhost/SBN_MCP/public/
3. Reiniciar Apache
```

### ❌ "La contraseña es incorrecta"
```
Solución:
1. Verificar que copiaste correctamente (Admin_bn)
2. Si fallaste 5 veces, esperar 2 minutos
3. Contactar administrador para reset
```

### ❌ "Las imágenes no se suben"
```
Solución:
1. Verificar que la carpeta uploads/ exista
2. Verificar permisos (clic derecho > Propiedades > Seguridad)
3. El usuario de XAMPP (SYSTEM) debe tener permisos
```

---

## 📚 ¿Necesitas Más Información?

| Necesito | Dónde | Archivo |
|----------|-------|---------|
| **Manual de usuario** | Registrar bienes | docs/MANUAL_USUARIO_REGISTRO_BIENES.md |
| **Manual de inventario** | Ver/filtrar bienes | docs/MANUAL_USUARIO_INVENTARIO_REPORTES.md |
| **Gestionar usuarios** | Admin solo | docs/MANUAL_CONFIGURACION_USUARIOS_ROLES.md |
| **Tecnicismos del código** | Desarrolladores | docs/DOCUMENTACION_CODIGO_ARQUITECTURA.md |
| **Todos los diagramas** | Ver arquitectura | docs/ (4 archivos) |
| **Instalación completa** | Detalles completos | INSTALACION_Y_USO.md |
| **Resumen ejecutivo** | Visión general | RESUMEN_EJECUTIVO.md |
| **Este archivo** | Quick start | QUICK_START.md |

---

## 🎓 Primeros Pasos como Usuario

### 1. Registrar un Bien
```
1. Menu → Registro de Bienes
2. Llenar el formulario (6 pasos)
3. Agregar foto del bien
4. Guardar
```

### 2. Ver Inventario
```
1. Menu → Inventario Global
2. Buscar/filtrar bienes
3. Exportar si lo necesitas
```

### 3. Ver Control Mural
```
1. Menu → Control Mural
2. Seleccionar un área
3. Ver bienes del área
```

### 4. Generar Reportes
```
1. Menu → Reportes
2. Seleccionar tipo (BM-1, BM-2, etc.)
3. Descargar en PDF/Excel
```

---

## ⚙️ Roles y Acceso

| Usuario | Permisos |
|---------|----------|
| **Admin** | Acceso total, gestionar usuarios |
| **Coordinador** | Crear/editar bienes, ver reportes |
| **Especialista** | Crear/editar bienes |
| **Visualizador** | Solo lectura (inventario) |
| **Auditor** | Ver auditoría, reportes |

---

## 🧪 Verificar Instalación (Opcional)

Para verificar que todo está correcto:

```bash
cd C:\xampp\htdocs\SBN_MCP
php tests/run_tests.php
```

Deberías ver:
```
✓ 41 tests pasaron
✓ 0 fallaron
✓ Instalación correcta
```

---

## 📞 Soporte

Si tienes problemas:

1. **Lee el manual correspondiente** (ver tabla de arriba)
2. **Revisa la sección de problemas comunes** (arriba)
3. **Contacta al administrador** con detalles del error
4. **Envía el log** de: `logs/error.log`

---

## 💡 Tips Útiles

- 🔐 Cambia la contraseña del admin en primer login
- 📸 Las imágenes se guardan automáticamente en `uploads/`
- 💾 Los respaldos se guardan en `backups/`
- 📋 Todos los cambios se registran en `logs/`
- 🔍 Usa la búsqueda avanzada para encontrar bienes rápido
- 📊 Exporta reportes en PDF para imprimirlos

---

## ✨ ¡Listo para Usar!

```
✅ Sistema instalado
✅ Base de datos funcionando
✅ Contraseña cambiada
✅ Listo para registrar bienes

¡Comienza a usar el sistema! 🚀
```

---

**⏱️ Tiempo total: ~5 minutos**

*Para instalación detallada, ver: INSTALACION_Y_USO.md*
