<?php
/**
 * Location: vetapp/app/controllers/auth/AuthController.php
 * Responsibility:
 * - Manejar login y logout
 * - NO SQL
 * - NO config
 * - NO session_start
 */

declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap.php';

class AuthController
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $db = Database::getInstance()->getConnection();
        $this->userRepository = new UserRepository($db);
    }

    // =====================================================
    // LOGIN
    // =====================================================
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToLogin();
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $_SESSION['error'] = 'Email y contraseña son obligatorios';
            $this->redirectToLogin();
        }

        // Buscar usuario
        $user = $this->userRepository->findByEmailWithRoles($email);

        if (!$user) {
            $_SESSION['error'] = 'Usuario no encontrado';
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        }



        if (!$user['active']) {
            $_SESSION['error'] = 'Usuario inactivo';
            $this->redirectToLogin();
        }

        if (!password_verify($password, $user['password'])) {
            $_SESSION['error'] = 'Credenciales inválidas';
            $this->redirectToLogin();
        }

        // ================= LOGIN OK =================
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id' => $user['id_user'],
            'email' => $user['email'],
            'name' => $user['name'],
            'roles' => $user['roles'], // ARRAY
        ];

        // ********* Redirigir al dashboard *********
        header('Location: ../app/views/dashboard/index.php');
        exit;

    }

    // =====================================================
    // LOGOUT
    // =====================================================
    public function logout(): void
    {
        session_unset();
        session_destroy();

        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }

    // =====================================================
    // HELPERS
    // =====================================================
    private function redirectToLogin(): void
    {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}
