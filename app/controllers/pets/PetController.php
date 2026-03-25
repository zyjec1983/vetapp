<?php
// app/controllers/pets/PetController.php

require_once __DIR__ . '/../BaseController.php';
require_once __DIR__ . '/../../repositories/PetRepository.php';
require_once __DIR__ . '/../../repositories/ClientRepository.php'; // para obtener clientes activos
require_once __DIR__ . '/../../models/PetModel.php';

class PetController extends BaseController
{
    private $petRepo;
    private $clientRepo;

    public function __construct()
    {
        parent::__construct();
        $this->petRepo = new PetRepository();
        $this->clientRepo = new ClientRepository();
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
        $pets = $this->petRepo->getAll();
        require_once __DIR__ . '/../../views/pets/index.php';
    }

    public function create()
    {
        // Obtener todos los clientes activos para el selector
        $clients = $this->clientRepo->getAll(); // asumimos que getAll() solo trae activos
        require_once __DIR__ . '/../../views/pets/create.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'pets.php');
            exit;
        }

        // Validaciones
        $errors = [];
        if (empty($_POST['id_client']))
            $errors[] = 'Debe seleccionar un cliente dueño.';
        if (empty($_POST['name']))
            $errors[] = 'El nombre de la mascota es obligatorio.';
        if (empty($_POST['species']))
            $errors[] = 'La especie es obligatoria.';
        if (!empty($_POST['date_of_birth'])) {
            $date = DateTime::createFromFormat('Y-m-d', $_POST['date_of_birth']);
            if (!$date || $date->format('Y-m-d') !== $_POST['date_of_birth']) {
                $errors[] = 'La fecha de nacimiento no es válida.';
            }
        }
        if (!empty($_POST['current_weight']) && !is_numeric($_POST['current_weight'])) {
            $errors[] = 'El peso debe ser un número.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            header('Location: ' . BASE_URL . 'pets.php?action=create');
            exit;
        }

        // Procesar imagen
        $picture = null;
        if (isset($_FILES['picture']) && $_FILES['picture']['error'] !== UPLOAD_ERR_NO_FILE) {
            $picture = $this->uploadPicture($_FILES['picture']);
            if ($picture === false) {
                // Si hubo error en la subida, redirigir con error
                $_SESSION['errors'] = [$_SESSION['error']];
                unset($_SESSION['error']);
                $_SESSION['old'] = $_POST;
                header('Location: ' . BASE_URL . 'pets.php?action=create');
                exit;
            }
        }

        $pet = new PetModel([
            'id_client' => $_POST['id_client'],
            'name' => trim($_POST['name']),
            'species' => trim($_POST['species']),
            'breed' => trim($_POST['breed'] ?? ''),
            'sex' => $_POST['sex'] ?? 'Unknown',
            'date_of_birth' => $_POST['date_of_birth'] ?? null,
            'current_weight' => !empty($_POST['current_weight']) ? (float) $_POST['current_weight'] : null,
            'color' => trim($_POST['color'] ?? ''),
            'microchip' => trim($_POST['microchip'] ?? ''),
            'allergies' => trim($_POST['allergies'] ?? ''),
            'observations' => trim($_POST['observations'] ?? ''),
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

    public function edit($id)
    {
        $pet = $this->petRepo->findById($id);
        if (!$pet) {
            $_SESSION['error'] = 'Mascota no encontrada.';
            header('Location: ' . BASE_URL . 'pets.php');
            exit;
        }
        $clients = $this->clientRepo->getAll(); // para selector
        require_once __DIR__ . '/../../views/pets/edit.php';
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'pets.php');
            exit;
        }

        $id = $_POST['id_pet'] ?? null;
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

        error_log("Actualizando mascota ID: " . $id);

        // Validaciones
        $errors = [];
        if (empty($_POST['id_client']))
            $errors[] = 'Debe seleccionar un cliente dueño.';
        if (empty($_POST['name']))
            $errors[] = 'El nombre de la mascota es obligatorio.';
        if (empty($_POST['species']))
            $errors[] = 'La especie es obligatoria.';
        if (!empty($_POST['date_of_birth'])) {
            $date = DateTime::createFromFormat('Y-m-d', $_POST['date_of_birth']);
            if (!$date || $date->format('Y-m-d') !== $_POST['date_of_birth']) {
                $errors[] = 'La fecha de nacimiento no es válida.';
            }
        }
        if (!empty($_POST['current_weight']) && !is_numeric($_POST['current_weight'])) {
            $errors[] = 'El peso debe ser un número.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            header('Location: ' . BASE_URL . 'pets.php?action=edit&id=' . $id);
            exit;
        }

        $pet->setIdClient($_POST['id_client']);
        $pet->setName(trim($_POST['name']));
        $pet->setSpecies(trim($_POST['species']));
        $pet->setBreed(trim($_POST['breed'] ?? ''));
        $pet->setSex($_POST['sex'] ?? 'Unknown');
        $pet->setDateOfBirth($_POST['date_of_birth'] ?? null);
        $pet->setCurrentWeight(!empty($_POST['current_weight']) ? (float) $_POST['current_weight'] : null);
        $pet->setColor(trim($_POST['color'] ?? ''));
        $pet->setMicrochip(trim($_POST['microchip'] ?? ''));
        $pet->setAllergies(trim($_POST['allergies'] ?? ''));
        $pet->setObservations(trim($_POST['observations'] ?? ''));

        // Procesar imagen
        $currentPicture = $pet->getPicture();
        if (isset($_POST['remove_picture']) && $_POST['remove_picture'] == '1') {

            // Eliminar imagen existente
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
                $_SESSION['old'] = $_POST;
                header('Location: ' . BASE_URL . 'pets.php?action=edit&id=' . $id);
                exit;
            }
            $currentPicture = $newPicture;
        }

        // Actualizar los campos, incluyendo picture
        $pet->setPicture($currentPicture);

        if ($this->petRepo->update($pet)) {
            $_SESSION['success'] = 'Mascota actualizada correctamente.';
        } else {
            $_SESSION['error'] = 'Error al actualizar la mascota.';
        }

        header('Location: ' . BASE_URL . 'pets.php');
        exit;
    }

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

    // Búsqueda para autocomplete (usado en consultas)
    public function search()
    {
        if (!isset($_GET['q']) || empty($_GET['q'])) {
            echo json_encode([]);
            exit;
        }
        $term = $_GET['q'];
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

    private function uploadPicture($file, $existingPicture = null)
    {
        if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../../public/storage/uploads/';

            // Crear directorio si no existe, con manejo de error
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    error_log("No se pudo crear el directorio: " . $uploadDir);
                    $_SESSION['error'] = 'No se pudo crear el directorio de subidas.';
                    return false;
                }
            }

            // Verificar permisos de escritura
            if (!is_writable($uploadDir)) {
                error_log("Directorio no escribible: " . $uploadDir);
                $_SESSION['error'] = 'El directorio de subidas no tiene permisos de escritura.';
                return false;
            }

            // Validar extensiones y MIME
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

            // Intentar mover el archivo
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                // Eliminar imagen anterior si existe (la ruta ya debe ser correcta)
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