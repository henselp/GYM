<?php

declare(strict_types=1);

namespace Gymfit\Controllers;

use Gymfit\Core\View;
use Gymfit\Helpers\SessionHelper;

class PageController
{
    private View $view;

    public function __construct()
    {
        $this->view = new View();
    }

    public function landing(): void
    {
        $this->view->render('landing/index');
    }

    public function login(): void
    {
        if (SessionHelper::isAuthenticated()) {
            $this->redirectByRole();
            return;
        }
        $this->view->render('auth/login');
    }

    public function registro(): void
    {
        if (SessionHelper::isAuthenticated()) {
            $this->redirectByRole();
            return;
        }
        $this->view->render('auth/registro');
    }

    public function seleccionarRol(): void
    {
        $this->view->render('auth/seleccionar-rol');
    }

    public function panelEntrenador(): void
    {
        $user = SessionHelper::user();
        if (!$user) {
            header('Location: /login');
            exit;
        }
        if ($user['rol'] !== 'entrenador') {
            header('Location: /panel-cliente');
            exit;
        }
        $this->view->render('entrenador/panel', ['user' => $user]);
    }

    public function asignarRutina(): void
    {
        $user = SessionHelper::user();
        if (!$user) {
            header('Location: /login');
            exit;
        }
        if ($user['rol'] !== 'entrenador') {
            header('Location: /panel-cliente');
            exit;
        }
        $this->view->render('entrenador/asignar-rutina', ['user' => $user]);
    }

    public function panelCliente(): void
    {
        $user = SessionHelper::user();
        if (!$user) {
            header('Location: /login');
            exit;
        }
        if ($user['rol'] !== 'cliente') {
            header('Location: /panel-entrenador');
            exit;
        }
        $this->view->render('cliente/panel', ['user' => $user]);
    }

    private function redirectByRole(): void
    {
        $user = SessionHelper::user();
        if ($user['rol'] === 'entrenador') {
            header('Location: /panel-entrenador');
        } else {
            header('Location: /panel-cliente');
        }
        exit;
    }
}
