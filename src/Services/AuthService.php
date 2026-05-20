<?php

declare(strict_types=1);

namespace Gymfit\Services;

use Gymfit\Exceptions\AuthException;
use Gymfit\Exceptions\DuplicateException;
use Gymfit\Exceptions\ValidationException;
use Gymfit\Helpers\SessionHelper;
use Gymfit\Logger\Logger;
use Gymfit\Repositories\UsuarioRepository;

class AuthService
{
    public function __construct(
        private readonly UsuarioRepository $usuarioRepository,
        private readonly Logger $logger,
    ) {}

    public function login(string $email, string $password, ?string $rol = null): array
    {
        $user = $this->usuarioRepository->findByEmail($email);

        if (!$user) {
            $this->logger->security('Intento de login con email inexistente', ['email' => $email]);
            throw new AuthException('Credenciales inválidas', 401);
        }

        if (!$this->usuarioRepository->verifyPassword($user->id, $password)) {
            $this->logger->security('Contraseña incorrecta', ['email' => $email, 'user_id' => $user->id]);
            throw new AuthException('Credenciales inválidas', 401);
        }

        if ($rol && $user->rol !== $rol) {
            throw new AuthException('Este usuario no tiene el rol seleccionado', 403);
        }

        SessionHelper::regenerate();
        SessionHelper::setUser($user->toPublicArray());

        $this->logger->audit('Login exitoso', ['user_id' => $user->id, 'rol' => $user->rol]);

        return $user->toPublicArray();
    }

    public function register(string $nombre, string $email, string $password, string $rol): array
    {
        if ($this->usuarioRepository->emailExists($email)) {
            throw new DuplicateException('Ese email ya está registrado');
        }

        $user = $this->usuarioRepository->create($nombre, $email, $password, $rol);

        SessionHelper::regenerate();
        SessionHelper::setUser($user->toPublicArray());

        $this->logger->audit('Registro exitoso', ['user_id' => $user->id, 'rol' => $user->rol]);

        return $user->toPublicArray();
    }

    public function logout(): void
    {
        $user = SessionHelper::user();
        if ($user) {
            $this->logger->audit('Logout', ['user_id' => $user['id']]);
        }
        SessionHelper::destroy();
    }

    public function getCurrentUser(): ?array
    {
        return SessionHelper::user();
    }
}
