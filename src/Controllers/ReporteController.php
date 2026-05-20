<?php

declare(strict_types=1);

namespace Gymfit\Controllers;

use Gymfit\Helpers\JsonHelper;
use Gymfit\Helpers\SessionHelper;
use Gymfit\Repositories\ProgresoRepository;
use Gymfit\Repositories\RutinaRepository;
use Gymfit\Services\ReporteService;

class ReporteController
{
    public function __construct(
        private readonly ReporteService $reporteService,
        private readonly ProgresoRepository $progresoRepository,
    ) {}

    /**
     * Dashboard del entrenador (Reporte 1)
     */
    public function trainerDashboard(): void
    {
        $user = SessionHelper::user();
        $dashboard = $this->reporteService->getTrainerDashboard((int)$user['id']);
        JsonHelper::success($dashboard);
    }

    /**
     * Progreso del cliente (Reporte 2)
     */
    public function clientProgress(): void
    {
        $user = SessionHelper::user();
        $cid = (int)($_GET['cliente_id'] ?? 0);

        if ($user['rol'] === 'cliente') {
            $cid = (int)$user['id'];
        }

        if ($cid <= 0) {
            JsonHelper::error('cliente_id requerido', 400);
        }

        $progress = $this->reporteService->getClientProgress($cid);
        JsonHelper::success($progress);
    }

    /**
     * Dashboard global del sistema
     */
    public function globalDashboard(): void
    {
        $dashboard = $this->reporteService->getGlobalDashboard();
        JsonHelper::success($dashboard);
    }

    /**
     * Reporte de actividad
     */
    public function activity(): void
    {
        $report = $this->reporteService->getActivityReport();
        JsonHelper::success($report);
    }
}
