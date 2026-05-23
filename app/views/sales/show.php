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
                    <!-- ********* Boton volver ********* -->
                    <a href="<?= BASE_URL ?>sales.php" class="btn btn-secondary">Volver</a>

                    <?php if ($saleData['sale']['status'] != 'cancelled'): ?>
                        <!-- ********* Boton VER PDF ********* -->
                        <a href="<?= BASE_URL ?>sales.php?action=pdf&id=<?= $saleData['sale']['id_sale'] ?>"
                            class="btn btn-info px-4 py-2 shadow-sm text-white" target="_blank">
                            <i class="bi bi-file-pdf me-1"></i> Ver PDF
                        </a>

                        <?php if ($invoice): ?>
                            <!-- Ya tiene factura electrónica SRI -->
                            <?php if ($invoice['estado_sri'] === 'autorizada'): ?>
                                <div class="alert alert-success py-2 mt-3 mb-2">
                                    <i class="bi bi-check-circle-fill me-1"></i>
                                    <strong>Factura Electrónica Autorizada por el SRI</strong>
                                    <?php if ($invoice['numero_autorizacion']): ?>
                                        <br><small>Nº Aut: <?= htmlspecialchars($invoice['numero_autorizacion']) ?></small>
                                    <?php endif; ?>
                                </div>

                                <!-- Opciones de impresión -->
                                <div class="d-flex flex-wrap gap-2 mt-2 mb-2">
                                    <a href="<?= BASE_URL ?>sales.php?action=printTicket&id=<?= $saleData['sale']['id_sale'] ?>&width=58"
                                       class="btn btn-outline-dark btn-sm" target="_blank">
                                        🧾 Ticket 58mm
                                    </a>
                                    <a href="<?= BASE_URL ?>sales.php?action=printTicket&id=<?= $saleData['sale']['id_sale'] ?>&width=80"
                                       class="btn btn-outline-dark btn-sm" target="_blank">
                                        🧾 Ticket 80mm
                                    </a>
                                    <a href="<?= BASE_URL ?>sales.php?action=printA5&id=<?= $saleData['sale']['id_sale'] ?>"
                                       class="btn btn-outline-primary btn-sm" target="_blank">
                                        📄 Hoja A5
                                    </a>
                                </div>
                            <?php elseif ($invoice['estado_sri'] === 'rechazada'): ?>
                                <div class="alert alert-danger py-2 mt-3 mb-2">
                                    <i class="bi bi-x-circle-fill me-1"></i>
                                    <strong>Rechazada por el SRI:</strong> <?= htmlspecialchars($invoice['mensaje_sri']) ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning py-2 mt-3 mb-2">
                                    <i class="bi bi-hourglass-split me-1"></i>
                                    <strong>Estado SRI:</strong> <?= ucfirst($invoice['estado_sri']) ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <!-- No tiene factura electrónica aún -->
                            <a href="<?= BASE_URL ?>sales.php?action=selectCompany&id=<?= $saleData['sale']['id_sale'] ?>"
                               class="btn btn-success px-4 py-2 shadow-sm">
                                <i class="bi bi-send me-1"></i> Facturar Electrónicamente
                            </a>
                        <?php endif; ?>

                        <!-- ********* ENVIAR POR WHATSAPP ********* -->
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
                            <a href="<?= $waUrl ?>" class="btn btn-success" target="_blank">
                                <i class="bi bi-whatsapp"></i> Enviar por WhatsApp
                            </a>
                        <?php endif; ?>

                        <button type="button" class="btn btn-danger px-4 py-2 shadow-sm" id="btnCancelSale"
                            data-id="<?= $saleData['sale']['id_sale'] ?>"
                            data-code="<?= htmlspecialchars($saleData['sale']['sale_code']) ?>">
                            <i class="bi bi-x-circle me-1"></i> Cancelar Venta
                        </button>
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
