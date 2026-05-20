<?php

declare(strict_types=1);

namespace Gymfit\Exceptions;

class AuthException extends \RuntimeException
{
    public function __construct(string $message = 'No autenticado', int $code = 401)
    {
        parent::__construct($message, $code);
    }
}
