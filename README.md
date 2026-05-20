# GYMFIT — Plataforma completa (PHP MVC + Bootstrap 5 + JS + Chart.js)

Sitio web público + plataforma privada con dos roles (Entrenador / Cliente),
refactorizada a **Arquitectura MVC** con principios **SOLID**, seguridad **OWASP**,
informes con **Chart.js** y tests **PHPUnit**.

## 📁 Estructura MVC

```
gymfit/
├── config/
│   ├── app.php                 # Config general (CORS, password policy, etc.)
│   ├── database.php            # Conexión PDO (SQLite / PostgreSQL)
│   └── router.php              # Router para servidor embebido
│
├── public/
│   └── index.php               # Front controller (rutas API + páginas)
│
├── src/
│   ├── Core/
│   │   └── View.php            # Motor de vistas con layouts + secciones
│   │
│   ├── Views/
│   │   ├── layouts/default.php # Layout principal (Bootstrap + Chart.js)
│   │   ├── landing/index.php   # Landing pública
│   │   ├── auth/login.php      # Inicio de sesión
│   │   ├── auth/registro.php   # Registro
│   │   ├── auth/seleccionar-rol.php
│   │   ├── entrenador/panel.php# Dashboard + Reportes con gráficos
│   │   ├── entrenador/asignar-rutina.php
│   │   └── cliente/panel.php   # Rutina + Progreso con evolución
│   │
│   ├── Controllers/            # 8 controladores con DI
│   ├── Models/                 # 5 modelos (Usuario, Rutina, etc.)
│   ├── Repositories/           # 5 repositorios (capa de datos)
│   ├── Services/               # 4 servicios (Auth, Security, Reporte, Mensaje)
│   ├── Middleware/             # 3 (Security OWASP, Auth, RateLimit)
│   ├── Helpers/                # 3 (JsonHelper, ValidatorHelper, SessionHelper)
│   ├── Exceptions/             # 5 excepciones custom
│   └── Logger/                 # Logging por niveles
│
├── HTML/                       # Archivos HTML legacy (referencia)
├── css/styles.css              # Diseño original (Bootstrap 5 dark theme)
├── js/app.js                   # API client con CSRF
├── sql/schema.sql              # Esquema PostgreSQL original
├── db/setup.php                # Inicializa DB SQLite con datos demo
├── tests/                      # Tests PHPUnit (27 tests)
├── composer.json               # PSR-4 autoloading
└── phpunit.xml
```

## ⚙️ Requisitos

- PHP 8.1+
- Extensiones: `pdo_sqlite` (dev) o `pdo_pgsql` (prod)
- Composer (para autoloading)

## 🚀 Instalación y ejecución

```bash
# 1. Instalar dependencias
composer install

# 2. Inicializar base de datos SQLite con datos demo
php db/setup.php

# 3. Iniciar servidor
php -S localhost:8000 config/router.php
```

Abrir [http://localhost:8000](http://localhost:8000)

### Usuarios demo

| Rol         | Email                   | Contraseña |
|-------------|-------------------------|------------|
| Entrenador  | entrenador@gymfit.com   | 123456     |
| Cliente     | juanperez@gmail.com     | 123456     |
| Cliente     | anagomez@gmail.com      | 123456     |

### Producción con PostgreSQL

Editar `config/database.php`:
```php
'driver' => 'pgsql',
'host' => '127.0.0.1',
'port' => 5432,
'dbname' => 'gymfit',
'user' => 'postgres',
'password' => 'tu_password',
```

Ejecutar `sql/schema.sql` en pgAdmin, luego `php db/setup.php`.

## 🧭 Rutas del sistema

### Páginas (MVC Views)

| Ruta                    | Descripción                          |
|-------------------------|--------------------------------------|
| `/`                     | Landing pública                      |
| `/login`                | Inicio de sesión                     |
| `/registro`             | Registro de usuario                  |
| `/panel-entrenador`     | Dashboard entrenador + reportes      |
| `/panel-cliente`        | Panel cliente + progreso             |
| `/asignar-rutina`       | Editor de rutina por cliente         |

### API REST (JSON)

| Endpoint                          | Método | Auth     | Descripción                     |
|-----------------------------------|--------|----------|---------------------------------|
| `/api/auth/login`                 | POST   | No       | Iniciar sesión                  |
| `/api/auth/register`              | POST   | No       | Registrarse                     |
| `/api/auth/logout`                | POST   | Sí       | Cerrar sesión                   |
| `/api/auth/me`                    | GET    | No       | Usuario actual                  |
| `/api/clientes`                   | GET    | Entr.    | Lista de clientes               |
| `/api/clientes`                   | POST   | Entr.    | Asignar cliente por email       |
| `/api/rutinas`                    | GET    | Sí       | Última rutina (`?cliente_id=N`) |
| `/api/rutinas`                    | POST   | Entr.    | Guardar rutina                  |
| `/api/contacto`                   | POST   | No       | Formulario de contacto          |
| `/api/reportes/trainer-dashboard` | GET    | Entr.    | Dashboard con KPIs + gráficos   |
| `/api/reportes/client-progress`   | GET    | Sí       | Progreso del cliente            |
| `/api/mensajes/inbox`             | GET    | Sí       | Bandeja de mensajes             |

## 📊 Informes (2)

1. **Dashboard Entrenador**: KPIs (clientes, rutinas), gráfico barras rutinas/mes, doughnut distribución niveles, pie objetivos, lista actividad reciente
2. **Progreso Cliente**: KPIs medidas corporales, gráfico línea evolución peso, historial de rutinas

## 🔐 Seguridad (OWASP)

- **CSP**: Content-Security-Policy estricta
- **HSTS**: Strict-Transport-Security
- **CSRF**: Token por sesión validado en cada request POST/PUT/DELETE
- **XSS**: `htmlspecialchars()` + `strip_tags()` en toda salida
- **Rate Limiting**: 60 requests/minuto por IP
- **Password Policy**: 8+ caracteres, mayúscula, número, especial
- **Session**: regeneración de ID post-login, httponly, samesite Strict
- **Headers**: X-Frame-Options, X-Content-Type-Options, Referrer-Policy

## 🧪 Tests

```bash
php vendor/bin/phpunit
```

- 27 tests (Unit: ValidatorHelper, SecurityService, Models — Integration: Auth)
- SQLite in-memory para tests

## 🎨 Personalización

Toda la paleta vive en `css/styles.css` bajo `:root` (rojo `#e63946`, fondo `#0e0e0e`).
