<?php
/**
 * =============================================================================
 * VISTA: LISTADO DE MANTENIMIENTOS
 * =============================================================================
 * 
 * Muestra el historial de mantenimientos preventivos, correctivos y predictivos
 * realizados a los bienes del sistema. Permite filtrar por bien, tipo y fechas.
 * 
 * Filtros disponibles:
 * - Bien: Buscar por nombre o código del bien
 * - Tipo: Preventivo, Correctivo, Predictivo
 * - Fechas: Rango desde/hasta para filtrar por fecha de ejecución
 * 
 * @var array  $mantenimientos Listado de mantenimientos paginados
 * @var string $bien           Filtro de búsqueda por nombre/código
 * @var string $tipo           Filtro por tipo de mantenimiento
 * @var string $desde          Fecha inicial del filtro
 * @var string $hasta          Fecha final del filtro
 * @var string $base_url       URL base de la aplicación
 * =============================================================================
 */

require APP_PATH . '/views/partials/sidebar.php';
?>
<main class="main-content">
    <div class="page-header">
        <h1>Mantenimientos</h1>
        <a href="<?= $base_url ?>/mantenimientos/nuevo" class="btn btn-primary btn-sm">+ Nuevo</a>
    </div>

    <form method="GET" style="margin-bottom:1.25rem">
        <div style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end">
            <div class="form-group" style="margin:0">
                <label class="form-label" style="font-size:.8rem">Bien</label>
                <input type="text" name="bien" value="<?= htmlspecialchars($bien) ?>"
                    class="form-control" placeholder="Nombre o código..." style="width:200px">
            </div>
            <div class="form-group" style="margin:0">
                <label class="form-label" style="font-size:.8rem">Tipo</label>
                <select name="tipo" class="form-control" style="width:150px">
                    <option value="">Todos</option>
                    <option value="preventivo"  <?= $tipo==='preventivo'  ? 'selected':'' ?>>Preventivo</option>
                    <option value="correctivo"  <?= $tipo==='correctivo'  ? 'selected':'' ?>>Correctivo</option>
                    <option value="predictivo"  <?= $tipo==='predictivo'  ? 'selected':'' ?>>Predictivo</option>
                </select>
            </div>
            <div class="form-group" style="margin:0">
                <label class="form-label" style="font-size:.8rem">Desde</label>
                <input type="date" name="desde" value="<?= htmlspecialchars($desde) ?>" class="form-control" style="width:145px">
            </div>
            <div class="form-group" style="margin:0">
                <label class="form-label" style="font-size:.8rem">Hasta</label>
                <input type="date" name="hasta" value="<?= htmlspecialchars($hasta) ?>" class="form-control" style="width:145px">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
            <a href="<?= $base_url ?>/mantenimientos" class="btn btn-secondary btn-sm">Limpiar</a>
        </div>
    </form>

    <?php if (empty($mantenimientos)): ?>
    <div class="empty-state"><h3>Sin mantenimientos</h3><p>No hay registros con los filtros aplicados.</p></div>
    <?php else: ?>
    <div class="table-container">
        <table class="table table-hover">
            <thead><tr>
                <th>Bien</th><th>Tipo</th><th>Programado</th><th>Ejecutado</th>
                <th>Proveedor</th><th>Costo (Bs.)</th><th>Próximo</th><th></th>
            </tr></thead>
            <tbody>
            <?php foreach ($mantenimientos as $m): ?>
            <tr>
                <td>
                    <a href="<?= $base_url ?>/bienes/<?= $m['bien_id'] ?>" style="font-size:.85rem">
                        <?= htmlspecialchars($m['bien_nombre'] ?? '—') ?>
                    </a>
                    <small style="color:#718096;display:block"><?= htmlspecialchars($m['codigo_interno'] ?? '') ?></small>
                </td>
                <td>
                    <span class="badge" style="background:<?= $m['tipo_servicio']==='correctivo'?'#e53e3e':($m['tipo_servicio']==='preventivo'?'#38a169':'#3182ce') ?>">
                        <?= ucfirst($m['tipo_servicio']) ?>
                    </span>
                </td>
                <td style="font-size:.85rem"><?= $m['fecha_programada'] ? date('d/m/Y', strtotime($m['fecha_programada'])) : '—' ?></td>
                <td style="font-size:.85rem"><?= $m['fecha_ejecutada']  ? date('d/m/Y', strtotime($m['fecha_ejecutada']))  : '<em style="color:#a0aec0">Pendiente</em>' ?></td>
                <td style="font-size:.85rem"><?= htmlspecialchars($m['proveedor'] ?? '—') ?></td>
                <td style="text-align:right;font-size:.85rem"><?= $m['costo'] ? number_format($m['costo'],2) : '—' ?></td>
                <td style="font-size:.85rem"><?= $m['proxima_fecha_programada'] ? date('d/m/Y', strtotime($m['proxima_fecha_programada'])) : '—' ?></td>
                <td>
                    <a href="<?= $base_url ?>/mantenimientos/<?= $m['id_mantenimiento'] ?>" class="btn btn-sm btn-secondary">Ver</a>
                    <a href="<?= $base_url ?>/mantenimientos/<?= $m['id_mantenimiento'] ?>/editar" class="btn btn-sm btn-warning">Editar</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <?php $qs = http_build_query(array_filter(compact('bien','tipo','desde','hasta'))); $qs = $qs ? '&'.$qs : ''; ?>
    <div style="display:flex;justify-content:center;gap:.5rem;margin-top:1.25rem;flex-wrap:wrap">
        <?php if ($page > 1): ?><a href="?page=<?= $page-1 ?><?= $qs ?>" class="btn btn-secondary btn-sm">‹</a><?php endif; ?>
        <?php for ($p = max(1,$page-2); $p <= min($totalPages,$page+2); $p++): ?>
        <a href="?page=<?= $p ?><?= $qs ?>" class="btn btn-sm <?= $p===$page?'btn-primary':'btn-secondary' ?>"><?= $p ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?><a href="?page=<?= $page+1 ?><?= $qs ?>" class="btn btn-secondary btn-sm">›</a><?php endif; ?>
        <span style="color:#718096;font-size:.8rem;align-self:center">Pág. <?= $page ?>/<?= $totalPages ?> (<?= number_format($total) ?>)</span>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</main>
