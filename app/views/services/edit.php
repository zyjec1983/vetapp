<?php
// app/views/services/edit.php

if (!function_exists('old')) {
    function old($key, $default = '') {
        return $_SESSION['old'][$key] ?? $default;
    }
}

$title = 'Editar Servicio | VetApp';
$old = $_SESSION['old'] ?? [];
unset($_SESSION['old']);

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../layouts/aside.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>services.php">Servicios</a></li>
                    <li class="breadcrumb-item active">Editar Servicio</li>
                </ol>
            </nav>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-dark py-3">
                    <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Editar Servicio</h5>
                </div>
                <div class="card-body p-4">

                    <?php if (isset($_SESSION['errors'])): ?>
                        <div class="alert alert-danger">
                            <ul><?php foreach ($_SESSION['errors'] as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?></ul>
                        </div>
                        <?php unset($_SESSION['errors']); ?>
                    <?php endif; ?>

                    <form action="<?= BASE_URL ?>services.php?action=update" method="POST" autocomplete="off">
                        <input type="hidden" name="id_service" value="<?= $service->getIdService() ?>">

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Nombre del Servicio *</label>
                                <input type="text" name="name" class="form-control" required
                                    value="<?= htmlspecialchars(old('name', $service->getName())) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Precio ($) *</label>
                                <input type="number" step="0.01" name="price" class="form-control" required
                                    value="<?= old('price', $service->getPrice()) ?>">
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="taxable" id="taxable" value="1"
                                        <?= old('taxable', $service->getTaxable()) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="taxable">¿Grava IVA?</label>
                                </div>
                                <small class="text-muted">Servicios veterinarios no gravan IVA (según decreto).</small>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label fw-bold small">Descripción (opcional)</label>
                                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars(old('description', $service->getDescription())) ?></textarea>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="active" id="active" value="1"
                                        <?= old('active', $service->getActive()) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="active">Servicio Activo</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-4">
                            <a href="<?= BASE_URL ?>services.php" class="btn btn-outline-secondary px-4">Cancelar</a>
                            <button type="submit" class="btn btn-warning px-5 shadow-sm">
                                <i class="bi bi-save me-2"></i>Actualizar Servicio
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>