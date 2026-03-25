<?php
// public/pets.php

session_start();

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/controllers/pets/PetController.php';

$controller = new PetController();

$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'create':
        $controller->create();
        break;
    case 'store':
        $controller->store();
        break;
    case 'edit':
        $id = $_GET['id'] ?? null;
        if ($id) $controller->edit($id);
        else header('Location: ' . BASE_URL . 'pets.php');
        break;
    case 'update':
        $controller->update();
        break;
    case 'show':
        $id = $_GET['id'] ?? null;
        if ($id) $controller->show($id);
        else header('Location: ' . BASE_URL . 'pets.php');
        break;
    case 'deactivate':
        $id = $_GET['id'] ?? null;
        if ($id) $controller->deactivate($id);
        else header('Location: ' . BASE_URL . 'pets.php');
        break;
    case 'search':
        $controller->search();
        break;
    default:
        $controller->index();
}