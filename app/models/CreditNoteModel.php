<?php
/**
 * Location: vetapp/app/models/CreditNoteModel.php
 *
 * Modelo de dominio para Notas de Crédito SRI.
 * Representa el header de una nota de crédito electrónica.
 */

class CreditNoteModel
{
    private $id;
    private $id_sale;
    private $id_original_invoice;
    private $company_id;
    private $id_user;
    private $numero_nota_credito;
    private $clave_acceso;
    private $numero_autorizacion;
    private $estado_sri;
    private $motivo;
    private $subtotal;
    private $discount;
    private $tax_total;
    private $total;
    private $mensaje_sri;
    private $xml_path;
    private $xml_firmado_path;
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
    public function getIdSale() { return $this->id_sale; }
    public function getIdOriginalInvoice() { return $this->id_original_invoice; }
    public function getCompanyId() { return $this->company_id; }
    public function getIdUser() { return $this->id_user; }
    public function getNumeroNotaCredito() { return $this->numero_nota_credito; }
    public function getClaveAcceso() { return $this->clave_acceso; }
    public function getNumeroAutorizacion() { return $this->numero_autorizacion; }
    public function getEstadoSri() { return $this->estado_sri; }
    public function getMotivo() { return $this->motivo; }
    public function getSubtotal() { return $this->subtotal; }
    public function getDiscount() { return $this->discount; }
    public function getTaxTotal() { return $this->tax_total; }
    public function getTotal() { return $this->total; }
    public function getMensajeSri() { return $this->mensaje_sri; }
    public function getXmlPath() { return $this->xml_path; }
    public function getXmlFirmadoPath() { return $this->xml_firmado_path; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }

    // Setters
    public function setId($v) { $this->id = $v; }
    public function setIdSale($v) { $this->id_sale = $v; }
    public function setIdOriginalInvoice($v) { $this->id_original_invoice = $v; }
    public function setCompanyId($v) { $this->company_id = $v; }
    public function setIdUser($v) { $this->id_user = $v; }
    public function setNumeroNotaCredito($v) { $this->numero_nota_credito = $v; }
    public function setClaveAcceso($v) { $this->clave_acceso = $v; }
    public function setNumeroAutorizacion($v) { $this->numero_autorizacion = $v; }
    public function setEstadoSri($v) { $this->estado_sri = $v; }
    public function setMotivo($v) { $this->motivo = $v; }
    public function setSubtotal($v) { $this->subtotal = $v; }
    public function setDiscount($v) { $this->discount = $v; }
    public function setTaxTotal($v) { $this->tax_total = $v; }
    public function setTotal($v) { $this->total = $v; }
    public function setMensajeSri($v) { $this->mensaje_sri = $v; }
    public function setXmlPath($v) { $this->xml_path = $v; }
    public function setXmlFirmadoPath($v) { $this->xml_firmado_path = $v; }
    public function setCreatedAt($v) { $this->created_at = $v; }
    public function setUpdatedAt($v) { $this->updated_at = $v; }
}
