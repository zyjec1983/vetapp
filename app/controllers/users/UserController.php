<?php
/**
 * Location: vetapp/app/controllers/users/UserController.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap.php';


class UserController
{
    private UserRepository $userRepository;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $db = Database::getInstance()->getConnection();
        $this->userRepository = new UserRepository($db);
    }

    public function index(): void
    {
        // 🔐 Solo admin
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


}

?>