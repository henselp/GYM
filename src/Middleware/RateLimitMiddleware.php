<?php

declare(strict_types=1);

namespace Gymfit\Middleware;

use Gymfit\Helpers\JsonHelper;
use Gymfit\Logger\Logger;

class RateLimitMiddleware
{
    private static array $store = [];

    public static function handle(int $maxRequests = 60, int $windowMinutes = 1): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $key = "rl_{$ip}";
        $now = time();
        $window = $windowMinutes * 60;

        // Clean old entries
        if (isset(self::$store[$key])) {
            self::$store[$key] = array_filter(
                self::$store[$key],
                fn(int $t): bool => ($now - $t) < $window
            );
        } else {
            self::$store[$key] = [];
        }

        self::$store[$key][] = $now;

        if (count(self::$store[$key]) > $maxRequests) {
            Logger::getInstance()->security('Rate limit excedido', ['ip' => $ip]);
            JsonHelper::error('Demasiadas solicitudes. Intenta de nuevo en un minuto.', 429);
        }

        // Persist across requests via shared memory / file
        $file = sys_get_temp_dir() . '/gymfit_ratelimit_' . md5($ip);
        $data = ['count' => count(self::$store[$key]), 'time' => $now];
        file_put_contents($file, json_encode($data), LOCK_EX);
    }
}
