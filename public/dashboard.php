<?php
/**
 * Location: vetapp/public/dashboard.php
 * Role: Dashboard view
 */

session_start();

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../app/controllers/DashboardController.php';
require_once __DIR__ . '/../app/bootstrap.php';

// 🔐 Protección
AuthMiddleware::handle();

$controller = new DashboardController();
$controller->index();