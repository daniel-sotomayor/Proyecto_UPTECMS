# Manual de Usuario - Registro de Bienes Nacionales

## 1. Acceso al Sistema

1. Abre tu navegador web y ve a: `http://localhost/SBN_MCP/public/`
2. Ingresa tu usuario y contraseña
3. En el primer login, se te pedirá cambiar tu contraseña

## 2. Registro de un Bien

### Paso 1: Acceder a Registro de Bienes
- Desde el menú lateral, haz clic en **Inventario**
- Luego haz clic en el botón **Registrar Bien** (esquina superior derecha)

### Paso 2: Rellenar Clasificación (Paso 1/6)
- **Clasificación (Grupo)**: Selecciona el tipo de bien (Equipos de Oficina, Equipos Quirúrgicos, etc.)
- **Código Ministerio**: Opcional. Código único del Ministerio de Salud (ej: MS-2026-001)
- **Nro. de Bien (Ministerio)**: 
  - Si tienes permiso, ingresa el número (6-10 caracteres alfanuméricos)
  - Si NO tienes permiso, será asignado por Gerencia de Bienes Nacionales
  - Si el bien NO tiene número, marca la casilla **S/N — Sin número asignado**
- **Estado**: Selecciona el estado inicial (Operativo, Inoperativo, En Resguardo, etc.)

### Paso 3: Identificación del Bien (Paso 2/6)
- **Nombre del Bien**: Nombre descriptivo (requerido, mínimo 3 caracteres)
- **Descripción**: Detalles adicionales
- **Marca, Modelo, Color, Año de Fabricación**: Datos técnicos
- **Serial/Nro. Serie**: Número único del fabricante (si existe)
- **Cantidad**: Número de unidades (por defecto 1)
- **Condición Inicial**: Nuevo, Bueno, Regular, Malo
- **Foto del Bien**: Sube una imagen (JPG, PNG) - opcional

### Paso 4: Ubicación - C.I.N (Paso 3/6)
- **Área/Departamento**: Selecciona el área donde se ubicará el bien
- **Oficina/Sub-área**: Nombre específico de la oficina
- **Nro. de Posición**: Ubicación exacta dentro de la oficina
- El sistema genera automáticamente el **C.I.N (Código de Identificación de Ubicación)**

### Paso 5: Responsable del Bien (Paso 4/6) - OPCIONAL
- **Responsable**: Selecciona la persona a cargo del bien
  - Si no asignas, el bien queda **"sin asignar"**
- **Número de Cédula**: Cédula del responsable (ej: V-12345678)
- **Foto del Responsable**: Sube una foto de la persona - opcional

### Paso 6: Datos Económicos (Paso 5/6)
- **Valor Unitario (Bs.)**: Costo del bien en bolívares
  - Si dejas en blanco, por defecto es **0.00**
- **Valor Residual (Bs.)**: Valor al final de su vida útil
- **Vida Útil (años)**: Años esperados de uso (por defecto 10)
- **Nro. Factura**: Número de documento de compra
- **Fecha de Adquisición**: Cuándo se compró el bien

### Paso 7: Observaciones Adicionales (Paso 6/6)
- **Observaciones**: Anotaciones especiales sobre el bien

### Paso 8: Guardar
- Haz clic en **✓ Guardar Bien**
- Si todo es correcto, verás un mensaje de éxito
- El bien quedará registrado en el inventario

## 3. Validaciones Automáticas

El sistema valida automáticamente:
- **Nro. de Bien**: No puede estar duplicado
- **Serial**: No puede estar duplicado
- **Código Ministerio**: No puede estar duplicado
- **Campos requeridos**: Todos deben completarse antes de guardar

## 4. Editar un Bien

1. Ve a **Inventario**
2. Busca el bien o filtra por tipo, estado, área
3. Haz clic en el nombre del bien para ver detalles
4. Haz clic en **Editar** (botón en la parte superior)
5. Realiza los cambios y haz clic en **Guardar**

## 5. Ver Detalles de un Bien

1. Ve a **Inventario**
2. Haz clic en el nombre del bien
3. Verás:
   - Todos los datos del bien
   - Responsable (si está asignado)
   - Historial de movimientos
   - Foto del bien (si existe)

## 6. Buscar y Filtrar Bienes

1. En **Inventario**, usa los filtros:
   - **Búsqueda**: Nombre, código, serial, marca
   - **Clasificación**: Tipo de bien
   - **Estado**: Estado actual del bien
   - **Edificio**: Ubicación
   - **Piso**: Nivel del edificio

## 7. Errores Comunes

| Error | Causa | Solución |
|-------|-------|----------|
| "Este número de bien ya está registrado" | Nro. duplicado | Usa un número diferente o S/N |
| "Este serial ya está registrado" | Serial duplicado | Verifica el serial ingresado |
| "Campo requerido" | Falta un campo obligatorio | Completa el campo |
| "Formato inválido" | Formato de entrada incorrecto | Usa el formato solicitado |

---

**¿Necesitas ayuda? Contacta a la Gerencia de Bienes Nacionales.**
