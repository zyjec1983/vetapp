<?php
/**
 * Location: vetapp/app/views/medications/index.php
 * Listado de medicamentos con filtro por tipo y paginación
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
                <div class="d-flex gap-2">
                    <!-- Botón para nuevo medicamento/accesorio -->
                    <a href="<?= BASE_URL ?>medications.php?action=create" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> Nuevo Producto
                    </a>
                </div>
            </div>

            <!-- Filtro por tipo (dropdown) -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="<?= BASE_URL ?>medications.php" class="row g-3 align-items-end">
                            
                    <!-- ********** GENERA TOKEN ********** -->    
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Filtrar por tipo</label>
                            <select name="type" class="form-select" onchange="this.form.submit()">
                                <option value="">-- Seleccione --</option>
                                <option value="medicamentos" <?= ($_GET['type'] ?? '') == 'medicamentos' ? 'selected' : '' ?>>Medicamentos</option>
                                <option value="accesorios" <?= ($_GET['type'] ?? '') == 'accesorios' ? 'selected' : '' ?>>
                                    Accesorios y otros</option>
                                <option value="todos" <?= ($_GET['type'] ?? '') == 'todos' ? 'selected' : '' ?>>Todos
                                </option>
                            </select>
                        </div>
                        <?php if (isset($_GET['type']) && $_GET['type'] !== ''): ?>
                            <div class="col-md-2">
                                <a href="<?= BASE_URL ?>medications.php" class="btn btn-outline-secondary w-100">Limpiar
                                    filtro</a>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Contenido principal: mensaje o tabla -->
            <?php if (!isset($_GET['type']) || $_GET['type'] === ''): ?>
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle fs-4"></i>
                    <p class="mb-0">Seleccione una categoría para ver los productos.</p>
                </div>
            <?php elseif (empty($medications)): ?>
                <div class="alert alert-warning text-center">
                    <i class="bi bi-exclamation-triangle fs-4"></i>
                    <p class="mb-0">No se encontraron productos en esta categoría.</p>
                </div>
            <?php else: ?>
                <!-- Tabla de productos -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Tipo</th>
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
                                            <td>
                                                <?php if (in_array($med->getCategory(), ['Accesorios y otros', 'Accesorio'])): ?>
                                                    <span class="badge bg-success">Accesorio</span>
                                                <?php else: ?>
                                                    <span class="badge bg-primary">Medicamento</span>
                                                <?php endif; ?>
                                            </td>
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
                                                <a href="<?= BASE_URL ?>medications.php?action=show&id=<?= $med->getIdMedication() ?>"
                                                    class="btn btn-sm btn-info" title="Ver detalle">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="<?= BASE_URL ?>medications.php?action=edit&id=<?= $med->getIdMedication() ?>"
                                                    class="btn btn-sm btn-primary" title="Editar">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-warning btn-deactivate"
                                                    data-id="<?= $med->getIdMedication() ?>"
                                                    data-name="<?= htmlspecialchars($med->getName()) ?>">
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

                <!-- Paginación -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Paginación" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?type=<?= urlencode($type) ?>&page=<?= $page - 1 ?>">Anterior</a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled"><span class="page-link">Anterior</span></li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?type=<?= urlencode($type) ?>&page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?type=<?= urlencode($type) ?>&page=<?= $page + 1 ?>">Siguiente</a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled"><span class="page-link">Siguiente</span></li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>
</div>

<script>
    // SweetAlert para desactivar (igual que antes)
    document.querySelectorAll('.btn-deactivate').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const id = this.dataset.id;
            const name = this.dataset.name;
            Swal.fire({
                title: '¿Desactivar producto?',
                text: `El producto "${name}" quedará inactivo y se moverá a la lista de inactivos.`,
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