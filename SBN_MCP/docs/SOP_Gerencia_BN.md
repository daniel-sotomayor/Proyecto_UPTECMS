# SOP — Gerencia de Bienes Nacionales (SBN MCP)

Última actualización: 2026-05-17

Propósito
- Documentar el procedimiento operativo estándar (SOP) para que la Gerencia de Bienes Nacionales use la aplicación SBN_MCP como herramienta única de control y gestión del inventario institucional.

Alcance
- Aplica a: registro, verificación física semestral, traslado, desincorporación, reportes y respaldo de datos.
- Usuarios objetivo: Gerencia B.N., Dirección, Validador de Inventario, Administrador.

Roles y permisos relevantes
- `administrador`: acceso total. Gestión de usuarios, backups y migraciones.
- `gerencia_bn`: registrar bienes, aprobar mov., generar actas, asignar N° de Bien.
- `controlador_inventario`: corregir inventario y revisar consistencia.
- `validador_inventario`: usuario designado para marcar verificaciones físicas semestrales.
- `registrador`: solo carga inicial de bienes.

Preparación previa (antes del inventario semestral)
1. Verificar que el sistema tenga backup reciente (regla 3-2-1). Ejecutar y validar backup:

```bash
php scripts/backup.php daily
# o usar el cron configurado / repo backup helper
```

2. Confirmar que el rol `validador_inventario` existe (si no, importar migración SQL):

```sql
-- Desde la DB: importar sql/20260517_add_validador_and_verificaciones.sql
mysql -u root -p hospital_bienes < sql/20260517_add_validador_and_verificaciones.sql
```

3. Asignar el usuario responsable (Amalia u otro) al rol `validador_inventario` (UI o SQL):

UI: `Usuarios` → Buscar usuario → `Asignar Validador`.

SQL (ejemplo):

```sql
UPDATE usuarios
SET id_rol = (SELECT id_rol FROM roles WHERE nombre = 'validador_inventario')
WHERE email LIKE '%amalia%' OR username LIKE '%amalia%';
```

Procedimiento de Verificación Semestral (paso a paso)
1. Planificación
  - La Gerencia programa la fecha de verificación y notifica a las áreas.
  - Exportar listado inicial para la zona/edificio: `Inventario` (/bienes) y filtrar por edificio/área.

2. Verificación física
  - Equipo recorre las áreas y comprueba: existencia, estado (Operativo/Inoperativo/En Resguardo/Chatarra), CIN (Edif/Piso/Dep/Ofic/Pos), serial y cantidad.
  - Registrar observaciones físicas y pruebas fotográficas (subir imagen al registro del bien si aplica).

3. Registro en el Sistema
  - Si el usuario tiene rol `validador_inventario` o `gerencia_bn`, abrir el bien y pulsar `Marcar Verificación Semestral`.
  - En caso de cambios (traslado, baja, corrección), usar el flujo de `Movimientos` (incorporación/traslado/desincorporación). Todos los cambios quedan en `Auditoría`.

4. Firma y Aprobación
  - Las acciones críticas (desincorporación, traslados entre edificios principales) requieren aprobación por `gerencia_bn` o `administrador` según políticas internas.
  - El sistema guarda un registro en `auditoria` con datos anteriores/nuevos cuando se realizan `UPDATE`/`DELETE`/`INSERT`.

5. Generación de Reportes
  - Reporte ejecutivo BM-4: `/reportes/bm4`.
  - Exportar CSV del Inventario Global: desde la vista `Inventario Global` usar botón `Exportar CSV`.

Auditoría y Trazabilidad
- Todo cambio significativo queda registrado en la tabla `auditoria` (tabla_afectada, registro_id, accion, usuario_id, datos_anteriores, datos_nuevos, fecha_operacion).
- Para revisar: ir a `Auditoría` (ruta `/auditoria`) y filtrar por tabla `bienes`, `movimientos` o `verificaciones`.

Backups y Recuperación
- Política recomendada: 3-2-1 (3 copias, 2 medios, 1 fuera del sitio).
- Usar `scripts/backup.php` o integrarlo en cron del servidor.
- Ver backups disponibles: `BackupHelper::listBackups()` o ruta administrativa (solo administrador).

Checklist post-verificación
- Registrar verificación semestral en cada bien.
- Confirmar que todas las imágenes/observaciones fueron subidas.
- Generar y exportar CSV del inventario actualizado.
- Revisar `auditoria` para confirmar cambios y firmas.
- Crear acta de verificación (descargar BM-4 y anexar evidencia).

Comandos útiles
- Ejecutar migraciones (si es necesario):

```bash
mysql -u root -p hospital_bienes < sql/20260517_add_validador_and_verificaciones.sql
```

- Verificar tabla de verificaciones:

```sql
SHOW TABLES LIKE 'verificaciones';
SELECT * FROM verificaciones WHERE bien_id = 123 ORDER BY fecha_verificacion DESC LIMIT 10;
```

- Marcar verificación desde UI: abrir `Bien` → `Marcar Verificación Semestral`.

Buenas prácticas y seguridad
- Mantener actualizado `MAIL_CONTACTO` en `.env` para notificaciones.
- No compartir credenciales; usar cuentas individuales.
- Validar backups antes de operaciones masivas (importaciones o migraciones).

Contactos
- Gerencia B.N.: Amalia — 0424-1649609
- Equipo técnico: desarrollo@hospital.local (ajustar según política local)

Anexo: Plantilla rápida de acta
- Nombre del bien | Código interno | Nro. Bien Ministerio | Estado | Observaciones | Verificado por | Fecha
