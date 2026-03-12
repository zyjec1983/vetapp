<?php
/**
 * location: vetapp/app/views/layouts/navbar.php
 * Navbar común para toda la aplicación
 */
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
                        <a class="nav-link" href="<?= BASE_URL ?>users.php">Usuarios</a>
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

            <!-- ************** Nombre de usuario y cerrar sesión ************* -->
            <span class="navbar-text me-3 text-white">
                <?= htmlspecialchars($_SESSION['user']['name']) ?>
            </span>

            <button type="button" class="btn btn-outline-light btn-sm" data-bs-toggle="modal"
                data-bs-target="#logoutModal">
                Cerrar sesión
            </button>
        </div>
    </div>

    <!-- ************** Modal de confirmación de cierre de sesión ************* -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Confirmación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-dark">
                    ¿Estás seguro de que deseas cerrar tu sesión en <strong>VetApp</strong>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <a href="<?= BASE_URL ?>logout.php" class="btn btn-primary">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </div>
</nav>