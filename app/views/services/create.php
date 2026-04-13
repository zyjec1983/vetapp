<?php
// app/views/services/create.php

if (!function_exists('old')) {
    function old($key, $default = '')
    {
        return $_SESSION['old'][$key] ?? $default;
    }
}

$title = 'Nuevo Servicio | VetApp';
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
                    <li class="breadcrumb-item active">Nuevo Servicio</li>
                </ol>
            </nav>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0"><i class="bi bi-briefcase me-2"></i>Registrar Servicio</h5>
                </div>
                <div class="card-body p-4">

                    <?php if (isset($_SESSION['errors'])): ?>
                        <div class="alert alert-danger">
                            <ul><?php foreach ($_SESSION['errors'] as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php unset($_SESSION['errors']); ?>
                    <?php endif; ?>

                    <form action="<?= BASE_URL ?>services.php?action=store" method="POST" autocomplete="off">

                        <!-- ********** GENERA TOKEN ********** -->
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Nombre del Servicio *</label>
                                <input type="text" name="name" class="form-control" required
                                    value="<?= htmlspecialchars(old('name')) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Precio ($) *</label>
                                <input type="number" step="0.01" name="price" class="form-control" required
                                    value="<?= old('price') ?>">
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="taxable" id="taxable"
                                        value="1" <?= old('taxable') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="taxable">¿Grava IVA?</label>
                                </div>
                                <small class="text-muted">Servicios veterinarios no gravan IVA (según decreto).</small>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label fw-bold small">Descripción (opcional)</label>
                                <textarea name="description" class="form-control"
                                    rows="3"><?= htmlspecialchars(old('description')) ?></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-4">
                            <a href="<?= BASE_URL ?>services.php" class="btn btn-outline-secondary px-4">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-5 shadow-sm">
                                <i class="bi bi-save me-2"></i>Guardar Servicio
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>