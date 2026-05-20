<?php

declare(strict_types=1);

namespace Gymfit\Tests\Integration;

use Gymfit\Database;
use Gymfit\Helpers\SessionHelper;
use Gymfit\Repositories\UsuarioRepository;
use Gymfit\Services\AuthService;
use Gymfit\Logger\Logger;
use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    private AuthService $authService;
    private UsuarioRepository $usuarioRepository;

    protected function setUp(): void
    {
        // Use SQLite in-memory for testing
        $_SERVER['APP_ENV'] = 'testing';
        $this->usuarioRepository = new UsuarioRepository();
        $this->authService = new AuthService(
            $this->usuarioRepository,
            $this->createMock(Logger::class)
        );
    }

    public function testRegisterNewUser(): void
    {
        $user = $this->authService->register(
            'Test User',
            'test-' . uniqid() . '@example.com',
            'Password1!',
            'cliente'
        );

        $this->assertNotEmpty($user['id']);
        $this->assertEquals('Test User', $user['nombre']);
        $this->assertEquals('cliente', $user['rol']);
    }

    public function testRegisterDuplicateEmail(): void
    {
        $email = 'duplicate-' . uniqid() . '@test.com';
        $this->authService->register('First', $email, 'Password1!', 'cliente');

        $this->expectException(\Gymfit\Exceptions\DuplicateException::class);
        $this->authService->register('Second', $email, 'Password1!', 'cliente');
    }

    public function testLoginSuccess(): void
    {
        $email = 'login-' . uniqid() . '@test.com';
        $this->authService->register('Login User', $email, 'Password1!', 'entrenador');
        $user = $this->authService->login($email, 'Password1!');

        $this->assertEquals('Login User', $user['nombre']);
        $this->assertEquals('entrenador', $user['rol']);
    }

    public function testLoginInvalidPassword(): void
    {
        $this->expectException(\Gymfit\Exceptions\AuthException::class);
        $this->authService->login('entrenador@gymfit.com', 'WrongPassword1!');
    }
}
