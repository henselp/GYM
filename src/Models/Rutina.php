<?php

declare(strict_types=1);

namespace Gymfit\Models;

class Rutina
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly int $clienteId = 0,
        public readonly int $entrenadorId = 0,
        public readonly string $contenido = '',
        public readonly ?string $observaciones = null,
        public readonly ?string $asignadaEn = null,
        public readonly ?string $actualizadaEn = null,
        public readonly ?string $entrenadorNombre = null,
    ) {}

    public static function fromRow(array $row): self
    {
        return new self(
            id: (int)($row['id'] ?? 0),
            clienteId: (int)($row['cliente_id'] ?? 0),
            entrenadorId: (int)($row['entrenador_id'] ?? 0),
            contenido: $row['contenido'] ?? '',
            observaciones: $row['observaciones'] ?? null,
            asignadaEn: $row['asignada_en'] ?? null,
            actualizadaEn: $row['actualizada_en'] ?? null,
            entrenadorNombre: $row['entrenador_nombre'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'cliente_id' => $this->clienteId,
            'entrenador_id' => $this->entrenadorId,
            'contenido' => $this->contenido,
            'observaciones' => $this->observaciones,
            'asignada_en' => $this->asignadaEn,
            'actualizada_en' => $this->actualizadaEn,
            'entrenador_nombre' => $this->entrenadorNombre,
        ];
    }
}
