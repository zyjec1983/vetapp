<?php
/**
 * Location: vetapp/app/controllers/pets/PetController.php
 */

require_once __DIR__ . '/../BaseController.php';
require_once __DIR__ . '/../../repositories/PetRepository.php';
require_once __DIR__ . '/../../repositories/ClientRepository.php';
require_once __DIR__ . '/../../models/PetModel.php';

class PetController extends BaseController
{
    private $petRepo;
    private $clientRepo;

    // ********** Constructor: inicializa repositorios y verifica autenticación **********
    public function __construct()
    {
        parent::__construct();
        $this->petRepo = new PetRepository();
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

    // ********** Listar todas las mascotas **********
    public function index()
    {
        $pets = $this->petRepo->getAll();
        require_once __DIR__ . '/../../views/pets/index.php';
    }

    // ********** Mostrar formulario para registrar mascota **********
    public function create()
    {
        $clients = $this->clientRepo->getAll();
        require_once __DIR__ . '/../../views/pets/create.php';
    }

    // ********** Guardar nueva mascota **********
    public function store()
    {
        // ********** Verificar CSRF **********
        $this->validateCSRF();

        // ********** Validar método POST **********
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'pets.php');
            exit;
        }

        // ********** Sanitizar todos los datos POST (excepto archivos) **********
        $data = $this->sanitizeInputData($_POST);

        // ********** Validaciones **********
        $errors = [];
        if (empty($data['id_client']))
            $errors[] = 'Debe seleccionar un cliente dueño.';
        if (empty($data['name']))
            $errors[] = 'El nombre de la mascota es obligatorio.';
        if (empty($data['species']))
            $errors[] = 'La especie es obligatoria.';
        if (!empty($data['date_of_birth'])) {
            $date = DateTime::createFromFormat('Y-m-d', $data['date_of_birth']);
            if (!$date || $date->format('Y-m-d') !== $data['date_of_birth']) {
                $errors[] = 'La fecha de nacimiento no es válida.';
            }
        }
        if (!empty($data['current_weight']) && !is_numeric($data['current_weight'])) {
            $errors[] = 'El peso debe ser un número.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            header('Location: ' . BASE_URL . 'pets.php?action=create');
            exit;
        }

        // Procesar imagen (NO se sanitiza, se valida tipo/tamaño)
        $picture = null;
        if (isset($_FILES['picture']) && $_FILES['picture']['error'] !== UPLOAD_ERR_NO_FILE) {
            $picture = $this->uploadPicture($_FILES['picture']);
            if ($picture === false) {
                $_SESSION['errors'] = [$_SESSION['error']];
                unset($_SESSION['error']);
                $_SESSION['old'] = $data;
                header('Location: ' . BASE_URL . 'pets.php?action=create');
                exit;
            }
        }

        // Crear modelo con datos sanitizados
        $pet = new PetModel([
            'id_client' => $data['id_client'],
            'name' => $data['name'],
            'species' => $data['species'],
            'breed' => $data['breed'] ?? '',
            'sex' => $data['sex'] ?? 'Unknown',
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'current_weight' => !empty($data['current_weight']) ? (float) $data['current_weight'] : null,
            'color' => $data['color'] ?? '',
            'microchip' => $data['microchip'] ?? '',
            'allergies' => $data['allergies'] ?? '',
            'observations' => $data['observations'] ?? '',
            'picture' => $picture
        ]);

        if ($this->petRepo->create($pet)) {
            $_SESSION['success'] = 'Mascota registrada correctamente.';
        } else {
            $_SESSION['error'] = 'Error al registrar la mascota.';
        }

        header('Location: ' . BASE_URL . 'pets.php');
        exit;
    }

    // ********** Mostrar formulario para editar mascota **********
    public function edit($id)
    {
        $pet = $this->petRepo->findById($id);
        if (!$pet) {
            $_SESSION['error'] = 'Mascota no encontrada.';
            header('Location: ' . BASE_URL . 'pets.php');
            exit;
        }
        $clients = $this->clientRepo->getAll();
        require_once __DIR__ . '/../../views/pets/edit.php';
    }

    // ********** Actualizar datos de mascota **********
    public function update()
    {
        // ********** Verificar CSRF **********
        $this->validateCSRF();

        // ********** Validar método POST **********
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'pets.php');
            exit;
        }

        // ********** Sanitizar todos los datos POST (excepto archivos) **********
        $data = $this->sanitizeInputData($_POST);

        $id = $data['id_pet'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'ID de mascota no proporcionado.';
            header('Location: ' . BASE_URL . 'pets.php');
            exit;
        }

        $pet = $this->petRepo->findById($id);
        if (!$pet) {
            $_SESSION['error'] = 'Mascota no encontrada.';
            header('Location: ' . BASE_URL . 'pets.php');
            exit;
        }

        // ********** Validaciones **********
        $errors = [];
        if (empty($data['id_client']))
            $errors[] = 'Debe seleccionar un cliente dueño.';
        if (empty($data['name']))
            $errors[] = 'El nombre de la mascota es obligatorio.';
        if (empty($data['species']))
            $errors[] = 'La especie es obligatoria.';
        if (!empty($data['date_of_birth'])) {
            $date = DateTime::createFromFormat('Y-m-d', $data['date_of_birth']);
            if (!$date || $date->format('Y-m-d') !== $data['date_of_birth']) {
                $errors[] = 'La fecha de nacimiento no es válida.';
            }
        }
        if (!empty($data['current_weight']) && !is_numeric($data['current_weight'])) {
            $errors[] = 'El peso debe ser un número.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            header('Location: ' . BASE_URL . 'pets.php?action=edit&id=' . $id);
            exit;
        }

        // Actualizar propiedades con datos sanitizados
        $pet->setIdClient($data['id_client']);
        $pet->setName($data['name']);
        $pet->setSpecies($data['species']);
        $pet->setBreed($data['breed'] ?? '');
        $pet->setSex($data['sex'] ?? 'Unknown');
        $pet->setDateOfBirth($data['date_of_birth'] ?? null);
        $pet->setCurrentWeight(!empty($data['current_weight']) ? (float) $data['current_weight'] : null);
        $pet->setColor($data['color'] ?? '');
        $pet->setMicrochip($data['microchip'] ?? '');
        $pet->setAllergies($data['allergies'] ?? '');
        $pet->setObservations($data['observations'] ?? '');

        // Procesar imagen (NO se sanitiza, se valida tipo/tamaño)
        $currentPicture = $pet->getPicture();
        if (isset($data['remove_picture']) && $data['remove_picture'] == '1') {
            if ($currentPicture && file_exists(__DIR__ . '/../../../public/storage/uploads/' . $currentPicture)) {
                unlink(__DIR__ . '/../../../public/storage/uploads/' . $currentPicture);
            }
            $currentPicture = null;
        }

        if (isset($_FILES['picture']) && $_FILES['picture']['error'] !== UPLOAD_ERR_NO_FILE) {
            $newPicture = $this->uploadPicture($_FILES['picture'], $currentPicture);
            if ($newPicture === false) {
                $_SESSION['errors'] = [$_SESSION['error']];
                unset($_SESSION['error']);
                $_SESSION['old'] = $data;
                header('Location: ' . BASE_URL . 'pets.php?action=edit&id=' . $id);
                exit;
            }
            $currentPicture = $newPicture;
        }

        $pet->setPicture($currentPicture);

        if ($this->petRepo->update($pet)) {
            $_SESSION['success'] = 'Mascota actualizada correctamente.';
        } else {
            $_SESSION['error'] = 'Error al actualizar la mascota.';
        }

        header('Location: ' . BASE_URL . 'pets.php');
        exit;
    }

    // ********** Desactivar mascota (soft delete) **********
    public function deactivate($id)
    {
        $pet = $this->petRepo->findById($id);
        if (!$pet) {
            $_SESSION['error'] = 'Mascota no encontrada.';
            header('Location: ' . BASE_URL . 'pets.php');
            exit;
        }

        if ($this->petRepo->deactivate($id)) {
            $_SESSION['success'] = 'Mascota desactivada correctamente.';
        } else {
            $_SESSION['error'] = 'Error al desactivar la mascota.';
        }

        header('Location: ' . BASE_URL . 'pets.php');
        exit;
    }

    // ********** Mostrar detalle de mascota **********
    public function show($id)
    {
        $pet = $this->petRepo->findById($id);
        if (!$pet) {
            $_SESSION['error'] = 'Mascota no encontrada.';
            header('Location: ' . BASE_URL . 'pets.php');
            exit;
        }
        require_once __DIR__ . '/../../views/pets/show.php';
    }

    // ********** Buscar mascotas para autocomplete (usado en consultas - evita XSS) **********
    public function search()
    {
        if (!isset($_GET['q']) || empty($_GET['q'])) {
            echo json_encode([]);
            exit;
        }
        $term = sanitizeInput($_GET['q']);
        $pets = $this->petRepo->search($term);
        $result = [];
        foreach ($pets as $pet) {
            $result[] = [
                'id' => $pet->getIdPet(),
                'text' => $pet->getName() . ' (' . $pet->getClientName() . ')',
                'client_id' => $pet->getIdClient(),
                'species' => $pet->getSpecies()
            ];
        }
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    // ********** Procesar subida de imagen de mascota (valida tipo/tamaño, NO sanitiza) **********
    private function uploadPicture($file, $existingPicture = null)
    {
        if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../../public/storage/uploads/';

            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    error_log("No se pudo crear el directorio: " . $uploadDir);
                    $_SESSION['error'] = 'No se pudo crear el directorio de subidas.';
                    return false;
                }
            }

            if (!is_writable($uploadDir)) {
                error_log("Directorio no escribible: " . $uploadDir);
                $_SESSION['error'] = 'El directorio de subida no tiene permisos de escritura.';
                return false;
            }

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];

            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($extension, $allowedExtensions) || !in_array($mimeType, $allowedMimeTypes)) {
                $_SESSION['error'] = "Formato no permitido (ext: $extension, mime: $mimeType).";
                return false;
            }

            $maxSize = 2 * 1024 * 1024;
            if ($file['size'] > $maxSize) {
                $_SESSION['error'] = 'La imagen no debe superar 2MB.';
                return false;
            }

            $filename = uniqid() . '.' . $extension;
            $destination = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                if ($existingPicture && file_exists($uploadDir . $existingPicture)) {
                    unlink($uploadDir . $existingPicture);
                }
                return $filename;
            } else {
                error_log("move_uploaded_file falló. tmp_name: {$file['tmp_name']}, destination: $destination");
                $_SESSION['error'] = 'Error al mover el archivo. Verifica permisos de la carpeta.';
                return false;
            }
        }
        return $existingPicture;
    }
}
