<?php

/**
 * GYMFIT - Router para PHP built-in server
 *
 * Uso: php -S localhost:8000 config/router.php
 *
 * Sirve archivos estáticos y enruta páginas MVC + API al front controller.
 */

$root = dirname(__DIR__);
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Extensiones de archivos estáticos que servimos directamente
$staticExtensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'eot', 'map'];

$ext = pathinfo($uri, PATHINFO_EXTENSION);

// Si tiene extensión de archivo estático, servirlo directamente
if ($ext && in_array($ext, $staticExtensions, true)) {
    return false;
}

// Si existe el archivo exacto, servirlo
$rootFile = $root . $uri;
if ($uri !== '/' && file_exists($rootFile) && !is_dir($rootFile)) {
    return false;
}

// Enrutar todo al front controller (MVC pages + API)
require $root . '/public/index.php';
