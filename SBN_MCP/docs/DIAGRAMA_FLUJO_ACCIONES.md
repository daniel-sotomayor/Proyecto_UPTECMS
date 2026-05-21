# Diagrama de Flujo de Acciones del Usuario

## 1. Flujo Principal del Sistema

```mermaid
flowchart TD
    A["🌐 Acceder a<br/>localhost/SBN_MCP"] --> B{¿Sesión<br/>Activa?}
    
    B -->|No| C["🔐 Pantalla Login"]
    B -->|Sí| D["📊 Dashboard Principal"]
    
    C --> E["👤 Ingresar credenciales"]
    E --> F["✓ Autenticación válida"]
    F --> G["🔄 ¿Cambiar contraseña?"]
    G -->|Sí| H["✏️ Cambiar clave"]
    G -->|No| D
    H --> D
    
    D --> I{¿Selecciona<br/>Módulo?}
    
    I -->|Inventario| J["📦 Ver bienes"]
    I -->|Control Mural| K["📍 Inventario por área"]
    I -->|Reportes| L["📊 Generar reportes"]
    I -->|Administración| M["⚙️ Gestión usuarios/roles"]
    I -->|Mi Perfil| N["👤 Editar perfil"]
    
    J --> O["🔍 Filtrar / Buscar"]
    O --> P["📄 Ver detalles"]
    P --> Q{¿Acción?}
    Q -->|Editar| R["✏️ Modificar bien"]
    Q -->|Registrar| S["➕ Nuevo bien"]
    Q -->|Eliminar| T["🗑️ Borrar bien"]
    Q -->|Ver Historial| U["📜 Movimientos"]
    
    R --> V["💾 Guardar"]
    S --> V
    T --> V
    
    K --> W["👆 Seleccionar área"]
    W --> X["📊 Mostrar bienes del área"]
    X --> Y{¿Acción?}
    Y -->|Verificar| Z["✅ Marcar verificados"]
    Y -->|Exportar| AA["📥 Descargar CSV"]
    Y -->|Reporte| AB["📊 Generar reporte"]
    
    L --> AC["📋 Seleccionar tipo<br/>BM-1, BM-2, BM-3, BM-4"]
    AC --> AD["🔧 Definir filtros"]
    AD --> AE["✓ Generar reporte"]
    AE --> AF{¿Formato?}
    AF -->|PDF| AG["📄 Descargar PDF"]
    AF -->|Excel| AH["📊 Descargar Excel"]
    
    M --> AI{¿Acción?}
    AI -->|Crear usuario| AJ["👤 Nuevo usuario"]
    AI -->|Editar usuario| AK["✏️ Modificar datos"]
    AI -->|Ver auditoría| AL["📝 Historial acciones"]
    
    AJ --> AM["💾 Guardar"]
    AK --> AM
    
    N --> AN["✏️ Editar información"]
    AN --> AO["🔐 Cambiar contraseña"]
    AO --> AP["💾 Guardar"]
    
    V --> AQ["✅ Acción completada"]
    Z --> AQ
    AA --> AQ
    AB --> AQ
    AG --> AQ
    AH --> AQ
    AM --> AQ
    AP --> AQ
    
    AQ --> AR{¿Continuar?}
    AR -->|Sí| D
    AR -->|No| AS["🚪 Logout"]
    AS --> AT["🌐 Sesión cerrada"]
    
    style A fill:#e1f5ff
    style AT fill:#ffcdd2
    style AQ fill:#c8e6c9
    style C fill:#fff9c4
    style D fill:#e1f5ff
```

## 2. Flujo Detallado: Registro de Bien

```mermaid
flowchart TD
    A["📦 Inicio:<br/>Registrar Bien"] --> B["📋 Mostrar formulario<br/>6 pasos"]
    
    B --> C["Paso 1:<br/>Clasificación"]
    C --> C1["✏️ Seleccionar tipo<br/>de bien"]
    C1 --> C2["✏️ Ingresar código<br/>ministerio"]
    C2 --> C3{"¿Permiso<br/>asignar Nro?"}
    C3 -->|Sí| C4["✏️ Ingresar<br/>Nro. Bien"]
    C3 -->|No| C5["ℹ️ Será asignado<br/>por Gerencia"]
    C4 --> C6["✏️ Seleccionar<br/>Estado"]
    C5 --> C6
    C6 --> C7["➡️ Siguiente"]
    
    C7 --> D["Paso 2:<br/>Identificación"]
    D --> D1["✏️ Nombre bien"]
    D1 --> D2["✏️ Descripción"]
    D2 --> D3["✏️ Marca, Modelo,<br/>Serial, Color"]
    D3 --> D4["✏️ Año, Condición"]
    D4 --> D5["📸 Foto bien<br/>opcional"]
    D5 --> D6["➡️ Siguiente"]
    
    D6 --> E["Paso 3:<br/>Ubicación C.I.N"]
    E --> E1["✏️ Seleccionar<br/>área"]
    E1 --> E2["✏️ Oficina,<br/>Posición"]
    E2 --> E3["🔄 Sistema genera<br/>C.I.N"]
    E3 --> E4["➡️ Siguiente"]
    
    E4 --> F["Paso 4:<br/>Responsable"]
    F --> F1["✏️ Seleccionar<br/>responsable<br/>opcional"]
    F1 --> F2["✏️ Cédula<br/>opcional"]
    F2 --> F3["📸 Foto responsable<br/>opcional"]
    F3 --> F4["➡️ Siguiente"]
    
    F4 --> G["Paso 5:<br/>Datos Económicos"]
    G --> G1["✏️ Valor unitario<br/>defecto: 0.0"]
    G1 --> G2["✏️ Valor residual"]
    G2 --> G3["✏️ Vida útil<br/>defecto: 10 años"]
    G3 --> G4["✏️ Factura, Fecha"]
    G4 --> G5["➡️ Siguiente"]
    
    G5 --> H["Paso 6:<br/>Observaciones"]
    H --> H1["✏️ Notas<br/>opcionales"]
    H1 --> H2["🔘 Guardar Bien"]
    
    H2 --> I["🔍 Backend: Validar"]
    I --> I1{Validar<br/>CSRF?}
    I1 -->|No| I2["❌ Error seguridad"]
    I1 -->|Sí| I3{Validar<br/>Datos?}
    I3 -->|Inválido| I4["❌ Mostrar errores<br/>ir al paso con error"]
    I3 -->|Válido| I5{Verificar<br/>Duplicados?}
    I5 -->|Duplicado| I6["❌ Mostrar<br/>duplicado"]
    I5 -->|No| J["✅ Generar códigos"]
    
    J --> J1["💾 Insertar bien"]
    J1 --> J2["📸 Subir imágenes"]
    J2 --> J3["📝 Registrar auditoría"]
    J3 --> J4["🔗 Crear movimiento<br/>incorporación"]
    J4 --> J5["✅ Éxito"]
    
    J5 --> K["🎉 Modal de éxito"]
    K --> L["🔄 Redirigir a inventario"]
    
    I2 --> M["🚫 Fin: Error"]
    I4 --> M
    I6 --> M
    L --> N["✅ Fin: Bien registrado"]
    
    style A fill:#c8e6c9
    style N fill:#c8e6c9
    style M fill:#ffcdd2
    style J5 fill:#e1f5ff
```

## 3. Flujo de Búsqueda y Filtrado en Inventario

```mermaid
flowchart TD
    A["🔍 Inicio:<br/>Ver Inventario"] --> B["📊 Mostrar tabla<br/>con 20 bienes/página"]
    
    B --> C{¿Usar<br/>Filtros?}
    
    C -->|No| D["📄 Ver página 1"]
    C -->|Sí| E["🔧 Panel de filtros"]
    
    E --> F["✏️ Búsqueda general<br/>nombre, código, serial"]
    F --> G["📋 Filtrar por<br/>Clasificación"]
    G --> H["🎨 Filtrar por<br/>Estado"]
    H --> I["🏢 Filtrar por<br/>Edificio"]
    I --> J["📍 Filtrar por<br/>Área"]
    J --> K["🔘 Aplicar filtros"]
    
    K --> L["🔄 Backend: Consultar BD"]
    L --> M["📊 Resultados encontrados"]
    
    M --> N{¿Resultados?}
    N -->|No| O["⚠️ Sin resultados"]
    N -->|Sí| P["📄 Mostrar tabla<br/>con resultados"]
    
    P --> Q{¿Paginación?}
    Q -->|Página anterior| R["📄 Página anterior"]
    Q -->|Página siguiente| S["📄 Página siguiente"]
    Q -->|Ir a página| T["✏️ Ingresar #página"]
    
    R --> P
    S --> P
    T --> P
    
    P --> U{¿Acción<br/>sobre bien?}
    U -->|Clic nombre| V["👁️ Ver detalles"]
    U -->|Editar| W["✏️ Editar bien"]
    U -->|Eliminar| X["🗑️ Eliminar bien"]
    
    V --> Y["✅ Fin: Viendo detalles"]
    W --> Y
    X --> Y
    O --> Y
    
    style A fill:#c8e6c9
    style Y fill:#c8e6c9
    style P fill:#e1f5ff
    style O fill:#fff9c4
```

## 4. Flujo de Logout y Cierre de Sesión

```mermaid
flowchart TD
    A["👤 Usuario en<br/>Sistema"] --> B["🔘 Haz clic en<br/>nombre usuario"]
    B --> C["📋 Menú desplegable"]
    C --> D["🚪 Logout"]
    D --> E["🔄 Backend: Destruir sesión"]
    E --> F["🌐 Redirigir a login"]
    F --> G["✅ Sesión cerrada"]
    G --> H["🌐 Mostrar pantalla<br/>login"]
    H --> I["✅ Fin"]
    
    style A fill:#e1f5ff
    style I fill:#ffcdd2
    style G fill:#c8e6c9
```

---

**Leyenda de Símbolos:**
- 🟢 Inicio/Final verde
- 📦 Proceso
- ✏️ Entrada de usuario
- ✅ Confirmación/Éxito
- ❌ Error/Rechazo
- 💾 Almacenamiento
- 🔄 Proceso backend
- 📊 Datos/Tabla
- 🔍 Búsqueda/Filtro
- ➡️ Siguiente paso
- ⚠️ Advertencia
- 🎉 Evento especial
