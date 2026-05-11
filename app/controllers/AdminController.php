<?php
class AdminController extends Controller {
    private AdminService $adminService;

    public function __construct() {
        $this->adminService = new AdminService();
    }

    public function index(): void {
        $admin  = $this->requireAdmin();
        $notice = $_GET['notice'] ?? null;

        $this->render('app/admin', [
            'title'      => 'Admin - LeG',
            'stats'      => $this->adminService->getStats(),
            'users'      => $this->adminService->getAllUsers(),
            'components' => $this->adminService->getAllComponents(),
            'currentId'  => $admin['id'],
            'notice'     => $notice,
        ]);
    }

    public function deleteUser(): void {
        $admin  = $this->requireAdmin();
        $target = (int)($_POST['user_id'] ?? 0);

        if ($target > 0 && $target !== $admin['id']) {
            $this->adminService->deleteUser($target);
        }

        $this->redirect('/admin?notice=deleted');
    }

    public function setRole(): void {
        $admin  = $this->requireAdmin();
        $target = (int)($_POST['user_id'] ?? 0);
        $role   = $_POST['role'] ?? '';

        if ($target > 0 && $target !== $admin['id'] && in_array($role, ['user', 'admin'], true)) {
            $this->adminService->setUserRole($target, $role);
        }

        $this->redirect('/admin?notice=role_updated');
    }
}
