<?php

declare(strict_types=1);

namespace Gymfit\Services;

use Gymfit\Exceptions\ValidationException;
use Gymfit\Repositories\MensajeRepository;
use Gymfit\Repositories\UsuarioRepository;

class MensajeService
{
    public function __construct(
        private readonly MensajeRepository $mensajeRepository,
        private readonly UsuarioRepository $usuarioRepository,
    ) {}

    public function send(int $fromId, string $toEmail, string $content): array
    {
        $toUser = $this->usuarioRepository->findByEmail($toEmail);
        if (!$toUser) {
            throw new ValidationException('Usuario destinatario no encontrado');
        }

        $id = $this->mensajeRepository->send($fromId, $toUser->id, $content);

        return [
            'id' => $id,
            'de_usuario_id' => $fromId,
            'para_usuario_id' => $toUser->id,
            'contenido' => $content,
        ];
    }

    public function getConversation(int $userId1, int $userId2): array
    {
        $mensajes = $this->mensajeRepository->getConversation($userId1, $userId2);
        foreach ($mensajes as $m) {
            if ($m['para_usuario_id'] === $userId1 && !$m['leido']) {
                $this->mensajeRepository->markAsRead($m['id'], $userId1);
            }
        }
        return $mensajes;
    }

    public function getInbox(int $userId): array
    {
        return $this->mensajeRepository->getInbox($userId);
    }

    public function getUnreadCount(int $userId): int
    {
        return $this->mensajeRepository->countUnread($userId);
    }
}
