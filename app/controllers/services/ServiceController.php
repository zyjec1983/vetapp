<?php
// app/controllers/services/ServiceController.php

require_once __DIR__ . '/../BaseController.php';
require_once __DIR__ . '/../../repositories/ServiceRepository.php';
require_once __DIR__ . '/../../models/ServiceModel.php';

class ServiceController extends BaseController
{
    private $serviceRepo;

    public function __construct()
    {
        parent::__construct();
        $this->serviceRepo = new ServiceRepository();
        $this->requireAuth();
        $this->requireAdmin();
    }

    private function requireAuth()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . 'login.php');
            exit;
        }
    }

    private function requireAdmin()
    {
        $roles = $_SESSION['user']['roles'] ?? [];
        if (!in_array('admin', $roles)) {
            $_SESSION['error'] = 'No tienes permiso para acceder a esta sección.';
            header('Location: ' . BASE_URL . 'dashboard.php');
            exit;
        }
    }

    public function index()
    {
        $services = $this->serviceRepo->getAll();
        require_once __DIR__ . '/../../views/services/index.php';
    }

    public function create()
    {
        require_once __DIR__ . '/../../views/services/create.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'services.php');
            exit;
        }

        $errors = [];
        if (empty($_POST['name'])) $errors[] = 'El nombre es obligatorio.';
        if (empty($_POST['price']) || !is_numeric($_POST['price'])) {
            $errors[] = 'El precio es obligatorio y debe ser un número.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            header('Location: ' . BASE_URL . 'services.php?action=create');
            exit;
        }

        $service = new ServiceModel([
            'name'        => trim($_POST['name']),
            'description' => trim($_POST['description'] ?? ''),
            'price'       => (float) $_POST['price'],
            'taxable'     => isset($_POST['taxable']) ? 1 : 0,
            'active'      => 1
        ]);

        if ($this->serviceRepo->create($service)) {
            $_SESSION['success'] = 'Servicio registrado correctamente.';
        } else {
            $_SESSION['error'] = 'Error al registrar el servicio.';
        }

        header('Location: ' . BASE_URL . 'services.php');
        exit;
    }

    public function edit($id)
    {
        $service = $this->serviceRepo->findById($id);
        if (!$service) {
            $_SESSION['error'] = 'Servicio no encontrado.';
            header('Location: ' . BASE_URL . 'services.php');
            exit;
        }
        require_once __DIR__ . '/../../views/services/edit.php';
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'services.php');
            exit;
        }

        $id = $_POST['id_service'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'ID de servicio no proporcionado.';
            header('Location: ' . BASE_URL . 'services.php');
            exit;
        }

        $service = $this->serviceRepo->findById($id);
        if (!$service) {
            $_SESSION['error'] = 'Servicio no encontrado.';
            header('Location: ' . BASE_URL . 'services.php');
            exit;
        }

        $errors = [];
        if (empty($_POST['name'])) $errors[] = 'El nombre es obligatorio.';
        if (empty($_POST['price']) || !is_numeric($_POST['price'])) {
            $errors[] = 'El precio es obligatorio y debe ser un número.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            header('Location: ' . BASE_URL . 'services.php?action=edit&id=' . $id);
            exit;
        }

        $service->setName(trim($_POST['name']));
        $service->setDescription(trim($_POST['description'] ?? ''));
        $service->setPrice((float) $_POST['price']);
        $service->setTaxable(isset($_POST['taxable']) ? 1 : 0);
        $service->setActive(isset($_POST['active']) ? 1 : 0);

        if ($this->serviceRepo->update($service)) {
            $_SESSION['success'] = 'Servicio actualizado correctamente.';
        } else {
            $_SESSION['error'] = 'Error al actualizar el servicio.';
        }

        header('Location: ' . BASE_URL . 'services.php');
        exit;
    }

    public function deactivate($id)
    {
        $service = $this->serviceRepo->findById($id);
        if (!$service) {
            $_SESSION['error'] = 'Servicio no encontrado.';
            header('Location: ' . BASE_URL . 'services.php');
            exit;
        }

        if ($this->serviceRepo->deactivate($id)) {
            $_SESSION['success'] = 'Servicio desactivado correctamente.';
        } else {
            $_SESSION['error'] = 'Error al desactivar el servicio.';
        }

        header('Location: ' . BASE_URL . 'services.php');
        exit;
    }

    public function reactivate($id)
    {
        $service = $this->serviceRepo->findById($id);
        if (!$service) {
            $_SESSION['error'] = 'Servicio no encontrado.';
            header('Location: ' . BASE_URL . 'services.php');
            exit;
        }

        if ($this->serviceRepo->reactivate($id)) {
            $_SESSION['success'] = 'Servicio reactivado correctamente.';
        } else {
            $_SESSION['error'] = 'Error al reactivar el servicio.';
        }

        header('Location: ' . BASE_URL . 'services.php?action=inactive');
        exit;
    }

    public function inactive()
    {
        $services = $this->serviceRepo->getAll(false); // todos, luego filtramos en vista
        require_once __DIR__ . '/../../views/services/inactive.php';
    }
}