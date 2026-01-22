<?php
/**
 * Location: vetapp/app/views/auth/logout.php
 */
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vaciar variables de sesión
$_SESSION = [];

// Destruir la sesión
session_destroy();

// Destruir cookie de sesión (buena práctica)
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
header('Location: /vetapp/public/index.php');
exit;
