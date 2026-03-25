<?php
// app/views/consultations/create.php

if (!function_exists('old')) {
    function old($key, $default = '') {
        return $_SESSION['old'][$key] ?? $default;
    }
}

$title = 'Nueva Consulta | VetApp';

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
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>consultations.php">Consultas</a></li>
                    <li class="breadcrumb-item active">Nueva Consulta</li>
                </ol>
            </nav>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0"><i class="bi bi-clipboard2-pulse me-2"></i>Registrar Consulta Médica</h5>
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

                    <form method="POST" action="<?= BASE_URL ?>consultations.php?action=store" autocomplete="off">
                        <!-- Selección de mascota -->
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label fw-bold">Mascota *</label>
                                <select name="id_pet" class="form-select" required>
                                    <option value="">Seleccione una mascota</option>
                                    <?php foreach ($pets as $pet): ?>
                                        <option value="<?= $pet['id_pet'] ?>" <?= old('id_pet') == $pet['id_pet'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($pet['pet_name'] . ' (' . $pet['client_name'] . ')') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Datos clínicos -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Peso (kg)</label>
                                <input type="number" step="0.01" name="weight" class="form-control" value="<?= old('weight') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Temperatura (°C)</label>
                                <input type="number" step="0.1" name="temperature" class="form-control" value="<?= old('temperature') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Próxima Visita</label>
                                <input type="date" name="next_visit" class="form-control" value="<?= old('next_visit') ?>">
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label fw-bold">Diagnóstico *</label>
                                <textarea name="diagnosis" class="form-control" rows="3" required><?= old('diagnosis') ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Tratamiento</label>
                                <textarea name="treatment" class="form-control" rows="3"><?= old('treatment') ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Observaciones</label>
                                <textarea name="observations" class="form-control" rows="2"><?= old('observations') ?></textarea>
                            </div>
                        </div>

                        <!-- Honorarios y estado (al final) -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Honorarios ($)</label>
                                <input type="number" step="0.01" name="consultation_fee" class="form-control" value="<?= old('consultation_fee') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Estado</label>
                                <select name="status" class="form-select">
                                    <option value="completed" <?= old('status') == 'completed' ? 'selected' : '' ?>>Completado</option>
                                    <option value="pending" <?= old('status') == 'pending' ? 'selected' : '' ?>>Pendiente</option>
                                    <option value="cancelled" <?= old('status') == 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
                                </select>
                            </div>
                        </div>

                        <!-- Recordatorio (opcional) -->
                        <div class="card border-warning mt-3">
                            <div class="card-body">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="enableReminder" name="enable_reminder" value="1">
                                    <label class="form-check-label" for="enableReminder">Generar Recordatorio</label>
                                </div>
                                <div id="reminderFields" style="display:none;" class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Tipo de Recordatorio</label>
                                        <select name="reminder_type" class="form-select">
                                            <option value="consultation">Consulta</option>
                                            <option value="vaccine">Vacuna</option>
                                            <option value="payment">Pago</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Fecha del Aviso</label>
                                        <input type="date" name="reminder_date" class="form-control">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Mensaje</label>
                                        <textarea name="reminder_message" class="form-control"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-4 mt-4">
                            <a href="<?= BASE_URL ?>consultations.php" class="btn btn-outline-secondary px-4">Cancelar</a>
                            <button type="submit" class="btn btn-success px-5 shadow-sm">
                                <i class="bi bi-save me-2"></i>Guardar Consulta
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    document.getElementById('enableReminder').addEventListener('change', function () {
        document.getElementById('reminderFields').style.display = this.checked ? 'block' : 'none';
    });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>