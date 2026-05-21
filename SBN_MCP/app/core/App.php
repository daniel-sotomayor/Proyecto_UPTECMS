<?php
/**
 * Clase Principal de la Aplicación — Registro de Rutas
 * Sistema de Gestión de Bienes Nacionales - MCP
 *
 * Roles operativos:
 *   administrador          — Acceso total + gestión de usuarios
 *   gerencia_bn            — Registra bienes, asigna Nro. de Bien, genera actas
 *   controlador_inventario — Corrige y controla el inventario global
 *   registrador            — Solo carga datos, sin edición
 */

namespace App\Core;

use App\Core\Router;
use App\Core\Database;
use App\Core\Session;

class App
{
    protected Router $router;

    public function __construct()
    {
        $this->initDatabase();
        $this->initSession();
        $this->router = new Router();
    }

    protected function initDatabase(): void
    {
        $config = require CONFIG_PATH . '/database.php';
        Database::connect($config);
    }

    protected function initSession(): void
    {
        Session::start();
    }

    public function run(): void
    {
        $this->registerRoutes();
        $this->router->dispatch();
    }

    protected function registerRoutes(): void
    {
        // ── Públicas ──────────────────────────────────────────────────────────
        $this->router->get('/',          ['App\Controllers\PublicController', 'index']);
        $this->router->get('/nosotros',  ['App\Controllers\PublicController', 'nosotros']);
        $this->router->get('/servicios', ['App\Controllers\PublicController', 'servicios']);
        $this->router->get('/contacto',  ['App\Controllers\PublicController', 'contacto']);

        // ── Autenticación ─────────────────────────────────────────────────────
        $this->router->get('/login',         ['App\Controllers\AuthController', 'showLogin']);
        $this->router->post('/login',        ['App\Controllers\AuthController', 'login']);
        $this->router->post('/logout',       ['App\Controllers\AuthController', 'logout']);
        $this->router->get('/cambiar-clave', ['App\Controllers\AuthController', 'showCambiarClave'], ['auth']);
        $this->router->post('/cambiar-clave',['App\Controllers\AuthController', 'cambiarClave'],     ['auth']);

        // ── Recuperación de Contraseña (público) ──────────────────────────────
        $this->router->get('/recuperar-clave',          ['App\Controllers\PasswordResetController', 'showForgotPassword']);
        $this->router->post('/recuperar-clave/enviar',  ['App\Controllers\PasswordResetController', 'sendResetCode']);
        $this->router->get('/verificar-codigo',         ['App\Controllers\PasswordResetController', 'showVerifyCode']);
        $this->router->post('/verificar-codigo/verificar', ['App\Controllers\PasswordResetController', 'verifyCode']);
        $this->router->get('/restablecer-clave',        ['App\Controllers\PasswordResetController', 'showResetPassword']);
        $this->router->post('/restablecer-clave/guardar', ['App\Controllers\PasswordResetController', 'resetPassword']);

        // ── Dashboard ─────────────────────────────────────────────────────────
        // Todos los roles autenticados acceden al dashboard
        $this->router->get('/dashboard', ['App\Controllers\DashboardController', 'index'],
            ['auth', 'role:administrador,gerencia_bn,controlador_inventario,registrador']);

        // ── Bienes — Ver (todos los roles) ────────────────────────────────────
        $this->router->get('/bienes',      ['App\Controllers\BienController', 'index'],
            ['auth', 'role:administrador,gerencia_bn,controlador_inventario,registrador']);
        $this->router->get('/bienes/:id',  ['App\Controllers\BienController', 'show'],
            ['auth', 'role:administrador,gerencia_bn,controlador_inventario,registrador']);
        // Verificación semestral: solo administradores, gerencia o validadores de inventario
        $this->router->post('/bienes/:id/verificar-semestral', ['App\Controllers\BienController', 'verificarSemestral'],
            ['auth', 'role:administrador,gerencia_bn,validador_inventario']);
        // Vista de verificaciones por bien
        $this->router->get('/bienes/:id/verificaciones', ['App\Controllers\BienController', 'verificaciones'],
            ['auth', 'role:administrador,gerencia_bn,controlador_inventario,validador_inventario']);
        
        // ── Bienes — Crear (gerencia_bn y registrador cargan, admin también) ──
        $this->router->get('/bienes/nuevo',  ['App\Controllers\BienController', 'create'],
            ['auth', 'role:administrador,gerencia_bn,registrador']);
        $this->router->post('/bienes',       ['App\Controllers\BienController', 'store'],
            ['auth', 'role:administrador,gerencia_bn,registrador']);
        
        // Validaciones AJAX
        $this->router->get('/bienes/validar-numero', ['App\Controllers\BienController', 'validarNumero'],
            ['auth', 'role:administrador,gerencia_bn,registrador']);
        $this->router->get('/bienes/validar-serial', ['App\Controllers\BienController', 'validarSerial'],
            ['auth', 'role:administrador,gerencia_bn,registrador']);
        $this->router->get('/bienes/validar-codigo-ministerio', ['App\Controllers\BienController', 'validarCodigoMinisterio'],
            ['auth', 'role:administrador,gerencia_bn,registrador']);

        // ── Bienes — Editar (solo gerencia_bn y controlador_inventario) ───────
        // gerencia_bn: puede editar Nro. de Bien y datos del registro
        // controlador_inventario: puede corregir cualquier campo del inventario
        $this->router->get('/bienes/:id/editar', ['App\Controllers\BienController', 'edit'],
            ['auth', 'role:administrador,gerencia_bn,controlador_inventario']);
        $this->router->put('/bienes/:id',        ['App\Controllers\BienController', 'update'],
            ['auth', 'role:administrador,gerencia_bn,controlador_inventario']);

        // ── Bienes — Desincorporar (solo gerencia_bn) ─────────────────────────
        $this->router->delete('/bienes/:id', ['App\Controllers\BienController', 'destroy'],
            ['auth', 'role:administrador,gerencia_bn']);

        // ── Movimientos y Actas ───────────────────────────────────────────────
        // Ver movimientos: todos
        $this->router->get('/movimientos',     ['App\Controllers\MovimientoController', 'index'],
            ['auth', 'role:administrador,gerencia_bn,controlador_inventario,registrador']);
        $this->router->get('/movimientos/:id', ['App\Controllers\MovimientoController', 'show'],
            ['auth', 'role:administrador,gerencia_bn,controlador_inventario,registrador']);

        // Crear actas (incorporación, traslado, desincorporación): solo gerencia_bn
        $this->router->get('/movimientos/nuevo',       ['App\Controllers\MovimientoController', 'create'],
            ['auth', 'role:administrador,gerencia_bn']);
        $this->router->post('/movimientos',            ['App\Controllers\MovimientoController', 'store'],
            ['auth', 'role:administrador,gerencia_bn']);
        $this->router->post('/movimientos/:id/aprobar',['App\Controllers\MovimientoController', 'approve'],
            ['auth', 'role:administrador,gerencia_bn']);
        $this->router->post('/movimientos/:id/rechazar',['App\Controllers\MovimientoController', 'reject'],
            ['auth', 'role:administrador,gerencia_bn']);

        // ── Reportes ──────────────────────────────────────────────────────────
        // Ver reportes: gerencia_bn y controlador_inventario
        $this->router->get('/reportes',      ['App\Controllers\ReporteController', 'index'],
            ['auth', 'role:administrador,gerencia_bn,controlador_inventario']);
        $this->router->get('/reportes/bm1',  ['App\Controllers\ReporteController', 'bm1'],
            ['auth', 'role:administrador,gerencia_bn,controlador_inventario']);
        $this->router->get('/reportes/bm2',  ['App\Controllers\ReporteController', 'bm2'],
            ['auth', 'role:administrador,gerencia_bn,controlador_inventario']);
        $this->router->get('/reportes/bm3',  ['App\Controllers\ReporteController', 'bm3'],
            ['auth', 'role:administrador,gerencia_bn,controlador_inventario']);
        $this->router->get('/reportes/bm4',  ['App\Controllers\ReporteController', 'bm4'],
            ['auth', 'role:administrador,gerencia_bn,controlador_inventario']);

        // ── Control Mural (Inventario por Departamento) ───────────────────────
        $this->router->get('/inventario-mural',       ['App\Controllers\InventarioMuralController', 'index'],
            ['auth', 'role:administrador,gerencia_bn,controlador_inventario,registrador']);
        $this->router->get('/inventario-mural/:id',   ['App\Controllers\InventarioMuralController', 'area'],
            ['auth', 'role:administrador,gerencia_bn,controlador_inventario,registrador']);

        // ── Usuarios (solo administrador) ─────────────────────────────────────
        $this->router->get('/usuarios',                    ['App\Controllers\AdminController', 'usuarios'],       ['auth', 'role:administrador']);
        $this->router->get('/usuarios/nuevo',              ['App\Controllers\AdminController', 'createUser'],     ['auth', 'role:administrador']);
        $this->router->post('/usuarios',                   ['App\Controllers\AdminController', 'storeUser'],      ['auth', 'role:administrador']);
        $this->router->get('/usuarios/preview-username',   ['App\Controllers\AdminController', 'previewUsername'],['auth', 'role:administrador']);
        $this->router->get('/usuarios/:id/editar',         ['App\Controllers\AdminController', 'editUser'],       ['auth', 'role:administrador']);
        $this->router->put('/usuarios/:id',                ['App\Controllers\AdminController', 'updateUser'],     ['auth', 'role:administrador']);
        $this->router->delete('/usuarios/:id',             ['App\Controllers\AdminController', 'deleteUser'],     ['auth', 'role:administrador']);
        // Asignar rol validador_inventario rápidamente
        $this->router->post('/usuarios/:id/asignar-validador', ['App\Controllers\AdminController', 'assignValidador'], ['auth', 'role:administrador']);

        // ── Mantenimientos ────────────────────────────────────────────────────
        $this->router->get('/mantenimientos',              ['App\Controllers\MantenimientoController', 'index'],
            ['auth', 'role:administrador,gerencia_bn,controlador_inventario']);
        $this->router->get('/mantenimientos/nuevo',        ['App\Controllers\MantenimientoController', 'create'],
            ['auth', 'role:administrador,gerencia_bn']);
        $this->router->post('/mantenimientos',             ['App\Controllers\MantenimientoController', 'store'],
            ['auth', 'role:administrador,gerencia_bn']);
        $this->router->get('/mantenimientos/:id',          ['App\Controllers\MantenimientoController', 'show'],
            ['auth', 'role:administrador,gerencia_bn,controlador_inventario']);
        $this->router->get('/mantenimientos/:id/editar',   ['App\Controllers\MantenimientoController', 'edit'],
            ['auth', 'role:administrador,gerencia_bn']);
        $this->router->put('/mantenimientos/:id',          ['App\Controllers\MantenimientoController', 'update'],
            ['auth', 'role:administrador,gerencia_bn']);

        // ── Configuración (solo administrador) ────────────────────────────────
        $this->router->get('/configuracion',  ['App\Controllers\ConfiguracionController', 'index'],
            ['auth', 'role:administrador']);
        $this->router->post('/configuracion', ['App\Controllers\ConfiguracionController', 'index'],
            ['auth', 'role:administrador']);

        // ── Auditoría ─────────────────────────────────────────────────────────
        $this->router->get('/auditoria', ['App\Controllers\AuditoriaController', 'index'],
            ['auth', 'role:administrador,gerencia_bn,controlador_inventario']);

        // ── Notificaciones en Tiempo Real ────────────────────────────────────
        $this->router->get('/notificaciones/stream', ['App\Controllers\NotificationController', 'stream'], ['auth']);
        $this->router->get('/notificaciones/count', ['App\Controllers\NotificationController', 'getUnreadCount'], ['auth']);
        $this->router->get('/notificaciones/recent', ['App\Controllers\NotificationController', 'getRecent'], ['auth']);
        $this->router->post('/notificaciones/read', ['App\Controllers\NotificationController', 'markAsRead'], ['auth']);
        $this->router->post('/notificaciones/read-all', ['App\Controllers\NotificationController', 'markAllAsRead'], ['auth']);

        // ── Backup (solo administrador) ───────────────────────────────────────
        $this->router->get('/backup/create', ['App\Helpers\BackupHelper', 'createBackup'],
            ['auth', 'role:administrador']);
        $this->router->get('/backup/list', ['App\Helpers\BackupHelper', 'listBackups'],
            ['auth', 'role:administrador']);
    }
}
