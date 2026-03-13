<?php
/**
 * Location: vetapp/public/users.php
 */

require_once __DIR__ . '/../app/bootstrap.php';

// Protección
AuthMiddleware::handle();

// Controller
require_once __DIR__ . '/../app/controllers/users/UserController.php';

$controller = new UserController();
$controller->index();