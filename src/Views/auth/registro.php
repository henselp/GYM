<?php
\Gymfit\Core\View::layout('default');
$title = 'Registro - GYMFIT';
?>
<?php \Gymfit\Core\View::section('scripts'); ?>
<script>
document.getElementById('formReg').addEventListener('submit', async (e) => {
  e.preventDefault();
  const f = e.target;
  try {
    const r = await api('/auth/register', { method:'POST', body: {
      nombre:f.nombre.value.trim(), email:f.email.value.trim(),
      password:f.password.value, rol:f.rol.value
    }});
    toast('¡Cuenta creada!');
    setTimeout(() => redirectByRole(r.data.user), 600);
  } catch(err){ toast(err.message, 'error'); }
});
</script>
<?php \Gymfit\Core\View::endSection('scripts'); ?>

<div class="auth-wrap">
  <div class="auth-card">
    <div class="text-center">
      <a href="<?= \Gymfit\Core\View::asset('') ?>" class="gf-logo d-inline-block">GYM<span>FIT</span></a>
      <h4 class="mt-3 mb-4 fw-bold">Crear cuenta</h4>
    </div>

    <form id="formReg">
      <?= \Gymfit\Core\View::csrfField() ?>
      <div class="mb-3"><input class="form-control" name="nombre" placeholder="Nombre completo" required></div>
      <div class="mb-3"><input class="form-control" type="email" name="email" placeholder="Email" required></div>
      <div class="mb-3"><input class="form-control" type="password" name="password" placeholder="Contraseña (mín. 8, mayúscula, número, especial)" required minlength="8"></div>
      <div class="mb-3">
        <select class="form-control form-select" name="rol" required>
          <option value="">Selecciona tu rol</option>
          <option value="cliente">Cliente</option>
          <option value="entrenador">Entrenador</option>
        </select>
      </div>
      <button class="btn btn-gf w-100" type="submit">Registrarme</button>
    </form>
    <p class="mt-3 text-center small text-muted">¿Ya tienes cuenta? <a href="<?= \Gymfit\Core\View::asset('login') ?>">Inicia sesión</a></p>
  </div>
</div>
