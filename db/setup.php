<?php

/**
 * GYMFIT - Setup de base de datos SQLite
 * Crea las tablas y datos de prueba.
 *
 * Uso: php db/setup.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Gymfit\Database;

$db = Database::getConnection();

echo "Creando tablas...\n";

$db->exec("
    CREATE TABLE IF NOT EXISTS usuarios (
        id              INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre          TEXT NOT NULL,
        email           TEXT NOT NULL UNIQUE,
        password_hash   TEXT NOT NULL,
        rol             TEXT NOT NULL CHECK(rol IN ('entrenador','cliente')),
        avatar_url      TEXT,
        edad            INTEGER,
        objetivo        TEXT,
        nivel           TEXT,
        creado_en       DATETIME NOT NULL DEFAULT (datetime('now'))
    )
");

$db->exec("
    CREATE TABLE IF NOT EXISTS entrenador_cliente (
        id              INTEGER PRIMARY KEY AUTOINCREMENT,
        entrenador_id   INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
        cliente_id      INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
        creado_en       DATETIME NOT NULL DEFAULT (datetime('now')),
        UNIQUE(entrenador_id, cliente_id)
    )
");

$db->exec("
    CREATE TABLE IF NOT EXISTS rutinas (
        id              INTEGER PRIMARY KEY AUTOINCREMENT,
        cliente_id      INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
        entrenador_id   INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
        contenido       TEXT NOT NULL,
        observaciones   TEXT,
        asignada_en     DATETIME NOT NULL DEFAULT (datetime('now')),
        actualizada_en  DATETIME NOT NULL DEFAULT (datetime('now'))
    )
");

$db->exec("
    CREATE TABLE IF NOT EXISTS mensajes (
        id              INTEGER PRIMARY KEY AUTOINCREMENT,
        de_usuario_id   INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
        para_usuario_id INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
        contenido       TEXT NOT NULL,
        leido           INTEGER NOT NULL DEFAULT 0,
        enviado_en      DATETIME NOT NULL DEFAULT (datetime('now'))
    )
");

$db->exec("
    CREATE TABLE IF NOT EXISTS contactos (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre      TEXT NOT NULL,
        email       TEXT NOT NULL,
        mensaje     TEXT NOT NULL,
        enviado_en  DATETIME NOT NULL DEFAULT (datetime('now'))
    )
");

$db->exec("
    CREATE TABLE IF NOT EXISTS progreso (
        id              INTEGER PRIMARY KEY AUTOINCREMENT,
        cliente_id      INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
        peso            REAL,
        altura          REAL,
        brazo           REAL,
        cintura         REAL,
        pierna          REAL,
        notas           TEXT,
        registrado_en   DATETIME NOT NULL DEFAULT (datetime('now'))
    )
");

echo "Insertando datos demo...\n";

// Usuarios demo
$entrenadorHash = password_hash('123456', PASSWORD_BCRYPT);
$clienteHash = password_hash('123456', PASSWORD_BCRYPT);

$db->exec("
    INSERT OR IGNORE INTO usuarios (nombre, email, password_hash, rol, edad, objetivo, nivel)
    VALUES ('Juan Martínez', 'entrenador@gymfit.com', '{$entrenadorHash}', 'entrenador', 32, 'Formar campeones', 'Profesional')
");

$db->exec("
    INSERT OR IGNORE INTO usuarios (nombre, email, password_hash, rol, edad, objetivo, nivel)
    VALUES ('Juan Pérez', 'juanperez@gmail.com', '{$clienteHash}', 'cliente', 28, 'Ganar masa muscular', 'Intermedio')
");

$db->exec("
    INSERT OR IGNORE INTO usuarios (nombre, email, password_hash, rol, edad, objetivo, nivel)
    VALUES ('Ana Gómez', 'anagomez@gmail.com', '{$clienteHash}', 'cliente', 25, 'Perder grasa', 'Principiante')
");

$db->exec("
    INSERT OR IGNORE INTO usuarios (nombre, email, password_hash, rol, edad, objetivo, nivel)
    VALUES ('Carlos Rodríguez', 'carlosrod@gmail.com', '{$clienteHash}', 'cliente', 34, 'Tonificar', 'Intermedio')
");

$db->exec("
    INSERT OR IGNORE INTO usuarios (nombre, email, password_hash, rol, edad, objetivo, nivel)
    VALUES ('María López', 'marialopez@gmail.com', '{$clienteHash}', 'cliente', 29, 'Resistencia', 'Avanzado')
");

// Asignar clientes al entrenador
$db->exec("
    INSERT OR IGNORE INTO entrenador_cliente (entrenador_id, cliente_id)
    SELECT
        (SELECT id FROM usuarios WHERE email='entrenador@gymfit.com'),
        u.id
    FROM usuarios u
    WHERE u.rol = 'cliente'
");

// Rutina demo
$db->exec("
    INSERT OR IGNORE INTO rutinas (cliente_id, entrenador_id, contenido, observaciones, asignada_en)
    SELECT
        (SELECT id FROM usuarios WHERE email='juanperez@gmail.com'),
        (SELECT id FROM usuarios WHERE email='entrenador@gymfit.com'),
        'Día 1 - Pecho y tríceps\n- Press banca 4x10\n- Aperturas con mancuernas 3x12\n- Fondos en paralelas 3x10\n- Extensión de tríceps 3x12\n\nDía 2 - Espalda y bíceps\n- Dominadas 4x8\n- Remo con barra 4x10\n- Curl de bíceps 3x12\n- Curl martillo 3x12',
        'Recuerda mantener una buena técnica.\nSube el peso progresivamente cada semana.\nDescansa 60-90 segundos entre series.',
        datetime('now', '-30 days')
    WHERE NOT EXISTS (SELECT 1 FROM rutinas WHERE cliente_id = (SELECT id FROM usuarios WHERE email='juanperez@gmail.com'))
");

// Rutinas históricas para datos de reportes
$db->exec("
    INSERT INTO rutinas (cliente_id, entrenador_id, contenido, observaciones, asignada_en)
    SELECT
        (SELECT id FROM usuarios WHERE email='juanperez@gmail.com'),
        (SELECT id FROM usuarios WHERE email='entrenador@gymfit.com'),
        'Rutina inicial de evaluación',
        'Evaluación física completa',
        datetime('now', '-90 days')
    WHERE (SELECT COUNT(*) FROM rutinas WHERE cliente_id = (SELECT id FROM usuarios WHERE email='juanperez@gmail.com')) = 1
");

$db->exec("
    INSERT INTO rutinas (cliente_id, entrenador_id, contenido, observaciones, asignada_en)
    SELECT
        (SELECT id FROM usuarios WHERE email='juanperez@gmail.com'),
        (SELECT id FROM usuarios WHERE email='entrenador@gymfit.com'),
        'Rutina intermedia - mes 1',
        'Aumentar carga progresivamente',
        datetime('now', '-60 days')
    WHERE (SELECT COUNT(*) FROM rutinas WHERE cliente_id = (SELECT id FROM usuarios WHERE email='juanperez@gmail.com')) BETWEEN 1 AND 2
");

// Progreso demo
$db->exec("
    INSERT INTO progreso (cliente_id, peso, altura, brazo, cintura, pierna, notas, registrado_en)
    SELECT
        (SELECT id FROM usuarios WHERE email='juanperez@gmail.com'),
        82.5, 178, 36.5, 82, 55, 'Inicio del plan',
        datetime('now', '-60 days')
    WHERE NOT EXISTS (SELECT 1 FROM progreso)
");

$db->exec("
    INSERT INTO progreso (cliente_id, peso, altura, brazo, cintura, pierna, notas, registrado_en)
    SELECT
        (SELECT id FROM usuarios WHERE email='juanperez@gmail.com'),
        80.2, 178, 37.1, 80, 55.5, 'Primer mes: buena evolución',
        datetime('now', '-30 days')
    WHERE (SELECT COUNT(*) FROM progreso) = 1
");

$db->exec("
    INSERT INTO progreso (cliente_id, peso, altura, brazo, cintura, pierna, notas, registrado_en)
    SELECT
        (SELECT id FROM usuarios WHERE email='juanperez@gmail.com'),
        78.0, 178, 37.8, 78, 56.2, 'Segundo mes: resultados visibles',
        datetime('now')
    WHERE (SELECT COUNT(*) FROM progreso) = 2
");

echo "¡Base de datos lista!\n";
