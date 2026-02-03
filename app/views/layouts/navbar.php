<?php
// app/views/layouts/navbar.php
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= BASE_URL ?>dashboard.php">VetApp</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#vetNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="vetNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                <?php if (hasRole('admin')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>users">Usuarios</a>
                    </li>
                <?php endif; ?>

                <?php if (hasRole('veterinarian')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>consultations">Consultas</a>
                    </li>
                <?php endif; ?>

                <?php if (hasRole('pharmacy')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>sales">Ventas</a>
                    </li>
                <?php endif; ?>

            </ul>

            <span class="navbar-text me-3">
                👤 <?= htmlspecialchars($_SESSION['user']['name']) ?>
            </span>

            <a href="<?= BASE_URL ?>logout.php" class="btn btn-outline-light btn-sm">
                Cerrar sesión
            </a>
        </div>
    </div>
</nav>
