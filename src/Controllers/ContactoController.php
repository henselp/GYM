<?php

declare(strict_types=1);

namespace Gymfit\Controllers;

use Gymfit\Exceptions\ValidationException;
use Gymfit\Helpers\JsonHelper;
use Gymfit\Helpers\ValidatorHelper;
use Gymfit\Repositories\ContactoRepository;
use Gymfit\Services\SecurityService;

class ContactoController
{
    public function __construct(
        private readonly ContactoRepository $contactoRepository,
        private readonly SecurityService $securityService,
    ) {}

    public function send(): void
    {
        $input = JsonHelper::input();
        $v = ValidatorHelper::validate($input)
            ->required('nombre', 'email', 'mensaje')
            ->email('email')
            ->maxLength('nombre', 120)
            ->maxLength('mensaje', 2000);

        try {
            $v->throwIf();

            $id = $this->contactoRepository->create(
                $v->sanitize('nombre'),
                $v->sanitize('email'),
                $v->sanitize('mensaje'),
            );
            JsonHelper::success(['id' => $id], 'Mensaje enviado. Pronto te contactaremos.');
        } catch (ValidationException $e) {
            JsonHelper::error($e->getMessage(), $e->getCode(), ['errors' => $e->getErrors()]);
        }
    }
}
