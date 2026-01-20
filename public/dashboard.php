<?php
/**
 * Location: vetapp/public/dashboard.php
 * Responsibility: Página protegida del dashboard
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../app/middleware/RoleMiddleware.php';

AuthMiddleware::handle();

var_dump($_SESSION['user']);

RoleMiddleware::require(['admin']);
echo "<h1>Bienvenido al Dashboard</h1>";