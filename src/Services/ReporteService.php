<?php

declare(strict_types=1);

namespace Gymfit\Services;

use Gymfit\Database;
use Gymfit\Repositories\ContactoRepository;
use Gymfit\Repositories\ProgresoRepository;
use Gymfit\Repositories\RutinaRepository;
use Gymfit\Repositories\UsuarioRepository;

class ReporteService
{
    public function __construct(
        private readonly RutinaRepository $rutinaRepository,
        private readonly UsuarioRepository $usuarioRepository,
        private readonly ContactoRepository $contactoRepository,
        private readonly ProgresoRepository $progresoRepository,
    ) {}

    public function getTrainerDashboard(int $trainerId): array
    {
        $totalClientes = $this->usuarioRepository->getClientCountByTrainer($trainerId);
        $totalRutinas = $this->rutinaRepository->countByTrainer($trainerId);
        $rutinasPorMes = $this->rutinaRepository->getRoutinesPerMonth($trainerId);
        $clientesList = $this->usuarioRepository->getClientesByEntrenador($trainerId);

        $niveles = [];
        $objetivos = [];
        foreach ($clientesList as $c) {
            if ($c['nivel']) {
                $niveles[$c['nivel']] = ($niveles[$c['nivel']] ?? 0) + 1;
            }
            if ($c['objetivo']) {
                $objetivos[$c['objetivo']] = ($objetivos[$c['objetivo']] ?? 0) + 1;
            }
        }

        return [
            'total_clientes' => $totalClientes,
            'total_rutinas' => $totalRutinas,
            'rutinas_por_mes' => $rutinasPorMes,
            'distribucion_niveles' => $niveles,
            'distribucion_objetivos' => $objetivos,
        ];
    }

    public function getGlobalDashboard(): array
    {
        $totalRutinas = $this->rutinaRepository->countAll();
        $totalContactos = $this->contactoRepository->countAll();
        $rutinasPorMes = $this->rutinaRepository->getRoutinesPerMonthAll();
        $contactosPorMes = $this->contactoRepository->getContactsPerMonth();

        $stmt = Database::getConnection()->query(
            "SELECT COUNT(*) AS cnt, rol FROM usuarios GROUP BY rol"
        );
        $usuariosPorRol = $stmt->fetchAll();

        return [
            'total_rutinas' => $totalRutinas,
            'total_contactos' => $totalContactos,
            'usuarios_por_rol' => $usuariosPorRol,
            'rutinas_por_mes' => $rutinasPorMes,
            'contactos_por_mes' => $contactosPorMes,
        ];
    }

    public function getClientProgress(int $clientId): array
    {
        $progreso = $this->progresoRepository->getByCliente($clientId);
        $rutinas = $this->rutinaRepository->getHistoryByCliente($clientId);

        return [
            'mediciones' => $progreso,
            'rutinas' => $rutinas,
            'total_rutinas' => count($rutinas),
        ];
    }

    public function getActivityReport(): array
    {
        $db = Database::getConnection();
        // SQLite date formatting
        $stmt = $db->query(
            "SELECT DATE(asignada_en) AS dia, COUNT(*) AS rutinas
             FROM rutinas
             WHERE asignada_en >= datetime('now', '-30 days')
             GROUP BY dia
             ORDER BY dia"
        );
        $actividadDiaria = $stmt->fetchAll();

        $stmt2 = $db->query(
            "SELECT
                u.nombre,
                COUNT(r.id) AS rutinas_asignadas
             FROM usuarios u
             JOIN rutinas r ON r.entrenador_id = u.id
             WHERE u.rol = 'entrenador'
             GROUP BY u.id, u.nombre
             ORDER BY rutinas_asignadas DESC"
        );
        $topEntrenadores = $stmt2->fetchAll();

        return [
            'actividad_diaria' => $actividadDiaria,
            'top_entrenadores' => $topEntrenadores,
        ];
    }
}
