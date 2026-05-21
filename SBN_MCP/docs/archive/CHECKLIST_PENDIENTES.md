# CHECKLIST — TAREAS PENDIENTES DE IMPLEMENTACIÓN
# Sistema de Gestión de Bienes Nacionales — MCP
# Generado: 01/04/2026
# ============================================================

## PRIORIDAD ALTA — Bloquea funcionalidad del sistema

### Vistas de error (evitan errores fatales)
- [x] `app/views/errors/404.php` — Página de error 404
- [x] `app/views/errors/403.php` — Página de acceso denegado

### Módulo Movimientos (rutas activas sin vista)
- [x] `app/views/movimiento/show.php` — Vista detalle de acta/movimiento
  - Mostrar datos completos del movimiento (tipo, estado, fechas, motivo)
  - Mostrar datos del bien asociado (nombre, código, descripción)
  - Mostrar áreas origen y destino
  - Mostrar usuarios que solicitaron y aprobaron
  - Botones de aprobar/rechazar si el estado es pendiente y el rol es admin o gerencia_bn
  - Botón de volver al listado
  - Incluir sidebar

- [x] `app/views/movimiento/create.php` — Formulario de nueva acta
  - Selector de tipo de acta (incorporación, traslado, desincorporación)
  - Selector de bien (con filtro/búsqueda)
  - Selector de área origen (opcional, precargada con área actual del bien)
  - Selector de área destino (obligatorio para traslados)
  - Campo de motivo (obligatorio)
  - Campo de observaciones (opcional)
  - Lógica JS para mostrar/ocultar campos según tipo de acta
  - Envío por fetch POST con CSRF
  - Mensajes de éxito/error
  - Incluir sidebar

### Módulo Reportes BM-1 (query lista, falta vista)
- [x] `app/views/reportes/bm1.php` — Vista reporte inventario activo
  - Título "Reporte BM-1 — Inventario de Bienes Activos"
  - Tabla con columnas: Código Interno, Nro. Bien, Nombre, Clasificación, Estado, Área, Valor
  - Totales al final: cantidad de bienes, valor total
  - Botón de volver a la lista de reportes
  - (Futuro) Botón de exportar a PDF/Excel
  - Incluir sidebar

### Assets del sistema
- [ ] `public/img/logo-mcp.svg` — Logo institucional en SVG
  - Referenciado en: `app/views/auth/login.php` línea 16
  - Referenciado en: `app/views/public/index.php` línea 7
  - Dimensiones recomendadas: 200x60px o similar
  - Alternativa temporal: usar texto "MCP" o PNG en lugar de SVG

- [ ] `public/img/favicon.ico` — Favicon del sitio
  - Referenciado en: `app/views/layout/header.php` línea 10
  - Alternativa temporal: eliminar la línea del favicon si no hay icono


## PRIORIDAD MEDIA — Reportes incompletos

### Reporte BM-2 — Bienes desincorporados
- [x] Implementar query SQL en `ReporteController::bm2()`
- [x] `app/views/reportes/bm2.php` — Vista reporte desincorporados

### Reporte BM-3 — Movimientos del período
- [x] Implementar query SQL en `ReporteController::bm3()`
- [x] `app/views/reportes/bm3.php` — Vista movimientos del período

### Reporte BM-4 — Resumen ejecutivo
- [x] Implementar query SQL en `ReporteController::bm4()`
- [x] `app/views/reportes/bm4.php` — Vista resumen ejecutivo


## PRIORIDAD BAJA — Páginas públicas del sitio

- [ ] `app/views/public/nosotros.php` — Página Nosotros
  - Información sobre la Maternidad Concepción Palacios
  - Misión y visión
  - Historia breve de la institución
  - Incluir header y footer públicos (igual que `public/index.php`)

- [ ] `app/views/public/servicios.php` — Página Servicios
  - Descripción de los servicios del hospital
  - Servicios obstétricos, ginecológicos, neonatales, etc.
  - Incluir header y footer públicos

- [ ] `app/views/public/contacto.php` — Página Contacto con formulario
  - Información de contacto (dirección, teléfonos, email)
  - Formulario de contacto: nombre, email, mensaje
  - Token CSRF en el formulario
  - Envío por fetch POST a `/contacto`
  - Mensajes de éxito/error
  - Incluir header y footer públicos

- [ ] Implementar envío SMTP en `PublicController::handleContactForm()`
  - Configurar PHPMailer o mail() nativo de PHP
  - Crear configuración SMTP en `.env` o `config/app.php`
  - Validar y enviar el mensaje del formulario
  - Responder con JSON de éxito/error


## PRIORIDAD BAJA — Mejoras funcionales

### Paginación
- [ ] Implementar paginación server-side en `BienController::index()`
  - Actualmente carga TODOS los bienes sin límite
  - Agregar parámetro `page` y `per_page` (default 20)
  - Agregar query COUNT separada para total
  - Agregar controles de paginación en `bien/index.php`
  - Preservar filtros al paginar

- [ ] Implementar paginación en `MovimientoController::index()`
  - Mismo patrón que bienes

### Exportación de reportes
- [ ] Implementar exportación PDF para reportes
  - Evaluar librería: TCPDF o DOMPDF
  - Agregar a `composer.json`
  - Crear método `exportPdf()` en cada reporte
  - Botón de exportar en cada vista de reporte

- [ ] Implementar exportación Excel/CSV para reportes
  - Evaluar librería: PhpSpreadsheet
  - O implementar CSV nativo con `fputcsv()`
  - Crear método `exportCsv()` en cada reporte

### Módulo de mantenimientos
- [ ] Crear `app/controllers/MantenimientoController.php`
  - CRUD completo de mantenimientos
  - Listar mantenimientos con filtros (bien, tipo, fecha, proveedor)
  - Crear mantenimiento vinculado a un bien
  - Editar mantenimiento
  - Marcar como ejecutado con fecha real
  - Programar próximo mantenimiento

- [ ] Definir rutas en `app/core/App.php` para mantenimientos
  - GET /mantenimientos — Listar
  - GET /mantenimientos/:id — Ver detalle
  - GET /mantenimientos/nuevo — Formulario
  - POST /mantenimientos — Guardar
  - GET /mantenimientos/:id/editar — Editar
  - PUT /mantenimientos/:id — Actualizar

- [ ] Crear vistas de mantenimientos
  - `app/views/mantenimiento/index.php` — Listado con filtros
  - `app/views/mantenimiento/show.php` — Detalle
  - `app/views/mantenimiento/create.php` — Formulario de creación
  - `app/views/mantenimiento/edit.php` — Formulario de edición

- [ ] Agregar enlace a mantenimientos en sidebar (`app/views/partials/sidebar.php`)

### Upload de imágenes de bienes
- [ ] Implementar upload de imagen en `BienController::store()`
  - Validar tipo de archivo (jpg, png, webp)
  - Validar tamaño máximo (5MB)
  - Generar nombre único para el archivo
  - Guardar en `uploads/bienes/`
  - Actualizar campo `imagen_path` en BD

- [ ] Implementar upload de imagen en `BienController::update()`
  - Mismo proceso, reemplazar imagen existente si se sube nueva

- [ ] Mostrar imagen en `app/views/bien/show.php`
  - Si existe `imagen_path`, mostrar imagen
  - Si no, mostrar placeholder

### Notificaciones automáticas
- [ ] Sistema de alertas de inventario bajo
  - Definir umbral de cantidad mínima en configuración
  - Mostrar alerta en dashboard cuando haya bienes bajo el umbral

- [ ] Alertas de productos sin movimiento
  - Detectar bienes sin movimientos en X meses
  - Mostrar en dashboard

### Gráficos en dashboard
- [ ] Integrar librería de gráficos (Chart.js vía CDN)
  - Gráfico de barras: bienes por estado
  - Gráfico de pastel: bienes por edificio
  - Gráfico de líneas: movimientos por mes (últimos 6 meses)

### Configuración del sistema
- [ ] Crear `app/controllers/ConfiguracionController.php`
  - Leer valores de la tabla `configuracion`
  - Permitir editar valores (solo admin)
  - Aplicar configuración: tiempo de sesión, intentos máximos de login, etc.

- [ ] Crear vista `app/views/configuracion/index.php`
  - Formulario de edición de parámetros
  - Solo accesible para administrador

- [ ] Definir ruta en `app/core/App.php`
  - GET /configuracion — Ver/editar configuración (solo admin)


## MEJORAS DE CÓDIGO (deuda técnica)

- [ ] Extraer `logAudit()` a un trait o servicio compartido
  - Actualmente duplicado en: AuthController, AdminController, BienController, MovimientoController
  - Crear `app/helpers/AuditTrait.php` o `app/services/AuditService.php`

- [ ] Crear middleware de autorización por recurso
  - Verificar que un usuario solo pueda modificar sus propios registros
  - Aplicar en edit/update de bienes y movimientos

- [ ] Implementar sistema de configuración de entorno
  - Crear `.env.example` con todas las variables documentadas
  - Validar variables requeridas al inicio de la aplicación

- [ ] Implementar logging estructurado
  - Crear clase `app/core/Logger.php`
  - Niveles: debug, info, warning, error, critical
  - Formato JSON con timestamp, level, message, context
  - Archivo de log rotado por día

- [ ] Tests unitarios
  - Evaluar PHPUnit
  - Tests para: autenticación, validaciones, generación de códigos, permisos
  - Mínimo 80% de cobertura en lógica de negocio


## NOTAS

- Las vistas marcadas con ✅ ya fueron creadas (errors/404.php, errors/403.php)
- Los controladores para movimientos y reportes YA tienen la lógica implementada,
  solo faltan las vistas HTML
- Las páginas públicas (nosotros, servicios, contacto) son informativas,
  contenido estático, no requieren lógica de BD compleja
- El módulo de mantenimientos es completamente nuevo (tabla existe en BD)
- La paginación es una mejora de rendimiento, no bloquea funcionalidad
- La exportación PDF/Excel es una mejora funcional solicitada por usuarios

## ESTADÍSTICAS

Total de tareas: 47
Completadas: 11 (errors/404, errors/403, movimiento/show, movimiento/create, reportes/bm1, ReporteController::bm2, reportes/bm2, ReporteController::bm3, reportes/bm3, ReporteController::bm4, reportes/bm4)
Pendientes: 36

Por prioridad:
  ALTA:   4 tareas pendientes
  MEDIA:  12 tareas pendientes
  BAJA:   29 tareas pendientes
