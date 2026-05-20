<?php

declare(strict_types=1);

namespace Gymfit\Controllers;

use Gymfit\Exceptions\ValidationException;
use Gymfit\Helpers\JsonHelper;
use Gymfit\Helpers\SessionHelper;
use Gymfit\Helpers\ValidatorHelper;
use Gymfit\Services\MensajeService;

class MensajeController
{
    public function __construct(
        private readonly MensajeService $mensajeService,
    ) {}

    public function send(): void
    {
        $user = SessionHelper::user();
        $input = JsonHelper::input();
        $v = ValidatorHelper::validate($input)
            ->required('para_email', 'contenido')
            ->email('para_email');

        try {
            $v->throwIf();
            $result = $this->mensajeService->send(
                (int)$user['id'],
                $v->sanitize('para_email'),
                $v->sanitize('contenido'),
            );
            JsonHelper::success($result, 'Mensaje enviado');
        } catch (ValidationException $e) {
            JsonHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function conversation(): void
    {
        $user = SessionHelper::user();
        $userId = (int)($_GET['usuario_id'] ?? 0);

        if ($userId <= 0) {
            JsonHelper::error('usuario_id requerido', 400);
        }

        $mensajes = $this->mensajeService->getConversation((int)$user['id'], $userId);
        JsonHelper::success(['mensajes' => $mensajes]);
    }

    public function inbox(): void
    {
        $user = SessionHelper::user();
        $mensajes = $this->mensajeService->getInbox((int)$user['id']);
        $noLeidos = $this->mensajeService->getUnreadCount((int)$user['id']);
        JsonHelper::success(['mensajes' => $mensajes, 'no_leidos' => $noLeidos]);
    }
}
