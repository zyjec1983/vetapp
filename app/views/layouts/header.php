<?php
// 🔥 Mostrar errores (solo en desarrollo)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 🔥 Iniciar sesión SIEMPRE
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🔥 Cargar configuración (BASE_URL)
require_once __DIR__ . '/../../config/config.php';

// 🔥 Cargar helpers de autenticación
require_once __DIR__ . '/../../helpers/auth.php';

// 🔥 Cargar helpers de CSRF (para tokens en formularios)
require_once __DIR__ . '/../../helpers/csrf.php';

// 🔥 Cargar helpers de sanitización (para old inputs)
require_once __DIR__ . '/../../helpers/sanitize.php';
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'VetApp' ?></title>

    <link rel="stylesheet" href="<?= BASE_URL ?>css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>

<!-- Estilos para footer siempre al fondo -->
<style>
    /* Footer siempre al fondo (sticky footer) */
    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }
    
    /* El contenido principal ocupa el espacio disponible */
    .container-fluid {
        flex: 1;
        padding-bottom: 2rem;
    }
    
    footer {
        margin-top: auto;
    }
</style>

<body class="bg-light"></body>