<?php
declare(strict_types=1);

namespace LeG\Models;

use LeG\Core\Database;

class User
{
    public static function findByUsername(string $username): ?array
    {
        $st = Database::pdo()->prepare(
            'SELECT id, username, email, password_hash, profile_icon, is_admin, created_at
             FROM users WHERE username = ?'
        );
        $st->execute([$username]);
        $r = $st->fetch();
        return $r ?: null;
    }

    public static function find(int $id): ?array
    {
        $st = Database::pdo()->prepare(
            'SELECT id, username, email, profile_icon, is_admin, created_at FROM users WHERE id = ?'
        );
        $st->execute([$id]);
        $r = $st->fetch();
        return $r ?: null;
    }

    public static function create(string $username, string $email, string $passwordHash, string $icon): int
    {
        $db = Database::pdo();
        $st = $db->prepare(
            'INSERT INTO users (username, email, password_hash, profile_icon) VALUES (?,?,?,?)'
        );
        $st->execute([$username, $email, $passwordHash, $icon]);
        return (int)$db->lastInsertId();
    }

    public static function updateIcon(int $id, string $icon): void
    {
        $st = Database::pdo()->prepare('UPDATE users SET profile_icon = ? WHERE id = ?');
        $st->execute([$icon, $id]);
    }

    public static function all(): array
    {
        return Database::pdo()->query(
            'SELECT id, username, email, profile_icon, is_admin, created_at FROM users ORDER BY id'
        )->fetchAll();
    }

    public static function delete(int $id): void
    {
        $st = Database::pdo()->prepare('DELETE FROM users WHERE id = ?');
        $st->execute([$id]);
    }
}
