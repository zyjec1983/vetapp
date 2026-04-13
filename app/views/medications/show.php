<?php
/**
 * Location: vetapp/app/views/medications/show.php
 * Detalle de un medicamento y gestión de lotes
 */

$title = 'Detalle de Medicamento | VetApp';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../layouts/aside.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>medications.php">Medicamentos</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($medication->getName() ?? '') ?></li>
                </ol>
            </nav>

            <div class="row">
                <div class="col-lg-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Información General</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Código</th>
                                    <td><?= htmlspecialchars($medication->getCode() ?? '') ?></td>
                                </tr>
                                <tr>
                                    <th>Nombre</th>
                                    <td><?= htmlspecialchars($medication->getName() ?? '') ?></td>
                                </tr>
                                <tr>
                                    <th>Principio Activo</th>
                                    <td><?= htmlspecialchars($medication->getActiveName() ?: 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <th>Categoría</th>
                                    <td><?= htmlspecialchars($medication->getCategory() ?? '') ?></td>
                                </tr>
                                <tr>
                                    <th>Descripción</th>
                                    <td><?= nl2br(htmlspecialchars($medication->getDescription() ?? '')) ?></td>
                                </tr>
                                <tr>
                                    <th>Ubicación</th>
                                    <td><?= htmlspecialchars($medication->getLocation() ?? '') ?></td>
                                </tr>
                                <tr>
                                    <th>Stock Actual</th>
                                    <td><strong><?= $medication->getStockTotal() ?></strong></td>
                                </tr>
                                <tr>
                                    <th>Stock Mínimo</th>
                                    <td><?= $medication->getMinimumStock() ?></td>
                                </tr>
                                <tr>
                                    <th>Precio Venta</th>
                                    <td>$<?= number_format($medication->getSalePrice(), 2) ?></td>
                                </tr>
                                <tr>
                                    <th>Estado</th>
                                    <td><?= $medication->getActive() ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>' ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="card-footer">
                            <a href="<?= BASE_URL ?>medications.php?action=edit&id=<?= $medication->getIdMedication() ?>"
                                class="btn btn-primary">Editar</a>
                            <a href="<?= BASE_URL ?>medications.php" class="btn btn-secondary">Volver</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card shadow-sm mb-4">
                        <div
                            class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Lotes</h5>
                            <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal"
                                data-bs-target="#addBatchModal">
                                <i class="bi bi-plus-lg"></i> Agregar Lote
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($batches)): ?>
                                <p class="text-muted text-center p-3">No hay lotes registrados.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Lote</th>
                                                <th>Caducidad</th>
                                                <th>Recibido</th>
                                                <th>Disponible</th>
                                                <th>Precio Compra</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($batches as $batch): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($batch->getBatchNumber() ?? '') ?></td>
                                                    <td><?= date('d/m/Y', strtotime($batch->getExpirationDate())) ?></td>
                                                    <td><?= $batch->getQuantityReceived() ?></td>
                                                    <td><?= $batch->getQuantityRemaining() ?></td>
                                                    <td>$<?= number_format($batch->getPurchasePrice(), 2) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal para agregar lote (sin cambios) -->
<div class="modal fade" id="addBatchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Agregar Lote</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>medications.php?action=addBatch" method="POST">

                <!-- ********** GENERA TOKEN ********** -->
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                <input type="hidden" name="id_medication" value="<?= $medication->getIdMedication() ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Número de Lote *</label>
                        <input type="text" name="batch_number" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha de Expiración *</label>
                        <input type="date" name="expiration_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cantidad Recibida *</label>
                        <input type="number" name="quantity_received" class="form-control" required min="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Precio de Compra ($)</label>
                        <input type="number" step="0.01" name="purchase_price" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Guardar Lote</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['errors'])): ?>
    <script>
        Swal.fire({ icon: 'error', title: 'Error', text: '<?= implode('\n', $_SESSION['errors']) ?>' });
    </script>
    <?php unset($_SESSION['errors']); ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>