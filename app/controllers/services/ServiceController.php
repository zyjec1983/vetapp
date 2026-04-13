<?php
/**
 * Location: vetapp/app/controllers/services/ServiceController.php
 */

require_once __DIR__ . '/../BaseController.php';
require_once __DIR__ . '/../../repositories/ServiceRepository.php';
require_once __DIR__ . '/../../models/ServiceModel.php';

class ServiceController extends BaseController
{
    private $serviceRepo;

    // ********** Constructor: inicializa repositorio y verifica autenticación/admin **********
    public function __construct()
    {
        parent::__construct();
        $this->serviceRepo = new ServiceRepository();
        $this->requireAuth();
        $this->requireAdmin();
    }

    // ********** Verifica que el usuario esté autenticado **********
    private function requireAuth()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . 'login.php');
            exit;
        }
    }

    // ********** Verifica que el usuario tenga rol de admin **********
    private function requireAdmin()
    {
        $roles = $_SESSION['user']['roles'] ?? [];
        if (!in_array('admin', $roles)) {
            $_SESSION['error'] = 'No tienes permiso para acceder a esta sección.';
            header('Location: ' . BASE_URL . 'dashboard.php');
            exit;
        }
    }

    // ********** Listar todos los servicios activos **********
    public function index()
    {
        $services = $this->serviceRepo->getAll();
        require_once __DIR__ . '/../../views/services/index.php';
    }

    // ********** Mostrar formulario para crear servicio **********
    public function create()
    {
        require_once __DIR__ . '/../../views/services/create.php';
    }

    // ********** Guardar nuevo servicio **********
    public function store()
    {
        // ********** Verificar CSRF **********
        $this->validateCSRF();

        // ********** Validar método POST **********
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'services.php');
            exit;
        }

        // ********** Sanitizar todos los datos POST **********
        $data = $this->sanitizeInputData($_POST);

        // ********** Validaciones **********
        $errors = [];
        if (empty($data['name'])) $errors[] = 'El nombre es obligatorio.';
        if (empty($data['price']) || !is_numeric($data['price'])) {
            $errors[] = 'El precio es obligatorio y debe ser un número.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            header('Location: ' . BASE_URL . 'services.php?action=create');
            exit;
        }

        // Crear modelo con datos sanitizados
        $service = new ServiceModel([
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'price' => (float) $data['price'],
            'taxable' => isset($data['taxable']) ? 1 : 0,
            'active' => 1
        ]);

        if ($this->serviceRepo->create($service)) {
            $_SESSION['success'] = 'Servicio registrado correctamente.';
        } else {
            $_SESSION['error'] = 'Error al registrar el servicio.';
        }

        header('Location: ' . BASE_URL . 'services.php');
        exit;
    }

    // ********** Mostrar formulario para editar servicio **********
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

    // ********** Actualizar servicio **********
    public function update()
    {
        // ********** Verificar CSRF **********
        $this->validateCSRF();

        // ********** Validar método POST **********
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'services.php');
            exit;
        }

        // ********** Sanitizar todos los datos POST **********
        $data = $this->sanitizeInputData($_POST);

        $id = $data['id_service'] ?? null;
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

        // ********** Validaciones **********
        $errors = [];
        if (empty($data['name'])) $errors[] = 'El nombre es obligatorio.';
        if (empty($data['price']) || !is_numeric($data['price'])) {
            $errors[] = 'El precio es obligatorio y debe ser un número.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            header('Location: ' . BASE_URL . 'services.php?action=edit&id=' . $id);
            exit;
        }

        // Actualizar propiedades con datos sanitizados
        $service->setName($data['name']);
        $service->setDescription($data['description'] ?? '');
        $service->setPrice((float) $data['price']);
        $service->setTaxable(isset($data['taxable']) ? 1 : 0);
        $service->setActive(isset($data['active']) ? 1 : 0);

        if ($this->serviceRepo->update($service)) {
            $_SESSION['success'] = 'Servicio actualizado correctamente.';
        } else {
            $_SESSION['error'] = 'Error al actualizar el servicio.';
        }

        header('Location: ' . BASE_URL . 'services.php');
        exit;
    }

    // ********** Desactivar servicio (soft delete) **********
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

    // ********** Reactivar servicio desactivado **********
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

    // ********** Listar servicios desactivados **********
    public function inactive()
    {
        $services = $this->serviceRepo->getAll(false);
        require_once __DIR__ . '/../../views/services/inactive.php';
    }
}
