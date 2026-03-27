<?php
/**
 * Location: vetapp/app/views/medications/create.php
 * Formulario para crear nuevo medicamento
 */

if (!function_exists('old')) {
    function old($key, $default = '') {
        return $_SESSION['old'][$key] ?? $default;
    }
}

$title = 'Nuevo Medicamento | VetApp';
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
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>medications.php">Medicamentos</a></li>
                    <li class="breadcrumb-item active">Nuevo Medicamento</li>
                </ol>
            </nav>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0"><i class="bi bi-capsule me-2"></i>Registrar Medicamento</h5>
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

                    <form action="<?= BASE_URL ?>medications.php?action=update" method="POST" autocomplete="off">

                    <input type="hidden" name="id_medication" value="<?= $medication->getIdMedication() ?>">

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Código *</label>
                                <input type="text" name="code" class="form-control" required value="<?= old('code') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Nombre *</label>
                                <input type="text" name="name" class="form-control" required value="<?= old('name') ?>">
                            </div>
                            
                             <div class="col-md-4">
                                <label class="form-label fw-bold small">Principio Activo</label>
                                <div class="input-group">
                                    <select name="id_active" id="active_ingredient_select" class="form-select">
                                        <option value="">-- Seleccionar --</option>
                                        <?php foreach ($activeIngredients as $active): ?>
                                            <option value="<?= $active['id_active'] ?>"
                                                <?= old('id_active') == $active['id_active'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($active['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal"
                                        data-bs-target="#addActiveIngredientModal">
                                        <i class="bi bi-plus-lg"></i> Nuevo
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Categoría</label>
                                <input type="text" name="category" class="form-control" value="<?= old('category') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Ubicación</label>
                                <input type="text" name="location" class="form-control" value="<?= old('location') ?>">
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Stock Mínimo *</label>
                                <input type="number" name="minimum_stock" class="form-control" required value="<?= old('minimum_stock') ?>">
                                <small class="text-muted">Unidades para alerta de bajo stock</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Precio de Venta ($) *</label>
                                <input type="number" step="0.01" name="sale_price" class="form-control" required value="<?= old('sale_price') ?>">
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="active" id="active" value="1" checked>
                                    <label class="form-check-label" for="active">Activo</label>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label fw-bold small">Descripción</label>
                                <textarea name="description" class="form-control" rows="2"><?= old('description') ?></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-4">
                            <a href="<?= BASE_URL ?>medications.php" class="btn btn-outline-secondary px-4">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-5 shadow-sm">
                                <i class="bi bi-save me-2"></i>Guardar Medicamento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

!-- Modal para agregar principio activo -->
<div class="modal fade" id="addActiveIngredientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Agregar Principio Activo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addActiveIngredientForm">
                    <div class="mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" id="new_active_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea id="new_active_description" class="form-control" rows="2"></textarea>
                    </div>
                </form>
                <div id="activeIngredientFeedback" class="alert alert-danger d-none mt-2"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="saveActiveIngredientBtn" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('saveActiveIngredientBtn').addEventListener('click', function() {
        const name = document.getElementById('new_active_name').value.trim();
        const description = document.getElementById('new_active_description').value.trim();
        const feedbackDiv = document.getElementById('activeIngredientFeedback');

        if (!name) {
            feedbackDiv.textContent = 'El nombre es obligatorio.';
            feedbackDiv.classList.remove('d-none');
            return;
        }

        feedbackDiv.classList.add('d-none');

        fetch('<?= BASE_URL ?>medications.php?action=addActiveIngredient', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'name=' + encodeURIComponent(name) + '&description=' + encodeURIComponent(description)
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                feedbackDiv.textContent = data.error;
                feedbackDiv.classList.remove('d-none');
            } else {
                // Agregar nueva opción al select
                const select = document.getElementById('active_ingredient_select');
                const option = document.createElement('option');
                option.value = data.id;
                option.textContent = data.name;
                option.selected = true;
                select.appendChild(option);
                // Cerrar modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('addActiveIngredientModal'));
                modal.hide();
                // Limpiar formulario
                document.getElementById('new_active_name').value = '';
                document.getElementById('new_active_description').value = '';
                // Opcional: mostrar mensaje de éxito
                Swal.fire('Éxito', 'Principio activo agregado correctamente', 'success');
            }
        })
        .catch(error => {
            feedbackDiv.textContent = 'Error de conexión. Intenta de nuevo.';
            feedbackDiv.classList.remove('d-none');
        });
    });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>