<?php

declare(strict_types=1);

namespace Gymfit;

use PDO;

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            if (getenv('APP_ENV') === 'testing') {
                self::$instance = self::createMemoryDb();
            } else {
                $config = require __DIR__ . '/../config/database.php';

                if ($config['driver'] === 'sqlite') {
                    $dir = dirname($config['path']);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0775, true);
                    }
                    self::$instance = new PDO('sqlite:' . $config['path'], null, null, $config['options']);
                    self::$instance->exec('PRAGMA journal_mode=WAL');
                } else {
                    $dsn = sprintf(
                        '%s:host=%s;port=%s;dbname=%s',
                        $config['driver'],
                        $config['host'],
                        $config['port'],
                        $config['dbname']
                    );
                    self::$instance = new PDO($dsn, $config['user'], $config['password'], $config['options']);
                }
            }
            self::$instance->exec('PRAGMA foreign_keys=ON');
        }
        return self::$instance;
    }

    private static function createMemoryDb(): PDO
    {
        $pdo = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $pdo->exec('PRAGMA foreign_keys=ON');

        // Create schema in memory
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS usuarios (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                password_hash TEXT NOT NULL,
                rol TEXT NOT NULL CHECK(rol IN ('entrenador','cliente')),
                avatar_url TEXT,
                edad INTEGER,
                objetivo TEXT,
                nivel TEXT,
                creado_en DATETIME NOT NULL DEFAULT (datetime('now'))
            )
        ");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS entrenador_cliente (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                entrenador_id INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
                cliente_id INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
                creado_en DATETIME NOT NULL DEFAULT (datetime('now')),
                UNIQUE(entrenador_id, cliente_id)
            )
        ");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS rutinas (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                cliente_id INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
                entrenador_id INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
                contenido TEXT NOT NULL,
                observaciones TEXT,
                asignada_en DATETIME NOT NULL DEFAULT (datetime('now')),
                actualizada_en DATETIME NOT NULL DEFAULT (datetime('now'))
            )
        ");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS mensajes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                de_usuario_id INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
                para_usuario_id INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
                contenido TEXT NOT NULL,
                leido INTEGER NOT NULL DEFAULT 0,
                enviado_en DATETIME NOT NULL DEFAULT (datetime('now'))
            )
        ");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS contactos (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre TEXT NOT NULL,
                email TEXT NOT NULL,
                mensaje TEXT NOT NULL,
                enviado_en DATETIME NOT NULL DEFAULT (datetime('now'))
            )
        ");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS progreso (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                cliente_id INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
                peso REAL,
                altura REAL,
                brazo REAL,
                cintura REAL,
                pierna REAL,
                notas TEXT,
                registrado_en DATETIME NOT NULL DEFAULT (datetime('now'))
            )
        ");
        return $pdo;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}
