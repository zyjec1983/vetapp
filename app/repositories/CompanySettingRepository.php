<?php
/**
 * Location: vetapp/app/repositories/CompanySettingRepository.php
 *
 * CRUD completo para la gestión de empresas/RUCs.
 * Usa los nombres de columna reales de la tabla company_settings.
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/CompanySettingModel.php';

class CompanySettingRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll()
    {
        $sql = "SELECT * FROM company_settings WHERE deleted_at IS NULL ORDER BY commercial_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActivas()
    {
        $sql = "SELECT * FROM company_settings WHERE activo = 1 AND deleted_at IS NULL ORDER BY commercial_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id)
    {
        $sql = "SELECT * FROM company_settings WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByRuc($ruc)
    {
        $sql = "SELECT * FROM company_settings WHERE ruc = :ruc LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':ruc' => $ruc]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(CompanySettingModel $company)
    {
        $sql = "INSERT INTO company_settings (
            ruc, business_name, commercial_name, address, phone, email,
            accountant, special_contributor, establishment_code,
            emission_point, certificate_path, ambiente, activo
        ) VALUES (
            :ruc, :business_name, :commercial_name, :address, :phone, :email,
            :accountant, :special_contributor, :establishment_code,
            :emission_point, :certificate_path, :ambiente, :activo
        )";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':ruc' => $company->getRuc(),
            ':business_name' => $company->getBusinessName(),
            ':commercial_name' => $company->getCommercialName(),
            ':address' => $company->getAddress(),
            ':phone' => $company->getPhone(),
            ':email' => $company->getEmail(),
            ':accountant' => $company->getAccountant() === 'SI' ? 1 : 0,
            ':special_contributor' => $company->getSpecialContributor() ? 1 : 0,
            ':establishment_code' => $company->getEstablishmentCode(),
            ':emission_point' => $company->getEmissionPoint(),
            ':certificate_path' => $company->getCertificatePath(),
            ':ambiente' => $company->getAmbiente(),
            ':activo' => $company->getActivo() ? 1 : 0,
        ]);
        return $this->db->lastInsertId();
    }

    public function update(CompanySettingModel $company)
    {
        $sql = "UPDATE company_settings SET
            ruc = :ruc,
            business_name = :business_name,
            commercial_name = :commercial_name,
            address = :address,
            phone = :phone,
            email = :email,
            accountant = :accountant,
            special_contributor = :special_contributor,
            establishment_code = :establishment_code,
            emission_point = :emission_point,
            certificate_path = :certificate_path,
            ambiente = :ambiente,
            activo = :activo,
            updated_at = NOW()
        WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $company->getId(),
            ':ruc' => $company->getRuc(),
            ':business_name' => $company->getBusinessName(),
            ':commercial_name' => $company->getCommercialName(),
            ':address' => $company->getAddress(),
            ':phone' => $company->getPhone(),
            ':email' => $company->getEmail(),
            ':accountant' => $company->getAccountant() === 'SI' ? 1 : 0,
            ':special_contributor' => $company->getSpecialContributor() ? 1 : 0,
            ':establishment_code' => $company->getEstablishmentCode(),
            ':emission_point' => $company->getEmissionPoint(),
            ':certificate_path' => $company->getCertificatePath(),
            ':ambiente' => $company->getAmbiente(),
            ':activo' => $company->getActivo() ? 1 : 0,
        ]);
    }

    public function deactivate($id)
    {
        $sql = "UPDATE company_settings SET activo = 0, updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function softDelete($id)
    {
        $sql = "UPDATE company_settings SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function restore($id)
    {
        $sql = "UPDATE company_settings SET deleted_at = NULL, updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function getDeleted()
    {
        $sql = "SELECT * FROM company_settings WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Upload certificate file to protected directory.
     * Returns the stored path or false on error.
     */
    public function uploadCertificate($companyId, $file)
    {
        $allowedExt = ['p12', 'pfx'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt)) {
            return ['success' => false, 'error' => 'Solo se aceptan archivos .p12 o .pfx'];
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            return ['success' => false, 'error' => 'El certificado no debe superar 2MB'];
        }

        $dir = STORAGE_PATH . '/certificates/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Get RUC for filename
        $company = $this->findById($companyId);
        $ruc = $company['ruc'] ?? 'unknown';
        $filename = $ruc . '_' . time() . '.' . $ext;
        $destPath = $dir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return ['success' => false, 'error' => 'Error al mover el archivo'];
        }

        return ['success' => true, 'path' => $destPath];
    }

    /**
     * Delete old certificate file before uploading new one.
     */
    public function deleteCertificate($companyId)
    {
        $company = $this->findById($companyId);
        if (!$company || empty($company['certificate_path'])) {
            return false;
        }

        $oldPath = $company['certificate_path'];
        if (file_exists($oldPath)) {
            unlink($oldPath);
        }

        // Clear from DB
        $sql = "UPDATE company_settings SET certificate_path = NULL, updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $companyId]);
    }

    /**
     * Update certificate path in DB.
     */
    public function updateCertificatePath($companyId, $path)
    {
        $sql = "UPDATE company_settings SET certificate_path = :path, updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $companyId, ':path' => $path]);
    }

    /**
     * Test SRI connection for a company.
     */
    public function testSriConnection($ambiente)
    {
        $wsdl = ($ambiente === 'pruebas')
            ? 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl'
            : 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl';

        try {
            if (!class_exists('SoapClient')) {
                return ['success' => false, 'error' => 'Extensión SOAP no habilitada'];
            }

            $client = new SoapClient($wsdl, [
                'trace' => true,
                'exceptions' => true,
                'connection_timeout' => 15,
            ]);

            // Try to get the WSDL functions (lightweight test)
            $functions = $client->__getFunctions();

            if (!empty($functions)) {
                return [
                    'success' => true,
                    'message' => "Conexión exitosa al SRI ($ambiente). WSDL cargado correctamente.",
                    'ambiente' => $ambiente,
                    'wsdl' => $wsdl,
                ];
            }

            return ['success' => false, 'error' => 'WSDL cargado pero sin funciones disponibles'];

        } catch (SoapFault $e) {
            return [
                'success' => false,
                'error' => "No se pudo conectar al SRI: " . $e->getMessage(),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => "Error: " . $e->getMessage(),
            ];
        }
    }
}
