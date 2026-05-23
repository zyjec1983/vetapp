<?php
/**
 * Location: vetapp/app/controllers/sales/SaleController.php
 */

require_once __DIR__ . '/../BaseController.php';
require_once __DIR__ . '/../../repositories/SaleRepository.php';
require_once __DIR__ . '/../../repositories/MedicationRepository.php';
require_once __DIR__ . '/../../repositories/ClientRepository.php';
require_once __DIR__ . '/../../models/SaleModel.php';
require_once __DIR__ . '/../../models/SaleDetailModel.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once ROOT_PATH . '/vendor/dompdf/autoload.inc.php';

class SaleController extends BaseController
{
    private $saleRepo;
    private $medRepo;
    private $clientRepo;

    // ********** Constructor: inicializa repositorios y verifica autenticación **********
    public function __construct()
    {
        parent::__construct();
        $this->saleRepo = new SaleRepository();
        $this->medRepo = new MedicationRepository();
        $this->clientRepo = new ClientRepository();
        $this->requireAuth();
    }

    // ********** Verifica que el usuario esté autenticado y tenga rol admin/veterinario/pharmacy **********
    private function requireAuth()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . 'login.php');
            exit;
        }
        $roles = $_SESSION['user']['roles'] ?? [];
        if (!in_array('admin', $roles) && !in_array('veterinarian', $roles) && !in_array('pharmacy', $roles)) {
            $_SESSION['error'] = 'No tienes permiso para acceder a esta sección.';
            header('Location: ' . BASE_URL . 'dashboard.php');
            exit;
        }
    }

    // ********** Listar todas las ventas con filtro de fechas **********
    public function index()
    {
        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-d');
        $sales = $this->saleRepo->getByDateRange($from, $to);
        $summary = $this->saleRepo->getSalesSummary($from, $to);
        require_once __DIR__ . '/../../views/sales/index.php';
    }

    // ********** Mostrar formulario para crear venta **********
    public function create()
    {
        $clients = $this->clientRepo->getAll();

        // Get Consumidor Final placeholder ID
        $cfPlaceholderId = null;
        $stmt = $this->db->prepare("SELECT id_client FROM clients WHERE identification = '9999999999999' LIMIT 1");
        $stmt->execute();
        $cfRow = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($cfRow) {
            $cfPlaceholderId = $cfRow['id_client'];
        }

        require_once __DIR__ . '/../../views/sales/create.php';
    }

    // ********** Guardar nueva venta con sus detalles **********
    public function store()
    {
        // ********** Verificar CSRF **********
        $this->validateCSRF();

        // ********** Validar método POST **********
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'sales.php');
            exit;
        }

        // ********** Sanitizar todos los datos POST **********
        $data = $this->sanitizeInputData($_POST);

        $user = currentUser();
        $id_user = $user['id'] ?? null;
        if (!$id_user) {
            $_SESSION['error'] = 'No se pudo identificar al usuario.';
            header('Location: ' . BASE_URL . 'login.php');
            exit;
        }

        // Recibir datos del carrito (enviado como JSON)
        $cart = json_decode($_POST['cart'] ?? '[]', true);
        if (empty($cart)) {
            $_SESSION['error'] = 'No hay productos en la venta.';
            header('Location: ' . BASE_URL . 'sales.php?action=create');
            exit;
        }

        // Validaciones básicas del carrito
        foreach ($cart as $item) {
            if (!isset($item['id_medication']) || !isset($item['quantity']) || $item['quantity'] <= 0) {
                $_SESSION['error'] = 'Datos de producto inválidos.';
                header('Location: ' . BASE_URL . 'sales.php?action=create');
                exit;
            }
        }

        // Crear modelo de venta con datos sanitizados
        $sale = new SaleModel([
            'sale_code' => $this->generateSaleCode(),
            'id_client' => $data['id_client'] ?? null,
            'id_user' => $id_user,
            'subtotal' => (float) $data['subtotal'],
            'discount' => (float) $data['discount'],
            'tax_total' => (float) $data['tax_total'],
            'total' => (float) $data['total'],
            'payment_method' => $data['payment_method'] ?? 'cash',
            'status' => 'paid',
            'observations' => $data['observations'] ?? ''
        ]);

        // Construir detalles de la venta
        $details = [];
        foreach ($cart as $item) {
            $med = $this->medRepo->findById($item['id_medication']);
            if (!$med) {
                $_SESSION['error'] = 'Medicamento no encontrado.';
                header('Location: ' . BASE_URL . 'sales.php?action=create');
                exit;
            }
            $taxable = is_object($med) ? $med->getTaxable() : ($med['taxable'] ?? true);
            $taxRate = $taxable ? 15 : 0;

            $detail = new SaleDetailModel([
                'id_medication' => $item['id_medication'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'tax_rate' => $taxRate
            ]);
            $details[] = $detail;
        }

        $saleId = $this->saleRepo->createSale($sale, $details);
        if ($saleId) {
            // Si el método de pago es crédito, crear recordatorio de pago pendiente
            if ($data['payment_method'] == 'credit') {
                require_once __DIR__ . '/../../repositories/ReminderRepository.php';
                require_once __DIR__ . '/../../models/ReminderModel.php';

                $reminderRepo = new ReminderRepository();
                $saleCode = $sale->getSaleCode();

                $reminderData = [
                    'reminder_type' => 'payment',
                    'id_client' => $data['id_client'],
                    'reminder_date' => date('Y-m-d', strtotime('+7 days')),
                    'message' => "Pago pendiente de factura {$saleCode}"
                ];
                $reminder = new ReminderModel($reminderData);
                $reminderRepo->create($reminder);
            }
            $_SESSION['success'] = 'Venta registrada correctamente.';
            header('Location: ' . BASE_URL . 'sales.php?action=show&id=' . $saleId);
        } else {
            $_SESSION['error'] = 'Error al registrar la venta. Verifique stock.';
            header('Location: ' . BASE_URL . 'sales.php?action=create');
        }
        exit;
    }

    // ********** Mostrar detalle de una venta **********
    public function show($id)
    {
        $saleData = $this->saleRepo->findById($id);
        if (!$saleData) {
            $_SESSION['error'] = 'Venta no encontrada.';
            header('Location: ' . BASE_URL . 'sales.php');
            exit;
        }
        require_once __DIR__ . '/../../views/sales/show.php';
    }

    // ********** Cancelar una venta **********
    public function cancel($id)
    {
        $saleData = $this->saleRepo->findById($id);
        if (!$saleData) {
            $_SESSION['error'] = 'Venta no encontrada.';
            header('Location: ' . BASE_URL . 'sales.php');
            exit;
        }
        if ($this->saleRepo->cancel($id)) {
            $_SESSION['success'] = 'Venta cancelada.';
        } else {
            $_SESSION['error'] = 'Error al cancelar venta.';
        }
        header('Location: ' . BASE_URL . 'sales.php');
        exit;
    }

    // ********** Generar código único para venta (ej: VENTA-20260328-001) **********
    private function generateSaleCode()
    {
        $date = date('Ymd');
        $prefix = "VENTA-{$date}-";
        $sql = "SELECT COUNT(*) FROM sales WHERE DATE(sale_date) = CURDATE()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $count = $stmt->fetchColumn() + 1;
        return $prefix . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    // ********** Buscar medicamentos para autocomplete (evita XSS en búsqueda) **********
    public function searchMedications()
    {
        header('Content-Type: application/json');
        $term = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';
        if (strlen($term) < 2) {
            echo json_encode([]);
            exit;
        }
        $results = $this->medRepo->search($term);
        echo json_encode($results);
        exit;
    }

    // ********** Buscar clientes para autocomplete (evita XSS en búsqueda) **********
    public function searchClients()
    {
        header('Content-Type: application/json');
        $q = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';
        $repo = new ClientRepository();
        $results = $repo->searchClientsWithPets($q);
        echo json_encode($results);
        exit;
    }

    // ********** Generar PDF de factura **********
    public function generatePDF($id)
    {
        $saleData = $this->saleRepo->findById($id);
        if (!$saleData) {
            $_SESSION['error'] = 'Venta no encontrada.';
            header('Location: ' . BASE_URL . 'sales.php');
            exit;
        }

        require_once ROOT_PATH . '/vendor/dompdf/autoload.inc.php';
        $dompdf = new Dompdf\Dompdf();

        $html = $this->renderPDFHTML($saleData);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream("factura_{$saleData['sale']['sale_code']}.pdf", array("Attachment" => false));
        exit;
    }

    // ********** Renderizar HTML para PDF de factura **********
    private function renderPDFHTML($saleData)
    {
        $sale = $saleData['sale'];
        $details = $saleData['details'];

        $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Factura</title>
        <style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
            .header { text-align: center; margin-bottom: 20px; }
            .empresa { font-size: 20px; font-weight: bold; }
            .factura { font-size: 16px; margin-top: 10px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .totales { margin-top: 20px; text-align: right; }
            .total { font-size: 14px; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="empresa">VetApp</div>
            <div class="factura">FACTURA: ' . htmlspecialchars($sale['sale_code']) . '</div>
            <div>Fecha: ' . date('d/m/Y H:i', strtotime($sale['sale_date'])) . '</div>
            <div>Cliente: ' . htmlspecialchars($sale['client_name'] ?? 'Consumidor final') . '</div>
            <div>Teléfono: ' . htmlspecialchars($sale['client_phone'] ?? 'No registrado') . '</div>
        </div>

        <table>
            <thead>
                <tr><th>Producto</th><th>Cant.</th><th>P.Unitario</th><th>Subtotal</th><th>IVA</th><th>Total</th></tr>
            </thead>
            <tbody>';
        foreach ($details as $det) {
            $html .= '<tr>
            <td>' . htmlspecialchars($det['medication_name']) . '</td>
            <td>' . $det['quantity'] . '</td>
            <td>$' . number_format($det['unit_price'], 2) . '</td>
            <td>$' . number_format($det['subtotal'], 2) . '</td>
            <td>$' . number_format($det['tax_amount'], 2) . '</td>
            <td>$' . number_format($det['total'], 2) . '</td>
        </tr>';
        }
        $html .= '</tbody>
        </table>

        <div class="totales">
            <p>Subtotal: $' . number_format($sale['subtotal'], 2) . '</p>
            <p>Descuento: $' . number_format($sale['discount'], 2) . '</p>
            <p>IVA: $' . number_format($sale['tax_total'], 2) . '</p>
            <p class="total">Total: $' . number_format($sale['total'], 2) . '</p>
        </div>
    </body>
    </html>';
        return $html;
    }

    // ========== MÉTODOS DE FACTURACIÓN ELECTRÓNICA SRI ==========

    /**
     * Mostrar vista de selección de empresa para enviar al SRI.
     */
    public function selectCompany($saleId)
    {
        $saleData = $this->saleRepo->findById($saleId);
        if (!$saleData) {
            $_SESSION['error'] = 'Venta no encontrada.';
            header('Location: ' . BASE_URL . 'sales.php');
            exit;
        }

        // Verificar que no ya tenga factura electrónica
        require_once __DIR__ . '/../../repositories/ElectronicInvoiceRepository.php';
        $invoiceRepo = new ElectronicInvoiceRepository();
        if ($invoiceRepo->existsForSale($saleId)) {
            $_SESSION['error'] = 'Esta venta ya tiene factura electrónica.';
            header('Location: ' . BASE_URL . 'sales.php?action=show&id=' . $saleId);
            exit;
        }

        // Obtener empresas activas
        require_once __DIR__ . '/../../repositories/CompanySettingRepository.php';
        $companyRepo = new CompanySettingRepository();
        $companies = $companyRepo->getActivas();

        require_once __DIR__ . '/../../views/sales/sri_select_company.php';
    }

    /**
     * Imprimir ticket térmico (58mm o 80mm).
     */
    public function printTicket($id)
    {
        $saleData = $this->saleRepo->findById($id);
        if (!$saleData) {
            $_SESSION['error'] = 'Venta no encontrada.';
            header('Location: ' . BASE_URL . 'sales.php');
            exit;
        }

        // Obtener datos de la factura electrónica
        require_once __DIR__ . '/../../repositories/ElectronicInvoiceRepository.php';
        require_once __DIR__ . '/../../repositories/CompanySettingRepository.php';
        $invoiceRepo = new ElectronicInvoiceRepository();
        $companyRepo = new CompanySettingRepository();

        $invoiceData = $invoiceRepo->findBySaleId($id);
        $companyData = $companyRepo->findById($saleData['sale']['company_id'] ?? 1);

        if (!$companyData) {
            // Fallback: usar datos genéricos si no hay empresa
            $companyData = [
                'commercial_name' => 'VetApp',
                'business_name' => 'VetApp',
                'ruc' => '1234567890001',
                'address' => '',
                'phone' => '',
                'ambiente' => 'pruebas',
            ];
        }

        require_once __DIR__ . '/../../views/sales/print_ticket.php';
    }

    /**
     * Imprimir factura en formato A5 (inyección/láser).
     */
    public function printA5($id)
    {
        $saleData = $this->saleRepo->findById($id);
        if (!$saleData) {
            $_SESSION['error'] = 'Venta no encontrada.';
            header('Location: ' . BASE_URL . 'sales.php');
            exit;
        }

        require_once __DIR__ . '/../../repositories/ElectronicInvoiceRepository.php';
        require_once __DIR__ . '/../../repositories/CompanySettingRepository.php';
        $invoiceRepo = new ElectronicInvoiceRepository();
        $companyRepo = new CompanySettingRepository();

        $invoiceData = $invoiceRepo->findBySaleId($id);
        $companyData = $companyRepo->findById($saleData['sale']['company_id'] ?? 1);

        if (!$companyData) {
            $companyData = [
                'commercial_name' => 'VetApp',
                'business_name' => 'VetApp',
                'ruc' => '1234567890001',
                'address' => '',
                'phone' => '',
                'email' => '',
                'ambiente' => 'pruebas',
            ];
        }

        require_once __DIR__ . '/../../views/sales/print_a5.php';
    }
}
