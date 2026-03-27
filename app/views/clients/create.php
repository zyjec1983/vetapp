<?php
// app/views/clients/create.php

if (!function_exists('old')) {
    function old($key, $default = '') {
        return $_SESSION['old'][$key] ?? $default;
    }
}

$title = 'Nuevo Cliente | VetApp';

// Recuperar datos antiguos si hay error
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
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>clients.php">Clientes</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Nuevo Cliente</li>
                </ol>
            </nav>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-person-plus-fill me-2"></i>Registrar Nuevo Cliente
                    </h5>
                </div>
                <div class="card-body p-4">

                    <?php if (isset($_SESSION['errors'])): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($_SESSION['errors'] as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php unset($_SESSION['errors']); ?>
                    <?php endif; ?>

                    <form action="<?= BASE_URL ?>clients.php?action=store" method="POST" autocomplete="off">
                        <h6 class="text-primary text-uppercase small fw-bold mb-3">Información Personal</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small">Cédula o Pasaporte</label>
                                <input type="text" name="identification" class="form-control" maxlength="20"
                                       value="<?= htmlspecialchars(old('identification')) ?>">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small">Primer Nombre *</label>
                                <input type="text" name="name" class="form-control" required
                                       value="<?= htmlspecialchars(old('name')) ?>">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small">Segundo Nombre</label>
                                <input type="text" name="middlename" class="form-control"
                                       value="<?= htmlspecialchars(old('middlename')) ?>">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Apellido Paterno *</label>
                                <input type="text" name="lastname1" class="form-control" required
                                       value="<?= htmlspecialchars(old('lastname1')) ?>">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Apellido Materno</label>
                                <input type="text" name="lastname2" class="form-control"
                                       value="<?= htmlspecialchars(old('lastname2')) ?>">
                            </div>
                        </div>

                        <h6 class="text-primary text-uppercase small fw-bold mb-3">Contacto</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Teléfono *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input type="text" name="phone" class="form-control" required
                                           value="<?= htmlspecialchars(old('phone')) ?>">
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Correo Electrónico</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control"
                                           value="<?= htmlspecialchars(old('email')) ?>">
                                </div>
                            </div>
                        </div>

                        <h6 class="text-primary text-uppercase small fw-bold mb-3">Dirección y Observaciones</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label fw-bold small">Dirección</label>
                                <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars(old('address')) ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small">Observaciones</label>
                                <textarea name="observations" class="form-control" rows="2"><?= htmlspecialchars(old('observations')) ?></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-4">
                            <a href="<?= BASE_URL ?>clients.php" class="btn btn-outline-secondary px-4">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-5 shadow-sm">
                                <i class="bi bi-save me-2"></i>Guardar Cliente
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>