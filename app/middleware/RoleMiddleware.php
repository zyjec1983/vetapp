<?php
/**
 * Location: vetapp/app/middleware/RoleMiddleware.php
 *
 * Responsibility:
 * - Verifica autorización por roles (MULTIROL)
 * - NO maneja login
 * - NO crea sesiones
 */

declare(strict_types=1);

require_once __DIR__ . '/AuthMiddleware.php';
require_once __DIR__ . '/../config/config.php';

class RoleMiddleware
{
    /**
     * @param array $allowedRoles Ej: ['admin'], ['admin','veterinarian']
     */
    public static function require(array $allowedRoles): void
    {
        // 1- Asegurar autenticación
        AuthMiddleware::handle();

        // 2- Obtener roles del usuario
        $userRoles = $_SESSION['user']['roles'] ?? [];

        // 3- Verificar intersección de roles
        foreach ($userRoles as $role) {
            if (in_array($role, $allowedRoles, true)) {
                return; // ✅ autorizado
            }
        }

        // 4- Si no coincide ningún rol → 403
        self::forbidden();
    }

    /**
     * Respuesta 403
     */
    private static function forbidden(): void
    {
        http_response_code(403);

        echo "<h1>403 - Acceso denegado</h1>";
        echo "<p>No tienes permisos para acceder a esta sección.</p>";
        echo "<a href='" . BASE_URL . "dashboard.php'>Volver al dashboard</a>";

        exit;
    }
}
