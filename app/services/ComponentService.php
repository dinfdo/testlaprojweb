<?php
class ComponentService {
    private Component $componentModel;

    public function __construct() {
        $this->componentModel = new Component();
    }

    public function getAll(?int $difficulty = null): array {
        return $this->componentModel->getAll($difficulty);
    }

    public function count(): int {
        return $this->componentModel->count();
    }
}
