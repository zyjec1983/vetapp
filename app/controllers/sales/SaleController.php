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

class SaleController extends BaseController
{
    private $saleRepo;
    private $medRepo;
    private $clientRepo;

    public function __construct()
    {
        parent::__construct();
        $this->saleRepo = new SaleRepository();
        $this->medRepo = new MedicationRepository();
        $this->clientRepo = new ClientRepository();
        $this->requireAuth();
    }

    private function requireAuth()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . 'login.php');
            exit;
        }
        $roles = $_SESSION['user']['roles'] ?? [];
        // Administradores, veterinarios y farmacia pueden vender
        if (!in_array('admin', $roles) && !in_array('veterinarian', $roles) && !in_array('pharmacy', $roles)) {
            $_SESSION['error'] = 'No tienes permiso para acceder a esta sección.';
            header('Location: ' . BASE_URL . 'dashboard.php');
            exit;
        }
    }

    public function index()
    {
        $sales = $this->saleRepo->getAll();
        require_once __DIR__ . '/../../views/sales/index.php';
    }

    public function create()
    {
        $clients = $this->clientRepo->getAll(); // lista de clientes para selector
        require_once __DIR__ . '/../../views/sales/create.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'sales.php');
            exit;
        }

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

        // Validaciones básicas
        foreach ($cart as $item) {
            if (!isset($item['id_medication']) || !isset($item['quantity']) || $item['quantity'] <= 0) {
                $_SESSION['error'] = 'Datos de producto inválidos.';
                header('Location: ' . BASE_URL . 'sales.php?action=create');
                exit;
            }
        }

        // Crear modelo de venta
        $sale = new SaleModel([
            'sale_code' => $this->generateSaleCode(),
            'id_client' => $_POST['id_client'] ?? null,
            'id_user' => $id_user,
            'subtotal' => (float) $_POST['subtotal'],
            'discount' => (float) $_POST['discount'],
            'tax_total' => (float) $_POST['tax_total'],
            'total' => (float) $_POST['total'],
            'payment_method' => $_POST['payment_method'] ?? 'cash',
            'status' => 'paid',
            'observations' => trim($_POST['observations'] ?? '')
        ]);

        // Construir detalles
        $details = [];
        foreach ($cart as $item) {
            $med = $this->medRepo->findById($item['id_medication']);
            if (!$med) {
                $_SESSION['error'] = 'Medicamento no encontrado.';
                header('Location: ' . BASE_URL . 'sales.php?action=create');
                exit;
            }
            // Si $med es un objeto MedicationModel, usar getTaxable()
            // Si es un array, usar $med['taxable']
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
            $_SESSION['success'] = 'Venta registrada correctamente.';
            // Redirigir a la vista de detalle (factura)
            header('Location: ' . BASE_URL . 'sales.php?action=show&id=' . $saleId);
        } else {
            $_SESSION['error'] = 'Error al registrar la venta. Verifique stock.';
            header('Location: ' . BASE_URL . 'sales.php?action=create');
        }
        exit;
    }

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

    /**
     * Genera un código único para la venta (ej: VENTA-20260328-001)
     */
    private function generateSaleCode()
    {
        $date = date('Ymd');
        $prefix = "VENTA-{$date}-";
        // Contar ventas de hoy para secuencia
        $sql = "SELECT COUNT(*) FROM sales WHERE DATE(sale_date) = CURDATE()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $count = $stmt->fetchColumn() + 1;
        return $prefix . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    public function searchMedications()
    {
        $term = $_GET['q'] ?? '';
        if (strlen($term) < 2) {
            echo json_encode([]);
            exit;
        }
        $results = $this->medRepo->search($term);
        echo json_encode($results);
        exit;
    }

    public function searchClients()
    {
        header('Content-Type: application/json');

        $q = $_GET['q'] ?? '';

        $repo = new ClientRepository();
        $results = $repo->searchClientsWithPets($q);

        echo json_encode($results);
        exit;
    }
}