<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= \Gymfit\Core\View::escape($title ?? 'GYMFIT - Entrena duro · Vive fuerte') ?></title>
  <meta name="description" content="GYMFIT, tu gimnasio ideal. Entrenamiento personal, clases grupales, musculación y nutrición.">
  <?= \Gymfit\Core\View::csrfMeta() ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <?= \Gymfit\Core\View::yield('styles') ?>
  <link rel="stylesheet" href="<?= \Gymfit\Core\View::asset('css/styles.css') ?>">
</head>
<body>
<?= $GLOBALS['_view_content'] ?? '' ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= \Gymfit\Core\View::asset('js/app.js') ?>"></script>
<?= \Gymfit\Core\View::yield('scripts') ?>
</body>
</html>
