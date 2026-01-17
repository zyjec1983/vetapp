<?php
/**
 * Location: vetapp/app/controllers/auth/AuthController.php
 * Responsibility:
 * - Coordina el proceso de autenticación
 * - NO accede directamente a la base de datos
 * - NO contiene SQL
 */

session_start();

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../repositories/UserRepository.php';

class AuthController
{
    // =====================================================
    // LOGIN
    // =====================================================
    public function login(): void
    {
        // ************* VALIDAR MÉTODO *************
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        // ************* OBTENER DATOS *************
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // ************* VALIDACIONES BÁSICAS *************
        if ($email === '' || $password === '') {
            $_SESSION['error'] = 'Email y contraseña son obligatorios';
            header('Location: /login');
            exit;
        }

        // ************* INICIALIZAR REPOSITORY *************
        $db = Database::getInstance()->getConnection();
        $userRepository = new UserRepository($db);

        // ************* BUSCAR USUARIO *************
        $user = $userRepository->findByEmail($email);

        if ($user === null) {
            $_SESSION['error'] = 'Credenciales inválidas';
            header('Location: /login');
            exit;
        }

        // ************* VERIFICAR ESTADO *************
        if (!$user->isActive()) {
            $_SESSION['error'] = 'Usuario desactivado';
            header('Location: /login');
            exit;
        }

        // ************* VERIFICAR PASSWORD *************
        if (!$user->verifyPassword($password)) {
            $_SESSION['error'] = 'Credenciales inválidas';
            header('Location: /login');
            exit;
        }

        // =====================================================
        // LOGIN EXITOSO
        // =====================================================

        // ************* REGENERAR SESIÓN *************
        session_regenerate_id(true);

        // ************* GUARDAR USUARIO EN SESIÓN *************
        $_SESSION['user'] = [
            'id'    => $user->getId(),
            'email' => $user->getEmail(),
            'name'  => $user->getFullName(),
            'role'  => $user->getRole()
        ];

        // ************* REDIRECCIÓN POR ROL *************
        switch ($user->getRole()) {
            case 'admin':
                header('Location: /admin/dashboard');
                break;

            case 'veterinarian':
                header('Location: /vet/dashboard');
                break;

            case 'pharmacy':
                header('Location: /pharmacy/dashboard');
                break;

            default:
                header('Location: /');
        }

        exit;
    }

    // =====================================================
    // LOGOUT
    // =====================================================
    public function logout(): void
    {
        session_start();
        session_unset();
        session_destroy();

        header('Location: /login');
        exit;
    }
}
