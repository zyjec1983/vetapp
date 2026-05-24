<?php
/**
 * location: vetapp/app/views/layouts/navbar.php
 * Navbar común para toda la aplicación
 */

$currentUser = currentUser() ?? [];

// Cargar personalización de la primera empresa activa
$companyTitle = 'VetApp';
$companyLogoUrl = null;
try {
    require_once __DIR__ . '/../../repositories/CompanySettingRepository.php';
    $navCompanyRepo = new CompanySettingRepository();
    $activeCompanies = $navCompanyRepo->getActivas();
    if (!empty($activeCompanies)) {
        $first = $activeCompanies[0];
        $companyTitle = !empty($first['app_title']) ? $first['app_title'] : (!empty($first['commercial_name']) ? $first['commercial_name'] : 'VetApp');
        if (!empty($first['logo_path']) && file_exists($first['logo_path'])) {
            $companyLogoUrl = BASE_URL . '../storage/logos/' . basename($first['logo_path']);
        }
    }
} catch (Exception $e) {
    $companyTitle = 'VetApp';
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <!-- Botón para mostrar sidebar en móvil (visible solo en móvil) -->
        <button class="navbar-toggler me-2" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Mostrar menú">
            <span class="navbar-toggler-icon"></span>
        </button>

        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_URL ?>dashboard.php">
            <?php if ($companyLogoUrl): ?>
                <img src="<?= $companyLogoUrl ?>" alt="Logo" height="30" style="border-radius:4px;" onerror="this.style.display='none'">
            <?php endif; ?>
            <?= htmlspecialchars($companyTitle) ?>
        </a>

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
                        <a class="nav-link" href="<?= BASE_URL ?>consultations.php">Consultas</a>
                    </li>
                <?php endif; ?>

                <?php if (hasRole('pharmacy')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>sales.php">Ventas</a>
                    </li>
                <?php endif; ?>

                <?php if (hasRole('admin') || hasRole('pharmacy')): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-gear me-1"></i>Configuración
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>settings.php">
                                <i class="bi bi-building me-1"></i>Empresa / RUC
                            </a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>settings.php?action=whatsapp">
                                <i class="bi bi-whatsapp me-1"></i>WhatsApp
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>settings.php?action=personalization">
                                <i class="bi bi-palette me-1"></i>Personalización
                            </a></li>
                        </ul>
                    </li>
                <?php endif; ?>

            </ul>

            <!-- Nombre de usuario y cerrar sesión agrupados -->
            <div class="d-flex align-items-center gap-2">
                <span class="navbar-text text-white">
                    <i class="bi bi-person-circle me-1"></i>
                    <strong><?= htmlspecialchars($currentUser['name'] ?? '') . ' ' . htmlspecialchars($currentUser['lastname1'] ?? '') ?></strong>
                </span>
                <button type="button" class="btn btn-outline-light btn-sm" data-bs-toggle="modal" data-bs-target="#logoutModal">
                    <i class="bi bi-box-arrow-right me-1"></i> Cerrar sesión
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación de cierre de sesión -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Confirmación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-dark">
                    ¿Estás seguro de que deseas cerrar tu sesión en <strong><?= htmlspecialchars($companyTitle) ?></strong>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <a href="<?= BASE_URL ?>logout.php" class="btn btn-primary">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </div>
</nav>
