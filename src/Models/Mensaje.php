<?php

declare(strict_types=1);

namespace Gymfit\Models;

class Mensaje
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly int $deUsuarioId = 0,
        public readonly int $paraUsuarioId = 0,
        public readonly string $contenido = '',
        public readonly bool $leido = false,
        public readonly ?string $enviadoEn = null,
        public readonly ?string $deNombre = null,
    ) {}

    public static function fromRow(array $row): self
    {
        return new self(
            id: (int)($row['id'] ?? 0),
            deUsuarioId: (int)($row['de_usuario_id'] ?? 0),
            paraUsuarioId: (int)($row['para_usuario_id'] ?? 0),
            contenido: $row['contenido'] ?? '',
            leido: (bool)($row['leido'] ?? false),
            enviadoEn: $row['enviado_en'] ?? null,
            deNombre: $row['de_nombre'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'de_usuario_id' => $this->deUsuarioId,
            'para_usuario_id' => $this->paraUsuarioId,
            'contenido' => $this->contenido,
            'leido' => $this->leido,
            'enviado_en' => $this->enviadoEn,
            'de_nombre' => $this->deNombre,
        ];
    }
}
