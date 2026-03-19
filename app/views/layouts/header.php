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
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'VetApp' ?></title>

    <link rel="stylesheet" href="<?= BASE_URL ?>css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-light"></body>