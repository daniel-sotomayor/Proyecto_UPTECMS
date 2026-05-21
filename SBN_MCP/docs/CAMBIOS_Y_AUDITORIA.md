# Cambios, Auditoría y Trazabilidad

## Auditoría de Cambios
- Todas las acciones críticas (crear, editar, eliminar bienes, usuarios, movimientos) quedan registradas en la tabla de auditoría.
- El log de auditoría se puede consultar desde el módulo de administración (solo roles autorizados).

## Control de Roles y Permisos
- El sistema soporta los roles: administrador, gerencia_bn, controlador_inventario, registrador, validador_inventario.
- Cada rol tiene permisos específicos, definidos en la tabla `roles`.

## Pruebas y Validaciones
- Validaciones en tiempo real en formularios (AJAX) para evitar duplicados y errores comunes.
- Pruebas automáticas incluidas en `tests/`.

## Respaldo y Recuperación
- El sistema genera logs de respaldo en la tabla `backups_log`.
- Se recomienda programar respaldos periódicos usando el script `scripts/backup.php`.

## Cambios recientes (2026-05-19)
- Unificación de migraciones en un solo archivo SQL definitivo.
- Soporte para foto y cédula de responsable en bienes.
- Mejoras en validación y control de errores.
- Pruebas automáticas 100% exitosas.

---
**Para más detalles, consulta el código fuente y los archivos en `docs/`.**
