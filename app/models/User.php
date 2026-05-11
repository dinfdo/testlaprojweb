<?php
class User extends Model {
    public function findByIdentifier(string $identifier): array|false {
        $stmt = $this->db->prepare(
            'SELECT id, username, email, password_hash, role FROM users WHERE username = ? OR email = ?'
        );
        $stmt->execute([$identifier, $identifier]);
        return $stmt->fetch();
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare(
            'SELECT id, username, email, role, created_at FROM users WHERE id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function existsByUsernameOrEmail(string $username, string $email): bool {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        return (bool)$stmt->fetch();
    }

    public function create(string $username, string $email, string $passwordHash): int {
        $stmt = $this->db->prepare(
            'INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)'
        );
        $stmt->execute([$username, $email, $passwordHash]);
        return (int)$this->db->lastInsertId();
    }

    public function getAll(): array {
        return $this->db->query(
            'SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC'
        )->fetchAll();
    }

    public function update(int $id, string $username, string $email): void {
        $stmt = $this->db->prepare('UPDATE users SET username = ?, email = ? WHERE id = ?');
        $stmt->execute([$username, $email, $id]);
    }

    public function updatePassword(int $id, string $hash): void {
        $stmt = $this->db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $stmt->execute([$hash, $id]);
    }

    public function setRole(int $id, string $role): void {
        $stmt = $this->db->prepare('UPDATE users SET role = ? WHERE id = ?');
        $stmt->execute([$role, $id]);
    }

    public function delete(int $id): void {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function count(): int {
        return (int)$this->db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }
}
