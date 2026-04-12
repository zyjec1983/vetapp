<?php
$title = 'Servicios | VetApp';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../layouts/aside.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Gestión de Servicios</h2>
                <a href="<?= BASE_URL ?>services.php?action=create" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Nuevo Servicio
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Precio</th>
                                    <th>IVA</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($services as $service): ?>
                                    <?php if (!$service->getActive()) continue; ?>
                                    <tr>
                                        <td><?= $service->getIdService() ?></td>
                                        <td><?= htmlspecialchars($service->getName()) ?></td>
                                        <td><?= nl2br(htmlspecialchars($service->getDescription())) ?></td>
                                        <td>$<?= number_format($service->getPrice(), 2) ?></td>
                                        <td><?= $service->getTaxable() ? 'Sí' : 'No' ?></td>
                                        <td><?= $service->getActive() ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>' ?></td>
                                        <td>
                                            <a href="<?= BASE_URL ?>services.php?action=edit&id=<?= $service->getIdService() ?>" class="btn btn-sm btn-primary">Editar</a>
                                            <button type="button" class="btn btn-sm btn-warning btn-deactivate" data-id="<?= $service->getIdService() ?>" data-name="<?= htmlspecialchars($service->getName()) ?>">Desactivar</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    document.querySelectorAll('.btn-deactivate').forEach(button => {
        button.addEventListener('click', function(e) {
            const id = this.dataset.id;
            const name = this.dataset.name;
            Swal.fire({
                title: '¿Desactivar servicio?',
                text: `El servicio "${name}" quedará inactivo.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, desactivar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '<?= BASE_URL ?>services.php?action=deactivate&id=' + id;
                }
            });
        });
    });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>