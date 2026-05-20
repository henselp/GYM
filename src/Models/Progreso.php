<?php

declare(strict_types=1);

namespace Gymfit\Models;

class Progreso
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly int $clienteId = 0,
        public readonly ?float $peso = null,
        public readonly ?float $altura = null,
        public readonly ?float $brazo = null,
        public readonly ?float $cintura = null,
        public readonly ?float $pierna = null,
        public readonly ?string $notas = null,
        public readonly ?string $registradoEn = null,
    ) {}

    public static function fromRow(array $row): self
    {
        return new self(
            id: (int)($row['id'] ?? 0),
            clienteId: (int)($row['cliente_id'] ?? 0),
            peso: isset($row['peso']) ? (float)$row['peso'] : null,
            altura: isset($row['altura']) ? (float)$row['altura'] : null,
            brazo: isset($row['brazo']) ? (float)$row['brazo'] : null,
            cintura: isset($row['cintura']) ? (float)$row['cintura'] : null,
            pierna: isset($row['pierna']) ? (float)$row['pierna'] : null,
            notas: $row['notas'] ?? null,
            registradoEn: $row['registrado_en'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'cliente_id' => $this->clienteId,
            'peso' => $this->peso,
            'altura' => $this->altura,
            'brazo' => $this->brazo,
            'cintura' => $this->cintura,
            'pierna' => $this->pierna,
            'notas' => $this->notas,
            'registrado_en' => $this->registradoEn,
        ];
    }
}
