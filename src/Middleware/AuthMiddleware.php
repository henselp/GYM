<?php

declare(strict_types=1);

namespace Gymfit\Middleware;

use Gymfit\Exceptions\AuthException;
use Gymfit\Helpers\JsonHelper;
use Gymfit\Helpers\SessionHelper;

class AuthMiddleware
{
    public static function authenticate(): void
    {
        if (!SessionHelper::isAuthenticated()) {
            JsonHelper::error('No autenticado', 401);
        }
    }

    public static function requireRole(string $rol): void
    {
        self::authenticate();
        $user = SessionHelper::user();
        if ($user['rol'] !== $rol) {
            JsonHelper::error('Acceso denegado', 403);
        }
    }

    public static function requireEntrenador(): void
    {
        self::requireRole('entrenador');
    }

    public static function requireCliente(): void
    {
        self::requireRole('cliente');
    }

    public static function getUser(): array
    {
        self::authenticate();
        return SessionHelper::user();
    }
}
