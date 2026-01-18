<?php
/**
 * Location: vetapp/app/middleware/RoleMiddleware.php
 *
 * Responsibility:
 * - Verificar que el usuario tenga un rol permitido
 * - NO maneja login
 * - NO crea sesiones
 * - SOLO controla autorización
 */

require_once __DIR__ . '/AuthMiddleware.php';
require_once __DIR__ . '/../config/config.php';

class RoleMiddleware
{
    // =====================================================
    // VERIFICAR ROL
    // =====================================================
    /**
     * @param array $allowedRoles  Ej: ['admin'], ['admin','veterinarian']
     */
    public static function require(array $allowedRoles): void
    {
        // ************* ASEGURAR AUTENTICACIÓN *************
        // Primero verificamos que el usuario esté logueado
        AuthMiddleware::handle();

        // ************* OBTENER ROL DEL USUARIO *************
        $userRole = $_SESSION['user']['role'] ?? null;

        // ************* VALIDAR ROL *************
        if (!in_array($userRole, $allowedRoles, true)) {
            self::forbidden();
        }
    }

    // =====================================================
    // RESPUESTA 403 - ACCESO DENEGADO
    // =====================================================
    private static function forbidden(): void
    {
        http_response_code(403);

        // 🔹 Puedes cambiar esto luego por una vista bonita
        echo "<h1>403 - Acceso denegado</h1>";
        echo "<p>No tienes permisos para acceder a esta sección.</p>";
        echo "<a href='" . BASE_URL . "/dashboard'>Volver al inicio</a>";

        exit;
    }
}
