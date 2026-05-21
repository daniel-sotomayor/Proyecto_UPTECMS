<?php
/**
 * =============================================================================
 * CONTROLADOR: ADMINISTRACIÓN DE USUARIOS
 * =============================================================================
 * 
 * Gestiona la administración de usuarios del sistema:
 * - Listado de usuarios con roles y estados
 * - Creación de nuevos usuarios con generación de contraseña temporal
 * - Edición de datos y roles
 * - Activación/desactivación de cuentas
 * - Registro de auditoría de todas las acciones
 * 
 * Roles del sistema:
 * - administrador: Acceso total al sistema
 * - gerencia_bn: Gestión de bienes y aprobación de actas
 * - controlador_inventario: Control y verificación física
 * - registrador: Registro de nuevos bienes
 * - consultor: Solo lectura de reportes
 * 
 * @package App\Controllers
 * @author  MCP Development Team
 * @version 1.0.0
 * =============================================================================
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Helpers\AuditTrait;

class AdminController extends Controller
{
    use AuditTrait;
    /**
     * Listar usuarios
     */
    public function usuarios(): void
    {
        $usuarios = Database::fetchAll(
            "SELECT u.id_usuario, u.username, u.cedula, u.primer_nombre, u.segundo_nombre,
                    u.primer_apellido, u.segundo_apellido, u.email, u.cargo,
                    u.activo, u.primer_login, u.ultimo_acceso, r.nombre as rol
            FROM usuarios u
            LEFT JOIN roles r ON u.id_rol = r.id_rol
            ORDER BY u.primer_apellido, u.primer_nombre"
        );

        $this->title = 'Gestión de Usuarios';
        $this->renderWithLayout('admin/usuarios', ['usuarios' => $usuarios]);
    }

    /**
     * Formulario crear usuario
     */
    public function createUser(): void
    {
        $roles = Database::fetchAll("SELECT id_rol, nombre FROM roles WHERE activo = 1 ORDER BY nombre");

        $this->title = 'Nuevo Usuario';
        $this->renderWithLayout('admin/create_user', [
            'roles' => $roles,
            'csrf_token' => $this->generateCSRFToken(),
        ]);
    }

    /**
     * Guardar nuevo usuario
     */
    public function storeUser(): void
    {
        if (!$this->verifyCSRFToken($this->getInput('csrf_token'))) {
            $this->json(['error' => 'Token de seguridad inválido'], 403);
            return;
        }

        $data = [
            'primer_nombre'    => trim($this->getInput('primer_nombre')),
            'segundo_nombre'   => trim($this->getInput('segundo_nombre')),
            'primer_apellido'  => trim($this->getInput('primer_apellido')),
            'segundo_apellido' => trim($this->getInput('segundo_apellido')),
            'cedula'           => trim($this->getInput('cedula')),
            'email'            => trim($this->getInput('email')),
            'cargo'            => trim($this->getInput('cargo')),
            'id_rol'           => (int) $this->getInput('id_rol'),
            'clave_temporal'   => trim($this->getInput('clave_temporal')),
        ];

        $errors = $this->validateUserData($data);
        if (!empty($errors)) {
            $this->json(['errors' => $errors], 400);
            return;
        }

        $username      = $this->generateUsername($data['primer_nombre'], $data['primer_apellido']);
        $password_hash = password_hash($data['clave_temporal'], PASSWORD_BCRYPT, ['cost' => 12]);

        Database::query(
            'INSERT INTO usuarios
                (id_rol, cedula, username, primer_nombre, segundo_nombre,
                 primer_apellido, segundo_apellido, nombre_completo,
                 email, password_hash, cargo, primer_login, activo)
             VALUES
                (:rol_id, :cedula, :username, :primer_nombre, :segundo_nombre,
                 :primer_apellido, :segundo_apellido, :nombre_completo,
                 :email, :password_hash, :cargo, 1, 1)',
            [
                'rol_id'          => (int) $data['id_rol'],
                'cedula'          => $data['cedula'],
                'username'        => $username,
                'primer_nombre'   => $data['primer_nombre'],
                'segundo_nombre'  => $data['segundo_nombre'],
                'primer_apellido' => $data['primer_apellido'],
                'segundo_apellido'=> $data['segundo_apellido'],
                'nombre_completo' => trim($data['primer_nombre'] . ' ' . $data['segundo_nombre'] . ' ' . $data['primer_apellido'] . ' ' . $data['segundo_apellido']),
                'email'           => $data['email'],
                'password_hash'   => $password_hash,
                'cargo'           => $data['cargo'],
            ]
        );

        $userId = Database::lastInsertId();
        $this->logAudit('INSERT', 'usuarios', $userId);

        $this->json([
            'success'  => true,
            'username' => $username,
            'message'  => "Usuario creado. Username: {$username}",
            'redirect' => '/usuarios',
        ]);
    }

    /**
     * Formulario editar usuario
     */
    public function editUser(int $id): void
    {
        $usuario = Database::fetch(
            "SELECT id_usuario, id_rol, cedula, username, primer_nombre, segundo_nombre,
                    primer_apellido, segundo_apellido, email, telefono,
                    cargo, ultimo_acceso, intentos_fallidos, bloqueado_hasta, primer_login, activo
             FROM usuarios WHERE id_usuario = :id",
            ['id' => $id]
        );

        if (!$usuario) {
            $this->notFound();
            return;
        }

        $roles = Database::fetchAll("SELECT id_rol, nombre FROM roles WHERE activo = 1 ORDER BY nombre");

        $this->title = 'Editar Usuario';
        $this->renderWithLayout('admin/edit_user', [
            'usuario'    => $usuario,
            'roles'      => $roles,
            'csrf_token' => $this->generateCSRFToken(),
        ]);
    }

    /**
     * Actualizar usuario
     */
    public function updateUser(int $id): void
    {
        if (!$this->verifyCSRFToken($this->getInput('csrf_token'))) {
            $this->json(['error' => 'Token de seguridad inválido'], 403);
            return;
        }

        $activo  = $this->getInput('activo') === '1' ? 1 : 0;
        $id_rol  = (int) $this->getInput('id_rol');
        $cargo   = trim($this->getInput('cargo'));
        $email   = trim($this->getInput('email'));

        // Resetear clave temporal si se indica
        $nuevaClave = trim($this->getInput('nueva_clave_temporal'));
        if (!empty($nuevaClave)) {
            $errors = $this->validatePassword($nuevaClave);
            if (!empty($errors)) {
                $this->json(['errors' => $errors], 400);
                return;
            }
            Database::query(
                "UPDATE usuarios SET id_rol=:rol, cargo=:cargo, email=:email, activo=:activo,
                  password_hash=:hash, primer_login=1 WHERE id_usuario=:id",
                ['rol'=>$id_rol,'cargo'=>$cargo,'email'=>$email,'activo'=>$activo,
                 'hash'=>password_hash($nuevaClave, PASSWORD_BCRYPT, ['cost'=>12]),'id'=>$id]
            );
        } else {
            Database::query(
                "UPDATE usuarios SET id_rol=:rol, cargo=:cargo, email=:email, activo=:activo WHERE id_usuario=:id",
                ['rol'=>$id_rol,'cargo'=>$cargo,'email'=>$email,'activo'=>$activo,'id'=>$id]
            );
        }

        $this->logAudit('UPDATE', 'usuarios', $id);
        $this->json(['success' => true, 'redirect' => '/usuarios']);
    }

    /**
     * Eliminar usuario
     */
    public function deleteUser(int $id): void
    {
        if (!$this->verifyCSRF()) { // Use the new flexible verifyCSRF method
            $this->json(['error' => 'Token de seguridad inválido'], 403);
            return;
        }

        // No permitir eliminar al propio usuario
        if ($id === (int) Session::get('user_id')) {
            $this->json(['error' => 'No puede eliminar su propio usuario'], 400);
            return;
        }

        // Verificar que el usuario existe
        $user = Database::fetch("SELECT id_usuario FROM usuarios WHERE id_usuario = :id", ['id' => $id]);
        if (!$user) {
            $this->json(['error' => 'Usuario no encontrado'], 404);
            return;
        }

        Database::query("UPDATE usuarios SET activo = 0 WHERE id_usuario = :id", ['id' => $id]);
        $this->logAudit('DELETE', 'usuarios', $id);
        $this->json(['success' => true]);
    }

    /**
     * Asignar rol `validador_inventario` a un usuario (rápido, solo admin).
     */
    public function assignValidador(int $id): void
    {
        if (!$this->verifyCSRF()) {
            $this->json(['error' => 'Token CSRF inválido'], 403);
            return;
        }

        $role = Database::fetch('SELECT id_rol FROM roles WHERE nombre = :n LIMIT 1', ['n' => 'validador_inventario']);
        if (!$role) {
            $this->json(['error' => 'Rol validador_inventario no existe'], 400);
            return;
        }

        $user = Database::fetch('SELECT id_usuario FROM usuarios WHERE id_usuario = :id', ['id' => $id]);
        if (!$user) { $this->json(['error' => 'Usuario no encontrado'], 404); return; }

        Database::query('UPDATE usuarios SET id_rol = :r WHERE id_usuario = :id', ['r' => $role['id_rol'], 'id' => $id]);
        $this->logAudit('UPDATE', 'usuarios', $id);
        $this->json(['success' => true, 'message' => 'Rol validador asignado']);
    }

    /**
     * Previsualizar username (AJAX)
     */
    public function previewUsername(): void
    {
        $primerNombre   = trim($this->getInput('primer_nombre'));
        $primerApellido = trim($this->getInput('primer_apellido'));

        if (empty($primerNombre) || empty($primerApellido)) {
            $this->json(['username' => '']);
            return;
        }

        $username = $this->generateUsername($primerNombre, $primerApellido);
        $this->json(['username' => $username]);
    }

    // ─── Métodos privados ────────────────────────────────────────────────────

    /**
     * Genera username: 1ª letra del nombre + apellido, con desambiguación
     * Ej: Daniel Sotomayor → dsotomayor
     *     Si existe → dasotomayor → dasotomayor → etc.
     */
    private function generateUsername(string $primerNombre, string $primerApellido): string
    {
        $nombre   = $this->normalizeStr($primerNombre);
        $apellido = $this->normalizeStr($primerApellido);

        // Intentar con 1 letra, luego 2, 3... hasta agotar el nombre
        for ($letras = 1; $letras <= mb_strlen($nombre); $letras++) {
            $candidate = mb_substr($nombre, 0, $letras) . $apellido;
            if (!$this->usernameExists($candidate)) {
                return $candidate;
            }
        }

        // Si se agotaron las letras del nombre, agregar sufijo numérico
        $base = $nombre . $apellido;
        $i = 2;
        while ($this->usernameExists($base . $i)) {
            $i++;
        }
        return $base . $i;
    }

    private function usernameExists(string $username): bool
    {
        $row = Database::fetch(
            "SELECT id_usuario FROM usuarios WHERE username = :u",
            ['u' => $username]
        );
        return $row !== null;
    }

    /**
     * Convierte a minúsculas sin tildes ni caracteres especiales
     */
    private function normalizeStr(string $str): string
    {
        $str = mb_strtolower(trim($str), 'UTF-8');
        $from = ['á','é','í','ó','ú','ü','ñ','à','è','ì','ò','ù'];
        $to   = ['a','e','i','o','u','u','n','a','e','i','o','u'];
        $str  = str_replace($from, $to, $str);
        // Quitar todo lo que no sea a-z
        return preg_replace('/[^a-z]/', '', $str);
    }

    private function validateUserData(array $data): array
    {
        $errors = [];

        // Validación de nombres (solo letras, espacios y tildes)
        $soloLetrasPattern = '/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/';
        
        if (empty($data['primer_nombre'])) {
            $errors['primer_nombre'] = 'El primer nombre es requerido';
        } elseif (!preg_match($soloLetrasPattern, $data['primer_nombre'])) {
            $errors['primer_nombre'] = 'Solo letras y espacios permitidos';
        } elseif (strlen($data['primer_nombre']) < 2 || strlen($data['primer_nombre']) > 50) {
            $errors['primer_nombre'] = 'Entre 2 y 50 caracteres';
        }
        
        if (!empty($data['segundo_nombre']) && !preg_match($soloLetrasPattern, $data['segundo_nombre'])) {
            $errors['segundo_nombre'] = 'Solo letras y espacios permitidos';
        }

        if (empty($data['primer_apellido'])) {
            $errors['primer_apellido'] = 'El primer apellido es requerido';
        } elseif (!preg_match($soloLetrasPattern, $data['primer_apellido'])) {
            $errors['primer_apellido'] = 'Solo letras y espacios permitidos';
        } elseif (strlen($data['primer_apellido']) < 2 || strlen($data['primer_apellido']) > 50) {
            $errors['primer_apellido'] = 'Entre 2 y 50 caracteres';
        }
        
        if (!empty($data['segundo_apellido']) && !preg_match($soloLetrasPattern, $data['segundo_apellido'])) {
            $errors['segundo_apellido'] = 'Solo letras y espacios permitidos';
        }

        // Validación de cédula (solo números, 6-9 dígitos - rango real Venezuela)
        if (empty($data['cedula'])) {
            $errors['cedula'] = 'La cédula es requerida';
        } elseif (!preg_match('/^\d{6,9}$/', $data['cedula'])) {
            $errors['cedula'] = 'Debe contener entre 6 y 9 números';
        } else {
            $exists = Database::fetch(
                "SELECT id_usuario FROM usuarios WHERE cedula = :c",
                ['c' => $data['cedula']]
            );
            if ($exists) {
                $errors['cedula'] = 'La cédula ya está registrada';
            }
        }
        if (empty($data['email'])) {
            $errors['email'] = 'El correo es requerido';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Correo inválido';
        } else {
            $exists = Database::fetch(
                "SELECT id_usuario FROM usuarios WHERE email = :e",
                ['e' => $data['email']]
            );
            if ($exists) {
                $errors['email'] = 'El correo ya está registrado';
            }
        }
        if (empty($data['id_rol'])) {
            $errors['id_rol'] = 'El rol es requerido';
        }

        $pwErrors = $this->validatePassword($data['clave_temporal']);
        $errors = array_merge($errors, $pwErrors);

        return $errors;
    }

    private function validatePassword(string $password): array
    {
        $errors = [];
        if (empty($password)) {
            $errors['clave_temporal'] = 'La clave temporal es requerida';
        } elseif (strlen($password) < 8) {
            $errors['clave_temporal'] = 'La clave debe tener mínimo 8 caracteres';
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $errors['clave_temporal'] = 'La clave debe tener al menos una mayúscula';
        } elseif (!preg_match('/[a-z]/', $password)) {
            $errors['clave_temporal'] = 'La clave debe tener al menos una minúscula';
        } elseif (!preg_match('/[0-9]/', $password)) {
            $errors['clave_temporal'] = 'La clave debe tener al menos un número';
        } elseif (!preg_match('/[\W_]/', $password)) {
            $errors['clave_temporal'] = 'La clave debe tener al menos un carácter especial';
        }
        return $errors;
    }
}