<?php

declare(strict_types=1);

namespace Gymfit\Models;

class Usuario
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly string $nombre = '',
        public readonly string $email = '',
        public readonly string $rol = '',
        public readonly ?string $avatarUrl = null,
        public readonly ?int $edad = null,
        public readonly ?string $objetivo = null,
        public readonly ?string $nivel = null,
        public readonly ?string $creadoEn = null,
    ) {}

    public static function fromRow(array $row): self
    {
        return new self(
            id: (int)($row['id'] ?? 0),
            nombre: $row['nombre'] ?? '',
            email: $row['email'] ?? '',
            rol: $row['rol'] ?? '',
            avatarUrl: $row['avatar_url'] ?? null,
            edad: isset($row['edad']) ? (int)$row['edad'] : null,
            objetivo: $row['objetivo'] ?? null,
            nivel: $row['nivel'] ?? null,
            creadoEn: $row['creado_en'] ?? null,
        );
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'email' => $this->email,
            'rol' => $this->rol,
            'avatar_url' => $this->avatarUrl,
            'edad' => $this->edad,
            'objetivo' => $this->objetivo,
            'nivel' => $this->nivel,
            'creado_en' => $this->creadoEn,
        ];
    }

    public function isTrainer(): bool
    {
        return $this->rol === 'entrenador';
    }

    public function isClient(): bool
    {
        return $this->rol === 'cliente';
    }
}
