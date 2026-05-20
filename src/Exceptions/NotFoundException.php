<?php

declare(strict_types=1);

namespace Gymfit\Exceptions;

class NotFoundException extends \RuntimeException
{
    public function __construct(string $message = 'Recurso no encontrado', int $code = 404)
    {
        parent::__construct($message, $code);
    }
}
