<?php

declare(strict_types=1);

namespace Gymfit\Exceptions;

class ValidationException extends \RuntimeException
{
    private array $errors;

    public function __construct(string $message = 'Datos inválidos', int $code = 400, array $errors = [])
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
