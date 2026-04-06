<?php
/**
 * Location: vetapp/app/controllers/medications/MedicationController.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../BaseController.php';
require_once __DIR__ . '/../../repositories/MedicationRepository.php';
require_once __DIR__ . '/../../repositories/BatchRepository.php';
require_once __DIR__ . '/../../repositories/ActiveIngredientRepository.php';
require_once __DIR__ . '/../../models/MedicationModel.php';
require_once __DIR__ . '/../../models/BatchModel.php';
require_once __DIR__ . '/../../helpers/auth.php';

class MedicationController extends BaseController
{
    private $medRepo;
    private $batchRepo;
    private $activeRepo;

    public function __construct()
    {
        parent::__construct();
        $this->medRepo = new MedicationRepository();
        $this->batchRepo = new BatchRepository();
        $this->activeRepo = new ActiveIngredientRepository($this->db);
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

    public function index()
    {
        $medications = $this->medRepo->getAll();
        require_once __DIR__ . '/../../views/medications/index.php';
    }

    public function create()
    {
        $activeIngredients = $this->activeRepo->getAll();
        require_once __DIR__ . '/../../views/medications/create.php';
    }

    /**
     * Guardar nuevo medicamento
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'medications.php');
            exit;
        }

        $errors = [];
        if (empty($_POST['code']))
            $errors[] = 'El código es obligatorio.';
        if (empty($_POST['name']))
            $errors[] = 'El nombre es obligatorio.';
        if (empty($_POST['sale_price']) || !is_numeric($_POST['sale_price'])) {
            $errors[] = 'El precio de venta es obligatorio y debe ser un número.';
        }
        if (empty($_POST['minimum_stock']) || !is_numeric($_POST['minimum_stock'])) {
            $errors[] = 'El stock mínimo es obligatorio y debe ser un número.';
        }

        // ***********  Verificar si el código ya existe *********** 
        $existing = $this->medRepo->findByCode($_POST['code']);
        if ($existing) {
            $errors[] = 'El código ya existe. Por favor, use otro.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            header('Location: ' . BASE_URL . 'medications.php?action=create');
            exit;
        }

        $med = new MedicationModel([
            'code' => trim($_POST['code']),
            'name' => trim($_POST['name']),
            'id_active' => !empty($_POST['id_active']) ? $_POST['id_active'] : null,
            'category' => trim($_POST['category'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'minimum_stock' => (int) $_POST['minimum_stock'],
            'sale_price' => (float) $_POST['sale_price'],
            'location' => trim($_POST['location'] ?? ''),
            'active' => 1,
            'taxable' => isset($_POST['taxable']) ? 1 : 0
        ]);

        if ($this->medRepo->create($med)) {
            $medicationId = $this->db->lastInsertId();

            if (!empty($_POST['initial_stock']) && $_POST['initial_stock'] > 0) {
                $batch = new BatchModel([
                    'id_medication' => $medicationId,
                    'batch_number' => 'INICIAL',
                    'expiration_date' => date('Y-m-d', strtotime('+5 years')),
                    'purchase_price' => 0,
                    'quantity_received' => (int) $_POST['initial_stock'],
                    'quantity_remaining' => (int) $_POST['initial_stock']
                ]);
                $this->batchRepo->create($batch);
            }
            $_SESSION['success'] = 'Medicamento registrado correctamente.';
        } else {
            $_SESSION['error'] = 'Error al registrar el medicamento.';
        }

        header('Location: ' . BASE_URL . 'medications.php');
        exit;
    }

    public function show($id)
    {
        $medication = $this->medRepo->findById($id);
        if (!$medication) {
            $_SESSION['error'] = 'Medicamento no encontrado.';
            header('Location: ' . BASE_URL . 'medications.php');
            exit;
        }
        $batches = $this->batchRepo->getByMedication($id);
        require_once __DIR__ . '/../../views/medications/show.php';
    }

    public function edit($id)
    {
        $medication = $this->medRepo->findById($id);
        if (!$medication) {
            $_SESSION['error'] = 'Medicamento no encontrado.';
            header('Location: ' . BASE_URL . 'medications.php');
            exit;
        }
        $activeIngredients = $this->activeRepo->getAll();
        require_once __DIR__ . '/../../views/medications/edit.php';
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'medications.php');
            exit;
        }

        $id = $_POST['id_medication'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'ID de medicamento no proporcionado.';
            header('Location: ' . BASE_URL . 'medications.php');
            exit;
        }

        $medication = $this->medRepo->findById($id);
        if (!$medication) {
            $_SESSION['error'] = 'Medicamento no encontrado.';
            header('Location: ' . BASE_URL . 'medications.php');
            exit;
        }

        // Validaciones
        $errors = [];
        if (empty($_POST['code']))
            $errors[] = 'El código es obligatorio.';
        if (empty($_POST['name']))
            $errors[] = 'El nombre es obligatorio.';
        if (empty($_POST['sale_price']) || !is_numeric($_POST['sale_price'])) {
            $errors[] = 'El precio de venta es obligatorio y debe ser un número.';
        }
        if (empty($_POST['minimum_stock']) || !is_numeric($_POST['minimum_stock'])) {
            $errors[] = 'El stock mínimo es obligatorio y debe ser un número.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            header('Location: ' . BASE_URL . 'medications.php?action=edit&id=' . $id);
            exit;
        }

        // Actualizar propiedades del objeto
        $medication->setCode(trim($_POST['code']));
        $medication->setName(trim($_POST['name']));
        $medication->setIdActive(!empty($_POST['id_active']) ? $_POST['id_active'] : null);
        $medication->setCategory(trim($_POST['category'] ?? ''));
        $medication->setDescription(trim($_POST['description'] ?? ''));
        $medication->setMinimumStock((int) $_POST['minimum_stock']);
        $medication->setSalePrice((float) $_POST['sale_price']);
        $medication->setLocation(trim($_POST['location'] ?? ''));
        // No modificar 'active' a menos que tengas un campo en el formulario
        $medication->setTaxable(isset($_POST['taxable']) ? 1 : 0);

        // Guardar cambios
        if ($this->medRepo->update($medication)) {
            // Crear un nuevo lote si se proporcionó stock inicial
            if (!empty($_POST['initial_stock']) && $_POST['initial_stock'] > 0) {
                $batch = new BatchModel([
                    'id_medication' => $id,
                    'batch_number' => 'EDIT-' . time(),
                    'expiration_date' => date('Y-m-d', strtotime('+5 years')),
                    'purchase_price' => 0,
                    'quantity_received' => (int) $_POST['initial_stock'],
                    'quantity_remaining' => (int) $_POST['initial_stock']
                ]);
                $this->batchRepo->create($batch);
            }
            $_SESSION['success'] = 'Medicamento actualizado correctamente.';
        } else {
            $_SESSION['error'] = 'Error al actualizar el medicamento.';
        }

        header('Location: ' . BASE_URL . 'medications.php');
        exit;
    }

    public function deactivate($id)
    {
        $medication = $this->medRepo->findById($id);
        if (!$medication) {
            $_SESSION['error'] = 'Medicamento no encontrado.';
            header('Location: ' . BASE_URL . 'medications.php');
            exit;
        }

        if ($this->medRepo->deactivate($id)) {
            $_SESSION['success'] = 'Medicamento desactivado correctamente.';
        } else {
            $_SESSION['error'] = 'Error al desactivar el medicamento.';
        }

        header('Location: ' . BASE_URL . 'medications.php');
        exit;
    }

    public function reactivate($id)
    {
        $medication = $this->medRepo->findById($id);
        if (!$medication) {
            $_SESSION['error'] = 'Medicamento no encontrado.';
            header('Location: ' . BASE_URL . 'medications.php');
            exit;
        }

        if ($this->medRepo->reactivate($id)) {
            $_SESSION['success'] = 'Medicamento reactivado correctamente.';
        } else {
            $_SESSION['error'] = 'Error al reactivar el medicamento.';
        }

        header('Location: ' . BASE_URL . 'medications.php?action=inactive');
        exit;
    }

    public function inactive()
    {
        $medications = $this->medRepo->getAllInactive(); // método nuevo
        require_once __DIR__ . '/../../views/medications/inactive.php';
    }

    public function addBatch()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'medications.php');
            exit;
        }

        $medicationId = $_POST['id_medication'] ?? null;
        if (!$medicationId) {
            $_SESSION['error'] = 'ID de medicamento no proporcionado.';
            header('Location: ' . BASE_URL . 'medications.php');
            exit;
        }

        $medication = $this->medRepo->findById($medicationId);
        if (!$medication) {
            $_SESSION['error'] = 'Medicamento no encontrado.';
            header('Location: ' . BASE_URL . 'medications.php');
            exit;
        }

        $errors = [];
        if (empty($_POST['batch_number']))
            $errors[] = 'El número de lote es obligatorio.';
        if (empty($_POST['expiration_date']))
            $errors[] = 'La fecha de expiración es obligatoria.';
        if (empty($_POST['quantity_received']) || !is_numeric($_POST['quantity_received']) || $_POST['quantity_received'] <= 0) {
            $errors[] = 'La cantidad recibida debe ser un número positivo.';
        }
        if (!empty($_POST['purchase_price']) && !is_numeric($_POST['purchase_price'])) {
            $errors[] = 'El precio de compra debe ser un número.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            header('Location: ' . BASE_URL . 'medications.php?action=show&id=' . $medicationId);
            exit;
        }

        $batch = new BatchModel([
            'id_medication' => $medicationId,
            'batch_number' => trim($_POST['batch_number']),
            'expiration_date' => $_POST['expiration_date'],
            'purchase_price' => !empty($_POST['purchase_price']) ? (float) $_POST['purchase_price'] : null,
            'quantity_received' => (int) $_POST['quantity_received'],
            'quantity_remaining' => (int) $_POST['quantity_received']
        ]);

        if ($this->batchRepo->create($batch)) {
            $_SESSION['success'] = 'Lote agregado correctamente.';
        } else {
            $_SESSION['error'] = 'Error al agregar el lote.';
        }

        header('Location: ' . BASE_URL . 'medications.php?action=show&id=' . $medicationId);
        exit;
    }

    public function addActiveIngredient()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['error' => 'El nombre del principio activo es obligatorio']);
            exit;
        }

        $stmt = $this->db->prepare("SELECT id_active FROM active_ingredients WHERE name = :name");
        $stmt->execute([':name' => $name]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'Este principio activo ya existe']);
            exit;
        }

        $stmt = $this->db->prepare("INSERT INTO active_ingredients (name, description) VALUES (:name, :description)");
        $success = $stmt->execute([
            ':name' => $name,
            ':description' => $description
        ]);

        if ($success) {
            $newId = $this->db->lastInsertId();
            echo json_encode(['id' => $newId, 'name' => $name]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al guardar el principio activo']);
        }
        exit;
    }

    /**
     * Guardar un accesorio / producto no medicamento
     */
    public function storeAccessory()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'medications.php');
            exit;
        }

        $errors = [];
        if (empty($_POST['code']))
            $errors[] = 'El código es obligatorio.';
        if (empty($_POST['name']))
            $errors[] = 'El nombre es obligatorio.';
        if (empty($_POST['sale_price']) || !is_numeric($_POST['sale_price'])) {
            $errors[] = 'El precio de venta es obligatorio y debe ser un número.';
        }
        if (empty($_POST['initial_stock']) || !is_numeric($_POST['initial_stock']) || $_POST['initial_stock'] < 0) {
            $errors[] = 'El stock inicial debe ser un número válido.';
        }

        // Verificar si el código ya existe (evitar duplicados)
        $existing = $this->medRepo->findByCode($_POST['code']);

        if ($existing) {
            $errors[] = 'El código ya existe. Por favor, use otro.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            header('Location: ' . BASE_URL . 'medications.php?action=create');
            exit;
        }

        $med = new MedicationModel([
            'code' => trim($_POST['code']),
            'name' => trim($_POST['name']),
            'category' => 'Accesorios y otros',
            'description' => trim($_POST['description'] ?? ''),
            'minimum_stock' => (int) ($_POST['minimum_stock'] ?? 0),
            'sale_price' => (float) $_POST['sale_price'],
            'location' => trim($_POST['location'] ?? ''),
            'active' => 1,
            'taxable' => isset($_POST['taxable']) ? 1 : 0
        ]);

        if ($this->medRepo->create($med)) {
            $medicationId = $this->db->lastInsertId();

            $batch = new BatchModel([
                'id_medication' => $medicationId,
                'batch_number' => 'INICIAL',
                'expiration_date' => date('Y-m-d', strtotime('+5 years')),
                'purchase_price' => 0,
                'quantity_received' => (int) $_POST['initial_stock'],
                'quantity_remaining' => (int) $_POST['initial_stock']
            ]);
            $this->batchRepo->create($batch);

            $_SESSION['success'] = 'Accesorio registrado correctamente.';
        } else {
            $_SESSION['error'] = 'Error al registrar el accesorio.';
        }

        header('Location: ' . BASE_URL . 'medications.php');
        exit;
    }
}