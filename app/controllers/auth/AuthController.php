<?php
/**
 * Location: vetapp/app/controllers/auth/AuthController.php
 *
 * Responsibility:
 * - Coordina el proceso de autenticación
 * - NO accede directamente a la base de datos
 * - NO contiene SQL
 * - Usa Repository + Entity
 */

// echo '<pre>';
// var_dump($_POST);
// echo '</pre>';
// exit;




require_once __DIR__ . '/../../config/config.php';
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
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }

        // ************* OBTENER DATOS *************
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // ************* VALIDACIONES BÁSICAS *************
        if ($email === '' || $password === '') {
            $_SESSION['error'] = 'Email y contraseña son obligatorios';
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }

        // ************* INICIALIZAR REPOSITORY *************
        $db = Database::getInstance()->getConnection();
        $userRepository = new UserRepository($db);

        // ************* BUSCAR USUARIO *************
        $user = $userRepository->findByEmail($email);

        if ($user === null) {
            $_SESSION['error'] = 'Credenciales inválidas';
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }

        // ************* VERIFICAR ESTADO *************
        if (!$user->isActive()) {
            $_SESSION['error'] = 'Usuario desactivado';
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }

        // ************* VERIFICAR PASSWORD *************
        if (!$user->verifyPassword($password)) {
            $_SESSION['error'] = 'Credenciales inválidas';
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }

        // =====================================================
        // LOGIN EXITOSO
        // =====================================================

        // ************* SEGURIDAD DE SESIÓN *************
        session_regenerate_id(true);

        // ************* GUARDAR USUARIO EN SESIÓN *************
        $_SESSION['user'] = [
            'id'    => $user->getId(),
            'email' => $user->getEmail(),
            'name'  => $user->getFullName(),
            'role'  => $user->getRole()
        ];

        // ************* REDIRECCIÓN (TEMPORAL ÚNICA) *************
        // La autorización por rol se manejará con middleware
        header('Location: ' . BASE_URL . 'dashboard/index.php');
        exit;
    }

    // =====================================================
    // LOGOUT
    // =====================================================
    public function logout(): void
    {
        session_unset();
        session_destroy();

        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}
