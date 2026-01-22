<?php
/**
 * Location: vetapp/app/views/dashboard/index.php
 */

require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';

AuthMiddleware::handle();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>VetApp | Dashboard</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/bootstrap.min.css">
</head>

<body class="container py-5">

    <h2 class="mb-4">
        Bienvenido, <?= htmlspecialchars($_SESSION['user']['name']) ?>
    </h2>

    <div class="row g-4">

        <?php if (hasRole('admin')): ?>
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body text-center">
                    <h5>Administración</h5>
                    <p>Gestión de usuarios y roles</p>
                    <a href="<?= BASE_URL ?>users" class="btn btn-primary">Entrar</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (hasRole('veterinarian')): ?>
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body text-center">
                    <h5>Veterinaria</h5>
                    <p>Consultas y mascotas</p>
                    <a href="<?= BASE_URL ?>consultations" class="btn btn-success">Entrar</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (hasRole('pharmacy')): ?>
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body text-center">
                    <h5>Farmacia</h5>
                    <p>Ventas y medicamentos</p>
                    <a href="<?= BASE_URL ?>sales" class="btn btn-warning">Entrar</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

</body>
</html>
