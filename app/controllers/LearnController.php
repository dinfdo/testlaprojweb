<?php
class LearnController extends Controller {
    private ComponentService $componentService;

    public function __construct() {
        $this->componentService = new ComponentService();
    }

    public function index(): void {
        $difficulty = isset($_GET['difficulty']) ? (int)$_GET['difficulty'] : null;
        $components = $this->componentService->getAll($difficulty);

        $this->render('app/learn', [
            'title'           => 'Invata - LeG',
            'components'      => $components,
            'activeDifficulty' => $difficulty,
        ]);
    }
}
