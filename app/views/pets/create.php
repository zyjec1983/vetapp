<?php
// app/views/pets/create.php

$title = 'Nueva Mascota | VetApp';
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
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>pets.php">Mascotas</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Nueva Mascota</li>
                </ol>
            </nav>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-heart-plus me-2"></i>Registrar Nueva Mascota
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

                    <form action="<?= BASE_URL ?>pets.php?action=store" method="POST" 
                            autocomplete="off" enctype="multipart/form-data">
                            
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label fw-bold small">Dueño *</label>
                                <select name="id_client" class="form-select" required>
                                    <option value="">Seleccione un cliente</option>
                                    <?php foreach ($clients as $client): ?>
                                        <option value="<?= $client->getIdClient() ?>" <?= (isset($old['id_client']) && $old['id_client'] == $client->getIdClient()) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($client->getFullName()) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Nombre de la mascota *</label>
                                <input type="text" name="name" class="form-control" required
                                    value="<?= htmlspecialchars($old['name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Especie *</label>
                                <input type="text" name="species" class="form-control" required
                                    value="<?= htmlspecialchars($old['species'] ?? '') ?>">
                                <small class="text-muted">Ej: Perro, Gato, Ave, etc.</small>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Raza</label>
                                <input type="text" name="breed" class="form-control"
                                    value="<?= htmlspecialchars($old['breed'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Sexo</label>
                                <select name="sex" class="form-select">
                                    <option value="M" <?= (isset($old['sex']) && $old['sex'] == 'M') ? 'selected' : '' ?>>
                                        Macho</option>
                                    <option value="F" <?= (isset($old['sex']) && $old['sex'] == 'F') ? 'selected' : '' ?>>
                                        Hembra</option>
                                    <option value="Unknown" <?= (!isset($old['sex']) || $old['sex'] == 'Unknown') ? 'selected' : '' ?>>Desconocido</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Fecha de nacimiento</label>
                                <input type="date" name="date_of_birth" class="form-control"
                                    value="<?= htmlspecialchars($old['date_of_birth'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Peso (kg)</label>
                                <input type="number" step="0.01" name="current_weight" class="form-control"
                                    value="<?= htmlspecialchars($old['current_weight'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Color</label>
                                <input type="text" name="color" class="form-control"
                                    value="<?= htmlspecialchars($old['color'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Microchip</label>
                                <input type="text" name="microchip" class="form-control"
                                    value="<?= htmlspecialchars($old['microchip'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Alergias</label>
                                <input type="text" name="allergies" class="form-control"
                                    value="<?= htmlspecialchars($old['allergies'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label fw-bold small">Observaciones</label>
                                <textarea name="observations" class="form-control"
                                    rows="2"><?= htmlspecialchars($old['observations'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label fw-bold small">Foto de la mascota</label>
                                <input type="file" name="picture" class="form-control" accept="image/*">
                                <small class="text-muted">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 2MB</small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-4">
                            <a href="<?= BASE_URL ?>pets.php" class="btn btn-outline-secondary px-4">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-5 shadow-sm">
                                <i class="bi bi-save me-2"></i>Guardar Mascota
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>