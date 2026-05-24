<?php
/**
 * Location: vetapp/public/credit_notes.php
 *
 * Router para Notas de Crédito Electrónicas.
 * Sigue el patrón MVC + Repository del sistema.
 */

session_start();

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/controllers/credit_notes/CreditNoteController.php';

$controller = new CreditNoteController();

$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'create':
        $saleId = $_GET['sale_id'] ?? null;
        if ($saleId)
            $controller->createForm($saleId);
        else
            header('Location: ' . BASE_URL . 'sales.php');
        break;

    case 'store':
        $controller->store();
        break;

    case 'show':
        $id = $_GET['id'] ?? null;
        if ($id)
            $controller->show($id);
        else
            header('Location: ' . BASE_URL . 'credit_notes.php');
        break;

    default:
        $controller->index();
}
