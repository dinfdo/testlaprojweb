<?php
class Component extends Model {
    public function getAll(?int $difficulty = null): array {
        if ($difficulty !== null) {
            $stmt = $this->db->prepare(
                'SELECT c.*, d.name AS device_name
                 FROM components c
                 JOIN devices d ON c.device_id = d.id
                 WHERE c.difficulty_level = ?
                 ORDER BY c.difficulty_level, c.name'
            );
            $stmt->execute([$difficulty]);
        } else {
            $stmt = $this->db->query(
                'SELECT c.*, d.name AS device_name
                 FROM components c
                 JOIN devices d ON c.device_id = d.id
                 ORDER BY c.difficulty_level, c.name'
            );
        }
        return $stmt->fetchAll();
    }

    public function count(): int {
        return (int)$this->db->query('SELECT COUNT(*) FROM components')->fetchColumn();
    }
}
