# 📚 Índice Maestro de Documentación - Sistema de Bienes Nacionales MCP

## Bienvenida

Bienvenido a la documentación completa del **Sistema de Gestión de Bienes Nacionales MCP (SBN_MCP)**.

Este índice te guiará a través de toda la documentación disponible, organizada por categoría y nivel de audiencia.

---

## 📖 Guías por Audiencia

### 👥 Para Usuarios Finales

Si eres un usuario que necesita usar el sistema para registrar y gestionar bienes:

1. **[Manual de Usuario - Registro de Bienes](MANUAL_USUARIO_REGISTRO_BIENES.md)**
   - Cómo acceder al sistema
   - Paso a paso para registrar un bien
   - Validaciones automáticas
   - Solución de errores comunes

2. **[Manual de Usuario - Inventario y Reportes](MANUAL_USUARIO_INVENTARIO_REPORTES.md)**
   - Vista del inventario global
   - Control Mural (inventario por área)
   - Generación de reportes
   - Exportación de datos

3. **[Manual de Configuración de Usuarios y Roles](MANUAL_CONFIGURACION_USUARIOS_ROLES.md)**
   - Gestión de usuarios (solo administrador)
   - Roles y permisos disponibles
   - Cambiar contraseña
   - Recuperar contraseña olvidada
   - Auditoría de accesos

### 👨‍💻 Para Desarrolladores

Si eres desarrollador y necesitas entender la arquitectura y el código:

1. **[Documentación del Código - Arquitectura y Estructura](DOCUMENTACION_CODIGO_ARQUITECTURA.md)**
   - Arquitectura MVC
   - Estructura de carpetas
   - Componentes principales (App, Database, Controller, Session)
   - Flujo de una solicitud
   - Modelo de datos
   - Validaciones
   - Autenticación y autorización
   - Auditoría
   - Manejo de errores
   - Tips de desarrollo

### 🔧 Para Administradores de Sistemas

Si administras la instalación e infraestructura:

1. **[Guía de Instalación y Uso](INSTALACION_Y_USO.md)**
   - Requisitos del sistema
   - Paso a paso de instalación
   - Primer acceso
   - Estructura de carpetas
   - Pruebas automáticas
   - Respaldo y restauración

2. **[Cambios y Auditoría](CAMBIOS_Y_AUDITORIA.md)**
   - Registro de cambios
   - Control de roles y permisos
   - Pruebas y validaciones
   - Respaldo y recuperación
   - Cambios recientes

---

## 📊 Diagramas Técnicos

### 🗄️ Diagrama Entidad-Relación (ER)
**Archivo:** [DIAGRAMA_ENTIDAD_RELACION.md](DIAGRAMA_ENTIDAD_RELACION.md)

Muestra la estructura completa de la base de datos:
- Tablas principales: usuarios, bienes, áreas, movimientos, etc.
- Relaciones entre tablas
- Campos y tipos de dato
- Claves primarias y foráneas

**Ideal para:** Desarrolladores, DBA, arquitectos de sistemas

---

### 📈 Diagrama de Flujo de Datos (DFD)
**Archivo:** [DIAGRAMA_FLUJO_DATOS.md](DIAGRAMA_FLUJO_DATOS.md)

Muestra cómo fluyen los datos a través del sistema:
- Nivel 0: Visión general
- Nivel 1: Procesos principales
- Nivel 2: Detalle de procesos específicos (Gestión de Bienes, Movimientos, Reportes, Auditoría)
- Almacenes de datos

**Ideal para:** Analistas de sistemas, arquitectos, desarrolladores

---

### 🔄 Diagrama de Procesos (BPMN)
**Archivo:** [DIAGRAMA_PROCESOS_BPMN.md](DIAGRAMA_PROCESOS_BPMN.md)

Muestra los procesos de negocio paso a paso:
- Flujo de autenticación
- Flujo de registro de bien
- Flujo de movimiento de bien
- Flujo de generación de reportes
- Flujo de control mural (inventario por área)

**Ideal para:** Analistas de negocio, coordinadores de proyecto, usuarios avanzados

---

### 🎯 Diagrama de Flujo de Acciones del Usuario
**Archivo:** [DIAGRAMA_FLUJO_ACCIONES.md](DIAGRAMA_FLUJO_ACCIONES.md)

Muestra las acciones que puede realizar un usuario:
- Flujo principal del sistema (navegación)
- Flujo detallado: Registro de bien (6 pasos)
- Flujo de búsqueda y filtrado
- Flujo de logout

**Ideal para:** Usuarios, capacitadores, especialistas en UX

---

## 🎓 Funcionalidades del Sistema

**Archivo:** [FUNCIONALIDADES.md](FUNCIONALIDADES.md)

Lista completa de funcionalidades implementadas y estado de cada módulo:
- Registro, edición y eliminación de bienes
- Inventario global y por área
- Validación de duplicados
- Soporte para responsables
- Carga de imágenes
- Control de roles y permisos
- Auditoría completa
- Reportes
- Respaldo y restauración
- Pruebas automáticas

---

## 📋 Cambios y Versiones

**Archivo:** [CAMBIOS_Y_AUDITORIA.md](CAMBIOS_Y_AUDITORIA.md)

Registro de cambios recientes y versiones:
- Auditoría de cambios
- Control de roles
- Pruebas y validaciones
- Cambios de 2026-05-19

---

## 🗄️ Archivo SQL Final

**Archivo:** `sql/hospital_bienes_FINAL.sql`

Archivo SQL único y definitivo que contiene:
- Estructura completa de la base de datos
- Todas las tablas (roles, usuarios, áreas, bienes, movimientos, etc.)
- Datos iniciales
- Índices y claves foráneas
- Compatible con MySQL 8.0+ y MariaDB 10.4+

**Para usar:** Importa este archivo en tu gestor MySQL/MariaDB para instalar el sistema en otra PC.

---

## 🗂️ Estructura de Carpetas de Documentación

```
docs/
├── INSTALACION_Y_USO.md                      # Guía de instalación
├── CAMBIOS_Y_AUDITORIA.md                    # Registro de cambios
├── FUNCIONALIDADES.md                        # Lista de funcionalidades
├── MANUAL_USUARIO_REGISTRO_BIENES.md         # Manual usuario - Registros
├── MANUAL_USUARIO_INVENTARIO_REPORTES.md     # Manual usuario - Inventario
├── MANUAL_CONFIGURACION_USUARIOS_ROLES.md    # Manual - Usuarios/Roles
├── DOCUMENTACION_CODIGO_ARQUITECTURA.md      # Documentación técnica
├── DIAGRAMA_ENTIDAD_RELACION.md              # ER Diagram
├── DIAGRAMA_FLUJO_DATOS.md                   # DFD
├── DIAGRAMA_PROCESOS_BPMN.md                 # BPMN Procesos
├── DIAGRAMA_FLUJO_ACCIONES.md                # Flujo de acciones
└── README_DOCUMENTACION.md                   # Este archivo

sql/
├── hospital_bienes_FINAL.sql                 # SQL DEFINITIVO
├── hospital_bienes_DEFINITIVO.sql            # (Anterior - no usar)
├── 20260517_add_responsable_fields.sql       # (Migración - ya incluida)
├── notifications_and_backup.sql              # (Incluida)
└── ...
```

---

## ✅ Checklist de Instalación

Para instalar el sistema en otra PC, sigue este checklist:

- [ ] Instalar XAMPP con PHP 8+ y MySQL/MariaDB
- [ ] Crear carpeta del proyecto: `C:/xampp/htdocs/SBN_MCP`
- [ ] Copiar todos los archivos del proyecto
- [ ] Importar `sql/hospital_bienes_FINAL.sql` en MySQL
- [ ] Editar `config/database.php` con datos de conexión
- [ ] Asegurar carpetas escribibles: `uploads/`, `backups/`, `logs/`
- [ ] Acceder a `http://localhost/SBN_MCP/public/`
- [ ] Login con: usuario=`admin`, clave=`Admin_bn`
- [ ] Cambiar contraseña en primer login
- [ ] Ejecutar pruebas: `php tests/run_tests.php`
- [ ] ¡Sistema listo!

---

## 🆘 Soporte y Contacto

### Problemas Comunes

| Problema | Solución |
|----------|----------|
| "Base de datos no encontrada" | Importa `sql/hospital_bienes_FINAL.sql` |
| "Error de conexión" | Verifica `config/database.php` |
| "Acceso denegado" | Asegura permisos en carpetas |
| "Pruebas fallan" | Verifica instalación de dependencias |

### Documentos Relacionados

- **Instalación:** Ver [INSTALACION_Y_USO.md](INSTALACION_Y_USO.md)
- **Errores de Usuario:** Ver [MANUAL_USUARIO_REGISTRO_BIENES.md](MANUAL_USUARIO_REGISTRO_BIENES.md)
- **Problemas de Código:** Ver [DOCUMENTACION_CODIGO_ARQUITECTURA.md](DOCUMENTACION_CODIGO_ARQUITECTURA.md)

### Contacto

Para soporte técnico, contactar a:
- Administrador del Sistema
- Gerencia de Bienes Nacionales MCP
- Equipo de Desarrollo

---

## 📞 Versión y Metadata

| Item | Valor |
|------|-------|
| **Sistema** | Sistema de Gestión de Bienes Nacionales (SBN_MCP) |
| **Versión** | 1.1.0 |
| **Fecha** | 2026-05-19 |
| **Estado** | PRODUCCIÓN |
| **Motor DB** | MySQL 8.0+ / MariaDB 10.4+ |
| **PHP** | 8.0+ |
| **Patrón** | MVC (Modelo-Vista-Controlador) |
| **Testing** | ✅ 41 tests pasando |
| **Documentación** | ✅ Completa |

---

## 🎯 Mapa de Lectura Recomendado

### Primero (10 minutos)
1. Este archivo (README_DOCUMENTACION.md)
2. [FUNCIONALIDADES.md](FUNCIONALIDADES.md)

### Luego (30 minutos)
3. Tu rol específico:
   - **Usuario:** [MANUAL_USUARIO_REGISTRO_BIENES.md](MANUAL_USUARIO_REGISTRO_BIENES.md)
   - **Admin:** [INSTALACION_Y_USO.md](INSTALACION_Y_USO.md)
   - **Desarrollador:** [DOCUMENTACION_CODIGO_ARQUITECTURA.md](DOCUMENTACION_CODIGO_ARQUITECTURA.md)

### Después (según necesidad)
4. Consulta diagramas específicos según tu rol
5. Manuales de referencia rápida

---

## 📝 Notas Finales

- Toda la documentación está en Markdown para fácil visualización
- Los diagramas están en formato Mermaid (se visualizan en GitHub y muchas plataformas)
- La documentación se actualiza con cada versión del sistema
- Para reportar errores o sugerencias, contacta al equipo de desarrollo

---

**¡Bienvenido al Sistema de Bienes Nacionales MCP! 🎉**

**Última actualización:** 2026-05-19  
**Estado:** ✅ Documentación completa y lista para producción
