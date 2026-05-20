<?php

declare(strict_types=1);

namespace Gymfit\Logger;

class Logger
{
    private string $logDir;
    private static ?Logger $instance = null;

    public function __construct(?string $logDir = null)
    {
        $this->logDir = $logDir ?? __DIR__ . '/../../logs';
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0775, true);
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }

    public function security(string $message, array $context = []): void
    {
        $this->log('SECURITY', $message, $context);
    }

    public function audit(string $action, array $context = []): void
    {
        $this->log('AUDIT', $action, $context);
    }

    private function log(string $level, string $message, array $context = []): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
        $userId = $_SESSION['user']['id'] ?? 'anon';
        $contextStr = $context !== [] ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $line = "[{$timestamp}] [{$level}] [{$ip}] [user:{$userId}] {$message}{$contextStr}" . PHP_EOL;

        $filename = $this->logDir . '/' . date('Y-m-d') . '-' . strtolower($level) . '.log';
        file_put_contents($filename, $line, FILE_APPEND | LOCK_EX);
    }
}
