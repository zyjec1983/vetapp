<?php
/**
 * Location: vetapp/app/views/sales/print_ticket.php
 *
 * Vista: Impresión de ticket térmico (58mm o 80mm).
 * Se accede con: sales.php?action=printTicket&id=X&width=58
 *
 * Formato monospace optimizado para impresora térmica.
 * Auto-imprime al cargar.
 */

require_once __DIR__ . '/../../config/config.php';

// Obtener datos
$sale = $saleData['sale'];
$details = $saleData['details'];
$company = $companyData;
$invoice = $invoiceData;

// Ancho del ticket: 58mm o 80mm
$width = isset($_GET['width']) && $_GET['width'] == 80 ? 80 : 58;
$maxChars = $width == 80 ? 42 : 30;

$widthMM = $width . 'mm';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket <?= htmlspecialchars($sale['sale_code']) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 11px;
            line-height: 1.3;
            color: #000;
            background: #fff;
            width: <?= $widthMM ?>;
            max-width: <?= $widthMM ?>;
            margin: 0 auto;
            padding: 3mm;
        }
        .separator { border-top: 1px dashed #000; margin: 5px 0; }
        .separator-solid { border-top: 1px solid #000; margin: 5px 0; }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .row { display: flex; justify-content: space-between; }
        .large { font-size: 13px; }
        .small { font-size: 9px; }
        .gracias { font-size: 12px; font-weight: bold; margin-top: 8px; }
        .word-break { word-break: break-all; }

        /* Botón de imprimir solo visible en pantalla */
        .print-btn {
            text-align: center;
            padding: 10px;
            background: #f0f0f0;
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 999;
        }
        .print-btn button {
            padding: 10px 30px;
            font-size: 16px;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .print-btn a { margin-left: 10px; color: #333; }
        body { padding-top: 60px; }

        @media print {
            .print-btn { display: none !important; }
            body { padding-top: 0; }
            @page { size: <?= $widthMM ?> auto; margin: 2mm; }
        }
    </style>
</head>
<body>
    <div class="print-btn">
        <button onclick="window.print()">🖨️ Imprimir Ticket</button>
        <a href="<?= BASE_URL ?>sales.php?action=show&id=<?= $sale['id_sale'] ?>">← Volver</a>
    </div>

    <!-- Encabezado -->
    <div class="center">
        <div class="bold large"><?= htmlspecialchars($company['commercial_name']) ?></div>
        <div class="small">RUC: <?= htmlspecialchars($company['ruc']) ?></div>
        <div class="small"><?= htmlspecialchars($company['address'] ?? '') ?></div>
        <div class="small">Tel: <?= htmlspecialchars($company['phone'] ?? '') ?></div>
        <div class="separator-solid"></div>
        <div class="bold">FACTURA ELECTRÓNICA</div>
    </div>

    <div class="separator"></div>

    <!-- Info factura -->
    <div class="row"><span>Nº Factura:</span><span class="bold"><?= htmlspecialchars($invoice['numero_factura']) ?></span></div>
    <div class="row"><span>Fecha:</span><span><?= date('d/m/Y H:i', strtotime($sale['sale_date'])) ?></span></div>
    <div class="row"><span>Ambiente:</span><span><?= strtoupper($company['ambiente']) ?></span></div>

    <div class="separator"></div>

    <!-- Cliente -->
    <?php
    $clientName = $saleData['client']['name'] ?? 'Consumidor Final';
    $clientRuc = $saleData['client']['ruc_cedula'] ?? '9999999999';
    $isCF = ($clientRuc === '9999999999' || $clientRuc === '9999999999999' || stripos($clientName, 'consumidor') !== false);
    ?>
    <div class="row"><span>Cliente:</span><span class="bold"><?= $isCF ? 'CONSUMIDOR FINAL' : htmlspecialchars($clientName) ?></span></div>
    <div class="row"><span>RUC/Cédula:</span><span><?= htmlspecialchars($isCF ? '9999999999999' : $clientRuc) ?></span></div>
    <div class="row"><span>Pago:</span><span><?= ucfirst($sale['payment_method']) ?></span></div>

    <div class="separator"></div>

    <!-- Detalles -->
    <div class="bold">Cant  Descripción              Total</div>
    <div class="separator"></div>
    <?php foreach ($details as $det): ?>
        <div>
            <?= str_pad($det['quantity'], 4) ?>
            <?= htmlspecialchars(mb_substr($det['medication_name'], 0, $maxChars - 15)) ?>
            <span class="right bold">$<?= number_format($det['total'], 2) ?></span>
        </div>
        <div class="small">
            @ $<?= number_format($det['unit_price'], 2) ?>
            <?= ($det['tax_rate'] ?? 0) > 0 ? '(IVA ' . $det['tax_rate'] . '%)' : '(Exento)' ?>
        </div>
    <?php endforeach; ?>

    <div class="separator-solid"></div>

    <!-- Totales -->
    <?php if ($sale['subtotal'] > 0): ?>
        <div class="row"><span>Subtotal:</span><span>$<?= number_format($sale['subtotal'], 2) ?></span></div>
    <?php endif; ?>
    <?php if ($sale['discount'] > 0): ?>
        <div class="row"><span>Descuento:</span><span>-$<?= number_format($sale['discount'], 2) ?></span></div>
    <?php endif; ?>
    <?php if ($sale['tax_total'] > 0): ?>
        <div class="row"><span>IVA:</span><span>$<?= number_format($sale['tax_total'], 2) ?></span></div>
    <?php endif; ?>
    <div class="separator-solid"></div>
    <div class="row bold large">
        <span>TOTAL:</span>
        <span>$<?= number_format($sale['total'], 2) ?></span>
    </div>

    <div class="separator"></div>

    <!-- SRI -->
    <?php if ($invoice['estado_sri'] === 'autorizada'): ?>
        <div class="center bold">✅ AUTORIZADA POR EL SRI</div>
        <?php if ($invoice['numero_autorizacion']): ?>
            <div class="center small">Nº Aut: <?= htmlspecialchars($invoice['numero_autorizacion']) ?></div>
        <?php endif; ?>
        <div class="separator"></div>
        <div class="center small word-break">Clave: <?= htmlspecialchars($invoice['clave_acceso']) ?></div>
        <div class="separator"></div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="center small">
        <p>Factura electrónica autorizada por el SRI</p>
        <p>Consulte en www.sri.gob.ec</p>
        <div class="gracias">¡Gracias por su compra!</div>
    </div>

    <script>
        window.onload = function() { window.print(); };
    </script>
</body>
</html>
