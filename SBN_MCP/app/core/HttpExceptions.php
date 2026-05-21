<?php declare(strict_types=1);
/**
 * Excepciones HTTP para control de flujo sin exit/die
 * Sistema de Gestión de Bienes Nacionales - MCP
 */

namespace App\Core;

/** Lanzada cuando se emite una redirección HTTP. */
class HttpRedirectException extends \RuntimeException {}

/** Lanzada cuando se termina una respuesta HTTP (404, 403, JSON, etc.). */
class HttpResponseException extends \RuntimeException
{
    public function __construct(int $statusCode = 200)
    {
        parent::__construct('HTTP Response: ' . $statusCode, $statusCode);
    }
}
