<?php
/**
 * Location: vetapp/app/views/sales/print_a5.php
 *
 * Vista: Impresión de factura en hoja A5 (148mm × 210mm).
 * Optimizada para impresora de inyección de tinta o láser.
 * No auto-imprime; el usuario presiona Ctrl+P o el botón.
 */

require_once __DIR__ . '/../../config/config.php';

$sale = $saleData['sale'];
$details = $saleData['details'];
$company = $companyData;
$invoice = $invoiceData;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura <?= htmlspecialchars($sale['sale_code']) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #000;
            background: #fff;
            width: 148mm;
            max-width: 148mm;
            margin: 0 auto;
            padding: 8mm;
        }
        h1 { font-size: 16px; margin-bottom: 2px; }
        h2 { font-size: 12px; }
        h3 { font-size: 11px; margin-bottom: 4px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #333; padding-bottom: 8px; margin-bottom: 8px; }
        .header-left h1 { color: #1a1a1a; }
        .header-right { text-align: right; background: #1a1a1a; color: #fff; padding: 8px 15px; border-radius: 4px; }
        .header-right .factura-type { font-size: 9px; opacity: 0.8; }
        .header-right .factura-number { font-size: 12px; font-weight: bold; font-family: monospace; }
        .info-row { display: flex; justify-content: space-between; font-size: 9px; margin-bottom: 2px; }
        .separator { border-top: 1px solid #ddd; margin: 6px 0; }
        .sri-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 4px; padding: 6px; margin-bottom: 8px; text-align: center; }
        .sri-box .auth { font-weight: bold; color: #16a34a; font-size: 11px; }
        .sri-box .clave { font-family: monospace; font-size: 7px; word-break: break-all; margin-top: 3px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th { background: #f3f4f6; text-align: left; padding: 4px 6px; font-size: 8px; font-weight: 600; border-bottom: 1px solid #ddd; }
        td { padding: 4px 6px; border-bottom: 1px solid #eee; font-size: 9px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totales { float: right; width: 200px; }
        .totales .row { display: flex; justify-content: space-between; padding: 2px 0; font-size: 9px; }
        .totales .total-final { border-top: 2px solid #333; padding-top: 4px; margin-top: 4px; font-size: 12px; font-weight: bold; }
        .footer { margin-top: 12px; padding-top: 6px; border-top: 1px solid #ddd; font-size: 8px; color: #666; text-align: center; }
        .footer .nota { font-style: italic; }

        /* Botón imprimir */
        .print-actions { text-align: center; padding: 10px; background: #f0f0f0; position: fixed; top: 0; left: 0; right: 0; z-index: 999; }
        .print-actions button { padding: 10px 30px; font-size: 16px; background: #2563eb; color: #fff; border: none; border-radius: 5px; cursor: pointer; margin: 0 5px; }
        .print-actions a { padding: 10px 20px; font-size: 14px; color: #333; text-decoration: none; }
        body { padding-top: 60px; }

        @media print {
            .print-actions { display: none !important; }
            body { padding-top: 0; }
            @page { size: A5 portrait; margin: 5mm; }
        }
    </style>
</head>
<body>
    <div class="print-actions">
        <button onclick="window.print()">🖨️ Imprimir A5</button>
        <a href="<?= BASE_URL ?>sales.php?action=show&id=<?= $sale['id_sale'] ?>">← Volver</a>
    </div>

    <!-- Encabezado -->
    <div class="header">
        <div class="header-left">
            <h1><?= htmlspecialchars($company['commercial_name']) ?></h1>
            <p style="font-size:9px;">RUC: <?= htmlspecialchars($company['ruc']) ?></p>
            <p style="font-size:9px;"><?= htmlspecialchars($company['address'] ?? '') ?></p>
            <p style="font-size:9px;">Tel: <?= htmlspecialchars($company['phone'] ?? '') ?> | <?= htmlspecialchars($company['email'] ?? '') ?></p>
        </div>
        <div class="header-right">
            <div class="factura-type">FACTURA ELECTRÓNICA</div>
            <div class="factura-number"><?= htmlspecialchars($invoice['numero_factura']) ?></div>
        </div>
    </div>

    <!-- Info venta -->
    <?php
    $clientName = $saleData['client']['name'] ?? 'Consumidor Final';
    $clientRuc = $saleData['client']['ruc_cedula'] ?? '9999999999';
    $isCF = ($clientRuc === '9999999999' || $clientRuc === '9999999999999' || stripos($clientName, 'consumidor') !== false);
    ?>
    <div class="info-row"><span><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($sale['sale_date'])) ?></span><span><strong>Pago:</strong> <?= ucfirst($sale['payment_method']) ?></span></div>
    <div class="info-row"><span><strong>Cliente:</strong> <?= $isCF ? 'CONSUMIDOR FINAL' : htmlspecialchars($clientName) ?></span><span><strong>RUC/Cédula:</strong> <?= htmlspecialchars($isCF ? '9999999999999' : $clientRuc) ?></span></div>

    <div class="separator"></div>

    <!-- SRI -->
    <?php if ($invoice['estado_sri'] === 'autorizada'): ?>
        <div class="sri-box">
            <div class="auth">✅ AUTORIZADA POR EL SRI</div>
            <?php if ($invoice['numero_autorizacion']): ?>
                <div style="font-size:8px;">Nº Autorización: <strong><?= htmlspecialchars($invoice['numero_autorizacion']) ?></strong></div>
            <?php endif; ?>
            <div class="clave">Clave de Acceso: <?= htmlspecialchars($invoice['clave_acceso']) ?></div>
        </div>
        <div class="separator"></div>
    <?php endif; ?>

    <!-- Detalles -->
    <h3>Detalle de Productos</h3>
    <table>
        <thead>
            <tr>
                <th>Cant.</th>
                <th>Descripción</th>
                <th class="text-right">P. Unit.</th>
                <th class="text-center">IVA</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($details as $det): ?>
                <tr>
                    <td class="text-center"><?= $det['quantity'] ?></td>
                    <td><?= htmlspecialchars($det['medication_name']) ?></td>
                    <td class="text-right">$<?= number_format($det['unit_price'], 2) ?></td>
                    <td class="text-center"><?= ($det['tax_rate'] ?? 0) > 0 ? $det['tax_rate'] . '%' : 'Exento' ?></td>
                    <td class="text-right">$<?= number_format($det['subtotal'], 2) ?></td>
                    <td class="text-right"><strong>$<?= number_format($det['total'], 2) ?></strong></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Totales -->
    <div class="totales">
        <div class="row"><span>Subtotal:</span><span>$<?= number_format($sale['subtotal'], 2) ?></span></div>
        <?php if ($sale['discount'] > 0): ?>
            <div class="row"><span>Descuento:</span><span>-$<?= number_format($sale['discount'], 2) ?></span></div>
        <?php endif; ?>
        <?php if ($sale['tax_total'] > 0): ?>
            <div class="row"><span>IVA:</span><span>$<?= number_format($sale['tax_total'], 2) ?></span></div>
        <?php endif; ?>
        <div class="row total-final"><span>TOTAL:</span><span>$<?= number_format($sale['total'], 2) ?></span></div>
    </div>

    <div style="clear:both;"></div>

    <!-- Footer -->
    <div class="footer">
        <p class="nota">Esta factura electrónica fue generada y autorizada por el Servicio de Rentas Internas (SRI) del Ecuador.</p>
        <p class="nota">Consulte su autenticidad en www.sri.gob.ec con la clave de acceso.</p>
        <p style="font-weight:bold; margin-top:4px;">¡Gracias por su compra!</p>
    </div>
</body>
</html>
