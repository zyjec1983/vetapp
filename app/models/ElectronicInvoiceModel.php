<?php
/**
 * Location: vetapp/app/models/ElectronicInvoiceModel.php
 *
 * Modelo para facturas electrónicas SRI.
 * Representa el estado de una venta ante el SRI.
 */

class ElectronicInvoiceModel
{
    private $id;
    private $id_sale;
    private $company_id;
    private $numero_factura;
    private $clave_acceso;
    private $numero_autorizacion;
    private $estado_sri;
    private $mensaje_sri;
    private $fecha_envio;
    private $fecha_autorizacion;
    private $xml_path;
    private $xml_firmado_path;
    private $creado_en;

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
    public function getCompanyId() { return $this->company_id; }
    public function getNumeroFactura() { return $this->numero_factura; }
    public function getClaveAcceso() { return $this->clave_acceso; }
    public function getNumeroAutorizacion() { return $this->numero_autorizacion; }
    public function getEstadoSri() { return $this->estado_sri; }
    public function getMensajeSri() { return $this->mensaje_sri; }
    public function getFechaEnvio() { return $this->fecha_envio; }
    public function getFechaAutorizacion() { return $this->fecha_autorizacion; }
    public function getXmlPath() { return $this->xml_path; }
    public function getXmlFirmadoPath() { return $this->xml_firmado_path; }
    public function getCreadoEn() { return $this->creado_en; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setIdSale($val) { $this->id_sale = $val; }
    public function setCompanyId($val) { $this->company_id = $val; }
    public function setNumeroFactura($val) { $this->numero_factura = $val; }
    public function setClaveAcceso($val) { $this->clave_acceso = $val; }
    public function setNumeroAutorizacion($val) { $this->numero_autorizacion = $val; }
    public function setEstadoSri($val) { $this->estado_sri = $val; }
    public function setMensajeSri($val) { $this->mensaje_sri = $val; }
    public function setFechaEnvio($val) { $this->fecha_envio = $val; }
    public function setFechaAutorizacion($val) { $this->fecha_autorizacion = $val; }
    public function setXmlPath($val) { $this->xml_path = $val; }
    public function setXmlFirmadoPath($val) { $this->xml_firmado_path = $val; }
    public function setCreadoEn($val) { $this->creado_en = $val; }
}
