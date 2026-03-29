<?php
/**
 * Location: vetapp/app/models/SaleModel.php
 * Representa la cabecera de una venta
 */

class SaleModel
{
    private $id_sale;
    private $sale_code;
    private $id_client;
    private $id_user;
    private $sale_date;
    private $subtotal;
    private $discount;
    private $tax_total;
    private $total;
    private $payment_method;
    private $status;
    private $observations;
    private $created_at;

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

    // Getters y setters (puedes generarlos con tu IDE, aquí pongo los esenciales)
    public function getIdSale() { return $this->id_sale; }
    public function setIdSale($id) { $this->id_sale = $id; }

    public function getSaleCode() { return $this->sale_code; }
    public function setSaleCode($code) { $this->sale_code = $code; }

    public function getIdClient() { return $this->id_client; }
    public function setIdClient($id) { $this->id_client = $id; }

    public function getIdUser() { return $this->id_user; }
    public function setIdUser($id) { $this->id_user = $id; }

    public function getSaleDate() { return $this->sale_date; }
    public function setSaleDate($date) { $this->sale_date = $date; }

    public function getSubtotal() { return $this->subtotal; }
    public function setSubtotal($sub) { $this->subtotal = $sub; }

    public function getDiscount() { return $this->discount; }
    public function setDiscount($disc) { $this->discount = $disc; }

    public function getTaxTotal() { return $this->tax_total; }
    public function setTaxTotal($tax) { $this->tax_total = $tax; }

    public function getTotal() { return $this->total; }
    public function setTotal($total) { $this->total = $total; }

    public function getPaymentMethod() { return $this->payment_method; }
    public function setPaymentMethod($method) { $this->payment_method = $method; }

    public function getStatus() { return $this->status; }
    public function setStatus($status) { $this->status = $status; }

    public function getObservations() { return $this->observations; }
    public function setObservations($obs) { $this->observations = $obs; }

    public function getCreatedAt() { return $this->created_at; }
    public function setCreatedAt($date) { $this->created_at = $date; }
}