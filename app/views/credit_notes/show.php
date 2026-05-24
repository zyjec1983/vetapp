<?php
/**
 * Location: vetapp/app/views/credit_notes/show.php
 *
 * Detalle de una Nota de Crédito con estado SRI.
 */

$title = 'Nota de Crédito | VetApp';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../layouts/aside.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>credit_notes.php">Notas de Crédito</a></li>
                    <li class="breadcrumb-item active">NC #<?= $creditNote['id'] ?></li>
                </ol>
            </nav>

            <div class="card shadow-sm">
                <div class="card-header <?= $creditNote['estado_sri'] === 'autorizada' ? 'bg-success' : ($creditNote['estado_sri'] === 'rechazada' ? 'bg-danger' : 'bg-warning') ?> text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-arrow-return-left me-2"></i>
                        Nota de Crédito <?= htmlspecialchars($creditNote['numero_nota_credito'] ?? 'Pendiente') ?>
                        <?php if ($creditNote['estado_sri'] === 'autorizada'): ?>
                            <span class="badge bg-light text-dark ms-2"><i class="bi bi-check-circle-fill text-success me-1"></i>Autorizada</span>
                        <?php elseif ($creditNote['estado_sri'] === 'rechazada'): ?>
                            <span class="badge bg-light text-dark ms-2"><i class="bi bi-x-circle-fill text-danger me-1"></i>Rechazada</span>
                        <?php else: ?>
                            <span class="badge bg-light text-dark ms-2"><i class="bi bi-clock-fill text-warning me-1"></i><?= ucfirst($creditNote['estado_sri']) ?></span>
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><strong>Venta:</strong>
                                <a href="<?= BASE_URL ?>sales.php?action=show&id=<?= $creditNote['id_sale'] ?>">
                                    <?= htmlspecialchars($creditNote['sale_code'] ?? '') ?>
                                </a>
                            </p>
                            <p><strong>Factura original:</strong> <?= htmlspecialchars($creditNote['factura_original'] ?? '') ?></p>
                            <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($creditNote['created_at'])) ?></p>
                            <p><strong>Motivo:</strong> <?= htmlspecialchars($creditNote['motivo']) ?></p>
                            <p><strong>Empresa:</strong> <?= htmlspecialchars($creditNote['company_name'] ?? '') ?></p>
                            <p><strong>RUC:</strong> <?= htmlspecialchars($creditNote['company_ruc'] ?? '') ?></p>
                        </div>
                        <div class="col-md-6">
                            <?php if ($creditNote['clave_acceso']): ?>
                                <p><strong>Clave de acceso:</strong><br>
                                    <small class="text-muted word-break"><?= htmlspecialchars($creditNote['clave_acceso']) ?></small>
                                </p>
                            <?php endif; ?>
                            <?php if ($creditNote['numero_autorizacion']): ?>
                                <p><strong>Nº Autorización:</strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($creditNote['numero_autorizacion']) ?></small>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <h6>Productos devueltos</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unitario</th>
                                    <th>Subtotal</th>
                                    <th>IVA</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($creditNote['details'] as $det): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($det['medication_name']) ?></td>
                                        <td><?= (int)$det['quantity'] ?></td>
                                        <td>$<?= number_format((float)$det['unit_price'], 2) ?></td>
                                        <td>$<?= number_format((float)$det['subtotal'], 2) ?></td>
                                        <td>$<?= number_format((float)$det['tax_amount'], 2) ?></td>
                                        <td class="fw-bold">$<?= number_format((float)$det['total'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="row justify-content-end mt-3">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <p class="d-flex justify-content-between"><span>Subtotal:</span>
                                        <span>$<?= number_format((float)$creditNote['subtotal'], 2) ?></span>
                                    </p>
                                    <p class="d-flex justify-content-between"><span>IVA:</span>
                                        <span>$<?= number_format((float)$creditNote['tax_total'], 2) ?></span>
                                    </p>
                                    <hr>
                                    <h5 class="d-flex justify-content-between">
                                        <span>Total NC:</span>
                                        <span class="text-danger">-$<?= number_format((float)$creditNote['total'], 2) ?></span>
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($creditNote['mensaje_sri']): ?>
                        <div class="alert alert-info mt-3">
                            <strong>Respuesta SRI:</strong><br>
                            <small><?= htmlspecialchars($creditNote['mensaje_sri']) ?></small>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card-footer">
                    <a href="<?= BASE_URL ?>credit_notes.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Volver
                    </a>
                    <a href="<?= BASE_URL ?>sales.php?action=show&id=<?= $creditNote['id_sale'] ?>" class="btn btn-outline-info">
                        <i class="bi bi-receipt me-1"></i> Ver Venta
                    </a>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
