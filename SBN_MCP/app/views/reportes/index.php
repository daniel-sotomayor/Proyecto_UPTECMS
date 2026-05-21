<?php
/**
 * Vista: Listado de Reportes Disponibles
 * Los reportes BM-1 a BM-4 cumplen normativas MCP/LOBIP.
 *
 * @var string $base_url URL base de la aplicación.
 */

require APP_PATH . '/views/partials/sidebar.php';
?>

<main class="main-content" id="mainContent">

    <div class="page-header">
        <div>
            <h1>Reportes de Bienes Nacionales</h1>
            <p class="page-subtitle">Generación de reportes según normativas LOBIP y Ministerio de Salud</p>
        </div>
    </div>

    <div class="reports-grid">

        <!-- BM-1 -->
        <div class="report-card">
            <div class="report-icon report-icon--blue" aria-hidden="true">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10 9 9 9 8 9"/>
                </svg>
            </div>
            <div class="report-body">
                <h3>Reporte BM-1</h3>
                <p>Inventario de bienes activos: Operativo, Inoperativo y En Resguardo. Incluye exportación CSV.</p>
            </div>
            <div class="report-actions">
                <a href="<?= $base_url ?>/reportes/bm1" class="btn btn-primary btn-sm">Ver Reporte</a>
                <a href="<?= $base_url ?>/reportes/bm1?export=csv" class="btn btn-secondary btn-sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    CSV
                </a>
            </div>
        </div>

        <!-- BM-2 -->
        <div class="report-card">
            <div class="report-icon report-icon--red" aria-hidden="true">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6l-1 14H6L5 6"/>
                    <path d="M10 11v6"/><path d="M14 11v6"/>
                    <path d="M9 6V4h6v2"/>
                </svg>
            </div>
            <div class="report-body">
                <h3>Reporte BM-2</h3>
                <p>Bienes desincorporados y en estado de chatarra. Registro histórico de bajas del inventario.</p>
            </div>
            <div class="report-actions">
                <a href="<?= $base_url ?>/reportes/bm2" class="btn btn-primary btn-sm">Ver Reporte</a>
                <a href="<?= $base_url ?>/reportes/bm2?export=csv" class="btn btn-secondary btn-sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    CSV
                </a>
            </div>
        </div>

        <!-- BM-3 -->
        <div class="report-card">
            <div class="report-icon report-icon--teal" aria-hidden="true">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <polyline points="5 9 2 12 5 15"/>
                    <polyline points="19 9 22 12 19 15"/>
                    <line x1="2" y1="12" x2="22" y2="12"/>
                </svg>
            </div>
            <div class="report-body">
                <h3>Reporte BM-3</h3>
                <p>Movimientos y actas del período seleccionado: incorporaciones, traslados y desincorporaciones.</p>
            </div>
            <div class="report-actions">
                <a href="<?= $base_url ?>/reportes/bm3" class="btn btn-primary btn-sm">Ver Reporte</a>
            </div>
        </div>

        <!-- BM-4 -->
        <div class="report-card">
            <div class="report-icon report-icon--purple" aria-hidden="true">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <path d="M3 9h18"/><path d="M9 21V9"/>
                </svg>
            </div>
            <div class="report-body">
                <h3>Reporte BM-4</h3>
                <p>Resumen ejecutivo del inventario: distribución por estado, clasificación, edificio y valor total.</p>
            </div>
            <div class="report-actions">
                <a href="<?= $base_url ?>/reportes/bm4" class="btn btn-primary btn-sm">Ver Reporte</a>
            </div>
        </div>

    </div>

</main>

<style>
.page-subtitle { font-size:.85rem; color:var(--gray-500); margin-top:.2rem; }
.reports-grid {
    display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
    gap:1.5rem; margin-top:.5rem;
}
.report-card {
    background:var(--white); border-radius:var(--radius-lg);
    box-shadow:var(--shadow); padding:1.75rem;
    display:flex; flex-direction:column; gap:1rem;
    transition:box-shadow var(--transition), transform var(--transition);
    border:1px solid var(--gray-100);
}
.report-card:hover { box-shadow:var(--shadow-lg); transform:translateY(-2px); }
.report-icon {
    width:60px; height:60px; border-radius:var(--radius-lg);
    display:flex; align-items:center; justify-content:center; color:#fff;
}
.report-icon--blue   { background:linear-gradient(135deg,#3182ce,#2c5282); }
.report-icon--red    { background:linear-gradient(135deg,#e53e3e,#c53030); }
.report-icon--teal   { background:linear-gradient(135deg,#319795,#2c7a7b); }
.report-icon--purple { background:linear-gradient(135deg,#805ad5,#6b46c1); }
.report-body h3 { font-size:1.05rem; font-weight:700; color:var(--gray-900); margin-bottom:.35rem; }
.report-body p  { font-size:.85rem; color:var(--gray-500); line-height:1.5; margin:0; }
.report-actions { display:flex; gap:.5rem; margin-top:auto; }
</style>
