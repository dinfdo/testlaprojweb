<?php
class GameController extends Controller {
    public function index(): void {
        $this->requireAuth();
        $this->render('app/game', ['title' => 'Joc - LeG']);
    }
}
