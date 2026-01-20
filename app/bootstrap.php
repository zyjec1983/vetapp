<?php
/**
 * Location: vetapp/app/bootstrap.php
 * Purpose: Inicializa toda la aplicación
 */

declare(strict_types=1);

// ==================================================
// SESIÓN GLOBAL
// ==================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==================================================
// CONFIGURACIÓN GLOBAL
// ==================================================
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/Database.php';

// ==================================================
// AUTOLOAD DE CLASES (SIN COMPOSER)
// ==================================================
spl_autoload_register(function (string $class) {

    $basePaths = [
        __DIR__ . '/controllers/',
        __DIR__ . '/models/',
        __DIR__ . '/middleware/',
        __DIR__ . '/repositories/',
        __DIR__ . '/helpers/',
    ];

    foreach ($basePaths as $basePath) {
        $file = $basePath . $class . '.php';

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// ==================================================
// HELPERS GLOBALES
// ==================================================
$helpers = [
    __DIR__ . '/helpers/auth.php',
    __DIR__ . '/helpers/redirect.php',
];

foreach ($helpers as $helper) {
    if (file_exists($helper)) {
        require_once $helper;
    }
}
