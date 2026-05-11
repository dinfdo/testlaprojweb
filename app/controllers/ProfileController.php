<?php
class ProfileController extends Controller {
    private UserService $userService;

    public function __construct() {
        $this->userService = new UserService();
    }

    public function index(): void {
        $session = $this->requireAuth();
        $user    = $this->userService->getById($session['id']);

        $this->render('app/profile', [
            'title'   => 'Profil - LeG',
            'user'    => $user,
            'error'   => null,
            'success' => null,
        ]);
    }

    public function update(): void {
        $session     = $this->requireAuth();
        $username    = trim($_POST['username'] ?? '');
        $email       = trim($_POST['email'] ?? '');
        $currentPass = $_POST['current_password'] ?? '';
        $newPass     = $_POST['new_password'] ?? '';

        $result = $this->userService->updateProfile($session['id'], $username, $email, $currentPass, $newPass);
        $user   = $this->userService->getById($session['id']);

        if ($result !== true) {
            $this->render('app/profile', [
                'title'   => 'Profil - LeG',
                'user'    => $user,
                'error'   => $result,
                'success' => null,
            ]);
            return;
        }

        $_SESSION['username'] = $username;

        $this->render('app/profile', [
            'title'   => 'Profil - LeG',
            'user'    => $user,
            'error'   => null,
            'success' => 'Profilul a fost actualizat.',
        ]);
    }

    public function deleteAccount(): void {
        $session  = $this->requireAuth();
        $password = $_POST['confirm_password'] ?? '';

        $user = $this->userService->getById($session['id']);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->render('app/profile', [
                'title'   => 'Profil - LeG',
                'user'    => $user,
                'error'   => 'Parola incorecta. Contul nu a fost sters.',
                'success' => null,
            ]);
            return;
        }

        $this->userService->deleteById($session['id']);
        session_destroy();
        $this->redirect('/');
    }
}
