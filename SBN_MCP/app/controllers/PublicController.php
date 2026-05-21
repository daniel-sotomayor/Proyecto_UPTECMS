<?php
/**
 * Controlador Público
 * Sistema de Gestión de Bienes Nacionales
 * Capa Pública - Landing Page
 */

namespace App\Controllers;

use App\Core\Controller;

class PublicController extends Controller
{
    /**
     * Página de inicio - Landing Page
     */
    public function index(): void
    {
        $this->title = 'Maternidad Concepción Palacios - Sistema de Gestión de Bienes Nacionales';
        
        $this->render('public/index');
    }

    /**
     * Página nosotros
     */
    public function nosotros(): void
    {
        $this->title = 'Nosotros - Maternidad Concepción Palacios';
        
        $this->render('public/nosotros');
    }

    /**
     * Página de servicios
     */
    public function servicios(): void
    {
        $this->title = 'Servicios - Sistema de Bienes Nacionales';
        
        $this->render('public/servicios');
    }

    /**
     * Página de contacto
     */
    public function contacto(): void
    {
        $this->title = 'Contacto - Maternidad Concepción Palacios';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verificar CSRF antes de cualquier procesamiento
            if (!$this->verifyCSRFToken($this->getInput('csrf_token'))) {
                $this->json(['error' => 'Token de seguridad inválido'], 403);
                return;
            }
            $this->handleContactForm();
            return;
        }

        $this->render('public/contacto', ['csrf_token' => $this->generateCSRFToken()]);
    }

    /**
     * Procesar formulario de contacto
     */
    protected function handleContactForm(): void
    {
        // CSRF ya verificado en contacto()
        $nombre  = trim($this->getInput('nombre', ''));
        $email   = trim($this->getInput('email', ''));
        $mensaje = trim($this->getInput('mensaje', ''));

        // Sanitizar para prevenir header injection
        $nombre  = str_replace(["\r", "\n", '%0a', '%0d'], '', $nombre);
        $email   = str_replace(["\r", "\n", '%0a', '%0d'], '', $email);

        $errors = [];

        if (empty($nombre)) {
            $errors['nombre'] = 'El nombre es requerido';
        } elseif (mb_strlen($nombre) > 100) {
            $errors['nombre'] = 'El nombre no puede exceder 100 caracteres';
        }

        if (empty($email)) {
            $errors['email'] = 'El correo es requerido';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Correo electrónico inválido';
        }

        if (empty($mensaje)) {
            $errors['mensaje'] = 'El mensaje es requerido';
        } elseif (mb_strlen($mensaje) > 2000) {
            $errors['mensaje'] = 'El mensaje no puede exceder 2000 caracteres';
        }

        if (!empty($errors)) {
            $this->json(['errors' => $errors], 400);
            return;
        }

        $destino = getenv('MAIL_CONTACTO') ?: 'bienes@mcp.gob.ve';
        // Validar destino
        if (!filter_var($destino, FILTER_VALIDATE_EMAIL)) {
            $this->json(['error' => 'Configuración de correo inválida'], 500);
            return;
        }

        $asunto  = '[MCP-BN] Mensaje de contacto';
        $cuerpo  = "Nombre: " . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') .
                   "\nEmail: " . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') .
                   "\n\nMensaje:\n" . htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8');
        $headers = "From: no-reply@mcp.gob.ve\r\nContent-Type: text/plain; charset=UTF-8";

        @mail($destino, $asunto, $cuerpo, $headers);

        $this->json(['success' => true, 'message' => 'Mensaje enviado correctamente']);
    }
}
