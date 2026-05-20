<?php

declare(strict_types=1);

namespace Gymfit\Repositories;

use Gymfit\Database;
use Gymfit\Models\Progreso;

class ProgresoRepository
{
    public function getByCliente(int $clienteId): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT * FROM progreso WHERE cliente_id = :c ORDER BY registrado_en ASC'
        );
        $stmt->execute([':c' => $clienteId]);
        return array_map(fn(array $row): array => Progreso::fromRow($row)->toArray(), $stmt->fetchAll());
    }

    public function create(int $clienteId, array $data): int
    {
        $stmt = Database::getConnection()->prepare(
            "INSERT INTO progreso (cliente_id, peso, altura, brazo, cintura, pierna, notas)
             VALUES (:c, :pe, :al, :br, :ci, :pi, :no)"
        );
        $stmt->execute([
            ':c' => $clienteId,
            ':pe' => $data['peso'] ?? null,
            ':al' => $data['altura'] ?? null,
            ':br' => $data['brazo'] ?? null,
            ':ci' => $data['cintura'] ?? null,
            ':pi' => $data['pierna'] ?? null,
            ':no' => $data['notas'] ?? null,
        ]);
        return (int)Database::getConnection()->lastInsertId();
    }

    public function getLatestByCliente(int $clienteId): ?Progreso
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT * FROM progreso WHERE cliente_id = :c ORDER BY registrado_en DESC LIMIT 1'
        );
        $stmt->execute([':c' => $clienteId]);
        $row = $stmt->fetch();
        return $row ? Progreso::fromRow($row) : null;
    }
}
