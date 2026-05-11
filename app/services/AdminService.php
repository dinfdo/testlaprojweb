<?php
class AdminService {
    private UserService      $userService;
    private ComponentService $componentService;
    private GameSession      $sessionModel;

    public function __construct() {
        $this->userService       = new UserService();
        $this->componentService  = new ComponentService();
        $this->sessionModel      = new GameSession();
    }

    public function getStats(): array {
        return [
            'total_users'       => $this->userService->count(),
            'total_components'  => $this->componentService->count(),
            'total_sessions'    => $this->sessionModel->count(),
            'avg_score'         => $this->sessionModel->averageScore(),
        ];
    }

    public function getAllUsers(): array {
        return $this->userService->getAll();
    }

    public function getAllComponents(): array {
        return $this->componentService->getAll();
    }

    public function deleteUser(int $id): void {
        (new User())->delete($id);
    }

    public function setUserRole(int $id, string $role): void {
        (new User())->setRole($id, $role);
    }
}
