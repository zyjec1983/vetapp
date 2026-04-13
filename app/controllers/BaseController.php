<?php
/**
 * app/controllers/BaseController.php
 */

require_once __DIR__ . '/../helpers/csrf.php';
require_once __DIR__ . '/../helpers/sanitize.php';

class BaseController
{
    protected $db;
    protected $userRepository;
    protected $roleRepository;

    public function __construct()
    {
        // ... (tu código existente)
        // Asegurar que la sesión esté iniciada para CSRF
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Valida el token CSRF en peticiones POST
     */
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

    /**
     * Valida campos requeridos y devuelve array de errores
     * @param array $fields ['campo' => 'Nombre legible', ...]
     * @param array $data Datos a validar
     * @return array
     */
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

    /**
     * Sanitiza los datos de entrada (globalmente para $_POST o $_GET)
     */
    protected function sanitizeInputData($data)
    {
        return sanitizeArray($data);
    }
}