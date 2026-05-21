# Diagrama de Procesos - BPMN

## Proceso 1: Flujo de Autenticación

```mermaid
graph TD
    A["🟢 Inicio:<br/>Usuario accede<br/>a la aplicación"]
    B["✏️ Ingresa usuario<br/>y contraseña"]
    C{Validar<br/>Credenciales}
    D["❌ Error:<br/>Mostrar mensaje"]
    E{Intentos<br/>Fallidos > 3?}
    F["🔐 Bloquear usuario<br/>15 minutos"]
    G{"¿Primera<br/>vez?"}
    H["✏️ Cambiar contraseña"]
    I["🟢 Acceso concedido<br/>Crear sesión"]
    J["📊 Mostrar Dashboard"]
    K["🔴 Fin: Sesión activa"]
    
    A --> B
    B --> C
    C -->|Incorrecto| D
    D --> E
    E -->|Sí| F
    F --> K
    E -->|No| B
    C -->|Correcto| G
    G -->|Sí| H
    H --> I
    G -->|No| I
    I --> J
    J --> K
    
    style A fill:#c8e6c9
    style K fill:#ffcdd2
    style I fill:#e1f5ff
    style F fill:#fff9c4
```

## Proceso 2: Flujo de Registro de Bien

```mermaid
graph TD
    A["🟢 Inicio:<br/>Usuario accede a<br/>Registrar Bien"]
    B["📋 Mostrar formulario<br/>multietapa"]
    C["✏️ Paso 1:<br/>Clasificación"]
    D["✏️ Paso 2:<br/>Identificación"]
    E["✏️ Paso 3:<br/>Ubicación C.I.N"]
    F["✏️ Paso 4:<br/>Responsable"]
    G["✏️ Paso 5:<br/>Datos Económicos"]
    H["✏️ Paso 6:<br/>Observaciones"]
    I["📤 Usuario envía<br/>formulario"]
    J{Validar<br/>Datos}
    K["❌ Mostrar errores<br/>en pasos específicos"]
    L{¿Duplicados?}
    M["❌ Mostrar mensaje<br/>duplicado"]
    N["🔄 Generar códigos<br/>Interno + Sudebip"]
    O["💾 Insertar en BD"]
    P["📸 Procesar imágenes<br/>Bien + Responsable"]
    Q["📝 Registrar auditoría"]
    R["🔗 Crear movimiento<br/>de incorporación"]
    S["✅ Mostrar éxito"]
    T["🟢 Fin: Bien registrado"]
    
    A --> B
    B --> C --> D --> E --> F --> G --> H
    H --> I
    I --> J
    J -->|Inválido| K
    K --> C
    J -->|Válido| L
    L -->|Sí| M
    M --> C
    L -->|No| N
    N --> O
    O --> P
    P --> Q
    Q --> R
    R --> S
    S --> T
    
    style A fill:#c8e6c9
    style T fill:#ffcdd2
    style S fill:#e1f5ff
    style K fill:#ffcdd2
    style M fill:#ffcdd2
    style N fill:#fff9c4
```

## Proceso 3: Flujo de Movimiento de Bien

```mermaid
graph TD
    A["🟢 Inicio:<br/>Crear movimiento"]
    B["🔍 Seleccionar bien"]
    C["📍 Seleccionar tipo<br/>Traslado/Desincorp/Asign"]
    D{Tipo de<br/>Movimiento?}
    
    D -->|Incorporación| E["✅ Auto-aprobado"]
    D -->|Traslado| F["⏳ Espera aprobación"]
    D -->|Desincorporación| G["⏳ Espera aprobación"]
    D -->|Asignación| H["⏳ Espera aprobación"]
    
    E --> I["🔄 Definir parámetros<br/>Origen, Destino<br/>Responsable, Motivo"]
    F --> I
    G --> I
    H --> I
    
    I --> J["💾 Crear registro<br/>en movimientos"]
    J --> K["📊 Actualizar estado<br/>del bien"]
    K --> L{Requiere<br/>Aprobación?}
    
    L -->|Sí| M["📢 Notificar a Gerencia"]
    L -->|No| N["🔗 Generar acta"]
    
    M --> O["⏳ Pendiente revisión"]
    O --> P{Gerencia<br/>Aprueba?}
    P -->|Sí| N
    P -->|No| Q["❌ Movimiento rechazado"]
    Q --> R["🔔 Notificar usuario"]
    
    N --> S["📝 Registrar auditoría"]
    S --> T["✅ Movimiento completado"]
    T --> U["🟢 Fin"]
    R --> U
    
    style A fill:#c8e6c9
    style U fill:#ffcdd2
    style T fill:#e1f5ff
    style Q fill:#ffcdd2
```

## Proceso 4: Flujo de Generación de Reportes

```mermaid
graph TD
    A["🟢 Inicio:<br/>Usuario solicita<br/>reporte"]
    B["📊 Mostrar opciones<br/>BM-1, BM-2, BM-3, BM-4"]
    C["✏️ Seleccionar tipo"]
    D["🔧 Definir filtros<br/>Fechas, Área, Estado"]
    E["✏️ Usuario confirma"]
    F["🔍 Consultar BD<br/>SELECT ..."]
    G["🧮 Procesar datos<br/>Cálculos<br/>Agrupaciones"]
    H["📄 Generar formato<br/>PDF o Excel"]
    I{¿Datos<br/>Encontrados?}
    J["⚠️ Sin resultados<br/>con esos filtros"]
    K["📝 Registrar en auditoría"]
    L["📥 Descargar o enviar"]
    M["✅ Reporte disponible"]
    N["🟢 Fin"]
    
    A --> B
    B --> C
    C --> D
    D --> E
    E --> F
    F --> G
    G --> H
    H --> I
    I -->|No| J
    J --> N
    I -->|Sí| K
    K --> L
    L --> M
    M --> N
    
    style A fill:#c8e6c9
    style N fill:#ffcdd2
    style M fill:#e1f5ff
    style J fill:#fff9c4
```

## Proceso 5: Flujo de Control Mural (Inventario por Área)

```mermaid
graph TD
    A["🟢 Inicio:<br/>Acceder a<br/>Control Mural"]
    B["📍 Mostrar lista<br/>de áreas"]
    C["👆 Seleccionar área"]
    D["📊 Mostrar bienes<br/>del área"]
    E{Quiere<br/>Verificar?}
    F["✅ Marcar bienes<br/>verificados"]
    G["📝 Registrar<br/>verificación"]
    H{"Quiere<br/>Exportar?"}
    I["📥 Descargar CSV"]
    J{"Quiere<br/>Generar<br/>Reporte?"}
    K["📊 Generar reporte<br/>del área"]
    L["✅ Completado"]
    M["🟢 Fin"]
    
    A --> B
    B --> C
    C --> D
    D --> E
    E -->|Sí| F
    F --> G
    E -->|No| H
    G --> H
    H -->|Sí| I
    H -->|No| J
    I --> J
    J -->|Sí| K
    J -->|No| L
    K --> L
    L --> M
    
    style A fill:#c8e6c9
    style M fill:#ffcdd2
    style L fill:#e1f5ff
```

---

**Leyenda:**
- 🟢 Inicio/Fin
- 📋 Proceso
- ✏️ Entrada de usuario
- ✅ Confirmación
- ❌ Error/Rechazo
- 💾 Almacenamiento
- 📊 Reporte
- 🔔 Notificación
- ⏳ Espera
- 📝 Registro
