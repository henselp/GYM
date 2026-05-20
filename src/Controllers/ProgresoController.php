<?php

declare(strict_types=1);

namespace Gymfit\Controllers;

use Gymfit\Helpers\JsonHelper;
use Gymfit\Helpers\SessionHelper;
use Gymfit\Helpers\ValidatorHelper;
use Gymfit\Repositories\ProgresoRepository;

class ProgresoController
{
    public function __construct(
        private readonly ProgresoRepository $progresoRepository,
    ) {}

    public function save(): void
    {
        $user = SessionHelper::user();
        $input = JsonHelper::input();
        $cid = (int)($input['cliente_id'] ?? 0);

        if ($user['rol'] === 'cliente') {
            $cid = (int)$user['id'];
        }

        if ($cid <= 0) {
            JsonHelper::error('cliente_id requerido', 400);
        }

        $id = $this->progresoRepository->create($cid, $input);
        JsonHelper::success(['id' => $id], 'Medición guardada');
    }

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

        $progreso = $this->progresoRepository->getByCliente($cid);
        JsonHelper::success(['mediciones' => $progreso]);
    }
}
