<?php
/**
 * Location: vetapp/app/views/settings/personalization.php
 *
 * Vista: Personalización de título y logo por empresa.
 */

$title = 'Personalización | VetApp';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../layouts/aside.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>settings.php">Configuración</a></li>
                    <li class="breadcrumb-item active">Personalización</li>
                </ol>
            </nav>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle-fill me-1"></i><?= htmlspecialchars($_SESSION['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i><?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark d-flex align-items-center gap-2 py-2">
                    <i class="bi bi-palette fs-5"></i>
                    <span class="fw-semibold">Personalización</span>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Personalice el título y logo de cada empresa. El título se muestra en el navbar del sistema y el logo aparece junto al nombre.
                        <br><small>Logo: imágenes JPG, PNG, GIF o WebP, máximo 1MB.</small>
                    </p>

                    <form method="POST" action="<?= BASE_URL ?>settings.php?action=personalization" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-warning">
                                    <tr>
                                        <th width="40">#</th>
                                        <th>Empresa</th>
                                        <th style="min-width:180px">Título del sistema</th>
                                        <th style="min-width:200px">Logo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($companies)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">
                                                <i class="bi bi-building-slash me-1"></i>No hay empresas registradas.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($companies as $i => $c): ?>
                                            <tr>
                                                <td class="text-muted"><?= $i + 1 ?></td>
                                                <td>
                                                    <span class="fw-semibold"><?= htmlspecialchars($c['commercial_name']) ?></span>
                                                    <br><small class="text-muted"><?= htmlspecialchars($c['ruc']) ?></small>
                                                </td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text"><i class="bi bi-fonts"></i></span>
                                                        <input type="text" class="form-control"
                                                               name="title[<?= (int)$c['id'] ?>]"
                                                               value="<?= htmlspecialchars($c['app_title'] ?? '') ?>"
                                                               placeholder="Ej: Mi Veterinaria" maxlength="100">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <?php if (!empty($c['logo_path']) && file_exists($c['logo_path'])): ?>
                                                            <img src="<?= BASE_URL ?>../storage/logos/<?= basename($c['logo_path']) ?>"
                                                                 alt="Logo" style="height:36px;width:auto;border-radius:4px;"
                                                                 onerror="this.style.display='none'">
                                                        <?php endif; ?>
                                                        <input type="file" class="form-control form-control-sm"
                                                               name="logo[<?= (int)$c['id'] ?>]"
                                                               accept="image/jpeg,image/png,image/gif,image/webp">
                                                        <input type="hidden" name="logo_existing[<?= (int)$c['id'] ?>]"
                                                               value="<?= htmlspecialchars($c['logo_path'] ?? '') ?>">
                                                        <?php if (!empty($c['logo_path'])): ?>
                                                            <label class="small text-muted">
                                                                <input type="checkbox" name="remove_logo[<?= (int)$c['id'] ?>]" value="1">
                                                                Eliminar
                                                            </label>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (!empty($companies)): ?>
                            <div class="mt-3 d-flex gap-2">
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-check-lg me-1"></i> Guardar cambios
                                </button>
                                <a href="<?= BASE_URL ?>settings.php" class="btn btn-outline-secondary">
                                    Cancelar
                                </a>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
