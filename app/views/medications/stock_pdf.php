<?php
/**
 * Location: vetapp/app/views/medications/stock_pdf.php
 *
 * Vista: Generación de PDF con reporte de stock actual en vitrinas/perchas.
 * Se accede con: medications.php?action=stockPdf&type=medicamentos
 * Usa dompdf para generar el PDF.
 */

$typeLabels = [
    'medicamentos' => 'Medicamentos',
    'accesorios' => 'Accesorios y Otros',
    'todos' => 'Todos los Productos',
];
$label = $typeLabels[$type] ?? 'Todos los Productos';

$html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Stock - ' . htmlspecialchars($label) . '</title>
    <style>
        @page { margin: 15mm 12mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1a1a1a; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #2c5f2d; padding-bottom: 10px; }
        .header .title { font-size: 16px; font-weight: bold; color: #2c5f2d; }
        .header .subtitle { font-size: 11px; margin-top: 3px; }
        .header .meta { font-size: 8px; color: #666; margin-top: 5px; }
        .filter-badge { display: inline-block; background: #2c5f2d; color: #fff; padding: 2px 10px; border-radius: 3px; font-size: 9px; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #2c5f2d; color: #fff; padding: 5px 4px; text-align: left; font-size: 8px; font-weight: bold; border: 1px solid #2c5f2d; }
        td { padding: 4px; border: 1px solid #ddd; font-size: 8px; }
        tr:nth-child(even) { background: #f9f9f9; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .row-num { width: 20px; text-align: center; }
        .code-col { width: 55px; }
        .category-col { width: 55px; }
        .stock-col { width: 35px; text-align: center; }
        .price-col { width: 45px; text-align: right; }
        .total-col { width: 50px; text-align: right; }
        .location-col { width: 50px; }
        .footer-totals { margin-top: 15px; padding: 8px; background: #f0f7f0; border: 1px solid #2c5f2d; border-radius: 3px; }
        .footer-totals .row { display: flex; justify-content: space-between; margin-bottom: 2px; }
        .footer-totals .total-row { border-top: 1px solid #2c5f2d; padding-top: 4px; margin-top: 4px; font-size: 10px; font-weight: bold; }
        .footer-note { margin-top: 10px; text-align: center; font-size: 7px; color: #999; border-top: 1px solid #eee; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">REPORTE DE INVENTARIO</div>
        <div class="subtitle">Stock Actual en Vitrinas / Perchas</div>
        <div class="filter-badge">' . htmlspecialchars($label) . '</div>
        <div class="meta">Generado: ' . date('d/m/Y H:i:s') . ' | Usuario: ' . (isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']['name'] . ' ' . $_SESSION['user']['lastname1']) : 'Sistema') . '</div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="row-num">#</th>
                <th class="code-col">Código</th>
                <th class="category-col">Categoría</th>
                <th>Producto</th>
                <th>Principio Activo</th>
                <th class="location-col">Ubicación</th>
                <th class="stock-col">Stock</th>
                <th class="price-col">P. Venta</th>
                <th class="total-col">Valor Total</th>
            </tr>
        </thead>
        <tbody>';

$counter = 1;
foreach ($products as $p) {
    $rowTotal = (float)$p['sale_price'] * (int)$p['stock_total'];
    $html .= '<tr>
        <td class="row-num">' . $counter . '</td>
        <td>' . htmlspecialchars($p['code']) . '</td>
        <td>' . htmlspecialchars($p['category']) . '</td>
        <td>' . htmlspecialchars($p['name']) . '</td>
        <td>' . htmlspecialchars($p['active_name'] ?? '-') . '</td>
        <td>' . htmlspecialchars($p['location'] ?? '-') . '</td>
        <td class="stock-col">' . (int)$p['stock_total'] . '</td>
        <td class="price-col">$' . number_format((float)$p['sale_price'], 2) . '</td>
        <td class="total-col">$' . number_format($rowTotal, 2) . '</td>
    </tr>';
    $counter++;
}

$html .= '</tbody>
    </table>

    <div class="footer-totals">
        <div class="row"><span>Total de productos con stock:</span><span>' . $totalItems . '</span></div>
        <div class="row total-row"><span>Valor total del inventario:</span><span>$' . number_format($totalValue, 2) . '</span></div>
    </div>

    <div class="footer-note">
        <p>Este reporte refleja el stock disponible al momento de la generación. VetApp - Sistema de Gestión Veterinaria.</p>
        <p>Los productos sin stock (stock = 0) no se incluyen en este reporte.</p>
    </div>
</body>
</html>';

$dompdf = new Dompdf\Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = 'stock_' . ($type ? $type : 'todos') . '_' . date('Y-m-d_His') . '.pdf';
$dompdf->stream($filename, array("Attachment" => true));
