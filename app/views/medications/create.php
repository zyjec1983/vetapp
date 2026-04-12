<?php
// app/views/medications/create.php

if (!function_exists('old')) {
    function old($key, $default = '')
    {
        return $_SESSION['old'][$key] ?? $default;
    }
}

$title = 'Nuevo Medicamento / Accesorio | VetApp';
$old = $_SESSION['old'] ?? [];
unset($_SESSION['old']);
$activeIngredients = $activeIngredients ?? []; // viene del controlador

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../layouts/aside.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="medicamento-tab" data-bs-toggle="tab"
                        data-bs-target="#medicamento" type="button" role="tab">Medicamento</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="accesorio-tab" data-bs-toggle="tab" data-bs-target="#accesorio"
                        type="button" role="tab">Accesorio / Otro</button>
                </li>
            </ul>

            <div class="tab-content" id="myTabContent">
                <!-- TAB MEDICAMENTO -->
                <div class="tab-pane fade show active" id="medicamento" role="tabpanel">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white py-3">
                            <h5 class="mb-0"><i class="bi bi-capsule me-2"></i>Registrar Medicamento</h5>
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

                            <form action="<?= BASE_URL ?>medications.php?action=store" method="POST" autocomplete="off">
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Código *</label>
                                        <input type="text" name="code" class="form-control" required
                                            value="<?= old('code') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Nombre *</label>
                                        <input type="text" name="name" class="form-control" required
                                            value="<?= old('name') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Principio Activo</label>
                                        <select name="id_active" class="form-select">
                                            <option value="">-- Seleccionar --</option>
                                            <?php foreach ($activeIngredients as $active): ?>
                                                <option value="<?= $active['id_active'] ?>"
                                                    <?= old('id_active') == $active['id_active'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($active['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Categoría</label>
                                        <select name="category" class="form-select">
                                            <option value="">-- Seleccionar --</option>
                                            <option value="Antibiótico" <?= old('category') == 'Antibiótico' ? 'selected' : '' ?>>Antibiótico</option>
                                            <option value="Antiinflamatorio" <?= old('category') == 'Antiinflamatorio' ? 'selected' : '' ?>>Antiinflamatorio</option>
                                            <option value="Antiparasitario" <?= old('category') == 'Antiparasitario' ? 'selected' : '' ?>>Antiparasitario</option>
                                            <option value="Analgésico" <?= old('category') == 'Analgésico' ? 'selected' : '' ?>>Analgésico</option>
                                            <option value="Accesorios y otros" <?= old('category') == 'Accesorios y otros' ? 'selected' : '' ?>>Accesorios y otros</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Ubicación</label>
                                        <input type="text" name="location" class="form-control"
                                            value="<?= old('location') ?>">
                                    </div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Stock Mínimo *</label>
                                        <input type="number" name="minimum_stock" class="form-control" required
                                            value="<?= old('minimum_stock') ?>">
                                        <small class="text-muted">Unidades para alerta de bajo stock</small>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Stock Inicial *</label>
                                        <input type="number" name="initial_stock" class="form-control" required
                                            value="<?= old('initial_stock') ?>">
                                        <small class="text-muted">Cantidad que ingresa al inventario</small>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Precio de Venta ($) *</label>
                                        <input type="number" step="0.01" name="sale_price" class="form-control" required
                                            value="<?= old('sale_price') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" name="taxable"
                                                id="taxable_med" value="1" <?= old('taxable') ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="taxable_med">¿Grava IVA?</label>
                                        </div>
                                        <small class="text-muted">Según decreto, medicamentos no gravan IVA
                                            (desactivado).</small>
                                    </div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-12">
                                        <label class="form-label fw-bold small">Descripción</label>
                                        <textarea name="description" class="form-control"
                                            rows="2"><?= old('description') ?></textarea>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2 border-top pt-4">
                                    <a href="<?= BASE_URL ?>medications.php"
                                        class="btn btn-outline-secondary px-4">Cancelar</a>
                                    <button type="submit" class="btn btn-primary px-5 shadow-sm">
                                        <i class="bi bi-save me-2"></i>Guardar Medicamento
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- ********************** TAB ACCESORIO ********************** -->
                <div class="tab-pane fade" id="accesorio" role="tabpanel">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-success text-white py-3">
                            <h5 class="mb-0"><i class="bi bi-bag-check me-2"></i>Registrar Accesorio / Otro Producto
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <form action="<?= BASE_URL ?>medications.php?action=storeAccessory" method="POST"
                                autocomplete="off">
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Código *</label>
                                        <input type="text" name="code" class="form-control" required
                                            value="<?= old('code') ?>">
                                        <small class="text-muted">Código del fabricante o propio</small>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Nombre *</label>
                                        <input type="text" name="name" class="form-control" required
                                            value="<?= old('name') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Stock Mínimo</label>
                                        <input type="number" name="minimum_stock" class="form-control"
                                            value="<?= old('minimum_stock', 0) ?>">
                                        <small class="text-muted">Para alerta de bajo stock (opcional)</small>
                                    </div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Precio Venta ($) *</label>
                                        <input type="number" step="0.01" name="sale_price" class="form-control" required
                                            value="<?= old('sale_price') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Stock Inicial *</label>
                                        <input type="number" name="initial_stock" class="form-control" required
                                            value="<?= old('initial_stock') ?>">
                                        <small class="text-muted">Cantidad que ingresa al inventario</small>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" name="taxable"
                                                id="taxable_acc" value="1" <?= old('taxable') ? 'checked' : 'checked' ?>>
                                            <label class="form-check-label" for="taxable_acc">¿Grava IVA?</label>
                                        </div>
                                        <small class="text-muted">Accesorios generalmente gravan IVA (activado por
                                            defecto).</small>
                                    </div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-12">
                                        <label class="form-label fw-bold small">Descripción (opcional)</label>
                                        <textarea name="description" class="form-control"
                                            rows="2"><?= old('description') ?></textarea>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2 border-top pt-4">
                                    <a href="<?= BASE_URL ?>medications.php"
                                        class="btn btn-outline-secondary px-4">Cancelar</a>
                                    <button type="submit" class="btn btn-success px-5 shadow-sm">
                                        <i class="bi bi-save me-2"></i>Guardar Accesorio
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>