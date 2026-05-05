<?php
/**
 * Location: vetapp/app/controllers/BaseController.php
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../repositories/RoleRepository.php';
require_once __DIR__ . '/../helpers/csrf.php';
require_once __DIR__ . '/../helpers/sanitize.php';

class BaseController
{
    protected $db;
    protected $userRepository;
    protected $roleRepository;

    // ********** CONSTRUCTOR: INICIALIZA CONEXIÓN Y REPOSITORIOS GLOBALES **********
    public function __construct()
    {
        // ********** INICIALIZAR CONEXIÓN A BASE DE DATOS **********
        $this->db = Database::getInstance()->getConnection();

        // ********** INICIALIZAR REPOSITORIOS GLOBALES **********
        $this->userRepository = new UserRepository($this->db);
        $this->roleRepository = new RoleRepository($this->db);

        // ********** ASEGURAR QUE LA SESIÓN ESTÉ INICIADA PARA CSRF **********
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // ********** VALIDA EL TOKEN CSRF EN PETICIONES POST **********
    protected function validateCSRF()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!verifyCSRFToken($token)) {
                http_response_code(403);
                die('Error de seguridad: CSRF token inválido. Por favor, recargue la página y vuelva a intentarlo.');
            }
        }
    }

    // ********** VALIDA CAMPOS REQUERIDOS Y DEVUELVE ARRAY DE ERRORES **********
    protected function validateRequiredFields($fields, $data)
    {
        $errors = [];
        foreach ($fields as $field => $label) {
            if (empty($data[$field])) {
                $errors[] = "El campo {$label} es obligatorio.";
            }
        }
        return $errors;
    }

    // ********** SANITIZA LOS DATOS DE ENTRADA **********
    protected function sanitizeInputData($data)
    {
        return sanitizeArray($data);
    }
}