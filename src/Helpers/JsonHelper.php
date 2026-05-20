<?php

declare(strict_types=1);

namespace Gymfit\Helpers;

class JsonHelper
{
    public static function response(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function success(mixed $data = null, string $message = 'OK'): void
    {
        self::response(['ok' => true, 'message' => $message, 'data' => $data]);
    }

    public static function error(string $message, int $status = 400, array $extra = []): void
    {
        $payload = array_merge(['ok' => false, 'error' => $message], $extra);
        self::response($payload, $status);
    }

    public static function input(): array
    {
        $raw = file_get_contents('php://input');
        if (!$raw) {
            return $_POST;
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}
