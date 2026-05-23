<?php
/**
 * Location: vetapp/public/sri.php
 *
 * Router AJAX para acciones del SRI:
 * - Enviar factura al SRI
 * - Consultar estado
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/Database.php';

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => "PHP Error: $errstr en $errfile:$errline"]);
    exit;
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => "Fatal: {$error['message']} en {$error['file']}:{$error['line']}"]);
        exit;
    }
});

require_once __DIR__ . '/../app/services/SriService.php';
require_once __DIR__ . '/../app/repositories/ElectronicInvoiceRepository.php';
require_once __DIR__ . '/../app/repositories/CompanySettingRepository.php';
require_once __DIR__ . '/../app/repositories/SaleRepository.php';
require_once __DIR__ . '/../app/repositories/ClientRepository.php';
require_once __DIR__ . '/../app/models/ElectronicInvoiceModel.php';
require_once __DIR__ . '/../app/helpers/csrf.php';
require_once __DIR__ . '/../app/helpers/sanitize.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'send' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'error' => 'Error de seguridad CSRF.']);
        exit;
    }

    $saleId = (int)($_POST['sale_id'] ?? 0);
    $companyId = (int)($_POST['company_id'] ?? 0);

    if (!$saleId || !$companyId) {
        echo json_encode(['success' => false, 'error' => 'Datos incompletos.']);
        exit;
    }

    try {
        $saleRepo = new SaleRepository();
        $saleData = $saleRepo->findById($saleId);
        if (!$saleData) {
            echo json_encode(['success' => false, 'error' => 'Venta no encontrada.']);
            exit;
        }

        $clientRepo = new ClientRepository();
        $clientData = $clientRepo->findById($saleData['sale']['id_client'] ?? 0);

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

        $companyRepo = new CompanySettingRepository();
        $companyData = $companyRepo->findById($companyId);
        if (!$companyData) {
            echo json_encode(['success' => false, 'error' => 'Empresa no encontrada.']);
            exit;
        }

        $sriService = new SriService($companyData['ambiente'] ?? 'pruebas');
        $resultado = $sriService->procesarFactura($saleData, $companyData);

        if ($resultado['success']) {
            $invoiceRepo = new ElectronicInvoiceRepository();
            $invoice = new ElectronicInvoiceModel([
                'id_sale' => $saleId,
                'company_id' => $companyId,
                'numero_factura' => $resultado['numero_factura'],
                'clave_acceso' => $resultado['clave_acceso'],
                'estado_sri' => $resultado['estado_sri'],
                'mensaje_sri' => $resultado['mensaje_sri'],
            ]);

            $invoiceId = $invoiceRepo->create($invoice);

            $invoiceRepo->updateEstado(
                $invoiceId,
                $resultado['estado_sri'],
                $resultado['mensaje_sri'],
                $resultado['numero_autorizacion'],
                $resultado['xml_path'],
                $resultado['xml_firmado_path']
            );

            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE sales SET company_id = :company_id WHERE id_sale = :id");
            $stmt->execute([':company_id' => $companyId, ':id' => $saleId]);

            echo json_encode([
                'success' => true,
                'mensaje' => $resultado['mensaje_sri'],
                'numero_autorizacion' => $resultado['numero_autorizacion'],
                'numero_factura' => $resultado['numero_factura'],
            ]);
        } elseif ($resultado['estado_sri'] === 'pendiente_autorizacion') {
            echo json_encode([
                'success' => false,
                'pending' => true,
                'error' => $resultado['mensaje_sri'] ?? 'El comprobante está pendiente de autorización.',
                'clave_acceso' => $resultado['clave_acceso'] ?? '',
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'pending' => false,
                'error' => $resultado['error'] ?? $resultado['mensaje_sri'] ?? 'Error desconocido',
                'observaciones' => $resultado['observaciones'] ?? [],
            ]);
        }

    } catch (Exception $e) {
        $logDir = STORAGE_PATH . '/logs/';
        if (!is_dir($logDir)) mkdir($logDir, 0755, true);
        file_put_contents($logDir . 'sri_errors.log', "[" . date('Y-m-d H:i:s') . "] sri.php exception: " . $e->getMessage() . "\n", FILE_APPEND);
        echo json_encode([
            'success' => false,
            'error' => 'Error interno: ' . $e->getMessage(),
        ]);
    }

    exit;
}

echo json_encode(['success' => false, 'error' => 'Acción no válida.']);
