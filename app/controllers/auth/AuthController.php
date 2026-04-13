<?php
/**
 * Location: vetapp/app/controllers/auth/AuthController.php
 * Responsabilidad: Manejar login y logout de usuarios
 */

declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../helpers/sanitize.php';

class AuthController
{
    private UserRepository $userRepository;

    // ********** Constructor: inicializa repositorio de usuarios **********
    public function __construct()
    {
        $db = Database::getInstance()->getConnection();
        $this->userRepository = new UserRepository($db);
    }

    // ********** Procesar login de usuario **********
    public function login(): void
    {
        // ********** Validar método POST **********
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToLogin();
        }

        // ********** Sanitizar datos de entrada (email y password) **********
        $data = sanitizeArray($_POST);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        // ********** Validar campos obligatorios **********
        if ($email === '' || $password === '') {
            $_SESSION['error'] = 'Email y Contraseña son obligatorios';
            $this->redirectToLogin();
        }

        // ********** Buscar usuario por email **********
        $user = $this->userRepository->findByEmailWithRoles($email);

        if (!$user) {
            $_SESSION['error'] = 'Usuario no encontrado';
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        }

        // ********** Verificar si el usuario está activo **********
        if (!$user['active']) {
            $_SESSION['error'] = 'Usuario inactivo';
            $this->redirectToLogin();
        }

        // ********** Verificar contraseña **********
        if (!password_verify($password, $user['password'])) {
            $_SESSION['error'] = 'Credenciales inválidas';
            $this->redirectToLogin();
        }

        // ********** Login exitoso: regenerar ID de sesión por seguridad **********
        session_regenerate_id(true);

        // ********** Guardar datos del usuario en sesión **********
        $_SESSION['user'] = [
            'id' => $user['id_user'],
            'email' => $user['email'],
            'name' => $user['name'],
            'lastname1' => $user['lastname1'],
            'roles' => $user['roles'],
        ];

        // ********** Redirigir al dashboard **********
        header('Location: ' . BASE_URL . 'dashboard.php');
        exit;
    }

    // ********** Cerrar sesión del usuario **********
    public function logout(): void
    {
        // Asegurar que la sesión esté iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Vaciar variables de sesión
        $_SESSION = [];

        // Destruir la sesión
        session_destroy();

        // Destruir cookie de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Redirigir al login
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }

    // ********** Redirigir a página de login **********
    private function redirectToLogin(): void
    {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}
