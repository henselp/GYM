<?php
/**
 * GYMFIT - Bootstrap
 *
 * Punto de entrada para servidores sin router.php
 * (CodeSandbox, php -S sin argumentos, etc.)
 */

try {
    // Si se accede a un archivo estático existente, servirlo directamente
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $staticExtensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'eot', 'map', 'webp'];

    $ext = pathinfo($uri, PATHINFO_EXTENSION);
    if ($ext && in_array($ext, $staticExtensions, true)) {
        return false;
    }

    $filePath = __DIR__ . $uri;
    if ($uri !== '/' && file_exists($filePath) && !is_dir($filePath)) {
        return false;
    }

    // Generar autoload si falta (CodeSandbox, entornos nuevos)
    if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
        $composer = __DIR__ . '/composer.phar';
        if (!file_exists($composer)) {
            $phar = @file_get_contents('https://getcomposer.org/composer.phar');
            if ($phar) {
                file_put_contents($composer, $phar);
            }
        }
        if (file_exists($composer)) {
            $cmd = sprintf('php %s dump-autoload --no-interaction 2>&1', escapeshellarg($composer));
            shell_exec($cmd);
        }
    }

    // Enrutar al front controller
    require __DIR__ . '/public/index.php';
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
