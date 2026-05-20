<?php

declare(strict_types=1);

namespace Gymfit\Repositories;

use Gymfit\Database;
use Gymfit\Models\Rutina;

class RutinaRepository
{
    public function getLatestByCliente(int $clienteId): ?Rutina
    {
        $stmt = Database::getConnection()->prepare(
            "SELECT r.*, ent.nombre AS entrenador_nombre
             FROM rutinas r
             JOIN usuarios ent ON ent.id = r.entrenador_id
             WHERE r.cliente_id = :c
             ORDER BY r.asignada_en DESC LIMIT 1"
        );
        $stmt->execute([':c' => $clienteId]);
        $row = $stmt->fetch();
        return $row ? Rutina::fromRow($row) : null;
    }

    public function create(int $clienteId, int $entrenadorId, string $contenido, ?string $observaciones): int
    {
        $stmt = Database::getConnection()->prepare(
            "INSERT INTO rutinas (cliente_id, entrenador_id, contenido, observaciones)
             VALUES (:c, :e, :ct, :ob)"
        );
        $stmt->execute([
            ':c' => $clienteId,
            ':e' => $entrenadorId,
            ':ct' => $contenido,
            ':ob' => $observaciones ?? '',
        ]);
        return (int)Database::getConnection()->lastInsertId();
    }

    public function countByTrainer(int $entrenadorId): int
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT COUNT(*) AS cnt FROM rutinas WHERE entrenador_id = :e'
        );
        $stmt->execute([':e' => $entrenadorId]);
        return (int)$stmt->fetch()['cnt'];
    }

    public function getRoutinesPerMonth(int $entrenadorId, int $months = 6): array
    {
        $stmt = Database::getConnection()->prepare(
            "SELECT strftime('%Y-%m', asignada_en) AS mes, COUNT(*) AS total
             FROM rutinas
             WHERE entrenador_id = :e
               AND asignada_en >= datetime('now', '-6 months')
             GROUP BY mes
             ORDER BY mes"
        );
        $stmt->execute([':e' => $entrenadorId]);
        return $stmt->fetchAll();
    }

    public function getRoutinesPerMonthAll(): array
    {
        $stmt = Database::getConnection()->prepare(
            "SELECT strftime('%Y-%m', asignada_en) AS mes, COUNT(*) AS total
             FROM rutinas
             WHERE asignada_en >= datetime('now', '-6 months')
             GROUP BY mes
             ORDER BY mes"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getHistoryByCliente(int $clienteId): array
    {
        $stmt = Database::getConnection()->prepare(
            "SELECT r.*, ent.nombre AS entrenador_nombre
             FROM rutinas r
             JOIN usuarios ent ON ent.id = r.entrenador_id
             WHERE r.cliente_id = :c
             ORDER BY r.asignada_en DESC"
        );
        $stmt->execute([':c' => $clienteId]);
        return array_map(fn(array $row): array => Rutina::fromRow($row)->toArray(), $stmt->fetchAll());
    }

    public function countAll(): int
    {
        $stmt = Database::getConnection()->query('SELECT COUNT(*) AS cnt FROM rutinas');
        return (int)$stmt->fetch()['cnt'];
    }
}
