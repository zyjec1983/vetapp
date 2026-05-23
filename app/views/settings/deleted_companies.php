<?php
/**
 * Location: vetapp/app/views/settings/deleted_companies.php
 *
 * Vista: Listado de empresas eliminadas (soft delete) con opción de restaurar.
 */

$title = 'Empresas Eliminadas | VetApp';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../layouts/aside.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h2><i class="bi bi-archive me-2"></i>Empresas Eliminadas</h2>
                <a href="<?= BASE_URL ?>settings.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Volver a Empresas
                </a>
            </div>

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
                <div class="card-body p-0">
                    <?php if (empty($companies)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                            No hay empresas eliminadas.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>RUC</th>
                                        <th>Nombre Comercial</th>
                                        <th class="d-none d-md-table-cell">Razón Social</th>
                                        <th>Eliminada</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($companies as $c): ?>
                                        <tr>
                                            <td><code class="fw-bold"><?= htmlspecialchars($c['ruc']) ?></code></td>
                                            <td>
                                                <strong><?= htmlspecialchars($c['commercial_name']) ?></strong>
                                            </td>
                                            <td class="d-none d-md-table-cell">
                                                <?= htmlspecialchars($c['business_name'] ?? '-') ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-trash me-1"></i><?= date('d/m/Y H:i', strtotime($c['deleted_at'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= BASE_URL ?>settings.php?action=restore&id=<?= (int)$c['id'] ?>"
                                                   class="btn btn-sm btn-outline-success btn-restore-company"
                                                   data-name="<?= htmlspecialchars($c['commercial_name']) ?>"
                                                   title="Restaurar empresa">
                                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Restaurar
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-restore-company').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const href = this.href;
            const name = this.dataset.name;
            Swal.fire({
                title: '¿Restaurar empresa?',
                html: `La empresa <strong>"${name}"</strong> volverá al listado principal.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, restaurar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            });
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
