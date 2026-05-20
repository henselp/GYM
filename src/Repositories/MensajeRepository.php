<?php

declare(strict_types=1);

namespace Gymfit\Repositories;

use Gymfit\Database;
use Gymfit\Models\Mensaje;

class MensajeRepository
{
    public function getConversation(int $userId1, int $userId2): array
    {
        $stmt = Database::getConnection()->prepare(
            "SELECT m.*, u.nombre AS de_nombre
             FROM mensajes m
             JOIN usuarios u ON u.id = m.de_usuario_id
             WHERE (m.de_usuario_id = :u1 AND m.para_usuario_id = :u2)
                OR (m.de_usuario_id = :u2 AND m.para_usuario_id = :u1)
             ORDER BY m.enviado_en ASC"
        );
        $stmt->execute([':u1' => $userId1, ':u2' => $userId2]);
        return array_map(fn(array $row): array => Mensaje::fromRow($row)->toArray(), $stmt->fetchAll());
    }

    public function getInbox(int $usuarioId): array
    {
        $stmt = Database::getConnection()->prepare(
            "SELECT m.*, u.nombre AS de_nombre
             FROM mensajes m
             JOIN usuarios u ON u.id = m.de_usuario_id
             WHERE m.para_usuario_id = :p
             ORDER BY m.enviado_en DESC"
        );
        $stmt->execute([':p' => $usuarioId]);
        return array_map(fn(array $row): array => Mensaje::fromRow($row)->toArray(), $stmt->fetchAll());
    }

    public function send(int $fromId, int $toId, string $content): int
    {
        $stmt = Database::getConnection()->prepare(
            "INSERT INTO mensajes (de_usuario_id, para_usuario_id, contenido)
             VALUES (:d, :p, :c)"
        );
        $stmt->execute([':d' => $fromId, ':p' => $toId, ':c' => $content]);
        return (int)Database::getConnection()->lastInsertId();
    }

    public function markAsRead(int $mensajeId, int $usuarioId): void
    {
        $stmt = Database::getConnection()->prepare(
            "UPDATE mensajes SET leido = TRUE WHERE id = :id AND para_usuario_id = :p"
        );
        $stmt->execute([':id' => $mensajeId, ':p' => $usuarioId]);
    }

    public function countUnread(int $usuarioId): int
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT COUNT(*) AS cnt FROM mensajes WHERE para_usuario_id = :p AND leido = FALSE'
        );
        $stmt->execute([':p' => $usuarioId]);
        return (int)$stmt->fetch()['cnt'];
    }
}
