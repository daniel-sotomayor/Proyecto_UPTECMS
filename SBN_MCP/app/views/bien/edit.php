<?php
/**
 * =============================================================================
 * VISTA: EDITAR BIEN NACIONAL
 * =============================================================================
 * 
 * Formulario para editar un bien existente. Permite modificar datos del bien
 * manteniendo su historial y trazabilidad.
 * 
 * Campos editables:
 * - Identificación (nombre, marca, serial, etc.)
 * - Nro. de Bien Ministerio (con toggle S/N)
 * - Clasificación y Estado
 * - Ubicación (Área, Oficina, Posición)
 * - Responsable
 * - Datos económicos
 * - Observaciones
 * 
 * Notas de seguridad:
 * - Solo usuarios con permisos pueden editar (admin, gerencia_bn, controlador_inventario)
 * - El código interno NO es editable (se genera al crear)
 * - Los cambios se registran en auditoría automáticamente
 * 
 * @var array  $bien           Datos completos del bien a editar
 * @var array  $tipos          Tipos de bienes disponibles
 * @var array  $estados        Estados posibles del bien
 * @var array  $areas          Áreas/departamentos del sistema
 * @var array  $personal       Personal para asignar responsable
 * @var string $csrf_token     Token CSRF
 * @var string $base_url       URL base de la aplicación
 * =============================================================================
 */

require APP_PATH . '/views/partials/sidebar.php';
?>

<main class="main-content">
    <div class="page-header">
        <h1>Editar Bien</h1>
        <a href="<?= $base_url ?>/bienes/<?= $bien['id_bien'] ?>" class="btn btn-secondary btn-sm">← Ver detalle</a>
    </div>

    <div class="form-card">
        <div style="background:#ebf8ff;border:1px solid #bee3f8;border-radius:.375rem;padding:.75rem 1rem;margin-bottom:1.5rem;font-size:.85rem">
            <strong>Código Interno:</strong> <span class="codigo-interno"><?= htmlspecialchars($bien['codigo_interno'] ?? '') ?></span>
            &nbsp;|&nbsp;
            <strong>Nro. Bien:</strong> <?= $bien['es_sn'] ? '<em>S/N</em>' : htmlspecialchars($bien['nro_bien_ministerio'] ?? '') ?>
        </div>

        <form id="editBienForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

            <div class="form-section">
                <div class="form-section-title">Identificación</div>
                <div class="form-grid">
                    <div class="form-group full">
                        <label>Nombre del Bien <span class="req">*</span></label>
                        <input type="text" name="nombre" class="form-input" value="<?= htmlspecialchars($bien['nombre']) ?>" required>
                        <span class="form-error" id="nombre-error"></span>
                    </div>
                    <div class="form-group full">
                        <label>Descripción Específica</label>
                        <textarea name="descripcion" class="form-textarea"><?= htmlspecialchars($bien['descripcion'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Nro. de Bien (Ministerio)</label>
                        <input type="text" name="nro_bien_ministerio" id="nro_bien_ministerio" class="form-input"
                               value="<?= $bien['es_sn'] ? '' : htmlspecialchars($bien['nro_bien_ministerio'] ?? '') ?>"
                               <?= $bien['es_sn'] ? 'disabled style="opacity:.4"' : '' ?>>
                        <span class="form-error" id="nro_bien_ministerio-error"></span>
                    </div>
                    <div class="form-group" style="justify-content:flex-end;padding-bottom:.3rem">
                        <label>&nbsp;</label>
                        <label class="check-group form-group" style="flex-direction:row;align-items:center;gap:.5rem;cursor:pointer">
                            <input type="checkbox" name="es_sn" id="es_sn" value="1"
                                   <?= $bien['es_sn'] ? 'checked' : '' ?>
                                   onchange="toggleNroBien(this)">
                            <span style="font-size:.875rem;font-weight:600">S/N — Sin número</span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Marca</label>
                        <input type="text" name="marca" class="form-input" value="<?= htmlspecialchars($bien['marca'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Modelo</label>
                        <input type="text" name="modelo" class="form-input" value="<?= htmlspecialchars($bien['modelo'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Serial</label>
                        <input type="text" name="serial" class="form-input" value="<?= htmlspecialchars($bien['serial'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Color</label>
                        <input type="text" name="color" class="form-input" value="<?= htmlspecialchars($bien['color'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Cantidad</label>
                        <input type="number" name="cantidad" class="form-input" value="<?= (int)($bien['cantidad'] ?? 1) ?>" min="1">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title">Clasificación y Estado</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Clasificación <span class="req">*</span></label>
                        <select name="id_tipo" class="form-select" required>
                            <?php foreach ($tipos as $t): ?>
                            <option value="<?= $t['id_tipo'] ?>" <?= $t['id_tipo'] == $bien['id_tipo'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['codigo'] . ' — ' . $t['nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Estado <span class="req">*</span></label>
                        <select name="id_estado" class="form-select" required>
                            <?php foreach ($estados as $e): ?>
                            <option value="<?= $e['id_estado'] ?>" <?= $e['id_estado'] == $bien['id_estado'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($e['nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Área <span class="req">*</span></label>
                        <select name="id_area" id="id_area" class="form-select" required>
                            <?php
                            $edificioActual = '';
                            foreach ($areas as $a):
                                if ($a['edificio'] !== $edificioActual):
                                    if ($edificioActual !== '') echo '</optgroup>';
                                    echo '<optgroup label="Edificio ' . htmlspecialchars($a['edificio']) . '">';
                                    $edificioActual = $a['edificio'];
                                endif;
                                $pisoLabel = $a['piso'] == -1 ? 'Sótano' : ($a['piso'] == 0 ? 'P.Baja' : 'Piso ' . $a['piso']);
                                $sel = $a['id_area'] == $bien['id_area'] ? 'selected' : '';
                            ?>
                            <option value="<?= $a['id_area'] ?>" <?= $sel ?>
                                    data-edificio="<?= htmlspecialchars($a['edificio']) ?>"
                                    data-piso="<?= htmlspecialchars($a['piso']) ?>"
                                    data-depto="<?= htmlspecialchars($a['nombre_area']) ?>">
                                <?= htmlspecialchars($pisoLabel . ' — ' . $a['nombre_area']) ?>
                            </option>
                            <?php endforeach; if ($edificioActual !== '') echo '</optgroup>'; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Responsable</label>
                        <select name="responsable_id" class="form-select">
                            <option value="">— Sin asignar —</option>
                            <?php foreach ($personal as $p): ?>
                            <option value="<?= $p['id_usuario'] ?>" <?= $p['id_usuario'] == $bien['responsable_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['nombre_completo']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Número de Cédula</label>
                        <input type="text" name="responsable_cedula" class="form-input" 
                               placeholder="Ej: V-12345678" maxlength="15"
                               value="<?= htmlspecialchars($bien['responsable_cedula'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title">C.I.N — Ubicación</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Oficina / Sub-área</label>
                        <input type="text" name="cin_oficina" id="cin_oficina" class="form-input"
                               value="<?= htmlspecialchars($bien['cin_oficina'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Nro. de Posición</label>
                        <input type="text" name="cin_posicion" id="cin_posicion" class="form-input"
                               value="<?= htmlspecialchars($bien['cin_posicion'] ?? '') ?>">
                    </div>
                </div>
                <div style="margin-top:.75rem">
                    <label style="font-size:.75rem;font-weight:700;text-transform:uppercase;color:#718096">C.I.N Actual</label>
                    <div class="cin-preview" id="cinPreview" style="margin-top:.3rem">
                        <?= htmlspecialchars(implode(' / ', array_filter([
                            $bien['cin_edificio'] ?? $bien['edificio'] ?? '',
                            $bien['cin_piso'] ?? $bien['piso'] ?? '',
                            $bien['cin_departamento'] ?? $bien['nombre_area'] ?? '',
                            $bien['cin_oficina'] ?? '',
                            $bien['cin_posicion'] ?? '',
                        ]))) ?: '—' ?>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title">Datos Económicos</div>
                <div class="form-grid form-grid-3">
                    <div class="form-group">
                        <label>Valor Unitario (Bs.)</label>
                        <input type="number" name="valor_inicial" class="form-input" step="0.01" min="0"
                               value="<?= htmlspecialchars($bien['valor_inicial'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Valor Residual (Bs.)</label>
                        <input type="number" name="valor_residual" class="form-input" step="0.01" min="0"
                               value="<?= htmlspecialchars($bien['valor_residual'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Fecha Adquisición</label>
                        <input type="date" name="fecha_adquisicion" class="form-input"
                               value="<?= htmlspecialchars($bien['fecha_adquisicion'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title">Observaciones</div>
                <div class="form-group">
                    <textarea name="observaciones" class="form-textarea"><?= htmlspecialchars($bien['observaciones'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success">Guardar Cambios</button>
                <a href="<?= $base_url ?>/bienes/<?= $bien['id_bien'] ?>" class="btn btn-secondary">Cancelar</a>
            </div>
            <div id="formMessage" class="message"></div>
        </form>
    </div>
</main>

<script>
const BASE    = '<?= $base_url ?>';
const BIEN_ID = <?= (int)$bien['id_bien'] ?>;

function toggleNroBien(cb) {
    const input = document.getElementById('nro_bien_ministerio');
    input.disabled = cb.checked;
    input.style.opacity = cb.checked ? '.4' : '1';
}

function updateCIN() {
    const areaEl = document.getElementById('id_area');
    const opt    = areaEl.options[areaEl.selectedIndex];
    const edif   = opt?.dataset.edificio || '';
    const piso   = opt?.dataset.piso !== undefined ? (opt.dataset.piso == -1 ? 'Sótano' : (opt.dataset.piso == 0 ? 'P.Baja' : 'P' + opt.dataset.piso)) : '';
    const depto  = opt?.dataset.depto || '';
    const ofic   = document.getElementById('cin_oficina').value.trim();
    const pos    = document.getElementById('cin_posicion').value.trim();
    const parts  = [edif, piso, depto, ofic, pos].filter(Boolean);
    document.getElementById('cinPreview').textContent = parts.length ? parts.join(' / ') : '—';
}

document.getElementById('id_area').addEventListener('change', updateCIN);
document.getElementById('cin_oficina').addEventListener('input', updateCIN);
document.getElementById('cin_posicion').addEventListener('input', updateCIN);

document.getElementById('editBienForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const msg = document.getElementById('formMessage');
    msg.className = 'message';
    document.querySelectorAll('.form-error').forEach(el => el.textContent = '');

    const res  = await fetch(`${BASE}/bienes/${BIEN_ID}`, { method:'PUT', body: new URLSearchParams(new FormData(this)) });
    const data = await res.json();

    if (data.success) {
        msg.className = 'message success';
        msg.textContent = '✓ Cambios guardados';
        setTimeout(() => window.location.href = BASE + data.redirect, 800);
    } else if (data.errors) {
        Object.entries(data.errors).forEach(([k,v]) => {
            const el = document.getElementById(k + '-error');
            if (el) el.textContent = v;
        });
    } else {
        msg.className = 'message error';
        msg.textContent = data.error || 'Error al guardar';
    }
});
</script>
