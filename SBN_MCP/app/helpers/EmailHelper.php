<?php
/**
 * Email Helper - Sistema de envío de correos
 * Usa PHPMailer para envío SMTP
 *
 * @package App\Helpers
 */

namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailHelper
{
    private static ?PHPMailer $mailer = null;

    /**
     * Obtiene instancia configurada de PHPMailer
     */
    private static function getMailer(): PHPMailer
    {
        if (self::$mailer === null) {
            self::$mailer = new PHPMailer(true);

            // Configuración SMTP desde .env
            self::$mailer->isSMTP();
            self::$mailer->Host = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
            self::$mailer->SMTPAuth = true;
            self::$mailer->Username = $_ENV['MAIL_USERNAME'] ?? '';
            self::$mailer->Password = $_ENV['MAIL_PASSWORD'] ?? '';
            self::$mailer->SMTPSecure = $_ENV['MAIL_ENCRYPTION'] ?? PHPMailer::ENCRYPTION_STARTTLS;
            self::$mailer->Port = (int)($_ENV['MAIL_PORT'] ?? 587);

            // Desactivar debug en producción
            self::$mailer->SMTPDebug = ($_ENV['APP_DEBUG'] ?? false) ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF;

            // Codificación UTF-8
            self::$mailer->CharSet = 'UTF-8';

            // From
            self::$mailer->setFrom(
                $_ENV['MAIL_FROM'] ?? 'noreply@mcp.gob.ve',
                $_ENV['MAIL_FROM_NAME'] ?? 'Sistema Bienes Nacionales MCP'
            );
        }

        return self::$mailer;
    }

    /**
     * Envía correo de recuperación de contraseña
     *
     * @param string $to Email del destinatario
     * @param string $nombre Nombre del usuario
     * @param string $codigo Código de verificación (6 dígitos)
     * @param int $minutos Minutos de validez (default 30)
     * @return bool Éxito del envío
     */
    public static function sendPasswordReset(string $to, string $nombre, string $codigo, int $minutos = 30): bool
    {
        try {
            $mail = self::getMailer();
            $mail->clearAddresses();
            $mail->addAddress($to, $nombre);

            $mail->isHTML(true);
            $mail->Subject = 'Código de recuperación de contraseña - SBN MCP';

            $html = self::getPasswordResetTemplate($nombre, $codigo, $minutos);
            $mail->Body = $html;
            $mail->AltBody = "Hola {$nombre},\n\nTu código de recuperación es: {$codigo}\n\nEste código expira en {$minutos} minutos.\n\nSi no solicitaste este código, ignora este mensaje.";

            return $mail->send();
        } catch (Exception $e) {
            error_log("Error enviando correo de recuperación: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Envía notificación genérica
     *
     * @param string $to Email destinatario
     * @param string $subject Asunto
     * @param string $bodyHTML Cuerpo HTML
     * @param string|null $bodyText Cuerpo texto plano (opcional)
     * @return bool
     */
    public static function sendNotification(string $to, string $subject, string $bodyHTML, ?string $bodyText = null): bool
    {
        try {
            $mail = self::getMailer();
            $mail->clearAddresses();
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $bodyHTML;
            $mail->AltBody = $bodyText ?? strip_tags($bodyHTML);

            return $mail->send();
        } catch (Exception $e) {
            error_log("Error enviando notificación: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Template HTML para correo de recuperación
     */
    private static function getPasswordResetTemplate(string $nombre, string $codigo, int $minutos): string
    {
        $contacto = $_ENV['MAIL_CONTACTO'] ?? 'bienes@mcp.gob.ve';
        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperación de Contraseña</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 30px; text-align: center; color: white; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 40px 30px; }
        .greeting { font-size: 18px; color: #334155; margin-bottom: 20px; }
        .code-box { background: #f1f5f9; border: 2px dashed #3b82f6; border-radius: 8px; padding: 30px; text-align: center; margin: 30px 0; }
        .code { font-size: 36px; font-weight: bold; color: #0f172a; letter-spacing: 8px; font-family: 'Courier New', monospace; }
        .expiry { color: #ef4444; font-size: 14px; margin-top: 15px; }
        .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin-top: 30px; border-radius: 4px; font-size: 14px; color: #92400e; }
        .footer { background: #f8fafc; padding: 20px 30px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Sistema Bienes Nacionales MCP</h1>
            <p>Recuperación de Contraseña</p>
        </div>
        <div class="content">
            <p class="greeting">Hola <strong>{$nombre}</strong>,</p>
            <p>Has solicitado recuperar tu contraseña. Utiliza el siguiente código de verificación:</p>

            <div class="code-box">
                <div class="code">{$codigo}</div>
                <p class="expiry">Este código expira en {$minutos} minutos</p>
            </div>

            <p>Ingresa este código en el sistema para continuar con el proceso de cambio de contraseña.</p>

            <div class="warning">
                <strong>Importante:</strong><br>
                • No compartas este código con nadie.<br>
                • Solo tienes 5 intentos de recuperación por día.<br>
                • Si no solicitaste este código, ignora este mensaje o contacta al administrador.
            </div>
        </div>
        <div class="footer">
            <p>Este es un mensaje automático del Sistema de Gestión de Bienes Nacionales</p>
            <p>Maternidad Concepción Palacios - Venezuela</p>
            <p>{$contacto}</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Genera código de verificación aleatorio
     *
     * @param int $length Longitud del código (default 6)
     * @return string Código numérico
     */
    public static function generateVerificationCode(int $length = 6): string
    {
        $min = (int) str_repeat('1', $length);
        $max = (int) str_repeat('9', $length);
        return (string) random_int($min, $max);
    }
}
