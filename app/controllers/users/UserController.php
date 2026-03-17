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


    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        parent::__construct();
    }

    public function index(): void
    {
        // ***** Solo admin *****
        if (!hasRole('admin')) {
            header('Location: ' . BASE_URL . 'dashboard.php');
            exit;
        }

        $users = $this->userRepository->findAll();

        require_once __DIR__ . '/../../views/users/index.php';
    }

    public function create()
    {
        require __DIR__ . '/../../views/users/create.php';
    }

    // ******************* guardar nuevo usuario *******************
    public function store(): void
    {
        // ******************* validar método POST *******************
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'users.php');
            exit;
        }

        // ******************* obtener datos del formulario *******************
        $identification = trim($_POST['identification'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $middlename = trim($_POST['middlename'] ?? '');
        $lastname1 = trim($_POST['lastname1'] ?? '');
        $lastname2 = trim($_POST['lastname2'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $roles = $_POST['roles'] ?? [];


        // ******************* validar campos obligatorios *******************
        if ($identification === '' || $name === '' || $lastname1 === '' || $email === '' || $password === '') {

            $_SESSION['error'] = 'Debe completar todos los campos obligatorios';
            header('Location: ' . BASE_URL . 'users.php?action=create');
            exit;
        }


        // ******************* validar identificación (solo números) *******************
        if (!preg_match("/^[0-9]{6,20}$/", $identification)) {

            $_SESSION['error'] = 'Identificación inválida';
            header('Location: ' . BASE_URL . 'users.php?action=create');
            exit;
        }


        // ******************* validar nombres *******************
        if (!preg_match("/^[A-Za-zñÑáéíóúÁÉÍÓÚ\s]+$/", $name)) {

            $_SESSION['error'] = 'Nombre inválido';
            header('Location: ' . BASE_URL . 'users.php?action=create');
            exit;
        }


        // ******************* validar segundo nombre si existe *******************
        if ($middlename !== '' && !preg_match("/^[A-Za-zñÑáéíóúÁÉÍÓÚ\s]+$/", $middlename)) {

            $_SESSION['error'] = 'Segundo nombre inválido';
            header('Location: ' . BASE_URL . 'users.php?action=create');
            exit;
        }


        // ******************* validar apellido paterno *******************
        if (!preg_match("/^[A-Za-zñÑáéíóúÁÉÍÓÚ\s]+$/", $lastname1)) {

            $_SESSION['error'] = 'Apellido paterno inválido';
            header('Location: ' . BASE_URL . 'users.php?action=create');
            exit;
        }


        // ******************* validar apellido materno *******************
        if ($lastname2 !== '' && !preg_match("/^[A-Za-zñÑáéíóúÁÉÍÓÚ\s]+$/", $lastname2)) {

            $_SESSION['error'] = 'Apellido materno inválido';
            header('Location: ' . BASE_URL . 'users.php?action=create');
            exit;
        }


        // ******************* sanitizar email *******************
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);


        // ******************* validar email *******************
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $_SESSION['error'] = 'Correo electrónico inválido';
            header('Location: ' . BASE_URL . 'users.php?action=create');
            exit;
        }


        // ******************* validar teléfono *******************
        if ($phone !== '' && !preg_match("/^[0-9+\s]{7,20}$/", $phone)) {

            $_SESSION['error'] = 'Teléfono inválido';
            header('Location: ' . BASE_URL . 'users.php?action=create');
            exit;
        }


        // ******************* validar contraseña *******************
        if (strlen($password) < 5 || strlen($password) > 8) {

            $_SESSION['error'] = 'La contraseña debe tener entre 5 y 8 caracteres';
            header('Location: ' . BASE_URL . 'users.php?action=create');
            exit;
        }


        // ******************* validar coincidencia de contraseñas *******************
        if ($password !== $confirmPassword) {

            $_SESSION['error'] = 'Las contraseñas no coinciden';
            header('Location: ' . BASE_URL . 'users.php?action=create');
            exit;
        }


        // ******************* validar roles *******************
        if (empty($roles)) {

            $_SESSION['error'] = 'Debe asignar al menos un rol';
            header('Location: ' . BASE_URL . 'users.php?action=create');
            exit;
        }


        // ******************* encriptar contraseña *******************
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);


        // ******************* preparar datos *******************
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


        // ******************* guardar usuario *******************
        $userId = $this->userRepository->create($userData);


        // ******************* guardar roles *******************
        $this->userRepository->assignRoles($userId, $roles);


        // ******************* mensaje éxito *******************
        $_SESSION['success'] = 'Usuario creado correctamente';

        header('Location: ' . BASE_URL . 'users.php');
        exit;

    }

    // =====================================================
    // EDITAR USUARIO
    // =====================================================
    public function edit($id)
    {
        $id = (int) $id;

        $user = $this->userRepository->findById($id);

        // 🔥 CREAR roleRepository
        $roleRepository = new RoleRepository($this->db);

        // 🔥 TRAER TODOS LOS ROLES
        $roles = $roleRepository->findAll();

        // 🔥 TRAER ROLES DEL USUARIO
        $userRoles = $this->userRepository->getRolesByUserId($id);

        // convertir a array simple
        $roleIds = array_column($userRoles, 'id_role');

        // 🔥 FORM DATA
        $formData = $user;
        $formData['role_ids'] = $roleIds;

        require __DIR__ . '/../../views/users/edit.php';
    }

    // =====================================================
    // ACTUALIZAR USUARIO (COMPLETO)
    // =====================================================
    public function update()
    {

        // ******************* validar método *******************
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'users.php');
            exit;
        }

        // ******************* obtener id y sanitizar datos *******************
        $id = (int) ($_POST['id_user'] ?? 0);

        if ($id <= 0) {
            die("ID inválido");
        }

        $identification = trim($_POST['identification'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $middlename = trim($_POST['middlename'] ?? '');
        $lastname1 = trim($_POST['lastname1'] ?? '');
        $lastname2 = trim($_POST['lastname2'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $roles = $_POST['roles'] ?? [];
        $active = isset($_POST['active']) ? 1 : 0;

        // ******************* validaciones básicas *******************
        if (!$id || $name === '' || $lastname1 === '' || $email === '') {

            $_SESSION['error'] = 'Datos obligatorios incompletos';
            $_SESSION['form_data'] = $_POST;

            header('Location: ' . BASE_URL . 'users.php?action=edit&id=' . $id);
            exit;
        }

        // ******************* validar email *******************
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $_SESSION['error'] = 'Correo electrónico inválido';
            $_SESSION['form_data'] = $_POST;

            header('Location: ' . BASE_URL . 'users.php?action=edit&id=' . $id);
            exit;
        }

        // ******************* validar roles *******************
        if (empty($roles)) {

            $_SESSION['error'] = 'Debe seleccionar al menos un rol';
            $_SESSION['form_data'] = $_POST;

            header('Location: ' . BASE_URL . 'users.php?action=edit&id=' . $id);
            exit;
        }

        // ******************* preparar password si existe *******************
        $hashedPassword = null;

        if (!empty($password)) {

            if (strlen($password) < 6) {

                $_SESSION['error'] = 'La contraseña debe tener mínimo 6 caracteres';
                $_SESSION['form_data'] = $_POST;

                header('Location: ' . BASE_URL . 'users.php?action=edit&id=' . $id);
                exit;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        }

        // ******************* actualizar usuario *******************
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

        // ******************* actualizar roles *******************
        $this->userRepository->updateRoles($id, $roles);

        $_SESSION['success'] = 'Usuario actualizado correctamente';

        header('Location: ' . BASE_URL . 'users.php');
        exit;
    }

    // =====================================================
// DESACTIVAR USUARIO
// =====================================================
    public function deactivate(int $id)
    {
        // ******************* obtener id *******************
        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['error'] = 'ID inválido';
            header("Location: users.php");
            exit;
        }

        // ******************* ejecutar soft delete *******************
        $deleted = $this->userRepository->deactivate($id);

        // ******************* mensaje SweetAlert *******************
        if ($deleted) {
            $_SESSION['success'] = 'Usuario desactivado correctamente';
        } else {
            $_SESSION['error'] = 'No se pudo desactivar el usuario';
        }

        // ******************* redirección *******************
        header("Location: users.php");
        exit;
    }

}

?>