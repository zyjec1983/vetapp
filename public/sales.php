<?php
/**
 * Location: vetapp/public/sales.php
 */

session_start();

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/controllers/sales/SaleController.php';

$controller = new SaleController();

$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'create':
        $controller->create();
        break;
    case 'store':
        $controller->store();
        break;
    case 'show':
        $id = $_GET['id'] ?? null;
        if ($id)
            $controller->show($id);
        else
            header('Location: ' . BASE_URL . 'sales.php');
        break;
    case 'cancel':
        $id = $_GET['id'] ?? null;
        if ($id)
            $controller->cancel($id);
        else
            header('Location: ' . BASE_URL . 'sales.php');
        break;
    case 'searchMedications':
        $controller->searchMedications();
        break;
    case 'searchClients':
        $controller->searchClients();
        break;
    case 'pdf':
        $id = $_GET['id'] ?? null;
        if ($id)
            $controller->generatePDF($id);
        else
            header('Location: ' . BASE_URL . 'sales.php');
        break;
    default:
        $controller->index();
}