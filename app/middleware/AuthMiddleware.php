<?php
/**
 * Location: vetapp/app/middleware/AuthMiddleware.php
 * Responsibility:
 * - Verifica si el usuario está autenticado
 * - Protege vistas y controladores
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

class AuthMiddleware
{
    // =====================================================
    // VERIFICA SI HAY SESIÓN ACTIVA
    // =====================================================
    public static function handle(): void
    {
        // Iniciar sesión si no existe
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Verificar autenticación
        if (!isset($_SESSION['user'])) {
            self::redirectToLogin();
        }
    }

    // =====================================================
    // REDIRECCIÓN AL LOGIN
    // =====================================================
    private static function redirectToLogin(): void
    {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}
