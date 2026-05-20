<?php

declare(strict_types=1);

namespace Gymfit\Middleware;

use Gymfit\Exceptions\AuthException;
use Gymfit\Helpers\JsonHelper;
use Gymfit\Logger\Logger;
use Gymfit\Services\SecurityService;

class SecurityMiddleware
{
    private static ?SecurityService $securityService = null;

    private static function getService(): SecurityService
    {
        if (self::$securityService === null) {
            self::$securityService = new SecurityService(Logger::getInstance());
        }
        return self::$securityService;
    }

    public static function handle(): void
    {
        $service = self::getService();
        $service->enforceSecurityHeaders();

        try {
            $service->validateCsrfToken();
        } catch (AuthException $e) {
            JsonHelper::error($e->getMessage(), 403);
        }
    }
}
