<?php
/**
 * Location: vetapp/public/dashboard.php
 * Role: Dashboard view (SOLO PRESENTACIÓN)
 */

session_start();

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/middleware/AuthMiddleware.php';

// 🔐 Protección
AuthMiddleware::handle();

require_once __DIR__ . '/../app/views/dashboard/index.php';
