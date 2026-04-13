<?php
/**
 * Location: vetapp/app/controllers/users/UserController.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../BaseController.php';
require_once __DIR__ . '/../../repositories/UserRepository.php';
require_once __DIR__ . '/../../repositories/RoleRepository.php';

class UserController extends BaseController
{
    // ********** Constructor: inicializa sesión y repositorios **********
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        parent::__construct();
    }

    // ********** Listar todos los usuarios (solo admin) **********
    public function index(): void
    {
        if (!hasRole('admin')) {
            header('Location: ' . BASE_URL . 'dashboard.php');
            exit;
        }

        $users = $this->userRepository->findAll();
        require_once __DIR__ . '/../../views/users/index.php';
    }

    // ********** Mostrar formulario para crear usuario **********
    public function create()
    {
        require __DIR__ . '/../../views/users/create.php';
    }

    // ********** Guardar nuevo usuario con roles **********
    public function store(): void
    {
        // ********** Verificar CSRF **********
        $this->validateCSRF();

        // ********** Validar método POST **********
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'users.php');
            exit;
        }

        // ********** Sanitizar todos los datos POST **********
        $data = $this->sanitizeInputData($_POST);

        // ********** Obtener datos del formulario con datos sanitizados **********
        $identification = $data['identification'] ?? '';
        $name = $data['name'] ?? '';
        $middlename = $data['middlename'] ?? '';
        $lastname1 = $data['lastname1'] ?? '';
        $lastname2 = $data['lastname2'] ?? '';
        $email = $data['email'] ?? '';
        $phone = $data['phone'] ?? '';
        $password = $data['password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';
        $roles = $data['roles'] ?? [];

        // ********** Validar campos obligatorios **********
        if ($identification === '' || $name === '' || $lastname1 === '' || $email === '' || $password === '') {
            $_SESSION['error'] = 'Debe completar todos los campos obligatorios';
            header('Location: ' . BASE_URL . 'users.php?action=create');
            exit;
        }

        // ********** Validar identificación (solo números) **********
        if (!preg_match("/^[0-9]{6,20}$/", $identification)) {
            $_SESSION['error'] = 'Identificación inválida';
            header('Location: ' . BASE_URL . 'users.php?action=create');
            exit;
        }

        // ********** Validar nombres **********
        if (!preg_match("/^[A-Za-zñÑáéíóúÁÉÍÓÚ\s]+$/", $name)) {
            $_SESSION['error'] = 'Nombre inválido';
            header('Location: ' . BASE_URL . 'users.php?action=create');
            exit;
        }

        // ********** Validar segundo nombre si existe **********
        if ($middlename !== '' && !preg_match("/^[A-Za-zñÑáéíóúÁÉÍÓÚ\s]+$/", $middlename)) {
            $_SESSION['error'] = 'Segundo nombre inválido';
            header('Location: ' . BASE_URL . 'users.php?action=create');
            exit;
        }

        // ********** Validar apellido paterno **********
        if (!preg_match("/^[A-Za-zñÑáéíóúÁÉÍÓÚ\s]+$/", $lastname1)) {
            $_SESSION['error'] = 'Apellido paterno inválido';
            header('Location: ' . BASE_URL . 'users.php?action=create');
            exit;
        }

        // ********** Validar apellido materno **********
        if ($lastname2 !== '' && !preg_match("/^[A-Za-zñÑáéíóúÁÉÍÓÚ\s]+$/", $lastname2)) {
            $_SESSION['error'] = 'Apellido materno inválido';
            header('Location: ' . BASE_URL . 'users.php?action=create');
            exit;
        }

        // ********** Sanitizar email (ya viene sanitizado pero se valida) **********
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);

        // ********** Validar email **********
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Correo electrónico inválido';
            header('Location: ' . BASE_URL . 'users.php?action=create');
            exit;
        }

        // ********** Validar teléfono **********
        if ($phone !== '' && !preg_match("/^[0-9+\s]{7,20}$/", $phone)) {
            $_SESSION['error'] = 'Teléfono inválido';
            header('Location: ' . BASE_URL . 'users.php?action=create');
            exit;
        }

        // ********** Validar contraseña **********
        if (strlen($password) < 5 || strlen($password) > 8) {
            $_SESSION['error'] = 'La contraseña debe tener entre 5 y 8 caracteres';
            header('Location: ' . BASE_URL . 'users.php?action=create');
            exit;
        }

        // ********** Validar coincidencia de contraseñas **********
        if ($password !== $confirmPassword) {
            $_SESSION['error'] = 'Las contraseñas no coinciden';
            header('Location: ' . BASE_URL . 'users.php?action=create');
            exit;
        }

        // ********** Validar roles **********
        if (empty($roles)) {
            $_SESSION['error'] = 'Debe asignar al menos un rol';
            header('Location: ' . BASE_URL . 'users.php?action=create');
            exit;
        }

        // ********** Encriptar contraseña **********
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // ********** Preparar datos del usuario **********
        $userData = [
            'email' => $email,
            'password' => $passwordHash,
            'name' => $name,
            'middlename' => $middlename,
            'lastname1' => $lastname1,
            'lastname2' => $lastname2,
            'identification' => $identification,
            'phone' => $phone
        ];

        // ********** Guardar usuario **********
        $userId = $this->userRepository->create($userData);

        // ********** Guardar roles **********
        $this->userRepository->assignRoles($userId, $roles);

        // ********** Mensaje de éxito **********
        $_SESSION['success'] = 'Usuario creado correctamente';
        header('Location: ' . BASE_URL . 'users.php');
        exit;
    }

    // ********** Mostrar formulario para editar usuario **********
    public function edit($id)
    {
        $id = (int) $id;
        $user = $this->userRepository->findById($id);

        $roleRepository = new RoleRepository($this->db);
        $roles = $roleRepository->findAll();
        $userRoles = $this->userRepository->getRolesByUserId($id);
        $roleIds = array_column($userRoles, 'id_role');

        $formData = $user;
        $formData['role_ids'] = $roleIds;

        require __DIR__ . '/../../views/users/edit.php';
    }

    // ********** Actualizar usuario y sus roles **********
    public function update()
    {
        // ********** Verificar CSRF **********
        $this->validateCSRF();

        // ********** Validar método POST **********
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'users.php');
            exit;
        }

        // ********** Sanitizar todos los datos POST **********
        $data = $this->sanitizeInputData($_POST);

        // ********** Obtener ID del usuario **********
        $id = (int) ($data['id_user'] ?? 0);

        if ($id <= 0) {
            die("ID inválido");
        }

        // ********** Obtener datos del formulario con datos sanitizados **********
        $identification = $data['identification'] ?? '';
        $name = $data['name'] ?? '';
        $middlename = $data['middlename'] ?? '';
        $lastname1 = $data['lastname1'] ?? '';
        $lastname2 = $data['lastname2'] ?? '';
        $email = $data['email'] ?? '';
        $phone = $data['phone'] ?? '';
        $password = $data['password'] ?? '';
        $roles = $data['roles'] ?? [];
        $active = isset($data['active']) ? 1 : 0;

        // ********** Validaciones básicas **********
        if (!$id || $name === '' || $lastname1 === '' || $email === '') {
            $_SESSION['error'] = 'Datos obligatorios incompletos';
            $_SESSION['form_data'] = $data;
            header('Location: ' . BASE_URL . 'users.php?action=edit&id=' . $id);
            exit;
        }

        // ********** Validar email **********
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Correo electrónico inválido';
            $_SESSION['form_data'] = $data;
            header('Location: ' . BASE_URL . 'users.php?action=edit&id=' . $id);
            exit;
        }

        // ********** Validar roles **********
        if (empty($roles)) {
            $_SESSION['error'] = 'Debe seleccionar al menos un rol';
            $_SESSION['form_data'] = $data;
            header('Location: ' . BASE_URL . 'users.php?action=edit&id=' . $id);
            exit;
        }

        // ********** Preparar password si existe **********
        $hashedPassword = null;

        if (!empty($password)) {
            if (strlen($password) < 6) {
                $_SESSION['error'] = 'La contraseña debe tener mínimo 6 caracteres';
                $_SESSION['form_data'] = $data;
                header('Location: ' . BASE_URL . 'users.php?action=edit&id=' . $id);
                exit;
            }
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        }

        // ********** Actualizar usuario **********
        $updated = $this->userRepository->updateFull(
            $id,
            $name,
            $middlename,
            $lastname1,
            $lastname2,
            $email,
            $phone,
            $active,
            $hashedPassword
        );

        if (!$updated) {
            $_SESSION['error'] = 'Error al actualizar usuario';
            header('Location: ' . BASE_URL . 'users.php');
            exit;
        }

        // ********** Actualizar roles **********
        $this->userRepository->updateRoles($id, $roles);

        $_SESSION['success'] = 'Usuario actualizado correctamente';
        header('Location: ' . BASE_URL . 'users.php');
        exit;
    }

    // ********** Desactivar usuario (soft delete) **********
    public function deactivate(int $id)
    {
        // ********** Obtener ID de la URL **********
        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['error'] = 'ID inválido';
            header("Location: users.php");
            exit;
        }

        // ********** Ejecutar soft delete **********
        $deleted = $this->userRepository->deactivate($id);

        // ********** Mensaje de resultado **********
        if ($deleted) {
            $_SESSION['success'] = 'Usuario desactivado correctamente';
        } else {
            $_SESSION['error'] = 'No se pudo desactivar el usuario';
        }

        header("Location: users.php");
        exit;
    }
}
