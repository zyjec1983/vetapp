<?php
/**
 * Location: vetapp/app/controllers/clients/ClientController.php
 */

require_once __DIR__ . '/../BaseController.php';
require_once __DIR__ . '/../../repositories/ClientRepository.php';
require_once __DIR__ . '/../../models/ClientModel.php';

class ClientController extends BaseController
{
    private $clientRepo;

    // ********** Constructor: inicializa repositorio y verifica autenticación **********
    public function __construct()
    {
        parent::__construct();
        $this->clientRepo = new ClientRepository();
        $this->requireAuth();
    }

    // ********** Verifica que el usuario esté autenticado y tenga rol admin o veterinario **********
    private function requireAuth()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . 'login.php');
            exit;
        }
        $roles = $_SESSION['user']['roles'] ?? [];
        if (!in_array('admin', $roles) && !in_array('veterinarian', $roles)) {
            $_SESSION['error'] = 'No tienes permiso para acceder a esta sección.';
            header('Location: ' . BASE_URL . 'dashboard.php');
            exit;
        }
    }

    // ********** Listar todos los clientes activos **********
    public function index()
    {
        $clients = $this->clientRepo->getAll();
        require_once __DIR__ . '/../../views/clients/index.php';
    }

    // ********** Mostrar formulario para crear cliente **********
    public function create()
    {
        require_once __DIR__ . '/../../views/clients/create.php';
    }

    // ********** Guardar nuevo cliente **********
    public function store()
    {
        // ********** Verificar CSRF **********
        $this->validateCSRF();

        // ********** Validar método POST **********
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'clients.php');
            exit;
        }

        // ********** Sanitizar todos los datos POST **********
        $data = $this->sanitizeInputData($_POST);

        // ********** Validaciones **********
        $errors = [];
        if (empty($data['name']))
            $errors[] = 'El nombre es obligatorio.';
        if (empty($data['lastname1']))
            $errors[] = 'El primer apellido es obligatorio.';
        if (empty($data['phone']))
            $errors[] = 'El teléfono es obligatorio.';
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El correo electrónico no es válido.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            header('Location: ' . BASE_URL . 'clients.php?action=create');
            exit;
        }

        // Crear modelo con datos sanitizados
        $client = new ClientModel([
            'name' => $data['name'],
            'middlename' => $data['middlename'] ?? '',
            'lastname1' => $data['lastname1'],
            'lastname2' => $data['lastname2'] ?? '',
            'phone' => $data['phone'],
            'email' => $data['email'] ?? '',
            'address' => $data['address'] ?? '',
            'identification' => $data['identification'] ?? '',
            'observations' => $data['observations'] ?? ''
        ]);

        if ($this->clientRepo->create($client)) {
            $_SESSION['success'] = 'Cliente creado correctamente.';
        } else {
            $_SESSION['error'] = 'Error al guardar el cliente.';
        }

        header('Location: ' . BASE_URL . 'clients.php');
        exit;
    }

    // ********** Mostrar formulario para editar cliente **********
    public function edit($id)
    {
        $client = $this->clientRepo->findById($id);
        if (!$client) {
            $_SESSION['error'] = 'Cliente no encontrado.';
            header('Location: ' . BASE_URL . 'clients.php');
            exit;
        }
        require_once __DIR__ . '/../../views/clients/edit.php';
    }

    // ********** Actualizar datos de cliente **********
    public function update()
    {
        // ********** Verificar CSRF **********
        $this->validateCSRF();

        // ********** Validar método POST **********
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'clients.php');
            exit;
        }

        // ********** Sanitizar todos los datos POST **********
        $data = $this->sanitizeInputData($_POST);

        $id = $data['id_client'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'ID de cliente no proporcionado.';
            header('Location: ' . BASE_URL . 'clients.php');
            exit;
        }

        $client = $this->clientRepo->findById($id);
        if (!$client) {
            $_SESSION['error'] = 'Cliente no encontrado.';
            header('Location: ' . BASE_URL . 'clients.php');
            exit;
        }

        // ********** Validaciones **********
        $errors = [];
        if (empty($data['name']))
            $errors[] = 'El nombre es obligatorio.';
        if (empty($data['lastname1']))
            $errors[] = 'El primer apellido es obligatorio.';
        if (empty($data['phone']))
            $errors[] = 'El teléfono es obligatorio.';
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El correo electrónico no es válido.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            header('Location: ' . BASE_URL . 'clients.php?action=edit&id=' . $id);
            exit;
        }

        // Actualizar propiedades con datos sanitizados
        $client->setName($data['name']);
        $client->setMiddlename($data['middlename'] ?? '');
        $client->setLastname1($data['lastname1']);
        $client->setLastname2($data['lastname2'] ?? '');
        $client->setPhone($data['phone']);
        $client->setEmail($data['email'] ?? '');
        $client->setAddress($data['address'] ?? '');
        $client->setIdentification($data['identification'] ?? '');
        $client->setObservations($data['observations'] ?? '');

        if ($this->clientRepo->update($client)) {
            $_SESSION['success'] = 'Cliente actualizado correctamente.';
        } else {
            $_SESSION['error'] = 'Error al actualizar el cliente.';
        }

        header('Location: ' . BASE_URL . 'clients.php');
        exit;
    }

    // ********** Desactivar cliente (soft delete) **********
    public function deactivate($id)
    {
        $client = $this->clientRepo->findById($id);
        if (!$client) {
            $_SESSION['error'] = 'Cliente no encontrado.';
            header('Location: ' . BASE_URL . 'clients.php');
            exit;
        }

        if ($this->clientRepo->deactivate($id)) {
            $_SESSION['success'] = 'Cliente desactivado correctamente.';
        } else {
            $_SESSION['error'] = 'Error al desactivar el cliente.';
        }

        header('Location: ' . BASE_URL . 'clients.php');
        exit;
    }

    // ********** Buscar clientes para autocomplete (evita XSS en búsqueda) **********
    public function search()
    {
        if (!isset($_GET['q']) || empty($_GET['q'])) {
            echo json_encode([]);
            exit;
        }
        $term = sanitizeInput($_GET['q']);
        $clients = $this->clientRepo->search($term);
        $result = [];
        foreach ($clients as $client) {
            $result[] = [
                'id' => $client->getIdClient(),
                'text' => $client->getFullName() . ' (' . $client->getIdentification() . ')',
                'identification' => $client->getIdentification(),
                'phone' => $client->getPhone()
            ];
        }
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
}
