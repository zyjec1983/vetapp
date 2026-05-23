<?php
/**
 * Location: vetapp/app/models/CompanySettingModel.php
 *
 * Modelo para las configuraciones de empresa (RUCs).
 * Usa los nombres de columna reales de la tabla company_settings.
 */

class CompanySettingModel
{
    private $id;
    private $business_name;        // Nombre legal (razón social)
    private $commercial_name;      // Nombre comercial visible
    private $ruc;
    private $address;
    private $phone;
    private $email;
    private $accountant;           // Obligado contabilidad (0/1)
    private $special_contributor;  // Contribuyente especial (0/1)
    private $withholding_agent;
    private $establishment_code;
    private $emission_point;
    private $logo;
    private $certificate_path;
    private $ambiente;             // pruebas | produccion
    private $activo;
    private $created_at;
    private $updated_at;

    public function __construct($data = [])
    {
        $this->hydrate($data);
    }

    public function hydrate(array $data)
    {
        foreach ($data as $key => $value) {
            $method = 'set' . str_replace('_', '', ucwords($key, '_'));
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    // Getters
    public function getId() { return $this->id; }
    public function getBusinessName() { return $this->business_name; }
    public function getCommercialName() { return $this->commercial_name; }
    public function getRuc() { return $this->ruc; }
    public function getAddress() { return $this->address; }
    public function getPhone() { return $this->phone; }
    public function getEmail() { return $this->email; }
    public function getAccountant() { return $this->accountant ? 'SI' : 'NO'; }
    public function getSpecialContributor() { return $this->special_contributor; }
    public function getWithholdingAgent() { return $this->withholding_agent; }
    public function getEstablishmentCode() { return $this->establishment_code ?? '001'; }
    public function getEmissionPoint() { return $this->emission_point ?? '001'; }
    public function getLogo() { return $this->logo; }
    public function getCertificatePath() { return $this->certificate_path; }
    public function getAmbiente() { return $this->ambiente ?? 'pruebas'; }
    public function getActivo() { return $this->activo; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setBusinessName($val) { $this->business_name = $val; }
    public function setCommercialName($val) { $this->commercial_name = $val; }
    public function setRuc($val) { $this->ruc = $val; }
    public function setAddress($val) { $this->address = $val; }
    public function setPhone($val) { $this->phone = $val; }
    public function setEmail($val) { $this->email = $val; }
    public function setAccountant($val) { $this->accountant = (bool)$val; }
    public function setSpecialContributor($val) { $this->special_contributor = (bool)$val; }
    public function setWithholdingAgent($val) { $this->withholding_agent = (bool)$val; }
    public function setEstablishmentCode($val) { $this->establishment_code = $val; }
    public function setEmissionPoint($val) { $this->emission_point = $val; }
    public function setLogo($val) { $this->logo = $val; }
    public function setCertificatePath($val) { $this->certificate_path = $val; }
    public function setAmbiente($val) { $this->ambiente = $val; }
    public function setActivo($val) { $this->activo = (bool)$val; }
    public function setCreatedAt($val) { $this->created_at = $val; }
    public function setUpdatedAt($val) { $this->updated_at = $val; }
}
