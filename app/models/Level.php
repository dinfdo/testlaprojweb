<?php
class Level extends Model {
    public function getAll(): array {
        return $this->db->query('SELECT * FROM levels ORDER BY difficulty')->fetchAll();
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare('SELECT * FROM levels WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
