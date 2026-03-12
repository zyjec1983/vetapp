<?php
/**
 * Location: vetapp/public/users.php
 * Entry point for Users module
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/controllers//users/UserController.php';

(new UserController())->index();