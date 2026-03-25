<?php
// app/controllers/consultations/ConsultationController.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../BaseController.php';
require_once __DIR__ . '/../../repositories/ConsultationRepository.php';
require_once __DIR__ . '/../../models/ConsultationModel.php';
require_once __DIR__ . '/../../helpers/auth.php';

class ConsultationController extends BaseController
{
    private $consultationRepo;

    public function __construct()
    {
        parent::__construct();
        $this->consultationRepo = new ConsultationRepository($this->db);
        $this->requireAuth();
    }

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

    public function index()
    {
        $consultations = $this->consultationRepo->getAll(); // devuelve array asociativo con joins
        require_once __DIR__ . '/../../views/consultations/index.php';
    }

    public function create()
    {
        // Obtener lista de mascotas con sus dueños para el selector
        $pets = $this->consultationRepo->getPetsWithClients(); // implementado arriba
        require_once __DIR__ . '/../../views/consultations/create.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'consultations.php');
            exit;
        }

        // Validaciones
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
            header('Location: ' . BASE_URL . 'consultations.php?action=create');
            exit;
        }

        // 🔥 Obtener usuario actual desde el helper currentUser()
        $user = currentUser();
        $id_user = $user['id'] ?? null;

        if (!$id_user) {
            $_SESSION['error'] = 'No se pudo identificar al usuario.';
            header('Location: ' . BASE_URL . 'login.php');
            exit;
        }

        // Crear modelo con los datos
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

        if ($this->consultationRepo->create($consultation)) {
            // Recordatorio (opcional)
            if (isset($_POST['enable_reminder']) && $_POST['enable_reminder'] == '1') {
                // Aquí llamarías al método para crear recordatorio
            }
            $_SESSION['success'] = 'Consulta registrada correctamente.';
        } else {
            $_SESSION['error'] = 'Error al registrar la consulta.';
        }

        header('Location: ' . BASE_URL . 'consultations.php');
        exit;
    }

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

    public function edit($id)
    {
        $consultation = $this->consultationRepo->findById($id);
        if (!$consultation) {
            $_SESSION['error'] = 'Consulta no encontrada.';
            header('Location: ' . BASE_URL . 'consultations.php');
            exit;
        }
        $pets = $this->consultationRepo->getPetsWithClients(); // para selector
        require_once __DIR__ . '/../../views/consultations/edit.php';
    }

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

        // Validaciones
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

    private function createReminder($post, $consultationId)
    {
        // Aquí implementar la lógica para insertar en reminders
        // Requiere tener el repositorio de reminders o una función auxiliar
        // Por ahora, solo dejamos la estructura.
        // Ejemplo:
        $reminderData = [
            'reminder_type' => $post['reminder_type'] ?? 'consultation',
            'id_pet' => $post['id_pet'],
            'reminder_date' => $post['reminder_date'],
            'message' => $post['message'] ?? ''
        ];
        // Llamar a ReminderRepository para insertar
    }
}