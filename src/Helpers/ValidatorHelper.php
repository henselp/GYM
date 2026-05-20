<?php

declare(strict_types=1);

namespace Gymfit\Helpers;

use Gymfit\Exceptions\ValidationException;

class ValidatorHelper
{
    private array $errors = [];
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function validate(array $data): self
    {
        return new self($data);
    }

    public function required(string ...$fields): self
    {
        foreach ($fields as $field) {
            $value = $this->data[$field] ?? '';
            if (is_string($value) && trim($value) === '') {
                $this->errors[$field][] = "El campo {$field} es obligatorio";
            }
        }
        return $this;
    }

    public function email(string $field): self
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = "El campo {$field} no es un email válido";
        }
        return $this;
    }

    public function minLength(string $field, int $min): self
    {
        $value = $this->data[$field] ?? '';
        if (is_string($value) && mb_strlen(trim($value)) < $min) {
            $this->errors[$field][] = "El campo {$field} debe tener al menos {$min} caracteres";
        }
        return $this;
    }

    public function maxLength(string $field, int $max): self
    {
        $value = $this->data[$field] ?? '';
        if (is_string($value) && mb_strlen(trim($value)) > $max) {
            $this->errors[$field][] = "El campo {$field} no debe exceder {$max} caracteres";
        }
        return $this;
    }

    public function inArray(string $field, array $allowed): self
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && !in_array($value, $allowed, true)) {
            $this->errors[$field][] = "El campo {$field} contiene un valor no permitido";
        }
        return $this;
    }

    public function passwordStrength(string $field): self
    {
        $value = $this->data[$field] ?? '';
        if ($value === '') {
            return $this;
        }
        if (mb_strlen($value) < 8) {
            $this->errors[$field][] = 'La contraseña debe tener al menos 8 caracteres';
        }
        if (!preg_match('/[A-Z]/', $value)) {
            $this->errors[$field][] = 'La contraseña debe contener al menos una mayúscula';
        }
        if (!preg_match('/[0-9]/', $value)) {
            $this->errors[$field][] = 'La contraseña debe contener al menos un número';
        }
        if (!preg_match('/[^A-Za-z0-9]/', $value)) {
            $this->errors[$field][] = 'La contraseña debe contener al menos un carácter especial';
        }
        return $this;
    }

    public function integer(string $field): self
    {
        $value = $this->data[$field] ?? null;
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->errors[$field][] = "El campo {$field} debe ser un número entero";
        }
        return $this;
    }

    public function positive(string $field): self
    {
        $value = $this->data[$field] ?? null;
        if ($value !== null && $value !== '' && (int)$value <= 0) {
            $this->errors[$field][] = "El campo {$field} debe ser un número positivo";
        }
        return $this;
    }

    public function sanitize(string $field): string
    {
        $value = $this->data[$field] ?? '';
        if (is_string($value)) {
            $value = trim($value);
            $value = strip_tags($value);
        }
        return $value;
    }

    public function get(string $field, mixed $default = null): mixed
    {
        return $this->data[$field] ?? $default;
    }

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }

    public function throwIf(): void
    {
        if ($this->errors !== []) {
            throw new ValidationException('Datos inválidos', 400, $this->errors);
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
