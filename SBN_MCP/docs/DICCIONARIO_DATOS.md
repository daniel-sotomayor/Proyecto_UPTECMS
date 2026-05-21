# Diccionario de Datos
## Sistema de Gestión de Bienes Nacionales - SBN_MCP
### Maternidad Concepción Palacios

---

## Tabla de Contenidos
1. [roles](#roles)
2. [usuarios](#usuarios)
3. [areas](#areas)
4. [estados](#estados)
5. [tipos_bien](#tipos_bien)
6. [bienes](#bienes)
7. [movimientos](#movimientos)
8. [mantenimientos](#mantenimientos)
9. [auditoria](#auditoria)
10. [configuracion](#configuracion)
11. [notificaciones](#notificaciones)
12. [backups_log](#backups_log)
13. [password_resets](#password_resets)
14. [v_intentos_recuperacion](#v_intentos_recuperacion)

---

## roles

Almacena los roles del sistema con sus permisos.

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id_rol | INT | PK, AUTO_INCREMENT | Identificador único del rol |
| nombre | VARCHAR(50) | UNIQUE, NOT NULL | Nombre del rol |
| descripcion | TEXT | NULL | Descripción del rol |
| permisos | JSON | NOT NULL | Array de permisos en formato JSON |
| activo | BOOLEAN | DEFAULT TRUE | Estado del rol |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Fecha de última actualización |

### Roles Predefinidos

| Rol| Permisos |
|---------------|----------|
| administrador | usuarios_crear, usuarios_editar, usuarios_eliminar, usuarios_ver, bienes_crear, bienes_editar, bienes_eliminar, bienes_ver, movimientos_crear, movimientos_ver, movimientos_aprobar, actas_generar, reportes_generar, configuracion, auditoria_ver |
| gerencia_bn | bienes_crear, bienes_editar, bienes_ver, movimientos_crear, movimientos_ver, movimientos_aprobar, actas_generar, reportes_generar, auditoria_ver |
| controlador_inventario | bienes_editar, bienes_ver, movimientos_ver, reportes_generar, auditoria_ver |
| registrador | bienes_crear, bienes_ver, movimientos_ver |

---

## usuarios

Registro de usuarios del sistema.

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id_usuario | INT | PK, AUTO_INCREMENT | Identificador único del usuario |
| id_rol | INT | FK → roles.id_rol | Rol asignado al usuario |
| cedula | VARCHAR(20) | UNIQUE, NOT NULL | Cédula de identidad (solo números, 6-9 dígitos) |
| username | VARCHAR(50) | UNIQUE, NULL | Nombre de usuario generado automáticamente |
| primer_nombre | VARCHAR(100) | NOT NULL, DEFAULT '' | Primer nombre |
| segundo_nombre | VARCHAR(100) | DEFAULT '' | Segundo nombre |
| primer_apellido | VARCHAR(100) | NOT NULL, DEFAULT '' | Primer apellido |
| segundo_apellido | VARCHAR(100) | DEFAULT '' | Segundo apellido |
| nombre_completo | VARCHAR(255) | NOT NULL | Nombre completo concatenado |
| email | VARCHAR(255) | UNIQUE, NOT NULL | Correo electrónico |
| password_hash | VARCHAR(255) | NOT NULL | Hash de la contraseña (bcrypt) |
| telefono | VARCHAR(20) | NULL | Teléfono de contacto |
| cargo | VARCHAR(100) | NULL | Cargo del usuario |
| ultimo_acceso | TIMESTAMP | NULL | Último login exitoso |
| intentos_fallidos | INT | DEFAULT 0 | Contador de intentos fallidos |
| bloqueado_hasta | TIMESTAMP | NULL | Hasta cuándo está bloqueado |
| primer_login | BOOLEAN | DEFAULT TRUE | Indica si es primer login |
| activo | BOOLEAN | DEFAULT TRUE | Estado del usuario |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Última actualización |

### Índices
- idx_usuarios_cedula (cedula)
- idx_usuarios_username (username)
- idx_usuarios_email (email)
- idx_usuarios_rol (id_rol)

---

## areas

Estructura organizativa del hospital.

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id_area | INT | PK, AUTO_INCREMENT | Identificador único |
| nombre_area | VARCHAR(100) | NOT NULL | Nombre del área/departamento |
| descripcion | TEXT | NULL | Descripción detallada |
| edificio | VARCHAR(50) | NULL | Edificio (Principal, Anexo, etc.) |
| piso | INT | NULL | Piso (-1=Sótano, 0=Planta Baja, 1..N) |
| responsable_id | INT | FK → usuarios.id_usuario | Responsable del área |
| area_padre_id | INT | FK → areas.id_area | Área padre (jerarquía) |
| activa | BOOLEAN | DEFAULT TRUE | Estado del área |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Última actualización |

### Índices
- idx_areas_edificio (edificio)

### Áreas Predefinidas (Muestra)

| Área | Edificio | Piso |
|------|----------|------|
| Mantenimiento Técnico | Principal | -1 |
| Sala de Parto | Principal | 1 |
| Quirófano | Principal | 1 |
| Mamá Canguro | Anexo | 0 |
| Adolescentes | Anexo | 1 |

---

## estados

Estados posibles de un bien nacional.

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id_estado | INT | PK | Identificador único |
| nombre | VARCHAR(50) | NOT NULL | Nombre del estado |
| descripcion | TEXT | NULL | Descripción del estado |
| color | VARCHAR(7) | NULL | Color HEX para UI |
| es_baja | BOOLEAN | DEFAULT FALSE | TRUE si es bien fuera de servicio |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |

### Estados Predefinidos

| ID | Estado | Descripción | Color | Es Baja |
|----|--------|-------------|-------|---------|
| 1 | Operativo | Bien en funcionamiento normal | #28a745 | No |
| 2 | Inoperativo | Bien que no funciona pero es recuperable | #ffc107 | No |
| 3 | En Resguardo | Bien resguardado temporalmente | #17a2b8 | No |
| 4 | Chatarra | Bien sin posibilidad de recuperación | #dc3545 | Sí |
| 5 | Desincorporado | Bien dado de baja oficialmente | #6c757d | Sí |

---

## tipos_bien

Clasificación de bienes según Publicación 9 / SUDEBIP.

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id_tipo | INT | PK, AUTO_INCREMENT | Identificador único |
| codigo | VARCHAR(10) | NOT NULL | Código de clasificación (01, 06-1, etc.) |
| nombre | VARCHAR(100) | NOT NULL | Nombre del tipo |
| descripcion | TEXT | NULL | Descripción del tipo |
| vida_util_anos | INT | DEFAULT 10 | Vida útil en años |
| categoria | VARCHAR(50) | NULL | Categoría general |
| activo | BOOLEAN | DEFAULT TRUE | Estado del tipo |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |

### Tipos Predefinidos

| Código | Nombre | Vida Útil | Categoría |
|--------|--------|-----------|-----------|
| 01 | Equipos de Oficina | 10 | Oficina |
| 02 | Del Alojamiento | 10 | Alojamiento |
| 03 | Material de Construcción y Taller | 15 | Construcción |
| 04 | Vehículos | 15 | Vehículos |
| 05 | Telecomunicaciones | 8 | Telecomunicaciones |
| 06 | Bienes Hospitalarios | 10 | Hospitalario |
| 13 | Equipos de Procesamiento de Datos | 5 | Tecnología |

---

## bienes

Registro principal del inventario de bienes nacionales.

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id_bien | INT | PK, AUTO_INCREMENT | Identificador único del bien |
| codigo_sudebip | VARCHAR(50) | UNIQUE, NOT NULL | Código interno BN-YYYY-NNNNNN |
| codigo_interno | VARCHAR(50) | NULL | Código Publicación 9: TIPO-EDIF-PISO-SEQ |
| codigo_ministerio | VARCHAR(50) | NULL | Código asignado por Ministerio de Salud |
| nro_bien_ministerio | VARCHAR(30) | NULL | Número de bien Ministerio (6-7 dígitos + 3 hex) |
| es_sn | BOOLEAN | DEFAULT FALSE | TRUE si no tiene número ministerio |
| serial | VARCHAR(100) | NULL | Número de serie del bien |
| nombre | VARCHAR(255) | NOT NULL | Nombre descriptivo del bien |
| descripcion | TEXT | NULL | Descripción específica |
| marca | VARCHAR(100) | NULL | Marca del bien |
| modelo | VARCHAR(100) | NULL | Modelo del bien |
| color | VARCHAR(50) | NULL | Color del bien |
| año_fabricacion | INT | NULL | Año de fabricación |
| cantidad | INT | DEFAULT 1 | Cantidad (máx 9999) |
| condicion_inicial | VARCHAR(20) | NULL | Nuevo, Bueno, Regular, Malo |
| id_estado | INT | DEFAULT 1, FK → estados.id_estado | Estado actual del bien |
| id_tipo | INT | FK → tipos_bien.id_tipo | Tipo/clasificación |
| id_area | INT | FK → areas.id_area | Ubicación actual |
| responsable_id | INT | FK → usuarios.id_usuario | Responsable del bien |
| cin_edificio | VARCHAR(50) | NULL | C.I.N: Edificio |
| cin_piso | VARCHAR(10) | NULL | C.I.N: Piso |
| cin_departamento | VARCHAR(100) | NULL | C.I.N: Departamento |
| cin_oficina | VARCHAR(100) | NULL | C.I.N: Oficina |
| cin_posicion | VARCHAR(20) | NULL | C.I.N: Posición |
| numero_factura | VARCHAR(50) | NULL | Número de factura |
| fecha_adquisicion | DATE | NULL | Fecha de adquisición (no futura) |
| valor_inicial | DECIMAL(15,2) | NULL | Valor unitario (max 9999999999.99) |
| valor_residual | DECIMAL(15,2) | DEFAULT 0 | Valor residual |
| vida_util_anos | INT | DEFAULT 10 | Vida útil en años |
| observaciones | TEXT | NULL | Observaciones adicionales |
| imagen_path | VARCHAR(500) | NULL | Ruta de imagen del bien |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de registro |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Última actualización |

### Índices
- idx_bienes_codigo_sudebip (codigo_sudebip)
- idx_bienes_codigo_interno (codigo_interno)
- idx_bienes_nro_ministerio (nro_bien_ministerio)
- idx_bienes_estado (id_estado)
- idx_bienes_area (id_area)
- idx_bienes_tipo (id_tipo)
- idx_bienes_nombre (nombre)
- idx_bienes_updated_at (updated_at)
- idx_bienes_valor (valor_inicial)

### Validaciones de Número de Bien Ministerio
- Formato: 6-7 dígitos seguidos de 3 letras A-F (hexadecimal)
- Ejemplos válidos: `1234567ABC`, `123456DEF`
- Sin guiones, espacios ni otros caracteres

---

## movimientos

Trazabilidad de movimientos de bienes.

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id_movimiento | INT | PK, AUTO_INCREMENT | Identificador único |
| bien_id | INT | NOT NULL, FK → bienes.id_bien | Bien afectado |
| tipo_movimiento | ENUM | NOT NULL | incorporacion, traslado, desincorporacion, asignacion |
| area_origen_id | INT | FK → areas.id_area | Área origen |
| area_destino_id | INT | FK → areas.id_area | Área destino |
| usuario_solicita_id | INT | FK → usuarios.id_usuario | Quien solicita |
| usuario_aprueba_id | INT | FK → usuarios.id_usuario | Quien aprueba |
| fecha_solicitud | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de solicitud |
| fecha_aprobacion | TIMESTAMP | NULL | Fecha de aprobación |
| fecha_ejecucion | TIMESTAMP | NULL | Fecha de ejecución |
| motivo | TEXT | NULL | Motivo del movimiento |
| observaciones | TEXT | NULL | Observaciones |
| documento_soporte | VARCHAR(500) | NULL | Ruta del documento soporte |
| estado | ENUM | DEFAULT 'pendiente' | pendiente, aprobado, rechazado, cancelado |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Última actualización |

### Índices
- idx_movimientos_bien (bien_id)
- idx_movimientos_tipo (tipo_movimiento)
- idx_movimientos_estado (estado)
- idx_movimientos_fecha (fecha_solicitud)

---

## mantenimientos

Registro de mantenimientos de bienes.

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id_mantenimiento | INT | PK, AUTO_INCREMENT | Identificador único |
| bien_id | INT | FK → bienes.id_bien | Bien mantenido |
| tipo_servicio | ENUM | NOT NULL | preventivo, correctivo, predictivo |
| fecha_programada | DATE | NULL | Fecha programada |
| fecha_ejecutada | DATE | NULL | Fecha de ejecución |
| proveedor | VARCHAR(255) | NULL | Proveedor del servicio |
| tecnico | VARCHAR(255) | NULL | Técnico que realizó el trabajo |
| costo | DECIMAL(15,2) | NULL | Costo del mantenimiento |
| diagnostico | TEXT | NULL | Diagnóstico realizado |
| trabajo_realizado | TEXT | NULL | Descripción del trabajo |
| proxima_fecha_programada | DATE | NULL | Próximo mantenimiento |
| garantia_meses | INT | NULL | Meses de garantía |
| observaciones | TEXT | NULL | Observaciones |
| documento_reporte | VARCHAR(500) | NULL | Ruta del reporte/documento |
| realizado_por_id | INT | FK → usuarios.id_usuario | Usuario que registró |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de registro |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Última actualización |

### Índices
- idx_mantenimientos_bien (bien_id)
- idx_mantenimientos_fecha (fecha_ejecutada)
- idx_mantenimientos_fecha_programada (fecha_programada)
- idx_mantenimientos_tipo (tipo_servicio)

---

## auditoria

Log de todas las operaciones del sistema.

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id_auditoria | INT | PK, AUTO_INCREMENT | Identificador único |
| tabla_afectada | VARCHAR(50) | NOT NULL | Tabla modificada |
| registro_id | INT | NULL | ID del registro afectado |
| accion | VARCHAR(20) | NOT NULL | CREATE, UPDATE, DELETE |
| usuario_id | INT | FK → usuarios.id_usuario | Usuario que realizó la acción |
| ip_address | VARCHAR(45) | NULL | Dirección IP |
| user_agent | TEXT | NULL | User agent del navegador |
| datos_anteriores | JSON | NULL | Estado anterior del registro |
| datos_nuevos | JSON | NULL | Nuevo estado del registro |
| fecha_operacion | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de la operación |

### Índices
- idx_auditoria_tabla (tabla_afectada)
- idx_auditoria_usuario (usuario_id)
- idx_auditoria_fecha (fecha_operacion)

---

## configuracion

Parámetros de configuración del sistema.

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id_configuracion | INT | PK, AUTO_INCREMENT | Identificador único |
| clave | VARCHAR(50) | UNIQUE, NOT NULL | Clave del parámetro |
| valor | TEXT | NULL | Valor del parámetro |
| descripcion | TEXT | NULL | Descripción del parámetro |
| tipo_dato | VARCHAR(20) | DEFAULT 'string' | Tipo: string, integer, boolean |
| editable | BOOLEAN | DEFAULT TRUE | Si es editable por UI |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Última actualización |

### Parámetros Predefinidos

| Clave | Valor | Tipo | Descripción |
|-------|-------|------|-------------|
| app_nombre | Sistema de Gestión de Bienes Nacionales | string | Nombre de la aplicación |
| app_version | 1.0.0 | string | Versión del sistema |
| institucion_nombre | Maternidad Concepción Palacios | string | Nombre de la institución |
| institucion_rif | G-20003090-0 | string | RIF de la institución |
| frecuencia_inventario | Semestral | string | Frecuencia de inventario |
| sesion_tiempo_minutos | 30 | integer | Tiempo de sesión |
| intentos_login_max | 5 | integer | Máximo intentos de login |

---

## notificaciones

Sistema de notificaciones en tiempo real.

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id_notificacion | INT | PK, AUTO_INCREMENT | Identificador único |
| usuario_id | INT | NOT NULL, FK → usuarios.id_usuario | Destinatario |
| tipo | ENUM | DEFAULT 'info' | info, success, warning, error |
| titulo | VARCHAR(255) | NOT NULL | Título de la notificación |
| mensaje | TEXT | NOT NULL | Mensaje detallado |
| link | VARCHAR(500) | NULL | URL opcional para redirección |
| leida | BOOLEAN | DEFAULT FALSE | Estado de lectura |
| created_at | DATETIME | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| read_at | DATETIME | NULL | Fecha de lectura |

### Índices
- idx_usuario (usuario_id)
- idx_leida (leida)
- idx_created (created_at)
- idx_usuario_leida (usuario_id, leida)

---

## backups_log

Registro de backups del sistema.

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id_backup | INT | PK, AUTO_INCREMENT | Identificador único |
| filename | VARCHAR(255) | NOT NULL | Nombre del archivo |
| size | INT | NOT NULL | Tamaño en bytes |
| created_at | DATETIME | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| status | ENUM | DEFAULT 'success' | success, error |
| error_message | TEXT | NULL | Mensaje de error si falló |

### Índices
- idx_created (created_at)
- idx_status (status)

---

## password_resets

Códigos de recuperación de contraseña.

| Columna | Tipo | Restricciones | Descripción |
|---------|------|---------------|-------------|
| id_reset | INT | PK, AUTO_INCREMENT | Identificador único |
| usuario_id | INT | NOT NULL, FK → usuarios.id_usuario | Usuario que solicita |
| codigo | VARCHAR(6) | NOT NULL | Código de 6 dígitos |
| email | VARCHAR(100) | NOT NULL | Email destino |
| created_at | DATETIME | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| expires_at | DATETIME | NOT NULL | Fecha de expiración |
| used_at | DATETIME | NULL | Fecha de uso |
| ip_address | VARCHAR(45) | NOT NULL | IP del solicitante |
| user_agent | TEXT | NULL | Navegador del solicitante |
| intentos_dia | INT | DEFAULT 1 | Intentos realizados hoy |
| fecha_intento | DATE | DEFAULT CURRENT_DATE | Fecha del intento |

### Índices
- idx_codigo (codigo)
- idx_expires (expires_at)
- idx_usuario_fecha (usuario_id, fecha_intento)

---

## v_intentos_recuperacion

Vista para contar intentos de recuperación por usuario y día.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| usuario_id | INT | Usuario que intenta recuperar |
| fecha_intento | DATE | Fecha del intento |
| total_intentos | BIGINT | Total de intentos en esa fecha |

### Filtros
- Solo cuenta intentos de la fecha actual (CURRENT_DATE)
- Agrupado por usuario_id y fecha_intento

---

## Relaciones entre Tablas

```
usuarios.id_rol → roles.id_rol
usuarios.responsable_id → usuarios.id_usuario (self-reference)
usuarios.area_padre_id → areas.id_area (self-reference)

bienes.id_estado → estados.id_estado
bienes.id_tipo → tipos_bien.id_tipo
bienes.id_area → areas.id_area
bienes.responsable_id → usuarios.id_usuario

movimientos.bien_id → bienes.id_bien
movimientos.area_origen_id → areas.id_area
movimientos.area_destino_id → areas.id_area
movimientos.usuario_solicita_id → usuarios.id_usuario
movimientos.usuario_aprueba_id → usuarios.id_usuario

mantenimientos.bien_id → bienes.id_bien
mantenimientos.realizado_por_id → usuarios.id_usuario

auditoria.usuario_id → usuarios.id_usuario

notificaciones.usuario_id → usuarios.id_usuario

password_resets.usuario_id → usuarios.id_usuario

areas.responsable_id → usuarios.id_usuario
areas.area_padre_id → areas.id_area
```

---

## Notas de Implementación

### Convenciones de Nombres
- Tablas: plural, minúsculas, snake_case
- Columnas: snake_case, nombres descriptivos
- Claves foráneas: `tabla_id` (ej: `id_rol`, `bien_id`)
- Timestamps: `created_at`, `updated_at`

### Tipos de Datos Comunes
- IDs: `INT AUTO_INCREMENT PRIMARY KEY`
- Textos cortos: `VARCHAR(50-255)`
- Textos largos: `TEXT`
- Decimales monetarios: `DECIMAL(15,2)`
- Booleanos: `BOOLEAN` (MySQL lo convierte a TINYINT)
- JSON: `JSON` (MySQL 5.7+)
- Fechas: `TIMESTAMP` o `DATETIME`

### Charset y Collation
- Todas las tablas: `utf8mb4`
- Collation: `utf8mb4_unicode_ci`
- Motor: `InnoDB` (soporta transacciones y FK)
