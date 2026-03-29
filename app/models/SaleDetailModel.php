<?php
/**
 * Location: vetapp/app/models/SaleDetailModel.php
 * Representa una línea de detalle de venta
 */

class SaleDetailModel
{
    private $id;
    private $id_sale;
    private $id_medication;
    private $id_batch;
    private $quantity;
    private $unit_price;
    private $subtotal;
    private $tax_rate;
    private $tax_amount;
    private $total;

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

    // Getters y setters
    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }

    public function getIdSale() { return $this->id_sale; }
    public function setIdSale($id) { $this->id_sale = $id; }

    public function getIdMedication() { return $this->id_medication; }
    public function setIdMedication($id) { $this->id_medication = $id; }

    public function getIdBatch() { return $this->id_batch; }
    public function setIdBatch($id) { $this->id_batch = $id; }

    public function getQuantity() { return $this->quantity; }
    public function setQuantity($qty) { $this->quantity = $qty; }

    public function getUnitPrice() { return $this->unit_price; }
    public function setUnitPrice($price) { $this->unit_price = $price; }

    public function getSubtotal() { return $this->subtotal; }
    public function setSubtotal($sub) { $this->subtotal = $sub; }

    public function getTaxRate() { return $this->tax_rate; }
    public function setTaxRate($rate) { $this->tax_rate = $rate; }

    public function getTaxAmount() { return $this->tax_amount; }
    public function setTaxAmount($amount) { $this->tax_amount = $amount; }

    public function getTotal() { return $this->total; }
    public function setTotal($total) { $this->total = $total; }
}