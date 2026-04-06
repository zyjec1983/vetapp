<?php
$title = 'Medicamentos Inactivos | VetApp';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../layouts/aside.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Medicamentos Inactivos</h2>
                <a href="<?= BASE_URL ?>medications.php" class="btn btn-secondary">Ver Activos</a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Categoría</th>
                                    <th>Stock</th>
                                    <th>Precio</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($medications as $med): ?>
                                <tr>
                                    <td><?= htmlspecialchars($med->getCode()) ?></td>
                                    <td><?= htmlspecialchars($med->getName()) ?></td>
                                    <td><?= htmlspecialchars($med->getCategory()) ?></td>
                                    <td><?= $med->getStockTotal() ?></td>
                                    <td>$<?= number_format($med->getSalePrice(), 2) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-success btn-reactivate" data-id="<?= $med->getIdMedication() ?>" data-name="<?= htmlspecialchars($med->getName()) ?>">
                                            <i class="bi bi-arrow-repeat"></i> Reactivar
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
    document.querySelectorAll('.btn-reactivate').forEach(button => {
        button.addEventListener('click', function(e) {
            const id = this.dataset.id;
            const name = this.dataset.name;
            Swal.fire({
                title: '¿Reactivar medicamento?',
                text: `El medicamento "${name}" volverá a estar disponible.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, reactivar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '<?= BASE_URL ?>medications.php?action=reactivate&id=' + id;
                }
            });
        });
    });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>