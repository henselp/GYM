<?php

declare(strict_types=1);

/**
 * GYMFIT - Front Controller
 * Punto de entrada único para la API REST.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Gymfit\Helpers\JsonHelper;
use Gymfit\Helpers\SessionHelper;
use Gymfit\Logger\Logger;
use Gymfit\Middleware\AuthMiddleware;
use Gymfit\Middleware\RateLimitMiddleware;
use Gymfit\Middleware\SecurityMiddleware;
use Gymfit\Router;
use Gymfit\Controllers\AuthController;
use Gymfit\Controllers\ClienteController;
use Gymfit\Controllers\ContactoController;
use Gymfit\Controllers\MensajeController;
use Gymfit\Controllers\ProgresoController;
use Gymfit\Controllers\ReporteController;
use Gymfit\Controllers\RutinaController;
use Gymfit\Repositories\ContactoRepository;
use Gymfit\Repositories\MensajeRepository;
use Gymfit\Repositories\ProgresoRepository;
use Gymfit\Repositories\RutinaRepository;
use Gymfit\Repositories\UsuarioRepository;
use Gymfit\Services\AuthService;
use Gymfit\Services\MensajeService;
use Gymfit\Services\ReporteService;
use Gymfit\Services\SecurityService;

// ── Boot ───────────────────────────────────────────────────────────
error_reporting(E_ALL);
date_default_timezone_set('America/Argentina/Buenos_Aires');

$config = require __DIR__ . '/../config/app.php';

if (!$config['debug']) {
    set_exception_handler(function (\Throwable $e): void {
        Logger::getInstance()->error('Excepción no capturada', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
        JsonHelper::error('Error interno del servidor', 500);
    });
}

SessionHelper::start();

// ── Dependencies ───────────────────────────────────────────────────
$usuarioRepo = new UsuarioRepository();
$rutinaRepo = new RutinaRepository();
$contactoRepo = new ContactoRepository();
$mensajeRepo = new MensajeRepository();
$progresoRepo = new ProgresoRepository();

$securityService = new SecurityService(Logger::getInstance());
$authService = new AuthService($usuarioRepo, Logger::getInstance());
$reporteService = new ReporteService($rutinaRepo, $usuarioRepo, $contactoRepo, $progresoRepo);
$mensajeService = new MensajeService($mensajeRepo, $usuarioRepo);

// ── Router ─────────────────────────────────────────────────────────
$router = new Router();
$pageController = new \Gymfit\Controllers\PageController();

// Global middleware
$router->addMiddleware(function () use ($securityService): void {
    SecurityMiddleware::handle();
    RateLimitMiddleware::handle(60, 1);
});

// ── Páginas (MVC Views) ──
$router->get('/', fn() => $pageController->landing());
$router->get('/login', fn() => $pageController->login());
$router->get('/registro', fn() => $pageController->registro());
$router->get('/seleccionar-rol', fn() => $pageController->seleccionarRol());
$router->get('/panel-entrenador', fn() => $pageController->panelEntrenador());
$router->get('/asignar-rutina', fn() => $pageController->asignarRutina());
$router->get('/panel-cliente', fn() => $pageController->panelCliente());

// Auth API
$authController = new AuthController($authService, $securityService);
$router->post('/api/auth/login', fn() => $authController->login());
$router->post('/api/auth/register', fn() => $authController->register());
$router->post('/api/auth/logout', fn() => $authController->logout());
$router->get('/api/auth/me', fn() => $authController->me());

// Clientes
$clienteController = new ClienteController($usuarioRepo);
$router->get('/api/clientes', fn() => $clienteController->list(), [fn() => AuthMiddleware::requireEntrenador()]);
$router->post('/api/clientes', fn() => $clienteController->assign(), [fn() => AuthMiddleware::requireEntrenador()]);

// Rutinas
$rutinaController = new RutinaController($rutinaRepo, $usuarioRepo);
$router->get('/api/rutinas', fn() => $rutinaController->get(), [fn() => AuthMiddleware::authenticate()]);
$router->post('/api/rutinas', fn() => $rutinaController->save(), [fn() => AuthMiddleware::authenticate()]);

// Contacto (público)
$contactoController = new ContactoController($contactoRepo, $securityService);
$router->post('/api/contacto', fn() => $contactoController->send());

// Reportes
$reporteController = new ReporteController($reporteService, $progresoRepo);
$router->get('/api/reportes/trainer-dashboard', fn() => $reporteController->trainerDashboard(), [fn() => AuthMiddleware::requireEntrenador()]);
$router->get('/api/reportes/client-progress', fn() => $reporteController->clientProgress(), [fn() => AuthMiddleware::authenticate()]);
$router->get('/api/reportes/global', fn() => $reporteController->globalDashboard(), [fn() => AuthMiddleware::authenticate()]);
$router->get('/api/reportes/activity', fn() => $reporteController->activity(), [fn() => AuthMiddleware::authenticate()]);

// Mensajes
$mensajeController = new MensajeController($mensajeService);
$router->post('/api/mensajes', fn() => $mensajeController->send(), [fn() => AuthMiddleware::authenticate()]);
$router->get('/api/mensajes/conversacion', fn() => $mensajeController->conversation(), [fn() => AuthMiddleware::authenticate()]);
$router->get('/api/mensajes/inbox', fn() => $mensajeController->inbox(), [fn() => AuthMiddleware::authenticate()]);

// Progreso
$progresoController = new ProgresoController($progresoRepo);
$router->post('/api/progreso', fn() => $progresoController->save(), [fn() => AuthMiddleware::authenticate()]);
$router->get('/api/progreso', fn() => $progresoController->get(), [fn() => AuthMiddleware::authenticate()]);

// ── Dispatch ───────────────────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

try {
    $router->dispatch($method, $uri);
} catch (\Throwable $e) {
    Logger::getInstance()->error('Error en dispatch', [
        'message' => $e->getMessage(),
        'uri' => $uri,
        'method' => $method,
    ]);
    JsonHelper::error('Error interno del servidor', 500);
}
