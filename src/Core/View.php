<?php

declare(strict_types=1);

namespace Gymfit\Core;

class View
{
    private string $viewsPath;
    private static array $globals = [];

    public function __construct(?string $viewsPath = null)
    {
        $this->viewsPath = $viewsPath ?? __DIR__ . '/../Views';
    }

    public static function share(string $key, mixed $value): void
    {
        self::$globals[$key] = $value;
    }

    public static function layout(string $name): void
    {
        $GLOBALS['_view_layout'] = 'layouts/' . $name;
    }

    public static function section(string $name): void
    {
        $GLOBALS['_view_section_' . $name] = true;
        ob_start();
    }

    public static function endSection(string $name): void
    {
        $content = ob_get_clean();
        if (isset($GLOBALS['_view_section_' . $name])) {
            $GLOBALS['_view_sections'][$name] = $content;
            unset($GLOBALS['_view_section_' . $name]);
        }
    }

    public static function yield(string $name, string $default = ''): string
    {
        return $GLOBALS['_view_sections'][$name] ?? $default;
    }

    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function csrfField(): string
    {
        $token = \Gymfit\Helpers\SessionHelper::getCsrfToken();
        return '<input type="hidden" name="_csrf_token" value="' . self::escape($token) . '">';
    }

    public static function csrfMeta(): string
    {
        $token = \Gymfit\Helpers\SessionHelper::getCsrfToken();
        return '<meta name="csrf-token" content="' . self::escape($token) . '">';
    }

    public static function asset(string $path): string
    {
        return '/' . ltrim($path, '/');
    }

    public function render(string $view, array $data = []): void
    {
        $viewFile = $this->viewsPath . '/' . $view . '.php';
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        $data = array_merge(self::$globals, $data);
        extract($data, EXTR_SKIP);

        $this->resetViewState();
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $layoutName = $GLOBALS['_view_layout'] ?? 'layouts/default';
        $GLOBALS['_view_content'] = $content;

        $layoutFile = $this->viewsPath . '/' . $layoutName . '.php';
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    private function resetViewState(): void
    {
        $GLOBALS['_view_layout'] = null;
        $GLOBALS['_view_sections'] = [];
        $GLOBALS['_view_content'] = null;
    }
}
