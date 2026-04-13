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


    // ********** Mostrar formulario para crear medicamento **********
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
        // ********** Verificar CSRF **********
        $this->validateCSRF();

        // ********** Validar método POST ********** 
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'medications.php');
            exit;
        }

        // ********** Sanitizar todos los datos POST ********** 
        $data = $this->sanitizeInputData($_POST);

        // ********** Validaciones ********** 
        $errors = [];
        if (empty($data['code']))
            $errors[] = 'El código es obligatorio.';
        if (empty($data['name']))
            $errors[] = 'El nombre es obligatorio.';
        if (empty($data['sale_price']) || !is_numeric($data['sale_price'])) {
            $errors[] = 'El precio de venta es obligatorio y debe ser un número.';
        }
        if (empty($data['minimum_stock']) || !is_numeric($data['minimum_stock'])) {
            $errors[] = 'El stock mínimo es obligatorio y debe ser un número.';
        }

        // ********** Verificar si el código ya existe ********** 
        $existing = $this->medRepo->findByCode($data['code']);
        if ($existing) {
            $errors[] = 'El código ya existe. Por favor, use otro.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            header('Location: ' . BASE_URL . 'medications.php?action=create');
            exit;
        }

        // ********** Crear modelo con datos sanitizados ********** 
        $med = new MedicationModel([
            'code' => $data['code'],
            'name' => $data['name'],
            'id_active' => !empty($data['id_active']) ? $data['id_active'] : null,
            'category' => $data['category'] ?? '',
            'description' => $data['description'] ?? '',
            'minimum_stock' => (int) $data['minimum_stock'],
            'sale_price' => (float) $data['sale_price'],
            'location' => $data['location'] ?? '',
            'active' => 1,
            'taxable' => isset($data['taxable']) ? 1 : 0
        ]);

        if ($this->medRepo->create($med)) {
            $medicationId = $this->db->lastInsertId();

            // ********** Crear lote inicial si se proporcionó stock ********** 
            if (!empty($data['initial_stock']) && $data['initial_stock'] > 0) {
                $batch = new BatchModel([
                    'id_medication' => $medicationId,
                    'batch_number' => 'INICIAL',
                    'expiration_date' => date('Y-m-d', strtotime('+5 years')),
                    'purchase_price' => 0,
                    'quantity_received' => (int) $data['initial_stock'],
                    'quantity_remaining' => (int) $data['initial_stock']
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

    // ********** Mostrar detalle de medicamento con sus lotes **********
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

    // ********** Mostrar formulario para editar medicamento **********
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
        // ********** Verificar CSRF ********** 
        $this->validateCSRF();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'medications.php');
            exit;
        }

        // ********** Sanitizar todos los datos POST ********** 
        $data = $this->sanitizeInputData($_POST);

        $id = $data['id_medication'] ?? null;
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

        // ********** Validaciones ********** 
        $errors = [];
        if (empty($data['code']))
            $errors[] = 'El código es obligatorio.';
        if (empty($data['name']))
            $errors[] = 'El nombre es obligatorio.';
        if (empty($data['sale_price']) || !is_numeric($data['sale_price'])) {
            $errors[] = 'El precio de venta es obligatorio y debe ser un número.';
        }
        if (empty($data['minimum_stock']) || !is_numeric($data['minimum_stock'])) {
            $errors[] = 'El stock mínimo es obligatorio y debe ser un número.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            header('Location: ' . BASE_URL . 'medications.php?action=edit&id=' . $id);
            exit;
        }

        // ********** Actualizar propiedades del objeto con datos sanitizados ********** 
        $medication->setCode($data['code']);
        $medication->setName($data['name']);
        $medication->setIdActive(!empty($data['id_active']) ? $data['id_active'] : null);
        $medication->setCategory($data['category'] ?? '');
        $medication->setDescription($data['description'] ?? '');
        $medication->setMinimumStock((int) $data['minimum_stock']);
        $medication->setSalePrice((float) $data['sale_price']);
        $medication->setLocation($data['location'] ?? '');
        // No modificar 'active' a menos que tengas un campo en el formulario
        $medication->setTaxable(isset($data['taxable']) ? 1 : 0);

        // ********** Guardar cambios ********** 
        if ($this->medRepo->update($medication)) {
            // Crear un nuevo lote si se proporcionó stock inicial
            if (!empty($data['initial_stock']) && $data['initial_stock'] > 0) {
                $batch = new BatchModel([
                    'id_medication' => $id,
                    'batch_number' => 'EDIT-' . time(),
                    'expiration_date' => date('Y-m-d', strtotime('+5 years')),
                    'purchase_price' => 0,
                    'quantity_received' => (int) $data['initial_stock'],
                    'quantity_remaining' => (int) $data['initial_stock']
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

    // ********** Desactivar medicamento (soft delete) **********
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

    // ********** Reactivar medicamento desactivado **********
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

    // ********** Listar medicamentos desactivados **********
    public function inactive()
    {
        $medications = $this->medRepo->getAllInactive();
        require_once __DIR__ . '/../../views/medications/inactive.php';
    }

    public function addBatch()
    {
        // ********** VERIFICAR CSRF **********
        $this->validateCSRF();

        // ********** VALIDAR MÉTODO POST **********
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'medications.php');
            exit;
        }

        // ********** SANITIZAR DATOS POST **********
        $data = $this->sanitizeInputData($_POST);

        // ********** OBTENER ID DEL MEDICAMENTO **********
        $medicationId = $data['id_medication'] ?? null;
        if (!$medicationId) {
            $_SESSION['error'] = 'ID de medicamento no proporcionado.';
            header('Location: ' . BASE_URL . 'medications.php');
            exit;
        }

        // ********** VERIFICAR QUE EL MEDICAMENTO EXISTA **********
        $medication = $this->medRepo->findById($medicationId);
        if (!$medication) {
            $_SESSION['error'] = 'Medicamento no encontrado.';
            header('Location: ' . BASE_URL . 'medications.php');
            exit;
        }

        // ********** VALIDACIONES DE CAMPOS **********
        $errors = [];
        if (empty($data['batch_number']))
            $errors[] = 'El número de lote es obligatorio.';
        if (empty($data['expiration_date']))
            $errors[] = 'La fecha de expiración es obligatoria.';
        if (empty($data['quantity_received']) || !is_numeric($data['quantity_received']) || $data['quantity_received'] <= 0) {
            $errors[] = 'La cantidad recibida debe ser un número positivo.';
        }
        if (!empty($data['purchase_price']) && !is_numeric($data['purchase_price'])) {
            $errors[] = 'El precio de compra debe ser un número.';
        }

        // ********** SI HAY ERRORES, REDIRIGIR **********
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            header('Location: ' . BASE_URL . 'medications.php?action=show&id=' . $medicationId);
            exit;
        }

        // ********** CREAR MODELO DE LOTE CON DATOS SANITIZADOS **********
        $batch = new BatchModel([
            'id_medication' => $medicationId,
            'batch_number' => $data['batch_number'],
            'expiration_date' => $data['expiration_date'],
            'purchase_price' => !empty($data['purchase_price']) ? (float) $data['purchase_price'] : null,
            'quantity_received' => (int) $data['quantity_received'],
            'quantity_remaining' => (int) $data['quantity_received']
        ]);

        // ********** GUARDAR LOTE **********
        if ($this->batchRepo->create($batch)) {
            $_SESSION['success'] = 'Lote agregado correctamente.';
        } else {
            $_SESSION['error'] = 'Error al agregar el lote.';
        }

        // ********** REDIRIGIR AL DETALLE DEL MEDICAMENTO **********
        header('Location: ' . BASE_URL . 'medications.php?action=show&id=' . $medicationId);
        exit;
    }

    // ********** Agregar principio activo vía AJAX **********
    public function addActiveIngredient()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }

        // ********** Sanitizar datos de entrada **********
        $data = $this->sanitizeInputData($_POST);
        $name = $data['name'] ?? '';
        $description = $data['description'] ?? '';

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
        // ********** VERIFICAR CSRF **********
        $this->validateCSRF();

        // ********** VALIDAR MÉTODO POST **********
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'medications.php');
            exit;
        }

        // ********** SANITIZAR DATOS POST **********
        $data = $this->sanitizeInputData($_POST);

        // ********** VALIDACIONES DE CAMPOS **********
        $errors = [];
        if (empty($data['code']))
            $errors[] = 'El código es obligatorio.';
        if (empty($data['name']))
            $errors[] = 'El nombre es obligatorio.';
        if (empty($data['sale_price']) || !is_numeric($data['sale_price'])) {
            $errors[] = 'El precio de venta es obligatorio y debe ser un número.';
        }
        if (empty($data['initial_stock']) || !is_numeric($data['initial_stock']) || $data['initial_stock'] < 0) {
            $errors[] = 'El stock inicial debe ser un número válido.';
        }

        // ********** VERIFICAR SI EL CÓDIGO YA EXISTE **********
        $existing = $this->medRepo->findByCode($data['code']);
        if ($existing) {
            $errors[] = 'El código ya existe. Por favor, use otro.';
        }

        // ********** SI HAY ERRORES, REDIRIGIR **********
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            header('Location: ' . BASE_URL . 'medications.php?action=create');
            exit;
        }

        // ********** CREAR MODELO DE ACCESORIO CON DATOS SANITIZADOS **********
        $med = new MedicationModel([
            'code' => $data['code'],
            'name' => $data['name'],
            'category' => 'Accesorios y otros',
            'description' => $data['description'] ?? '',
            'minimum_stock' => (int) ($data['minimum_stock'] ?? 0),
            'sale_price' => (float) $data['sale_price'],
            'location' => $data['location'] ?? '',
            'active' => 1,
            'taxable' => isset($data['taxable']) ? 1 : 0
        ]);

        // ********** GUARDAR ACCESORIO **********
        if ($this->medRepo->create($med)) {
            $medicationId = $this->db->lastInsertId();

            // ********** CREAR LOTE INICIAL CON EL STOCK PROPORCIONADO **********
            $batch = new BatchModel([
                'id_medication' => $medicationId,
                'batch_number' => 'INICIAL',
                'expiration_date' => date('Y-m-d', strtotime('+5 years')),
                'purchase_price' => 0,
                'quantity_received' => (int) $data['initial_stock'],
                'quantity_remaining' => (int) $data['initial_stock']
            ]);
            $this->batchRepo->create($batch);

            $_SESSION['success'] = 'Accesorio registrado correctamente.';
        } else {
            $_SESSION['error'] = 'Error al registrar el accesorio.';
        }

        // ********** REDIRIGIR AL LISTADO DE MEDICAMENTOS **********
        header('Location: ' . BASE_URL . 'medications.php');
        exit;
    }


    /**
     * Listar medicamentos con filtro y paginación
     */
    public function index()
    {
        //  ********** Obtener tipo de filtro (medicamentos, accesorios, todos) ********** 
        $type = $_GET['type'] ?? '';
        $page = (int) ($_GET['page'] ?? 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;

        // Validar tipo permitido
        $allowedTypes = ['medicamentos', 'accesorios', 'todos'];
        if (!in_array($type, $allowedTypes)) {
            $type = ''; // No seleccionado
        }

        $medications = [];
        $total = 0;
        $totalPages = 0;

        if ($type !== '') {
            $medications = $this->medRepo->getPaginatedFiltered($type, $limit, $offset);
            $total = $this->medRepo->countFiltered($type);
            $totalPages = ceil($total / $limit);
        }

        require_once __DIR__ . '/../../views/medications/index.php';
    }


}