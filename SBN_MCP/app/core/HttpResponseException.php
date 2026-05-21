<?php declare(strict_types=1);
namespace App\Core;

class HttpResponseException extends \RuntimeException
{
    public function __construct(int $statusCode = 200)
    {
        parent::__construct('HTTP Response: ' . $statusCode, $statusCode);
    }
}
