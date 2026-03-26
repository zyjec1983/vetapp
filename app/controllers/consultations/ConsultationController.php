<?php
/**
 * Location: vetapp/app/controllers/consultations/ConsultationController.php
 * Controlador de consultas médicas (CRUD completo + recordatorios)
 */

// Mostrar errores (solo en desarrollo)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Requerir dependencias
require_once __DIR__ . '/../BaseController.php';
require_once __DIR__ . '/../../repositories/ConsultationRepository.php';
require_once __DIR__ . '/../../repositories/PetRepository.php';
require_once __DIR__ . '/../../repositories/ReminderRepository.php';
require_once __DIR__ . '/../../models/ConsultationModel.php';
require_once __DIR__ . '/../../models/ReminderModel.php';
require_once __DIR__ . '/../../helpers/auth.php';

class ConsultationController extends BaseController
{
    private $consultationRepo;   // Repositorio de consultas
    private $petRepo;            // Repositorio de mascotas (para recordatorios)
    private $reminderRepo;       // Repositorio de recordatorios

    /**
     * Constructor: inicializa repositorios y verifica autenticación/permisos
     */
    public function __construct()
    {
        parent::__construct();
        $this->consultationRepo = new ConsultationRepository($this->db);
        $this->petRepo = new PetRepository();
        $this->reminderRepo = new ReminderRepository();
        $this->requireAuth();
    }

    /**
     * Verifica que el usuario esté autenticado y tenga rol de admin o veterinario
     */
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

    /**
     * Lista todas las consultas (index)
     */
    public function index()
    {
        $consultations = $this->consultationRepo->getAll(); // array asociativo con joins
        require_once __DIR__ . '/../../views/consultations/index.php';
    }

    /**
     * Muestra el formulario para crear una nueva consulta
     */
    public function create()
    {
        // Obtener lista de mascotas con sus dueños para el selector
        $pets = $this->consultationRepo->getPetsWithClients();
        require_once __DIR__ . '/../../views/consultations/create.php';
    }

    /**
     * Procesa el formulario de creación de consulta y guarda en BD
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'consultations.php');
            exit;
        }

        // ********** Validaciones **********
        $errors = [];
        if (empty($_POST['id_pet']))
            $errors[] = 'Debe seleccionar una mascota.';
        if (empty($_POST['diagnosis']))
            $errors[] = 'El diagnóstico es obligatorio.';
        if (!empty($_POST['consultation_fee']) && !is_numeric($_POST['consultation_fee'])) {
            $errors[] = 'El honorario debe ser un número.';
        }
        if (!empty($_POST['weight']) && !is_numeric($_POST['weight']))
            $errors[] = 'El peso debe ser un número.';
        if (!empty($_POST['temperature']) && !is_numeric($_POST['temperature']))
            $errors[] = 'La temperatura debe ser un número.';
        if (!empty($_POST['next_visit'])) {
            $date = DateTime::createFromFormat('Y-m-d', $_POST['next_visit']);
            if (!$date || $date->format('Y-m-d') !== $_POST['next_visit']) {
                $errors[] = 'La fecha de próxima visita no es válida.';
            }
        }

        // Si hay errores, redirigir al formulario
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
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

        // Crear modelo de consulta
        $consultation = new ConsultationModel([
            'id_pet' => $_POST['id_pet'],
            'id_user' => $id_user,
            'weight' => !empty($_POST['weight']) ? (float) $_POST['weight'] : null,
            'temperature' => !empty($_POST['temperature']) ? (float) $_POST['temperature'] : null,
            'diagnosis' => trim($_POST['diagnosis']),
            'treatment' => trim($_POST['treatment'] ?? ''),
            'next_visit' => $_POST['next_visit'] ?? null,
            'consultation_fee' => !empty($_POST['consultation_fee']) ? (float) $_POST['consultation_fee'] : null,
            'status' => $_POST['status'] ?? 'completed',
            'observations' => trim($_POST['observations'] ?? '')
        ]);

        // Guardar consulta
        if ($this->consultationRepo->create($consultation)) {
            // ********** Crear recordatorio si se solicitó **********
            if (isset($_POST['enable_reminder']) && $_POST['enable_reminder'] == '1') {
                // Obtener datos de la mascota para saber el cliente dueño
                $pet = $this->petRepo->findById($consultation->getIdPet());
                if ($pet) {
                    // Preparar modelo de recordatorio
                    $reminder = new ReminderModel();
                    $reminder->setReminderType($_POST['reminder_type'] ?? 'consultation');
                    $reminder->setIdPet($consultation->getIdPet());
                    $reminder->setIdClient($pet->getIdClient());
                    // Fecha del recordatorio: la especificada o la próxima visita
                    $reminderDate = !empty($_POST['reminder_date']) ? $_POST['reminder_date'] : $consultation->getNextVisit();
                    $reminder->setReminderDate($reminderDate);
                    $reminder->setMessage($_POST['reminder_message'] ?? '');
                    $reminder->setSent(false);

                    // Guardar recordatorio
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

    /**
     * Muestra el detalle de una consulta
     */
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

    /**
     * Muestra el formulario de edición de una consulta
     */
    public function edit($id)
    {
        $consultation = $this->consultationRepo->findById($id);
        if (!$consultation) {
            $_SESSION['error'] = 'Consulta no encontrada.';
            header('Location: ' . BASE_URL . 'consultations.php');
            exit;
        }
        $pets = $this->consultationRepo->getPetsWithClients(); // para el selector
        require_once __DIR__ . '/../../views/consultations/edit.php';
    }

    /**
     * Procesa la actualización de una consulta
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'consultations.php');
            exit;
        }

        $id = $_POST['id_consultation'] ?? null;
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

        // Validaciones (igual que en store)
        $errors = [];
        if (empty($_POST['id_pet']))
            $errors[] = 'Debe seleccionar una mascota.';
        if (empty($_POST['diagnosis']))
            $errors[] = 'El diagnóstico es obligatorio.';
        if (!empty($_POST['consultation_fee']) && !is_numeric($_POST['consultation_fee'])) {
            $errors[] = 'El honorario debe ser un número.';
        }
        if (!empty($_POST['weight']) && !is_numeric($_POST['weight']))
            $errors[] = 'El peso debe ser un número.';
        if (!empty($_POST['temperature']) && !is_numeric($_POST['temperature']))
            $errors[] = 'La temperatura debe ser un número.';
        if (!empty($_POST['next_visit'])) {
            $date = DateTime::createFromFormat('Y-m-d', $_POST['next_visit']);
            if (!$date || $date->format('Y-m-d') !== $_POST['next_visit']) {
                $errors[] = 'La fecha de próxima visita no es válida.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            header('Location: ' . BASE_URL . 'consultations.php?action=edit&id=' . $id);
            exit;
        }

        // Actualizar modelo
        $consultation->setIdPet($_POST['id_pet']);
        $consultation->setWeight(!empty($_POST['weight']) ? (float) $_POST['weight'] : null);
        $consultation->setTemperature(!empty($_POST['temperature']) ? (float) $_POST['temperature'] : null);
        $consultation->setDiagnosis(trim($_POST['diagnosis']));
        $consultation->setTreatment(trim($_POST['treatment'] ?? ''));
        $consultation->setNextVisit($_POST['next_visit'] ?? null);
        $consultation->setConsultationFee(!empty($_POST['consultation_fee']) ? (float) $_POST['consultation_fee'] : null);
        $consultation->setStatus($_POST['status'] ?? 'completed');
        $consultation->setObservations(trim($_POST['observations'] ?? ''));

        if ($this->consultationRepo->update($consultation)) {
            $_SESSION['success'] = 'Consulta actualizada correctamente.';
        } else {
            $_SESSION['error'] = 'Error al actualizar la consulta.';
        }

        header('Location: ' . BASE_URL . 'consultations.php');
        exit;
    }

    /**
     * Desactiva una consulta (soft delete)
     */
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
}