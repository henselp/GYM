<?php
\Gymfit\Core\View::layout('default');
$title = 'Selecciona tu rol - GYMFIT';
?>
<body class="app-bg">
  <nav class="gf-navbar">
    <div class="container">
      <a class="gf-logo" href="<?= \Gymfit\Core\View::asset('') ?>">GYM<span>FIT</span></a>
    </div>
  </nav>

  <main class="container py-5">
    <div class="text-center mb-5">
      <h2 class="fw-bold">Selecciona tu rol</h2>
      <p class="text-secondary">Elige cómo deseas acceder a la plataforma</p>
    </div>
    <div class="row g-4 justify-content-center">
      <div class="col-md-5">
        <a href="<?= \Gymfit\Core\View::asset('panel-entrenador') ?>" class="text-decoration-none text-light"><div class="role-card">
          <div class="role-icon"><i class="bi bi-person-badge-fill"></i></div>
          <h4 class="fw-bold">Entrenador</h4>
          <p class="text-secondary mb-0">Gestiona tus clientes, asigna rutinas y lleva el control de su progreso.</p>
        </div></a>
      </div>
      <div class="col-md-5">
        <a href="<?= \Gymfit\Core\View::asset('panel-cliente') ?>" class="text-decoration-none text-light"><div class="role-card">
          <div class="role-icon"><i class="bi bi-person-fill"></i></div>
          <h4 class="fw-bold">Cliente</h4>
          <p class="text-secondary mb-0">Consulta tu rutina, revisa tus observaciones y sigue tu progreso.</p>
        </div></a>
      </div>
    </div>
  </main>

  <footer class="gf-footer mt-5">© 2026 GYMFIT - Todos los derechos reservados</footer>
