<?php
/**
 * Location: vetapp/app/views/sales/show.php
 * Detalle de una venta
 */

$title = 'Detalle de Venta | VetApp';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../layouts/aside.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>sales.php">Ventas</a></li>
                    <li class="breadcrumb-item active">Detalle Venta #<?= $saleData['sale']['id_sale'] ?></li>
                </ol>
            </nav>

            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Venta <?= htmlspecialchars($saleData['sale']['sale_code']) ?></h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($saleData['sale']['sale_date'])) ?></p>
                            <p><strong>Cliente:</strong> <?= htmlspecialchars($saleData['sale']['client_name'] ?? 'Consumidor final') ?></p>
                            <p><strong>Atendido por:</strong> Usuario ID <?= $saleData['sale']['id_user'] ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Estado:</strong> 
                                <?php if ($saleData['sale']['status'] == 'paid'): ?>
                                    <span class="badge bg-success">Pagada</span>
                                <?php elseif ($saleData['sale']['status'] == 'cancelled'): ?>
                                    <span class="badge bg-danger">Cancelada</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">Pendiente</span>
                                <?php endif; ?>
                            </p>
                            <p><strong>Método de pago:</strong> <?= ucfirst($saleData['sale']['payment_method']) ?></p>
                            <p><strong>Observaciones:</strong> <?= nl2br(htmlspecialchars($saleData['sale']['observations'] ?? '')) ?></p>
                        </div>
                    </div>

                    <h6>Productos</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unitario</th>
                                    <th>Subtotal</th>
                                    <th>IVA</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($saleData['details'] as $det): ?>
                                <tr>
                                    <td><?= htmlspecialchars($det['medication_name']) ?></td>
                                    <td><?= $det['quantity'] ?></td>
                                    <td>$<?= number_format($det['unit_price'], 2) ?></td>
                                    <td>$<?= number_format($det['subtotal'], 2) ?></td>
                                    <td>$<?= number_format($det['tax_amount'], 2) ?></td>
                                    <td class="fw-bold">$<?= number_format($det['total'], 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="row justify-content-end mt-3">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <p class="d-flex justify-content-between"><span>Subtotal:</span> <span>$<?= number_format($saleData['sale']['subtotal'], 2) ?></span></p>
                                    <p class="d-flex justify-content-between"><span>Descuento:</span> <span>$<?= number_format($saleData['sale']['discount'], 2) ?></span></p>
                                    <p class="d-flex justify-content-between"><span>IVA:</span> <span>$<?= number_format($saleData['sale']['tax_total'], 2) ?></span></p>
                                    <hr>
                                    <h5 class="d-flex justify-content-between"><span>Total:</span> <span class="text-primary">$<?= number_format($saleData['sale']['total'], 2) ?></span></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="<?= BASE_URL ?>sales.php" class="btn btn-secondary">Volver</a>
                    <?php if ($saleData['sale']['status'] != 'cancelled'): ?>
                        <a href="<?= BASE_URL ?>sales.php?action=cancel&id=<?= $saleData['sale']['id_sale'] ?>" class="btn btn-danger" onclick="return confirm('¿Cancelar esta venta?')">Cancelar Venta</a>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>