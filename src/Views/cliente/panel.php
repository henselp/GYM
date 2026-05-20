<?php
\Gymfit\Core\View::layout('default');
$title = 'Mi rutina - GYMFIT';
$user = $user ?? [];
?>
<?php \Gymfit\Core\View::section('styles'); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.css">
<?php \Gymfit\Core\View::endSection('styles'); ?>
<?php \Gymfit\Core\View::section('scripts'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.bundle.min.js"></script>
<script>
let chartPeso = null;

(async () => {
  const u = await requireRole('cliente');
  if (!u) return;
  document.getElementById('meName').textContent = u.nombre;

  try {
    const r = await api('/rutinas');
    if (r.data.rutina) {
      document.getElementById('rutinaBox').textContent = r.data.rutina.contenido;
      document.getElementById('obsBox').textContent = r.data.rutina.observaciones || 'Sin observaciones';
      document.getElementById('meta').textContent = `Asignada el ${formatDate(r.data.rutina.asignada_en)} por ${r.data.rutina.entrenador_nombre}`;
    } else {
      document.getElementById('rutinaBox').textContent = 'Aún no tienes una rutina asignada. Contacta a tu entrenador.';
    }
  } catch(e){ toast(e.message, 'error'); }
})();

document.querySelectorAll('[data-section]').forEach(link => {
  link.addEventListener('click', (e) => {
    e.preventDefault();
    document.querySelectorAll('[data-section]').forEach(x => x.classList.remove('active'));
    link.classList.add('active');
    document.querySelectorAll('.dash-main > div[id^="sec"]').forEach(x => x.style.display = 'none');
    const sec = document.getElementById('sec' + link.dataset.section.charAt(0).toUpperCase() + link.dataset.section.slice(1));
    if (sec) sec.style.display = 'block';
    if (link.dataset.section === 'progreso') cargarProgreso();
    if (link.dataset.section === 'mensajes') cargarMensajes();
  });
});

async function cargarProgreso() {
  try {
    const r = await api('/reportes/client-progress');
    const d = r.data;
    document.getElementById('progRutinas').textContent = d.total_rutinas;

    if (d.mediciones.length > 0) {
      const ultima = d.mediciones[d.mediciones.length - 1];
      document.getElementById('progPeso').textContent = ultima.peso ? ultima.peso + ' kg' : '—';
      document.getElementById('progBrazo').textContent = ultima.brazo ? ultima.brazo + ' cm' : '—';
      document.getElementById('progCintura').textContent = ultima.cintura ? ultima.cintura + ' cm' : '—';

      const fechas = d.mediciones.map(m => formatDate(m.registrado_en));
      const pesos = d.mediciones.map(m => m.peso);
      if (chartPeso) chartPeso.destroy();
      chartPeso = new Chart(document.getElementById('chartPeso').getContext('2d'), {
        type:'line',
        data:{ labels:fechas, datasets:[{ label:'Peso (kg)', data:pesos, borderColor:'#e63946', backgroundColor:'rgba(230,57,70,.1)', fill:true, tension:0.3, pointBackgroundColor:'#e63946' }] },
        options:{ responsive:true, maintainAspectRatio:false, legend:{ labels:{ fontColor:'#9a9a9a' } }, scales:{ xAxes:[{ ticks:{ fontColor:'#9a9a9a' }, gridLines:{ color:'#2a2a2a' } }], yAxes:[{ ticks:{ fontColor:'#9a9a9a' }, gridLines:{ color:'#2a2a2a' } }] } }
      });
    } else {
      document.getElementById('progPeso').textContent = '—';
      document.getElementById('progBrazo').textContent = '—';
      document.getElementById('progCintura').textContent = '—';
    }

    const hist = d.rutinas.slice(0, 5);
    document.getElementById('historialRutinas').innerHTML = hist.length
      ? hist.map(r => `<div class="mb-2">● <strong>${formatDate(r.asignada_en)}</strong><br><small>${r.entrenador_nombre}</small></div>`).join('')
      : 'Sin historial de rutinas';
  } catch(e) { toast(e.message, 'error'); }
}

async function cargarMensajes() {
  try {
    const r = await api('/mensajes/inbox');
    const msgs = r.data.mensajes;
    document.getElementById('inboxList').innerHTML = msgs.length
      ? msgs.map(m => `<div class="mb-2 p-2" style="border-bottom:1px solid var(--gf-border)"><strong>${m.de_nombre}</strong><p class="mb-0 small text-secondary">${m.contenido}</p><small class="text-muted">${formatDate(m.enviado_en)} ${m.leido ? '✓' : '○'}</small></div>`).join('')
      : '<p class="text-center py-4">No tienes mensajes</p>';
  } catch(e) { toast(e.message, 'error'); }
}
</script>
<?php \Gymfit\Core\View::endSection('scripts'); ?>

<div class="dash">
  <aside class="dash-side">
    <div class="brand"><a class="gf-logo" href="<?= \Gymfit\Core\View::asset('') ?>">GYM<span>FIT</span></a></div>
    <div class="user-mini">
      <img src="https://i.pravatar.cc/100?img=15" alt="">
      <div><small class="text-secondary d-block">Cliente</small><strong id="meName"><?= \Gymfit\Core\View::escape($user['nombre'] ?? '—') ?></strong></div>
    </div>
    <ul class="menu">
      <li><a class="active" href="#" data-section="rutina"><i class="bi bi-clipboard-check"></i> Mi rutina</a></li>
      <li><a href="#" data-section="progreso"><i class="bi bi-graph-up-arrow"></i> Progreso</a></li>
      <li><a href="#" data-section="mensajes"><i class="bi bi-chat-dots"></i> Mensajes</a></li>
    </ul>
    <div class="logout"><a href="#" onclick="logout();return false;"><i class="bi bi-box-arrow-left"></i> Cerrar sesión</a></div>
  </aside>

  <main class="dash-main">
    <div id="secRutina">
      <h3 class="fw-bold">Mi rutina</h3>
      <p class="text-secondary">Aquí puedes ver tu rutina asignada por tu entrenador</p>
      <div class="gf-card mb-3">
        <h5 class="fw-bold mb-3"><i class="bi bi-clipboard-check gf-red"></i> Rutina asignada</h5>
        <div id="rutinaBox" class="routine-block text-secondary">Cargando...</div>
      </div>
      <div class="gf-card">
        <h6 class="fw-bold mb-3"><i class="bi bi-chat-left-quote gf-red"></i> Observaciones de tu entrenador</h6>
        <div id="obsBox" class="observation-block text-light">—</div>
        <small class="text-secondary d-block mt-3" id="meta"></small>
      </div>
    </div>

    <div id="secProgreso" style="display:none">
      <h3 class="fw-bold">Mi evolución</h3>
      <p class="text-secondary">Seguimiento de tus mediciones corporales</p>
      <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="gf-card text-center"><h2 class="gf-red fw-bold" id="progPeso">—</h2><small class="text-secondary">Peso actual (kg)</small></div></div>
        <div class="col-md-3"><div class="gf-card text-center"><h2 class="gf-red fw-bold" id="progBrazo">—</h2><small class="text-secondary">Brazo (cm)</small></div></div>
        <div class="col-md-3"><div class="gf-card text-center"><h2 class="gf-red fw-bold" id="progCintura">—</h2><small class="text-secondary">Cintura (cm)</small></div></div>
        <div class="col-md-3"><div class="gf-card text-center"><h2 class="gf-red fw-bold" id="progRutinas">—</h2><small class="text-secondary">Rutinas totales</small></div></div>
      </div>
      <div class="row g-3">
        <div class="col-md-8"><div class="gf-card"><h6 class="fw-bold mb-3">Evolución de peso corporal</h6><canvas id="chartPeso" height="220"></canvas></div></div>
        <div class="col-md-4"><div class="gf-card"><h6 class="fw-bold mb-3">Historial de rutinas</h6><div id="historialRutinas" class="text-secondary small">Cargando...</div></div></div>
      </div>
    </div>

    <div id="secMensajes" style="display:none">
      <h3 class="fw-bold mb-3">Mensajes</h3>
      <div class="gf-card"><div id="inboxList" class="text-secondary">Cargando...</div></div>
    </div>
  </main>
</div>
