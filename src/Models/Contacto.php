<?php

declare(strict_types=1);

namespace Gymfit\Models;

class Contacto
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly string $nombre = '',
        public readonly string $email = '',
        public readonly string $mensaje = '',
        public readonly ?string $enviadoEn = null,
    ) {}

    public static function fromRow(array $row): self
    {
        return new self(
            id: (int)($row['id'] ?? 0),
            nombre: $row['nombre'] ?? '',
            email: $row['email'] ?? '',
            mensaje: $row['mensaje'] ?? '',
            enviadoEn: $row['enviado_en'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'email' => $this->email,
            'mensaje' => $this->mensaje,
            'enviado_en' => $this->enviadoEn,
        ];
    }
}
