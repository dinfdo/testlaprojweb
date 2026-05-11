<?php
class HomeController extends Controller {
    public function index(): void {
        if ($this->currentUser()) {
            $this->redirect('/dashboard');
        }
        $this->render('home/index', ['title' => 'LeG - Learning by Playing']);
    }
}
