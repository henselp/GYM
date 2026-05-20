<?php

declare(strict_types=1);

namespace Gymfit\Repositories;

use Gymfit\Database;
use Gymfit\Models\Usuario;

class UsuarioRepository
{
    public function findByEmail(string $email): ?Usuario
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT id, nombre, email, password_hash, rol, avatar_url, edad, objetivo, nivel, creado_en
             FROM usuarios WHERE email = :email LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row ? Usuario::fromRow($row) : null;
    }

    public function findById(int $id): ?Usuario
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT id, nombre, email, password_hash, rol, avatar_url, edad, objetivo, nivel, creado_en
             FROM usuarios WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? Usuario::fromRow($row) : null;
    }

    public function verifyPassword(int $id, string $password): bool
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT password_hash FROM usuarios WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            return false;
        }
        return password_verify($password, $row['password_hash']);
    }

    public function create(string $nombre, string $email, string $password, string $rol): Usuario
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = Database::getConnection()->prepare(
            "INSERT INTO usuarios (nombre, email, password_hash, rol)
             VALUES (:n, :e, :h, :r)"
        );
        $stmt->execute([':n' => $nombre, ':e' => $email, ':h' => $hash, ':r' => $rol]);

        $id = (int)Database::getConnection()->lastInsertId();
        return $this->findById($id);
    }

    public function emailExists(string $email): bool
    {
        $stmt = Database::getConnection()->prepare('SELECT 1 FROM usuarios WHERE email = :e LIMIT 1');
        $stmt->execute([':e' => $email]);
        return (bool)$stmt->fetch();
    }

    public function getClientesByEntrenador(int $entrenadorId): array
    {
        $stmt = Database::getConnection()->prepare(
            "SELECT u.id, u.nombre, u.email, u.avatar_url, u.edad, u.objetivo, u.nivel,
                    (SELECT MAX(asignada_en) FROM rutinas r WHERE r.cliente_id = u.id) AS ultima_rutina
             FROM entrenador_cliente ec
             JOIN usuarios u ON u.id = ec.cliente_id
             WHERE ec.entrenador_id = :eid
             ORDER BY u.nombre"
        );
        $stmt->execute([':eid' => $entrenadorId]);
        $rows = $stmt->fetchAll();
        return array_map(fn(array $row): array => [
            'id' => (int)$row['id'],
            'nombre' => $row['nombre'],
            'email' => $row['email'],
            'avatar_url' => $row['avatar_url'],
            'edad' => $row['edad'] ? (int)$row['edad'] : null,
            'objetivo' => $row['objetivo'],
            'nivel' => $row['nivel'],
            'ultima_rutina' => $row['ultima_rutina'],
        ], $rows);
    }

    public function assignClientToTrainer(int $trainerId, int $clientId): bool
    {
        $stmt = Database::getConnection()->prepare(
            "INSERT INTO entrenador_cliente (entrenador_id, cliente_id)
             VALUES (:e, :c) ON CONFLICT DO NOTHING"
        );
        $stmt->execute([':e' => $trainerId, ':c' => $clientId]);
        return $stmt->rowCount() > 0;
    }

    public function verifyClientBelongsToTrainer(int $clientId, int $trainerId): bool
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT 1 FROM entrenador_cliente WHERE entrenador_id = :e AND cliente_id = :c LIMIT 1'
        );
        $stmt->execute([':e' => $trainerId, ':c' => $clientId]);
        return (bool)$stmt->fetch();
    }

    public function getEntrenadorByCliente(int $clientId): ?int
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT entrenador_id FROM entrenador_cliente WHERE cliente_id = :c LIMIT 1'
        );
        $stmt->execute([':c' => $clientId]);
        $row = $stmt->fetch();
        return $row ? (int)$row['entrenador_id'] : null;
    }

    public function getClientCountByTrainer(int $trainerId): int
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT COUNT(*) AS cnt FROM entrenador_cliente WHERE entrenador_id = :e'
        );
        $stmt->execute([':e' => $trainerId]);
        return (int)$stmt->fetch()['cnt'];
    }
}
