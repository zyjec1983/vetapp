<?php
/**
 * Location: vetapp/app/controllers/credit_notes/CreditNoteController.php
 *
 * Controlador para Notas de Crédito Electrónicas.
 * MVC + Repository: obtiene datos de repositories, pasa a vistas.
 */

require_once __DIR__ . '/../BaseController.php';
require_once __DIR__ . '/../../repositories/CreditNoteRepository.php';
require_once __DIR__ . '/../../repositories/SaleRepository.php';
require_once __DIR__ . '/../../repositories/ElectronicInvoiceRepository.php';
require_once __DIR__ . '/../../repositories/CompanySettingRepository.php';
require_once __DIR__ . '/../../repositories/ClientRepository.php';
require_once __DIR__ . '/../../models/CreditNoteModel.php';
require_once __DIR__ . '/../../models/CreditNoteDetailModel.php';
require_once __DIR__ . '/../../services/SriService.php';
require_once __DIR__ . '/../../helpers/auth.php';

class CreditNoteController extends BaseController
{
    private $cnRepo;
    private $saleRepo;
    private $invoiceRepo;
    private $companyRepo;

    public function __construct()
    {
        parent::__construct();
        $this->cnRepo = new CreditNoteRepository();
        $this->saleRepo = new SaleRepository();
        $this->invoiceRepo = new ElectronicInvoiceRepository();
        $this->companyRepo = new CompanySettingRepository();
        $this->requireAuth();
    }

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

    /**
     * Listado de notas de crédito.
     */
    public function index()
    {
        $creditNotes = $this->cnRepo->getAll();
        require_once __DIR__ . '/../../views/credit_notes/index.php';
    }

    /**
     * Formulario para crear nota de crédito.
     */
    public function createForm($saleId)
    {
        $saleData = $this->saleRepo->findById($saleId);
        if (!$saleData) {
            $_SESSION['error'] = 'Venta no encontrada.';
            header('Location: ' . BASE_URL . 'sales.php');
            exit;
        }

        $invoice = $this->invoiceRepo->findBySaleId($saleId);
        if (!$invoice || $invoice['estado_sri'] !== 'autorizada') {
            $_SESSION['error'] = 'La venta no tiene una factura electrónica autorizada.';
            header('Location: ' . BASE_URL . 'sales.php?action=show&id=' . $saleId);
            exit;
        }

        if ($this->cnRepo->existsTotalCreditNoteForSale($saleId)) {
            $_SESSION['error'] = 'Esta venta ya tiene una Nota de Crédito total.';
            header('Location: ' . BASE_URL . 'sales.php?action=show&id=' . $saleId);
            exit;
        }

        require_once __DIR__ . '/../../views/credit_notes/create.php';
    }

    /**
     * Procesar y guardar nota de crédito.
     */
    public function store()
    {
        $this->validateCSRF();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'credit_notes.php');
            exit;
        }

        $data = $this->sanitizeInputData($_POST);

        $saleId = (int)($data['sale_id'] ?? 0);
        $companyId = (int)($data['company_id'] ?? 0);
        $motivo = trim($data['motivo'] ?? '');
        $items = $data['items'] ?? [];

        if (!$saleId || !$companyId || empty($motivo) || empty($items)) {
            $_SESSION['error'] = 'Datos incompletos para la Nota de Crédito.';
            header('Location: ' . BASE_URL . 'sales.php?action=show&id=' . $saleId);
            exit;
        }

        $allowedMotivos = ['Devolución total', 'Devolución parcial', 'Cambio de producto'];
        if (!in_array($motivo, $allowedMotivos)) {
            $_SESSION['error'] = 'Motivo no válido.';
            header('Location: ' . BASE_URL . 'credit_notes.php?action=create&sale_id=' . $saleId);
            exit;
        }

        // Cargar datos
        $saleData = $this->saleRepo->findById($saleId);
        if (!$saleData) {
            $_SESSION['error'] = 'Venta no encontrada.';
            header('Location: ' . BASE_URL . 'sales.php');
            exit;
        }

        $invoice = $this->invoiceRepo->findBySaleId($saleId);
        if (!$invoice || $invoice['estado_sri'] !== 'autorizada') {
            $_SESSION['error'] = 'La venta no tiene factura autorizada.';
            header('Location: ' . BASE_URL . 'sales.php?action=show&id=' . $saleId);
            exit;
        }

        $company = $this->companyRepo->findById($companyId);
        if (!$company) {
            $_SESSION['error'] = 'Empresa no encontrada.';
            header('Location: ' . BASE_URL . 'credit_notes.php?action=create&sale_id=' . $saleId);
            exit;
        }

        // Obtener datos del cliente para el XML
        $clientRepo = new ClientRepository();
        $clientData = $clientRepo->findById($saleData['sale']['id_client']);

        if ($clientData && is_object($clientData)) {
            $saleData['client'] = [
                'name' => $clientData->getName(),
                'ruc_cedula' => $clientData->getIdentification(),
                'email' => $clientData->getEmail(),
                'tipo_identificacion' => ($clientData->getIdentification() && strlen($clientData->getIdentification()) == 13) ? 'RUC' : 'Cédula',
            ];
        } else {
            $saleData['client'] = ['name' => 'Consumidor Final', 'ruc_cedula' => '9999999999999', 'email' => ''];
        }

        // Filtrar items válidos
        $validDetails = [];
        $subtotal = 0;
        $taxTotal = 0;
        $total = 0;

        foreach ($items as $idx => $item) {
            $medId = (int)($item['medication_id'] ?? 0);
            $batchId = (int)($item['batch_id'] ?? 0);
            $qty = (int)($item['quantity'] ?? 0);
            $unitPrice = (float)($item['unit_price'] ?? 0);
            $taxRate = (float)($item['tax_rate'] ?? 0);

            if ($medId <= 0 || $batchId <= 0 || $qty <= 0) {
                continue;
            }

            // Validar que no se devuelva más de lo vendido en este lote
            $batchesSold = $this->cnRepo->getBatchesSoldInSale($saleId, $medId);
            $maxReturnQty = 0;
            foreach ($batchesSold as $bs) {
                if ((int)$bs['id_batch'] === $batchId) {
                    $maxReturnQty = (int)$bs['quantity'];
                    break;
                }
            }
            if ($qty > $maxReturnQty) {
                $_SESSION['error'] = "Cantidad a devolver excede lo vendido del producto.";
                header('Location: ' . BASE_URL . 'credit_notes.php?action=create&sale_id=' . $saleId);
                exit;
            }

            $itemTotal = $qty * $unitPrice;
            $itemTax = $itemTotal * ($taxRate / 100);
            $itemSubtotal = $itemTotal;

            $validDetails[] = [
                'medication_id' => $medId,
                'batch_id' => $batchId,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'subtotal' => $itemSubtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $itemTax,
                'total' => $itemSubtotal + $itemTax,
                'medication_name' => $item['medication_name'] ?? '',
                'medication_code' => $item['medication_code'] ?? '',
            ];

            $subtotal += $itemSubtotal;
            $taxTotal += $itemTax;
            $total += ($itemSubtotal + $itemTax);
        }

        if (empty($validDetails)) {
            $_SESSION['error'] = 'Debe seleccionar al menos un producto para devolver.';
            header('Location: ' . BASE_URL . 'credit_notes.php?action=create&sale_id=' . $saleId);
            exit;
        }

        $this->db->beginTransaction();

        try {
            // Crear modelo de NC
            $cn = new CreditNoteModel([
                'id_sale' => $saleId,
                'id_original_invoice' => $invoice['id'],
                'company_id' => $companyId,
                'id_user' => $_SESSION['user']['id'] ?? 0,
                'numero_nota_credito' => '',
                'clave_acceso' => '',
                'estado_sri' => 'pendiente',
                'motivo' => $motivo,
                'subtotal' => $subtotal,
                'discount' => 0,
                'tax_total' => $taxTotal,
                'total' => $total,
                'mensaje_sri' => '',
            ]);

            $creditNoteId = $this->cnRepo->create($cn);

            // Insertar detalles
            foreach ($validDetails as $vd) {
                $d = new CreditNoteDetailModel([
                    'id_credit_note' => $creditNoteId,
                    'id_medication' => $vd['medication_id'],
                    'id_batch' => $vd['batch_id'],
                    'quantity' => $vd['quantity'],
                    'unit_price' => $vd['unit_price'],
                    'subtotal' => $vd['subtotal'],
                    'tax_rate' => $vd['tax_rate'],
                    'tax_amount' => $vd['tax_amount'],
                    'total' => $vd['total'],
                ]);
                $this->cnRepo->createDetail($d);
            }

            // Preparar datos para el servicio SRI
            $creditNoteData = [
                'details' => $validDetails,
                'motivo' => $motivo,
                'subtotal' => $subtotal,
                'total' => $total,
                'invoice_num' => $invoice['numero_factura'],
                'invoice_date' => date('d/m/Y', strtotime($saleData['sale']['sale_date'])),
            ];

            // Enviar al SRI
            $sriService = new SriService($company['ambiente']);
            $result = $sriService->procesarNotaCredito($saleData, $creditNoteData, $company);

            if ($result['success']) {
                // Actualizar NC con datos SRI
                $this->cnRepo->updateSriStatus(
                    $creditNoteId,
                    $result['estado_sri'],
                    $result['mensaje_sri'],
                    $result['numero_autorizacion'],
                    $result['xml_path'],
                    $result['xml_firmado_path']
                );

                // Actualizar número y clave
                $this->db->prepare(
                    "UPDATE credit_notes SET numero_nota_credito = :num, clave_acceso = :clave WHERE id = :id"
                )->execute([
                    ':num' => $result['numero_nota_credito'],
                    ':clave' => $result['clave_acceso'],
                    ':id' => $creditNoteId,
                ]);

                // Restaurar stock
                $this->cnRepo->restoreStock($creditNoteId);

                $this->db->commit();

                $_SESSION['success'] = 'Nota de Crédito autorizada por el SRI correctamente. Stock restaurado.';
                header('Location: ' . BASE_URL . 'credit_notes.php?action=show&id=' . $creditNoteId);
                exit;

            } else {
                $this->cnRepo->updateSriStatus(
                    $creditNoteId,
                    $result['estado_sri'] ?? 'rechazada',
                    $result['error'] ?? 'Error al enviar al SRI'
                );
                $this->db->commit();

                $_SESSION['error'] = 'Error del SRI: ' . ($result['error'] ?? 'Error desconocido');
                header('Location: ' . BASE_URL . 'credit_notes.php?action=show&id=' . $creditNoteId);
                exit;
            }

        } catch (Exception $e) {
            $this->db->rollBack();
            $this->db->prepare(
                "UPDATE credit_notes SET estado_sri = 'rechazada', mensaje_sri = :msg WHERE id = :id"
            )->execute([
                ':msg' => $e->getMessage(),
                ':id' => $creditNoteId ?? 0,
            ]);
            $_SESSION['error'] = 'Error al procesar Nota de Crédito: ' . $e->getMessage();
            header('Location: ' . BASE_URL . 'credit_notes.php?action=create&sale_id=' . $saleId);
            exit;
        }
    }

    /**
     * Mostrar detalle de una nota de crédito.
     */
    public function show($id)
    {
        $creditNote = $this->cnRepo->findById($id);
        if (!$creditNote) {
            $_SESSION['error'] = 'Nota de Crédito no encontrada.';
            header('Location: ' . BASE_URL . 'credit_notes.php');
            exit;
        }

        require_once __DIR__ . '/../../views/credit_notes/show.php';
    }
}
