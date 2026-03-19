<?php
/**
 * Location: vetapp/public/consultations.php
 */

require_once __DIR__ . '/../app/controllers/consultations/ConsultationController.php';

$controller = new ConsultationController();

// Obtener acción (por defecto: index)
$action = $_GET['action'] ?? 'index';


switch ($action) {
    case 'index':
        $controller->index();
        break;

    default:
        echo "Acción no válida";
        break;
}