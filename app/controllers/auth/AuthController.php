<?php
/**
 * Location: vetapp/app/controllers/auth/AuthController.php
 * Responsibility: Handle authentication logic
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../repositories/UserRepository.php';

class AuthController
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $db = Database::getInstance()->getConnection();
        $this->userRepository = new UserRepository($db);
    }

    /**
     * LOGIN PROCESS
     */
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $_SESSION['error'] = 'Email y contraseña requeridos';
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        }

        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            $_SESSION['error'] = 'Usuario no encontrado';
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        }

        if (!$user['active']) {
            $_SESSION['error'] = 'Usuario inactivo';
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        }

        if (!password_verify($password, $user['password'])) {
            $_SESSION['error'] = 'Contraseña incorrecta';
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        }

        $_SESSION['user'] = [
            'id' => $user['id_user'],
            'email' => $user['email'],
            'name' => $user['name'],
            'roles' => $user['roles']
        ];

        header('Location: ' . BASE_URL . 'dashboard.php');
        exit;
    }


    /**
     * LOGOUT
     */
    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_unset();
        session_destroy();

        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

/**
 * Ejecutar login directamente si se llama desde el formulario
 */

