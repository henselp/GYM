<?php

declare(strict_types=1);

namespace Gymfit\Exceptions;

class DuplicateException extends \RuntimeException
{
    public function __construct(string $message = 'El recurso ya existe', int $code = 409)
    {
        parent::__construct($message, $code);
    }
}
