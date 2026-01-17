<?php
/**
 * Location: vetapp/app/middleware/AuthMiddleware.php
 * Responsibility: Protect routes and views
 */

class AuthMiddleware
{
    // ************* SESSION CHECK *************
    public static function check(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            self::redirectToLogin();
        }
    }

    // ************* ROLE CHECK *************
    public static function requireRole(array $allowedRoles): void
    {
        self::check();

        $userRole = $_SESSION['user']['role'] ?? null;

        if (!in_array($userRole, $allowedRoles)) {
            self::forbidden();
        }
    }

    // ************* LOGOUT *************
    public static function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_unset();
        session_destroy();

        self::redirectToLogin();
    }

    // ************* REDIRECT HELPERS *************
    private static function redirectToLogin(): void
    {
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }

    private static function forbidden(): void
    {
        http_response_code(403);
        echo "Acceso denegado";
        exit;
    }
}
