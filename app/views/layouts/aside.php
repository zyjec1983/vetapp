<?php
/**
 * Sidebar Navigation
 * Location: vetapp/views/layouts/aside.php
 */

// ********* se encuentra en -> HELPERS/AUTH.PHP
$currentUser = currentUser() ?? [];
?>

<div class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse" id="sidebarMenu">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link text-white" href="<?= BASE_URL ?>dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            <hr class="text-white">

            <?php if (hasRole('admin') || hasRole('veterinarian')): ?>
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?= BASE_URL ?>users.php">
                        <i class="bi bi-person-gear me-2"></i> Usuarios
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-white" href="<?= BASE_URL ?>clients.php">
                        <i class="bi bi-people me-2"></i> Clientes
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-white" href="<?= BASE_URL ?>pets.php">
                        <i class="bi bi-heart me-2"></i> Mascotas
                    </a>
                </li>
            <?php endif; ?>
            <hr class="text-white">

            <?php if (hasRole('veterinarian') || hasRole('admin')): ?>
                <li class="nav-item">
                    <a class="nav-link text-white active" href="<?= BASE_URL ?>consultations.php">
                        <i class="bi bi-clipboard2-pulse me-2"></i> Consultas
                    </a>
                </li>
            <?php endif; ?>

            <?php if (hasRole('veterinarian') || hasRole('admin') || hasRole('pharmacy')): ?>
                <!-- Dropdown Medicamentos -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" id="medicamentosDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-capsule me-2"></i> Medicamentos
                    </a>
                    <ul class="dropdown-menu bg-dark" aria-labelledby="medicamentosDropdown">
                        <li><a class="dropdown-item text-white bg-dark" href="<?= BASE_URL ?>medications.php">Gestión de Medicamentos</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-white bg-dark" href="<?= BASE_URL ?>medications.php?action=inactive">Reactivar Producto</a></li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-white" href="<?= BASE_URL ?>sales.php">
                        <i class="bi bi-cart3 me-2"></i> Ventas
                    </a>
                </li>
            <?php endif; ?>
        </ul>
        <hr class="text-white">
    </div>
</div>

<style>
    .sidebar {
        min-height: calc(100vh - 56px);
        position: sticky;
        top: 56px;
    }

    .sidebar .nav-link {
        padding: 10px 20px;
        transition: all 0.2s ease-in-out;
    }

    /* Hover para los enlaces principales */
    .sidebar .nav-link:hover {
        background-color: #6c757d;   /* gris claro  */
        color: #ffffff !important;
        border-radius: 8px;
        padding-left: 28px;
    }

    /* Estilos para el dropdown de Medicamentos */
    .dropdown-menu.bg-dark {
        background-color: #343a40 !important;
        border: 1px solid #6c757d;
    }

    .dropdown-menu.bg-dark .dropdown-item {
        color: #ffffff;
        background-color: #343a40;
        transition: all 0.2s ease-in-out;
    }

    /* Hover para los items del dropdown */
    .dropdown-menu.bg-dark .dropdown-item:hover {
        background-color: #6c757d !important;
        color: #ffffff !important;
        padding-left: 28px;
    }
</style>