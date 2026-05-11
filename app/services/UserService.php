<?php
class UserService {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function authenticate(string $identifier, string $password): array|false {
        $user = $this->userModel->findByIdentifier($identifier);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }
        return $user;
    }

    public function register(string $username, string $email, string $password): array|string {
        if (!$username || !$email || !$password) {
            return 'Toate campurile sunt obligatorii.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Adresa de email este invalida.';
        }
        if (strlen($password) < 6) {
            return 'Parola trebuie sa aiba cel putin 6 caractere.';
        }
        if ($this->userModel->existsByUsernameOrEmail($username, $email)) {
            return 'Username sau email deja folosit.';
        }
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $id   = $this->userModel->create($username, $email, $hash);
        return ['id' => $id, 'username' => $username, 'role' => 'user'];
    }

    public function updateProfile(int $id, string $username, string $email, string $currentPassword, string $newPassword): string|true {
        $user = $this->userModel->findById($id);
        if (!$user) return 'Utilizatorul nu exista.';

        if (!$username || !$email) return 'Username si email sunt obligatorii.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return 'Email invalid.';

        $this->userModel->update($id, $username, $email);

        if ($newPassword !== '') {
            if (!password_verify($currentPassword, $user['password_hash'])) {
                return 'Parola curenta este incorecta.';
            }
            if (strlen($newPassword) < 6) return 'Parola noua trebuie sa aiba cel putin 6 caractere.';
            $this->userModel->updatePassword($id, password_hash($newPassword, PASSWORD_BCRYPT));
        }

        return true;
    }

    public function getById(int $id): array|false {
        return $this->userModel->findById($id);
    }

    public function deleteById(int $id): void {
        $this->userModel->delete($id);
    }

    public function getAll(): array {
        return $this->userModel->getAll();
    }

    public function count(): int {
        return $this->userModel->count();
    }
}
