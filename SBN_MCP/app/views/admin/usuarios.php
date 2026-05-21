<?php
/**
 * Vista: Gestión de Usuarios
 *
 * @var array  $usuarios Lista de usuarios del sistema.
 * @var string $base_url URL base de la aplicación.
 */

require APP_PATH . '/views/partials/sidebar.php';
?>

<main class="main-content" id="mainContent">

    <div class="page-header">
        <h1>Gestión de Usuarios</h1>
        <a href="<?= $base_url ?>/usuarios/nuevo" class="btn btn-success btn-sm">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="8.5" cy="7" r="4"/>
                <line x1="20" y1="8" x2="20" y2="14"/>
                <line x1="23" y1="11" x2="17" y2="11"/>
            </svg>
            Nuevo Usuario
        </a>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table class="data-table" aria-label="Lista de usuarios del sistema">
                <thead>
                    <tr>
                        <th scope="col">Username</th>
                        <th scope="col">Nombre Completo</th>
                        <th scope="col">Cédula</th>
                        <th scope="col">Rol</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Primer Login</th>
                        <th scope="col">Último Acceso</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($usuarios)): ?>
                    <tr class="empty-row">
                        <td colspan="8">No hay usuarios registrados</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($usuarios as $u):
                        $nombreCompleto = htmlspecialchars(trim(
                            ($u['primer_nombre']    ?? '') . ' ' .
                            ($u['segundo_nombre']   ?? '') . ' ' .
                            ($u['primer_apellido']  ?? '') . ' ' .
                            ($u['segundo_apellido'] ?? '')
                        ));
                    ?>
                    <tr>
                        <td>
                            <code style="font-weight:700;font-size:.85rem">
                                <?= htmlspecialchars($u['username'] ?? '') ?>
                            </code>
                        </td>
                        <td style="font-size:.875rem"><?= $nombreCompleto ?></td>
                        <td style="font-size:.875rem"><?= htmlspecialchars($u['cedula'] ?? '') ?></td>
                        <td>
                            <span class="badge badge-sm" style="background:var(--primary)">
                                <?= htmlspecialchars($u['rol'] ?? '') ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge" style="background:<?= $u['activo'] ? '#38a169' : '#e53e3e' ?>">
                                <?= $u['activo'] ? 'Activo' : 'Inactivo' ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($u['primer_login']): ?>
                                <span class="badge badge-sm" style="background:#d69e2e">Pendiente</span>
                            <?php else: ?>
                                <span class="badge badge-sm" style="background:#718096">Completado</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:.8rem;color:var(--gray-500)">
                            <?= $u['ultimo_acceso']
                                ? date('d/m/Y H:i', strtotime($u['ultimo_acceso']))
                                : 'Nunca' ?>
                        </td>
                        <td>
                            <div style="display:flex;gap:.3rem">
                                <a href="<?= $base_url ?>/usuarios/<?= (int)$u['id_usuario'] ?>/editar"
                                   class="btn btn-sm btn-secondary"
                                   aria-label="Editar usuario <?= htmlspecialchars($u['username'] ?? '') ?>">
                                    Editar
                                </a>
                                <?php if (($u['rol'] ?? '') !== 'validador_inventario'): ?>
                                <form method="post" action="<?= $base_url ?>/usuarios/<?= (int)$u['id_usuario'] ?>/asignar-validador" style="display:inline">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <button class="btn btn-sm btn-info" type="submit">Asignar Validador</button>
                                </form>
                                <?php else: ?>
                                <span class="badge badge-sm" style="background:#38a169">Validador</span>
                                <?php endif; ?>
                                <?php if ((int)$u['id_usuario'] !== (int)($_SESSION['user_id'] ?? 0)): ?>
                                <button class="btn btn-sm btn-danger"
                                        onclick="desactivarUsuario(<?= (int)$u['id_usuario'] ?>, '<?= htmlspecialchars($u['username'] ?? '') ?>')"
                                        aria-label="Desactivar usuario <?= htmlspecialchars($u['username'] ?? '') ?>">
                                    Desactivar
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<script>
'use strict';
async function desactivarUsuario(id, username) {
    if (!confirm(`¿Desactivar al usuario "${username}"? Podrá reactivarlo editando el usuario.`)) return;

    const csrfToken = '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>';
    const BASE      = '<?= $base_url ?>';

    try {
        const res  = await fetch(`${BASE}/usuarios/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-Token': csrfToken }
        });
        const data = await res.json();

        if (data.success) {
            Toast.success(`Usuario "${username}" desactivado correctamente`);
            setTimeout(() => location.reload(), 1000);
        } else {
            Toast.error(data.error || 'Error al desactivar el usuario');
        }
    } catch {
        Toast.error('Error de conexión. Intente nuevamente.');
    }
}
</script>
