<?php
/**
 * Location: vetapp/app/controllers/BaseController.php
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../repositories/RoleRepository.php';

class BaseController
{
    protected $db;

    // 🔥 repositorios globales
    protected $userRepository;
    protected $roleRepository;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();

        // 🔥 inicializar repos
        $this->userRepository = new UserRepository($this->db);
        $this->roleRepository = new RoleRepository($this->db);
    }
}