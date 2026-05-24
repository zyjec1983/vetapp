<?php
/**
 * Location: vetapp/app/views/settings/whatsapp.php
 *
 * Vista: Configuración de números WhatsApp por empresa.
 */

$title = 'WhatsApp | VetApp';
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
                    <li class="breadcrumb-item active">WhatsApp</li>
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
                <div class="card-header bg-success text-white d-flex align-items-center gap-2 py-2">
                    <i class="bi bi-whatsapp fs-5"></i>
                    <span class="fw-semibold">Configuración WhatsApp</span>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Ingrese el número de WhatsApp de cada empresa. Este número se usará como contacto en facturas, recordatorios y mensajes a clientes.
                        <br><small>Ingrese solo dígitos, sin 0 ni +593 (ej: 999999999).</small>
                    </p>

                    <form method="POST" action="<?= BASE_URL ?>settings.php?action=whatsapp">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-success">
                                    <tr>
                                        <th width="40">#</th>
                                        <th>Empresa</th>
                                        <th>RUC</th>
                                        <th style="min-width:200px">Número WhatsApp</th>
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
                                                    <br><small class="text-muted"><?= htmlspecialchars($c['business_name']) ?></small>
                                                </td>
                                                <td><code><?= htmlspecialchars($c['ruc']) ?></code></td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">+593</span>
                                                        <input type="text" class="form-control phone-input"
                                                               name="whatsapp[<?= (int)$c['id'] ?>]"
                                                               value="<?= htmlspecialchars($c['whatsapp_phone'] ?? '') ?>"
                                                               placeholder="999999999" maxlength="10">
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
                                <button type="submit" class="btn btn-success">
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

<script>
document.querySelectorAll('.phone-input').forEach(input => {
    input.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '');
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
