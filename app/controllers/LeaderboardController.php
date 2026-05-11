<?php
class LeaderboardController extends Controller {
    private LeaderboardService $leaderboardService;

    public function __construct() {
        $this->leaderboardService = new LeaderboardService();
    }

    public function index(): void {
        $entries = $this->leaderboardService->getTopUsers(20);

        $this->render('app/leaderboard', [
            'title'   => 'Clasament - LeG',
            'entries' => $entries,
        ]);
    }
}
