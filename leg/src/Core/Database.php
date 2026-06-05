<?php
declare(strict_types=1);

namespace LeG\Core;

use PDO;

class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $envPath = getenv('SQLITE_PATH');
            if ($envPath !== false && $envPath !== '') {
                if (!str_starts_with($envPath, '/') && !preg_match('/^[A-Za-z]:[\\/]/', $envPath)) {
                    $envPath = __DIR__ . '/../../' . ltrim($envPath, './');
                }
                $path = $envPath;
            } else {
                $path = __DIR__ . '/../../data/leg.sqlite';
            }
            if (!is_dir(dirname($path))) {
                @mkdir(dirname($path), 0777, true);
            }
            self::$pdo = new PDO('sqlite:' . $path);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            self::$pdo->exec('PRAGMA journal_mode=WAL');
            self::$pdo->exec('PRAGMA busy_timeout=5000');
            self::$pdo->exec('PRAGMA foreign_keys=ON');
        }
        return self::$pdo;
    }

    public static function init(): void
    {
        $db = self::pdo();

        $db->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            email TEXT,
            password_hash TEXT NOT NULL,
            profile_icon TEXT DEFAULT 'astronaut.jpg',
            is_admin INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL DEFAULT (datetime('now'))
        )");

        $db->exec("CREATE TABLE IF NOT EXISTS components (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            slug TEXT NOT NULL UNIQUE,
            name TEXT NOT NULL,
            category TEXT NOT NULL,
            short_desc TEXT NOT NULL,
            description TEXT NOT NULL,
            icon TEXT,
            specs TEXT,
            created_at TEXT NOT NULL DEFAULT (datetime('now'))
        )");

        $db->exec("CREATE TABLE IF NOT EXISTS games (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            slug TEXT NOT NULL UNIQUE,
            name TEXT NOT NULL,
            kind TEXT NOT NULL,
            description TEXT NOT NULL,
            icon TEXT,
            max_difficulty INTEGER NOT NULL DEFAULT 3
        )");

        $db->exec("CREATE TABLE IF NOT EXISTS scores (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            game_id INTEGER NOT NULL,
            score INTEGER NOT NULL,
            time_seconds INTEGER NOT NULL,
            difficulty INTEGER NOT NULL DEFAULT 1,
            played_at TEXT NOT NULL DEFAULT (datetime('now')),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
        )");

        $db->exec("CREATE TABLE IF NOT EXISTS user_progress (
            user_id INTEGER NOT NULL,
            game_id INTEGER NOT NULL,
            best_score INTEGER NOT NULL DEFAULT 0,
            best_difficulty INTEGER NOT NULL DEFAULT 0,
            plays INTEGER NOT NULL DEFAULT 0,
            last_played TEXT,
            PRIMARY KEY (user_id, game_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
        )");

        self::seed();
    }

    private static function seed(): void
    {
        $db    = self::pdo();
        $admin = getenv('ADMIN_USERNAME') ?: 'admin';
        $comps = require __DIR__ . '/../../data/components_seed.php';
        $games = [
            ['assemble-pc',      'Asamblare PC',              'drag_drop',
             'Trage fiecare componentă în slotul corect al plăcii de bază.', 'tools.png', 3],
            ['identify-hw',      'Identifică Hardware',       'identify',
             'Citește descrierea și alege componenta corectă.', 'search.png', 3],
            ['match-specs',      'Potrivește Specificațiile', 'match',
             'Asociază fiecare specificație cu componenta din care face parte.', 'spreadsheet_program.png', 3],
            ['cable-manager',    'Cable Manager',             'cables',
             'Conectează cablurile PSU-ului la componentele potrivite.', 'briefcase.png', 3],
            ['mini-quiz',        'Provocarea Finală',         'mini_quiz',
             'Mini-joc bonus: răspunde la întrebări într-un timp limită.', 'minecraft.png', 3],
            ['history-timeline', 'Linia Timpului',            'timeline',
             'Aranjează evenimentele din istoria calculatoarelor în ordine cronologică.', 'webpage_file.png', 3],
        ];

        $st = $db->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
        $st->execute([$admin]);
        $needsAdmin = (int)$st->fetchColumn() === 0;

        $existingGameSlugs = $db->query('SELECT slug FROM games')->fetchAll(PDO::FETCH_COLUMN);
        $missingGames = array_values(array_filter($games, fn($g) => !in_array($g[0], $existingGameSlugs)));

        $existingCompSlugs = $db->query('SELECT slug FROM components')->fetchAll(PDO::FETCH_COLUMN);
        $missingComps = array_values(array_filter($comps, fn($c) => !in_array($c['slug'], $existingCompSlugs)));

        if (!$needsAdmin && !$missingGames && !$missingComps) return;

        if ($needsAdmin) {
            $pass = getenv('ADMIN_PASSWORD') ?: 'admin123';
            $db->prepare(
                'INSERT OR IGNORE INTO users (username, email, password_hash, profile_icon, is_admin)
                 VALUES (?, ?, ?, ?, 1)'
            )->execute([$admin, 'admin@leg.local',
                         password_hash($pass, PASSWORD_BCRYPT), 'astronaut.jpg']);
        }

        if ($missingGames) {
            $ins = $db->prepare(
                'INSERT OR IGNORE INTO games (slug, name, kind, description, icon, max_difficulty)
                 VALUES (?,?,?,?,?,?)'
            );
            foreach ($missingGames as $g) $ins->execute($g);
        }

        if ($missingComps) {
            $ins = $db->prepare(
                'INSERT OR IGNORE INTO components (slug, name, category, short_desc, description, icon, specs)
                 VALUES (?,?,?,?,?,?,?)'
            );
            foreach ($missingComps as $c) {
                $ins->execute([
                    $c['slug'], $c['name'], $c['category'],
                    $c['short_desc'], $c['description'], $c['icon'],
                    json_encode($c['specs'], JSON_UNESCAPED_UNICODE),
                ]);
            }
        }
    }
}
