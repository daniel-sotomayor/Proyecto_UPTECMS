<?php
/**
 * Vista: Log de Auditoría
 * Tabla paginada con filtros por tabla, acción y búsqueda libre.
 *
 * @var array  $logs    Registros de auditoría de la página actual.
 * @var int    $page    Página actual.
 * @var int    $pages   Total de páginas.
 * @var int    $total   Total de registros.
 * @var string $tabla   Filtro de tabla activo.
 * @var string $accion  Filtro de acción activo.
 * @var string $search  Término de búsqueda activo.
 * @var string $base_url URL base de la aplicación.
 */

require APP_PATH . '/views/partials/sidebar.php';

/** Devuelve clase CSS para el badge según la acción de auditoría. */
function auditBadgeColor(string $accion): string
{
    return match ($accion) {
        'INSERT'  => '#38a169',
        'UPDATE'  => '#d69e2e',
        'DELETE'  => '#e53e3e',
        'login'   => '#3182ce',
        'logout'  => '#718096',
        default   => '#a0aec0',
    };
}
?>

<main class="main-content" id="mainContent">

    <div class="page-header">
        <div>
            <h1>Auditoría del Sistema</h1>
            <p class="page-subtitle">
                <?= number_format($total) ?> registro<?= $total !== 1 ? 's' : '' ?> encontrado<?= $total !== 1 ? 's' : '' ?>
            </p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="filters-card">
        <form method="GET" role="search" aria-label="Filtros de auditoría">
            <div class="filters-grid">
                <div class="filter-item">
                    <label for="f-tabla" class="filter-label">Tabla</label>
                    <select name="tabla" id="f-tabla" class="form-control">
                        <option value="">Todas</option>
                        <?php foreach (['bienes','movimientos','usuarios','mantenimientos','auditoria'] as $t): ?>
                        <option value="<?= $t ?>" <?= $tabla === $t ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-item">
                    <label for="f-accion" class="filter-label">Acción</label>
                    <select name="accion" id="f-accion" class="form-control">
                        <option value="">Todas</option>
                        <?php foreach (['INSERT','UPDATE','DELETE','login','logout'] as $a): ?>
                        <option value="<?= $a ?>" <?= $accion === $a ? 'selected' : '' ?>><?= $a ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-item" style="grid-column: span 2;">
                    <label for="f-search" class="filter-label">Búsqueda</label>
                    <input type="search" name="search" id="f-search" class="form-control"
                           placeholder="Tabla, acción, usuario, IP…"
                           value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="filter-item filter-actions">
                    <label class="filter-label">&nbsp;</label>
                    <div class="filter-buttons">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="<?= $base_url ?>/auditoria" class="btn btn-secondary">Limpiar</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <?php if (empty($logs)): ?>
    <div class="card">
        <div class="card-body" style="text-align:center;padding:3rem">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.5" style="color:var(--gray-300);margin-bottom:1rem" aria-hidden="true">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
            <p style="color:var(--gray-500);font-size:.9rem">No se encontraron registros con los filtros aplicados.</p>
        </div>
    </div>
    <?php else: ?>

    <div class="card">
        <div class="table-wrapper">
            <table class="data-table" aria-label="Log de auditoría">
                <thead>
                    <tr>
                        <th scope="col">Fecha y Hora</th>
                        <th scope="col">Usuario</th>
                        <th scope="col">Tabla</th>
                        <th scope="col">ID Reg.</th>
                        <th scope="col">Acción</th>
                        <th scope="col">IP</th>
                        <th scope="col">Agente</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td style="white-space:nowrap;font-size:.8rem">
                        <?= date('d/m/Y', strtotime($log['fecha_operacion'])) ?>
                        <span style="color:var(--gray-400)">
                            <?= date('H:i:s', strtotime($log['fecha_operacion'])) ?>
                        </span>
                    </td>
                    <td style="font-size:.85rem">
                        <?= htmlspecialchars($log['usuario_nombre'] ?? '—') ?>
                    </td>
                    <td>
                        <code style="font-size:.78rem;background:var(--gray-100);padding:.1rem .35rem;border-radius:.25rem">
                            <?= htmlspecialchars($log['tabla_afectada']) ?>
                        </code>
                    </td>
                    <td style="text-align:center;font-size:.85rem;font-weight:600">
                        <?= $log['registro_id'] ?: '—' ?>
                    </td>
                    <td>
                        <span class="badge badge-sm"
                              style="background:<?= auditBadgeColor($log['accion']) ?>">
                            <?= htmlspecialchars($log['accion']) ?>
                        </span>
                    </td>
                    <td style="font-size:.78rem;font-family:monospace;color:var(--gray-500)">
                        <?= htmlspecialchars($log['ip_address'] ?? '—') ?>
                    </td>
                    <td style="font-size:.72rem;color:var(--gray-400);max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                        title="<?= htmlspecialchars($log['user_agent'] ?? '') ?>">
                        <?= htmlspecialchars(mb_substr($log['user_agent'] ?? '—', 0, 40)) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Paginación -->
    <?php if ($pages > 1):
        $qs = http_build_query(array_filter(compact('tabla', 'accion', 'search')));
        $qs = $qs ? '&' . $qs : '';
    ?>
    <nav class="pagination" aria-label="Paginación de auditoría">
        <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?><?= $qs ?>" class="btn btn-secondary btn-sm" aria-label="Página anterior">‹ Anterior</a>
        <?php endif; ?>

        <?php for ($p = max(1, $page - 2); $p <= min($pages, $page + 2); $p++): ?>
        <a href="?page=<?= $p ?><?= $qs ?>"
           class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-secondary' ?>"
           <?= $p === $page ? 'aria-current="page"' : '' ?>>
            <?= $p ?>
        </a>
        <?php endfor; ?>

        <?php if ($page < $pages): ?>
        <a href="?page=<?= $page + 1 ?><?= $qs ?>" class="btn btn-secondary btn-sm" aria-label="Página siguiente">Siguiente ›</a>
        <?php endif; ?>

        <span class="pagination-info">
            Página <?= $page ?> de <?= $pages ?>
            &nbsp;·&nbsp; <?= number_format($total) ?> registros
        </span>
    </nav>
    <?php endif; ?>

    <?php endif; ?>

</main>

<style>
.page-subtitle { font-size:.85rem; color:var(--gray-500); margin-top:.2rem; }
.pagination {
    display:flex; justify-content:center; align-items:center;
    gap:.5rem; margin-top:1.5rem; flex-wrap:wrap;
}
.pagination-info { font-size:.8rem; color:var(--gray-500); margin-left:.5rem; }
</style>
