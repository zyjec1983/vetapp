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
        if(session_status() === PHP_SESSION_NONE){
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
    
}

?>

