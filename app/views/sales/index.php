<?php
$title = 'Ventas | VetApp';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';

$from = $_GET['from'] ?? date('Y-m-01');
$to = $_GET['to'] ?? date('Y-m-d');
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../layouts/aside.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h2><i class="bi bi-cart-check me-2"></i>Gestión de Ventas</h2>
                <a href="<?= BASE_URL ?>sales.php?action=create" class="btn btn-primary">
                    <i class="bi bi-cart-plus me-1"></i> Nueva Venta
                </a>
            </div>

            <!-- Filtro de Fechas -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-6 col-sm-4 col-md-2">
                            <label class="form-label small fw-bold mb-1">Desde</label>
                            <input type="date" name="from" class="form-control form-control-sm" value="<?= htmlspecialchars($from) ?>">
                        </div>
                        <div class="col-6 col-sm-4 col-md-2">
                            <label class="form-label small fw-bold mb-1">Hasta</label>
                            <input type="date" name="to" class="form-control form-control-sm" value="<?= htmlspecialchars($to) ?>">
                        </div>
                        <div class="col-12 col-sm-4 col-md-auto">
                            <button type="submit" class="btn btn-sm btn-primary w-100">
                                <i class="bi bi-funnel me-1"></i> Filtrar
                            </button>
                        </div>
                        <div class="col-12 col-sm-12 col-md-auto">
                            <a href="<?= BASE_URL ?>sales.php" class="btn btn-sm btn-outline-secondary w-100">
                                <i class="bi bi-arrow-clockwise me-1"></i> Este Mes
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Resumen -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-lg-3">
                    <div class="card shadow-sm border-start border-4 border-primary">
                        <div class="card-body">
                            <small class="text-muted fw-bold">Total Ventas</small>
                            <h4 class="mb-0"><?= (int)$summary['total_sales'] ?></h4>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card shadow-sm border-start border-4 border-success">
                        <div class="card-body">
                            <small class="text-muted fw-bold">Monto Total</small>
                            <h4 class="mb-0">$<?= number_format($summary['total_amount'], 2) ?></h4>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card shadow-sm border-start border-4 border-info">
                        <div class="card-body">
                            <small class="text-muted fw-bold">Subtotal</small>
                            <h4 class="mb-0">$<?= number_format($summary['subtotal_amount'], 2) ?></h4>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card shadow-sm border-start border-4 border-warning">
                        <div class="card-body">
                            <small class="text-muted fw-bold">IVA</small>
                            <h4 class="mb-0">$<?= number_format($summary['tax_amount'], 2) ?></h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla -->
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <th>ID</th>
                                <th>Código</th>
                                <th>Fecha</th>
                                <th class="d-none d-sm-table-cell">Cliente</th>
                                <th class="d-none d-md-table-cell">Usuario</th>
                                <th>Total</th>
                                <th class="d-none d-sm-table-cell">Estado</th>
                                <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($sales)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            No hay ventas en el período seleccionado.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($sales as $sale): ?>
                                        <tr>
                                            <td><?= $sale['id_sale'] ?></td>
                                            <td><code><?= htmlspecialchars($sale['sale_code']) ?></code></td>
                                            <td><?= date('d/m/Y H:i', strtotime($sale['sale_date'])) ?></td>
                                            <td class="d-none d-sm-table-cell"><?= htmlspecialchars($sale['client_name'] ?? 'Consumidor final') ?></td>
                                            <td class="d-none d-md-table-cell"><?= htmlspecialchars($sale['user_name'] ?? '-') ?></td>
                                            <td><strong>$<?= number_format($sale['total'], 2) ?></strong></td>
                                            <td class="d-none d-sm-table-cell">
                                                <?php if ($sale['status'] == 'paid'): ?>
                                                    <span class="badge bg-success">Pagada</span>
                                                <?php elseif ($sale['status'] == 'cancelled'): ?>
                                                    <span class="badge bg-danger">Cancelada</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Pendiente</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="<?= BASE_URL ?>sales.php?action=show&id=<?= $sale['id_sale'] ?>" class="btn btn-sm btn-info" title="Ver detalle">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <?php if ($sale['status'] != 'cancelled'): ?>
                                                    <button type="button" class="btn btn-sm btn-warning btn-cancel" data-id="<?= $sale['id_sale'] ?>" data-code="<?= $sale['sale_code'] ?>" title="Cancelar">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.querySelectorAll('.btn-cancel').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const code = this.dataset.code;
        Swal.fire({
            title: '¿Cancelar venta?',
            text: `La venta ${code} se marcará como cancelada.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '<?= BASE_URL ?>sales.php?action=cancel&id=' + id;
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
