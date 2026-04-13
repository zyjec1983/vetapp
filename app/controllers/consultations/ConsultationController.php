<?php
/**
 * Location: vetapp/app/controllers/consultations/ConsultationController.php
 * Controlador de consultas médicas (CRUD completo + recordatorios)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../BaseController.php';
require_once __DIR__ . '/../../repositories/ConsultationRepository.php';
require_once __DIR__ . '/../../repositories/PetRepository.php';
require_once __DIR__ . '/../../repositories/ReminderRepository.php';
require_once __DIR__ . '/../../models/ConsultationModel.php';
require_once __DIR__ . '/../../models/ReminderModel.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../repositories/ServiceRepository.php';
require_once __DIR__ . '/../../models/ServiceModel.php';

class ConsultationController extends BaseController
{
    private $consultationRepo;
    private $petRepo;
    private $reminderRepo;

    // ********** Constructor: inicializa repositorios y verifica autenticación/permisos **********
    public function __construct()
    {
        parent::__construct();
        $this->consultationRepo = new ConsultationRepository($this->db);
        $this->petRepo = new PetRepository();
        $this->reminderRepo = new ReminderRepository();
        $this->requireAuth();
    }

    // ********** Verifica que el usuario esté autenticado y tenga rol de admin o veterinario **********
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

    // ********** Lista todas las consultas médicas **********
    public function index()
    {
        $consultations = $this->consultationRepo->getAll();
        require_once __DIR__ . '/../../views/consultations/index.php';
    }

    // ********** Mostrar formulario para crear consulta **********
    public function create()
    {
        $pets = $this->consultationRepo->getPetsWithClients();
        $serviceRepo = new ServiceRepository();
        $services = $serviceRepo->getAll(true);
        require_once __DIR__ . '/../../views/consultations/create.php';
    }

    // ********** Guardar nueva consulta médica **********
    public function store()
    {
        // ********** Verificar CSRF **********
        $this->validateCSRF();

        // ********** Validar método POST **********
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'consultations.php');
            exit;
        }

        // ********** Sanitizar todos los datos POST **********
        $data = $this->sanitizeInputData($_POST);

        // ********** Validaciones **********
        $errors = [];
        if (empty($data['id_pet']))
            $errors[] = 'Debe seleccionar una mascota.';
        if (empty($data['diagnosis']))
            $errors[] = 'El diagnóstico es obligatorio.';
        if (!empty($data['consultation_fee']) && !is_numeric($data['consultation_fee'])) {
            $errors[] = 'El honorario debe ser un número.';
        }
        if (!empty($data['weight']) && !is_numeric($data['weight']))
            $errors[] = 'El peso debe ser un número.';
        if (!empty($data['temperature']) && !is_numeric($data['temperature']))
            $errors[] = 'La temperatura debe ser un número.';
        if (!empty($data['next_visit'])) {
            $date = DateTime::createFromFormat('Y-m-d', $data['next_visit']);
            if (!$date || $date->format('Y-m-d') !== $data['next_visit']) {
                $errors[] = 'La fecha de próxima visita no es válida.';
            }
        }

        // Si hay errores, redirigir al formulario
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            header('Location: ' . BASE_URL . 'consultations.php?action=create');
            exit;
        }

        // Obtener ID del usuario autenticado
        $user = currentUser();
        $id_user = $user['id'] ?? null;

        if (!$id_user) {
            $_SESSION['error'] = 'No se pudo identificar al usuario.';
            header('Location: ' . BASE_URL . 'login.php');
            exit;
        }

        // Crear modelo de consulta con datos sanitizados
        $consultation = new ConsultationModel([
            'id_pet' => $data['id_pet'],
            'id_user' => $id_user,
            'id_service' => $data['id_service'] ?? null,
            'weight' => !empty($data['weight']) ? (float) $data['weight'] : null,
            'temperature' => !empty($data['temperature']) ? (float) $data['temperature'] : null,
            'diagnosis' => $data['diagnosis'],
            'treatment' => $data['treatment'] ?? '',
            'next_visit' => $data['next_visit'] ?? null,
            'consultation_fee' => !empty($data['consultation_fee']) ? (float) $data['consultation_fee'] : null,
            'status' => $data['status'] ?? 'completed',
            'observations' => $data['observations'] ?? ''
        ]);

        // Guardar consulta
        if ($this->consultationRepo->create($consultation)) {
            // ********** Crear recordatorio si se solicitó **********
            if (isset($data['enable_reminder']) && $data['enable_reminder'] == '1') {
                $pet = $this->petRepo->findById($consultation->getIdPet());
                if ($pet) {
                    $reminder = new ReminderModel();
                    $reminder->setReminderType($data['reminder_type'] ?? 'consultation');
                    $reminder->setIdPet($consultation->getIdPet());
                    $reminder->setIdClient($pet->getIdClient());
                    $reminderDate = !empty($data['reminder_date']) ? $data['reminder_date'] : $consultation->getNextVisit();
                    $reminder->setReminderDate($reminderDate);
                    $reminder->setMessage($data['reminder_message'] ?? '');
                    $reminder->setSent(false);
                    $this->reminderRepo->create($reminder);
                }
            }
            $_SESSION['success'] = 'Consulta registrada correctamente.';
        } else {
            $_SESSION['error'] = 'Error al registrar la consulta.';
        }

        header('Location: ' . BASE_URL . 'consultations.php');
        exit;
    }

    // ********** Mostrar detalle de una consulta **********
    public function show($id)
    {
        $consultation = $this->consultationRepo->findById($id);
        if (!$consultation) {
            $_SESSION['error'] = 'Consulta no encontrada.';
            header('Location: ' . BASE_URL . 'consultations.php');
            exit;
        }
        require_once __DIR__ . '/../../views/consultations/show.php';
    }

    // ********** Mostrar formulario para editar consulta **********
    public function edit($id)
    {
        $consultation = $this->consultationRepo->findById($id);
        if (!$consultation) {
            $_SESSION['error'] = 'Consulta no encontrada.';
            header('Location: ' . BASE_URL . 'consultations.php');
            exit;
        }
        $pets = $this->consultationRepo->getPetsWithClients();
        require_once __DIR__ . '/../../views/consultations/edit.php';
    }

    // ********** Actualizar consulta médica **********
    public function update()
    {
        // ********** Verificar CSRF **********
        $this->validateCSRF();

        // ********** Validar método POST **********
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'consultations.php');
            exit;
        }

        // ********** Sanitizar todos los datos POST **********
        $data = $this->sanitizeInputData($_POST);

        $id = $data['id_consultation'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'ID de consulta no proporcionado.';
            header('Location: ' . BASE_URL . 'consultations.php');
            exit;
        }

        $consultation = $this->consultationRepo->findById($id);
        if (!$consultation) {
            $_SESSION['error'] = 'Consulta no encontrada.';
            header('Location: ' . BASE_URL . 'consultations.php');
            exit;
        }

        // ********** Validaciones **********
        $errors = [];
        if (empty($data['id_pet']))
            $errors[] = 'Debe seleccionar una mascota.';
        if (empty($data['diagnosis']))
            $errors[] = 'El diagnóstico es obligatorio.';
        if (!empty($data['consultation_fee']) && !is_numeric($data['consultation_fee'])) {
            $errors[] = 'El honorario debe ser un número.';
        }
        if (!empty($data['weight']) && !is_numeric($data['weight']))
            $errors[] = 'El peso debe ser un número.';
        if (!empty($data['temperature']) && !is_numeric($data['temperature']))
            $errors[] = 'La temperatura debe ser un número.';
        if (!empty($data['next_visit'])) {
            $date = DateTime::createFromFormat('Y-m-d', $data['next_visit']);
            if (!$date || $date->format('Y-m-d') !== $data['next_visit']) {
                $errors[] = 'La fecha de próxima visita no es válida.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            header('Location: ' . BASE_URL . 'consultations.php?action=edit&id=' . $id);
            exit;
        }

        // Actualizar modelo con datos sanitizados
        $consultation->setIdPet($data['id_pet']);
        $consultation->setWeight(!empty($data['weight']) ? (float) $data['weight'] : null);
        $consultation->setTemperature(!empty($data['temperature']) ? (float) $data['temperature'] : null);
        $consultation->setDiagnosis($data['diagnosis']);
        $consultation->setTreatment($data['treatment'] ?? '');
        $consultation->setNextVisit($data['next_visit'] ?? null);
        $consultation->setConsultationFee(!empty($data['consultation_fee']) ? (float) $data['consultation_fee'] : null);
        $consultation->setStatus($data['status'] ?? 'completed');
        $consultation->setObservations($data['observations'] ?? '');

        if ($this->consultationRepo->update($consultation)) {
            $_SESSION['success'] = 'Consulta actualizada correctamente.';
        } else {
            $_SESSION['error'] = 'Error al actualizar la consulta.';
        }

        header('Location: ' . BASE_URL . 'consultations.php');
        exit;
    }

    // ********** Desactivar consulta médica (soft delete) **********
    public function deactivate($id)
    {
        $consultation = $this->consultationRepo->findById($id);
        if (!$consultation) {
            $_SESSION['error'] = 'Consulta no encontrada.';
            header('Location: ' . BASE_URL . 'consultations.php');
            exit;
        }

        if ($this->consultationRepo->deactivate($id)) {
            $_SESSION['success'] = 'Consulta desactivada correctamente.';
        } else {
            $_SESSION['error'] = 'Error al desactivar la consulta.';
        }

        header('Location: ' . BASE_URL . 'consultations.php');
        exit;
    }

    // ********** Buscar mascotas para autocomplete (evita XSS en búsqueda) **********
    public function searchPets()
    {
        header('Content-Type: application/json');

        $q = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';

        $repo = new PetRepository();
        $results = $repo->searchPetsWithClients($q);

        echo json_encode($results);
        exit;
    }
}
