<?php
/**
 * Location: vetapp/public/medications.php
 */

session_start();

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/controllers/medications/MedicationController.php';

$controller = new MedicationController();

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
            header('Location: ' . BASE_URL . 'medications.php');
        break;
    case 'edit':
        $id = $_GET['id'] ?? null;
        if ($id)
            $controller->edit($id);
        else
            header('Location: ' . BASE_URL . 'medications.php');
        break;
    case 'update':
        $controller->update();
        break;
    case 'deactivate':
        $id = $_GET['id'] ?? null;
        if ($id)
            $controller->deactivate($id);
        else
            header('Location: ' . BASE_URL . 'medications.php');
        break;
    case 'addBatch':
        $controller->addBatch();
        break;
    case 'addActiveIngredient':
        $controller->addActiveIngredient();
        break;
    case 'storeAccessory':
        $controller->storeAccessory();
        break;
    case 'inactive':
        $controller->inactive();
        break;
    case 'reactivate':
        $id = $_GET['id'] ?? null;
        if ($id)
            $controller->reactivate($id);
        else
            header('Location: ' . BASE_URL . 'medications.php');
        break;
    default:
        $controller->index();
}