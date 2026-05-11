<?php
class AuthController extends Controller {
    private UserService $userService;

    public function __construct() {
        $this->userService = new UserService();
    }

    public function loginPage(): void {
        if ($this->currentUser()) $this->redirect('/dashboard');
        $this->render('auth/login', ['title' => 'Login - LeG', 'error' => null]);
    }

    public function registerPage(): void {
        if ($this->currentUser()) $this->redirect('/dashboard');
        $this->render('auth/register', ['title' => 'Inregistrare - LeG', 'error' => null]);
    }

    public function doLogin(): void {
        $identifier = trim($_POST['identifier'] ?? '');
        $password   = $_POST['password'] ?? '';

        if (!$identifier || !$password) {
            $this->render('auth/login', [
                'title' => 'Login - LeG',
                'error' => 'Completati toate campurile.',
            ]);
            return;
        }

        $user = $this->userService->authenticate($identifier, $password);
        if (!$user) {
            $this->render('auth/login', [
                'title' => 'Login - LeG',
                'error' => 'Date de autentificare incorecte.',
            ]);
            return;
        }

        session_regenerate_id(true);
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];

        $this->redirect('/dashboard');
    }

    public function doRegister(): void {
        $username = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $result = $this->userService->register($username, $email, $password);

        if (is_string($result)) {
            $this->render('auth/register', [
                'title' => 'Inregistrare - LeG',
                'error' => $result,
            ]);
            return;
        }

        session_regenerate_id(true);
        $_SESSION['user_id']  = $result['id'];
        $_SESSION['username'] = $result['username'];
        $_SESSION['role']     = $result['role'];

        $this->redirect('/dashboard');
    }

    public function logout(): void {
        session_destroy();
        $this->redirect('/');
    }
}
