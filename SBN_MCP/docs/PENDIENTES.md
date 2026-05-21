# Tareas Pendientes
## Sistema de Gestión de Bienes Nacionales - SBN_MCP

---

## Estado General

**Versión Actual:** 1.1.0 (Producción)

**Estado:** ✅ **COMPLETO** - Todas las funcionalidades implementadas

---

## Resumen de Implementación

### ✅ Completado - Páginas Públicas
- [x] Logo institucional SVG (`public/img/logo-mcp.svg`)
- [x] Favicon del sitio (`public/img/favicon.svg`)
- [x] Página Nosotros (`app/views/public/nosotros.php`)
- [x] Página Servicios (`app/views/public/servicios.php`)
- [x] Página Contacto (`app/views/public/contacto.php`)
- [x] Envío de formulario de contacto con validación CSRF

### ✅ Completado - Exportación de Reportes
- [x] Exportación PDF de BM-1 (Inventario Activo)
- [x] Exportación PDF de BM-2 (Bienes Desincorporados)
- [x] Exportación PDF de BM-3 (Movimientos)
- [x] Exportación PDF de BM-4 (Resumen Ejecutivo)
- [x] Exportación Excel (.xlsx) de todos los reportes
- [x] Exportación CSV de todos los reportes
- [x] Botones de exportación en vistas de reportes

### ✅ Completado - Dashboard con Gráficos
- [x] Integración de Chart.js
- [x] Gráfico de dona: Distribución por Estado
- [x] Gráfico de barras: Bienes por Edificio
- [x] Métricas en tiempo real
- [x] Tablas de bienes y movimientos recientes

---

## Roadmap de Versiones Futuras

### Baja Prioridad - Optimizaciones

- [ ] **Optimización de Performance**
  - Implementar caching con Redis/Memcached
  - Lazy loading de imágenes
  - Compresión de assets (CSS/JS)

- [ ] **Mejoras de Accesibilidad**
  - Auditoría WCAG 2.1 AA
  - Mejoras en navegación por teclado
  - ARIA labels completos

- [ ] **Documentación de Código**
  - Comentarios PHPDoc en todos los métodos
  - Documentación de API con Swagger/OpenAPI

---

## Roadmap de Versiones

### Versión 1.2.0 (Próxima)
- [ ] Exportación PDF de reportes
- [ ] Exportación Excel de reportes
- [ ] Dashboard con gráficos
- [ ] API REST documentada

### Versión 1.3.0 (Futura)
- [ ] App móvil complementaria
- [ ] Escaneo de códigos QR/Barcode
- [ ] Integraciones con sistemas externos
- [ ] Módulo de mantenimiento predictivo

### Versión 2.0.0 (Futura Mayor)
- [ ] Migración a Laravel/Symfony
- [ ] Arquitectura de microservicios
- [ ] Panel de administración avanzado
- [ ] Módulo de Business Intelligence

---

## Bugs Conocidos

**Ninguno crítico identificado.**

Pequeños ajustes de UI que no afectan funcionalidad:
- Ajuste de márgenes en formularios largos
- Mejora de mensajes de error en validaciones
- Optimización de consultas en listados grandes (>1000 registros)

---

## Notas

- Todas las funcionalidades requeridas para operación en MCP están **completas**
- El sistema cumple con LOBIP y normativas SUDEBIP
- Las tareas pendientes son **mejoras opcionales**, no bloqueantes
- Prioridad actual: estabilidad y mantenimiento del sistema en producción

---

**Última actualización:** Abril 2026  
**Responsable:** Equipo de Desarrollo MCP
