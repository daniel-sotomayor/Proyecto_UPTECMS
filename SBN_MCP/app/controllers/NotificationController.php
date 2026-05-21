<?php
/**
 * Notification Controller - Notificaciones en tiempo real
 * Usa Server-Sent Events (SSE) para notificaciones push
 *
 * @package App\Controllers
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;

class NotificationController extends Controller
{
    /**
     * Endpoint SSE para notificaciones en tiempo real
     * Mantiene conexión abierta y envía notificaciones nuevas
     */
    public function stream(): void
    {
        // Verificar autenticación
        if (!Session::has('user_id')) {
            http_response_code(401);
            exit;
        }

        $userId = Session::get('user_id');

        // Configurar headers para SSE
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Desactivar buffering de nginx

        // Desactivar output buffering
        if (ob_get_level()) {
            ob_end_clean();
        }
        set_time_limit(0);

        $lastCheck = time();
        $lastId = 0;

        // Enviar evento de conexión establecida
        echo "event: connected\n";
        echo "data: " . json_encode(['status' => 'connected', 'time' => date('Y-m-d H:i:s')]) . "\n\n";
        flush();

        // Loop de eventos (cada 5 segundos)
        while (true) {
            // Verificar si hay nuevas notificaciones
            $notifications = $this->getPendingNotifications($userId, $lastId);
            
            foreach ($notifications as $notif) {
                echo "event: notification\n";
                echo "data: " . json_encode([
                    'id' => $notif['id_notificacion'],
                    'type' => $notif['tipo'],
                    'title' => $notif['titulo'],
                    'message' => $notif['mensaje'],
                    'link' => $notif['link'],
                    'time' => $notif['created_at'],
                    'icon' => $this->getIconForType($notif['tipo'])
                ]) . "\n\n";
                $lastId = $notif['id_notificacion'];
            }
            
            if (!empty($notifications)) {
                flush();
            }

            // Enviar heartbeat cada 30 segundos
            if (time() - $lastCheck > 30) {
                echo "event: heartbeat\n";
                echo "data: " . json_encode(['time' => time()]) . "\n\n";
                flush();
                $lastCheck = time();
            }

            // Verificar si el cliente sigue conectado
            if (connection_aborted()) {
                break;
            }

            // Esperar 5 segundos antes de siguiente chequeo
            sleep(5);
        }
    }

    /**
     * Obtiene notificaciones pendientes del usuario
     */
    private function getPendingNotifications(int $userId, int $lastId): array
    {
        return Database::fetchAll(
            "SELECT id_notificacion, tipo, titulo, mensaje, link, created_at 
            FROM notificaciones 
            WHERE usuario_id = :user_id 
            AND id_notificacion > :last_id
            AND leida = FALSE
            ORDER BY id_notificacion ASC
            LIMIT 10",
            ['user_id' => $userId, 'last_id' => $lastId]
        );
    }

    /**
     * Marcar notificación como leída
     */
    public function markAsRead(): void
    {
        if (!Session::has('user_id')) {
            $this->json(['error' => 'No autenticado'], 401);
            return;
        }

        $notifId = (int)$this->getInput('id');
        $userId = Session::get('user_id');

        Database::query(
            "UPDATE notificaciones SET leida = TRUE, read_at = NOW() 
            WHERE id_notificacion = :id AND usuario_id = :user_id",
            ['id' => $notifId, 'user_id' => $userId]
        );

        $this->json(['success' => true]);
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public function markAllAsRead(): void
    {
        if (!Session::has('user_id')) {
            $this->json(['error' => 'No autenticado'], 401);
            return;
        }

        $userId = Session::get('user_id');

        Database::query(
            "UPDATE notificaciones SET leida = TRUE, read_at = NOW() 
            WHERE usuario_id = :user_id AND leida = FALSE",
            ['user_id' => $userId]
        );

        $this->json(['success' => true, 'message' => 'Todas las notificaciones marcadas como leídas']);
    }

    /**
     * Obtiene el conteo de notificaciones no leídas
     */
    public function getUnreadCount(): void
    {
        if (!Session::has('user_id')) {
            $this->json(['error' => 'No autenticado'], 401);
            return;
        }

        $userId = Session::get('user_id');

        $result = Database::fetch(
            "SELECT COUNT(*) as count FROM notificaciones WHERE usuario_id = :user_id AND leida = FALSE",
            ['user_id' => $userId]
        );

        $this->json(['count' => (int)($result['count'] ?? 0)]);
    }

    /**
     * Obtiene lista de notificaciones recientes
     */
    public function getRecent(): void
    {
        if (!Session::has('user_id')) {
            $this->json(['error' => 'No autenticado'], 401);
            return;
        }

        $userId = Session::get('user_id');
        $limit = min((int)($this->getInput('limit') ?? 10), 50);

        $notifications = Database::fetchAll(
            "SELECT id_notificacion, tipo, titulo, mensaje, link, leida, created_at 
            FROM notificaciones 
            WHERE usuario_id = :user_id
            ORDER BY created_at DESC
            LIMIT :limit",
            ['user_id' => $userId, 'limit' => $limit]
        );

        $this->json(['notifications' => $notifications]);
    }

    /**
     * Crea una nueva notificación (para uso interno)
     *
     * @param int $userId ID del usuario destinatario
     * @param string $type Tipo: info, success, warning, error
     * @param string $title Título de la notificación
     * @param string $message Mensaje detallado
     * @param string|null $link URL opcional para navegar
     * @return int ID de la notificación creada
     */
    public static function create(int $userId, string $type, string $title, string $message, ?string $link = null): int
    {
        // Validar tipo
        $validTypes = ['info', 'success', 'warning', 'error'];
        if (!in_array($type, $validTypes, true)) {
            $type = 'info';
        }

        Database::query(
            "INSERT INTO notificaciones (usuario_id, tipo, titulo, mensaje, link, created_at) 
            VALUES (:user_id, :tipo, :titulo, :mensaje, :link, NOW())",
            [
                'user_id' => $userId,
                'tipo' => $type,
                'titulo' => $title,
                'mensaje' => $message,
                'link' => $link
            ]
        );

        return (int)Database::lastInsertId();
    }

    /**
     * Notifica a todos los usuarios de un rol específico
     */
    public static function notifyRole(string $role, string $type, string $title, string $message, ?string $link = null): void
    {
        $users = Database::fetchAll(
            "SELECT id_usuario FROM usuarios WHERE rol = :rol AND activo = TRUE",
            ['rol' => $role]
        );

        foreach ($users as $user) {
            self::create($user['id_usuario'], $type, $title, $message, $link);
        }
    }

    /**
     * Notifica a todos los administradores
     */
    public static function notifyAdmins(string $type, string $title, string $message, ?string $link = null): void
    {
        self::notifyRole('administrador', $type, $title, $message, $link);
    }

    /**
     * Retorna el icono según el tipo de notificación
     */
    private function getIconForType(string $type): string
    {
        return match ($type) {
            'success' => 'check-circle',
            'warning' => 'alert-triangle',
            'error' => 'x-circle',
            default => 'info',
        };
    }
}
