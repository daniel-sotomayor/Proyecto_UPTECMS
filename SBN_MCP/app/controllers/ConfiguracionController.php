<?php declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Helpers\AuditTrait;

class ConfiguracionController extends Controller
{
    use AuditTrait;

    /** Tipos de dato permitidos en configuración */
    private const ALLOWED_TYPES = ['string', 'integer', 'float', 'boolean'];

    public function index(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verificar CSRF antes de cualquier procesamiento
            if (!$this->verifyCSRFToken($this->getInput('csrf_token'))) {
                $this->json(['error' => 'Token de seguridad inválido'], 403);
                return;
            }
            $this->update();
            return;
        }

        $params = Database::fetchAll(
            'SELECT * FROM configuracion WHERE editable = TRUE ORDER BY id_configuracion'
        );

        $this->title = 'Configuración del Sistema';
        $this->renderWithLayout('configuracion/index', [
            'params'     => $params,
            'csrf_token' => $this->generateCSRFToken(),
        ]);
    }

    private function update(): void
    {
        $valores = $_POST['config'] ?? [];
        if (!is_array($valores)) {
            $this->json(['error' => 'Datos inválidos'], 400);
            return;
        }

        foreach ($valores as $clave => $valor) {
            // Validar que la clave sea alfanumérica (previene SQL injection en nombre de clave)
            if (!preg_match('/^[a-zA-Z0-9_]+$/', (string) $clave)) {
                continue;
            }

            $config = Database::fetch(
                'SELECT tipo_dato FROM configuracion WHERE clave = :c AND editable = TRUE',
                ['c' => (string) $clave]
            );
            if (!$config) continue;

            // Validar tipo de dato
            if (!in_array($config['tipo_dato'], self::ALLOWED_TYPES, true)) continue;

            $valor = trim((string) $valor);

            if ($config['tipo_dato'] === 'integer') {
                if (filter_var($valor, FILTER_VALIDATE_INT) === false) continue;
                $valor = (string)(int) $valor;
            } elseif ($config['tipo_dato'] === 'float') {
                if (filter_var($valor, FILTER_VALIDATE_FLOAT) === false) continue;
                $valor = (string)(float) $valor;
            } elseif ($config['tipo_dato'] === 'boolean') {
                $valor = in_array(strtolower($valor), ['1', 'true', 'on', 'yes'], true) ? '1' : '0';
            }

            Database::query(
                'UPDATE configuracion SET valor = :v WHERE clave = :c AND editable = TRUE',
                ['v' => $valor, 'c' => (string) $clave]
            );
        }

        $this->logAudit('UPDATE', 'configuracion', 0);
        $this->json(['success' => true, 'message' => 'Configuración guardada correctamente']);
    }
}
