<?php
/**
 * Location: vetapp/app/middleware/RoleMiddleware.php
 *
 * Responsibility:
 * - Autorizar acceso según roles
 * - NO maneja login
 * - NO crea sesiones
 * - SOLO autorización
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/AuthMiddleware.php';

class RoleMiddleware
{
    // =====================================================
    // VERIFICAR ROLES PERMITIDOS
    // =====================================================
    /**
     * @param array $allowedRoles Ej: ['admin'], ['admin','veterinarian']
     */
    public static function require(array $allowedRoles): void
    {
        // 1️⃣ Asegurar que esté logueado
        AuthMiddleware::handle();

        // 2️⃣ Obtener roles del usuario (ARRAY)
        $userRoles = $_SESSION['user']['roles'] ?? [];

        // 3️⃣ Verificar intersección de roles
        foreach ($allowedRoles as $role) {
            if (in_array($role, $userRoles, true)) {
                return; // ✅ acceso permitido
            }
        }

        // 4️⃣ Si no coincide ningún rol → 403
        self::forbidden();
    }

    // =====================================================
    // RESPUESTA 403
    // =====================================================
    private static function forbidden(): void
    {
        http_response_code(403);

        echo "<h1>403 - Acceso denegado</h1>";
        echo "<p>No tienes permisos para acceder a esta sección.</p>";
        echo "<a href='" . BASE_URL . "dashboard.php'>Volver al dashboard</a>";

        exit;
    }
}
