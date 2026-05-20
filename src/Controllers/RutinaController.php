<?php

declare(strict_types=1);

namespace Gymfit\Controllers;

use Gymfit\Exceptions\ForbiddenException;
use Gymfit\Exceptions\ValidationException;
use Gymfit\Helpers\JsonHelper;
use Gymfit\Helpers\SessionHelper;
use Gymfit\Helpers\ValidatorHelper;
use Gymfit\Repositories\RutinaRepository;
use Gymfit\Repositories\UsuarioRepository;

class RutinaController
{
    public function __construct(
        private readonly RutinaRepository $rutinaRepository,
        private readonly UsuarioRepository $usuarioRepository,
    ) {}

    public function get(): void
    {
        $user = SessionHelper::user();
        $cid = (int)($_GET['cliente_id'] ?? 0);

        if ($user['rol'] === 'cliente') {
            $cid = (int)$user['id'];
        }

        if ($cid <= 0) {
            JsonHelper::error('cliente_id requerido', 400);
        }

        $rutina = $this->rutinaRepository->getLatestByCliente($cid);
        JsonHelper::success(['rutina' => $rutina?->toArray()]);
    }

    public function save(): void
    {
        $user = SessionHelper::user();

        if ($user['rol'] !== 'entrenador') {
            JsonHelper::error('Solo los entrenadores pueden asignar rutinas', 403);
        }

        $input = JsonHelper::input();
        $v = ValidatorHelper::validate($input)
            ->required('cliente_id', 'contenido')
            ->integer('cliente_id')
            ->positive('cliente_id');

        try {
            $v->throwIf();

            $cid = (int)$v->get('cliente_id');
            $contenido = $v->sanitize('contenido');
            $observaciones = trim((string)($input['observaciones'] ?? ''));

            // Verify client belongs to this trainer
            $belongs = $this->usuarioRepository->verifyClientBelongsToTrainer($cid, (int)$user['id']);
            if (!$belongs) {
                throw new ForbiddenException('Este cliente no está asignado a tu lista');
            }

            $id = $this->rutinaRepository->create($cid, (int)$user['id'], $contenido, $observaciones);
            JsonHelper::success(['id' => $id], 'Rutina guardada exitosamente');
        } catch (ValidationException | ForbiddenException $e) {
            JsonHelper::error($e->getMessage(), $e->getCode());
        }
    }
}
