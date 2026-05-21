<?php declare(strict_types=1);

namespace App\Helpers;

use App\Core\Database;
use App\Core\Session;
use App\Core\Logger;

trait AuditTrait
{
    /**
     * Registra una operación en el log de auditoría.
     * Todos los valores se pasan como parámetros PDO — sin interpolación.
     */
    protected function logAudit(
        string $action,
        string $table,
        int $id,
        ?array $oldData = null,
        ?array $newData = null
    ): void {
        try {
            $userId = (int) Session::get('user_id');
            if ($userId <= 0) return;

            // Verificar que el usuario existe usando parámetro PDO
            $userExists = Database::fetchValue(
                'SELECT id_usuario FROM usuarios WHERE id_usuario = :id LIMIT 1',
                ['id' => $userId]
            );
            if (!$userExists) return;

            Database::query(
                'INSERT INTO auditoria
                    (tabla_afectada, registro_id, accion, usuario_id, ip_address, user_agent, datos_anteriores, datos_nuevos)
                 VALUES
                    (:t, :r, :a, :u, :ip, :ua, :old, :new)',
                [
                    't'   => $table,
                    'r'   => $id,
                    'a'   => $action,
                    'u'   => $userId,
                    'ip'  => $_SERVER['REMOTE_ADDR'] ?? '',
                    'ua'  => $_SERVER['HTTP_USER_AGENT'] ?? '',
                    'old' => $oldData !== null ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null,
                    'new' => $newData !== null ? json_encode($newData, JSON_UNESCAPED_UNICODE) : null,
                ]
            );
        } catch (\Throwable $e) {
            Logger::error('Error en logAudit', ['error' => $e->getMessage()]);
        }
    }
}
