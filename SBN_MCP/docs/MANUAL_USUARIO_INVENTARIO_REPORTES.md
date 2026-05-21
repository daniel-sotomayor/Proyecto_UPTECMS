# Manual de Usuario - Inventario y Reportes

## 1. Vista del Inventario Global

### Acceso
- Desde el menú lateral, haz clic en **Inventario**
- Verás una tabla con todos los bienes registrados

### Información Mostrada
- **Código Sudebip**: Identificador único del sistema
- **Clasificación**: Tipo de bien
- **Nombre**: Nombre del bien
- **Estado**: Color indicador del estado actual
- **Área**: Departamento donde se ubica
- **Responsable**: Persona a cargo (si existe)

### Búsqueda y Filtros
- **Búsqueda General**: Encuentra bienes por nombre, código, serial, marca
- **Clasificación**: Filtra por tipo de bien
- **Estado**: Filtra por estado (Operativo, Inoperativo, etc.)
- **Edificio**: Filtra por ubicación (Edificio Principal, Anexo, etc.)

### Paginación
- Los resultados se muestran por página (20 bienes por página)
- Usa las flechas de navegación para cambiar de página

## 2. Control Mural - Inventario por Área

### Acceso
- Desde el menú lateral, haz clic en **Control Mural**
- Verás una lista de todas las áreas del hospital

### Ver Inventario de un Área
1. Haz clic en el nombre del área o en **Ver área**
2. Verás una tabla con todos los bienes de esa área
3. Información visible:
   - Código Sudebip
   - Nombre del bien
   - Clasificación
   - Estado
   - Responsable

### Exportar Inventario por Área
1. En la vista de un área, haz clic en **Descargar CSV**
2. Se descargará un archivo con todos los bienes del área
3. Puedes importarlo en Excel o Google Sheets

### Usar Control Mural para Verificación Física
- El Control Mural es ideal para:
  - Verificaciones semestrales
  - Auditorías de área específica
  - Comparar inventario físico con el sistema
  - Identificar bienes faltantes o mal ubicados

## 3. Reportes

### Acceso a Reportes
- Desde el menú lateral, haz clic en **Reportes** (solo para roles autorizados)
- O desde la vista de Inventario, haz clic en el botón **Reportes**

### Tipos de Reportes

#### Reporte BM-1: Inventario General
- Muestra todos los bienes de la institución
- Incluye: Código, Nombre, Estado, Área, Responsable, Valor
- Útil para auditoría anual

#### Reporte BM-2: Por Clasificación
- Agrupa bienes por tipo (Equipos Quirúrgicos, Alojamiento, etc.)
- Muestra cantidad y valor por clasificación
- Útil para análisis de inversión

#### Reporte BM-3: Por Estado
- Agrupa bienes por estado (Operativo, Inoperativo, etc.)
- Identifica bienes que necesitan mantenimiento
- Muestra cantidad y valor por estado

#### Reporte BM-4: Por Responsable
- Agrupa bienes asignados a cada persona
- Útil para auditoría de responsabilidades
- Muestra cantidad y valor por responsable

### Generar un Reporte
1. Selecciona el tipo de reporte
2. Elige fechas de inicio y fin (si aplica)
3. Haz clic en **Generar Reporte**
4. Verás el reporte en pantalla
5. Para descargar:
   - Haz clic en **Descargar PDF** o **Descargar Excel**

### Filtros en Reportes
- **Área**: Mostrar solo bienes de un área específica
- **Estado**: Mostrar solo bienes en cierto estado
- **Clasificación**: Mostrar solo cierto tipo de bien
- **Rango de Fechas**: Bienes registrados en un período específico

## 4. Exportación de Datos

### Formatos Disponibles
- **CSV**: Archivo de texto separado por comas (abre en Excel)
- **PDF**: Documento para imprimir o archivar

### Cómo Exportar
1. Desde la vista de Inventario o Reportes
2. Haz clic en **Descargar CSV** o **Descargar PDF**
3. Elige carpeta de guardado y listo

### Usar Exportación para
- Backups manuales
- Análisis en Excel
- Impresión de reportes
- Envío por correo a Gerencia

## 5. Movimientos y Trazabilidad

### Ver Historial de Movimientos de un Bien
1. Ve a **Inventario**
2. Haz clic en el bien
3. Desplázate hacia abajo a **Historial de Movimientos**
4. Verás todos los cambios: traslados, cambios de estado, cambios de responsable

## 6. Errores Comunes

| Error | Causa | Solución |
|-------|-------|----------|
| "Sin resultados" | No hay bienes con esos filtros | Modifica los filtros o búsqueda |
| "Acceso denegado" | Tu rol no tiene permiso | Solicita permisos al Administrador |
| "Reporte vacío" | No hay datos en ese período | Verifica las fechas de filtrado |

---

**¿Necesitas ayuda? Contacta a la Gerencia de Bienes Nacionales.**
