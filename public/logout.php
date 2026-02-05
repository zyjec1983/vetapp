<?php
/**
 * Location: vetapp/public/logout.php
 * Responsibility: Handle user logout by destroying session and cookies
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/controllers/auth/AuthController.php';

(new AuthController())->logout();
