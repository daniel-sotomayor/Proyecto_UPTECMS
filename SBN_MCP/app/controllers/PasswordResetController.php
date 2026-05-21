<?php
/**
 * Password Reset Controller
 * Sistema de recuperación de contraseña con código de verificación
 *
 * @package App\Controllers
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Helpers\EmailHelper;
use App\Helpers\AuditTrait;

class PasswordResetController extends Controller
{
    use AuditTrait;

    private const CODE_EXPIRY_MINUTES = 30;
    private const MAX_DAILY_ATTEMPTS = 5;

    /**
     * Muestra formulario para solicitar recuperación
     */
    public function showForgotPassword(): void
    {
        if (Session::has('user_id')) {
            $this->redirect('/dashboard');
            return;
        }
        $this->title = 'Recuperar Contraseña';
        $this->render('auth/forgot_password', ['csrf_token' => $this->generateCSRFToken()]);
    }

    /**
     * Procesa solicitud de recuperación y envía código
     */
    public function sendResetCode(): void
    {
        if (!$this->verifyCSRFToken($this->getInput('csrf_token'), false)) {
            $this->json(['error' => 'Token de seguridad inválido'], 403);
            return;
        }

        $email = trim($this->getInput('email'));
        $cedula = trim($this->getInput('cedula'));

        if (empty($email) || empty($cedula)) {
            $this->json(['error' => 'Correo y cédula son requeridos'], 400);
            return;
        }

        // Buscar usuario
        $user = Database::fetch(
            "SELECT id_usuario, nombre_completo, email, activo FROM usuarios 
             WHERE email = :email AND cedula = :cedula LIMIT 1",
            ['email' => $email, 'cedula' => $cedula]
        );

        if (!$user) {
            // Respuesta genérica por seguridad (no revelar si existe)
            $this->json(['success' => true, 'message' => 'Si los datos son correctos, recibirás un código de verificación']);
            return;
        }

        if (!$user['activo']) {
            $this->json(['error' => 'Usuario inactivo. Contacte al administrador.'], 403);
            return;
        }

        // Verificar intentos del día
        $attemptsToday = $this->getDailyAttempts($user['id_usuario']);
        if ($attemptsToday >= self::MAX_DAILY_ATTEMPTS) {
            $this->json(['error' => 'Has excedido el límite de 5 intentos de recuperación por día. Intenta mañana.'], 429);
            return;
        }

        // Generar código de 6 dígitos
        $code = EmailHelper::generateVerificationCode(6);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::CODE_EXPIRY_MINUTES . ' minutes'));

        // Guardar en base de datos
        Database::query(
            'INSERT INTO password_resets (usuario_id, codigo, email, expires_at, ip_address, user_agent, intentos_dia, fecha_intento)
             VALUES (:user_id, :code, :email, :expires, :ip, :ua, 1, CURDATE())
             ON DUPLICATE KEY UPDATE
             codigo = VALUES(codigo),
             expires_at = VALUES(expires_at),
             intentos_dia = intentos_dia + 1,
             used_at = NULL',
            [
                'user_id' => (int) $user['id_usuario'],
                'code'    => $code,
                'email'   => $email,
                'expires' => $expiresAt,
                'ip'      => $_SERVER['REMOTE_ADDR'] ?? '',
                'ua'      => $_SERVER['HTTP_USER_AGENT'] ?? '',
            ]
        );

        // Enviar correo
        $sent = EmailHelper::sendPasswordReset($email, $user['nombre_completo'], $code, self::CODE_EXPIRY_MINUTES);

        if ($sent) {
            $this->logAudit('password_reset_requested', 'usuarios', $user['id_usuario']);
            $this->json(['success' => true, 'message' => 'Código de verificación enviado. Revisa tu correo.']);
        } else {
            $this->json(['error' => 'Error al enviar el correo. Intenta más tarde.'], 500);
        }
    }

    /**
     * Muestra formulario para verificar código
     */
    public function showVerifyCode(): void
    {
        if (Session::has('user_id')) {
            $this->redirect('/dashboard');
            return;
        }
        $this->title = 'Verificar Código';
        $this->render('auth/verify_code', ['csrf_token' => $this->generateCSRFToken()]);
    }

    /**
     * Verifica el código ingresado
     */
    public function verifyCode(): void
    {
        if (!$this->verifyCSRFToken($this->getInput('csrf_token'), false)) {
            $this->json(['error' => 'Token de seguridad inválido'], 403);
            return;
        }

        $code = trim($this->getInput('codigo'));
        $email = trim($this->getInput('email'));

        if (empty($code) || empty($email)) {
            $this->json(['error' => 'Código y correo son requeridos'], 400);
            return;
        }

        // Buscar código válido
        $reset = Database::fetch(
            "SELECT pr.*, u.id_usuario, u.nombre_completo 
             FROM password_resets pr
             JOIN usuarios u ON pr.usuario_id = u.id_usuario
             WHERE pr.codigo = :code 
             AND pr.email = :email
             AND pr.used_at IS NULL
             AND pr.expires_at > NOW()
             ORDER BY pr.created_at DESC
             LIMIT 1",
            ['code' => $code, 'email' => $email]
        );

        if (!$reset) {
            $this->json(['error' => 'Código inválido o expirado'], 400);
            return;
        }

        // Marcar código como usado
        Database::query(
            'UPDATE password_resets SET used_at = NOW() WHERE id_reset = :id',
            ['id' => (int) $reset['id_reset']]
        );

        // Crear sesión temporal para cambio de contraseña
        Session::set('reset_user_id', $reset['id_usuario']);
        Session::set('reset_verified', true);
        Session::set('reset_expires', time() + 600); // 10 minutos para cambiar

        $this->json(['success' => true, 'redirect' => '/restablecer-clave']);
    }

    /**
     * Muestra formulario para restablecer contraseña
     */
    public function showResetPassword(): void
    {
        if (!Session::get('reset_verified') || !Session::get('reset_user_id')) {
            $this->redirect('/recuperar-clave');
            return;
        }

        if (Session::get('reset_expires') < time()) {
            Session::forget('reset_user_id');
            Session::forget('reset_verified');
            Session::forget('reset_expires');
            $this->redirect('/recuperar-clave?error=expired');
            return;
        }

        $this->title = 'Restablecer Contraseña';
        $this->render('auth/reset_password', ['csrf_token' => $this->generateCSRFToken()]);
    }

    /**
     * Procesa el cambio de contraseña
     */
    public function resetPassword(): void
    {
        if (!$this->verifyCSRFToken($this->getInput('csrf_token'), false)) {
            $this->json(['error' => 'Token de seguridad inválido'], 403);
            return;
        }

        if (!Session::get('reset_verified') || !Session::get('reset_user_id')) {
            $this->json(['error' => 'Sesión de recuperación inválida'], 403);
            return;
        }

        if (Session::get('reset_expires') < time()) {
            $this->json(['error' => 'La sesión de recuperación ha expirado. Solicita un nuevo código.'], 401);
            return;
        }

        $nueva = $this->getInput('nueva_clave');
        $confirma = $this->getInput('confirmar_clave');

        $errors = $this->validatePasswordStrength($nueva);

        if (empty($errors) && $nueva !== $confirma) {
            $errors['confirmar_clave'] = 'Las contraseñas no coinciden';
        }

        if (!empty($errors)) {
            $this->json(['errors' => $errors], 400);
            return;
        }

        $userId = Session::get('reset_user_id');

        // Actualizar contraseña
        Database::query(
            "UPDATE usuarios 
             SET password_hash = :hash, 
                 primer_login = FALSE,
                 updated_at = NOW()
             WHERE id_usuario = :id",
            ['hash' => password_hash($nueva, PASSWORD_BCRYPT, ['cost' => 12]), 'id' => $userId]
        );

        // Limpiar sesión de recuperación
        Session::forget('reset_user_id');
        Session::forget('reset_verified');
        Session::forget('reset_expires');

        // Log de auditoría
        $this->logAudit('password_reset_completed', 'usuarios', $userId);

        $this->json(['success' => true, 'message' => 'Contraseña actualizada correctamente', 'redirect' => '/login']);
    }

    /**
     * Obtiene intentos de recuperación del día para un usuario
     */
    private function getDailyAttempts(int $userId): int
    {
        $result = Database::fetch(
            "SELECT COUNT(*) as total FROM password_resets 
             WHERE usuario_id = :user_id AND fecha_intento = CURDATE()",
            ['user_id' => $userId]
        );
        return (int)($result['total'] ?? 0);
    }

    /**
     * Valida fortaleza de contraseña
     */
    private function validatePasswordStrength(string $password): array
    {
        $errors = [];
        if (strlen($password) < 8) {
            $errors['nueva_clave'] = 'Mínimo 8 caracteres';
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $errors['nueva_clave'] = 'Debe contener al menos una mayúscula';
        } elseif (!preg_match('/[a-z]/', $password)) {
            $errors['nueva_clave'] = 'Debe contener al menos una minúscula';
        } elseif (!preg_match('/\d/', $password)) {
            $errors['nueva_clave'] = 'Debe contener al menos un número';
        } elseif (!preg_match('/[\W_]/', $password)) {
            $errors['nueva_clave'] = 'Debe contener al menos un carácter especial';
        }
        return $errors;
    }
}
