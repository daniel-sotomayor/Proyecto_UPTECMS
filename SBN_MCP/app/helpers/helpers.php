<?php
/**
 * Funciones Helper Globales
 * Sistema de Gestión de Bienes Nacionales - MCP
 */

/** Escapa salida HTML para prevenir XSS */
function esc(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/** Formatea fecha a d/m/Y (u otro formato) */
function formatDate(?string $date, string $format = 'd/m/Y'): string
{
    if (empty($date)) return '—';
    $ts = strtotime($date);
    if ($ts === false) return '—';
    return date($format, $ts);
}

/** Formatea valor monetario en Bolívares */
function formatCurrency(float $value): string
{
    return 'Bs. ' . number_format($value, 2, ',', '.');
}

/** Calcula valor actual del bien por depreciación lineal */
function calcularDepreciacion(float $valorInicial, float $valorResidual, int $vidaUtil, ?string $fechaAdquisicion): float
{
    if ($vidaUtil <= 0) return $valorInicial;
    $fecha = $fechaAdquisicion ? strtotime($fechaAdquisicion) : time();
    $anos  = max(0, min($vidaUtil, (time() - $fecha) / (365.25 * 86400)));
    return max($valorResidual, $valorInicial - (($valorInicial - $valorResidual) / $vidaUtil) * $anos);
}
