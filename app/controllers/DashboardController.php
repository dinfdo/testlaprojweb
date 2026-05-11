<?php
class DashboardController extends Controller {
    private DashboardService $dashboardService;

    public function __construct() {
        $this->dashboardService = new DashboardService();
    }

    public function index(): void {
        $user = $this->requireAuth();
        $data = $this->dashboardService->getProgressData($user['id']);

        $this->render('app/dashboard', [
            'title' => 'Dashboard - LeG',
            'user'  => $user,
            'data'  => $data,
        ]);
    }
}
