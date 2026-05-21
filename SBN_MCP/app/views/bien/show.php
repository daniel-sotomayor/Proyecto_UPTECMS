<?php
/**
 * =============================================================================
 * VISTA: DETALLE DEL BIEN NACIONAL
 * =============================================================================
 * 
 * Muestra toda la información detallada de un bien específico.
 * Incluye codificación, descripción, ubicación, datos económicos e historial.
 * 
 * Layout:
 * - Columna principal (2fr): Codificación, Descripción, Historial de movimientos
 * - Columna lateral (1fr): C.I.N, Datos económicos, Imagen, Responsable
 * 
 * Características:
 * - Visualización de C.I.N completo
 * - Historial de movimientos con colores según tipo
 * - Cálculo automático de costo total (valor × cantidad)
 * - Visualización de imagen si existe
 * - Botón de edición según permisos del rol
 * 
 * @var array  $bien       Datos completos del bien
 * @var array  $historial  Listado de movimientos asociados al bien
 * @var string $base_url   URL base de la aplicación
 * =============================================================================
 */

require APP_PATH . '/views/partials/sidebar.php';
?>

<main class="main-content">
    <div class="page-header">
        <div>
            <h1><?= htmlspecialchars($bien['nombre']) ?></h1>
            <div style="display:flex;gap:.5rem;align-items:center;margin-top:.3rem">
                <span class="codigo-interno"><?= htmlspecialchars($bien['codigo_interno'] ?? '') ?></span>
                <span class="badge" style="background:<?= htmlspecialchars($bien['estado_color']) ?>">
                    <?= htmlspecialchars($bien['estado_nombre']) ?>
                </span>
                <span class="badge badge-sm" style="background:#1e3a5f" title="<?= htmlspecialchars($bien['tipo_nombre'] ?? '') ?>">
                    <?= htmlspecialchars($bien['tipo_codigo'] ?? '') ?>
                </span>
            </div>
        </div>
        <div class="page-actions">
            <?php if (in_array($_SESSION['rol']??'', ['administrador','gerencia_bn','controlador_inventario'])): ?>
            <a href="<?= $base_url ?>/bienes/<?= $bien['id_bien'] ?>/editar" class="btn btn-warning btn-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Editar
            </a>
            <?php endif; ?>
            <?php if (in_array($_SESSION['rol']??'', ['administrador','gerencia_bn','validador_inventario'])): ?>
            <form method="post" action="<?= $base_url ?>/bienes/<?= $bien['id_bien'] ?>/verificar-semestral" style="display:inline">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Registrar verificación semestral para este bien?')">
                    Marcar Verificación Semestral
                </button>
            </form>
            <?php endif; ?>
            <a href="<?= $base_url ?>/bienes/<?= $bien['id_bien'] ?>/verificaciones" class="btn btn-sm btn-outline">Ver verificaciones</a>
            <a href="<?= $base_url ?>/bienes" class="btn btn-secondary btn-sm">← Volver</a>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;align-items:start">

        <!-- Columna principal -->
        <div>
            <!-- Codificación -->
            <div class="card" style="margin-bottom:1.5rem">
                <div class="card-header"><h3>Codificación e Identificación</h3></div>
                <div class="card-body">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Código Interno (Pub. 9)</span>
                            <span class="detail-value codigo-interno"><?= htmlspecialchars($bien['codigo_interno'] ?? '—') ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Nro. de Bien (Ministerio)</span>
                            <span class="detail-value">
                                <?= $bien['es_sn'] ? '<em style="color:#a0aec0">S/N — Sin número asignado</em>' : htmlspecialchars($bien['nro_bien_ministerio'] ?? '—') ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Código Ministerio Salud</span>
                            <span class="detail-value"><?= htmlspecialchars($bien['codigo_ministerio'] ?? '—') ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Clasificación</span>
                            <span class="detail-value"><?= htmlspecialchars(($bien['tipo_codigo'] ?? '') . ' — ' . ($bien['tipo_nombre'] ?? '')) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Descripción -->
            <div class="card" style="margin-bottom:1.5rem">
                <div class="card-header"><h3>Descripción del Bien</h3></div>
                <div class="card-body">
                    <div class="detail-grid">
                        <div class="detail-item full" style="grid-column:1/-1">
                            <span class="detail-label">Descripción Específica</span>
                            <span class="detail-value"><?= nl2br(htmlspecialchars($bien['descripcion'] ?? '—')) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Marca</span>
                            <span class="detail-value"><?= htmlspecialchars($bien['marca'] ?? '—') ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Modelo</span>
                            <span class="detail-value"><?= htmlspecialchars($bien['modelo'] ?? '—') ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Serial</span>
                            <span class="detail-value"><?= htmlspecialchars($bien['serial'] ?? '—') ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Color</span>
                            <span class="detail-value"><?= htmlspecialchars($bien['color'] ?? '—') ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Cantidad</span>
                            <span class="detail-value"><?= (int)($bien['cantidad'] ?? 1) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Año Fabricación</span>
                            <span class="detail-value"><?= htmlspecialchars($bien['año_fabricacion'] ?? '—') ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Condición Inicial</span>
                            <span class="detail-value"><?= htmlspecialchars($bien['condicion_inicial'] ?? '—') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historial de movimientos -->
            <div class="card">
                <div class="card-header"><h3>Historial de Movimientos y Trazabilidad</h3></div>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead><tr>
                            <th>Fecha</th><th>Tipo</th><th>Origen</th><th>Destino</th>
                            <th>Solicitado por</th><th>Aprobado por</th><th>Estado</th>
                        </tr></thead>
                        <tbody>
                        <?php if (empty($historial)): ?>
                            <tr class="empty-row"><td colspan="7">Sin movimientos registrados</td></tr>
                        <?php else: foreach ($historial as $m): ?>
                            <tr>
                                <td style="font-size:.8rem;white-space:nowrap"><?= date('d/m/Y H:i', strtotime($m['fecha_solicitud'])) ?></td>
                                <td><span class="badge badge-sm" style="background:<?= $m['tipo_movimiento']==='incorporacion'?'#38a169':($m['tipo_movimiento']==='traslado'?'#3182ce':'#e53e3e') ?>"><?= ucfirst($m['tipo_movimiento']) ?></span></td>
                                <td style="font-size:.8rem"><?= htmlspecialchars($m['area_origen'] ?? '—') ?></td>
                                <td style="font-size:.8rem"><?= htmlspecialchars($m['area_destino'] ?? '—') ?></td>
                                <td style="font-size:.8rem"><?= htmlspecialchars($m['usuario_solicita'] ?? '—') ?></td>
                                <td style="font-size:.8rem"><?= htmlspecialchars($m['usuario_aprueba'] ?? '—') ?></td>
                                <td><span class="badge badge-sm" style="background:<?= $m['estado']==='aprobado'?'#38a169':($m['estado']==='pendiente'?'#d69e2e':'#e53e3e') ?>"><?= ucfirst($m['estado']) ?></span></td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Columna lateral -->
        <div>
            <!-- C.I.N -->
            <div class="card" style="margin-bottom:1.5rem">
                <div class="card-header"><h3>C.I.N — Ubicación</h3></div>
                <div class="card-body">
                    <div style="margin-bottom:1rem">
                        <div class="cin-preview" style="font-size:.85rem;word-break:break-all">
                            <?= htmlspecialchars($bien['cin_completo'] ?? '—') ?>
                        </div>
                    </div>
                    <div class="detail-grid" style="grid-template-columns:1fr">
                        <div class="detail-item">
                            <span class="detail-label">Edificio</span>
                            <span class="detail-value"><?= htmlspecialchars($bien['cin_edificio'] ?? $bien['edificio'] ?? '—') ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Piso</span>
                            <span class="detail-value"><?= htmlspecialchars($bien['cin_piso'] ?? $bien['piso'] ?? '—') ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Departamento</span>
                            <span class="detail-value"><?= htmlspecialchars($bien['cin_departamento'] ?? $bien['nombre_area'] ?? '—') ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Oficina</span>
                            <span class="detail-value"><?= htmlspecialchars($bien['cin_oficina'] ?? '—') ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Posición</span>
                            <span class="detail-value"><?= htmlspecialchars($bien['cin_posicion'] ?? '—') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Datos económicos -->
            <div class="card" style="margin-bottom:1.5rem">
                <div class="card-header"><h3>Datos Económicos</h3></div>
                <div class="card-body">
                    <div class="detail-grid" style="grid-template-columns:1fr">
                        <div class="detail-item">
                            <span class="detail-label">Valor Unitario</span>
                            <span class="detail-value" style="font-size:1.1rem;font-weight:700;color:#1e3a5f">
                                <?= $bien['valor_inicial'] > 0 ? 'Bs. ' . number_format($bien['valor_inicial'], 2) : '—' ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Costo Total (x<?= (int)($bien['cantidad']??1) ?>)</span>
                            <span class="detail-value">
                                <?= $bien['valor_inicial'] > 0 ? 'Bs. ' . number_format($bien['valor_inicial'] * ($bien['cantidad'] ?? 1), 2) : '—' ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Valor Residual</span>
                            <span class="detail-value"><?= $bien['valor_residual'] > 0 ? 'Bs. ' . number_format($bien['valor_residual'], 2) : '—' ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Vida Útil</span>
                            <span class="detail-value"><?= htmlspecialchars($bien['vida_util_anos'] ?? '—') ?> años</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Fecha Adquisición</span>
                            <span class="detail-value"><?= !empty($bien['fecha_adquisicion']) ? date('d/m/Y', strtotime($bien['fecha_adquisicion'])) : '—' ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Nro. Factura</span>
                            <span class="detail-value"><?= htmlspecialchars($bien['numero_factura'] ?? '—') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Imagen del bien -->
            <?php
            $imgSrc = !empty($bien['imagen_path'])
                ? $base_url . '/uploads/' . htmlspecialchars($bien['imagen_path'])
                : null;
            ?>
            <div class="card" style="margin-bottom:1.5rem">
                <div class="card-header"><h3>Imagen</h3></div>
                <div class="card-body" style="text-align:center;padding:1.5rem">
                    <?php if ($imgSrc): ?>
                    <img src="<?= $imgSrc ?>" alt="Imagen del bien"
                         style="max-width:100%;max-height:220px;border-radius:8px;object-fit:contain">
                    <?php else: ?>
                    <div style="width:100%;height:120px;background:#f7fafc;border:2px dashed #e2e8f0;
                                border-radius:8px;display:flex;align-items:center;justify-content:center;
                                color:#a0aec0;font-size:.85rem">
                        Sin imagen registrada
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Responsable -->
            <div class="card">
                <div class="card-header"><h3>Responsable</h3></div>
                <div class="card-body">
                    <div class="detail-item">
                        <span class="detail-label">Responsable del Bien</span>
                        <span class="detail-value"><?= htmlspecialchars($bien['responsable_nombre'] ?? 'Sin asignar') ?></span>
                    </div>
                    <?php if (!empty($bien['observaciones'])): ?>
                    <div class="detail-item" style="margin-top:1rem">
                        <span class="detail-label">Observaciones</span>
                        <span class="detail-value" style="font-size:.85rem"><?= nl2br(htmlspecialchars($bien['observaciones'])) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="detail-item" style="margin-top:1rem">
                        <span class="detail-label">Registrado</span>
                        <span class="detail-value" style="font-size:.8rem"><?= date('d/m/Y H:i', strtotime($bien['created_at'])) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
