# Diagrama de Flujo de Datos (DFD)

## Nivel 0: Visión General del Sistema

```mermaid
graph LR
    Usuario["👤 Usuario"]
    Sistema["🖥️ Sistema SBN-MCP"]
    BD["🗄️ Base de Datos"]
    Email["📧 Email"]
    Reports["📊 Reportes"]
    
    Usuario -->|Login/Datos| Sistema
    Sistema -->|Consultas/Updates| BD
    BD -->|Resultados| Sistema
    Sistema -->|Respuestas| Usuario
    Sistema -->|Verificaciones/Reset| Email
    Usuario -->|Solicita| Reports
    Sistema -->|Genera| Reports
    
    style Usuario fill:#e1f5ff
    style Sistema fill:#f3e5f5
    style BD fill:#fff3e0
    style Email fill:#f1f8e9
    style Reports fill:#fce4ec
```

## Nivel 1: Procesos Principales

```mermaid
graph TD
    A["Entrada: Usuario/Datos"]
    
    B["1.0 Autenticación"]
    C["2.0 Gestión de Bienes"]
    D["3.0 Gestión de Movimientos"]
    E["4.0 Reportes y Análisis"]
    F["5.0 Auditoría y Seguridad"]
    
    B -->|Datos válidos| C
    C -->|Cambios| D
    D -->|Datos procesados| E
    C -->|Acciones| F
    D -->|Acciones| F
    E -->|Datos| F
    F -->|Salida: Reports/Logs| G["Salida: Sistema Actualizado"]
    
    style A fill:#e1f5ff
    style B fill:#f3e5f5
    style C fill:#f3e5f5
    style D fill:#f3e5f5
    style E fill:#f3e5f5
    style F fill:#f3e5f5
    style G fill:#c8e6c9
```

## Nivel 2: Proceso 2.0 - Gestión de Bienes

```mermaid
graph TD
    A["Inicio: Crear/Editar Bien"]
    
    B["2.1 Ingreso de Datos<br/>bien/create.php"]
    C{Validar<br/>Datos?}
    D["2.2 Verificar Duplicados<br/>AJAX Validation"]
    E{¿Duplicados?}
    F["2.3 Generar Códigos<br/>Código Interno<br/>Código Sudebip"]
    G["2.4 Insertar/Actualizar<br/>en BD"]
    H["2.5 Procesar Imágenes<br/>Bien + Responsable"]
    I["2.6 Registrar Auditoría"]
    J["2.7 Crear Movimiento<br/>de Incorporación"]
    K["Salida: Bien Registrado"]
    L["Error: Mostrar Mensajes"]
    
    A --> B
    B --> C
    C -->|No| L
    C -->|Sí| D
    D --> E
    E -->|Sí| L
    E -->|No| F
    F --> G
    G --> H
    H --> I
    I --> J
    J --> K
    
    style A fill:#e1f5ff
    style K fill:#c8e6c9
    style L fill:#ffcdd2
    style B fill:#f3e5f5
    style D fill:#f3e5f5
    style F fill:#f3e5f5
    style G fill:#f3e5f5
    style H fill:#f3e5f5
    style I fill:#f3e5f5
    style J fill:#f3e5f5
```

## Nivel 2: Proceso 3.0 - Gestión de Movimientos

```mermaid
graph TD
    A["Inicio: Movimiento de Bien"]
    B["3.1 Seleccionar Bien<br/>y Tipo de Movimiento"]
    C["3.2 Definir Parámetros<br/>Área Origen/Destino<br/>Responsable<br/>Motivo"]
    D["3.3 Crear Registro<br/>de Movimiento"]
    E{Tipo de<br/>Movimiento?}
    
    E -->|Incorporación| F["3.4a Movimiento<br/>Auto-aprobado"]
    E -->|Traslado| G["3.4b Movimiento<br/>Pendiente Aprobación"]
    E -->|Desincorporación| H["3.4c Movimiento<br/>Pendiente Aprobación"]
    
    F --> I["3.5 Actualizar Estado Bien"]
    G --> J["3.6 Notificar Gerencia"]
    H --> J
    I --> K["3.7 Generar Acta"]
    J --> L["3.8 Registrar Auditoría"]
    K --> L
    L --> M["Salida: Movimiento Completado"]
    
    style A fill:#e1f5ff
    style M fill:#c8e6c9
    style D fill:#f3e5f5
    style I fill:#f3e5f5
    style J fill:#f3e5f5
    style K fill:#f3e5f5
    style L fill:#f3e5f5
```

## Nivel 2: Proceso 4.0 - Reportes y Análisis

```mermaid
graph TD
    A["Inicio: Generar Reporte"]
    B["4.1 Seleccionar Tipo<br/>BM-1 General<br/>BM-2 Clasificación<br/>BM-3 Estado<br/>BM-4 Responsable"]
    C["4.2 Definir Filtros<br/>Rango Fechas<br/>Área<br/>Estado<br/>Clasificación"]
    D["4.3 Consultar Base de Datos"]
    E["4.4 Procesar Datos<br/>Cálculos<br/>Agrupaciones<br/>Totalizaciones"]
    F["4.5 Generar Formato<br/>PDF o Excel"]
    G["4.6 Registrar Generación<br/>en Auditoría"]
    H["Salida: Reporte Disponible"]
    
    A --> B
    B --> C
    C --> D
    D --> E
    E --> F
    F --> G
    G --> H
    
    style A fill:#e1f5ff
    style H fill:#c8e6c9
    style B fill:#f3e5f5
    style C fill:#f3e5f5
    style D fill:#f3e5f5
    style E fill:#f3e5f5
    style F fill:#f3e5f5
    style G fill:#f3e5f5
```

## Nivel 2: Proceso 5.0 - Auditoría y Seguridad

```mermaid
graph TD
    A["Evento: Acción de Usuario"]
    B{Tipo de<br/>Acción?}
    
    B -->|CREATE/UPDATE/DELETE| C["5.1 Registrar en<br/>audit_logs"]
    B -->|LOGIN/LOGOUT| D["5.2 Registrar Acceso"]
    B -->|CAMBIO PERMISOS| E["5.3 Registrar Cambio<br/>de Rol"]
    
    C --> F["5.4 Capturar Detalles<br/>Usuario<br/>Tabla<br/>Cambios Anteriores<br/>Cambios Nuevos<br/>IP Address<br/>Timestamp"]
    D --> F
    E --> F
    
    F --> G["5.5 Almacenar en BD"]
    G --> H["5.6 Disponibilizar<br/>para Auditoría"]
    H --> I["Salida: Registro<br/>de Auditoría"]
    
    style A fill:#e1f5ff
    style I fill:#c8e6c9
    style C fill:#f3e5f5
    style D fill:#f3e5f5
    style E fill:#f3e5f5
    style F fill:#f3e5f5
    style G fill:#f3e5f5
    style H fill:#f3e5f5
```

## Almacenes de Datos (Detalles de Archivos/Tablas)

```
D1: Usuarios
    - id_usuario
    - cedula, username, email, password_hash
    - id_rol, cargo
    - created_at, updated_at

D2: Bienes
    - id_bien
    - codigo_sudebip, codigo_interno, nro_bien_ministerio
    - nombre, descripcion, serial, marca, modelo
    - id_tipo, id_area, id_estado, responsable_id
    - responsable_cedula, responsable_foto_path
    - imagen_path
    - valor_inicial, valor_residual, vida_util_anos
    - created_at, updated_at

D3: Movimientos
    - id_movimiento
    - bien_id, tipo_movimiento
    - area_origen_id, area_destino_id
    - usuario_solicita_id, usuario_aprueba_id
    - estado, fecha_solicitud, fecha_aprobacion
    - motivo, observaciones

D4: Auditoría
    - id_auditoria
    - usuario_id, accion (INSERT/UPDATE/DELETE)
    - tabla, registro_id
    - cambios_anterior (JSON)
    - cambios_nuevo (JSON)
    - fecha, ip_address

D5: Notificaciones
    - id_notificacion
    - usuario_id, tipo, titulo, mensaje
    - leida, created_at, read_at

D6: Áreas
    - id_area
    - nombre_area, edificio, piso
    - responsable_id, area_padre_id
    - activa
```

---

**Leyenda:**
- Las elipses azules (👤) representan actores externos (usuarios)
- Los rectángulos morados representan procesos
- Los cilindros naranjas (🗄️) representan almacenes de datos
- Las flechas representan flujo de información
