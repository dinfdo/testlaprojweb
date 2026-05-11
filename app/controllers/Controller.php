<?php
abstract class Controller {
    protected function render(string $template, array $data = []): void {
        $data['base'] = $this->baseUrl();
        View::render($template, $data);
    }

    protected function redirect(string $path): void {
        header('Location: ' . $this->baseUrl() . $path);
        exit;
    }

    protected function baseUrl(): string {
        return rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    }

    protected function currentUser(): array|null {
        if (empty($_SESSION['user_id'])) return null;
        return [
            'id'       => (int)$_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'role'     => $_SESSION['role'],
        ];
    }

    protected function requireAuth(): array {
        $user = $this->currentUser();
        if (!$user) $this->redirect('/login');
        return $user;
    }

    protected function requireAdmin(): array {
        $user = $this->requireAuth();
        if ($user['role'] !== 'admin') $this->redirect('/dashboard');
        return $user;
    }
}
