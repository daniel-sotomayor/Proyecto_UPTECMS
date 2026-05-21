# Diagrama Entidad-Relación (ER)

Este es el diagrama de la estructura de la base de datos del Sistema de Bienes Nacionales MCP.

```mermaid
erDiagram
    USUARIOS ||--o{ ROLES : tiene
    USUARIOS ||--o{ BIENES : responsable
    USUARIOS ||--o{ AREAS : responsable
    USUARIOS ||--o{ MOVIMIENTOS : solicita
    USUARIOS ||--o{ MOVIMIENTOS : aprueba
    USUARIOS ||--o{ NOTIFICACIONES : recibe
    USUARIOS ||--o{ VERIFICACIONES : realiza
    USUARIOS ||--o{ PASSWORD_RESETS : solicita
    
    BIENES ||--o{ ESTADOS : tiene
    BIENES ||--o{ TIPOS_BIEN : clasificacion
    BIENES ||--o{ AREAS : ubicacion
    BIENES ||--o{ MOVIMIENTOS : genera
    BIENES ||--o{ VERIFICACIONES : verifica
    
    AREAS ||--o{ AREAS : subarea
    AREAS ||--o{ BIENES : contiene
    AREAS ||--o{ MOVIMIENTOS : origen
    AREAS ||--o{ MOVIMIENTOS : destino
    
    MOVIMIENTOS ||--o{ NOTIFICACIONES : genera
    
    ROLES {
        int id_rol PK
        string nombre UK
        text descripcion
        json permisos
        tinyint activo
        timestamp created_at
    }
    
    USUARIOS {
        int id_usuario PK
        int id_rol FK
        string cedula UK
        string username UK
        string nombre_completo
        string email UK
        string password_hash
        string cargo
        timestamp ultimo_acceso
        tinyint primer_login
        tinyint activo
        timestamp created_at
    }
    
    AREAS {
        int id_area PK
        string nombre_area
        text descripcion
        string edificio
        tinyint piso
        int responsable_id FK
        int area_padre_id FK
        tinyint activa
        timestamp created_at
    }
    
    ESTADOS {
        int id_estado PK
        string nombre
        text descripcion
        string color
        tinyint es_baja
        timestamp created_at
    }
    
    TIPOS_BIEN {
        int id_tipo PK
        string codigo
        string nombre
        text descripcion
        tinyint vida_util_anos
        string categoria
        tinyint activo
        timestamp created_at
    }
    
    BIENES {
        int id_bien PK
        string codigo_sudebip UK
        string codigo_interno
        string codigo_ministerio
        string nro_bien_ministerio
        tinyint es_sn
        string nombre
        text descripcion
        string serial
        string marca
        string modelo
        string color
        smallint anio_fabricacion
        smallint cantidad
        enum condicion_inicial
        int id_estado FK
        int id_tipo FK
        int id_area FK
        int responsable_id FK
        string responsable_cedula
        string responsable_foto_path
        string cin_edificio
        string cin_piso
        string cin_departamento
        string cin_oficina
        string cin_posicion
        decimal valor_inicial
        decimal valor_residual
        tinyint vida_util_anos
        text observaciones
        string imagen_path
        timestamp created_at
        timestamp updated_at
    }
    
    MOVIMIENTOS {
        int id_movimiento PK
        int bien_id FK
        enum tipo_movimiento
        int area_origen_id FK
        int area_destino_id FK
        int usuario_solicita_id FK
        int usuario_aprueba_id FK
        timestamp fecha_solicitud
        timestamp fecha_aprobacion
        timestamp fecha_ejecucion
        text motivo
        text observaciones
        string documento_soporte
        enum estado
        timestamp created_at
    }
    
    NOTIFICACIONES {
        int id_notificacion PK
        int usuario_id FK
        enum tipo
        string titulo
        text mensaje
        string link
        boolean leida
        datetime created_at
        datetime read_at
    }
    
    VERIFICACIONES {
        int id_verificacion PK
        int bien_id FK
        int usuario_id FK
        datetime fecha_verificacion
        string tipo
        text observaciones
    }
    
    PASSWORD_RESETS {
        int id_reset PK
        int usuario_id FK
        string codigo
        string email
        datetime created_at
        datetime expires_at
        datetime used_at
        string ip_address
        text user_agent
        int intentos_dia
    }
```

## Descripción de Tablas Principales

### ROLES
Define los roles de usuario: administrador, gerencia_bn, controlador_inventario, registrador, validador_inventario.

### USUARIOS
Almacena toda la información de los usuarios del sistema. Relacionado con ROLES.

### AREAS
Estructura de áreas del hospital. Puede ser jerárquica (un área puede tener sub-áreas).

### BIENES
Tabla principal que almacena todos los bienes nacionales. Incluye información técnica, ubicación (C.I.N), responsable y datos económicos.

### ESTADOS
Estados posibles de un bien: Operativo, Inoperativo, En Resguardo, Chatarra, Desincorporado.

### TIPOS_BIEN
Clasificación de bienes según Publicación 9: Equipos Oficina, Alojamiento, Material Construcción, Vehículos, etc.

### MOVIMIENTOS
Registra todos los movimientos de bienes: traslados, desincorporaciones, asignaciones. Permite auditoría completa.

### NOTIFICACIONES
Notificaciones en tiempo real para usuarios sobre cambios en bienes o movimientos.

### VERIFICACIONES
Registro de verificaciones físicas realizadas para auditoría semestral.

### PASSWORD_RESETS
Gestión de recuperación de contraseñas con tokens temporales.

---

**Notas:**
- PK = Primary Key (Clave Primaria)
- FK = Foreign Key (Clave Foránea)
- UK = Unique Key (Clave Única)
- Todas las tablas tienen índices en claves foráneas para mejor rendimiento.
