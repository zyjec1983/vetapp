<?php
/**
 * Location: vetapp/app/views/medications/index.php
 * Listado de medicamentos con stock calculado
 */

$title = 'Medicamentos | VetApp';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../layouts/aside.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Gestión de Medicamentos</h2>
                <a href="<?= BASE_URL ?>medications.php?action=create" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Nuevo Medicamento
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-dark">
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Principio Activo</th>
                                    <th>Categoría</th>
                                    <th>Stock</th>
                                    <th>Mínimo</th>
                                    <th>Precio Venta</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($medications as $med): ?>
                                    <?php $isLowStock = $med->getStockTotal() <= $med->getMinimumStock(); ?>
                                    <tr>
                                        <td><?= htmlspecialchars($med->getCode()) ?></td>
                                        <td><?= htmlspecialchars($med->getName()) ?></td>
                                        <td><?= htmlspecialchars($med->getActiveName() ?: 'N/A') ?></td>
                                        <td><?= htmlspecialchars($med->getCategory()) ?></td>
                                        <td class="<?= $isLowStock ? 'text-danger fw-bold' : '' ?>">
                                            <?= $med->getStockTotal() ?>
                                            <?php if ($isLowStock): ?>
                                                <span class="badge bg-danger">Crítico</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $med->getMinimumStock() ?></td>
                                        <td>$<?= number_format($med->getSalePrice(), 2) ?></td>
                                        <td class="text-center">
                                            <a href="<?= BASE_URL ?>medications.php?action=show&id=<?= $med->getIdMedication() ?>" class="btn btn-sm btn-info" title="Ver detalle">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>medications.php?action=edit&id=<?= $med->getIdMedication() ?>" class="btn btn-sm btn-primary" title="Editar">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-warning btn-deactivate" data-id="<?= $med->getIdMedication() ?>" data-name="<?= htmlspecialchars($med->getName()) ?>">
                                                <i class="bi bi-archive"></i> Desactivar
                                            </button>
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
            e.preventDefault();
            const id = this.dataset.id;
            const name = this.dataset.name;
            Swal.fire({
                title: '¿Desactivar medicamento?',
                text: `El medicamento "${name}" quedará inactivo y no aparecerá en la lista principal.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, desactivar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '<?= BASE_URL ?>medications.php?action=deactivate&id=' + id;
                }
            });
        });
    });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>