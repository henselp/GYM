<?php

declare(strict_types=1);

namespace Gymfit\Controllers;

use Gymfit\Exceptions\NotFoundException;
use Gymfit\Exceptions\ValidationException;
use Gymfit\Helpers\JsonHelper;
use Gymfit\Helpers\SessionHelper;
use Gymfit\Helpers\ValidatorHelper;
use Gymfit\Repositories\UsuarioRepository;

class ClienteController
{
    public function __construct(
        private readonly UsuarioRepository $usuarioRepository,
    ) {}

    public function list(): void
    {
        $user = SessionHelper::user();
        $clientes = $this->usuarioRepository->getClientesByEntrenador((int)$user['id']);
        JsonHelper::success(['clientes' => $clientes]);
    }

    public function assign(): void
    {
        $user = SessionHelper::user();
        $input = JsonHelper::input();
        $v = ValidatorHelper::validate($input)->required('email')->email('email');

        try {
            $v->throwIf();

            $cliente = $this->usuarioRepository->findByEmail($v->sanitize('email'));
            if (!$cliente || !$cliente->isClient()) {
                throw new NotFoundException('No existe un cliente con ese email');
            }

            $this->usuarioRepository->assignClientToTrainer((int)$user['id'], $cliente->id);
            JsonHelper::success(null, 'Cliente agregado exitosamente');
        } catch (ValidationException | NotFoundException $e) {
            JsonHelper::error($e->getMessage(), $e->getCode());
        }
    }
}
