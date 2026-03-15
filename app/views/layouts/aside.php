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
                <a class="nav-link text-white" href="dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i>
                    Dashboard
                </a>
            </li>

            <!-- ********** VISTAS MENU ASIDE PARA ADMIN - VETERINARIAN ********** -->
            <?php if(hasRole('admin') || hasRole('veterinarian')) : ?>
            <li class="nav-item">
                <a class="nav-link text-white" href="users.php">
                    <i class="bi bi-person-gear me-2"></i>
                    Usuarios
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="#">
                    <i class="bi bi-people me-2"></i>
                    Clientes
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="#">
                    <i class="bi bi-heart me-2"></i>
                    Mascotas
                </a>
            </li>
            <?php endif; ?>

            <!-- ********** VISTAS MENU ASIDE PARA ADMIN - VETERINARIAN - PHARMACY ********** -->
            <?php if (hasRole('veterinarian') || hasRole('admin')) : ?>
            <li class="nav-item">
                <a class="nav-link text-white" href="#">
                    <i class="bi bi-clipboard2-pulse me-2"></i>
                    Consultas
                </a>
            </li>
            <?php endif; ?>

             <!-- ********** VISTAS MENU ASIDE PARA PHARMACY ********** -->
            <?php if (hasRole('veterinarian') || hasRole('admin') || hasRole('pharmacy')) : ?>
            <li class="nav-item">
                <a class="nav-link text-white" href="#">
                    <i class="bi bi-capsule me-2"></i>
                    Medicamentos
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="#">
                    <i class="bi bi-cart3 me-2"></i>
                    Ventas
                </a>
            </li>
            <?php endif; ?>

        </ul>

        <hr class="text-white">
        

    </div>

</div>

<style>

.sidebar{
    min-height: calc(100vh - 56px);
    position: sticky;
    top:56px;
}

.sidebar .nav-link{
    padding:10px 20px;
}

.sidebar .nav-link:hover{
    background:#343a40;
}

</style>