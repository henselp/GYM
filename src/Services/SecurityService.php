<?php

declare(strict_types=1);

namespace Gymfit\Services;

use Gymfit\Exceptions\AuthException;
use Gymfit\Helpers\SessionHelper;
use Gymfit\Logger\Logger;

class SecurityService
{
    private array $config;

    public function __construct(
        private readonly Logger $logger,
    ) {
        $configPath = __DIR__ . '/../../config/app.php';
        $this->config = file_exists($configPath) ? require $configPath : [];
    }

    public function enforceSecurityHeaders(): void
    {
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: DENY");
        header("X-XSS-Protection: 1; mode=block");
        header("Referrer-Policy: strict-origin-when-cross-origin");
        header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
        header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net https://fonts.googleapis.com https://cdnjs.cloudflare.com 'unsafe-inline'; style-src 'self' https://cdn.jsdelivr.net https://fonts.googleapis.com 'unsafe-inline'; font-src 'self' https://cdn.jsdelivr.net https://fonts.gstatic.com; img-src 'self' https://images.unsplash.com https://i.pravatar.cc data:; connect-src 'self'");
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

        $cors = $this->config['cors'] ?? [];
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        if ($origin && in_array($origin, $cors['allowed_origins'] ?? [], true)) {
            header("Access-Control-Allow-Origin: {$origin}");
            header("Access-Control-Allow-Methods: " . implode(', ', $cors['allowed_methods'] ?? []));
            header("Access-Control-Allow-Headers: " . implode(', ', $cors['allowed_headers'] ?? []));
            header("Access-Control-Allow-Credentials: true");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }

    public function validateCsrfToken(): void
    {
        $exemptPaths = ['/api/auth/login', '/api/auth/register', '/api/contacto'];
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);

        if (in_array($path, $exemptPaths, true)) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return;
        }

        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf_token'] ?? '';
        if (!SessionHelper::validateCsrfToken($token)) {
            $this->logger->security('CSRF token inválido', [
                'path' => $path,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            ]);
            throw new AuthException('Token de seguridad inválido', 403);
        }
    }

    public function sanitizeInput(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $value = trim($value);
                $value = strip_tags($value);
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
            $sanitized[$key] = $value;
        }
        return $sanitized;
    }

    public function validatePasswordStrength(string $password): ?string
    {
        $pwdConfig = $this->config['password'] ?? [];

        if (mb_strlen($password) < ($pwdConfig['min_length'] ?? 8)) {
            return 'La contraseña debe tener al menos ' . ($pwdConfig['min_length'] ?? 8) . ' caracteres';
        }
        if (($pwdConfig['require_uppercase'] ?? true) && !preg_match('/[A-Z]/', $password)) {
            return 'La contraseña debe contener al menos una mayúscula';
        }
        if (($pwdConfig['require_number'] ?? true) && !preg_match('/[0-9]/', $password)) {
            return 'La contraseña debe contener al menos un número';
        }
        if (($pwdConfig['require_special'] ?? true) && !preg_match('/[^A-Za-z0-9]/', $password)) {
            return 'La contraseña debe contener al menos un carácter especial';
        }
        return null;
    }

    public function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
