<?php
/**
 * Location: vetapp/app/views/sales/show.php
 * Detalle de una venta con facturación electrónica SRI
 */

$title = 'Detalle de Venta | VetApp';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';

// Verificar si tiene factura electrónica
require_once __DIR__ . '/../../repositories/ElectronicInvoiceRepository.php';
$invoiceRepo = new ElectronicInvoiceRepository();
$invoice = $invoiceRepo->findBySaleId($saleData['sale']['id_sale']);

// Verificar notas de crédito
require_once __DIR__ . '/../../repositories/CreditNoteRepository.php';
$cnRepo = new CreditNoteRepository();
$creditNotes = $cnRepo->findBySaleId($saleData['sale']['id_sale']);
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
                            <p><strong>Fecha:</strong>
                                <?= date('d/m/Y H:i', strtotime($saleData['sale']['sale_date'])) ?></p>
                            <p><strong>Cliente:</strong>
                                <?php
                                $clientName = $saleData['sale']['client_name'] ?? 'Consumidor Final';
                                $clientIdent = $saleData['sale']['client_identification'] ?? '';
                                $isCF = ($clientIdent === '9999999999999' || stripos($clientName, 'consumidor') !== false);
                                ?>
                                <?php if ($isCF): ?>
                                    <span class="badge bg-warning text-dark">CONSUMIDOR FINAL</span>
                                    <br><small class="text-muted">RUC: 9999999999999</small>
                                <?php else: ?>
                                    <?= htmlspecialchars($clientName) ?>
                                    <?php if ($clientIdent): ?>
                                        <br><small class="text-muted">ID: <?= htmlspecialchars($clientIdent) ?></small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </p>
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
                            <p><strong>Observaciones:</strong>
                                <?= nl2br(htmlspecialchars($saleData['sale']['observations'] ?? '')) ?></p>
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
                                    <p class="d-flex justify-content-between"><span>Subtotal:</span>
                                        <span>$<?= number_format($saleData['sale']['subtotal'], 2) ?></span>
                                    </p>
                                    <p class="d-flex justify-content-between"><span>Descuento:</span>
                                        <span>$<?= number_format($saleData['sale']['discount'], 2) ?></span>
                                    </p>
                                    <p class="d-flex justify-content-between"><span>IVA:</span>
                                        <span>$<?= number_format($saleData['sale']['tax_total'], 2) ?></span>
                                    </p>
                                    <hr>
                                    <h5 class="d-flex justify-content-between"><span>Total:</span> <span
                                            class="text-primary">$<?= number_format($saleData['sale']['total'], 2) ?></span>
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <!-- Barra superior: Volver + Cancelar -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <a href="<?= BASE_URL ?>sales.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Volver
                        </a>

                        <?php if ($saleData['sale']['status'] != 'cancelled'): ?>
                            <button type="button" class="btn btn-outline-danger" id="btnCancelSale"
                                data-id="<?= $saleData['sale']['id_sale'] ?>"
                                data-code="<?= htmlspecialchars($saleData['sale']['sale_code']) ?>">
                                <i class="bi bi-x-circle me-1"></i> Cancelar Venta
                            </button>
                        <?php endif; ?>
                    </div>

                    <?php if ($saleData['sale']['status'] != 'cancelled'): ?>
                        <?php if ($invoice): ?>
                            <!-- Alerta SRI -->
                            <?php if ($invoice['estado_sri'] === 'autorizada'): ?>
                                <div class="alert alert-success d-flex align-items-center gap-2 py-2 mb-3">
                                    <i class="bi bi-check-circle-fill fs-5"></i>
                                    <div>
                                        <strong>Factura Electrónica Autorizada por el SRI</strong>
                                        <?php if ($invoice['numero_autorizacion']): ?>
                                            <br><small>Nº Aut: <?= htmlspecialchars($invoice['numero_autorizacion']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Opciones de impresión -->
                                <div class="card bg-light border-0 shadow-sm mb-3">
                                    <div class="card-body py-3">
                                        <h6 class="text-muted mb-2">
                                            <i class="bi bi-printer me-1"></i>Opciones de impresión
                                        </h6>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="<?= BASE_URL ?>sales.php?action=printTicket&id=<?= $saleData['sale']['id_sale'] ?>&width=58"
                                               class="btn btn-outline-secondary" target="_blank">
                                                <i class="bi bi-receipt me-1"></i> Ticket 58mm
                                            </a>
                                            <a href="<?= BASE_URL ?>sales.php?action=printTicket&id=<?= $saleData['sale']['id_sale'] ?>&width=80"
                                               class="btn btn-outline-secondary" target="_blank">
                                                <i class="bi bi-receipt-cutoff me-1"></i> Ticket 80mm
                                            </a>
                                            <a href="<?= BASE_URL ?>sales.php?action=printA5&id=<?= $saleData['sale']['id_sale'] ?>"
                                               class="btn btn-outline-primary" target="_blank">
                                                <i class="bi bi-file-earmark me-1"></i> Hoja A5
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Nota de Crédito -->
                                <?php if (empty($creditNotes)): ?>
                                    <div class="mb-3">
                                        <a href="<?= BASE_URL ?>credit_notes.php?action=create&sale_id=<?= $saleData['sale']['id_sale'] ?>"
                                           class="btn btn-warning">
                                            <i class="bi bi-arrow-return-left me-1"></i> Nota de Crédito
                                        </a>
                                    </div>
                                <?php endif; ?>

                            <?php elseif ($invoice['estado_sri'] === 'rechazada'): ?>
                                <div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-3">
                                    <i class="bi bi-x-circle-fill fs-5"></i>
                                    <div>
                                        <strong>Rechazada por el SRI</strong>
                                        <br><small><?= htmlspecialchars($invoice['mensaje_sri']) ?></small>
                                    </div>
                                </div>

                            <?php else: ?>
                                <div class="alert alert-warning d-flex align-items-center gap-2 py-2 mb-3">
                                    <i class="bi bi-hourglass-split fs-5"></i>
                                    <div>
                                        <strong>Estado SRI:</strong> <?= ucfirst($invoice['estado_sri']) ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                        <?php else: ?>
                            <!-- Sin factura electrónica -->
                            <div class="mb-3">
                                <a href="<?= BASE_URL ?>sales.php?action=selectCompany&id=<?= $saleData['sale']['id_sale'] ?>"
                                   class="btn btn-success">
                                    <i class="bi bi-send me-1"></i> Facturar Electrónicamente
                                </a>
                            </div>
                        <?php endif; ?>

                        <!-- WhatsApp -->
                        <?php
                        $clientPhone = $saleData['sale']['client_phone'] ?? '';
                        if ($clientPhone):
                            $phone = preg_replace('/[^0-9]/', '', $clientPhone);
                            if (str_starts_with($phone, '0')) {
                                $phone = '593' . substr($phone, 1);
                            }
                            $pdfUrl = BASE_URL . "sales.php?action=pdf&id=" . $saleData['sale']['id_sale'];
                            $message = "Estimado cliente, su factura {$saleData['sale']['sale_code']} está disponible.\n";
                            $message .= "Total: $" . number_format($saleData['sale']['total'], 2) . "\n";
                            if (ENV == 'production') {
                                $message .= "Descargue su factura aquí: " . $pdfUrl;
                            } else {
                                $message .= "(Enlace disponible solo en el sistema de la veterinaria)";
                            }
                            $waUrl = "https://wa.me/{$phone}?text=" . urlencode($message);
                            ?>
                            <a href="<?= $waUrl ?>" class="btn btn-outline-success mb-3" target="_blank">
                                <i class="bi bi-whatsapp me-1"></i> Enviar por WhatsApp
                            </a>
                        <?php endif; ?>

                        <!-- Notas de Crédito existentes -->
                        <?php if (!empty($creditNotes)): ?>
                            <div class="mt-2">
                                <h6 class="text-muted mb-2">
                                    <i class="bi bi-arrow-return-left me-1"></i>Notas de Crédito
                                </h6>
                                <div class="list-group">
                                    <?php foreach ($creditNotes as $nc): ?>
                                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2">
                                            <div>
                                                <strong>NC <?= htmlspecialchars($nc['numero_nota_credito'] ?? '#'.$nc['id']) ?></strong>
                                                <span class="text-muted mx-1">—</span>
                                                <?= htmlspecialchars($nc['motivo']) ?>
                                                <span class="text-muted mx-1">—</span>
                                                Monto: <strong class="text-danger">-$<?= number_format($nc['total'], 2) ?></strong>
                                                <?php if ($nc['estado_sri'] === 'autorizada'): ?>
                                                    <span class="badge bg-success ms-1">Autorizada</span>
                                                <?php elseif ($nc['estado_sri'] === 'rechazada'): ?>
                                                    <span class="badge bg-danger ms-1">Rechazada</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark ms-1"><?= ucfirst($nc['estado_sri']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <a href="<?= BASE_URL ?>credit_notes.php?action=show&id=<?= $nc['id'] ?>" class="btn btn-sm btn-outline-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.getElementById('btnCancelSale')?.addEventListener('click', function(e) {
    const id = this.dataset.id;
    const code = this.dataset.code;
    Swal.fire({
        title: '¿Cancelar venta?',
        text: `La venta ${code} se marcará como cancelada. Esta acción no se puede deshacer.`,
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
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
