<?php

declare(strict_types=1);

namespace Gymfit\Exceptions;

class ForbiddenException extends \RuntimeException
{
    public function __construct(string $message = 'Acceso denegado', int $code = 403)
    {
        parent::__construct($message, $code);
    }
}
