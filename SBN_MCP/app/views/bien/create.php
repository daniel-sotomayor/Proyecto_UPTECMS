<?php require APP_PATH . '/views/partials/sidebar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <h1>Registrar Nuevo Bien</h1>
        <a href="<?= $base_url ?>/bienes" class="btn btn-secondary btn-sm">← Volver al inventario</a>
    </div>

    <div class="form-card">
        <!-- Progress Bar -->
        <div class="form-progress">
            <div class="form-step active" data-step="1">
                <div class="form-step-circle">1</div>
                <div class="form-step-label">Clasificación</div>
            </div>
            <div class="form-step" data-step="2">
                <div class="form-step-circle">2</div>
                <div class="form-step-label">Identificación</div>
            </div>
            <div class="form-step" data-step="3">
                <div class="form-step-circle">3</div>
                <div class="form-step-label">Ubicación</div>
            </div>
            <div class="form-step" data-step="4">
                <div class="form-step-circle">4</div>
                <div class="form-step-label">Responsable</div>
            </div>
            <div class="form-step" data-step="5">
                <div class="form-step-circle">5</div>
                <div class="form-step-label">Datos Económicos</div>
            </div>
            <div class="form-step" data-step="6">
                <div class="form-step-circle">6</div>
                <div class="form-step-label">Observaciones</div>
            </div>
        </div>

        <form id="bienForm" method="POST" action="<?= $base_url ?>/bienes" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

            <!-- Step 1: Clasificación -->
            <div class="form-tab-content active" data-step="1">
                <div class="form-section">
                    <h3 class="form-section-title">Clasificación y Codificación</h3>
                    <div class="form-grid form-grid-3">
                        <div class="form-group">
                            <label>Clasificación (Grupo) <span class="req">*</span></label>
                            <select name="id_tipo" id="id_tipo" class="form-control" required>
                                <option value="">— Seleccione —</option>
                                <?php foreach ($tipos as $t): ?>
                                <option value="<?= $t['id_tipo'] ?>" data-codigo="<?= htmlspecialchars($t['codigo']) ?>">
                                    <?= htmlspecialchars($t['codigo'] . ' — ' . $t['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="form-error" id="id_tipo-error"></span>
                        </div>
                        <div class="form-group">
                            <label>Código Interno (Pub. 9)</label>
                            <div class="cin-preview" id="codigoInternoPreview" style="min-height:38px">
                                Se genera automáticamente
                            </div>
                            <span class="form-hint">Generado al seleccionar clasificación y área</span>
                        </div>
                        <div class="form-group">
                            <label>Código Ministerio de Salud</label>
                            <input type="text" name="codigo_ministerio" id="codigo_ministerio" class="form-control" placeholder="Ej: MS-2026-001">
                            <span class="form-error" id="codigo_ministerio-error"></span>
                        </div>
                    </div>

                    <div class="form-grid form-grid-3" style="margin-top:1rem">
                        <?php if ($puedeAsignarNro): ?>
                        <input type="hidden" name="es_sn" id="es_sn" value="0">
                        <div class="form-group">
                            <label>Nro. de Bien (Ministerio)</label>
                            <input type="text" name="nro_bien_ministerio" id="nro_bien_ministerio" class="form-control"
                                   placeholder="Ej: 1234567ABC" maxlength="10" pattern="[A-Za-z0-9]{6,10}">
                            <span class="form-error" id="nro_bien_ministerio-error"></span>
                            <span class="form-hint">Formato: 6 a 10 caracteres alfanuméricos (letras y números). Marque S/N si no aplica.</span>
                        </div>
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <label class="check-group" style="cursor:pointer">
                                <input type="checkbox" id="es_sn_checkbox" value="1" onchange="toggleNroBien(this)">
                                <span style="font-size:.875rem;font-weight:600">S/N — Sin número asignado</span>
                            </label>
                        </div>
                        <?php else: ?>
                        <div class="form-group">
                            <label>Nro. de Bien (Ministerio)</label>
                            <div class="cin-preview" style="background:#fefcbf;border-color:#d69e2e;color:#744210;font-size:.85rem">
                                Será asignado por la Gerente de Bienes Nacionales
                            </div>
                            <input type="hidden" name="es_sn" id="es_sn" value="1">
                        </div>
                        <?php endif; ?>
                        <div class="form-group">
                            <label>Estado <span class="req">*</span></label>
                            <select name="id_estado" class="form-control" required>
                                <option value="">— Seleccione —</option>
                                <?php foreach ($estados as $e): ?>
                                <option value="<?= $e['id_estado'] ?>"><?= htmlspecialchars($e['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="form-error" id="id_estado-error"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Identificación -->
            <div class="form-tab-content" data-step="2">
                <div class="form-section">
                    <h3 class="form-section-title">Identificación del Bien</h3>
                    <div class="form-grid">
                        <div class="form-group full">
                            <label>Nombre del Bien <span class="req">*</span></label>
                            <input type="text" name="nombre" class="form-control" 
                                   placeholder="Nombre descriptivo del bien" 
                                   required minlength="3" maxlength="200">
                            <span class="form-error" id="nombre-error"></span>
                        </div>
                        <div class="form-group full">
                            <label>Descripción Específica</label>
                            <textarea name="descripcion" class="form-control" 
                                      placeholder="Descripción detallada..." 
                                      maxlength="1000" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Marca</label>
                            <input type="text" name="marca" class="form-control" maxlength="100">
                        </div>
                        <div class="form-group">
                            <label>Modelo</label>
                            <input type="text" name="modelo" class="form-control" maxlength="100">
                        </div>
                        <div class="form-group">
                            <label>Serial / Nro. Serie</label>
                            <input type="text" name="serial" class="form-control" maxlength="100">
                            <span class="form-error" id="serial-error"></span>
                        </div>
                        <div class="form-group">
                            <label>Color</label>
                            <input type="text" name="color" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Cantidad</label>
                            <input type="number" name="cantidad" class="form-control" value="1" min="1" max="9999" required>
                        </div>
                        <div class="form-group">
                            <label>Año de Fabricación</label>
                            <input type="number" name="anio_fabricacion" class="form-control" min="1900" max="<?= date('Y') ?>">
                        </div>
                        <div class="form-group">
                            <label>Condición Inicial</label>
                            <select name="condicion_inicial" class="form-control">
                                <option value="">— Seleccione —</option>
                                <option value="Nuevo">Nuevo</option>
                                <option value="Bueno">Bueno</option>
                                <option value="Regular">Regular</option>
                                <option value="Malo">Malo</option>
                            </select>
                        </div>
                        <div class="form-group full">
                            <label>Foto del Bien</label>
                            <input type="file" name="imagen" id="imagen" class="form-control" accept="image/*">
                            <span class="form-hint">Opcional. Seleccione una imagen del bien (JPG, PNG, etc.)</span>
                            <div id="imagenPreview" style="margin-top:0.75rem; display:none; max-width:200px;">
                                <img id="imagenImg" src="" alt="Vista previa" style="max-width:100%; border-radius:8px; border:1px solid #e2e8f0;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: Ubicación -->
            <div class="form-tab-content" data-step="3">
                <div class="form-section">
                    <h3 class="form-section-title">Ubicación — C.I.N</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Área / Departamento <span class="req">*</span></label>
                            <select name="id_area" id="id_area" class="form-control" required>
                                <option value="">— Seleccione —</option>
                                <?php
                                $edificioActual = '';
                                foreach ($areas as $a):
                                    if ($a['edificio'] !== $edificioActual):
                                        if ($edificioActual !== '') echo '</optgroup>';
                                        echo '<optgroup label="Edificio ' . htmlspecialchars($a['edificio']) . '">';
                                        $edificioActual = $a['edificio'];
                                    endif;
                                    $pisoLabel = $a['piso'] == -1 ? 'Sótano' : ($a['piso'] == 0 ? 'P.Baja' : 'Piso ' . $a['piso']);
                                ?>
                                <option value="<?= $a['id_area'] ?>"
                                        data-edificio="<?= htmlspecialchars($a['edificio']) ?>"
                                        data-piso="<?= htmlspecialchars($a['piso']) ?>"
                                        data-depto="<?= htmlspecialchars($a['nombre_area']) ?>">
                                    <?= htmlspecialchars($pisoLabel . ' — ' . $a['nombre_area']) ?>
                                </option>
                                <?php endforeach; if ($edificioActual !== '') echo '</optgroup>'; ?>
                            </select>
                            <span class="form-error" id="id_area-error"></span>
                        </div>
                        <div class="form-group">
                            <label>Oficina / Sub-área</label>
                            <input type="text" name="cin_oficina" id="cin_oficina" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Nro. de Posición</label>
                            <input type="text" name="cin_posicion" id="cin_posicion" class="form-control">
                        </div>
                    </div>
                    <div style="margin-top:.75rem">
                        <label style="font-size:.75rem;font-weight:700;text-transform:uppercase;color:#718096">C.I.N Generado</label>
                        <div class="cin-preview" id="cinPreview" style="margin-top:.3rem">—</div>
                    </div>
                </div>
            </div>

            <!-- Step 4: Responsable -->
            <div class="form-tab-content" data-step="4">
                <div class="form-section">
                    <h3 class="form-section-title">Responsable del Bien (Opcional)</h3>
                    <p style="font-size:0.875rem; color:#64748b; margin-bottom:1.5rem;">Datos de la persona a la que se le entrega o asigna el bien. Todos los campos son opcionales.</p>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Responsable</label>
                            <select name="responsable_id" class="form-control">
                                <option value="">— Sin asignar —</option>
                                <?php foreach ($personal as $p): ?>
                                <option value="<?= $p['id_usuario'] ?>">
                                    <?= htmlspecialchars($p['nombre_completo']) ?>
                                    <?= !empty($p['cargo']) ? ' — ' . htmlspecialchars($p['cargo']) : '' ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Número de Cédula</label>
                            <input type="text" name="responsable_cedula" class="form-control" placeholder="Ej: V-12345678" maxlength="15">
                        </div>
                        <div class="form-group full">
                            <label>Foto del Responsable</label>
                            <input type="file" name="responsable_foto" id="responsable_foto" class="form-control" accept="image/*">
                            <span class="form-hint">Opcional. Seleccione una foto del responsable (JPG, PNG, etc.)</span>
                            <div id="responsableFotoPreview" style="margin-top:0.75rem; display:none; max-width:200px;">
                                <img id="responsableFotoImg" src="" alt="Vista previa" style="max-width:100%; border-radius:8px; border:1px solid #e2e8f0;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 5: Datos Económicos -->
            <div class="form-tab-content" data-step="5">
                <div class="form-section">
                    <h3 class="form-section-title">Datos Económicos</h3>
                    <div class="form-grid form-grid-3">
                        <div class="form-group">
                            <label>Valor Unitario (Bs.)</label>
                            <input type="number" name="valor_inicial" class="form-control" placeholder="0.00" step="0.01" min="0" value="0.0">
                        </div>
                        <div class="form-group">
                            <label>Valor Residual (Bs.)</label>
                            <input type="number" name="valor_residual" class="form-control" placeholder="0.00" step="0.01" min="0">
                        </div>
                        <div class="form-group">
                            <label>Vida Útil (años)</label>
                            <input type="number" name="vida_util_anos" class="form-control" value="10" min="1" max="100">
                        </div>
                        <div class="form-group">
                            <label>Nro. Factura</label>
                            <input type="text" name="numero_factura" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Fecha de Adquisición</label>
                            <input type="date" name="fecha_adquisicion" class="form-control" max="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 6: Observaciones -->
            <div class="form-tab-content" data-step="6">
                <div class="form-section">
                    <h3 class="form-section-title">Observaciones Adicionales</h3>
                    <div class="form-group">
                        <textarea name="observaciones" class="form-control" placeholder="Observaciones adicionales sobre el bien..." rows="5"></textarea>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <div class="form-navigation">
                <button type="button" class="btn btn-secondary" id="prevBtn" style="display:none">← Anterior</button>
                <button type="button" class="btn btn-primary" id="nextBtn">Siguiente →</button>
                <button type="submit" class="btn btn-success" id="submitBtn" style="display:none">✓ Guardar Bien</button>
            </div>
            <div id="formMessage" class="message"></div>
        </form>
    </div>
</main>

<!-- Modal de Éxito -->
<div id="successModal" class="modal-overlay" style="display: none;">
    <div class="modal-box">
        <div class="modal-icon success">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        </div>
        <h2 class="modal-title">¡Bien Registrado con Éxito!</h2>
        <p class="modal-message" id="modalMessage">El bien ha sido registrado correctamente en el inventario.</p>
        <button type="button" class="btn btn-primary" onclick="closeSuccessModal()">
            Aceptar
        </button>
    </div>
</div>

<script>
const BASE = '<?= $base_url ?>';
let currentStep = 1;
const totalSteps = 6;

function toggleNroBien(cb) {
    const input = document.getElementById('nro_bien_ministerio');
    const hiddenEsSn = document.getElementById('es_sn');
    if (hiddenEsSn) {
        hiddenEsSn.value = cb.checked ? '1' : '0';
    }
    if (input) {
        input.disabled = cb.checked;
        input.required = !cb.checked;
        if (cb.checked) {
            input.value = '';
        }
        input.style.opacity = cb.checked ? '.4' : '1';
        
        // Limpiar error si se marca S/N
        if (cb.checked) {
            const errorEl = document.getElementById('nro_bien_ministerio-error');
            if (errorEl) errorEl.textContent = '';
            input.classList.remove('is-invalid');
        }
    }
}

function updateCIN() {
    const areaEl = document.getElementById('id_area');
    const opt = areaEl.options[areaEl.selectedIndex];
    const edif = opt?.dataset.edificio || '';
    const piso = opt?.dataset.piso !== undefined ? (opt.dataset.piso == -1 ? 'Sótano' : (opt.dataset.piso == 0 ? 'P.Baja' : 'P' + opt.dataset.piso)) : '';
    const depto = opt?.dataset.depto || '';
    const ofic = document.getElementById('cin_oficina').value.trim();
    const pos = document.getElementById('cin_posicion').value.trim();
    const parts = [edif, piso, depto, ofic, pos].filter(Boolean);
    document.getElementById('cinPreview').textContent = parts.length ? parts.join(' / ') : '—';
}

function updateCodigoInterno() {
    const tipoEl = document.getElementById('id_tipo');
    const areaEl = document.getElementById('id_area');
    const tOpt = tipoEl.options[tipoEl.selectedIndex];
    const aOpt = areaEl.options[areaEl.selectedIndex];
    if (!tOpt?.value || !aOpt?.value) {
        document.getElementById('codigoInternoPreview').textContent = 'Se genera automáticamente';
        return;
    }
    const codigo = tOpt.dataset.codigo || '00';
    const edif = (aOpt.dataset.edificio || 'X').substring(0,3).toUpperCase();
    const piso = aOpt.dataset.piso || '0';
    document.getElementById('codigoInternoPreview').textContent = `${codigo}-${edif}-${piso}-####`;
}

function showStep(step) {
    document.querySelectorAll('.form-tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.form-step').forEach(el => el.classList.remove('active', 'completed'));
    
    const content = document.querySelector(`.form-tab-content[data-step="${step}"]`);
    if (content) content.classList.add('active');
    
    document.querySelectorAll('.form-step').forEach((el, idx) => {
        if (idx + 1 < step) el.classList.add('completed');
        if (idx + 1 === step) el.classList.add('active');
    });
    
    document.getElementById('prevBtn').style.display = step === 1 ? 'none' : 'inline-flex';
    document.getElementById('nextBtn').style.display = step === totalSteps ? 'none' : 'inline-flex';
    document.getElementById('submitBtn').style.display = step === totalSteps ? 'inline-flex' : 'none';
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

document.getElementById('nextBtn').addEventListener('click', () => {
    if (currentStep < totalSteps) {
        currentStep++;
        showStep(currentStep);
    }
});

document.getElementById('prevBtn').addEventListener('click', () => {
    if (currentStep > 1) {
        currentStep--;
        showStep(currentStep);
    }
});

document.getElementById('id_area').addEventListener('change', () => { updateCIN(); updateCodigoInterno(); });
document.getElementById('id_tipo').addEventListener('change', updateCodigoInterno);
document.getElementById('cin_oficina').addEventListener('input', updateCIN);
document.getElementById('cin_posicion').addEventListener('input', updateCIN);

// Validar Nro. de Bien: solo números y letras
const nroBienInput = document.getElementById('nro_bien_ministerio');
if (nroBienInput) {
    nroBienInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
    });
    
    // Validación en tiempo real - duplicados
    let timeoutId;
    nroBienInput.addEventListener('blur', async function() {
        const valor = this.value.trim();
        const errorEl = document.getElementById('nro_bien_ministerio-error');
        const esSn = document.getElementById('es_sn')?.checked;
        
        if (!valor || esSn || valor === 'S/N') {
            errorEl.textContent = '';
            this.classList.remove('is-invalid');
            return;
        }
        
        try {
            const res = await fetch(`${BASE}/bienes/validar-numero?nro=${encodeURIComponent(valor)}`);
            const data = await res.json();
            
            if (data.existe) {
                errorEl.textContent = 'Este número de bien ya está registrado';
                this.classList.add('is-invalid');
            } else {
                errorEl.textContent = '';
                this.classList.remove('is-invalid');
            }
        } catch (error) {
            console.error('Error validando número:', error);
        }
    });
}

// Validación en tiempo real - Serial duplicado
const serialInput = document.querySelector('[name="serial"]');
if (serialInput) {
    serialInput.addEventListener('blur', async function() {
        const valor = this.value.trim();
        const errorEl = document.getElementById('serial-error');
        
        if (!valor || valor.length < 3) {
            errorEl.textContent = '';
            this.classList.remove('is-invalid');
            return;
        }
        
        try {
            const res = await fetch(`${BASE}/bienes/validar-serial?serial=${encodeURIComponent(valor)}`);
            const data = await res.json();
            
            if (data.existe) {
                errorEl.textContent = 'Este serial ya está registrado en otro bien';
                this.classList.add('is-invalid');
            } else {
                errorEl.textContent = '';
                this.classList.remove('is-invalid');
            }
        } catch (error) {
            console.error('Error validando serial:', error);
        }
    });
}

// Validación en tiempo real - Código Ministerio duplicado
const codigoMinisterioInput = document.getElementById('codigo_ministerio');
if (codigoMinisterioInput) {
    codigoMinisterioInput.addEventListener('blur', async function() {
        const valor = this.value.trim();
        const errorEl = document.getElementById('codigo_ministerio-error');
        
        if (!valor) {
            errorEl.textContent = '';
            this.classList.remove('is-invalid');
            return;
        }
        
        try {
            const res = await fetch(`${BASE}/bienes/validar-codigo-ministerio?codigo=${encodeURIComponent(valor)}`);
            const data = await res.json();
            
            if (data.existe) {
                errorEl.textContent = 'Este código de ministerio ya está registrado en otro bien';
                this.classList.add('is-invalid');
            } else {
                errorEl.textContent = '';
                this.classList.remove('is-invalid');
            }
        } catch (error) {
            console.error('Error validando código ministerio:', error);
        }
    });
}

// Vista previa de imagen del bien
const imagenInput = document.getElementById('imagen');
if (imagenInput) {
    imagenInput.addEventListener('change', function() {
        const preview = document.getElementById('imagenPreview');
        const img = document.getElementById('imagenImg');
        
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = (e) => {
                img.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(this.files[0]);
        } else {
            preview.style.display = 'none';
        }
    });
}

// Vista previa de foto del responsable
const responsableFotoInput = document.getElementById('responsable_foto');
if (responsableFotoInput) {
    responsableFotoInput.addEventListener('change', function() {
        const preview = document.getElementById('responsableFotoPreview');
        const img = document.getElementById('responsableFotoImg');
        
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = (e) => {
                img.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(this.files[0]);
        } else {
            preview.style.display = 'none';
        }
    });
}

document.getElementById('bienForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const msg = document.getElementById('formMessage');
    const submitBtn = document.getElementById('submitBtn');
    msg.className = 'message';
    document.querySelectorAll('.form-error').forEach(el => el.textContent = '');
    document.querySelectorAll('.form-control').forEach(el => el.classList.remove('is-invalid'));

    // Deshabilitar botón
    submitBtn.disabled = true;
    submitBtn.textContent = 'Guardando...';

    try {
        const res = await fetch(`${BASE}/bienes`, { method: 'POST', body: new FormData(this) });
        
        console.log('Status:', res.status);
        console.log('Status Text:', res.statusText);
        
        const contentType = res.headers.get('content-type');
        console.log('Content-Type:', contentType);
        
        if (!contentType || !contentType.includes('application/json')) {
            const text = await res.text();
            console.error('Respuesta no es JSON:', text.substring(0, 500));
            throw new Error('El servidor no devolvió una respuesta JSON válida');
        }
        
        const data = await res.json();
        console.log('Respuesta del servidor:', data);
        
        // Manejar errores HTTP
        if (res.status >= 400) {
            if (data.errors) {
                // Errores de validación (400)
                handleValidationErrors(data.errors);
                submitBtn.disabled = false;
                submitBtn.innerHTML = '✓ Guardar Bien';
                return;
            } else if (data.error) {
                // Error del servidor (500)
                msg.className = 'message error';
                msg.textContent = '⚠ ' + data.error;
                submitBtn.disabled = false;
                submitBtn.innerHTML = '✓ Guardar Bien';
                return;
            }
        }

        if (data.success) {
            // Mostrar modal de éxito
            document.getElementById('modalMessage').textContent = data.message || 'El bien ha sido registrado correctamente.';
            const modal = document.getElementById('successModal');
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('active'), 10);
        } else if (data.errors) {
            handleValidationErrors(data.errors);
            submitBtn.disabled = false;
            submitBtn.innerHTML = '✓ Guardar Bien';
        } else {
            msg.className = 'message error';
            msg.textContent = data.error || 'Error al guardar';
            submitBtn.disabled = false;
            submitBtn.innerHTML = '✓ Guardar Bien';
        }
    } catch (error) {
        console.error('Error:', error);
        msg.className = 'message error';
        msg.textContent = 'Error de conexión con el servidor. Por favor, intente nuevamente.';
        submitBtn.disabled = false;
        submitBtn.innerHTML = '✓ Guardar Bien';
    }
});

function handleValidationErrors(errors) {
    const msg = document.getElementById('formMessage');
    // Mapear errores a pasos
    const errorSteps = {
        'id_tipo': 1, 'id_estado': 1, 'nro_bien_ministerio': 1,
        'nombre': 2, 'marca': 2, 'modelo': 2, 'serial': 2, 'color': 2, 'cantidad': 2, 'anio_fabricacion': 2, 'condicion_inicial': 2,
        'id_area': 3, 'cin_oficina': 3, 'cin_posicion': 3,
        'responsable_id': 4,
        'valor_inicial': 5, 'valor_residual': 5, 'vida_util_anos': 5, 'numero_factura': 5, 'fecha_adquisicion': 5,
        'observaciones': 6
    };
    
    let firstErrorStep = 6;
    Object.entries(errors).forEach(([k,v]) => {
        const el = document.getElementById(k + '-error');
        const input = document.querySelector(`[name="${k}"]`);
        if (el) el.textContent = v;
        if (input) input.classList.add('is-invalid');
        
        // Encontrar el primer paso con error
        if (errorSteps[k] && errorSteps[k] < firstErrorStep) {
            firstErrorStep = errorSteps[k];
        }
    });
    
    msg.className = 'message error';
    msg.textContent = '⚠ Corrija los errores indicados en el formulario';
    
    // Ir al primer paso con error
    currentStep = firstErrorStep;
    showStep(currentStep);
    
    // Scroll al primer error
    setTimeout(() => {
        const firstError = document.querySelector('.form-error:not(:empty)');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }, 100);
}

function closeSuccessModal() {
    const modal = document.getElementById('successModal');
    modal.classList.remove('active');
    setTimeout(() => {
        modal.style.display = 'none';
        window.location.href = BASE + '/bienes';
    }, 300);
}

showStep(1);
</script>

<style>
.form-control.is-invalid {
    border-color: var(--danger);
    background-color: #fef2f2;
}

.form-control.is-invalid:focus {
    border-color: var(--danger);
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
}

/* Modal de Éxito */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.modal-overlay.active {
    opacity: 1;
    visibility: visible;
}

.modal-box {
    background: white;
    border-radius: 16px;
    padding: 3rem 2.5rem;
    max-width: 450px;
    width: 90%;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    transform: scale(0.9);
    transition: transform 0.3s ease;
}

.modal-overlay.active .modal-box {
    transform: scale(1);
}

.modal-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1.5rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: scaleIn 0.5s ease;
}

.modal-icon.success {
    background: #dcfce7;
    color: var(--success);
}

.modal-icon svg {
    animation: checkmark 0.6s ease 0.2s both;
}

@keyframes scaleIn {
    from { transform: scale(0); }
    to { transform: scale(1); }
}

@keyframes checkmark {
    0% { stroke-dasharray: 0 100; }
    100% { stroke-dasharray: 100 100; }
}

.modal-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 1rem;
}

.modal-message {
    font-size: 1rem;
    color: var(--text-muted);
    margin-bottom: 2rem;
    line-height: 1.6;
}

.modal-box .btn {
    min-width: 150px;
    padding: 0.875rem 2rem;
}
</style>
