<?php
/**
 * Vista: Listado de Movimientos y Actas
 *
 * @var array  $movimientos Lista de movimientos paginados.
 * @var string $tipo        Filtro de tipo activo.
 * @var string $estado      Filtro de estado activo.
 * @var string $search      Término de búsqueda activo.
 * @var int    $page        Página actual.
 * @var int    $totalPages  Total de páginas.
 * @var int    $total       Total de registros.
 * @var string $base_url    URL base de la aplicación.
 */

require APP_PATH . '/views/partials/sidebar.php';

$colorTipo = fn(string $t): string => match ($t) {
    'incorporacion'    => '#38a169',
    'traslado'         => '#3182ce',
    'desincorporacion' => '#e53e3e',
    default            => '#718096',
};
$colorEstado = fn(string $e): string => match ($e) {
    'aprobado'  => '#38a169',
    'rechazado' => '#e53e3e',
    default     => '#d69e2e',
};
?>

<main class="main-content" id="mainContent">

    <div class="page-header">
        <h1>Movimientos y Actas</h1>
        <?php if (in_array($_SESSION['rol'] ?? '', ['administrador', 'gerencia_bn'], true)): ?>
        <a href="<?= $base_url ?>/movimientos/nuevo" class="btn btn-primary btn-sm">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nueva Acta
        </a>
        <?php endif; ?>
    </div>

    <!-- Filtros -->
    <div class="filters-card">
        <form method="GET" role="search" aria-label="Filtros de movimientos">
            <div class="filters-grid">
                <div class="filter-item">
                    <label for="f-tipo" class="filter-label">Tipo</label>
                    <select name="tipo" id="f-tipo" class="form-control">
                        <option value="">Todos</option>
                        <option value="incorporacion"    <?= $tipo === 'incorporacion'    ? 'selected' : '' ?>>Incorporación</option>
                        <option value="traslado"         <?= $tipo === 'traslado'         ? 'selected' : '' ?>>Traslado</option>
                        <option value="desincorporacion" <?= $tipo === 'desincorporacion' ? 'selected' : '' ?>>Desincorporación</option>
                    </select>
                </div>
                <div class="filter-item">
                    <label for="f-estado" class="filter-label">Estado</label>
                    <select name="estado" id="f-estado" class="form-control">
                        <option value="">Todos</option>
                        <option value="pendiente"  <?= $estado === 'pendiente'  ? 'selected' : '' ?>>Pendiente</option>
                        <option value="aprobado"   <?= $estado === 'aprobado'   ? 'selected' : '' ?>>Aprobado</option>
                        <option value="rechazado"  <?= $estado === 'rechazado'  ? 'selected' : '' ?>>Rechazado</option>
                    </select>
                </div>
                <div class="filter-item" style="grid-column: span 2;">
                    <label for="f-search" class="filter-label">Búsqueda</label>
                    <input type="search" name="search" id="f-search" class="form-control"
                           placeholder="Bien, código, Nro. Ministerio…"
                           value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="filter-item filter-actions">
                    <label class="filter-label">&nbsp;</label>
                    <div class="filter-buttons">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="<?= $base_url ?>/movimientos" class="btn btn-secondary">Limpiar</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <?php if (empty($movimientos)): ?>
    <div class="card">
        <div class="card-body" style="text-align:center;padding:3rem">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.5" style="color:var(--gray-300);margin-bottom:1rem" aria-hidden="true">
                <polyline points="5 9 2 12 5 15"/><polyline points="19 9 22 12 19 15"/>
                <line x1="2" y1="12" x2="22" y2="12"/>
            </svg>
            <p style="color:var(--gray-500)">No se encontraron movimientos con los filtros aplicados.</p>
        </div>
    </div>
    <?php else: ?>

    <div class="card">
        <div class="card-header">
            <h3><?= number_format($total) ?> movimiento(s) encontrado(s)</h3>
        </div>
        <div class="table-wrapper">
            <table class="data-table" aria-label="Lista de movimientos y actas">
                <thead>
                    <tr>
                        <th scope="col">Fecha</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Bien</th>
                        <th scope="col">Código Interno</th>
                        <th scope="col">Nro. Min.</th>
                        <th scope="col">Área Origen</th>
                        <th scope="col">Área Destino</th>
                        <th scope="col">Solicitante</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($movimientos as $m): ?>
                <tr>
                    <td style="font-size:.8rem;white-space:nowrap">
                        <?= date('d/m/Y H:i', strtotime($m['fecha_solicitud'])) ?>
                    </td>
                    <td>
                        <span class="badge badge-sm" style="background:<?= $colorTipo($m['tipo_movimiento']) ?>">
                            <?= ucfirst($m['tipo_movimiento']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-sm" style="background:<?= $colorEstado($m['estado']) ?>">
                            <?= ucfirst($m['estado']) ?>
                        </span>
                    </td>
                    <td style="font-size:.875rem;font-weight:500">
                        <?= htmlspecialchars($m['bien_nombre']) ?>
                    </td>
                    <td>
                        <span class="codigo-interno"><?= htmlspecialchars($m['codigo_interno'] ?? '') ?></span>
                    </td>
                    <td style="font-size:.85rem">
                        <?= $m['es_sn']
                            ? '<em style="color:var(--gray-400)">S/N</em>'
                            : htmlspecialchars($m['nro_bien_ministerio'] ?? '—') ?>
                    </td>
                    <td style="font-size:.82rem"><?= htmlspecialchars($m['area_origen'] ?? '—') ?></td>
                    <td style="font-size:.82rem"><?= htmlspecialchars($m['area_destino'] ?? '—') ?></td>
                    <td style="font-size:.82rem"><?= htmlspecialchars($m['usuario_solicita'] ?? '—') ?></td>
                    <td>
                        <a href="<?= $base_url ?>/movimientos/<?= (int)$m['id_movimiento'] ?>"
                           class="btn btn-sm btn-secondary"
                           aria-label="Ver acta #<?= (int)$m['id_movimiento'] ?>">
                            Ver
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Paginación -->
    <?php if ($totalPages > 1):
        $qs = http_build_query(array_filter(compact('tipo', 'estado', 'search')));
        $qs = $qs ? '&' . $qs : '';
    ?>
    <nav class="pagination" aria-label="Paginación de movimientos">
        <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?><?= $qs ?>" class="btn btn-secondary btn-sm" aria-label="Página anterior">‹</a>
        <?php endif; ?>

        <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
        <a href="?page=<?= $p ?><?= $qs ?>"
           class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-secondary' ?>"
           <?= $p === $page ? 'aria-current="page"' : '' ?>>
            <?= $p ?>
        </a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?><?= $qs ?>" class="btn btn-secondary btn-sm" aria-label="Página siguiente">›</a>
        <?php endif; ?>

        <span class="pagination-info">
            Pág. <?= $page ?>/<?= $totalPages ?> · <?= number_format($total) ?> registros
        </span>
    </nav>
    <?php endif; ?>

    <?php endif; ?>

</main>

<style>
.pagination {
    display:flex; justify-content:center; align-items:center;
    gap:.5rem; margin-top:1.5rem; flex-wrap:wrap;
}
.pagination-info { font-size:.8rem; color:var(--gray-500); margin-left:.5rem; }
</style>
