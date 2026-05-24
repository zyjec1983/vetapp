<?php
/**
 * Location: vetapp/app/views/credit_notes/create.php
 *
 * Formulario para crear Nota de Crédito.
 * El usuario selecciona productos y cantidades a devolver.
 */

$title = 'Nota de Crédito | VetApp';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';

$sale = $saleData['sale'];
$details = $saleData['details'];
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../layouts/aside.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>sales.php">Ventas</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>sales.php?action=show&id=<?= $saleId ?>">Venta <?= htmlspecialchars($sale['sale_code']) ?></a></li>
                    <li class="breadcrumb-item active">Nota de Crédito</li>
                </ol>
            </nav>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i><?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-arrow-return-left me-2"></i>Nueva Nota de Crédito</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Venta:</strong> <?= htmlspecialchars($sale['sale_code']) ?> |
                        <strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($sale['sale_date'])) ?> |
                        <strong>Factura:</strong> <?= htmlspecialchars($invoice['numero_factura']) ?>
                    </div>

                    <form method="POST" action="<?= BASE_URL ?>credit_notes.php?action=store" id="formNC">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                        <input type="hidden" name="sale_id" value="<?= $saleId ?>">
                        <input type="hidden" name="company_id" value="<?= $sale['company_id'] ?? $invoice['company_id'] ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Motivo de la devolución</label>
                            <select name="motivo" class="form-select" required>
                                <option value="">-- Seleccione --</option>
                                <option value="Devolución total">Devolución total (todos los productos)</option>
                                <option value="Devolución parcial">Devolución parcial (uno o varios productos)</option>
                                <option value="Cambio de producto">Cambio de producto</option>
                            </select>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width:40px">Devolver</th>
                                        <th>Producto</th>
                                        <th class="d-none d-md-table-cell">Lote</th>
                                        <th style="width:60px">Vendido</th>
                                        <th style="width:80px">A devolver</th>
                                        <th style="width:80px">P. Unitario</th>
                                        <th style="width:60px">IVA</th>
                                        <th style="width:90px">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($details as $det):
                                        $medId = $det['id_medication'];
                                        $batchId = $det['id_batch'] ?? 0;
                                        $qtySold = $det['quantity'];
                                        $unitPrice = $det['unit_price'];
                                        $taxRate = $det['tax_rate'] ?? 0;
                                        $subtotalLine = $det['subtotal'];
                                    ?>
                                        <tr>
                                            <td class="text-center">
                                                <input type="checkbox" class="form-check-input item-checkbox"
                                                       data-med-id="<?= $medId ?>"
                                                       data-batch-id="<?= $batchId ?>"
                                                       data-qty="<?= $qtySold ?>"
                                                       data-price="<?= $unitPrice ?>"
                                                       data-tax="<?= $taxRate ?>"
                                                       data-name="<?= htmlspecialchars($det['medication_name']) ?>"
                                                       data-code="<?= htmlspecialchars($det['medication_name']) ?>">
                                            </td>
                                            <td><?= htmlspecialchars($det['medication_name']) ?></td>
                                            <td class="d-none d-md-table-cell"><small class="text-muted">#<?= $batchId ?></small></td>
                                            <td class="text-center"><?= $qtySold ?></td>
                                            <td>
                                                <input type="number"
                                                       class="form-control form-control-sm item-qty"
                                                       min="1" max="<?= $qtySold ?>"
                                                       value="<?= $qtySold ?>"
                                                       disabled>
                                                <input type="hidden" class="item-qty-hidden" value="<?= $qtySold ?>" disabled>
                                            </td>
                                            <td class="text-end">$<?= number_format($unitPrice, 2) ?></td>
                                            <td class="text-center"><?= $taxRate > 0 ? $taxRate . '%' : 'Exento' ?></td>
                                            <td class="text-end subtotal-cell">$<?= number_format($subtotalLine, 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="row">
                            <div class="col-md-6 offset-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <p class="d-flex justify-content-between mb-1">
                                            <span>Subtotal a devolver:</span>
                                            <span id="previewSubtotal" class="fw-bold">$0.00</span>
                                        </p>
                                        <p class="d-flex justify-content-between mb-1">
                                            <span>IVA:</span>
                                            <span id="previewTax" class="fw-bold">$0.00</span>
                                        </p>
                                        <hr class="my-1">
                                        <p class="d-flex justify-content-between mb-0">
                                            <span class="fw-bold">Total NC:</span>
                                            <span id="previewTotal" class="fw-bold text-primary">$0.00</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary" id="btnSubmit" disabled>
                                <i class="bi bi-send me-1"></i> Emitir Nota de Crédito
                            </button>
                            <a href="<?= BASE_URL ?>sales.php?action=show&id=<?= $saleId ?>" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    const form = document.getElementById('formNC');

    function updateTotals() {
        let subtotal = 0;
        let tax = 0;
        let total = 0;
        let hasItems = false;

        checkboxes.forEach(cb => {
            const qtyInput = cb.closest('tr').querySelector('.item-qty');
            const hidden = cb.closest('tr').querySelector('.item-qty-hidden');
            const subtotalCell = cb.closest('tr').querySelector('.subtotal-cell');

            if (cb.checked) {
                const qty = parseInt(qtyInput.value) || 0;
                const price = parseFloat(cb.dataset.price) || 0;
                const taxRate = parseFloat(cb.dataset.tax) || 0;
                const lineSubtotal = qty * price;
                const lineTax = lineSubtotal * (taxRate / 100);
                const lineTotal = lineSubtotal + lineTax;

                subtotal += lineSubtotal;
                tax += lineTax;
                total += lineTotal;
                hasItems = true;

                hidden.value = qty;
                hidden.name = `items[${cb.dataset.medId}][quantity]`;
                hidden.disabled = false;
                subtotalCell.textContent = '$' + lineSubtotal.toFixed(2);
            } else {
                qtyInput.disabled = true;
                hidden.value = 0;
                hidden.name = '';
                hidden.disabled = true;
                qtyInput.readOnly = false;
                subtotalCell.textContent = '$' + (parseInt(cb.dataset.qty) * parseFloat(cb.dataset.price)).toFixed(2);
            }
        });

        document.getElementById('previewSubtotal').textContent = '$' + subtotal.toFixed(2);
        document.getElementById('previewTax').textContent = '$' + tax.toFixed(2);
        document.getElementById('previewTotal').textContent = '$' + total.toFixed(2);
        document.getElementById('btnSubmit').disabled = !hasItems;
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            const qtyInput = this.closest('tr').querySelector('.item-qty');
            const hidden = this.closest('tr').querySelector('.item-qty-hidden');

            if (this.checked) {
                qtyInput.disabled = false;
                // Create hidden input for submission
                if (!hidden || hidden.dataset.created !== '1') {
                    const newHidden = document.createElement('input');
                    newHidden.type = 'hidden';
                    newHidden.className = 'item-qty-hidden';
                    newHidden.dataset.created = '1';
                    newHidden.name = `items[${this.dataset.medId}][quantity]`;
                    newHidden.value = qtyInput.value;
                    this.closest('tr').querySelector('td:nth-child(5)').appendChild(newHidden);

                    // Additional hidden fields
                    const fields = ['medication_id', 'batch_id', 'unit_price', 'tax_rate', 'medication_name', 'medication_code'];
                    fields.forEach(f => {
                        const inp = document.createElement('input');
                        inp.type = 'hidden';
                        inp.name = `items[${this.dataset.medId}][${f}]`;
                        inp.className = `item-${f}`;
                        const valMap = {
                            medication_id: this.dataset.medId,
                            batch_id: this.dataset.batchId,
                            unit_price: this.dataset.price,
                            tax_rate: this.dataset.tax,
                            medication_name: this.dataset.name,
                            medication_code: this.dataset.code,
                        };
                        inp.value = valMap[f] || '';
                        this.closest('tr').appendChild(inp);
                    });
                }
            } else {
                qtyInput.disabled = true;
                // Remove hidden inputs
                this.closest('tr').querySelectorAll('input[type="hidden"][name^="items["]').forEach(el => el.remove());
                this.closest('tr').querySelectorAll('.item-qty-hidden[data-created]').forEach(el => el.remove());
            }
            updateTotals();
        });

        const qtyInput = cb.closest('tr').querySelector('.item-qty');
        if (qtyInput) {
            qtyInput.addEventListener('input', function () {
                if (cb.checked) {
                    const hidden = cb.closest('tr').querySelector('.item-qty-hidden[data-created]');
                    if (hidden) hidden.value = this.value;
                    updateTotals();
                }
            });
        }
    });

    form.addEventListener('submit', function (e) {
        const checkboxes = document.querySelectorAll('.item-checkbox:checked');
        if (checkboxes.length === 0) {
            e.preventDefault();
            Swal.fire('Seleccione productos', 'Debe seleccionar al menos un producto para devolver.', 'warning');
            return;
        }

        const motivo = document.querySelector('select[name="motivo"]').value;
        if (!motivo) {
            e.preventDefault();
            Swal.fire('Seleccione motivo', 'Debe seleccionar un motivo para la devolución.', 'warning');
            return;
        }

        const totalText = document.getElementById('previewTotal').textContent;
        Swal.fire({
            title: '¿Emitir Nota de Crédito?',
            html: `Se emitirá una Nota de Crédito por <strong>${totalText}</strong>.<br><small>El stock será restaurado automáticamente.</small>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, emitir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('btnSubmit').disabled = true;
                document.getElementById('btnSubmit').innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Procesando...';
                form.submit();
            } else {
                e.preventDefault();
            }
        });
        e.preventDefault();
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
