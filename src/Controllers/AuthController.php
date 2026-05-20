<?php

declare(strict_types=1);

namespace Gymfit\Controllers;

use Gymfit\Exceptions\AuthException;
use Gymfit\Exceptions\DuplicateException;
use Gymfit\Exceptions\ValidationException;
use Gymfit\Helpers\JsonHelper;
use Gymfit\Helpers\ValidatorHelper;
use Gymfit\Logger\Logger;
use Gymfit\Repositories\UsuarioRepository;
use Gymfit\Services\AuthService;
use Gymfit\Services\SecurityService;

class AuthController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly SecurityService $securityService,
    ) {}

    public function login(): void
    {
        $input = JsonHelper::input();
        $v = ValidatorHelper::validate($input)->required('email', 'password')->email('email');

        try {
            $v->throwIf();
            $user = $this->authService->login(
                $v->sanitize('email'),
                $input['password'] ?? '',
                $v->get('rol', null),
            );
            JsonHelper::success(['user' => $user], 'Inicio de sesión exitoso');
        } catch (AuthException $e) {
            JsonHelper::error($e->getMessage(), $e->getCode());
        }
    }

    public function register(): void
    {
        $input = JsonHelper::input();
        $v = ValidatorHelper::validate($input)
            ->required('nombre', 'email', 'password', 'rol')
            ->email('email')
            ->inArray('rol', ['entrenador', 'cliente']);

        try {
            $v->throwIf();

            $passwordError = $this->securityService->validatePasswordStrength($input['password'] ?? '');
            if ($passwordError) {
                throw new ValidationException($passwordError);
            }

            $user = $this->authService->register(
                $v->sanitize('nombre'),
                $v->sanitize('email'),
                $input['password'],
                $v->get('rol'),
            );
            JsonHelper::success(['user' => $user], 'Cuenta creada exitosamente');
        } catch (ValidationException | DuplicateException $e) {
            JsonHelper::error($e->getMessage(), $e->getCode(), $e instanceof ValidationException ? ['errors' => $e->getErrors()] : []);
        }
    }

    public function logout(): void
    {
        $this->authService->logout();
        JsonHelper::success(null, 'Sesión cerrada');
    }

    public function me(): void
    {
        $user = $this->authService->getCurrentUser();
        if (!$user) {
            JsonHelper::success(['user' => null]);
            return;
        }
        JsonHelper::success(['user' => $user]);
    }
}
