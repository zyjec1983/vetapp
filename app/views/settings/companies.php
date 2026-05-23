<?php
/**
 * Location: vetapp/app/views/settings/companies.php
 *
 * Vista CRUD para gestionar empresas/RUCs de facturación electrónica.
 */

$title = 'Configuración de Empresas | VetApp';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../layouts/aside.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h2><i class="bi bi-buildings me-2"></i>Empresas / RUCs</h2>
                <a href="<?= BASE_URL ?>settings.php?action=create" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Nueva Empresa
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
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>RUC</th>
                                    <th>Nombre Comercial</th>
                                    <th class="d-none d-md-table-cell">Dirección</th>
                                    <th>Ambiente</th>
                                    <th>Certificado</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($companies)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                            No hay empresas registradas.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($companies as $c): ?>
                                        <tr>
                                            <td><code class="fw-bold"><?= htmlspecialchars($c['ruc']) ?></code></td>
                                            <td>
                                                <strong><?= htmlspecialchars($c['commercial_name']) ?></strong>
                                                <br><small class="text-muted"><?= htmlspecialchars($c['business_name'] ?? '') ?></small>
                                            </td>
                                            <td class="d-none d-md-table-cell text-truncate" style="max-width:200px;">
                                                <?= htmlspecialchars($c['address'] ?? '-') ?>
                                            </td>
                                            <td>
                                                <?php if (($c['ambiente'] ?? 'pruebas') === 'pruebas'): ?>
                                                    <span class="badge bg-warning text-dark"><i class="bi bi-flask me-1"></i>Pruebas</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success"><i class="bi bi-globe me-1"></i>Producción</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($c['certificate_path']) && file_exists($c['certificate_path'])): ?>
                                                    <span class="badge bg-success"><i class="bi bi-shield-lock-fill me-1"></i>Instalado</span>
                                                    <a href="<?= BASE_URL ?>settings.php?action=deleteCert&id=<?= (int)$c['id'] ?>"
                                                       class="text-danger ms-1" title="Eliminar certificado"
                                                       onclick="return confirm('¿Eliminar este certificado?')">
                                                        <i class="bi bi-trash small"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><i class="bi bi-shield-x me-1"></i>Sin certificado</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($c['activo']): ?>
                                                    <span class="badge bg-success">Activa</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactiva</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1 flex-wrap">
                                                    <a href="<?= BASE_URL ?>settings.php?action=edit&id=<?= (int)$c['id'] ?>"
                                                       class="btn btn-sm btn-outline-primary" title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <?php if ($c['activo']): ?>
                                                        <a href="<?= BASE_URL ?>settings.php?action=deactivate&id=<?= (int)$c['id'] ?>"
                                                           class="btn btn-sm btn-outline-warning btn-deactivate-company"
                                                           data-name="<?= htmlspecialchars($c['commercial_name']) ?>"
                                                           title="Desactivar">
                                                            <i class="bi bi-toggle-off"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="<?= BASE_URL ?>settings.php?action=delete&id=<?= (int)$c['id'] ?>"
                                                       class="btn btn-sm btn-outline-danger btn-delete-company"
                                                       data-name="<?= htmlspecialchars($c['commercial_name']) ?>"
                                                       title="Eliminar empresa">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                    <?php if (!empty($c['certificate_path']) && file_exists($c['certificate_path'])): ?>
                                                        <button class="btn btn-sm btn-outline-secondary"
                                                                onclick="testSriConnection(<?= (int)$c['id'] ?>)"
                                                                title="Probar conexión SRI">
                                                            <i class="bi bi-wifi"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php if (!empty($companies)): ?>
                <div class="text-end mt-3">
                    <a href="<?= BASE_URL ?>settings.php?action=deleted" class="text-muted small">
                        <i class="bi bi-archive me-1"></i>Ver empresas eliminadas
                    </a>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-deactivate-company').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const href = this.href;
            const name = this.dataset.name;
            Swal.fire({
                title: '¿Desactivar empresa?',
                html: `La empresa <strong>"${name}"</strong> quedará inactiva y no podrá facturar hasta que se reactive.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, desactivar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            });
        });
    });

    document.querySelectorAll('.btn-delete-company').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const href = this.href;
            const name = this.dataset.name;
            Swal.fire({
                title: '¿Eliminar empresa?',
                html: `Se eliminará <strong>"${name}"</strong> del listado principal.<br><small class="text-muted">Los datos se conservan en la base de datos y puede restaurarse después.</small>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            });
        });
    });
});

function testSriConnection(companyId) {
    Swal.fire({
        title: 'Probando conexión SRI...',
        html: 'Conectando con el servicio web del SRI',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    fetch('<?= BASE_URL ?>settings.php?action=testConnection', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `company_id=${companyId}&csrf_token=<?= generateCSRFToken() ?>`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Conexión Exitosa',
                html: `<strong>${data.message}</strong><br><small>WSDL: <code>${data.wsdl}</code></small>`,
                confirmButtonText: 'OK'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error de Conexión',
                text: data.error,
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(err => {
        Swal.fire('Error', 'No se pudo conectar: ' + err.message, 'error');
    });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
