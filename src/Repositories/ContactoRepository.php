<?php

declare(strict_types=1);

namespace Gymfit\Repositories;

use Gymfit\Database;
use Gymfit\Models\Contacto;

class ContactoRepository
{
    public function create(string $nombre, string $email, string $mensaje): int
    {
        $stmt = Database::getConnection()->prepare(
            'INSERT INTO contactos (nombre, email, mensaje) VALUES (:n, :e, :m)'
        );
        $stmt->execute([':n' => $nombre, ':e' => $email, ':m' => $mensaje]);
        return (int)Database::getConnection()->lastInsertId();
    }

    public function getAll(): array
    {
        $stmt = Database::getConnection()->query(
            'SELECT * FROM contactos ORDER BY enviado_en DESC'
        );
        return array_map(fn(array $row): array => Contacto::fromRow($row)->toArray(), $stmt->fetchAll());
    }

    public function countAll(): int
    {
        $stmt = Database::getConnection()->query('SELECT COUNT(*) AS cnt FROM contactos');
        return (int)$stmt->fetch()['cnt'];
    }

    public function getContactsPerMonth(): array
    {
        $stmt = Database::getConnection()->prepare(
            "SELECT strftime('%Y-%m', enviado_en) AS mes, COUNT(*) AS total
             FROM contactos
             WHERE enviado_en >= datetime('now', '-6 months')
             GROUP BY mes
             ORDER BY mes"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
