<?php
/**
 * Location: vetapp/app/views/consultations/create.php
 */

// Helper old() si no existe
if (!function_exists('old')) {
    function old($key, $default = '')
    {
        return $_SESSION['old'][$key] ?? $default;
    }
}

// app/views/consultations/create.php
$title = 'Nueva Consulta | VetApp';
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
                        <!-- Datos de la mascota -->
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <div class="col-12">
                                    <label class="form-label fw-bold">Buscar Cliente / Mascota *</label>

                                    <div class="input-group">
                                        <input type="text" id="petSearch" class="form-control"
                                            placeholder="Buscar por cliente o mascota...">
                                        <button class="btn btn-primary" type="button" id="petSearchBtn">
                                            <i class="bi bi-search"></i>
                                        </button>
                                    </div>

                                    <!-- Resultados -->
                                    <div id="petResults" class="list-group mt-2"
                                        style="max-height: 250px; overflow-y: auto;"></div>

                                    <!-- Seleccionado -->
                                    <div id="selectedPet" class="mt-2"></div>

                                    <!-- ID real -->
                                    <input type="hidden" name="id_pet" id="petId" required>
                                </div>
                            </div>
                        </div>

                        <!-- Datos clínicos -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Peso (kg)</label>
                                <input type="number" step="0.01" name="weight" class="form-control"
                                    value="<?= old('weight') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Temperatura (°C)</label>
                                <input type="number" step="0.1" name="temperature" class="form-control"
                                    value="<?= old('temperature') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Próxima Visita</label>
                                <input type="date" name="next_visit" class="form-control"
                                    value="<?= old('next_visit') ?>">
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label fw-bold">Diagnóstico *</label>
                                <textarea name="diagnosis" class="form-control" rows="3"
                                    required><?= old('diagnosis') ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Tratamiento</label>
                                <textarea name="treatment" class="form-control"
                                    rows="3"><?= old('treatment') ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Observaciones</label>
                                <textarea name="observations" class="form-control"
                                    rows="2"><?= old('observations') ?></textarea>
                            </div>
                        </div>

                        <!-- Honorarios y estado (al final) -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-7">
                                <label class="form-label">Servicio *</label>
                                <select name="id_service" id="serviceSelect" class="form-select" required>
                                    <option value="">-- Seleccione un servicio --</option>
                                    <?php foreach ($services as $service): ?>
                                        <option value="<?= $service->getIdService() ?>"
                                            data-price="<?= $service->getPrice() ?>">
                                            <?= htmlspecialchars($service->getName()) ?> -
                                            $<?= number_format($service->getPrice(), 2) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Honorarios ($)</label>
                                <input type="number" step="0.01" name="consultation_fee" id="consultationFee"
                                    class="form-control" readonly>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Estado</label>
                                <select name="status" class="form-select">
                                    <option value="completed" <?= old('status') == 'completed' ? 'selected' : '' ?>>
                                        Completado</option>
                                    <option value="pending" <?= old('status') == 'pending' ? 'selected' : '' ?>>Pendiente
                                    </option>
                                    <option value="cancelled" <?= old('status') == 'cancelled' ? 'selected' : '' ?>>
                                        Cancelado</option>
                                </select>
                            </div>
                        </div>

                        <!-- Recordatorio -->
                        <div class="card border-warning mt-3">
                            <div class="card-body">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="enableReminder"
                                        name="enable_reminder" value="1">
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
                            <a href="<?= BASE_URL ?>consultations.php"
                                class="btn btn-outline-secondary px-4">Cancelar</a>
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

<script>
    let petTimeout;

    const petInput = document.getElementById('petSearch');
    const petBtn = document.getElementById('petSearchBtn');
    const petResults = document.getElementById('petResults');
    const selectedPetDiv = document.getElementById('selectedPet');
    const petIdInput = document.getElementById('petId');

    function debouncePetSearch() {
        clearTimeout(petTimeout);
        petTimeout = setTimeout(searchPets, 300);
    }

    function searchPets() {
        const q = petInput.value.trim();

        if (q.length < 2) {
            petResults.innerHTML = '';
            return;
        }

        fetch(`<?= BASE_URL ?>consultations.php?action=searchPets&q=${encodeURIComponent(q)}`)
            .then(res => res.json())
            .then(data => {
                petResults.innerHTML = '';

                if (data.length === 0) {
                    petResults.innerHTML = '<div class="list-group-item text-muted">Sin resultados</div>';
                    return;
                }

                data.forEach(item => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'list-group-item list-group-item-action';

                    btn.innerHTML = `
                    <strong>${item.pet_name}</strong><br>
                    <small>Dueño: ${item.client_name}</small>
                `;

                    btn.addEventListener('click', () => selectPet(item));

                    petResults.appendChild(btn);
                });
            })
            .catch(err => {
                console.error(err);
                alert('Error al buscar mascotas');
            });
    }

    function selectPet(item) {
        petIdInput.value = item.id_pet;

        selectedPetDiv.innerHTML = `
        <div class="alert alert-success p-2">
            <strong>${item.pet_name}</strong><br>
            <small>Dueño: ${item.client_name}</small>
        </div>
    `;

        petResults.innerHTML = '';
        petInput.value = '';
    }

    // eventos
    petBtn.addEventListener('click', searchPets);
    petInput.addEventListener('input', debouncePetSearch);
    petInput.addEventListener('keypress', e => {
        if (e.key === 'Enter') searchPets();
    });

    document.getElementById('serviceSelect').addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        const price = selected.dataset.price || 0;
        document.getElementById('consultationFee').value = price;
    });

    // Al final del archivo, antes del footer

    document.getElementById('serviceSelect').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const price = selected.dataset.price || 0;
        document.getElementById('consultationFee').value = price;
    });

</script>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>