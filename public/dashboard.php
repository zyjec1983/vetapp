<?php
require_once __DIR__ . '/../app/bootstrap.php';

AuthMiddleware::handle();
RoleMiddleware::require(['admin', 'veterinarian', 'pharmacy']);

echo "Bienvenido al Dashboard";
