<?php
// app/controllers/clients/ClientController.php

require_once __DIR__ . '/../BaseController.php';
require_once __DIR__ . '/../../repositories/ClientRepository.php';
require_once __DIR__ . '/../../models/ClientModel.php';

class ClientController extends BaseController
{
    private $clientRepo;

    public function __construct()
    {
        parent::__construct();
        $this->clientRepo = new ClientRepository();
        $this->requireAuth();
    }

    private function requireAuth()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . 'login.php');
            exit;
        }
        // Solo admin y veterinario pueden acceder
        $roles = $_SESSION['user']['roles'] ?? [];
        if (!in_array('admin', $roles) && !in_array('veterinarian', $roles)) {
            $_SESSION['error'] = 'No tienes permiso para acceder a esta sección.';
            header('Location: ' . BASE_URL . 'dashboard.php');
            exit;
        }
    }

    public function index()
    {
        $clients = $this->clientRepo->getAll();
        require_once __DIR__ . '/../../views/clients/index.php';
    }

    public function create()
    {
        require_once __DIR__ . '/../../views/clients/create.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'clients.php');
            exit;
        }

        // Validación simple
        $errors = [];
        if (empty($_POST['name']))
            $errors[] = 'El nombre es obligatorio.';
        if (empty($_POST['lastname1']))
            $errors[] = 'El primer apellido es obligatorio.';
        if (empty($_POST['phone']))
            $errors[] = 'El teléfono es obligatorio.';
        if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El correo electrónico no es válido.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            header('Location: ' . BASE_URL . 'clients.php?action=create');
            exit;
        }

        $client = new ClientModel([
            'name' => trim($_POST['name']),
            'middlename' => trim($_POST['middlename'] ?? ''),
            'lastname1' => trim($_POST['lastname1']),
            'lastname2' => trim($_POST['lastname2'] ?? ''),
            'phone' => trim($_POST['phone']),
            'email' => trim($_POST['email'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'identification' => trim($_POST['identification'] ?? ''),
            'observations' => trim($_POST['observations'] ?? '')
        ]);

        if ($this->clientRepo->create($client)) {
            $_SESSION['success'] = 'Cliente creado correctamente.';
        } else {
            $_SESSION['error'] = 'Error al guardar el cliente.';
        }

        header('Location: ' . BASE_URL . 'clients.php');
        exit;
    }

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

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'clients.php');
            exit;
        }

        $id = $_POST['id_client'] ?? null;
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

        // Validación
        $errors = [];
        if (empty($_POST['name']))
            $errors[] = 'El nombre es obligatorio.';
        if (empty($_POST['lastname1']))
            $errors[] = 'El primer apellido es obligatorio.';
        if (empty($_POST['phone']))
            $errors[] = 'El teléfono es obligatorio.';
        if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El correo electrónico no es válido.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            header('Location: ' . BASE_URL . 'clients.php?action=edit&id=' . $id);
            exit;
        }

        $client->setName(trim($_POST['name']));
        $client->setMiddlename(trim($_POST['middlename'] ?? ''));
        $client->setLastname1(trim($_POST['lastname1']));
        $client->setLastname2(trim($_POST['lastname2'] ?? ''));
        $client->setPhone(trim($_POST['phone']));
        $client->setEmail(trim($_POST['email'] ?? ''));
        $client->setAddress(trim($_POST['address'] ?? ''));
        $client->setIdentification(trim($_POST['identification'] ?? ''));
        $client->setObservations(trim($_POST['observations'] ?? ''));

        if ($this->clientRepo->update($client)) {
            $_SESSION['success'] = 'Cliente actualizado correctamente.';
        } else {
            $_SESSION['error'] = 'Error al actualizar el cliente.';
        }

        header('Location: ' . BASE_URL . 'clients.php');
        exit;
    }   

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

    // Búsqueda en vivo para autocomplete (usado en consultas)
    public function search()
    {
        if (!isset($_GET['q']) || empty($_GET['q'])) {
            echo json_encode([]);
            exit;
        }
        $term = $_GET['q'];
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
?>

<?php
/**
 * Location: vetapp/app/controllers/clients/ClientController.php
 */
/*
require_once __DIR__ . '/../BaseController.php';
require_once __DIR__ . '/../../repositories/ClientRepository.php';
require_once __DIR__ . '/../../helpers/alert.php';
require_once __DIR__ . '/../../helpers/redirect.php';

// Si no existe función redirect global, la definimos aquí
if (!function_exists('redirect')) {
    function redirect($url) {
        header('Location: ' . BASE_URL . $url);
        exit;
    }
}

class ClientController extends BaseController
{
    private $clientRepo;

    public function __construct()
    {
        parent::__construct();
        $this->clientRepo = new ClientRepository($this->db);
        $this->requireAuth();
    }

    private function requireAuth()
    {
        // Verificar autenticación y roles (admin o veterinarian)
        if (!isset($_SESSION['user'])) {
            redirect('login.php');
        }
        $userRoles = $_SESSION['user']['roles'] ?? [];
        if (!in_array('admin', $userRoles) && !in_array('veterinarian', $userRoles)) {
            // No autorizado, redirigir a dashboard o mostrar error
            Alert::error('No tienes permiso para acceder a esta sección.');
            redirect('dashboard.php');
        }
    }

    public function index()
    {
        $clients = $this->clientRepo->getAll();
        require_once __DIR__ . '/../../views/clients/index.php';
    }

    public function create()
    {
        require_once __DIR__ . '/../../views/clients/create.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('clients.php');
            return;
        }

        // Validaciones básicas
        $errors = [];
        if (empty($_POST['name'])) {
            $errors[] = 'El nombre es obligatorio.';
        }
        if (empty($_POST['lastname1'])) {
            $errors[] = 'El primer apellido es obligatorio.';
        }
        if (empty($_POST['phone'])) {
            $errors[] = 'El teléfono es obligatorio.';
        }
        if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El correo electrónico no es válido.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            redirect('clients.php?action=create');
            return;
        }

        // Crear modelo
        $client = new ClientModel([
            'name'          => trim($_POST['name']),
            'middlename'    => trim($_POST['middlename'] ?? ''),
            'lastname1'     => trim($_POST['lastname1']),
            'lastname2'     => trim($_POST['lastname2'] ?? ''),
            'phone'         => trim($_POST['phone']),
            'email'         => trim($_POST['email'] ?? ''),
            'address'       => trim($_POST['address'] ?? ''),
            'identification'=> trim($_POST['identification'] ?? ''),
            'observations'  => trim($_POST['observations'] ?? '')
        ]);

        if ($this->clientRepo->create($client)) {
            Alert::success('Cliente creado correctamente.');
            redirect('clients.php');
        } else {
            Alert::error('Error al guardar el cliente.');
            redirect('clients.php?action=create');
        }
    }

    public function edit($id)
    {
        $client = $this->clientRepo->findById($id);
        if (!$client) {
            Alert::error('Cliente no encontrado.');
            redirect('clients.php');
            return;
        }
        require_once __DIR__ . '/../../views/clients/edit.php';
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('clients.php');
            return;
        }

        $id = $_POST['id_client'] ?? null;
        if (!$id) {
            Alert::error('ID de cliente no proporcionado.');
            redirect('clients.php');
            return;
        }

        $client = $this->clientRepo->findById($id);
        if (!$client) {
            Alert::error('Cliente no encontrado.');
            redirect('clients.php');
            return;
        }

        // Validaciones
        $errors = [];
        if (empty($_POST['name'])) {
            $errors[] = 'El nombre es obligatorio.';
        }
        if (empty($_POST['lastname1'])) {
            $errors[] = 'El primer apellido es obligatorio.';
        }
        if (empty($_POST['phone'])) {
            $errors[] = 'El teléfono es obligatorio.';
        }
        if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El correo electrónico no es válido.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            redirect('clients.php?action=edit&id=' . $id);
            return;
        }

        // Actualizar modelo
        $client->setName(trim($_POST['name']));
        $client->setMiddlename(trim($_POST['middlename'] ?? ''));
        $client->setLastname1(trim($_POST['lastname1']));
        $client->setLastname2(trim($_POST['lastname2'] ?? ''));
        $client->setPhone(trim($_POST['phone']));
        $client->setEmail(trim($_POST['email'] ?? ''));
        $client->setAddress(trim($_POST['address'] ?? ''));
        $client->setIdentification(trim($_POST['identification'] ?? ''));
        $client->setObservations(trim($_POST['observations'] ?? ''));

        if ($this->clientRepo->update($client)) {
            Alert::success('Cliente actualizado correctamente.');
            redirect('clients.php');
        } else {
            Alert::error('Error al actualizar el cliente.');
            redirect('clients.php?action=edit&id=' . $id);
        }
    }

    public function destroy($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('clients.php');
            return;
        }

        $client = $this->clientRepo->findById($id);
        if (!$client) {
            Alert::error('Cliente no encontrado.');
            redirect('clients.php');
            return;
        }

        // Verificar si tiene mascotas asociadas (opcional, puedes implementar después)
        // if ($this->clientRepo->hasPets($id)) {
        //     Alert::error('No se puede eliminar el cliente porque tiene mascotas registradas.');
        //     redirect('clients.php');
        //     return;
        // }

        if ($this->clientRepo->delete($id)) {
            Alert::success('Cliente eliminado correctamente.');
        } else {
            Alert::error('Error al eliminar el cliente.');
        }
        redirect('clients.php');
    }

    public function search()
    {
        if (!isset($_GET['q']) || empty($_GET['q'])) {
            echo json_encode([]);
            return;
        }
        $term = $_GET['q'];
        $clients = $this->clientRepo->search($term);

        $result = [];
        foreach ($clients as $client) {
            $result[] = [
                'id'   => $client->getIdClient(),
                'text' => $client->getFullName() . ' (' . $client->getIdentification() . ')',
                'identification' => $client->getIdentification(),
                'phone' => $client->getPhone()
            ];
        }
        header('Content-Type: application/json');
        echo json_encode($result);
    }
}

*/
?>