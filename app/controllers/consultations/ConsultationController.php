<?php
/**
 * Location: vetapp/app/controllers/consultations/ConsultationController.php
 */

require_once __DIR__ . '/../BaseController.php';
require_once __DIR__ . '/../../repositories/ConsultationRepository.php';

class ConsultationController extends BaseController
{
    private $consultationRepository;

    public function __construct()
    {
        parent::__construct();

        $this->consultationRepository = new ConsultationRepository($this->db);
    }

    public function index()
    {

        // Obtener datos
        $consultations = $this->consultationRepository->getAll();       

        // 🔍 DEBUG 3: verificar ruta de vista
        $viewPath = __DIR__ . '/../../views/consultations/index.php';

        if (!file_exists($viewPath)) {
            die("❌ NO EXISTE: " . $viewPath);
        }



        require_once $viewPath;
    }
}